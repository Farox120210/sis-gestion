<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'module',
        'user_id',
        'user_name',
        'ip_address',
        'message',
        'trace',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
