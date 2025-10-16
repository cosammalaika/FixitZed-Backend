<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type', // user|fixer|other
        'subject',
        'message',
        'target_user_id',
        'status', // open|reviewed|action_taken|closed
        'action', // none|warn|suspend|ban
        'resolved_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'target_user_id' => 'integer',
        'resolved_at' => 'datetime',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
