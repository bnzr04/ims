<?php

namespace App\Http\Controllers;

use App\Models\Canceled_Request;
use App\Models\Log;
use App\Models\Request as ModelsRequest;
use App\Models\Request_Item;
use App\Models\Stock;
use App\Models\Stock_Log;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ManagerRequestController extends Controller
{

    //show requests
    public function managerRequest() //this function will return the manager request view
    {
        return view('manager.requests');
    }

    public function showPending() //this function will return manager show-pending view this tis the iframe in the manager dashboard
    {
        return view('manager.sub-page.requests.show-pending');
    }

    public function showPendingRequest() //this function will retrieve all the pending request and return the data in jason format
    {
        //retrieve all the pending request and order the updated_at as descending
        $pending = ModelsRequest::where('status', 'pending')
            ->orderByDesc('updated_at')
            ->get()->each(function ($pending) {
                $pending->formatted_date = Carbon::parse($pending->created_at)
                    ->format('F j, Y, g:i:s a'); //format the created_at to a readble format
            });

        //get the count of pending request
        $requestCount = ModelsRequest::where('status', 'pending')
            ->count();

        //return the pending requests and the pending count
        return response()->json([
            'pending' => $pending,
            'pendingCount' => $requestCount,
        ]);
    }

    public function showAcceptedRequest() //this function will retrieve all the accepted request and return the data in json format
    {
        $user = Auth::user(); //get the authenticated user information
        $user_id = $user->id; //get the user id

        //retrieve all the accepted request and order the updated_at as descending
        $requests = ModelsRequest::where('status', 'accepted')
            ->where('accepted_by_user_id', $user_id)
            ->orderByDesc('updated_at')
            ->get()->each(function ($pending) {
                $pending->formatted_date = Carbon::parse($pending->created_at)
                    ->format('F j, Y, g:i:s a'); //format the created_at to a readble format
            });

        //get the count of accepted requests accepted by the authenticated user
        $requestCount = ModelsRequest::where('status', 'accepted')
            ->where('accepted_by_user_id', $user_id)
            ->count();

        return response()->json([
            'accepted' => $requests,
            'acceptedCount' => $requestCount,
        ]);
    }

    public function showDeliveredRequest() //this function will retrieve all the delivered request and return the data in json format
    {
        $user = Auth::user(); //get the authenticated user information
        $user_id = $user->id; //get the user id

        //retrieve all the delivered request and order the updated_at as descending
        $requests = ModelsRequest::where('status', 'delivered')
            ->where('accepted_by_user_id', $user_id)
            ->orderByDesc('updated_at')
            ->get()->each(function ($pending) {
                $pending->formatted_date = Carbon::parse($pending->created_at)
                    ->format('F j, Y, g:i:s a'); //format the created_at to a readble format
            });

        //get the count of delivered requests accepted by the authenticated user
        $requestCount = ModelsRequest::where('status', 'delivered')
            ->where('accepted_by_user_id', $user_id)
            ->count();

        //return the accepted requests and the accepted count in json format
        return response()->json([
            'delivered' => $requests,
            'deliveredCount' => $requestCount,
        ]);
    }


    public function requestedItems($id) //this will return the requested-items view with the request, items and requested items information, the parameter $id is the request id
    {
        $request = ModelsRequest::where('id', $id)->first(); //get the request information by request id

        $request->formatted_date =
            Carbon::parse($request->created_at)->format('F j, Y, g:i:s a'); //format the created_at to a readable format

        $items = Request_Item::where('request_id', $id)->get(); //get all the requested items of the request

        //join the request_items and items table to get the requested items information by the request id
        $requestItems = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->select('request_items.*', 'items.*')
            ->where('request_items.request_id', $id)
            ->orderBy('request_items.created_at', 'asc')
            ->get();

        return view('manager.sub-page.requests.requested-items')->with([
            'request' => $request,
            'items' => $items,
            'requestItems' => $requestItems,

        ]);
    }

    public function viewRequest($id) //this function will return the view-request view with the request information and the requested items, the parameter $id is the request id
    {
        $request = ModelsRequest::where('id', $id)->first(); //retrieve the request information
        $request->formatted_date =
            Carbon::parse($request->created_at)->format('F j, Y, g:i:s a'); //format the request created_at to a readble format

        $items = Request_Item::where('request_id', $id)->get(); //get the requested items

        //join the request_items and items table and get the items information
        $requestItems = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->select('request_items.*', 'items.*')
            ->where('request_items.request_id', $id)
            ->orderBy('request_items.created_at', 'asc')
            ->get();

        $canceled = Canceled_Request::where('request_id', $id)->first(); //this will get the cancelation information if the request is canceled

        if ($canceled) {
            $canceled->format_date = Carbon::parse($canceled->created_at)->format('F j, Y, g:i:s a'); //format the created_at to a readable format

            //return the view-request with the request, requested items, items and canceled information
            return view('manager.sub-page.requests.view-request')->with([
                'request' => $request,
                'items' => $items,
                'requestItems' => $requestItems,
                'canceled' => $canceled,
            ]);
        } else {
            //return the view-request with the request, requested items and items information
            return view('manager.sub-page.requests.view-request')->with([
                'request' => $request,
                'items' => $items,
                'requestItems' => $requestItems,
            ]);
        }
    }

    public function acceptRequest($rid) //this function will accept the request, the parameter $rid is the request id
    {
        //Enable Query Log
        DB::enableQueryLog();

        $request = ModelsRequest::find($rid); //find the request information by $rid parameter

        $user = Auth::user(); //get the authenticated user information

        $user_id = $user->id; //get the user id
        $user_type = $user->type; //get the user type
        $user_name = $user->name; //get the user name


        if ($request) { //if the $request is true or exist
            $request->accepted_by_user_id = $user_id; //add a value of the user id who accept the request
            $request->accepted_by_user_name = $user_name; //add a value of the user name who accept the request
            $requestStatus = $request->status; //store request status value

            $requestedItems = Request_Item::where('request_id', $rid)
                ->get();

            foreach ($requestedItems as $item) {
                $item_id = $item->item_id;
                $quantity = $item->quantity;
                $stock_id = $item->stock_id;
                $mode_acquisition = $item->mode_acquisition;

                //Log the deduction to stock_logs table 
                $stockLog = new Stock_Log(); //get the stock_log table
                $stockLog->stock_id = $stock_id; //store the $stock_id value to 'stock_id' in the stock_log table
                $stockLog->item_id = $item_id; //store the $item_id value to 'item_id' in the stock_log table
                $stockLog->quantity = $quantity; //store the $quantity value to 'quantity' in the stock_log table
                $stockLog->mode_acquisition = $mode_acquisition; //store the $mode_acquisition value to 'mode_acquisition' in the stock_log table
                $stockLog->transaction_type = 'accepted'; //store the 'accepted' to 'transaction_type' in the stock_log table

                $currentStockQuantity = Stock::where('item_id', $item_id)
                    ->where('status', 'active')
                    ->sum('stock_qty'); //store the sum of the current stock quantity if the item

                $oldStockQuantity = Request_Item::where('request_id', $item->request_id)
                    ->where('item_id', $item_id)
                    // ->where('status', 'active')
                    ->sum('quantity'); //store the sum of the item requested quantity

                $oldStockQuantity = $currentStockQuantity + $oldStockQuantity; //sum the requested item quantity as $oldStockQuantity to $currentStockQuantity

                $stockLog->current_quantity = $currentStockQuantity; //store the $currentStockQuantity value to 'current_quantity' in the stock_log table
                $stockLog->prev_quantity = $oldStockQuantity; //store the $oldStockQuantity value to 'prev_quantity' in the stock_log table
                $stockLog->save();
            }


            if ($requestStatus == 'canceled') { //if request status marked as 'canceled'
                return back()->with('error', 'Request is already canceled by the requester.'); //return to current view with error message
            } else {
                $request->status = 'accepted'; //update the request status to 'accepted'
            }

            $request->save(); //save or update the request information

            //Get Query
            $sql = DB::getQueryLog();

            if (is_array($sql) && count($sql) > 0) {
                $last_query = end($sql)['query'];
            } else {
                $last_query = 'No query log found.';
            }

            //Log Message
            $message = "Request ID: " . $rid . ", request is accepted.";

            // Log the data to the logs table
            Log::create([
                'user_id' => $user_id,
                'user_type' => $user_type,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return back()->with('success', 'Request accepted'); //return to the current view with the success message
        } else {
            return back()->with('error', 'Request failed to mark as accepted');  //return to the current view with the error message
        }
    }

    //this will change the status of request to accepted
    public function completeRequest($rid)
    {
        //Enable Query Log
        DB::enableQueryLog();

        $request = ModelsRequest::find($rid);

        $user = Auth::user();

        $user_id = $user->id;
        $user_type = $user->type;
        $user_name = $user->name;


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
            $message = "Request ID: " . $rid . ", request is delivered and mark as completed.";

            // Log the data to the logs table
            Log::create([
                'user_id' => $user_id,
                'user_type' => $user_type,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->route('admin.requests')->with('success', 'Request Completed');
        } else {
            return back()->with('error', 'Request failed to mark as complete');
        }
    }


    public function deliverRequest($rid) //this function will update the request status to delivered, the parameter $rid is the request id
    {
        //Enable Query Log
        DB::enableQueryLog();

        $request = ModelsRequest::find($rid); //find the request information by $rid parameter

        $user = Auth::user(); //get the authenticated user information

        $user_id = $user->id; //get the user id
        $user_type = $user->type; //get the user type
        $user_name = $user->name; //get the user name


        if ($request) { //if the $request is true or exist

            $requestStatus = $request->status; //store request status value

            if ($requestStatus == 'canceled') { //if request status marked as 'canceled'
                return back()->with('error', 'Request is already canceled by the requester.'); //return to current view with error message
            } else {
                $request->status = 'delivered'; //update the request status to 'delivered'
            }

            $request->save(); //save or update the request information

            //Get Query
            $sql = DB::getQueryLog();

            if (is_array($sql) && count($sql) > 0) {
                $last_query = end($sql)['query'];
            } else {
                $last_query = 'No query log found.';
            }

            //Log Message
            $message = "Request ID: " . $rid . ", request is delivered.";

            // Log the data to the logs table
            Log::create([
                'user_id' => $user_id,
                'user_type' => $user_type,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return back()->with('success', 'Request delivered'); //return current view with success message
        } else {
            return back()->with('error', 'Request failed to mark as delivered'); //return current view with error message
        }
    }
}
