<?php

namespace App\Livewire\Member;

use App\Models\PersonalTask;
use App\Services\PersonalTaskPageService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class PersonalTasksTable extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $status = 'open';

    public string $source = 'all';

    public string $priority = 'all';

    public string $assignment = 'all';

    public int $perPage = 15;

    public bool $showModal = false;

    public ?int $editingTaskId = null;

    public string $title = '';

    public string $description = '';

    public string $taskPriority = 'medium';

    public ?string $dueAt = null;

    public ?int $assignedTo = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'open'],
        'source' => ['except' => 'all'],
        'priority' => ['except' => 'all'],
        'assignment' => ['except' => 'all'],
        'perPage' => ['except' => 15, 'as' => 'per_page'],
    ];

    public function mount(): void
    {
        $this->assignedTo = auth()->id();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSource(): void
    {
        $this->resetPage();
    }

    public function updatingPriority(): void
    {
        $this->resetPage();
    }

    public function updatingAssignment(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->authorize('create', PersonalTask::class);
        $this->resetForm();
        $this->editingTaskId = null;
        $this->assignedTo = auth()->id();
        $this->showModal = true;
    }

    public function openEditModal(int $taskId): void
    {
        $task = PersonalTask::query()->findOrFail($taskId);
        $this->authorize('update', $task);

        $this->editingTaskId = $task->id;
        $this->title = $task->title;
        $this->description = (string) ($task->description ?? '');
        $this->taskPriority = $task->priority;
        $this->dueAt = $task->due_at?->format('Y-m-d');
        $this->assignedTo = $task->assigned_to;
        $this->showModal = true;
    }

    public function saveTask(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'taskPriority' => ['required', 'in:low,medium,high'],
            'dueAt' => ['nullable', 'date'],
            'assignedTo' => ['required', 'integer', 'exists:users,id'],
        ]);

        if ($this->editingTaskId) {
            $task = PersonalTask::query()->findOrFail($this->editingTaskId);
            $this->authorize('update', $task);

            $task->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'priority' => $validated['taskPriority'],
                'due_at' => $validated['dueAt'] ?? null,
                'assigned_to' => $validated['assignedTo'],
            ]);

            session()->flash('success', 'Task updated.');
        } else {
            $this->authorize('create', PersonalTask::class);

            PersonalTask::query()->create([
                'created_by' => auth()->id(),
                'assigned_to' => $validated['assignedTo'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'priority' => $validated['taskPriority'],
                'due_at' => $validated['dueAt'] ?? null,
                'status' => PersonalTask::STATUS_PENDING,
            ]);

            session()->flash('success', 'Task created.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function deleteTask(int $taskId): void
    {
        $task = PersonalTask::query()->findOrFail($taskId);
        $this->authorize('delete', $task);
        $task->delete();

        session()->flash('success', 'Task deleted.');
    }

    public function completeTask(int $taskId): void
    {
        $task = PersonalTask::query()->findOrFail($taskId);
        $this->authorize('complete', $task);

        app(PersonalTaskPageService::class)->markComplete($task, auth()->user());

        session()->flash('success', $task->fresh()->awaitsCreatorConfirmation()
            ? 'Task marked complete. Waiting for creator confirmation.'
            : 'Task marked complete.');
    }

    public function confirmTask(int $taskId): void
    {
        $task = PersonalTask::query()->findOrFail($taskId);
        $this->authorize('confirm', $task);

        app(PersonalTaskPageService::class)->confirmCompletion($task, auth()->user());

        session()->flash('success', 'Task confirmed as complete.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render(): View
    {
        $user = auth()->user();
        $service = app(PersonalTaskPageService::class);

        $tasks = $service->paginateForUser($user, [
            'search' => $this->search,
            'status' => $this->status,
            'source' => $this->source,
            'priority' => $this->priority,
            'assignment' => $this->assignment,
            'per_page' => $this->perPage,
            'page' => $this->getPage(),
        ]);

        return view('livewire.member.personal-tasks-table', [
            'tasks' => $tasks,
            'assigneeOptions' => $service->assigneeOptionsFor($user),
        ]);
    }

    protected function resetForm(): void
    {
        $this->editingTaskId = null;
        $this->title = '';
        $this->description = '';
        $this->taskPriority = 'medium';
        $this->dueAt = null;
        $this->assignedTo = auth()->id();
        $this->resetValidation();
    }
}
