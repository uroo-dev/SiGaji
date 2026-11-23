<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    /**
     * Catat log audit aktivitas ke database.
     */
    public static function log(string $action, ?Model $model = null, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        $userId = auth()->id();
        $modelType = $model ? get_class($model) : 'System';
        $modelId = $model ? ($model->getKey() ?? 0) : 0;

        if ($newValues === null && $model && method_exists($model, 'getChanges')) {
            $newValues = $model->getChanges();
        }

        $newValues = $newValues ?? [];

        if (!isset($newValues['time'])) {
            $newValues['time'] = now()->toDateTimeString();
        }

        return AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip() ?? '127.0.0.1',
        ]);
    }
}
