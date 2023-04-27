<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Log;
use App\Models\Request as ModelsRequest;
use App\Models\Request_Item;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            ->select('items.name', 'items.category', 'items.unit', 'item_stocks.*')
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

    public function newRequest(Request $request)
    {
        $items = Item::query();
        $searchItem = $request->search_item;

        if ($searchItem !== null) {
            $items =
                $items->where(function ($query) use ($searchItem) {
                    $query->where('name', 'like', '%' . $searchItem . '%')
                        ->orWhere('id', $searchItem);
                })->get();
        }

        return view('user.sub-page.new-request')->with(['items' => $items, 'search_item' => $searchItem]);
    }

    public function userRequest(Request $request)
    {
        $status = $request->query('request');
        $userId = Auth::id();

        if ($status) {
            $requests = ModelsRequest::where('user_id', $userId)
                ->where('status', $status)
                ->each(function ($request) {
                    $request->formatted_created_at = Carbon::parse($request->created_at)->format('F j, Y, g:i:s a');
                })
                ->orderBy('created_at', 'asc')
                ->get();

            $pending = ModelsRequest::where('user_id', $userId)
                ->where('status', 'pending')
                ->count('user_id');
            $accepted = ModelsRequest::where('user_id', $userId)
                ->where('status', 'accepted')
                ->count('user_id');
            $delivered = ModelsRequest::where('user_id', $userId)
                ->where('status', 'delivered')
                ->count('user_id');
            $completed = ModelsRequest::where('user_id', $userId)
                ->where('status', 'completed')
                ->count('user_id');
            $notcompleted = ModelsRequest::where('user_id', $userId)
                ->where('status', 'not completed')
                ->count('user_id');
        } else {
            $requests = ModelsRequest::where('user_id', $userId)
                ->where('status', 'pending')
                ->each(function ($request) {
                    $request->formatted_created_at = Carbon::parse($request->created_at)->format('F j, Y, g:i:s a');
                })
                ->orderBy('created_at', 'asc')
                ->get();

            $pending = ModelsRequest::where('user_id', $userId)
                ->where('status', 'pending')
                ->count('user_id');
            $accepted = ModelsRequest::where('user_id', $userId)
                ->where('status', 'accepted')
                ->count('user_id');
            $delivered = ModelsRequest::where('user_id', $userId)
                ->where('status', 'delivered')
                ->count('user_id');
            $completed = ModelsRequest::where('user_id', $userId)
                ->where('status', 'completed')
                ->count('user_id');
            $notcompleted = ModelsRequest::where('user_id', $userId)
                ->where('status', 'not completed')
                ->count('user_id');
        }

        return view('user.my-request')->with([
            'requests' => $requests,
            'status' => $status,
            'pending' => $pending,
            'accepted' => $accepted,
            'delivered' => $delivered,
            'completed' => $completed,
            'notcompleted' => $notcompleted,
        ]);
    }

    public function viewRequest($request)
    {
        $user_id = Auth::user()->id;

        if ($request === 'all') {
            $items = ModelsRequest::where('user_id', $user_id)
                ->where('status', '!=', 'completed')
                ->orderBy('created_at', 'desc')
                ->get();
        } else if ($request === 'completed') {
            $items = ModelsRequest::where('user_id', $user_id)
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        foreach ($items as $item) {
            $item->formatted_date = Carbon::parse($item->created_at)->format('F j, Y, g:i:s a');
        }


        return view('user.sub-page.view-request')->with([
            'items' => $items,
            'request' => $request
        ]);
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

        // foreach ($requestItems as $item) {
        // $exp_date = Carbon::createFromFormat('Y-m-d', $item->exp_date);
        // $item->exp_date = $exp_date->format('m-d-Y');
        // }

        if ($requested->request_to === 'pharmacy') {

            $items =
                DB::table('item_stocks')
                ->join('items', 'item_stocks.item_id', '=', 'items.id')
                ->select('items.name', 'item_stocks.*')
                ->where('item_stocks.exp_date', ">", $today)
                ->orderBy('items.name', 'asc')
                ->get();
        } else {
            $items =
                DB::table('item_stocks')
                ->join('items', 'item_stocks.item_id', '=', 'items.id')
                ->select('items.name', 'item_stocks.*')
                ->where('item_stocks.exp_date', ">", $today)
                ->orderBy('items.name', 'asc')
                ->get();
        }

        // foreach ($items as $item) {
        //     $exp_date = Carbon::createFromFormat('Y-m-d', $item->exp_date);
        //     $item->formatted_exp_date = $exp_date->format('m-d-Y');
        // }


        return view('user.sub-page.view-items')->with([
            'requestItems' => $requestItems,
            'request' => $requested,
            'items' => $items,
        ]);
    }

    public function saveRequest(Request $request)
    {
        //Enable Query log
        DB::enableQueryLog();

        $model = new ModelsRequest;

        $model->user_id = $request->user_id;
        $model->office = $request->office;
        $model->request_to = $request->request_to;
        $model->save();


        //QUERY LOG
        $user = auth()->user();

        $user_id = $user->id; // Get the ID of the authenticated user
        $user_type = $user->type; // Get the type of the authenticated user
        $user_name = $user->name; // Get the name of the authenticated user


        // Get the SQL query being executed
        $sql = DB::getQueryLog();
        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
            $newStockID = $model->id;
        } else {
            $last_query = 'No query log found.';
        }

        //Log Message
        $message = "New request created (ID: " . $newStockID . ")";

        // Log the data to the logs table
        Log::create([
            'user_id' => $user_id,
            'user_type' => $user_type,
            'message' => $message,
            'query' => $last_query,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('user.request-items', ['id' => $newStockID])->with('success', 'Request successfully created');
    }

    //This function will add item to request
    public function addItem(Request $request)
    {
        $table = new Request_Item;

        $query = Request_Item::where('request_id', $request->request_id)
            ->where('item_id', $request->nameSearch)
            ->where('stock_id', $request->stock_id)
            ->first();

        if ($query) {
            return back()->with('error', 'Item stock already added.');
        } else {
            $table->request_id = $request->request_id;
            $table->item_id = $request->nameSearch;
            $table->stock_id = $request->stock_id;
            $table->mode_acquisition = $request->mode_acquisition;
            $table->exp_date = $request->exp_date;
            $table->quantity = $request->quantity;

            $quantity = $request->quantity;

            $available =
                DB::table('item_stocks')
                ->join('items', 'item_stocks.item_id', '=', 'items.id')
                ->select('item_stocks.stock_qty')
                ->where('item_stocks.id', $request->stock_id)
                ->where('item_stocks.item_id', $request->nameSearch)->first();

            if (!empty($available)) {
                $stock = $available->stock_qty;
                if ($quantity > $stock) {
                    return back()->with('warning', 'The available stocks are not enough for your demand.');
                } else {
                    $table->save();
                    return back()->with('success', 'Item successfully added.');
                }
            } else {
                return back()->with('error', 'Item failed to request.');
            }
        }
    }

    public function removeItem($sid, $id)
    {
        $items = Request_Item::where('stock_id', $sid)->where('item_id', $id);

        $items->delete();

        return back()->with('success', 'Item successfully removed to requested item.');
    }


    public function deleteRequest($id)
    {
        $request = ModelsRequest::find($id);

        //Enable Query log
        DB::enableQueryLog();

        $request->delete();

        //QUERY LOG
        $user = auth()->user();

        $user_id = $user->id; // Get the ID of the authenticated user
        $user_type = $user->type; // Get the type of the authenticated user
        $user_name = $user->name; // Get the name of the authenticated user


        // Get the SQL query being executed
        $sql = DB::getQueryLog();
        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
        } else {
            $last_query = 'No query log found.';
        }

        //Log Message
        $message = "Request (ID: " . $id . ") requested by " . $user_name . " is deleted";

        // Log the data to the logs table
        Log::create([
            'user_id' => $user_id,
            'user_type' => $user_type,
            'message' => $message,
            'query' => $last_query,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if ($request == true) {
            return back()->with('success', 'Request successfully deleted.');
        } else {
            return back()->with('error', 'Request failed to deleted.');
        }
    }

    //This will submit the requested items
    public function submitRequest(Request $request)
    {
        $requested = $request->input('requestedItems');
        $request_by = $request->input('requestBy');
        $patient_name = $request->input('patientName');
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


    //this will mark status as received
    public function receiveRequest($rid)
    {
        //Enable Query Log
        DB::enableQueryLog();

        $user = Auth::user();

        $user_id = $user->id;
        $user_type = $user->type;
        $user_name = $user->name;

        $request = ModelsRequest::find($rid);
        if ($request) {
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

            return back()->with('success', 'Request completed');
        } else {
            return back()->with('error', "Request doesn't exist.");
        }
    }
}
