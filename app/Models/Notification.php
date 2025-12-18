<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class Notification extends Model
{
    use HasFactory;
    use Prunable;

    protected $fillable = [
        'recipient_type',
        'user_id',
        'title',
        'message',
        'read',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Prune notifications older than 7 days.
     */
    public function prunable()
    {
        return static::where('created_at', '<', now()->subDays(7));
    }
}
