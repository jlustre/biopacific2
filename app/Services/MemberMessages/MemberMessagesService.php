<?php

namespace App\Services\MemberMessages;

use App\Contracts\MemberMessageSource;
use App\Models\User;
use Illuminate\Support\Collection;

class MemberMessagesService
{
    /**
     * @param  list<MemberMessageSource>  $sources
     */
    public function __construct(
        protected array $sources = []
    ) {
        if ($this->sources === []) {
            $this->sources = [
                app(PortalAlertMessageSource::class),
                app(AssignedTaskMessageSource::class),
                app(TrainingApprovalMessageSource::class),
                app(DocumentVerificationMessageSource::class),
                app(HelpRequestMessageSource::class),
                app(FeedbackMessageSource::class),
            ];
        }
    }

    /**
     * Register an additional source at runtime (for future modules).
     */
    public function register(MemberMessageSource $source): self
    {
        $this->sources[] = $source;

        return $this;
    }

    /**
     * @return list<array{key: string, label: string, count: int}>
     */
    public function filtersFor(User $user): array
    {
        $messages = $this->allFor($user);

        $filters = [
            [
                'key' => 'all',
                'label' => 'All',
                'count' => $messages->count(),
            ],
        ];

        foreach ($this->sources as $source) {
            $count = $messages->where('source', $source->key())->count();
            $filters[] = [
                'key' => $source->key(),
                'label' => $source->label(),
                'count' => $count,
            ];
        }

        return $filters;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function allFor(User $user, ?string $source = null): Collection
    {
        $messages = collect();

        foreach ($this->sources as $messageSource) {
            $messages = $messages->merge($messageSource->messagesFor($user));
        }

        if ($source && $source !== 'all') {
            $messages = $messages->where('source', $source)->values();
        }

        return $messages
            ->sortByDesc(function (array $message) {
                $at = $message['occurred_at'] ?? null;

                return $at instanceof \DateTimeInterface ? $at->getTimestamp() : 0;
            })
            ->values();
    }

    public function countFor(User $user): int
    {
        return $this->allFor($user)->where('attention', true)->count();
    }
}
