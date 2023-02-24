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
        //This will get the request from input category
        $category = $request->input('category');

        //This will get the database table item
        $items = Item::query();

        if ($category) {
            $items = $items->where('category', $category);
        }

        if ($category === 'medicine') {
            $items = $items->select(
                'id',
                'name',
                'description',
                'category',
                'price',
            );
        } elseif ($category === 'medical supplies') {
            $items = $items->select(
                'id',
                'name',
                'description',
                'category',
                'price',
            );
        } else {
            $items = $items->select('*');
        }

        $items = $items->get();
        return view('admin.items', ['items' => $items, 'category' => $category]);
    }

    //This will view new item page
    public function newItem()
    {
        return view("admin.sub-page.items.new-item");
    }

    //This will add new item in database
    public function saveItem(Request $request)
    {
        $item = new Item;

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'category' => 'required',
            'price' => 'required',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        };

        $item->name = $request->name;
        $item->description = $request->description;
        $item->category = $request->category;
        $item->price = $request->price;
        $item->save();
        return back()->with('success', 'Item successfully added.');
    }

    //This will show the item depending on id
    public function showItem($id)
    {
        $item = Item::find($id);
        return view("admin.sub-page.items.edit-item")->with("item", $item);
    }

    //This will update the details of item
    public function updateItem(Request $request, $id)
    {
        $item = Item::find($id);
        $item->name = $request->name;
        $item->description = $request->description;
        $item->category = $request->category;
        $item->price = $request->price;
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
