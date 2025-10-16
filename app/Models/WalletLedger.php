<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixer_id', 'delta', 'reason', 'meta',
    ];

    protected $casts = [
        'fixer_id' => 'integer',
        'delta' => 'integer',
        'meta' => 'array',
    ];

    public function fixer()
    {
        return $this->belongsTo(Fixer::class);
    }
}
