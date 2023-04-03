<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request_Item extends Model
{
    use HasFactory;

    protected $table = "request_items";

    protected $fillable = [
        'request_id', 'item_id', 'stock_id', 'exp_date', 'quantity'
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function items()
    {
        return $this->belongsTo(Item::class);
    }
}
