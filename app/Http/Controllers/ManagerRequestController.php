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

class ManagerRequestController extends Controller
{
    //show requests
    public function managerRequest()
    {
        return view('manager.requests');
    }

    public function showPending()
    {
        return view('manager.sub-page.requests.show-pending');
    }

    public function showPendingRequest()
    {
        $user = Auth::user();
        $user_id = $user->id;
        $user_name = $user->name;
        $user_type = $user->type;

        $pending = ModelsRequest::where('status', 'pending')
            ->orderByDesc('updated_at')
            ->get()->each(function ($pending) {
                $pending->formatted_date = Carbon::parse($pending->created_at)
                    ->format('F j, Y, g:i:s a');
            });

        $requestCount = ModelsRequest::where('status', 'pending')
            ->count();

        return response()->json([
            'pending' => $pending,
            'pendingCount' => $requestCount,
        ]);
    }

    public function showAcceptedRequest()
    {
        $user = Auth::user();
        $user_id = $user->id;
        $user_name = $user->name;
        $user_type = $user->type;

        if ($user_type === 'manager') {
            $requests = ModelsRequest::where('status', 'accepted')
                ->where('accepted_by_user_id', $user_id)
                ->orderByDesc('updated_at')
                ->get()->each(function ($pending) {
                    $pending->formatted_date = Carbon::parse($pending->created_at)
                        ->format('F j, Y, g:i:s a');
                });

            $requestCount = ModelsRequest::where('status', 'accepted')
                ->where('accepted_by_user_id', $user_id)
                ->count();
        } else {
            $requests = ModelsRequest::where('status', 'accepted')
                ->orderByDesc('updated_at')
                ->get()->each(function ($pending) {
                    $pending->formatted_date = Carbon::parse($pending->created_at)
                        ->format('F j, Y, g:i:s a');
                });
        }


        return response()->json([
            'accepted' => $requests,
            'acceptedCount' => $requestCount,
        ]);
        // return back()->with(['requests' => $requests]);
    }

    public function showDeliveredRequest()
    {
        $user = Auth::user();
        $user_id = $user->id;
        $user_name = $user->name;
        $user_type = $user->type;

        if ($user_type === 'manager') {
            $requests = ModelsRequest::where('status', 'delivered')
                ->where('accepted_by_user_id', $user_id)
                ->orderByDesc('updated_at')
                ->get()->each(function ($pending) {
                    $pending->formatted_date = Carbon::parse($pending->created_at)
                        ->format('F j, Y, g:i:s a');
                });

            $requestCount = ModelsRequest::where('status', 'delivered')
                ->where('accepted_by_user_id', $user_id)
                ->count();
        } else {
            $requests = ModelsRequest::where('status', 'delivered')
                ->orderByDesc('updated_at')
                ->get()->each(function ($pending) {
                    $pending->formatted_date = Carbon::parse($pending->created_at)
                        ->format('F j, Y, g:i:s a');
                });
        }


        return response()->json([
            'delivered' => $requests,
            'deliveredCount' => $requestCount,
        ]);
    }

    //this will show the requested items
    public function requestedItems($id)
    {
        $user = Auth::user();
        $user_id = $user->id;
        $user_name = $user->name;
        $user_type = $user->type;

        $request = ModelsRequest::where('id', $id)->first();

        $request->formatted_date =
            Carbon::parse($request->created_at)->format('F j, Y, g:i:s a');

        $items = Request_Item::where('request_id', $id)->get();

        $requestItems = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            // ->join('item_stocks', 'request_items.stock_id', '=', 'item_stocks.id')
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

    public function viewRequest($id)
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

        $canceled = Canceled_Request::where('request_id', $id)->first();

        if ($canceled) {
            $canceled->format_date = Carbon::parse($canceled->created_at)->format('F j, Y, g:i:s a');

            return view('manager.sub-page.requests.view-request')->with([
                'request' => $request,
                'items' => $items,
                'requestItems' => $requestItems,
                'canceled' => $canceled,
            ]);
        } else {
            return view('manager.sub-page.requests.view-request')->with([
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
            $request->accepted_by_user_id = $user_id;
            $request->accepted_by_user_name = $user_name;
            $request->status = 'accepted';
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

            return back()->with('success', 'Request delivered');
        } else {
            return back()->with('error', 'Request failed to mark as delivered');
        }
    }
}
