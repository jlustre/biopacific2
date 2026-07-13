<?php

namespace App\Services\MemberMessages;

use App\Contracts\MemberMessageSource;
use App\Models\PortalHelpRequest;
use App\Models\User;
use App\Services\PortalHelpRecipientService;
use App\Support\MemberPortalLayout;
use Illuminate\Support\Collection;

class HelpRequestMessageSource implements MemberMessageSource
{
    public function __construct(
        protected PortalHelpRecipientService $recipients
    ) {}

    public function key(): string
    {
        return 'help';
    }

    public function label(): string
    {
        return 'Help requests';
    }

    public function messagesFor(User $user): Collection
    {
        $channels = $this->recipients->channelsForUser($user);
        $canAdmin = MemberPortalLayout::userIsSystemAdmin($user) || $user->hasRole('rdhr');

        return PortalHelpRequest::query()
            ->where(function ($query) use ($user, $channels) {
                $query->where('user_id', $user->id);
                if ($channels !== []) {
                    $query->orWhereIn('type', $channels);
                }
            })
            ->latest('updated_at')
            ->limit(50)
            ->get()
            ->map(function (PortalHelpRequest $request) use ($user, $canAdmin) {
                $isOwner = (int) $request->user_id === (int) $user->id;
                $status = ucfirst(str_replace('_', ' ', (string) $request->status));
                $isResolved = $request->isResolved();

                if ($isOwner) {
                    $body = $isResolved
                        ? 'Your '.$request->typeLabel().' was marked resolved.'
                        : 'Status: '.$status.'. We will follow up using your preferred contact method.';
                    $route = route('member.help.show', $request);
                    $action = 'View request';
                } else {
                    $body = $isResolved
                        ? 'Team '.$request->typeLabel().' from '.$request->name.' was resolved.'
                        : 'New team '.$request->typeLabel().' from '.$request->name.' · '.$status;
                    $route = $canAdmin
                        ? route('admin.portal-help-requests.show', $request)
                        : route('member.help.show', $request);
                    $action = 'Open inbox item';
                }

                return [
                    'id' => 'help:'.$request->id.($isOwner ? ':own' : ':team'),
                    'source' => $this->key(),
                    'category' => $request->typeLabel(),
                    'title' => $request->subject ?: $request->categoryLabel(),
                    'body' => $body,
                    'tone' => $isResolved ? 'brand' : ($request->priority === 'urgent' ? 'rose' : 'amber'),
                    'occurred_at' => $request->updated_at ?? $request->created_at,
                    'route' => $route,
                    'action_label' => $action,
                    'attention' => ! $isResolved,
                    'meta' => [
                        'status' => $request->status,
                        'team' => ! $isOwner,
                    ],
                ];
            });
    }
}
