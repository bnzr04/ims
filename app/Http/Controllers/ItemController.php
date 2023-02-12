<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class ItemController extends Controller
{
    //This will show all the saved items
    public function showItems()
    {
        $items = Item::all();
        return view('admin.items')->with('items', $items);
    }

    //This will add new item in database
    public function saveItem(Request $request)
    {
        $item = new Item;

        $item->item_name = $request->name;
        $item->item_description = $request->description;
        $item->category = $request->category;
        $item->item_cost = $request->cost;
        $item->item_salvage_cost = $request->salvage_cost;
        $item->item_useful_life = $request->useful_life;

        $item->save();
    }
}
