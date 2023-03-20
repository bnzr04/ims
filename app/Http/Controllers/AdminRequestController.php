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
        $pending = ModelsRequest::where('status', 'pending')
            ->orderByDesc('updated_at')->get()->each(function ($pending) {
                $pending->formatted_date = Carbon::parse($pending->updated_at)->format('F j, Y, g:i:s a');
            });
        $accepted = ModelsRequest::where('status', 'accepted')
            ->orderByDesc('updated_at')->get()->each(function ($accepted) {
                $accepted->formatted_date = Carbon::parse($accepted->updated_at)->format('F j, Y, g:i:s a');
            });
        $delivered = ModelsRequest::where('status', 'delivered')
            ->orderByDesc('updated_at')->get()->each(function ($accepted) {
                $accepted->formatted_date = Carbon::parse($accepted->updated_at)->format('F j, Y, g:i:s a');
            });
        $completed = ModelsRequest::where('status', 'completed')
            ->orderByDesc('updated_at')->get()->each(function ($accepted) {
                $accepted->formatted_date = Carbon::parse($accepted->updated_at)->format('F j, Y, g:i:s a');
            });


        return view('admin.request')->with([
            'pending' => $pending,
            'accepted' => $accepted,
            'delivered' => $delivered,
            'completed' => $completed,
        ]);
    }

    //this will show the requested items
    public function requestedItems($id)
    {
        $request = ModelsRequest::where('id', $id)->first();
        $request->formatted_date =
            Carbon::parse($request->updated_at)->format('F j, Y, g:i:s a');

        $items = Request_Item::where('request_id', $id)->get();

        $requestItems = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->join('item_stocks', 'request_items.stock_id', '=', 'item_stocks.id')
            ->select('request_items.*', 'items.*', 'item_stocks.*')
            ->where('request_id', $id)
            ->orderBy('request_items.created_at', 'asc')
            ->get();

        foreach ($requestItems as $item) {
            $exp_date = Carbon::createFromFormat('Y-m-d', $item->exp_date);
            $item->exp_date = $exp_date->format('m-d-Y');
        }

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
            $request->save();

            //Get Query
            $sql = DB::getQueryLog();

            if (is_array($sql) && count($sql) > 0) {
                $last_query = end($sql)['query'];
            } else {
                $last_query = 'No query log found.';
            }

            //Log Message
            $message = "Request ID: " . $rid . ", request accepted.";

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
            return back()->with('error', 'Request failed to accept');
        }
    }

    //this will mark the status as delivered and will deduct the requested quantity to current stocks
    public function deliverRequest($rid)
    {
        //Enable Query Log
        DB::enableQueryLog();

        $request = ModelsRequest::find($rid);

        $user = Auth::user();

        $user_id = $user->id;
        $user_type = $user->type;
        $user_name = $user->name;


        $requestedItems = Request_Item::select('stock_id', 'quantity')
            ->where('request_id', $rid)->get();

        foreach ($requestedItems as $item) {
            $stock_id = $item->stock_id;
            $quantity = $item->quantity;

            $availableStocksQuery = Stock::find($stock_id);

            $availableStock = $availableStocksQuery->stock_qty;

            if ($quantity > $availableStock) {
                return back()->with('error', 'Requested quantity is greater than available stocks');
            } else {
                //stocks deductions
                $newStock = $availableStock - $quantity;
                $deductedValue = $availableStock - $newStock;
                $availableStocksQuery->update(['stock_qty' => $newStock]);

                if ($availableStocksQuery->stock_qty <= 0) {
                    $availableStocksQuery->delete();
                }

                if ($availableStocksQuery == true) {

                    //Get Query
                    $sql = DB::getQueryLog();

                    if (is_array($sql) && count($sql) > 0) {
                        $last_query = end($sql)['query'];
                    } else {
                        $last_query = 'No query log found.';
                    }

                    //Log Message
                    $message = "Stock id: " . $stock_id . ", deducted " . $deductedValue;

                    // Log the data to the logs table
                    Log::create([
                        'user_id' => $user_id,
                        'user_type' => $user_type,
                        'message' => $message,
                        'query' => $last_query,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    return back()->with('error', 'Stock quantity failed to update');
                }
            }
        }

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
            $message = "Request ID: " . $rid . ", successfully delivered";

            // Log the data to the logs table
            Log::create([
                'user_id' => $user_id,
                'user_type' => $user_type,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return back()->with('success', 'Requested items successfully delivered');
        }
    }
}
