<?php
<?php

namespace App\Traits;

use App\Services\AuditService;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            AuditService::logModelEvent($model, 'created', [], $model->toArray());
        });

        static::updated(function ($model) {
            AuditService::logModelEvent(
                $model,
                'updated',
                $model->getOriginal(),
                $model->getChanges()
            );
        });

        static::deleted(function ($model) {
            AuditService::logModelEvent($model, 'deleted', $model->toArray(), []);
        });
    }
}
