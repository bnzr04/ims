<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function saveItem($data)
    {
        return $this->create($data);
    }

    public function category()
    {
        //this line will join the category table to item table
        return $this->belongsTo(Category::class);
    }
}
