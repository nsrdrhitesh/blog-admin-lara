<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Writes a row to activity_logs on create/update/delete.
 * Attach to any model whose changes should be auditable.
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(fn ($model) => $model->recordActivity('created'));
        static::updated(fn ($model) => $model->recordActivity('updated'));
        static::deleted(fn ($model) => $model->recordActivity('deleted'));
    }

    protected function recordActivity(string $action): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => static::class,
            'subject_id' => $this->id,
            'properties' => $action === 'updated' ? $this->getChanges() : null,
            'ip_address' => Request::ip(),
        ]);
    }
}
