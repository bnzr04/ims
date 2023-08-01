<?php

namespace App\Http\Controllers;

use App\Models\Canceled_Request;
use App\Models\Item;
use App\Models\Log;
use App\Models\Request as ModelsRequest;
use App\Models\Request_Item;
use App\Models\Stock;
use App\Models\Stock_Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class RequestController extends Controller
{

    //this shows when user click request module
    public function request()
    {
        //Get today date
        $today = Carbon::today()->format('Y-m-d');


        $items = //this $items will show to the select tag where the user can choose the items they want to request
            DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id') //join the item_stocks and items table
            ->select(
                'items.id',
                'items.name',
                'items.category',
                'items.unit',
                'item_stocks.id as item_stock_id',
                'item_stocks.stock_qty',
                'item_stocks.exp_date',
                'item_stocks.mode_acquisition',
                'item_stocks.lot_number',
                'item_stocks.block_number',
                'item_stocks.created_at'
            ) //select this items and item_stocks columns
            ->where('item_stocks.exp_date', ">", $today) //select the item stock with the exp_date that not expired
            ->where('item_stocks.stock_qty', '>', 0) //show the stocks where stock_qty is greater than 0
            ->where('item_stocks.status', 'active') //show the stocks where stock status is equal to 'active'
            ->orderBy('items.name', 'asc')
            ->get();

        foreach ($items as $item) {
            $exp_date = Carbon::createFromFormat('Y-m-d', $item->exp_date);
            $item->formatted_exp_date = $exp_date->format('m-d-Y'); //format the exp_date to 'm-d-Y'
        }

        return view('user.request')->with([ //return tp request module with the $items data
            'items' => $items,
        ]);
    }

    public function viewRequests()
    {
        return view('user.sub-page.view-request');
    }

    public function viewRequest(Request $requests, $request, $filter) //this function will retrieve the requests, parameter $request = the request status what to retrieve, parameter $filter = the selected date period
    {
        $user_id = Auth::user()->id; //get the id of authenticated user

        $date_from = $requests->input("date_from"); //get the value of date_from input
        $date_to = $requests->input("date_to"); //get the value of date_to input

        $requests = ModelsRequest::where('user_id', $user_id) //query a request with column user_id equal to the user id of authenticated user 
            ->orderBy('created_at', 'desc'); //order the request created_at as descending

        if ($filter === "today") { //if the $filter parameter is equal to 'today'
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        } else if ($filter === "this-week") { //if the $filter parameter is equal to 'this-week'
            $from = Carbon::now()->startOfWeek();
            $to = Carbon::now()->endOfWeek();
        } else if ($filter === "this-month") { //if the $filter parameter is equal to 'this-month'
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfMonth();
        } else if ($date_from && $date_to) { //if the $date_from and $date_to is true or has a value passed

            $from = date('Y-m-d', strtotime($date_from)); //format the value of $date_from to 'Year-Month-Day'
            $to = date('Y-m-d', strtotime($date_to . '+1day')); //format the value of $date_to to 'Year-Month-Day' and add 1 day

            $dateFrom = Carbon::parse($from)->format('F j, Y'); //format the $from value to readable format ex. 'January 10, 2023'
            $dateTo = Carbon::parse(strtotime($date_to . '+1day'))->format('F j, Y'); //format the $to value to readable format ex. 'January 10, 2023' and add 1 day
        }

        if ($request === 'pending') { //if the $request value is equal to 'pending'

            $requests = $requests->where('status', 'pending'); //add to $requests query that will retrieve the request where status with the value of 'pending'

            $title = "pending"; //set the $title value to 'pending'
        } else if ($request === 'accepted') { //if the $request value is equal to 'accepted'

            $requests = $requests->where('status', 'accepted'); //add to $requests query that will retrieve the request where status with the value of 'accepted'

            $title = "accepted"; //set the $title value to 'accepted'
        } else if ($request === 'delivered') { //if the $request value is equal to 'delivered'

            $requests = $requests->where('status', 'delivered'); //add to $requests query that will retrieve the request where status with the value of 'delivered'

            $title = "delivered"; //set the $title value to 'delivered'
        } else if ($request === 'completed') { //if the $request value is equal to 'completed'

            $requests = $requests->where('status', 'completed'); //add to $requests query that will retrieve the request where status with the value of 'completed'

            $title = "completed"; //set the $title value to 'completed'
        } else if ($request === 'canceled') { //if the $request value is equal to 'canceled'

            $requests = $requests->where('status', 'canceled'); //add to $requests query that will retrieve the request where status with the value of 'canceled'

            $title = "canceled"; //set the $title value to 'canceled'
        }

        $requests = $requests->whereBetween('created_at', [$from, $to]) //filter the request created_at between the value of $from and $to
            ->orderBy('created_at', 'desc') //order the request created_at as descending 
            ->get(); //get the query

        foreach ($requests as $item) {
            $item->formatted_date = Carbon::parse($item->created_at)->format('F j, Y, g:i:s a'); //format the created_at to a readble format and store the value to 'formatted_date'
        }

        if ($date_from && $date_to) {
            return view('user.sub-page.view-request')->with([
                'requests' => $requests,
                'title' => $title,
                'filter' => $filter,
                'request' => $request,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ]);
        } else {
            return view('user.sub-page.view-request')->with([
                'requests' => $requests,
                'title' => $title,
                'filter' => $filter,
                'request' => $request
            ]);
        }
    }

    public function itemRequest(Request $request, $id) //this function will return the view-request view
    {
        $requested = ModelsRequest::where('id', $id)->first(); //get the information of the request using the request id = $id

        $requested->formatted_date = Carbon::parse($requested->created_at)->format('F j, Y, g:i:s a'); //format the created_at to a readable format

        $requestItems = Request_Item::join('items', 'request_items.item_id', '=', 'items.id') //join the request_items and items table
            ->select('request_items.*', 'items.*') //select all the column of the request_items and items table
            ->where('request_items.request_id', $id) //select all the data where the request_id is equal to $id
            ->orderBy('request_items.created_at', 'asc') //fetch the request_items created_at to ascending order
            ->get();

        $today = Carbon::today()->format('Y-m-d'); //format the todays date to 'Year-Month-Day'

        $items =
            DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id') //join the item_stocks and items table
            ->select('items.name', 'item_stocks.*') //select the name column to items table and all the columns to item_stocks
            ->where('item_stocks.exp_date', ">", $today) //fetch all the item_stocks row with the exp_date that has exp_date that is not expired
            ->orderBy('items.name', 'asc')
            ->get();

        $canceled = Canceled_Request::where('request_id', $id)->first(); //find the $id that match to request_id in canceled_requests table

        if ($canceled) { //if the the request_id is exist in canceled_requests table

            $canceled->format_date = Carbon::parse($canceled->created_at)->format('F j, Y, g:i:s a'); //format the created_at to a readable format

            return view('user.sub-page.view-items')->with([ //return the requested items, request, items and canceled data to view-items view
                'requestItems' => $requestItems,
                'request' => $requested,
                'items' => $items,
                'canceled' => $canceled,
            ]);
        } else {
            return view('user.sub-page.view-items')->with([ //return the requested items, request and items data to view-items view
                'requestItems' => $requestItems,
                'request' => $requested,
                'items' => $items,
            ]);
        }
    }

    //This will submit the requested items
    public function submitRequest(Request $request)
    {
        $requested = $request->input('requestedItems'); //get the requestedItems input value
        $request_by = $request->input('requestBy'); //get the requestBy input value
        $patient_name = $request->input('patientName'); //get the patientName input value
        $patient_age = $request->input('patientAge'); //get the patientAge input value
        $patient_gender = $request->input('patientGender'); //get the patientGender input value
        $doctor_name = $request->input('doctorName'); //get the doctorName input value
        $requestedItems = json_decode($requested); //decode the $requested from json format

        //Enable Query Log
        DB::enableQueryLog();

        //get user details
        $user = Auth::user();

        $userId = $user->id; //user id
        $userType = $user->type; //user type
        $office = $user->name; //we use user name as office name

        $requestModel = new ModelsRequest(); //set the $requestModel to ModelsRequest or request table
        $requestModel->user_id = $userId; //set the request.user_id to the $userId
        $requestModel->office = $office; //set the request.office to the $office
        $requestModel->request_to = 'pharmacy'; //set the request.request_to value to 'pharmacy'
        $requestModel->request_by = ucwords($request_by); //set the request.request_by to the $request_by and upper case the value every word
        $requestModel->patient_name = ucwords($patient_name); //set the request.patient_name to the $patient_name and upper case the value every word
        $requestModel->age = filled($patient_age) ? $patient_age : 0; //if the patient_age has a value, pass the value to request age else pass the value as '0'
        $requestModel->gender = filled($patient_gender) ? $patient_gender : "-"; //if the patient_gender has a value, pass the value to request gender else pass the value as '-'
        $requestModel->doctor_name = ucwords($doctor_name); //set the request.doctor_name to the $doctor_name and upper case the value every word
        $requestModel->save(); //save the data to the table

        $requestID = $requestModel->id; //set the $requestID to the new request id

        // Get the SQL query being executed
        $sql = DB::getQueryLog();
        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
        } else {
            $last_query = 'No query log found.';
        }

        //Log Message
        $message = "New request is created. REQUEST ID: " . $requestID;

        // Log the data to the logs table
        Log::create([
            'user_id' => $userId,
            'user_type' => $userType,
            'message' => $message,
            'query' => $last_query,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        //add the item in request_items
        foreach ($requestedItems as $item) { //every requested items

            $model = new Request_Item(); //set the model to request_items table
            $model->request_id = $requestModel->id; //set the request_item.request_id to new request ID
            $model->item_id = $item->item_id; //set the request_item.item_id from the passed or requested item id
            $model->stock_id = $item->stock_id; //set the request_item.stock_id from the passed or requested item stock_id
            $model->mode_acquisition = $item->mode_acquisition; //set the request_item.mode_acquisition from the passed or requested item mode_acquisition
            $model->lot_number = $item->lot_number; //set the request_item.lot_number from the passed or requested item stock lot_number
            $model->block_number = $item->block_number; //set the request_item.block_number from the passed or requested item stock block_number
            $model->exp_date = $item->exp_date; //set the request_item.exp_date from the passed or requested item exp_date
            $model->quantity = intval($item->quantity); //set the request_item.quantity from the passed or requested item quantity
            $model->save(); //save the setted data to request_items table

            // Get the SQL query being executed
            $sql = DB::getQueryLog();
            if (is_array($sql) && count($sql) > 0) {
                $last_query = end($sql)['query'];
            } else {
                $last_query = 'No query log found.';
            }

            //Log Message
            $message = "Item ID: " . $item->item_id . " is added to Request ID: " . $requestID;

            // Log the data to the logs table
            Log::create([
                'user_id' => $userId,
                'user_type' => $userType,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $oldStockQuantity = Stock::where('item_id', $item->item_id)
                ->where('status', 'active')
                ->sum('stock_qty'); //store the sum of the old stock quantity if the item

            /////Stock reserving/////

            $requestedQty = $model->quantity; //set the requested quantity of items to $requestedQty

            $itemStock = Stock::find($model->stock_id); //find the requested item stock_id that exist in Stock or items_stocks table
            $newStock = $itemStock->stock_qty - $requestedQty; //deduct the requested quantity to the stock quantity and set the value to $newStock
            $itemStock->stock_qty = $newStock; //set the stock quantity to new stock value
            $itemStock->save(); //save the data to item stock

            // Get the SQL query being executed
            $sql = DB::getQueryLog();
            if (is_array($sql) && count($sql) > 0) {
                $last_query = end($sql)['query'];
            } else {
                $last_query = 'No query log found.';
            }

            //Log Message
            $message = "Stock ID: " . $model->stock_id . " of Item ID: " . $model->item_id . " reserved " . $requestedQty;

            // Log the data to the logs table
            Log::create([
                'user_id' => $userId,
                'user_type' => $userType,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            //Log the deduction to stock_logs table 
            $stockLog = new Stock_Log(); //get the stock_log table
            $stockLog->stock_id = $item->stock_id;  //store the $stockId value to 'stock_id' in the stock_log table
            $stockLog->item_id = $item->item_id;  //store the $itemId value to 'item_id' in the stock_log table
            $stockLog->quantity = $item->quantity;  //store the $quantity value to 'quantity' in the stock_log table
            $stockLog->mode_acquisition = $item->mode_acquisition;  //store the $item->mode_acquisition value to 'mode_acquisition' in the stock_log table
            $stockLog->transaction_type = 'reserve';  //store the 'reserve' to 'transaction_type' in the stock_log table

            $currentStockQuantity = Stock::where('item_id', $item->item_id)
                ->where('status', 'active')
                ->sum('stock_qty'); //store the sum of the current stock quantity if each item

            $stockLog->current_quantity = $currentStockQuantity;  //store the $currentStockQuantity value to 'current_quantity' in the stock_log table
            $stockLog->prev_quantity = $oldStockQuantity;  //store the $oldStockQuantity value to 'prev_quantity' in the stock_log table
            $stockLog->save();
        }

        return response()->json([
            'success' => true,
            'request_id' => $requestModel->id,
            'requestedQty' => $requestedQty,
            'itemStock' => $itemStock,
        ]);
    }


    public function cancelRequest(Request $request, $rid) //this function will cancel the request
    {
        //Enable Query Log
        DB::enableQueryLog();

        //get user details
        $user = Auth::user();
        $userId = $user->id;
        $userType = $user->type;

        $requestItems = Request_Item::where("request_id", $rid)->get(); //find all the requested items of the request using the request id = $rid

        //get the reason of cancelation
        $cancelReason = $request->input("canceled_reason");

        $theRequest = ModelsRequest::where("id", $rid)->first(); //get the request data of the canceled request

        $canceledModel =  new Canceled_Request(); //set the canceled_requests table to $canceledModel
        $canceledModel->request_id = $rid; //set the canceled_request request_id to $rid  
        $canceledModel->reason = $cancelReason; //set the canceled_request reason to $cancelReason value
        $canceledModel->save(); //save the data to canceled_request

        if ($theRequest->status == "pending") { //if the request status is equal to 'pending'
            $theRequest->status = "canceled"; //set the request status value to 'canceled'
            $theRequest->save(); //save the updated data
        } else {
            return back()->with("error", "The request is failed to cancel because the request is either already accepted or delivered."); //show alert, saying that the request is either accepted or delivered.
        }

        foreach ($requestItems as $item) {

            //get the requested items details
            $stockId = $item->stock_id;
            $itemId =  $item->item_id;
            $quantity = $item->quantity;

            $oldStockQuantity = Stock::where('item_id', $itemId)
                ->where('status', 'active')
                ->sum('stock_qty'); //store the sum of the old stock quantity if the item

            //get the current stock details
            $stock = Stock::where("id", $stockId)->first();

            if ($stock) {
                $stock->stock_qty += $quantity; //return the requested item quantity to stock quantity
                $stock->save(); //save the data to item_stock table
            } else {
                $createStock = new Stock();
                $createStock->id = $stockId;
                $createStock->item_id = $itemId;
                $createStock->stock_qty = $quantity;
                $createStock->exp_date = Carbon::createFromFormat('m-d-Y', $item->exp_date)->format('Y-m-d');
                $createStock->mode_acquisition = $item->mode_acquisition;
                $createStock->save();
            }


            //Log the deduction to stock_logs table 
            $stockLog = new Stock_Log(); //get the stock_log table
            $stockLog->stock_id = $stockId;  //store the $stockId value to 'stock_id' in the stock_log table
            $stockLog->item_id = $itemId;  //store the $itemId value to 'item_id' in the stock_log table
            $stockLog->quantity = $quantity;  //store the $quantity value to 'quantity' in the stock_log table
            $stockLog->mode_acquisition = $item->mode_acquisition;  //store the $item->mode_acquisition value to 'mode_acquisition' in the stock_log table
            $stockLog->transaction_type = 'return';  //store the 'return' to 'transaction_type' in the stock_log table

            $currentStockQuantity = Stock::where('item_id', $item->item_id)
                ->where('status', 'active')
                ->sum('stock_qty'); //store the sum of the current stock quantity if each item

            $stockLog->current_quantity = $currentStockQuantity;  //store the $currentStockQuantity value to 'current_quantity' in the stock_log table
            $stockLog->prev_quantity = $oldStockQuantity;  //store the $oldStockQuantity value to 'prev_quantity' in the stock_log table
            $stockLog->save();

            // Get the SQL query being executed
            $sql = DB::getQueryLog();
            if (is_array($sql) && count($sql) > 0) {
                $last_query = end($sql)['query'];
            } else {
                $last_query = 'No query log found.';
            }

            //Log Message
            $message = "The requested " . $quantity . " of Item ID: " . $itemId . " from Stock ID: " . $stockId . " with Request ID: " . $rid . " is canceled.";

            // Log the data to the logs table
            Log::create([
                'user_id' => $userId,
                'user_type' => $userType,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Get the SQL query being executed
        $sql = DB::getQueryLog();
        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
        } else {
            $last_query = 'No query log found.';
        }

        if ($theRequest) {
            //Log Message
            $message = "Request ID: " . $rid . " is marked as canceled";

            // Log the data to the logs table
            Log::create([
                'user_id' => $userId,
                'user_type' => $userType,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        if ($stock || $createStock->save()) {
            return back()->with("success", "The request is successfully canceled.");
        } else {
            return back()->with("error", "The request is failed to canceled.");
        }
    }


    public function receiveRequest(Request $request, $rid) //this function will mark the request status to 'completed'
    {
        //Enable Query Log
        DB::enableQueryLog();

        $user = Auth::user(); //get the authenticated user details
        $user_id = $user->id; //get the user id
        $user_type = $user->type; //get the user type
        $user_name = $user->name; //get the user name

        $receiverName = $request->receiver_name; //get the receiver name from receiver_name input

        $request = ModelsRequest::find($rid); //find the request by requested_id = $rid

        if ($request) { //if $request is true

            $requestedItems = Request_Item::where('request_id', $rid)->get();

            // return dd($requestedItems);

            foreach ($requestedItems as $item) {
                $stock_id =  $item->stock_id;

                $stock = Stock::where('id', $stock_id)->first();

                if ($stock) { // if stock batch is exist

                    $stockQuantity = $stock->stock_qty;

                    // return dd($stockQuantity);

                    //check if the stock batch is 0 value
                    if ($stockQuantity <= 0) {
                        $stock->delete();
                    }
                }
            }

            $request->receiver = $receiverName; //set the request receiver to $receiverName value
            $request->status = 'completed'; //set the request status to 'completed'
            $request->save(); //save the updated data

            //Get Query
            $sql = DB::getQueryLog();

            if (is_array($sql) && count($sql) > 0) {
                $last_query = end($sql)['query'];
            } else {
                $last_query = 'No query log found.';
            }

            //Log Message
            $message = $user_name . " received the items. Request ID: " . $rid;

            // Log the data to the logs table
            Log::create([
                'user_id' => $user_id,
                'user_type' => $user_type,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->route('user.viewRequest', ['request' => 'delivered', 'filter' => 'today'])->with('success', 'Request completed');
        } else {
            return redirect()->route('user.viewRequest', ['request' => 'delivered', 'filter' => 'today'])->with('error', "Request doesn't exist.");
        }
    }


    public function showList() //this function will show all the availble items on stock
    {
        $items = Stock::leftjoin('items', 'item_stocks.item_id', '=', 'items.id') //join the item_stocks and items table
            ->select( //select the name,category and unit column to items table
                'items.name',
                'items.category',
                'items.unit',
            )
            ->where('item_stocks.stock_qty', '>', 0)
            ->where('item_stocks.status', 'active')
            ->groupBy( //group the name,category and unit column
                'items.name',
                'items.category',
                'items.unit',
            )
            ->orderBy('name') //order the items name as ascending
            ->get();

        return response()->json($items); //return the $items value in json format
    }
}
