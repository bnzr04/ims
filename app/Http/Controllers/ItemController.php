<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    //This will show all the saved items
    public function showAllItems(Request $request)
    {
        //This will initiate all the categories available
        $categories = Item::distinct('category')->pluck('category');

        //This will get the request from input category
        $category = $request->input('category');

        //This will get the database table item
        $items = Item::query();

        if ($category) {
            $items = $items->where('category', $category);
        }

        $items = $items->get();
        return view('admin.items', ['items' => $items, 'category' => $category, 'categories' => $categories]);
    }

    //This will view new item page
    public function newItem()
    {
        $units = Item::distinct('unit')->pluck('unit');
        $categories = Item::distinct('category')->pluck('category');
        return view("admin.sub-page.items.new-item")->with([
            'categories' => $categories,
            'units' => $units
        ]);
    }

    //This will add new item in database
    public function saveItem(Request $request)
    {
        $item = new Item;

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'category' => 'required',
            'unit' => 'required',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        };

        $item->name = $request->name;
        $item->description = $request->description;
        if ($request->category === 'other') {
            $item->category = $request->new_category;
        } else {
            $item->category = $request->category;
        }

        if ($request->unit === 'other') {
            $item->unit = $request->new_unit;
        } else {
            $item->unit = $request->unit;
        }
        $item->save();
        return back()->with('success', 'Item successfully added.');
    }

    //This will show the item depending on id
    public function showItem($id)
    {
        $item = Item::find($id);
        $categories = Item::distinct('category')->pluck('category');
        $units = Item::distinct('unit')->pluck('unit');
        return view("admin.sub-page.items.edit-item")->with([
            "item" => $item,
            'categories' => $categories,
            'units' => $units
        ]);
    }

    //This will update the details of item
    public function updateItem(Request $request, $id)
    {
        $item = Item::find($id);
        $item->name = $request->name;
        $item->description = $request->description;
        if ($request->category === 'other') {
            $item->category = $request->new_category;
        } else {
            $item->category = $request->category;
        }

        if ($request->unit === 'other') {
            $item->unit = $request->new_unit;
        } else {
            $item->unit = $request->unit;
        }
        $item->save();
        return back()->with('success', 'Details updated.');
    }

    //This will delete the item
    public function deleteItem($id)
    {
        $item = Item::find($id);
        $item->delete();
        return back();
    }
}
