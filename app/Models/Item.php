<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $fillable = [
        "item_name", "item_description", "category", "item_cost", "item_salvage_cost", "item_useful_life",
    ];
}
