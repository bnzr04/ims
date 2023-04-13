<?php

namespace App\Http\Controllers;

use App\Exports\DispenseExport;
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
            $user_type = $user_type . " (" . $user_dept . ")";
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
        $categories = Item::distinct('category')->pluck('category');

        $search = $request->input("search");

        //get the requested category
        $category = $request->category;


        if ($category) {
            $stocks = Stock::leftjoin('items', 'item_stocks.item_id', '=', 'items.id')
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
        } elseif ($search) {
            $stocks = Stock::leftjoin('items', 'item_stocks.item_id', '=', 'items.id')
                ->select(
                    'items.id',
                    'items.name',
                    'items.description',
                    'items.category',
                    DB::raw('SUM(item_stocks.stock_qty) as total_quantity'),
                    DB::raw('COUNT(item_stocks.item_id) as stocks_batch'),
                    DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as latest_stock")
                )
                ->where(function ($query) use ($search) {
                    $query->where('items.name', 'like', "%" . $search . "%")
                        ->orWhere('item_stocks.item_id', $search);
                })
                ->groupBy('items.id', 'items.name', 'items.description', 'items.category')
                ->orderBy('name')
                ->get();
        } else {
            $stocks = Stock::leftjoin('items', 'item_stocks.item_id', '=', 'items.id')
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
        }

        if ($user->type === 'manager') {
            return view('manager.allStocks')->with(['stocks' => $stocks, 'categories' => $categories, 'category' => $category, 'search' => $search]);
        } else {
            return view('admin.stocks')->with(['stocks' => $stocks, 'categories' => $categories, 'category' => $category, 'search' => $search]);
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
        list($user_id, $user_type, $user_dept) = $this->startLog();

        $save = new Stock;

        $save->item_id = $request->item_id;
        $save->stock_qty = $request->stock_qty;
        $save->mode_acquisition = $request->mode_acq;
        $save->exp_date = $request->exp_date;
        $save->save();

        //Log Message
        $message = "New stocks batch created for ITEM ID: " . $request->item_id;

        $this->endLog($user_id, $user_type, $user_dept, $message);

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

        list($user_id, $user_type, $user_dept) = $this->startLog();

        $stock->save();

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
        $item = Stock::find($id);

        list($user_id, $user_type, $user_dept) = $this->startLog();

        $item->delete();

        //Log Message
        $message = "Stock batch dispose (id: " . $id . ")";

        $this->endLog($user_id, $user_type, $user_dept, $message);

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

        // Filter dispensed items that occurred today
        $from = Carbon::now()->startOfDay();
        $to = Carbon::now()->endOfDay();

        $data = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->select('request_items.item_id', 'items.name', 'items.description', 'items.category', 'items.unit', DB::raw('SUM(request_items.quantity) as total_dispense'))
            ->distinct()
            ->whereIn('request_id', $completedAndDeliveredId)
            ->whereBetween('request_items.updated_at', [$from, $to])
            ->groupBy('request_items.item_id', 'items.name', 'items.description', 'items.category', 'items.unit')
            ->orderBy('items.name', 'asc')
            ->get();

        return response()->json($data);
    }

    public function dispenseFilter(Request $request)
    {
        $completedAndDeliveredId = ModelsRequest::whereIn('status', ['completed', 'delivered'])->pluck('id');

        $today = $request->input('today');
        $yesterday = $request->input('yesterday');
        $thisMonth = $request->input('this-month');


        if ($today) {
            // Filter dispensed items that occurred today
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        } elseif ($yesterday) {
            // Filter dispensed items that occurred yesterday
            $from = Carbon::yesterday()->startOfDay();
            $to = Carbon::yesterday()->endOfDay();
        } elseif ($thisMonth) {
            // Filter dispensed items that occurred this month
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfMonth();
        } else {
            // Filter dispensed items that occurred from selected date
            $from = $request->input('date_from');
            $to = $request->input('date_to');
            $to = date('Y-m-d', strtotime($to . ' +1 day'));
        }


        $data = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->select('request_items.item_id', 'items.name', 'items.description', 'items.category', 'items.unit', DB::raw('SUM(request_items.quantity) as total_dispense'))
            ->distinct()
            ->whereIn('request_id', $completedAndDeliveredId)
            ->whereBetween('request_items.updated_at', [$from, $to])
            ->groupBy('request_items.item_id', 'items.name', 'items.description', 'items.category', 'items.unit')
            ->orderBy('items.name', 'asc')
            ->get();

        return response()->json($data);
    }


    public function dispenseExport(Request $request)
    {
        $req =  ucfirst($request->input('filter'));

        list($user_id, $user_type, $user_dept) = $this->startLog();

        $message = "Dispensed " . $req . " Report Downloaded";

        $filename = 'Pharma_Dispensed_Item' . Carbon::now()->format('Ymd-His') . '.xlsx';
        $response = Excel::download(new DispenseExport($filename), $filename, \Maatwebsite\Excel\Excel::XLSX, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);

        $this->endLog($user_id, $user_type, $user_dept, $message);

        return $response;
    }
}
