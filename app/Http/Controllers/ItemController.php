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
use Carbon\Carbon;

class ItemController extends Controller
{
    //Import items data
    public function import()
    {
        //Enable query log
        DB::enableQueryLog();

        $user = Auth::user();
        $user_id = $user->id;
        $user_type = $user->type;

        if ($user_type === 'manager') {
            $user_dept = $user->dept;
            $user_type = $user_type . "(" . $user_dept . ")";
        }

        //will import the file
        $import =
            Excel::import(new ItemImport, request()->file('import_item'));

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

    //Export items data
    public function export()
    {
        $user = Auth::user();
        $user_type = $user->type;


        if ($user_type === 'manager') {
            $user_dept = $user->dept;

            if ($user_dept === 'pharmacy') {
                $filename = 'Pharma_ITEMS_' . Carbon::now()->format('Ymd-His') . '.xlsx';
                return Excel::download(new ItemExport($filename), $filename, \Maatwebsite\Excel\Excel::XLSX, [
                    'Content-Type' => 'application/vnd.ms-excel',
                ]);
            } elseif ($user_dept === 'csr') {
                $filename = 'Csr_ITEMS_' . Carbon::now()->format('Ymd-His') . '.xlsx';
                return Excel::download(new ItemExport($filename), $filename, \Maatwebsite\Excel\Excel::XLSX, [
                    'Content-Type' => 'application/vnd.ms-excel',
                ]);
            }
        } else {
            $filename = 'Pharma&Csr_ITEMS_' . Carbon::now()->format('Ymd-His') . '.xlsx';
            return Excel::download(new ItemExport($filename), $filename, \Maatwebsite\Excel\Excel::XLSX, [
                'Content-Type' => 'application/vnd.ms-excel',
            ]);
        }
    }


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

        //Get authenticated user credential
        $user = Auth::user();

        //Check the user if manager type
        if ($user->type === 'manager') {
            //If the user is manager type, manager has department to assign
            //the condition will return the 
            if ($user->dept === 'pharmacy') {
                $categories = Item::where('category', '!=', 'medical supply')
                    ->distinct('category')
                    ->pluck('category');

                $items = Item::leftjoin('item_stocks', 'items.id', '=', 'item_stocks.item_id')
                    ->select('items.id', 'items.name', 'items.description', 'items.category', 'items.unit', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'))
                    ->groupBy('items.id', 'items.name', 'items.description', 'items.category', 'items.unit',)
                    ->where('items.category', '!=', 'medical supply')
                    ->orderBy('items.name');

                if ($category) {
                    $items = $items->where('category', $category)->orderBy('name');
                } else if ($search) {
                    $items = $items->where('name', 'like', "%" . $search . "%")->orderBy('name');
                }

                $items = $items->get();

                return view('manager.stocks')->with([
                    'items' => $items,
                    'categories' => $categories,
                    'category' => $category,
                    'search' => $search,
                ]);
            } elseif ($user->dept === 'csr') {

                $items = Item::leftjoin('item_stocks', 'items.id', '=', 'item_stocks.item_id')
                    ->select('items.id', 'items.name', 'items.description', 'items.category', 'items.unit', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'))
                    ->groupBy('items.id', 'items.name', 'items.description', 'items.category', 'items.unit',)
                    ->where('items.category', '=', 'medical supply')
                    ->orderBy('items.name');

                if ($category) {
                    $items = $items->where('category', $category);
                } else if ($search) {
                    $items = $items->where('name', 'like', "%" . $search . "%");
                }

                $items = $items->get();

                return view('manager.stocks')->with([
                    'items' => $items,
                    'categories' => $categories,
                    'category' => $category,
                    'search' => $search,
                ]);
            }
        }

        //This will get the all items and will know if there is a stocks or none
        //This portion will execute if the user is admin
        $items = Item::leftjoin('item_stocks', 'items.id', '=', 'item_stocks.item_id')
            ->select('items.id', 'items.name', 'items.description', 'items.category', 'items.unit', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'))
            ->groupBy('items.id', 'items.name', 'items.description', 'items.category', 'items.unit',)
            ->orderBy('items.name');

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
        $user = Auth::user();
        $user_type = $user->type;

        $units = Item::distinct('unit')->pluck('unit');

        if ($user_type === 'manager') {
            if ($user->dept === 'pharmacy') {
                $categories = Item::where('category', '!=', 'medical supply')->distinct('category')->pluck('category');
            } elseif ($user->dept === 'csr') {
                $categories = Item::where('category', '=', 'medical supply')->distinct('category')->pluck('category');
            }
            return view("manager.sub-page.items.new-item")->with([
                'categories' => $categories,
                'units' => $units
            ]);
        }

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

        $item->name = ucwords($request->name);
        $item->description = ucfirst($request->description);
        if ($request->category === 'other') {
            $item->category = ucwords($request->new_category);
        } else {
            $item->category = ucwords($request->category);
        }

        if ($request->unit === 'other') {
            $item->unit = $request->new_unit;
        } else {
            $item->unit = $request->unit;
        }
        $item->save();

        $user = auth()->user();

        $user_id = $user->id; // Get the ID of the authenticated user
        $dept = $user->dept; // Get the department if the user is manager

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

    //This will show the item depending on id
    public function showItem($id)
    {
        $item = Item::find($id);

        $units = Item::distinct('unit')->pluck('unit');
        $user = Auth::user();
        $user_type = $user->type;

        if ($user_type === 'manager') {
            $user_dept = $user->dept;

            if ($user_dept === 'pharmacy') {
                $categories = Item::where('category', '!=', 'medical supply')->distinct('category')->pluck('category');
            } elseif ($user_dept === 'csr') {
                $categories = Item::where('category', 'medical supply')->distinct('category')->pluck('category');
            }

            return view("manager.sub-page.items.edit-item")->with([
                "item" => $item,
                'categories' => $categories,
                'units' => $units
            ]);
        } else {
            $categories = Item::distinct('category')->pluck('category');
            return view("admin.sub-page.items.edit-item")->with([
                "item" => $item,
                'categories' => $categories,
                'units' => $units
            ]);
        }
    }

    //This will update the details of item
    public function updateItem(Request $request, $id)
    {
        // Enable query logging
        DB::enableQueryLog();

        $item = Item::find($id);
        $item->name = ucwords($request->name);
        $item->description = ucfirst($request->description);
        if ($request->category === 'other') {
            $item->category = ucwords($request->new_category);
        } else {
            $item->category = ucwords($request->category);
        }

        if ($request->unit === 'other') {
            $item->unit = ucwords($request->new_unit);
        } else {
            $item->unit = ucwords($request->unit);
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
