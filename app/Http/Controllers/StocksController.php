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


    public function export() //this function will generate excel file of all the stock batches of the items
    {
        list($user_id, $user_type, $user_dept) = $this->startLog(); //start log

        $filename = 'Pharma_Stocks_' . Carbon::now()->format('Ymd-His') . '.xlsx'; //set the filename 'Pharma_Stocks_' after is the date today, default filename ex. Pharma_Stocks_20231201-125900.xlsx

        //this will generate stocks excel file
        $response = Excel::download(new StocksExport($filename), $filename, \Maatwebsite\Excel\Excel::XLSX, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);

        $message = "Stock Report Downloaded"; //log message

        $this->endLog($user_id, $user_type, $user_dept, $message); //end part of log

        return $response;
    }

    public function stocks(Request $request) //this function will return stocks view with the item and stocks information
    {
        $user = Auth::user(); //get the authenticated user information

        //This will initiate all the categories available
        $categories = Item::distinct('category')->pluck('category');

        //This will will get the request from search input
        $search = $request->input("search");

        //This will will get the request from filter input
        $filter = $request->input('filter');

        //get the requested category
        $category = $request->category;

        $stocks = Stock::leftjoin('items', 'item_stocks.item_id', '=', 'items.id') //the item_stocks and items table is joined to get all items that has stocks in item_stocks table
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
            ->where('item_stocks.stock_qty', '>', 0)
            ->where('item_stocks.status', 'active')
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

        if ($category) { //if $category is true
            $stocks = $stocks->where('items.category', $category);
        } else if ($search) { //if $search is true
            $stocks = $stocks->where(function ($query) use ($search) {
                $query->where('items.name', 'like', "%" . $search . "%") //find the item name that match the value of $search
                    ->orWhere('item_stocks.item_id', $search); //find the item id that match the value of $search
            });
        } else if ($filter === 'max') { //if $filter value is 'max'
            $stocks = $stocks->havingRaw('total_quantity > items.max_limit')->orderBy('items.name');
        } else if ($filter === 'safe') { //if $filter value is 'safe'
            $stocks = $stocks->havingRaw('total_quantity <= items.max_limit AND total_quantity >= (items.max_limit * (items.warning_level / 100))')->orderBy('items.name');
        } else if ($filter === 'warning') { //if $filter value is 'warning'
            $stocks = $stocks->havingRaw('total_quantity < items.max_limit * (warning_level / 100)')->orderBy('items.name');
        } else if ($filter === 'no-stocks') { //if $filter value is 'no-stocks'
            $stocks = $stocks->whereNotIn('items.id', function ($query) {
                $query->select('item_id')
                    ->from('item_stocks')
                    ->groupBy('item_id')
                    ->havingRaw('SUM(stock_qty) IS NOT NULL');
            });
        }

        $stocks = $stocks->get(); //retrieve the data

        foreach ($stocks as $item) {
            $hasExpiredStocks = Stock::where('item_id', $item->id)
                ->where('exp_date', '<', Carbon::now()->format('Y-m-d'))
                ->exists(); //get the item_stocks with exp_date that has expired stock batch

            $isExpiringSoon = Stock::where('item_id', $item->id)
                ->where('exp_date', '<=', Carbon::now()->addMonth()->format('Y-m-d'))
                ->exists(); //get the item_stocks with exp_date that will expired in the next month

            $item->hasExpiredStocks = $hasExpiredStocks;
            $item->isExpiringSoon = $isExpiringSoon;
        }

        if ($user->type === 'manager') {
            return view('manager.stocks')->with(['stocks' => $stocks, 'categories' => $categories, 'category' => $category, 'search' => $search]);
        } else {
            return view('admin.stocks')->with(['stocks' => $stocks, 'categories' => $categories, 'category' => $category, 'search' => $search]);
        }
    }

    public function addToStocks(Request $request, $id) //return the add-to-stock view and show all the stock batch of the selected item, the parameter $id is the item id
    {
        $pettyCash = $request->input("petty-cash"); //get the 'petty-cash' input
        $donation = $request->input("donation"); //get the 'donation' input
        $lgu = $request->input("lgu"); //get the 'lgu' input

        //get authenticated user data
        $user = Auth::user();

        //get user type
        $user_type = $user->type;

        //find item by id set to request
        $item = Item::find($id);

        //this will get the total stocks of the item
        $totalStocks = DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->select(DB::raw('SUM(item_stocks.stock_qty) as total_stocks'))
            ->where('item_stocks.item_id', $id)
            ->where('item_stocks.stock_qty', '>', 0)
            ->where('item_stocks.status', 'active')
            ->get();

        //join the item_stocks and items table to get the stocks of the item
        $stocks = DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->select(
                'items.*',
                'item_stocks.*',
                DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as created_at"),
                DB::raw("DATE_FORMAT(MAX(item_stocks.updated_at), '%M %d, %Y, %h:%i:%s %p') as updated_at")
            )
            ->where('item_stocks.item_id', $id)
            ->where('item_stocks.stock_qty', '>', 0)
            ->where('item_stocks.status', 'active')
            ->groupBy(
                'item_stocks.id',
                'item_stocks.item_id',
                'item_stocks.stock_qty',
                'item_stocks.exp_date',
                'item_stocks.mode_acquisition',
                'item_stocks.lot_number',
                'item_stocks.block_number',
                'item_stocks.status',
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

        if ($pettyCash) { //if the $pettyCash is true retrieve all the stock batch with mode_acquisition = "Petty Cash"
            $stocks->where('item_stocks.mode_acquisition', '=', 'Petty Cash');
        } else if ($donation) { //if the $donation is true retrieve all the stock batch with mode_acquisition = "Donation"
            $stocks->where('item_stocks.mode_acquisition', '=', 'Donation');
        } else if ($lgu) { //if the $lgu is true retrieve all the stock batch with mode_acquisition = "LGU"
            $stocks->where('item_stocks.mode_acquisition', '=', 'LGU');
        }

        $stocks = $stocks->get(); // Retrieve the query results

        foreach ($stocks as $stock) {
            $stock->exp_date = Carbon::parse($stock->exp_date)->format('Y-m-d'); //format the exp_date to 'Year-month-Day' format.
        }

        //if the user is manager
        if ($user_type === 'manager') {

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

    public function saveStock(Request $request) //this function will save the new stock batch
    {
        list($user_id, $user_type, $user_dept) = $this->startLog(); //the first part of the log

        $oldStockQuantity = Stock::where('item_id', $request->item_id)
            ->where('status', 'active') // select the stock batch with status 'active'
            ->sum('stock_qty'); //store the sum of the previous stock quantity if the item

        $stock = new Stock; //get the stock table
        $stock->item_id = $request->item_id; //store the item_id to the item_stock item_id column
        $stock->stock_qty = $request->stock_qty; //store the stock_qty to the item_stock stock_qty column
        $stock->mode_acquisition = $request->mode_acq; //store the mode_acq to the item_stock mode_acquisition column
        $stock->lot_number = $request->lot_num; //store the lot_num value to the item_stocks lot_number column
        $stock->block_number = $request->block_num; //store the block_num value to the item_stocks block_num column
        $stock->exp_date = $request->exp_date; //store the exp_date to the item_stock exp_date column
        $stock->save(); //save the new stock batch 

        //Log Message
        $message = "New stocks batch created for ITEM ID: " . $request->item_id;

        $this->endLog($user_id, $user_type, $user_dept, $message); //end of log

        $currentStockQuantity = Stock::where('item_id', $request->item_id)
            ->where('status', 'active') // select the stock batch with status 'active'
            ->sum('stock_qty'); //store the sum of the previous stock quantity if the item

        $stock_log = new Stock_Log(); //get the stock_logs table
        $stock_log->stock_id = $stock->id; //store the stock id to stock_logs stock_id column
        $stock_log->item_id = $stock->item_id; //store the item_id to stock_logs item_id column
        $stock_log->quantity = $stock->stock_qty; //store the stock_qty to stock_logs quantity column
        $stock_log->mode_acquisition = $stock->mode_acquisition; //store the mode_acquisition to stock_logs mode_acquisition column
        $stock_log->transaction_type = 'new-stock'; //store the 'new-stock' to stock_logs transaction_type column
        $stock_log->current_quantity = $currentStockQuantity;
        $stock_log->prev_quantity = $oldStockQuantity;
        $stock_log->save(); //save the stock log


        return back()->with('success', 'Item is successfully added to stocks.');
    }

    public function editStock($id) //return the edit-stock view with the information of the of the stock batch and items information, the parameter $id is the stock id
    {
        $stock = Stock::find($id); //find the stock batch by the $id
        $stockItem = $stock->item_id; //store the stock item_id

        $items = Item::find($stockItem); //find the item by $stockItem

        $stock->formated_created_at = Carbon::parse($stock->created_at)->format('F d, Y h:i:s A'); //format the created_at to a readable format
        $stock->formated_updated_at = Carbon::parse($stock->updated_at)->format('F d, Y h:i:s A'); //format the updated_at to a readable format

        return view('admin.sub-page.stocks.edit-stock')->with(['stock' => $stock, 'item' => $items]);
    }

    public function addStock($id) //return the add-stock view 
    {
        $stock = Stock::find($id); //find the id of the stock batch to 'item_stocks' table

        $stockItem = $stock->item_id; //store the stock item_id

        $user = Auth::user(); //get the authenticated user information

        $user_type = $user->type; //get the user type

        if ($user_type === 'manager') {
            if ($stock !== null) {
                $stock->formated_created_at = Carbon::parse($stock->created_at)->format('F d, Y h:i:s A'); //format the created_at to a readable format
                $stock->formated_updated_at = Carbon::parse($stock->updated_at)->format('F d, Y h:i:s A'); //format the updated_at to a readable format

                return view('manager.sub-page.stocks.add-stock')->with(['stock' => $stock, 'item' => $stockItem]);
            }
        } else {
            if ($stock !== null) {
                $stock->formated_created_at = Carbon::parse($stock->created_at)->format('F d, Y h:i:s A'); //format the created_at to a readable format
                $stock->formated_updated_at = Carbon::parse($stock->updated_at)->format('F d, Y h:i:s A'); //format the updated_at to a readable format

                return view('admin.sub-page.stocks.add-stock')->with(['stock' => $stock, 'item' => $stockItem]);
            }
        }
    }

    public function adminUpdateStock(Request $request) //this function will update the stock quantity and return the response in json format
    {
        $stockId = $request->input('stock_id'); //store the value of stock_id input
        $stockQty = $request->input('stock_qty'); //store the value of stock_qty input
        $inputedLotNumber = $request->input('lot_num'); //store the value of lot_num input
        $inputedBlockNumber = $request->input('block_num'); //store the value of block_num input

        $stock = Stock::find($stockId); //get the data of stock batch

        $itemId = $stock->item_id; //store the value of item_id

        $oldStockQty = Stock::where('item_id', $itemId)
            ->where('status', 'active') // select the stock batch with status 'active'
            ->sum('stock_qty'); //sum the stock_qty

        if ($stock) { //if the stock is true means its exist
            $oldQty = $stock->stock_qty; //store the value of stock_qty
            $oldLotNumber = $stock->lot_number; //store the current lot number of the stock batch
            $oldBlockNumber = $stock->block_number; //store the current block number of the stock batch

            if ($stockQty != $oldQty || $inputedLotNumber != $oldLotNumber || $inputedBlockNumber != $oldBlockNumber) {
                list($user_id, $user_type, $user_dept) = $this->startLog(); // starting part of log

                if ($stockQty != $oldQty) { //if the $stockQty is not equal or not match in $oldQty means the current quantity is changed 
                    $stock->stock_qty = $stockQty; //store the value of $newQty to the stock batch stock_qty
                    if (!$stock->save()) {
                        return response()->json([
                            'error' => 'Failed to update stock quantity',
                        ]);
                    }

                    $message = "Stock batch id " . $stockId . " of Item id " . $itemId . " was updated the quantity from `" . $oldQty . "` to `" . $stockQty . "`"; //log message of the stock batch information update
                    $this->endLog($user_id, $user_type, $user_dept, $message); //end of log that will get the log message and user details

                    $currentStockQty = Stock::where('item_id', $itemId)
                        ->where('status', 'active') // select the stock batch with status 'active'
                        ->sum('stock_qty'); //sum the stock_qty

                    $stock_log = new Stock_Log(); //get the stock_log
                    $stock_log->stock_id = $stock->id; //store the stock id to stock_log stock_id column
                    $stock_log->item_id = $stock->item_id; //store the stock item_id to stock_log item_id column
                    $stock_log->quantity = $oldQty < $stockQty ? $stockQty - $oldQty : $oldQty - $stockQty; //if the $oldQty is less than $stockQty subtract the $oldQty to $stockQty else subtract the $stockQty to $oldQty
                    $stock_log->mode_acquisition = $stock->mode_acquisition; //store the stock mode_acquisition to stock_log mode_acquisition column
                    $stock_log->transaction_type = $stockQty > $oldQty ? 'add-stock' : 'deduct-stock'; //if the $newQty is greater than $oldQty, store 'addition' to stock_log transaction_type else store 'deduction'
                    $stock_log->current_quantity = $currentStockQty;
                    $stock_log->prev_quantity = $oldStockQty;
                    $stock_log->save(); //save the data to stock_logs table
                }

                if ($inputedLotNumber != $oldLotNumber) { //if the $inputedLotNumber is not equal or not match in $oldLotNumber means the current lot number is changed 
                    $stock->lot_number = $inputedLotNumber; //store the value of $inputedLotNumber to the stock batch lot_number
                    if (!$stock->save()) {
                        return response()->json([
                            'error' => 'Failed to update stock lot number',
                        ]);
                    }

                    $message = "Lot number of the stock batch: " . $stockId . ", is updated from `" . $oldLotNumber . "` to `" . $inputedLotNumber . "`."; //log message of the lot number that changed
                    $this->endLog($user_id, $user_type, $user_dept, $message); //end of log that will get the log message and user details
                }

                if ($inputedBlockNumber != $oldBlockNumber) { //if the $inputedLotNumber is not equal or not match in $oldBlockNumber means the current block number is changed 
                    $stock->block_number = $inputedBlockNumber; //store the value of $inputedBlockNumber to the stock batch lot_number
                    if (!$stock->save()) {
                        return response()->json([
                            'error' => 'Failed to update stock block number',
                        ]);
                    }

                    $message = "Block number of the stock batch: " . $stockId . ", is updated from `" . $oldBlockNumber . "` to `" . $inputedBlockNumber . "`."; //log message of the block number that changed
                    $this->endLog($user_id, $user_type, $user_dept, $message); //end of log that will get the log message and user details
                }

                return response()->json([
                    'success' => 'Stock quantity updated successfully',
                ]);
            }
        } else {
            return response()->json([
                'error' => 'Stock batch cannot found.',
            ]);
        }
    }

    public function updateStock(Request $request, $id) //this function will update the quantity of the stock batch, the parameter $id is the stock id
    {
        $stock = Stock::find($id); //find the stock id to item_stock table 

        $oldStockQty = $stock->stock_qty; //store the value of current stock quantity of the stock batch 
        $oldLotNumber = $stock->lot_number; //store the current stock batch lot number
        $oldBlockNumber = $stock->block_number; //store the current stock batch block number
        $operation = $request->input("operation"); //store the value of operation input
        $quantity = $request->input("quantity"); //store the value of new_stock input
        $lotNumber = $request->input("lot_number"); //store the value of lot_number input
        $blockNumber = $request->input("block_number"); //store the value of block_number input

        $oldStockQuantity = Stock::where('item_id', $stock->item_id)
            ->where('status', 'active') // select the stock batch with status 'active'
            ->sum('stock_qty'); //store the sum of the previous stock quantity if the item

        if ($operation == 'remove') { //if the operation value is 'remove'
            if ($quantity > $oldStockQty) {
                return response()->json([
                    'error' => 'Cannot remove quantity to stocks.'
                ]);
            } else {
                $newStockQty = $oldStockQty - $quantity; //deduct the value of the $quantity to $oldStockQty
            }
        } else { //else the operation value is 'return'
            $newStockQty = $oldStockQty + $quantity; //add the value of the $quantity to $oldStockQty
        }

        list($user_id, $user_type, $user_dept) = $this->startLog(); //start log

        if ($oldLotNumber !== $lotNumber) {
            $stock->lot_number = $lotNumber;
            if (!$stock->save()) {
                return response()->json([
                    'error' => 'Updating lot number Failed.'
                ]);
            }

            $message = "Stock ID: " . $id . " lot number updated from `" . $oldLotNumber . "` to `" . $lotNumber . "`.";

            $this->endLog($user_id, $user_type, $user_dept, $message); //end part of the log that will save the message

        }

        if ($oldBlockNumber !== $blockNumber) {
            $stock->block_number = $blockNumber;
            if (!$stock->save()) {
                return response()->json([
                    'error' => 'Updating block number Failed.'
                ]);
            }

            $message = "Stock ID: " . $id . " block number updated from `" . $oldBlockNumber . "` to `" . $blockNumber . "`.";

            $this->endLog($user_id, $user_type, $user_dept, $message); //end part of the log that will save the message

        }

        if ($quantity != '') {
            $stock->stock_qty = $newStockQty; //store the value of $newStock to the stock stock_qty
            if (!$stock->save()) {
                return response()->json([
                    'error' => 'Updating stock quantity Failed.'
                ]);
            }

            //Log Message
            if ($operation == "return") {
                $message = "Stock ID: " . $id . ",  returned: " . $quantity . ", prev quantity: " . $oldStockQty . ", current quantity: " . $newStockQty;
            } else {
                $message = "Stock ID: " . $id . ",  removed: " . $quantity . ", prev quantity: " . $oldStockQty . ", current quantity: " . $newStockQty;
            }

            $this->endLog($user_id, $user_type, $user_dept, $message); //end part of the log that will save the message

            $currentStockQuantity = Stock::where('item_id', $stock->item_id)
                ->where('status', 'active')
                ->sum('stock_qty'); //store the sum of the current stock quantity if the item

            $stock_log = new Stock_Log(); //get the stock_log table
            $stock_log->stock_id = $stock->id; //store the stock id to stock_log stock_id column
            $stock_log->item_id = $stock->item_id; //store the stock item_id to stock_log item_id column
            $stock_log->quantity = $quantity; //store the $quantity value to stock_log quantity column
            $stock_log->mode_acquisition = $stock->mode_acquisition; //store the $mode_acquisition value to stock_log mode_acquisition column
            $stock_log->transaction_type = $operation == 'remove' ? 'deduct-stock' : 'add-stock'; //if the $operation value is 'remove' store the value as 'deduction' else if the operation value is 'return' store the value as 'addition'
            $stock_log->current_quantity = $currentStockQuantity;
            $stock_log->prev_quantity = $oldStockQuantity;
            $stock_log->save(); //save to stock_logs
        }


        return response()->json([
            'new_quantity' => $newStockQty,
            'success' => 'Stock quantity is successfully updated.'
        ]);
    }

    public function deleteStock($id) //this function will delete the stock batch, the parameter $id is the stock batch id
    {
        list($user_id, $user_type, $user_dept) = $this->startLog(); //start log

        $stock = Stock::find($id); //find the stock batch by stock id

        $oldStockQuantity = Stock::where('item_id', $stock->item_id)
            ->where('status', 'active') // select the stock batch with status 'active'
            ->sum('stock_qty'); //store the sum of the previous stock quantity if the item

        $stock->status = 'disposed'; //delete the selected stock
        $stock->save(); //update the stock_batch status

        //Log Message
        $message = "Stock batch dispose (id: " . $id . ")";

        $this->endLog($user_id, $user_type, $user_dept, $message); //end of log

        $currentStockQuantity = Stock::where('item_id', $stock->item_id)
            ->where('status', 'active') // select the stock batch with status 'active'
            ->sum('stock_qty'); //store the sum of the current stock quantity if the item



        $stock_log = new Stock_Log(); //get the stock_log table
        $stock_log->stock_id = $stock->id; //store the stock id to stock_log stock_id column
        $stock_log->item_id = $stock->item_id; //store the stock item_id to stock_log item_id column
        $stock_log->quantity = $stock->stock_qty; //store the stock stock_qty to stock_log quantity column
        $stock_log->mode_acquisition = $stock->mode_acquisition; //store the $toStockQty value to stock_log quantity column
        $stock_log->transaction_type = 'dispose'; //store 'deduction' to stock_log transaction_type column
        $stock_log->current_quantity = $currentStockQuantity;
        $stock_log->prev_quantity = $oldStockQuantity;
        $stock_log->save(); //save the data to stock_log table

        if ($stock) {
            return back()->with('success', 'Stock successfully deleted.');
        } else {
            return back()->with('error', 'Stock failed to delete.');
        }
    }
}
