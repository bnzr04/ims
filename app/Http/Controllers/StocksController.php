<?php

namespace App\Http\Controllers;

use App\Exports\StocksExport;
use App\Models\Item;
use App\Models\Log;
use App\Models\Request as ModelsRequest;
use App\Models\Request_Item;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StocksController extends Controller
{
    //stocks excel download
    public function export()
    {
        $user = Auth::user();
        $user_type = $user->type;


        // if ($user_type === 'manager') {
        //     $user_dept = $user->dept;

        //     if ($user_dept === 'pharmacy') {
        //         $filename = 'Pharma_Stocks_' . Carbon::now()->format('Ymd-His') . '.xlsx';
        //         return Excel::download(new StocksExport($filename), $filename, \Maatwebsite\Excel\Excel::XLSX, [
        //             'Content-Type' => 'application/vnd.ms-excel',
        //         ]);
        //     } elseif (
        //         $user_dept === 'csr'
        //     ) {
        //         $filename = 'Csr_Stocks_' . Carbon::now()->format('Ymd-His') . '.xlsx';
        //         return Excel::download(new StocksExport($filename), $filename, \Maatwebsite\Excel\Excel::XLSX, [
        //             'Content-Type' => 'application/vnd.ms-excel',
        //         ]);
        //     }
        // } else {

        $filename = 'Pharma_Stocks_' . Carbon::now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new StocksExport($filename), $filename, \Maatwebsite\Excel\Excel::XLSX, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
        // }
    }

    public function stocks(Request $request)
    {
        $user = Auth::user();

        //check if user is manager
        // if ($user->type === 'manager') {
        //     //check if the manager is from pharmacy
        //     if ($user->dept === 'pharmacy') {
        //         $categories = Item::where('category', '!=', 'medical supply')->distinct('category')->pluck('category');
        //     }
        //     //check if the manager is from csr
        //     elseif ($user->dept === 'csr') {
        //         $categories = Item::where('category', 'medical supply')->distinct('category')->pluck('category');
        //     }
        // } else {
        $categories = Item::distinct('category')->pluck('category');
        // }

        //get the requested category
        $category = $request->category;


        if ($category) {
            $stocks = Item::leftjoin('item_stocks', 'items.id', '=', 'item_stocks.item_id')
                ->select(
                    'items.id',
                    'items.name',
                    'items.description',
                    'items.category',
                    DB::raw('SUM(item_stocks.stock_qty) as total_quantity'),
                    DB::raw('COUNT(item_stocks.item_id) as stocks_batch'),
                    DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as latest_stock")
                )
                ->groupBy('items.id', 'items.name', 'items.description', 'items.category')
                ->where('items.category', $category)
                ->get();
        } else {
            // if ($user->type === 'manager') {
            //     if ($user->dept === 'pharmacy') {
            //         $stocks = DB::table('item_stocks')
            //             ->join('items', 'item_stocks.item_id', '=', 'items.id')
            //             ->select('item_stocks.item_id', 'items.name', 'items.description', 'items.category', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'), DB::raw('COUNT(item_stocks.item_id) as stocks_batch'), DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as latest_stock"))
            //             ->where('items.category', '!=', 'medical supply')
            //             ->groupBy('item_stocks.item_id', 'items.name', 'items.description', 'items.category')
            //             ->get();
            //     } elseif ($user->dept === 'csr') {
            //         $stocks = DB::table('item_stocks')
            //             ->join('items', 'item_stocks.item_id', '=', 'items.id')
            //             ->select('item_stocks.item_id', 'items.name', 'items.description', 'items.category', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'), DB::raw('COUNT(item_stocks.item_id) as stocks_batch'), DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as latest_stock"))
            //             ->where('items.category', 'medical supply')
            //             ->groupBy('item_stocks.item_id', 'items.name', 'items.description', 'items.category')
            //             ->get();
            //     }
            // } else {
            $stocks = Item::leftjoin('item_stocks', 'items.id', '=', 'item_stocks.item_id')
                ->select(
                    'items.id',
                    'items.name',
                    'items.description',
                    'items.category',
                    DB::raw('SUM(item_stocks.stock_qty) as total_quantity'),
                    DB::raw('COUNT(item_stocks.item_id) as stocks_batch'),
                    DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as latest_stock")
                )
                ->groupBy('items.id', 'items.name', 'items.description', 'items.category')
                ->get();
            // }
        }

        if ($user->type === 'manager') {
            return view('manager.allStocks')->with(['stocks' => $stocks, 'categories' => $categories, 'category' => $category]);
        } else {
            return view('admin.stocks')->with(['stocks' => $stocks, 'categories' => $categories, 'category' => $category]);
        }
    }

    public function addToStocks($id)
    {
        //get authenticated user data
        $user = Auth::user();

        //get user type
        $user_type = $user->type;

        //find item by id set to request
        $item = Item::find($id);

        //if the user is manager
        if ($user_type === 'manager') {
            $user_dept = $user->dept;

            //if the manager department is pharmacy
            if ($user_dept === 'pharmacy') {

                //get stocks information by item id
                $stocks = DB::table('item_stocks')
                    ->join('items', 'item_stocks.item_id', '=', 'items.id')
                    ->select('items.*', 'item_stocks.*', DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as created_at"), DB::raw("DATE_FORMAT(MAX(item_stocks.updated_at), '%M %d, %Y, %h:%i:%s %p') as updated_at"))
                    ->where('item_stocks.item_id', $id)
                    ->groupBy('item_stocks.id', 'item_stocks.item_id', 'item_stocks.stock_qty', 'item_stocks.exp_date', 'item_stocks.mode_acquisition', 'item_stocks.created_at', 'item_stocks.updated_at', 'items.id', 'items.name', 'items.category', 'items.description', 'items.unit', 'items.created_at', 'items.updated_at',)
                    ->orderByDesc('item_stocks.created_at')
                    ->get();

                //get total stocks by item id
                $totalStocks = DB::table('item_stocks')
                    ->join('items', 'item_stocks.item_id', '=', 'items.id')
                    ->select(DB::raw('SUM(item_stocks.stock_qty) as total_stocks'))->where('item_stocks.item_id', $id)
                    ->get();

                // if ($stocks->empty()) {
                //     return abort(403, 'Item not available for you to access');
                // }
            } //if the manager department is csr
            // elseif ($user_dept === 'csr') {
            //     $stocks = DB::table('item_stocks')
            //         ->join('items', 'item_stocks.item_id', '=', 'items.id')
            //         ->select('items.*', 'item_stocks.*', DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as created_at"), DB::raw("DATE_FORMAT(MAX(item_stocks.updated_at), '%M %d, %Y, %h:%i:%s %p') as updated_at"))->where('item_stocks.item_id', $id)
            //         ->where('items.category', 'medical supply')
            //         ->groupBy('item_stocks.id', 'item_stocks.item_id', 'item_stocks.stock_qty', 'item_stocks.exp_date', 'item_stocks.created_at', 'item_stocks.updated_at', 'items.id', 'items.name', 'items.category', 'items.description', 'items.unit', 'items.created_at', 'items.updated_at',)
            //         ->orderByDesc('item_stocks.created_at')
            //         ->get();

            //     $totalStocks = DB::table('item_stocks')
            //         ->join('items', 'item_stocks.item_id', '=', 'items.id')
            //         ->select(DB::raw('SUM(item_stocks.stock_qty) as total_stocks'))->where('item_stocks.item_id', $id)
            //         ->where('items.category', 'medical supply')
            //         ->get();

            // if ($stocks->empty()) {
            //     return abort(403, 'Item not available for you to access');
            // }
            // }

            if ($stocks) {
                return view('manager.sub-page.stocks.add-to-stock')->with([
                    'item' => $item,
                    'stocks' => $stocks,
                    'total_stocks' => $totalStocks[0]->total_stocks,
                ]);
            } else {
                return view('manager.sub-page.stocks.add-to-stock')->with('item', $item);
            }
        }

        //else the user is admin
        else {
            $stocks = DB::table('item_stocks')
                ->join('items', 'item_stocks.item_id', '=', 'items.id')
                ->select('items.*', 'item_stocks.*', DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as created_at"), DB::raw("DATE_FORMAT(MAX(item_stocks.updated_at), '%M %d, %Y, %h:%i:%s %p') as updated_at"))->where('item_stocks.item_id', $id)
                ->groupBy('item_stocks.id', 'item_stocks.item_id', 'item_stocks.stock_qty', 'item_stocks.exp_date', 'item_stocks.mode_acquisition', 'item_stocks.created_at', 'item_stocks.updated_at', 'items.id', 'items.name', 'items.category', 'items.description', 'items.unit', 'items.created_at', 'items.updated_at',)
                ->orderByDesc('item_stocks.created_at')
                ->get();


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
    }

    public function saveStock(Request $request)
    {
        // Enable query logging
        DB::enableQueryLog();

        $save = new Stock;

        $save->item_id = $request->item_id;
        $save->stock_qty = $request->stock_qty;
        $save->mode_acquisition = $request->mode_acq;
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
        $stockItem = $stock->item_id;

        $user = Auth::user();
        $user_type = $user->type;

        if ($user_type === 'manager') {
            if ($stock !== null) {
                // foreach ($stock as $stock) {
                $stock->formated_created_at = Carbon::parse($stock->created_at)->format('F d, Y h:i:s A');
                $stock->formated_updated_at = Carbon::parse($stock->updated_at)->format('F d, Y h:i:s A');
                // }

                return view('manager.sub-page.stocks.add-stock')->with(['stock' => $stock, 'item' => $stockItem]);
            }
        } else {
            if ($stock !== null) {
                // foreach ($stock as $stock) {
                $stock->formated_created_at = Carbon::parse($stock->created_at)->format('F d, Y h:i:s A');
                $stock->formated_updated_at = Carbon::parse($stock->updated_at)->format('F d, Y h:i:s A');
                // }

                return view('admin.sub-page.stocks.add-stock')->with(['stock' => $stock, 'item' => $stockItem]);
            }
        }
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
        if ($operation == "return") {
            $message = "Stock ID: " . $id . ",  returned: " . $toStockQty . ", prev quantity: " . $currentStockQty . ", current quantity: " . $newStockQty;
        } else {
            $message = "Stock ID: " . $id . ",  removed: " . $toStockQty . ", prev quantity: " . $currentStockQty . ", current quantity: " . $newStockQty;
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

    ///////////////Dispense report///////////////
    public function dispense()
    {
        $user = Auth::user();

        if ($user->type === 'manager') {
            return view('manager.sub-page.stocks.dispense');
        } else {
            return view('admin.sub-page.stocks.dispense');
        }
    }

    public function getDispense()
    {
        $completedAndDeliveredId = ModelsRequest::whereIn('status', ['completed', 'delivered'])->pluck('id');

        $items = Request_Item::select('*')
            ->distinct()
            ->whereIn('request_id', $completedAndDeliveredId)
            ->get();

        return response()->json($items);
    }
}
