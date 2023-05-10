<?php

namespace App\Http\Controllers;

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
        // $pending = ModelsRequest::where('status', 'pending')
        //     ->orderByDesc('updated_at')->get()->each(function ($pending) {
        //         $pending->formatted_date = Carbon::parse($pending->updated_at)->format('F j, Y, g:i:s a');
        //     });
        // $accepted = ModelsRequest::where('status', 'accepted')
        //     ->orderByDesc('updated_at')->get()->each(function ($accepted) {
        //         $accepted->formatted_date = Carbon::parse($accepted->updated_at)->format('F j, Y, g:i:s a');
        //     });
        // $delivered = ModelsRequest::where('status', 'delivered')
        //     ->orderByDesc('updated_at')->get()->each(function ($accepted) {
        //         $accepted->formatted_date = Carbon::parse($accepted->updated_at)->format('F j, Y, g:i:s a');
        //     });
        // $completed = ModelsRequest::where('status', 'completed')
        //     ->orderByDesc('updated_at')->get()->each(function ($accepted) {
        //         $accepted->formatted_date = Carbon::parse($accepted->updated_at)->format('F j, Y, g:i:s a');
        //     });


        return view('admin.request');
    }

    public function showRequest()
    {
        $pending = ModelsRequest::where('status', '!=', 'completed')->where('status', '!=', 'delivered')
            ->orderByDesc('updated_at')->get()->each(function ($pending) {
                $pending->formatted_date = Carbon::parse($pending->created_at)->format('F j, Y, g:i:s a');
            });
        // $completed = ModelsRequest::where('status', 'completed')
        //     ->orderByDesc('updated_at')->get()->each(function ($accepted) {
        //         $accepted->formatted_date = Carbon::parse($accepted->updated_at)->format('F j, Y, g:i:s a');
        //     });

        $requests = [
            'pending' => $pending,
            // 'completed' => $completed,

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
            // ->join('item_stocks', 'request_items.stock_id', '=', 'item_stocks.id')
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

    public function generate_receipt($rid)
    {
        $user = Auth::user();
        $user_name = $user->name;
        $request = ModelsRequest::find($rid);
        $items = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->where('request_id', $rid)->get();
        $total_amount = 0; // initialize total amount to 0
        foreach ($items as $item) {
            $stock = Stock::select('stock_qty')
                ->where('id', $item->stock_id)
                ->first();
            $item->remaining = empty($stock->stock_qty) ? "0" : $stock->stock_qty;
            $item->amount = number_format($item->quantity * $item->price, 2);
            $total_amount += $item->quantity * $item->price; // add item amount to total amount
        }
        $total_amount = number_format($total_amount, 2); // format total amount with 2 decimal places
        return view("pdf.request")->with([
            'request' => $request,
            'items' => $items,
            'total_amount' => $total_amount, // pass total amount to view
        ]);
    }


    //Request transaction
    public function transaction()
    {
        return view('admin.sub-page.transaction.transaction');
    }

    //get transactions/request data
    public function showTransaction()
    {
        $transc = ModelsRequest::where('status', 'completed')->orderBy('updated_at', 'desc')->get();

        foreach ($transc as $trans) {
            $trans->formatted_date =
                Carbon::parse($trans->updated_at)->format('F j, Y, g:i:s a');
        }

        return response()->json($transc);
    }

    //filter transaction
    public function filterTransaction(Request $request)
    {
        $today = $request->input('today');
        $yesterday = $request->input('yesterday');
        $thisMonth = $request->input('this-month');


        if ($today) {
            // Filter transactions that occurred today
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();

            $data = ModelsRequest::where('status', 'completed')
                ->whereBetween('updated_at', [$from, $to])
                ->orderBy('updated_at', 'desc')
                ->get();
        } else if ($yesterday) {
            // Filter transactions that occurred today
            $from = Carbon::yesterday()->startOfDay();
            $to = Carbon::yesterday()->endOfDay();

            $data = ModelsRequest::where('status', 'completed')
                ->whereBetween('updated_at', [$from, $to])
                ->orderBy('updated_at', 'desc')
                ->get();
        } else if ($thisMonth) {
            // Filter transactions that occurred today
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfMonth();

            $data = ModelsRequest::where('status', 'completed')
                ->whereBetween('updated_at', [$from, $to])
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {

            $from = $request->input('from');
            $to = $request->input('to');
            $date_to = date('Y-m-d', strtotime($to . ' +1 day'));

            $data = ModelsRequest::where('status', 'completed')
                ->whereBetween('updated_at', [$from, $date_to])
                ->orderBy('updated_at', 'desc')
                ->get();
        }


        // Loop through the data and format the created_at timestamp
        foreach ($data as $item) {
            $item->formatted_date = date('F j, Y, g:i:s a', strtotime($item->updated_at));
        }

        return response()->json($data);
    }
}
