<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    /**
     * Catat log audit ke database.
     */
    public static function log(string $action, Model $model, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => $oldValues ?? ($model->wasChanged() ? array_intersect_key($model->getOriginal(), $model->getChanges()) : null),
            'new_values' => $newValues ?? $model->getChanges(),
            'ip_address' => request()->ip(),
        ]);
    }
}
