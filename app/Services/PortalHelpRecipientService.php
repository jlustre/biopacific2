<?php

namespace App\Services;

use App\Models\EmployeeEmailMapping;
use App\Models\PortalHelpRequest;
use App\Models\User;
use Illuminate\Support\Collection;

class PortalHelpRecipientService
{
    /**
     * Resolve email delivery from Employee Email Mappings for a help channel/role.
     * Available primary role holders → To; if none, available secondary → To;
     * remaining secondaries → CC. Away/vacation people are skipped for email.
     *
     * @return array{to: list<string>, cc: list<string>, skipped_vacation: list<string>}
     */
    public function resolveEmailRecipients(string $channel, ?int $facilityId = null): array
    {
        $recipients = $this->mappingsForChannel($channel, $facilityId);

        if ($recipients->isEmpty()) {
            return $this->fallbackFromConfig($channel);
        }

        $available = $recipients->filter(fn (EmployeeEmailMapping $r) => $r->isAvailable())->values();
        $skipped = $recipients
            ->filter(fn (EmployeeEmailMapping $r) => $r->isAway() || ! $r->is_active)
            ->map(fn (EmployeeEmailMapping $r) => $r->displayName())
            ->values()
            ->all();

        $to = $this->emailsFor($available->filter(fn (EmployeeEmailMapping $r) => $r->is_primary));

        if ($to === []) {
            $to = $this->emailsFor($available->filter(fn (EmployeeEmailMapping $r) => ! $r->is_primary));
        }

        if ($to === []) {
            $to = $this->emailsFor($available);
        }

        if ($to === []) {
            $fallback = $this->fallbackFromConfig($channel);

            return [
                'to' => $fallback['to'],
                'cc' => $fallback['cc'],
                'skipped_vacation' => $skipped,
            ];
        }

        $cc = $this->emailsFor($available->filter(fn (EmployeeEmailMapping $r) => ! $r->is_primary));
        $cc = array_values(array_diff($cc, $to));

        return [
            'to' => $to,
            'cc' => $cc,
            'skipped_vacation' => $skipped,
        ];
    }

    /**
     * @return list<string>
     */
    public function channelsForUser(User $user): array
    {
        return EmployeeEmailMapping::query()
            ->active()
            ->whereIn('category', array_keys(EmployeeEmailMapping::portalHelpCategories()))
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);
                if (filled($user->email)) {
                    $query->orWhereRaw('LOWER(employee_email) = ?', [strtolower(trim($user->email))]);
                }
            })
            ->pluck('category')
            ->unique()
            ->values()
            ->all();
    }

    public function userReceivesChannel(User $user, string $channel): bool
    {
        return in_array($channel, $this->channelsForUser($user), true);
    }

    public function userCanAccessHelpRequest(User $user, PortalHelpRequest $request): bool
    {
        if ((int) $request->user_id === (int) $user->id) {
            return true;
        }

        return $this->userReceivesChannel($user, (string) $request->type);
    }

    /**
     * @return Collection<int, User>
     */
    public function candidateUsers(): Collection
    {
        return User::query()
            ->role(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd'])
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * Flatten contact roles for admin selects: roleKey => "Channel — Role label".
     *
     * @return array<string, array{channel: string, label: string, responsibility: string}>
     */
    public function flattenedContactRoles(): array
    {
        $out = [];
        foreach (config('portal-help.contact_roles', []) as $channel => $roles) {
            $channelLabel = config('portal-help.types.'.$channel, $channel);
            foreach ($roles as $roleKey => $meta) {
                $out[$roleKey] = [
                    'channel' => $channel,
                    'label' => $channelLabel.' — '.($meta['label'] ?? $roleKey),
                    'short_label' => $meta['label'] ?? $roleKey,
                    'responsibility' => $meta['responsibility'] ?? 'secondary',
                    'description' => $meta['description'] ?? '',
                ];
            }
        }

        return $out;
    }

    /**
     * @return Collection<int, EmployeeEmailMapping>
     */
    protected function mappingsForChannel(string $channel, ?int $facilityId = null): Collection
    {
        return EmployeeEmailMapping::query()
            ->forCategory($channel)
            ->active()
            ->with('user:id,name,email')
            ->when($facilityId, function ($query) use ($facilityId) {
                $query->where(function ($inner) use ($facilityId) {
                    $inner->where('facility_id', $facilityId)->orWhereNull('facility_id');
                });
            })
            ->orderByDesc('is_primary')
            ->orderBy('employee_name')
            ->get();
    }

    /**
     * @param  Collection<int, EmployeeEmailMapping>  $recipients
     * @return list<string>
     */
    protected function emailsFor(Collection $recipients): array
    {
        return $recipients
            ->map(fn (EmployeeEmailMapping $r) => $r->resolvedEmail())
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array{to: list<string>, cc: list<string>, skipped_vacation: list<string>}
     */
    protected function fallbackFromConfig(string $channel): array
    {
        $email = $channel === PortalHelpRequest::TYPE_HR
            ? config('portal-help.hr_notification_email')
            : config('portal-help.support_notification_email');

        $to = filled($email) ? [strtolower(trim((string) $email))] : [];

        return [
            'to' => $to,
            'cc' => [],
            'skipped_vacation' => [],
        ];
    }
}
