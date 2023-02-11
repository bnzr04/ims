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
        $model = new Item;
        $model->item_name = $request->itemName;
        $model->item_description = $request->itemDescription;
        $model->category = $request->category;
        $model->item_cost = $request->cost;
        $model->item_salvage_cost = $request->salvageCost;
        $model->item_useful_life = $request->usefulLife;
        $model->save();
    }
}
