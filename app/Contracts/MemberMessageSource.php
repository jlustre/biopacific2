<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface MemberMessageSource
{
    /**
     * Stable source key used for filtering (e.g. action, task, help, feedback).
     */
    public function key(): string;

    /**
     * Human label for filter chips.
     */
    public function label(): string;

    /**
     * @return Collection<int, array{
     *     id: string,
     *     source: string,
     *     category: string,
     *     title: string,
     *     body: string,
     *     tone: string,
     *     occurred_at: ?\Illuminate\Support\Carbon,
     *     route: ?string,
     *     action_label: string,
     *     attention: bool,
     *     meta: array<string, mixed>
     * }>
     */
    public function messagesFor(User $user): Collection;
}
