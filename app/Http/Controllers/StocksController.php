<?php

namespace App\Http\Controllers;

use App\Exports\DispenseExport;
use App\Exports\StocksExport;
use App\Models\Item;
use App\Models\Log;
use App\Models\Request as ModelsRequest;
use App\Models\Request_Item;
use App\Models\Stock;
use App\Models\Stock_Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StocksController extends Controller
{
    //////////LOG//////////

    /////Log first part//////////
    public function startLog()
    {
        DB::enableQueryLog();

        $user = Auth::user();
        $user_id = $user->id;
        $user_type = $user->type;
        if ($user_type === 'manager') {
            $user_dept = $user->dept;
        } else {
            $user_dept = "";
        }

        return [$user_id, $user_type, $user_dept];
    }

    /////Log last part//////////
    public function endLog($user_id, $user_type, $user_dept, $message)
    {
        if ($user_type === 'manager') {
            $user_type = $user_type;
        }

        // Get the SQL query being executed
        $sql = DB::getQueryLog();
        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
        } else {
            $last_query = 'No query log found.';
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
    }

    //////////LOG END////////////

    //stocks excel download
    public function export()
    {
        list($user_id, $user_type, $user_dept) = $this->startLog();

        $message = "Stock Report Downloaded";

        $filename = 'Pharma_Stocks_' . Carbon::now()->format('Ymd-His') . '.xlsx';
        $response = Excel::download(new StocksExport($filename), $filename, \Maatwebsite\Excel\Excel::XLSX, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);

        $this->endLog($user_id, $user_type, $user_dept, $message);

        return $response;
    }

    public function stocks(Request $request)
    {
        $user = Auth::user();

        //This will initiate all the categories available
        $categories = Item::distinct('category')->pluck('category');

        //This will will get the request from search input
        $search = $request->input("search");

        //This will will get the request from filter input
        $filter = $request->input('filter');

        //get the requested category
        $category = $request->category;

        $stocks = Stock::leftjoin('items', 'item_stocks.item_id', '=', 'items.id')
            ->select(
                'items.id',
                'items.name',
                'items.description',
                'items.category',
                'items.unit',
                'items.max_limit',
                'items.warning_level',
                DB::raw('SUM(item_stocks.stock_qty) as total_quantity'),
                DB::raw('COUNT(item_stocks.item_id) as stocks_batch'),
                DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as latest_stock")
            )
            ->groupBy(
                'items.id',
                'items.name',
                'items.description',
                'items.category',
                'items.unit',
                'items.max_limit',
                'items.warning_level'
            )
            ->orderBy('name');

        if ($category) {
            $stocks = Stock::leftjoin('items', 'item_stocks.item_id', '=', 'items.id')
                ->select(
                    'items.id',
                    'items.name',
                    'items.description',
                    'items.category',
                    'items.unit',
                    'items.max_limit',
                    'items.warning_level',
                    DB::raw('SUM(item_stocks.stock_qty) as total_quantity'),
                    DB::raw('COUNT(item_stocks.item_id) as stocks_batch'),
                    DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as latest_stock")
                )
                ->groupBy(
                    'items.id',
                    'items.name',
                    'items.description',
                    'items.category',
                    'items.unit',
                    'items.max_limit',
                    'items.warning_level',
                )
                ->where('items.category', $category)
                ->orderBy('name');
        } else if ($search) {
            $stocks = Stock::leftjoin('items', 'item_stocks.item_id', '=', 'items.id')
                ->select(
                    'items.id',
                    'items.name',
                    'items.description',
                    'items.category',
                    'items.unit',
                    'items.max_limit',
                    'items.warning_level',
                    DB::raw('SUM(item_stocks.stock_qty) as total_quantity'),
                    DB::raw('COUNT(item_stocks.item_id) as stocks_batch'),
                    DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as latest_stock")
                )
                ->where(function ($query) use ($search) {
                    $query->where('items.name', 'like', "%" . $search . "%")
                        ->orWhere('item_stocks.item_id', $search);
                })
                ->groupBy(
                    'items.id',
                    'items.name',
                    'items.description',
                    'items.category',
                    'items.unit',
                    'items.max_limit',
                    'items.warning_level',
                )
                ->orderBy('name');
        } else if ($filter === 'max') {
            $stocks = $stocks->havingRaw('total_quantity > items.max_limit')->orderBy('items.name');
        } else if ($filter === 'max') {
            $stocks = $stocks->havingRaw('total_quantity > items.max_limit')->orderBy('items.name');
        } else if ($filter === 'safe') {
            $stocks = $stocks->havingRaw('total_quantity <= items.max_limit AND total_quantity >= (items.max_limit * (items.warning_level / 100))')->orderBy('items.name');
        } else if ($filter === 'warning') {
            $stocks = $stocks->havingRaw('total_quantity < items.max_limit * (warning_level / 100)')->orderBy('items.name');
        } else if ($filter === 'no-stocks') {
            $stocks = $stocks->whereNotIn('items.id', function ($query) {
                $query->select('item_id')
                    ->from('item_stocks')
                    ->groupBy('item_id')
                    ->havingRaw('SUM(stock_qty) IS NOT NULL');
            })
                ->orderBy('items.name');
        }

        $stocks = $stocks->get();

        foreach ($stocks as $item) {
            $hasExpiredStocks = Stock::where('item_id', $item->id)
                ->where('exp_date', '<', Carbon::now()->format('Y-m-d'))
                ->exists();

            $isExpiringSoon = Stock::where('item_id', $item->id)
                ->where('exp_date', '<=', Carbon::now()->addMonth()->format('Y-m-d'))
                ->exists();

            $item->hasExpiredStocks = $hasExpiredStocks;
            $item->isExpiringSoon = $isExpiringSoon;
        }

        if ($user->type === 'manager') {
            return view('manager.allStocks')->with(['stocks' => $stocks, 'categories' => $categories, 'category' => $category, 'search' => $search]);
        } else {
            return view('admin.stocks')->with(['stocks' => $stocks, 'categories' => $categories, 'category' => $category, 'search' => $search]);
        }
    }

    public function addToStocks(Request $request, $id)
    {
        $pettyCash = $request->input("petty-cash");
        $donation = $request->input("donation");
        $lgu = $request->input("lgu");

        //get authenticated user data
        $user = Auth::user();

        //get user type
        $user_type = $user->type;

        //find item by id set to request
        $item = Item::find($id);

        $totalStocks = DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->select(DB::raw('SUM(item_stocks.stock_qty) as total_stocks'))
            ->where('item_stocks.item_id', $id)
            ->get();

        $stocks = DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->select(
                'items.*',
                'item_stocks.*',
                DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as created_at"),
                DB::raw("DATE_FORMAT(MAX(item_stocks.updated_at), '%M %d, %Y, %h:%i:%s %p') as updated_at")
            )
            ->where('item_stocks.item_id', $id)
            ->groupBy(
                'item_stocks.id',
                'item_stocks.item_id',
                'item_stocks.stock_qty',
                'item_stocks.exp_date',
                'item_stocks.mode_acquisition',
                'item_stocks.created_at',
                'item_stocks.updated_at',
                'items.id',
                'items.name',
                'items.category',
                'items.description',
                'items.unit',
                'items.max_limit',
                'items.warning_level',
                'items.price',
                'items.created_at',
                'items.updated_at'
            )
            ->orderByDesc('item_stocks.created_at');

        if ($pettyCash) {
            $stocks->where('item_stocks.mode_acquisition', '=', 'Petty Cash');
        } else if ($donation) {
            $stocks->where('item_stocks.mode_acquisition', '=', 'Donation');
        } else if ($lgu) {
            $stocks->where('item_stocks.mode_acquisition', '=', 'LGU');
        }

        $stocks = $stocks->get(); // Retrieve the query results

        foreach ($stocks as $stock) {
            $stock->exp_date = Carbon::parse($stock->exp_date)->format('Y-m-d');
        }

        //if the user is manager
        if ($user_type === 'manager') {

            //get total stocks by item id
            // $totalStocks = DB::table('item_stocks')
            //     ->join('items', 'item_stocks.item_id', '=', 'items.id')
            //     ->select(DB::raw('SUM(item_stocks.stock_qty) as total_stocks'))->where('item_stocks.item_id', $id)
            //     ->get();

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
        list($user_id, $user_type, $user_dept) = $this->startLog();

        $save = new Stock;

        $save->item_id = $request->item_id;
        $save->stock_qty = $request->stock_qty;
        $save->mode_acquisition = $request->mode_acq;
        $save->exp_date = $request->exp_date;
        $save->save();

        $stock_log = new Stock_Log();
        $stock_log->stock_id = $save->id;
        $stock_log->item_id = $save->item_id;
        $stock_log->quantity = $save->stock_qty;
        $stock_log->mode_acquisition = $save->mode_acquisition;
        $stock_log->transaction_type = 'addition';
        $stock_log->save();

        //Log Message
        $message = "New stocks batch created for ITEM ID: " . $request->item_id;

        $this->endLog($user_id, $user_type, $user_dept, $message);

        return back()->with('success', 'Item is successfully added to stocks.');
    }

    public function editStock($id)
    {
        $stock = Stock::find($id);
        $stockItem = $stock->item_id;

        $items = Item::find($stock->item_id);

        $stock->formated_created_at = Carbon::parse($stock->created_at)->format('F d, Y h:i:s A');
        $stock->formated_updated_at = Carbon::parse($stock->updated_at)->format('F d, Y h:i:s A');

        return view('admin.sub-page.stocks.edit-stock')->with(['stock' => $stock, 'item' => $items]);
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

    public function adminUpdateStock(Request $request)
    {
        $stockId = $request->input('stock_id');
        $stockQty = $request->input('stock_qty');

        $stock = Stock::find($stockId);

        $itemId = $stock->item_id;
        $oldQty = $stock->stock_qty;

        if ($stock) {
            $newQty = $stockQty;
            $stock->stock_qty = $newQty;

            list($user_id, $user_type, $user_dept) = $this->startLog();

            if ($stock->save()) {

                $stock_log = new Stock_Log();
                $stock_log->stock_id = $stock->id;
                $stock_log->item_id = $stock->item_id;
                $stock_log->quantity = $oldQty < $newQty ? $newQty - $oldQty : $oldQty - $newQty;
                $stock_log->mode_acquisition = $stock->mode_acquisition;
                $stock_log->transaction_type = $newQty > $oldQty ? 'addition' : 'deduction';
                $stock_log->save();

                $message = "Stock batch id " . $stockId . " of Item id " . $itemId . " was updated the quantity from " . $oldQty . " to " . $newQty;

                $this->endLog($user_id, $user_type, $user_dept, $message);

                return response()->json([
                    'success' => 'Stock quantity updated successfully',
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to update stock quantity',
                ]);
            }
        } else {
            return response()->json([
                'error' => 'Stock batch cannot found.',
            ]);
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

        list($user_id, $user_type, $user_dept) = $this->startLog();

        $stock->save();

        $stock_log = new Stock_Log();
        $stock_log->stock_id = $stock->id;
        $stock_log->item_id = $stock->item_id;
        $stock_log->quantity = $toStockQty;
        $stock_log->mode_acquisition = $stock->mode_acquisition;
        $stock_log->transaction_type = $operation == 'remove' ? 'deduction' : 'addition';
        $stock_log->save();

        //Log Message
        if ($operation == "return") {
            $message = "Stock ID: " . $id . ",  returned: " . $toStockQty . ", prev quantity: " . $currentStockQty . ", current quantity: " . $newStockQty;
        } else {
            $message = "Stock ID: " . $id . ",  removed: " . $toStockQty . ", prev quantity: " . $currentStockQty . ", current quantity: " . $newStockQty;
        }

        $this->endLog($user_id, $user_type, $user_dept, $message);

        return back()->with('success', 'Stock Successfully Updated');
    }

    public function deleteStock($id)
    {
        $stock = Stock::find($id);

        list($user_id, $user_type, $user_dept) = $this->startLog();

        $stock->delete();

        $stock_log = new Stock_Log();
        $stock_log->stock_id = $stock->id;
        $stock_log->item_id = $stock->item_id;
        $stock_log->quantity = $stock->stock_qty;
        $stock_log->mode_acquisition = $stock->mode_acquisition;
        $stock_log->transaction_type = 'deduction';
        $stock_log->save();

        //Log Message
        $message = "Stock batch dispose (id: " . $id . ")";

        $this->endLog($user_id, $user_type, $user_dept, $message);

        if ($stock) {
            return back()->with('success', 'Stock successfully deleted.');
        } else {
            return back()->with('error', 'Stock failed to delete.');
        }
    }
}
