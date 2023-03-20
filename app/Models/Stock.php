<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;
    protected $table = "item_stocks";
    protected $fillable = ['stock_qty'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
