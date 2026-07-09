<?php

namespace App\Services;

use App\Models\PersonalTask;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class PersonalTaskPageService
{
    public function __construct(
        protected MemberDashboardService $memberDashboard,
        protected MemberPersonalTaskService $memberPersonalTaskService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        $rows = $this->collectRows($user, $filters);
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(5, min(50, (int) ($filters['per_page'] ?? 15)));
        $total = $rows->count();
        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        return new Paginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => array_filter([
                    'search' => $filters['search'] ?? null,
                    'status' => $filters['status'] ?? null,
                    'source' => $filters['source'] ?? null,
                    'priority' => $filters['priority'] ?? null,
                    'assignment' => $filters['assignment'] ?? null,
                    'per_page' => $perPage !== 15 ? $perPage : null,
                ]),
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function collectRows(User $user, array $filters = []): Collection
    {
        $rows = collect();

        if (($filters['source'] ?? 'all') !== 'manual') {
            $rows = $rows->merge($this->systemRows($user));
        }

        if (($filters['source'] ?? 'all') !== 'system') {
            $rows = $rows->merge($this->personalRows($user));
        }

        $rows = $this->applyFilters($rows, $user, $filters);

        return $rows
            ->sortBy(fn (array $row) => [
                $row['sort_tier'] ?? 1,
                match ($row['priority'] ?? 'medium') {
                    'high' => 0,
                    'medium' => 1,
                    default => 2,
                },
                $row['due_at_sort'] ?? '9999-99-99',
                strtolower((string) ($row['title'] ?? '')),
            ])
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function systemRows(User $user): Collection
    {
        $payload = $this->memberDashboard->build($user);
        $tasks = $this->memberPersonalTaskService->buildStaffTasks($user, $payload, null);

        return collect($tasks)->map(function (array $task) {
            return [
                'key' => 'system:' . ($task['id'] ?? uniqid('system-')),
                'type' => 'system',
                'personal_task_id' => null,
                'title' => (string) ($task['title'] ?? 'Task'),
                'description' => (string) ($task['description'] ?? ''),
                'priority' => (string) ($task['priority'] ?? 'medium'),
                'category' => (string) ($task['category'] ?? 'system'),
                'status' => 'pending',
                'status_label' => 'Open',
                'due_at' => null,
                'due_at_sort' => '9999-99-99',
                'route' => $task['route'] ?? $task['url'] ?? null,
                'action' => $task['action'] ?? null,
                'created_by' => null,
                'created_by_name' => 'System',
                'assigned_to' => null,
                'assigned_to_name' => 'You',
                'can_edit' => false,
                'can_delete' => false,
                'can_complete' => false,
                'can_confirm' => false,
                'sort_tier' => 0,
            ];
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function personalRows(User $user): Collection
    {
        return PersonalTask::query()
            ->visibleTo($user)
            ->with(['creator:id,name', 'assignee:id,name'])
            ->whereNot('status', PersonalTask::STATUS_CANCELLED)
            ->latest('updated_at')
            ->get()
            ->map(fn (PersonalTask $task) => $this->mapPersonalTaskRow($task, $user));
    }

    /**
     * @return array<string, mixed>
     */
    public function mapPersonalTaskRow(PersonalTask $task, User $user): array
    {
        $isCreator = (int) $task->created_by === (int) $user->id;
        $isAssignee = (int) $task->assigned_to === (int) $user->id;

        return [
            'key' => 'personal:' . $task->id,
            'type' => 'personal',
            'personal_task_id' => $task->id,
            'title' => $task->title,
            'description' => (string) ($task->description ?? ''),
            'priority' => $task->priority,
            'category' => 'personal',
            'status' => $task->status,
            'status_label' => $task->statusLabel(),
            'due_at' => $task->due_at?->format('M j, Y'),
            'due_at_sort' => $task->due_at?->toDateString() ?? '9999-99-99',
            'route' => null,
            'action' => null,
            'created_by' => $task->created_by,
            'created_by_name' => $task->creator?->name ?? 'Unknown',
            'assigned_to' => $task->assigned_to,
            'assigned_to_name' => $task->assignee?->name ?? 'Unknown',
            'can_edit' => $isCreator && $task->status === PersonalTask::STATUS_PENDING,
            'can_delete' => $isCreator && $task->status === PersonalTask::STATUS_PENDING,
            'can_complete' => $isAssignee && $task->status === PersonalTask::STATUS_PENDING,
            'can_confirm' => $isCreator && $task->awaitsCreatorConfirmation(),
            'sort_tier' => in_array($task->status, [PersonalTask::STATUS_PENDING, PersonalTask::STATUS_COMPLETED], true) ? 0 : 2,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    protected function applyFilters(Collection $rows, User $user, array $filters): Collection
    {
        $search = strtolower(trim((string) ($filters['search'] ?? '')));
        if ($search !== '') {
            $rows = $rows->filter(function (array $row) use ($search) {
                return str_contains(strtolower((string) $row['title']), $search)
                    || str_contains(strtolower((string) $row['description']), $search)
                    || str_contains(strtolower((string) ($row['created_by_name'] ?? '')), $search)
                    || str_contains(strtolower((string) ($row['assigned_to_name'] ?? '')), $search);
            });
        }

        $status = (string) ($filters['status'] ?? 'open');
        if ($status === 'open') {
            $rows = $rows->filter(fn (array $row) => in_array($row['status'] ?? '', ['pending', 'completed'], true));
        } elseif ($status === 'confirmed') {
            $rows = $rows->filter(fn (array $row) => ($row['status'] ?? '') === 'confirmed');
        }

        $priority = (string) ($filters['priority'] ?? 'all');
        if ($priority !== 'all') {
            $rows = $rows->filter(fn (array $row) => ($row['priority'] ?? '') === $priority);
        }

        $assignment = (string) ($filters['assignment'] ?? 'all');
        if ($assignment === 'created') {
            $rows = $rows->filter(fn (array $row) => ($row['type'] ?? '') === 'personal'
                && (int) ($row['created_by'] ?? 0) === (int) $user->id);
        } elseif ($assignment === 'assigned') {
            $rows = $rows->filter(fn (array $row) => ($row['type'] ?? '') === 'system'
                || ((int) ($row['assigned_to'] ?? 0) === (int) $user->id
                    && (int) ($row['created_by'] ?? 0) !== (int) $user->id));
        }

        return $rows->values();
    }

    /**
     * @return Collection<int, User>
     */
    public function assigneeOptionsFor(User $user): Collection
    {
        $facilityId = $user->facility_id ?? $user->resolvedBpEmployee()?->currentAssignment?->facility_id;

        $query = User::query()->orderBy('name');

        if ($facilityId) {
            $query->where(function ($nested) use ($facilityId) {
                $nested->where('facility_id', $facilityId)
                    ->orWhereHas('bpEmployee.currentAssignment', fn ($assignment) => $assignment->where('facility_id', $facilityId));
            });
        } else {
            $query->where('id', $user->id);
        }

        $users = $query->limit(100)->get(['id', 'name', 'email']);

        if (! $users->contains(fn (User $candidate) => (int) $candidate->id === (int) $user->id)) {
            $users->prepend($user);
        }

        return $users->unique('id')->values();
    }

    public function markComplete(PersonalTask $task, User $user): PersonalTask
    {
        $task->status = PersonalTask::STATUS_COMPLETED;
        $task->completed_at = now();
        $task->completed_by = $user->id;

        if ((int) $task->created_by === (int) $task->assigned_to) {
            $task->status = PersonalTask::STATUS_CONFIRMED;
            $task->confirmed_at = now();
            $task->confirmed_by = $user->id;
        }

        $task->save();

        return $task->fresh(['creator', 'assignee']);
    }

    public function confirmCompletion(PersonalTask $task, User $user): PersonalTask
    {
        $task->status = PersonalTask::STATUS_CONFIRMED;
        $task->confirmed_at = now();
        $task->confirmed_by = $user->id;
        $task->save();

        return $task->fresh(['creator', 'assignee']);
    }
}
