<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityLog extends Model
{
    use SoftDeletes;
    
    protected $table = 'logs';

    protected $fillable = [
        'action',
        'model',
        'model_id',
        'old_values',
        'new_values',
        'user_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function logActivity($action, $model, $modelId = null, $oldValues = null, $newValues = null, $userId = null)
    {
        return self::create([
            'action' => $action,
            'model' => class_basename($model),
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => $userId ?? auth()->id(),
        ]);
    }
}
