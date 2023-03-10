<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    //
    //This will show all the saved items
    //
    public function showAllItems(Request $request)
    {
        //This will initiate all the categories available
        $categories = Item::distinct('category')->pluck('category');

        //This will get the request from input category
        $category = $request->input('category');

        //This will will get the request from search input
        $search = $request->input('search');



        //This will get the all items and will know if there is a stocks or none
        $items = DB::table('items')
            ->leftjoin('item_stocks', 'items.id', '=', 'item_stocks.item_id')
            ->select('items.id', 'items.name', 'items.description', 'items.category', 'items.unit', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'))
            ->groupBy('items.id', 'items.name', 'items.description', 'items.category', 'items.unit',);

        if ($category) {
            $items = $items->where('category', $category);
        } else if ($search) {
            $items = $items->where('name', 'like', "%" . $search . "%");
        }
        $items = $items->get();

        return view('admin.items', ['items' => $items, 'category' => $category, 'categories' => $categories, 'search' => $search]);
    }

    //
    //This will view new item page
    //

    public function newItem()
    {
        $units = Item::distinct('unit')->pluck('unit');
        $categories = Item::distinct('category')->pluck('category');


        return view("admin.sub-page.items.new-item")->with([
            'categories' => $categories,
            'units' => $units
        ]);
    }

    //
    //This will add new item in database
    //
    public function saveItem(Request $request)
    {
        // Enable query logging
        DB::enableQueryLog();
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

        //QUERY LOG
        $user = auth()->user();

        $user_id = $user->id; // Get the ID of the authenticated user
        $dept = $user->dept; // Get the depart if the user is manager

        if ($user->type === "manager") {
            $user_type = $user->type . " (" . $dept . ")"; // Get the dept of the authenticated manager
        } else {
            $user_type = $user->type;
        }

        // Get the SQL query being executed
        $sql = DB::getQueryLog();
        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
        } else {
            $last_query = 'No query log found.';
        }

        //Log Message
        $message = "Item inserted.";

        // Log the data to the logs table
        Log::create([
            'user_id' => $user_id,
            'user_type' => $user_type,
            'message' => $message,
            'query' => $last_query,
            'created_at' => now(),
            'updated_at' => now()
        ]);

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
        // Enable query logging
        DB::enableQueryLog();

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

        //QUERY LOG
        $user_id = Auth::id(); // Get the ID of the authenticated user
        $user_type = Auth::user()->type; // Get the Type of the authenticated user

        // Get the SQL query being executed
        $sql = DB::getQueryLog();
        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
        } else {
            $last_query = 'No query log found.';
        }

        //Log Message
        $message = "Item updated.";

        // Log the data to the logs table
        Log::create([
            'user_id' => $user_id,
            'user_type' => $user_type,
            'message' => $message,
            'query' => $last_query,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Details updated.');
    }
}
