<?php

namespace App\Http\Controllers;

use App\Models\Canceled_Request;
use App\Models\Item;
use App\Models\Log;
use App\Models\Request as ModelsRequest;
use App\Models\Request_Item;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{

    //this shows when user click request module
    public function request()
    {
        //Get today date
        $today = Carbon::today()->format('Y-m-d');


        $items =
            DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->select('items.id', 'items.name', 'items.category', 'items.unit', 'item_stocks.id as item_stock_id', 'item_stocks.stock_qty', 'item_stocks.exp_date', 'item_stocks.mode_acquisition')
            ->where('item_stocks.exp_date', ">", $today)
            ->orderBy('items.name', 'asc')
            ->get();

        foreach ($items as $item) {
            $exp_date = Carbon::createFromFormat('Y-m-d', $item->exp_date);
            $item->formatted_exp_date = $exp_date->format('m-d-Y');
        }

        return view('user.request')->with([
            'items' => $items,
        ]);
    }

    // public function showPendingRequest()
    // {
    //     $user = Auth::user();
    //     $user_id = $user->id;

    //     $pending = ModelsRequest::where('status', 'pending')
    //         ->where('user_id', $user_id)
    //         ->orderByDesc('updated_at')
    //         ->get()->each(function ($pending) {
    //             $pending->formatted_date = Carbon::parse($pending->created_at)
    //                 ->format('F j, Y, g:i:s a');
    //         });

    //     $expiresAt = now()->addMinutes(10); // cache will expire after 10 minutes
    //     Cache::put('filteredData', $pending, $expiresAt);

    //     $requestCount = ModelsRequest::where('status', 'pending')
    //         ->where('user_id', $user_id)
    //         ->count();

    //     DB::connection()->commit();

    //     return response()->json([
    //         'pending' => $pending,
    //         'pendingCount' => $requestCount,
    //     ]);
    // }

    // public function showAcceptedRequest()
    // {
    //     $user = Auth::user();
    //     $user_id = $user->id;

    //     $requests = ModelsRequest::where('status', 'accepted')
    //         ->where('user_id', $user_id)
    //         ->orderByDesc('updated_at')
    //         ->get()->each(function ($pending) {
    //             $pending->formatted_date = Carbon::parse($pending->created_at)
    //                 ->format('F j, Y, g:i:s a');
    //         });

    //     $expiresAt = now()->addMinutes(10); // cache will expire after 10 minutes
    //     Cache::put('filteredData', $requests, $expiresAt);

    //     $requestCount = ModelsRequest::where('status', 'accepted')
    //         ->where('user_id', $user_id)
    //         ->count();

    //     DB::connection()->commit();

    //     return response()->json([
    //         'accepted' => $requests,
    //         'acceptedCount' => $requestCount,
    //     ]);
    // }

    // public function showDeliveredRequest()
    // {
    //     $user = Auth::user();
    //     $user_id = $user->id;

    //     $requests = ModelsRequest::where('status', 'delivered')
    //         ->where('user_id', $user_id)
    //         ->orderByDesc('updated_at')
    //         ->get()->each(function ($pending) {
    //             $pending->formatted_date = Carbon::parse($pending->created_at)
    //                 ->format('F j, Y, g:i:s a');
    //         });

    //     $expiresAt = now()->addMinutes(10); // cache will expire after 10 minutes
    //     Cache::put('filteredData', $requests, $expiresAt);

    //     $requestCount = ModelsRequest::where('status', 'delivered')
    //         ->where('user_id', $user_id)
    //         ->count();

    //     DB::connection()->commit();

    //     return response()->json([
    //         'delivered' => $requests,
    //         'deliveredCount' => $requestCount,
    //     ]);
    // }

    // public function showCompletedRequest()
    // {
    //     $user = Auth::user();
    //     $user_id = $user->id;

    //     $requests = ModelsRequest::where('status', 'completed')
    //         ->where('user_id', $user_id)
    //         ->orderByDesc('updated_at')
    //         ->get()->each(function ($pending) {
    //             $pending->formatted_date = Carbon::parse($pending->created_at)
    //                 ->format('F j, Y, g:i:s a');
    //         });

    //     $expiresAt = now()->addMinutes(10); // cache will expire after 10 minutes
    //     Cache::put('filteredData', $requests, $expiresAt);

    //     $requestCount = ModelsRequest::where('status', 'completed')
    //         ->where('user_id', $user_id)
    //         ->count();

    //     DB::connection()->commit();

    //     return response()->json([
    //         'completed' => $requests,
    //         'completedCount' => $requestCount,
    //     ]);
    // }

    public function viewRequests()
    {
        return view('user.sub-page.view-request');
    }

    public function viewRequest(Request $requests, $request, $filter)
    {
        $user_id = Auth::user()->id;

        $date_from = $requests->input("date_from");
        $date_to = $requests->input("date_to");

        if ($filter === "today") {
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        } else if ($filter === "this-week") {
            $from = Carbon::now()->startOfWeek();
            $to = Carbon::now()->endOfWeek();
        } else if ($filter === "this-month") {
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfMonth();
        } else if ($date_from && $date_to) {

            $from = date('Y-m-d', strtotime($date_from));
            $to = date('Y-m-d', strtotime($date_to . '+1day'));

            $dateFrom = Carbon::parse($from)->format('F j, Y');
            $dateTo = Carbon::parse(strtotime($date_to . '+1day'))->format('F j, Y');
        }

        if ($request === 'pending') {

            $items = ModelsRequest::where('user_id', $user_id)
                ->whereBetween('created_at', [$from, $to])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            $title = "pending";
        } else if ($request === 'accepted') {

            $items = ModelsRequest::where('user_id', $user_id)
                ->whereBetween('created_at', [$from, $to])
                ->where('status', 'accepted')
                ->orderBy('created_at', 'desc')
                ->get();

            $title = "accepted";
        } else if ($request === 'delivered') {

            $items = ModelsRequest::where('user_id', $user_id)
                ->whereBetween('created_at', [$from, $to])
                ->where('status', 'delivered')
                ->orderBy('created_at', 'desc')
                ->get();

            $title = "delivered";
        } else if ($request === 'completed') {

            $items = ModelsRequest::where('user_id', $user_id)
                ->whereBetween('created_at', [$from, $to])
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->get();

            $title = "completed";
        } else if ($request === 'canceled') {

            $items = ModelsRequest::where('user_id', $user_id)
                ->whereBetween('created_at', [$from, $to])
                ->where('status', 'canceled')
                ->orderBy('created_at', 'desc')
                ->get();

            $title = "canceled";
        }

        foreach ($items as $item) {
            $item->formatted_date = Carbon::parse($item->created_at)->format('F j, Y, g:i:s a');
        }

        if ($date_from && $date_to) {
            return view('user.sub-page.view-request')->with([
                'items' => $items,
                'title' => $title,
                'filter' => $filter,
                'request' => $request,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ]);
        } else {
            return view('user.sub-page.view-request')->with([
                'items' => $items,
                'title' => $title,
                'filter' => $filter,
                'request' => $request
            ]);
        }
    }

    public function itemRequest(Request $request, $id)
    {
        $requested = ModelsRequest::where('id', $id)->first();

        //Formatted date
        $requested->formatted_date = Carbon::parse($requested->created_at)->format('F j, Y, g:i:s a');

        $requestItems = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->select('request_items.*', 'items.*')
            ->where('request_items.request_id', $id)
            ->orderBy('request_items.created_at', 'asc')
            ->get();

        //Get today date
        $today = Carbon::today()->format('Y-m-d');


        $items =
            DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->select('items.name', 'item_stocks.*')
            ->where('item_stocks.exp_date', ">", $today)
            ->orderBy('items.name', 'asc')
            ->get();

        $canceled = Canceled_Request::where('request_id', $id)->first();

        if ($canceled) {

            $canceled->format_date = Carbon::parse($canceled->created_at)->format('F j, Y, g:i:s a');

            return view('user.sub-page.view-items')->with([
                'requestItems' => $requestItems,
                'request' => $requested,
                'items' => $items,
                'canceled' => $canceled,
            ]);
        } else {
            return view('user.sub-page.view-items')->with([
                'requestItems' => $requestItems,
                'request' => $requested,
                'items' => $items,
            ]);
        }
    }

    // public function removeItem($sid, $id)
    // {
    //     $items = Request_Item::where('stock_id', $sid)->where('item_id', $id);

    //     $items->delete();

    //     return back()->with('success', 'Item successfully removed to requested item.');
    // }


    // public function deleteRequest($id)
    // {
    //     $request = ModelsRequest::find($id);

    //     //Enable Query log
    //     DB::enableQueryLog();

    //     $request->delete();

    //     //QUERY LOG
    //     $user = auth()->user();

    //     $user_id = $user->id; // Get the ID of the authenticated user
    //     $user_type = $user->type; // Get the type of the authenticated user
    //     $user_name = $user->name; // Get the name of the authenticated user


    //     // Get the SQL query being executed
    //     $sql = DB::getQueryLog();
    //     if (is_array($sql) && count($sql) > 0) {
    //         $last_query = end($sql)['query'];
    //     } else {
    //         $last_query = 'No query log found.';
    //     }

    //     //Log Message
    //     $message = "Request (ID: " . $id . ") requested by " . $user_name . " is deleted";

    //     // Log the data to the logs table
    //     Log::create([
    //         'user_id' => $user_id,
    //         'user_type' => $user_type,
    //         'message' => $message,
    //         'query' => $last_query,
    //         'created_at' => now(),
    //         'updated_at' => now()
    //     ]);

    //     if ($request == true) {
    //         return back()->with('success', 'Request successfully deleted.');
    //     } else {
    //         return back()->with('error', 'Request failed to deleted.');
    //     }
    // }

    //This will submit the requested items
    public function submitRequest(Request $request)
    {
        $requested = $request->input('requestedItems');
        $request_by = $request->input('requestBy');
        $patient_name = $request->input('patientName');
        $patient_age = $request->input('patientAge');
        $patient_gender = $request->input('patientGender');
        $doctor_name = $request->input('doctorName');
        $requestedItems = json_decode($requested);

        //Enable Query Log
        DB::enableQueryLog();

        //get user details
        $user = Auth::user();
        //user id
        $userId = $user->id;
        //user type
        $userType = $user->type;
        //we use user name as office name
        $office = $user->name;

        $requestModel = new ModelsRequest();
        $requestModel->user_id = $userId;
        $requestModel->office = $office;
        //we always send the request to pharmacy
        $requestModel->request_to = 'pharmacy';
        $requestModel->request_by = ucfirst($request_by);
        $requestModel->patient_name = ucfirst($patient_name);
        $requestModel->age = filled($patient_age) ? $patient_age : 0;
        $requestModel->gender = filled($patient_gender) ? $patient_gender : "-";
        $requestModel->doctor_name = ucfirst($doctor_name);
        $requestModel->save();

        $requestID = $requestModel->id;

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
        foreach ($requestedItems as $item) {
            $model = new Request_Item();
            $model->request_id = $requestModel->id;
            $model->item_id = $item->item_id;
            $model->stock_id = $item->stock_id;
            $model->mode_acquisition = $item->mode_acquisition;
            $model->exp_date = $item->exp_date;
            $model->quantity = intval($item->quantity);
            $model->save();


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
            // var_dump($model->exp_date);

            //Stock reserving
            $requestedQty = $model->quantity;

            $itemStock = Stock::find($model->stock_id);

            //minus the requested quantity to current stocks
            $newStock = $itemStock->stock_qty - $requestedQty;
            $itemStock->stock_qty = $newStock;
            $itemStock->save();

            if ($itemStock->stock_qty === 0) {
                $itemStock->delete();
            }

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
        }

        return response()->json([
            'success' => true,
            'request_id' => $requestModel->id,
            'requestedQty' => $requestedQty,
            'itemStock' => $itemStock,
        ]);
    }

    //cancel request
    public function cancelRequest(Request $request, $rid)
    {
        //Enable Query Log
        DB::enableQueryLog();

        //get user details
        $user = Auth::user();
        $userId = $user->id;
        $userType = $user->type;

        $requestItems = Request_Item::where("request_id", $rid)->get();

        //get the reason of cancelation
        $cancelReason = $request->input("canceled_reason");

        foreach ($requestItems as $item) {
            //get the requested items details
            $stockId = $item->stock_id;
            $itemId =  $item->item_id;
            $quantity = $item->quantity;

            //get the current stock details
            $stock = Stock::where("id", $stockId)->first();
            $stockQty = Stock::select("stock_qty")->where("id", $stockId)->first();

            //item quantity to return
            $stock->stock_qty += $quantity;
            $stock->save();

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

        $canceledModel =  new Canceled_Request();
        $canceledModel->request_id = $rid;
        $canceledModel->reason = $cancelReason;
        $canceledModel->save();

        $theRequest = ModelsRequest::where("id", $rid)->first();
        $theRequest->status = "canceled";
        $theRequest->save();

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

        if ($stock) {
            return back()->with("success", "The request is successfully canceled.");
        } else {
            return back()->with("error", "The request is failed to canceled.");
        }
    }


    //this will mark status as received
    public function receiveRequest(Request $request, $rid)
    {
        //Enable Query Log
        DB::enableQueryLog();

        $user = Auth::user();

        $user_id = $user->id;
        $user_type = $user->type;
        $user_name = $user->name;

        $receiverName = $request->receiver_name;

        $request = ModelsRequest::find($rid);
        if ($request) {
            $request->receiver = $receiverName;
            $request->status = 'completed';
            $request->save();

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
}
