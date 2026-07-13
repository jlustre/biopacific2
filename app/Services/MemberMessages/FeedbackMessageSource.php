<?php

namespace App\Services\MemberMessages;

use App\Contracts\MemberMessageSource;
use App\Models\User;
use App\Models\WebmasterContact;
use App\Models\WebmasterContactComment;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FeedbackMessageSource implements MemberMessageSource
{
    public function key(): string
    {
        return 'feedback';
    }

    public function label(): string
    {
        return 'Feedback';
    }

    public function messagesFor(User $user): Collection
    {
        return WebmasterContact::query()
            ->where('user_id', $user->id)
            ->with(['comments' => fn ($q) => $q->latest('created_at')->limit(1)])
            ->withCount('comments')
            ->latest('updated_at')
            ->limit(30)
            ->get()
            ->map(function (WebmasterContact $contact) {
                $latestComment = $contact->comments->first();
                $isAdminReply = $latestComment
                    && ($latestComment->author_type ?? null) === WebmasterContactComment::AUTHOR_ADMIN;
                $status = ucfirst(str_replace('_', ' ', (string) $contact->status));

                $body = $isAdminReply
                    ? 'New reply: '.Str::limit((string) $latestComment->body, 140)
                    : 'Your '.$contact->categoryLabel().' submission is '.$status.'.';

                return [
                    'id' => 'feedback:'.$contact->id,
                    'source' => $this->key(),
                    'category' => $contact->categoryLabel(),
                    'title' => $contact->subject ?: 'Portal feedback',
                    'body' => $body,
                    'tone' => $isAdminReply ? 'brand' : ($contact->status === 'resolved' ? 'slate' : 'amber'),
                    'occurred_at' => $latestComment?->created_at ?? $contact->updated_at ?? $contact->created_at,
                    'route' => route('member.feedback.show', $contact),
                    'action_label' => $isAdminReply ? 'Read reply' : 'View submission',
                    'attention' => ! $contact->isResolved(),
                    'meta' => [
                        'status' => $contact->status,
                        'comments' => (int) ($contact->comments_count ?? 0),
                    ],
                ];
            });
    }
}
