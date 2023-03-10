<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Log;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function GuzzleHttp\Promise\all;

class StocksController extends Controller
{
    public function stocks(Request $request)
    {
        $categories = Item::distinct('category')->pluck('category');
        $category = $request->category;

        if ($category) {
            $stocks = DB::table('item_stocks')
                ->join('items', 'item_stocks.item_id', '=', 'items.id')
                ->select('item_stocks.item_id', 'items.name', 'items.description', 'items.category', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'), DB::raw('COUNT(item_stocks.item_id) as stocks_batch'), DB::raw('MAX(item_stocks.created_at) as latest_stock'))
                ->groupBy('item_stocks.item_id', 'items.name', 'items.description', 'items.category')
                ->where('items.category', $category)
                ->get();
        } else {
            $stocks = DB::table('item_stocks')
                ->join('items', 'item_stocks.item_id', '=', 'items.id')
                ->select('item_stocks.item_id', 'items.name', 'items.description', 'items.category', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'), DB::raw('COUNT(item_stocks.item_id) as stocks_batch'), DB::raw('MAX(item_stocks.created_at) as latest_stock'))
                ->groupBy('item_stocks.item_id', 'items.name', 'items.description', 'items.category')
                ->get();
        }

        return view('admin.stocks')->with(['stocks' => $stocks, 'categories' => $categories, 'category' => $category]);
    }

    public function addToStocks($id)
    {
        $item = Item::find($id);
        $stocks = DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->select('items.*', 'item_stocks.*')->where('item_stocks.item_id', $id)->get();

        $totalStocks = DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->select(DB::raw('SUM(item_stocks.stock_qty) as total_stocks'))->where('item_stocks.item_id', $id)->get();

        if ($stocks) {
            return view('admin.sub-page.stocks.add-to-stock')->with([
                'item' => $item,
                'stocks' => $stocks,
                'total_stocks' => $totalStocks[0]->total_stocks,
            ]);
        } else {
            return view('admin.sub-page.stocks.add-to-stock')->with('item', $item);
        }
    }

    public function saveStock(Request $request)
    {
        // Enable query logging
        DB::enableQueryLog();

        $save = new Stock;

        $save->item_id = $request->item_id;
        $save->stock_qty = $request->stock_qty;
        $save->exp_date = $request->exp_date;
        $save->save();

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
        $message = "New stocks batch created for ITEM ID: " . $request->item_id;

        // Log the data to the logs table
        Log::create([
            'user_id' => $user_id,
            'user_type' => $user_type,
            'message' => $message,
            'query' => $last_query,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Item is successfully added to stocks.');
    }

    public function editStock($id)
    {
        return view('admin.sub-page.stocks.edit-stock');
    }

    public function addStock($id)
    {
        $stock = Stock::find($id);
        $item = Item::find($id);
        $stockItem = $stock->item_id;

        if ($stock !== null) {
            return view('admin.sub-page.stocks.add-stock')->with(['stock' => $stock, 'item' => $stockItem]);
        }

        return view('admin.sub-page.stocks.add-stock')->with(['item' => $item]);
    }

    public function updateStock(Request $request, $id)
    {
        $stock = Stock::find($id);

        $operation = $request->operation;
        $currentStockQty = $stock->stock_qty;
        $toStockQty = $request->new_stock;
        if ($operation == 'remove') {
            $newStockQty = $currentStockQty - $toStockQty;
        } else {
            $newStockQty = $currentStockQty + $toStockQty;
        }

        $stock->stock_qty = $newStockQty;

        // Enable query logging
        DB::enableQueryLog();

        $stock->save();


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
        if ($operation == "add") {
            $message = "Stock batch (id:" . $id . ") updated (+ " . $toStockQty . ")";
        } else {
            $message = "Stock batch (id:" . $id . ") updated (- " . $toStockQty . ")";
        }


        // Log the data to the logs table
        Log::create([
            'user_id' => $user_id,
            'user_type' => $user_type,
            'message' => $message,
            'query' => $last_query,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Stock Successfully Updated');
    }

    public function deleteStock($id)
    {
        $item = Stock::find($id);

        // Enable query logging
        DB::enableQueryLog();

        $item->delete();


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
        $message = "Stock batch dispose (id: " . $id . ")";

        // Log the data to the logs table
        Log::create([
            'user_id' => $user_id,
            'user_type' => $user_type,
            'message' => $message,
            'query' => $last_query,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if ($item) {
            return back()->with('success', 'Stock successfully deleted.');
        } else {
            return back()->with('error', 'Stock failed to delete.');
        }
    }
}
