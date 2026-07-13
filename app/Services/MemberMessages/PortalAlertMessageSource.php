<?php

namespace App\Services\MemberMessages;

use App\Contracts\MemberMessageSource;
use App\Models\User;
use App\Services\MemberDashboardService;
use Illuminate\Support\Collection;

class PortalAlertMessageSource implements MemberMessageSource
{
    public function __construct(
        protected MemberDashboardService $dashboard
    ) {}

    public function key(): string
    {
        return 'action';
    }

    public function label(): string
    {
        return 'Action required';
    }

    public function messagesFor(User $user): Collection
    {
        $alerts = $this->dashboard->buildPortalAlerts($user, 25);

        return collect($alerts['items'] ?? [])->values()->map(function (array $item, int $index) {
            return [
                'id' => 'action:'.$index.':'.md5(($item['title'] ?? '').'|'.($item['message'] ?? '')),
                'source' => $this->key(),
                'category' => 'Action required',
                'title' => (string) ($item['title'] ?? 'Portal notice'),
                'body' => (string) ($item['message'] ?? ''),
                'tone' => (string) ($item['tone'] ?? 'slate'),
                'occurred_at' => now(),
                'route' => $item['route'] ?? null,
                'action_label' => 'Open',
                'attention' => true,
                'meta' => [],
            ];
        });
    }
}
