<?php

namespace App\Http\Controllers;

use App\Exports\ItemExport;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ItemImport;
use App\Models\Stock;
use Carbon\Carbon;

class ItemController extends Controller
{
    public function import() //this function will import items data or information using csv file
    {
        //Enable query log
        DB::enableQueryLog();

        $user = Auth::user(); //get the authenticated user information
        $user_id = $user->id; //get the user id
        $user_type = $user->type; //get the user type

        $import = Excel::import(new ItemImport, request()->file('import_item')); //import the file from the 'import_item' file input

        //if the import is true 
        if ($import) {
            // Get the SQL query being executed
            $sql = DB::getQueryLog();
            if (is_array($sql) && count($sql) > 0) {
                $last_query = end($sql)['query'];
            } else {
                $last_query = 'No query log found.';
            }


            //Log Message
            $message = "Items uploaded";

            // Log the data to the logs table
            Log::create([
                'user_id' => $user_id,
                'user_type' => $user_type,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            //back to route and show success message
            return back()->with(['success' => 'Item files successfully inserted']);
        } else {
            // Get the SQL query being executed
            $sql = DB::getQueryLog();
            if (is_array($sql) && count($sql) > 0) {
                $last_query = end($sql)['query'];
            } else {
                $last_query = 'No query log found.';
            }


            //Log Message
            $message = "Items upload failed";

            // Log the data to the logs table
            Log::create([
                'user_id' => $user_id,
                'user_type' => $user_type,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            //back to route and show error message
            return back()->with(['success' => 'Item uploading failed']);
        }
    }

    public function export(Request $request) //this function will export or generate a xlsx file.
    {
        //Enable query log
        DB::enableQueryLog();

        $filter = $request->input('filter'); //get the value of 'filter' input
        $date = $request->input('date'); //get the value of 'date' input

        ItemExport::$filter = $filter; //set the value of $filter in ItemExport 
        ItemExport::$date = $date; //set the value of $filter in ItemExport 

        $user = Auth::user(); //get the authencticated user information
        $user_id = $user->id; //get the user id
        $user_type = $user->type; //get the user type

        switch ($filter) {
            case 'max': //if the $filter value is 'max'
                $firstFilename = "Pharma_Items_Over_Max";
                break;
            case 'safe': //if the $filter value is 'safe'
                $firstFilename = "Pharma_Items_Safe_Level";
                break;
            case 'warning': //if the $filter value is 'warning'
                $firstFilename = "Pharma_Items_Warning_Level";
                break;
            case 'no-stocks': //if the $filter value is 'no-stocks'
                $firstFilename = "Pharma_Items_No_Stocks";
                break;
            default:
                $firstFilename = "Pharma_Items";
        }

        if ($date) {
            $formatted_date = Carbon::parse($date)->endOfDay()->format('Ymd-His');
        } else {
            $formatted_date =  Carbon::now()->format('Ymd-His');
        }

        // Get the SQL query being executed
        $sql = DB::getQueryLog();
        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
        } else {
            $last_query = 'No query log found.';
        }

        $filename = $firstFilename . $formatted_date . '.xlsx'; //set the filename as the $firstFilename value and the date today, default filename ex. Pharma_Items20231201-125900.xlsx

        //Log Message
        $message = "Downloaded a report as " . $filename;

        // Log the data to the logs table
        Log::create([
            'user_id' => $user_id,
            'user_type' => $user_type,
            'message' => $message,
            'query' => $last_query,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return Excel::download(new ItemExport(), $filename, \Maatwebsite\Excel\Excel::XLSX, [
            'Content-Type' => 'application/vnd.ms-excel', //generate the excek file
        ]);
    }


    public function showAllItems(Request $request) //this function will return items view with the items information and total stocks
    {
        //This will initiate all the categories available
        $categories = Item::distinct('category')->pluck('category');

        //This will get the request from input category
        $category = $request->input('category');

        //This will will get the request from search input
        $search = $request->input('search');

        //This will will get the request from filter input
        $filter = $request->input('filter');

        //this will get the value of 'moa' input
        $modeOfAcq = $request->input('moa');

        //Get authenticated user information
        $user = Auth::user();

        //left join the items and item_stocks and get all the items information and the total stocks of the item
        $items = Item::leftJoin('item_stocks', function ($join) {
            $join->on('items.id', '=', 'item_stocks.item_id')
                ->where('item_stocks.status', '=', 'active');
        })
            ->select(
                'items.id',
                'items.name',
                'items.description',
                'items.category',
                'items.unit',
                'items.max_limit',
                'items.warning_level',
                'items.price',
                DB::raw('COALESCE(SUM(item_stocks.stock_qty), 0) as total_quantity')
            )
            ->groupBy('items.id', 'items.name', 'items.description', 'items.category', 'items.unit', 'items.max_limit', 'items.warning_level', 'items.price')
            ->orderBy('items.name');


        if ($category) { //if the $category is true or filter the category
            $items = $items->where('category', $category); //retrieve the category by the $category value and order 
        } else if ($search) { //if the $search is true or $search has a value
            $items = $items->where('name', 'like', "%" . $search . "%"); //find the matching letter or word of items name
        } else if ($filter === 'max') { //if the $filter is true and value is equal to 'max'
            $items = $items->havingRaw('total_quantity > items.max_limit'); //select the total_quantity with greater than value to the max_limit of the item
        } else if ($filter === 'safe') { //if the $filter is true and value is equal to 'safe'
            $items = $items->havingRaw('total_quantity <= items.max_limit AND total_quantity >= (items.max_limit * (items.warning_level / 100))'); //select the total_quantity that will not exceeed to the max_limit of the item and its warning level quantity, divide the warning_level value to 100 and multiply to max_limit
        } else if ($filter === 'warning') { //if the $filter is true and value is equal to 'warning
            $items = $items->havingRaw('total_quantity < items.max_limit * (warning_level / 100)'); //select the total_quantity less than warning level quantity, divide the warning_level value to 100 and multiply to max_limit
        } else if ($filter === 'no-stocks') { //if the $filter is true and value is equal to 'no-stocks'
            $items = $items->whereNotIn('items.id', function ($query) {
                $query->select('item_id')
                    ->from('item_stocks')
                    ->groupBy('item_id')
                    ->havingRaw('SUM(stock_qty) IS NOT NULL');
            });
        } else if ($modeOfAcq === 'petty-cash') { //if $modeOfAcq value is equal to 'petty-cash'
            $items = $items->where('item_stocks.mode_acquisition', 'Petty Cash'); //select where the item_stocks mode_acquisition column with value 'Petty Cash'
        } else if ($modeOfAcq === 'donation') { //if $modeOfAcq value is equal to 'donation'
            $items = $items->where('item_stocks.mode_acquisition', 'Donation'); //select where the item_stocks mode_acquisition column with value 'Donation'
        } else if ($modeOfAcq === 'lgu') { //if $modeOfAcq value is equal to 'lgu'
            $items = $items->where('item_stocks.mode_acquisition', 'LGU'); //select where the item_stocks mode_acquisition column with value 'LGU'
        }

        $items = $items->get(); //retrieve the data

        foreach ($items as $item) {
            $hasExpiredStocks = Stock::where('item_id', $item->id)
                ->where('exp_date', '<', Carbon::now()->format('Y-m-d'))
                ->exists(); //get the item_stocks with exp_date that has expired stock batch

            $isExpiringSoon = Stock::where('item_id', $item->id)
                ->where('exp_date', '<=', Carbon::now()->addMonth()->format('Y-m-d'))
                ->exists(); //get the item_stocks with exp_date that will expired in the next month

            $item->hasExpiredStocks = $hasExpiredStocks;
            $item->isExpiringSoon = $isExpiringSoon;

            if ($item->total_quantity <= 0) {
                $item->total_quantity = null;
            }
        }

        //Check the user if manager type
        if ($user->type === 'manager') {

            return view('manager.items')->with([
                'items' => $items,
                'categories' => $categories,
                'category' => $category,
                'search' => $search,
            ]);
        } else {

            return view('admin.items', [
                'items' => $items,
                'category' => $category,
                'categories' => $categories,
                'search' => $search
            ]);
        }
    }


    public function newItem() //this function will return the new-item view module with the categories and units of the items
    {
        $user = Auth::user(); //get the authenticated user information
        $user_type = $user->type; //get the user type 

        $units = Item::distinct('unit')->pluck('unit'); //get all the items unit without duplicating its value

        $categories = Item::distinct('category')->pluck('category'); //get all the items category without duplicating its value

        if ($user_type === 'manager') {
            return view("manager.sub-page.items.new-item")->with([
                'categories' => $categories,
                'units' => $units
            ]);
        } else {
            return view("admin.sub-page.items.new-item")->with([
                'categories' => $categories,
                'units' => $units
            ]);
        }
    }


    public function saveItem(Request $request) //this function will save the new item information to items table in the database
    {
        // Enable query logging
        DB::enableQueryLog();

        $item = new Item; //get the items table

        $validator = Validator::make($request->all(), [ //requiring the input fields 
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

        $item->name = ucwords($request->name); //put the value of the new item name into the name column and make upper case every word preparing to save to items table
        $item->description = ucfirst($request->description); //put the value of the new item description into the description column and make upper case the first word preparing to save to items table
        if ($request->category === 'other') { // if the category input value is 'other'
            $item->category = ucwords($request->new_category); //put the value of the new item new_category into the category column and make upper case every word preparing to save to items table
        } else {
            $item->category = ucwords($request->category); //put the value of the new item category into the category column and make upper case every word preparing to save to items table
        }

        if ($request->unit === 'other') { // if the unit input value is 'other'
            $item->unit = $request->new_unit; //put the value of the new item new_unit into the unit column preparing to save to items table
        } else {
            $item->unit = $request->unit; //put the value of the new item unit into the unit column preparing to save to items table
        }

        $item->price = $request->price; //put the value of the new item price into the price column preparing to save to items table

        $item->max_limit = $request->filled('max_limit') ? $request->max_limit : 500; //if the max_limit input has a value, store the value of max_limit to the items max_limit column and if its empty store the value 500 as default

        $item->warning_level = $request->filled('warning_level') ? $request->warning_level : 30; //if the warning_level input has a value, store the value of warning_level to the items warning_level column and if its empty store the value 30 as default

        $item->save(); //save all the new item information to database

        $user = auth()->user(); //get the authenticated user information

        $user_id = $user->id; // Get the ID of the authenticated user

        $user_type = $user->type; //get the user type

        // Get the SQL query being executed
        $sql = DB::getQueryLog();
        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
        } else {
            $last_query = 'No query log found.';
        }

        //Log Message
        $message = "New item created. Item name: " . $item->name . ", ID: " . $item->id;

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

    public function showItem($id) //this function will return the edit-item view module that will show the information of the item and can be edit its information, the parameter is the item id.
    {
        $item = Item::find($id); //find the item by its id

        $units = Item::distinct('unit')->pluck('unit'); //get all the items unit without duplicating its value

        $categories = Item::distinct('category')->pluck('category'); //get all the items category without duplicating its value

        $user = Auth::user(); //get the authenticated user information
        $user_type = $user->type; //get the user type

        if ($user_type === 'manager') { //if the user type is manager and return to the manager edit-item view

            return view("manager.sub-page.items.edit-item")->with([
                "item" => $item,
                'categories' => $categories,
                'units' => $units
            ]);
        } else { //else the user is admin and return to the admin edit-item view

            return view("admin.sub-page.items.edit-item")->with([
                "item" => $item,
                'categories' => $categories,
                'units' => $units
            ]);
        }
    }

    public function updateItem(Request $request, $id) //this function will update the item information, the parameter $id is the item id
    {
        // Enable query logging
        DB::enableQueryLog();

        $item = Item::find($id); //find the item in items table

        $item->name = ucwords($request->name); //store the value of the new item name in the selected item 'name' column
        $item->description = ucfirst($request->description); //store the value of the new item description in the selected item 'description' column
        if ($request->category === 'other') { //if the category input value is 'other'
            $item->category = ucwords($request->new_category); //store the value of the new item category from new_category input in the selected item 'category' column
        } else {
            $item->category = ucwords($request->category); //store the value of the new item category in the selected item 'category' column
        }

        if ($request->unit === 'other') { //if the unit input value is 'other'
            $item->unit = ucwords($request->new_unit); //store the value of the new item unit from new_unit input in the selected item 'unit' column
        } else {
            $item->unit = ucwords($request->unit); //store the value of the new item unit in the selected item 'unit' column
        }

        $item->price = $request->price; //store the value of the new item price in the selected item 'price' column

        $item->max_limit = $request->max_limit; //store the value of the new item max_limit in the selected item 'max_limit' column

        $item->warning_level = $request->warning_level; //store the value of the new item warning_level in the selected item 'warning_level' column

        $item->save(); //save the updated item information

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
        $message = "Item ID: " . $item->id . ", " . $item->name .  " updated its info.";

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
