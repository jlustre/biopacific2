<?php

namespace App\Models\Concerns;

use App\Support\ContentVisibility;
use Illuminate\Database\Eloquent\Builder;

trait HasContentVisibility
{
    public function getVisibilityLabelAttribute(): string
    {
        return ContentVisibility::label($this->visibility ?? null);
    }

    public function scopeVisibleOn(Builder $query, string $channel): Builder
    {
        return $query->whereIn(
            $query->getModel()->getTable().'.visibility',
            ContentVisibility::valuesForChannel($channel)
        );
    }

    public function isVisibleOnWebsite(): bool
    {
        return in_array(
            ContentVisibility::normalize($this->visibility ?? null),
            ContentVisibility::valuesForChannel(ContentVisibility::CHANNEL_WEBSITE),
            true
        );
    }

    public function isVisibleOnPortal(): bool
    {
        return in_array(
            ContentVisibility::normalize($this->visibility ?? null),
            ContentVisibility::valuesForChannel(ContentVisibility::CHANNEL_PORTAL),
            true
        );
    }
}
