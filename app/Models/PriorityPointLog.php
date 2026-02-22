<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriorityPointLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixer_id',
        'delta',
        'points_before',
        'points_after',
        'reason',
        'meta',
        'performed_by',
    ];

    protected $casts = [
        'fixer_id' => 'integer',
        'delta' => 'integer',
        'points_before' => 'integer',
        'points_after' => 'integer',
        'meta' => 'array',
        'performed_by' => 'integer',
    ];

    public function fixer()
    {
        return $this->belongsTo(Fixer::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
