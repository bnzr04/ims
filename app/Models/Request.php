<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $table = 'request';
    protected $fillable = [];

    public function items()
    {
        return $this->hasMany(Request_Item::class);
    }
}
