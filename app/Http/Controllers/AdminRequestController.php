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
    //this will show all the requests
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
        $pending = ModelsRequest::where('status', 'pending')
            ->orderByDesc('updated_at')->get()->each(function ($pending) {
                $pending->formatted_date = Carbon::parse($pending->created_at)->format('F j, Y, g:i:s a');
            });
        $completed = ModelsRequest::where('status', 'completed')
            ->orderByDesc('updated_at')->get()->each(function ($accepted) {
                $accepted->formatted_date = Carbon::parse($accepted->updated_at)->format('F j, Y, g:i:s a');
            });

        $requests = [
            'pending' => $pending,
            'completed' => $completed,

        ];

        return response()->json($requests);
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
