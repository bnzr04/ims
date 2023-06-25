<?php

namespace App\Http\Controllers;

use App\Models\Canceled_Request;
use App\Models\Log;
use App\Models\Request as ModelsRequest;
use App\Models\Request_Item;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminRequestController extends Controller
{
    public function adminRequest() //this function will return the admin request view
    {
        return view('admin.request');
    }

    // public function showRequest() //this function 
    // {
    //     $pending = ModelsRequest::where('status', '!=', 'completed')->where('status', '!=', 'delivered')
    //         ->orderByDesc('updated_at')->get()->each(function ($pending) {
    //             $pending->formatted_date = Carbon::parse($pending->created_at)->format('F j, Y, g:i:s a');
    //         });

    //     $requests = [
    //         'pending' => $pending,

    //     ];

    //     return response()->json($requests);
    // }

    public function showPendingRequest() //this function will retrieve all the pending request and return the data in json format
    {
        //retrieve all the pending request and order the updated_at as descending
        $requests = ModelsRequest::where('status', 'pending')
            ->orderByDesc('updated_at')
            ->get();

        foreach ($requests as $request) {
            $request->formatted_date = Carbon::parse($request->updated_at)->format('F j, Y, g:i:s a'); //format the updated_at to a readble format
        }

        //get the count of pending request
        $requestCount = ModelsRequest::where('status', 'pending')
            ->count();

        //return the pending requests and the pending count
        return response()->json([
            'pending' => $requests,
            'pendingCount' => $requestCount,
        ]);
    }

    public function showAcceptedRequest() //this function will retrieve all the accepted request and return the data in json format
    {
        //retrieve all the accepted request and order the updated_at as descending
        $requests = ModelsRequest::where('status', 'accepted')
            ->orderByDesc('updated_at')
            ->get();

        foreach ($requests as $request) {
            $request->formatted_date = Carbon::parse($request->updated_at)->format('F j, Y, g:i:s a'); //format the updated_at to a readble format
        }

        //get the count of accepted request
        $requestCount = ModelsRequest::where('status', 'accepted')
            ->count();

        //return the pending requests and the accepted count
        return response()->json([
            'accepted' => $requests,
            'acceptedCount' => $requestCount,
        ]);
    }

    public function showDeliveredRequest() //this function will retrieve all the delivered request and return the data in json format
    {
        //retrieve all the request where the status is 'delivered' and order by descending the updated_at
        $requests = ModelsRequest::where('status', 'delivered')
            ->orderByDesc('updated_at')
            ->get();

        foreach ($requests as $request) {
            $request->formatted_date = Carbon::parse($request->updated_at)->format('F j, Y, g:i:s a'); //format the updated_at to a readble format
        }

        //get the count of all delivered request
        $requestCount = ModelsRequest::where('status', 'delivered')
            ->count();

        //return the pending requests and the delivered count
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


        return view('admin.sub-page.requests.requested-items')->with([
            'request' => $request,
            'items' => $items,
            'requestItems' => $requestItems,
        ]);
    }

    public function viewRequest($id) //this function will return the view-request view with the request information and the requested items, the parameter $id is the request id
    {
        $request = ModelsRequest::where('id', $id)->first(); //retrieve the request information
        $request->formatted_date = Carbon::parse($request->created_at)->format('F j, Y, g:i:s a'); //format the request created_at to a readble format

        $items = Request_Item::where('request_id', $id)->get(); //get the requested items

        //join the request_items and items table and get the items information
        $requestItems = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->select('request_items.*', 'items.*')
            ->where('request_items.request_id', $id)
            ->orderBy('request_items.created_at', 'asc')
            ->get();

        //this will get the cancelation information if the request is canceled
        $canceled = Canceled_Request::where('request_id', $id)->first();

        if ($canceled) { //if the request is canceled
            $canceled->format_date = Carbon::parse($canceled->created_at)->format('F j, Y, g:i:s a'); //format the created_at to a readable format

            //return the view-request with the request, requested items, items and canceled information
            return view('admin.sub-page.requests.view-request')->with([
                'request' => $request,
                'items' => $items,
                'requestItems' => $requestItems,
                'canceled' => $canceled,
            ]);
        } else {
            //return the view-request with the request, requested items and items information
            return view('admin.sub-page.requests.view-request')->with([
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
            $request->status = 'accepted'; //update the request status to 'accepted'
            $request->accepted_by_user_id = $user_id; //add a value of the user id who accept the request
            $request->accepted_by_user_name = $user_name; //add a value of the user name who accept the request
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
            $request->status = 'delivered'; //update the request status to 'delivered'
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

            return redirect()->route('admin.requests')->with('success', 'Request delivered'); //return to admin requests view with success message
        } else {
            return back()->with('error', 'Request failed to mark as delivered'); //return current view with error message
        }
    }

    //this will change the status of request to accepted
    // public function completeRequest($rid)
    // {
    //     //Enable Query Log
    //     DB::enableQueryLog();

    //     $request = ModelsRequest::find($rid);

    //     $user = Auth::user();

    //     $user_id = $user->id;
    //     $user_type = $user->type;
    //     $user_name = $user->name;


    //     if ($request) {
    //         $request->status = 'completed';
    //         $request->save();

    //         //Get Query
    //         $sql = DB::getQueryLog();

    //         if (is_array($sql) && count($sql) > 0) {
    //             $last_query = end($sql)['query'];
    //         } else {
    //             $last_query = 'No query log found.';
    //         }

    //         //Log Message
    //         $message = "Request ID: " . $rid . ", request is delivered and mark as completed.";

    //         // Log the data to the logs table
    //         Log::create([
    //             'user_id' => $user_id,
    //             'user_type' => $user_type,
    //             'message' => $message,
    //             'query' => $last_query,
    //             'created_at' => now(),
    //             'updated_at' => now()
    //         ]);

    //         return redirect()->route('admin.requests')->with('success', 'Request Completed');
    //     } else {
    //         return back()->with('error', 'Request failed to mark as complete');
    //     }
    // }
}
