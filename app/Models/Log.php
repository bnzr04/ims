<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    protected $casts = [
        'bindings' => 'array',
    ];

    protected $fillable = [
        'user_id',
        'user_type',
        'message',
        'query',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
