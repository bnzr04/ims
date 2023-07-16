<?php

namespace App\Http\Controllers;

use App\Exports\DispenseExport;
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

class DispenseController extends Controller
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

    ///////////////Dispense report///////////////
    public function dispense() //this function will return the dispense view that can see all the dispensed items
    {
        $user = Auth::user(); //get the authenticated user information

        if ($user->type === 'manager') { //if the user type is 'manager'
            return view('manager.sub-page.dispense.dispense');
        } else {
            return view('admin.sub-page.dispense.dispense');
        }
    }

    // public function getDispense()
    // {
    //     $completedAndDeliveredId = ModelsRequest::whereIn('status', ['completed', 'delivered'])->pluck('id');

    //     // Filter dispensed items that occurred today
    //     $from = Carbon::now()->startOfDay();
    //     $to = Carbon::now()->endOfDay();

    //     $data = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
    //         ->select('request_items.item_id', 'items.name', 'items.description', 'items.category', 'items.unit', DB::raw('SUM(request_items.quantity) as total_dispense'))
    //         ->distinct()
    //         ->whereIn('request_id', $completedAndDeliveredId)
    //         ->whereBetween('request_items.updated_at', [$from, $to])
    //         ->groupBy('request_items.item_id', 'items.name', 'items.description', 'items.category', 'items.unit')
    //         ->orderBy('items.name', 'asc')
    //         ->get();

    //     return response()->json($data);
    // }

    public function dispenseFilter(Request $request) //this  function will get the dispensed items
    {
        $today = $request->input('today'); //store the today input 
        $yesterday = $request->input('yesterday'); //store the yesterday input 
        $thisMonth = $request->input('this-month'); //store the this-month input 

        $moaFilter = $request->input('moa'); //store the value of 'moa' input


        if ($today) { //if $today is true
            // Filter dispensed items that occurred today
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        } elseif ($yesterday) { //if $yesterday is true
            // Filter dispensed items that occurred yesterday
            $from = Carbon::yesterday()->startOfDay();
            $to = Carbon::yesterday()->endOfDay();
        } elseif ($thisMonth) {  //if $thisMonth is true
            // Filter dispensed items that occurred this month
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfMonth();
        } else {
            // Filter dispensed items that occurred from selected date
            $from = $request->input('date_from');
            $to = $request->input('date_to');
            $to = date('Y-m-d', strtotime($to . ' +1 day'));
        }

        $completedAndDeliveredId = ModelsRequest::whereIn('status', ['completed', 'delivered'])->pluck('id'); //retrieve all the request id with 'completed' and 'completed' status 

        //join the request_items and items table, this will get all the items that requested and with the request status of 'completed' or 'delivered', and retrieve the data between the date period $from and $to
        $data = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->select('request_items.item_id', 'items.name', 'items.description', 'items.category', 'items.unit', DB::raw('SUM(request_items.quantity) as total_dispense'))
            ->distinct()
            ->whereIn('request_id', $completedAndDeliveredId)
            ->whereBetween('request_items.updated_at', [$from, $to])
            ->groupBy('request_items.item_id', 'items.name', 'items.description', 'items.category', 'items.unit')
            ->orderBy('items.name', 'asc');

        if ($moaFilter) { //if $moaFilter is true
            if ($moaFilter == 'petty-cash') { //if $moaFilter value is "petty-cash"
                $data =  $data->where('request_items.mode_acquisition', 'Petty Cash'); //add a query to $data where the request_items mode_acquisition column is 'Petty Cash' 
            } else if ($moaFilter == 'donation') { //if $moaFilter value is "donation"
                $data =  $data->where('request_items.mode_acquisition', 'Donation'); //add a query to $data where the request_items mode_acquisition column is 'Donation'
            } else if ($moaFilter == 'lgu') { //if $moaFilter value is "lgu"
                $data =  $data->where('request_items.mode_acquisition', 'LGU'); //add a query to $data where the request_items mode_acquisition column is 'LGU'
            }
        }

        $data = $data->get(); //pass the query and get the $data

        foreach ($data as $stock) {
            $stockQty = Stock_Log::select('current_quantity')
                ->where('item_id', $stock->item_id)
                ->where('created_at', '<=', $to)
                ->latest('created_at')
                ->first(); //retrieve all the stock_log table information by the item_id

            //add the query to $stockQty where the transaction_type column is 'addition' between the date period and sum all the quantity of every row
            $stockQty = $stockQty;

            $stock->stock_qty = $stockQty; //store the $stockQty to stock_qty of data as stock

            if ($moaFilter) { //if $moaFilter is true
                $acquired = Stock_Log::where('item_id', $stock->item_id); //retrieve all the stock_log table information by the item_id

                if ($moaFilter == 'petty-cash') { //if $moaFilter value is "petty-cash"
                    $acquired =  $acquired->where('mode_acquisition', 'Petty Cash'); //add a query to $acquired where the request_items mode_acquisition column is 'Petty Cash' 
                } else if ($moaFilter == 'donation') { //if $moaFilter value is "donation"
                    $acquired =  $acquired->where('mode_acquisition', 'Donation'); //add a query to $acquired where the request_items mode_acquisition column is 'Donation'
                } else if ($moaFilter == 'lgu') { //if $moaFilter value is "lgu"
                    $acquired =  $acquired->where('mode_acquisition', 'LGU'); //add a query to $acquired where the request_items mode_acquisition column is 'LGU'
                }

                //add the query to $acquired where the transaction_type column is 'addition' between the date period and sum all the quantity of every row
                $acquired = $acquired->where('transaction_type', 'addition')
                    ->whereBetween('created_at', [$from, $to])
                    ->value(DB::raw('SUM(quantity)'));

                $stock->acquired = $acquired;
            }
        }

        return response()->json($data); //return the $data as json format
    }

    public function dispenseExport(Request $request) //this function will generate a excel file of dispense report 
    {
        $req =  ucfirst($request->input('filter')); //store the value of 'filter' input and make the value upper case the letter

        list($user_id, $user_type, $user_dept) = $this->startLog(); //start log

        $filename = 'Pharma_Dispensed_Item' . Carbon::now()->format('Ymd-His') . '.xlsx'; //this will be the filename of the generated excel file

        //this will generate the excel file
        $response = Excel::download(new DispenseExport($filename), $filename, \Maatwebsite\Excel\Excel::XLSX, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);

        $message = "Dispensed " . $req . " Report Downloaded"; //log message

        $this->endLog($user_id, $user_type, $user_dept, $message); //end log 

        return $response; //return and generate the dispense report
    }

    public function fetchRecord(Request $request, $id) //this function fetch the records, the parameter $id is the item id
    {
        $today = $request->input('today'); //get the request of "today" input
        $yesterday = $request->input('yesterday'); //get the request of "yesterday" input
        $thisMonth = $request->input('this-month'); //get the request of "this-month" input
        $filter = $request->input('filter'); //get the request of "filter" input

        $moaFilter = $request->input('moa'); //get the value of 'moa' input

        if ($today) { //if $today is true
            $from = Carbon::now()->startOfDay(); //store the starting time of the day
            $to = Carbon::now()->endOfDay(); //store the end time of the day
        } else if ($yesterday) { //if $yesterday is true
            $from = Carbon::yesterday()->startOfDay(); //store the starting time of yesterday
            $to = Carbon::yesterday()->endOfDay(); //store the end time of yesterday
        } else if ($thisMonth) { //if $thisMonth is true
            $from = Carbon::now()->startOfMonth(); //store the date of the start of month
            $to = Carbon::now()->endOfMonth(); //store the date of the end of month
        } else if ($filter) { //if $filter is true
            $from = $request->input('from'); //store the value of 'from' input
            $to = $request->input('to'); //store the value of 'to' input
            $to = Carbon::createFromFormat('Y-m-d', $to)->addDay(); //store the value $to and format the value like 'Y-m-d' and add 1 day
        } else {
            $from = Carbon::now()->startOfDay(); //store the starting time of the day
            $to = Carbon::now()->endOfDay(); //store the end time of the day
        }

        //join the request and request_items table and retrieve the request with status 'completed' and 'delivered' value with the request_items item_id equal to $id and filter the date period of created_at column
        $record = Request_Item::join('request', 'request_items.request_id', '=', 'request.id')
            ->whereIn('request.status', ['completed', 'delivered'])
            ->where('request_items.item_id', $id)
            ->whereBetween('request_items.created_at', [$from, $to]);

        if ($moaFilter) { //if $moaFilter is true

            if ($moaFilter == 'petty-cash') { //if the $moaFilter value is 'petty-cash'
                $record =  $record->where('request_items.mode_acquisition', 'Petty Cash');
            } else if ($moaFilter == 'donation') { //if the $moaFilter value is 'donation'
                $record =  $record->where('request_items.mode_acquisition', 'Donation');
            } else if ($moaFilter == 'lgu') { //if the $moaFilter value is 'lgu'
                $record =  $record->where('request_items.mode_acquisition', 'LGU');
            }
        }

        $record = $record->get(); //get the record data

        foreach ($record as $rec) {
            $rec->formatDate = Carbon::parse($rec->created_at)->format("F d, Y h:i:s A"); //format the created_at to a readable format
        }

        return response()->json($record); //return $record in json format
    }
}
