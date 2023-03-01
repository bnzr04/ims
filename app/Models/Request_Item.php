<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request_Item extends Model
{
    use HasFactory;

    protected $table = "request_items";
    protected $fillable = [];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function items()
    {
        return $this->belongsTo(Item::class);
    }
}
