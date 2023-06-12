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

    //this will show the request view
    public function adminRequest()
    {
        return view('admin.request');
    }

    public function showRequest()
    {
        $pending = ModelsRequest::where('status', '!=', 'completed')->where('status', '!=', 'delivered')
            ->orderByDesc('updated_at')->get()->each(function ($pending) {
                $pending->formatted_date = Carbon::parse($pending->created_at)->format('F j, Y, g:i:s a');
            });

        $requests = [
            'pending' => $pending,

        ];

        return response()->json($requests);
    }

    public function showPendingRequest()
    {
        $requests = ModelsRequest::where('status', 'pending')
            ->orderByDesc('updated_at')
            ->get();

        foreach ($requests as $request) {
            $request->formatted_date = Carbon::parse($request->updated_at)->format('F j, Y, g:i:s a');
        }

        $requestCount = ModelsRequest::where('status', 'pending')
            ->count();

        DB::connection()->commit();

        return response()->json([
            'pending' => $requests,
            'pendingCount' => $requestCount,
        ]);
    }

    public function showAcceptedRequest()
    {
        $requests = ModelsRequest::where('status', 'accepted')
            ->orderByDesc('updated_at')
            ->get();

        foreach ($requests as $request) {
            $request->formatted_date = Carbon::parse($request->updated_at)->format('F j, Y, g:i:s a');
        }

        $requestCount = ModelsRequest::where('status', 'accepted')
            ->count();

        DB::connection()->commit();

        return response()->json([
            'accepted' => $requests,
            'acceptedCount' => $requestCount,
        ]);
        // return back()->with(['requests' => $requests]);
    }

    public function showDeliveredRequest()
    {
        $requests = ModelsRequest::where('status', 'delivered')
            ->orderByDesc('updated_at')
            ->get();

        foreach ($requests as $request) {
            $request->formatted_date = Carbon::parse($request->updated_at)->format('F j, Y, g:i:s a');
        }


        $requestCount = ModelsRequest::where('status', 'delivered')
            ->count();

        DB::connection()->commit();

        return response()->json([
            'delivered' => $requests,
            'deliveredCount' => $requestCount,
        ]);
    }

    //this will show the requested items
    public function requestedItems($id)
    {
        $request = ModelsRequest::where('id', $id)->first();
        $request->formatted_date =
            Carbon::parse($request->created_at)->format('F j, Y, g:i:s a');

        $items = Request_Item::where('request_id', $id)->get();

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

    public function viewRequest($id)
    {
        $request = ModelsRequest::where('id', $id)->first();
        $request->formatted_date = Carbon::parse($request->created_at)->format('F j, Y, g:i:s a');

        $items = Request_Item::where('request_id', $id)->get();

        $requestItems = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->select('request_items.*', 'items.*')
            ->where('request_items.request_id', $id)
            ->orderBy('request_items.created_at', 'asc')
            ->get();

        $canceled = Canceled_Request::where('request_id', $id)->first();

        if ($canceled) {
            $canceled->format_date = Carbon::parse($canceled->created_at)->format('F j, Y, g:i:s a');

            return view('admin.sub-page.requests.view-request')->with([
                'request' => $request,
                'items' => $items,
                'requestItems' => $requestItems,
                'canceled' => $canceled,
            ]);
        } else {
            return view('admin.sub-page.requests.view-request')->with([
                'request' => $request,
                'items' => $items,
                'requestItems' => $requestItems,
            ]);
        }
    }

    //this will change the status of request to accepted
    public function acceptRequest($rid)
    {
        //Enable Query Log
        DB::enableQueryLog();

        $request = ModelsRequest::find($rid);

        $user = Auth::user();

        $user_id = $user->id;
        $user_type = $user->type;
        $user_name = $user->name;


        if ($request) {
            $request->status = 'accepted';
            $request->accepted_by_user_name = $user_name;
            $request->save();

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

            return back()->with('success', 'Request accepted');
        } else {
            return back()->with('error', 'Request failed to mark as accepted');
        }
    }

    //this will change the status of request to accepted
    public function deliverRequest($rid)
    {
        //Enable Query Log
        DB::enableQueryLog();

        $request = ModelsRequest::find($rid);

        $user = Auth::user();

        $user_id = $user->id;
        $user_type = $user->type;
        $user_name = $user->name;


        if ($request) {
            $request->status = 'delivered';
            $request->save();

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

            return redirect()->route('admin.requests')->with('success', 'Request delivered');
        } else {
            return back()->with('error', 'Request failed to mark as delivered');
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
}
