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

class ManagerRequestController extends Controller
{
    //show requests
    public function userRequest()
    {
        $user = Auth::user();

        $userType = $user->type;
        $userDept = $user->dept;

        if ($userType === 'manager') {

            if ($userDept === 'pharmacy') {
                $pending = ModelsRequest::where('status', 'pending')
                    ->where('request_to', 'pharmacy')
                    ->orderByDesc('updated_at')->get()->each(function ($pending) {
                        $pending->formatted_date = Carbon::parse($pending->updated_at)->format('F j, Y, g:i:s a');
                    });
                $accepted = ModelsRequest::where('status', 'accepted')
                    ->where('request_to', 'pharmacy')
                    ->orderByDesc('updated_at')->get()->each(function ($accepted) {
                        $accepted->formatted_date = Carbon::parse($accepted->updated_at)->format('F j, Y, g:i:s a');
                    });
                $delivered = ModelsRequest::where('status', 'delivered')
                    ->where('request_to', 'pharmacy')
                    ->orderByDesc('updated_at')->get()->each(function ($accepted) {
                        $accepted->formatted_date = Carbon::parse($accepted->updated_at)->format('F j, Y, g:i:s a');
                    });
                $completed = ModelsRequest::where('status', 'completed')
                    ->where('request_to', 'pharmacy')
                    ->orderByDesc('updated_at')->get()->each(function ($accepted) {
                        $accepted->formatted_date = Carbon::parse($accepted->updated_at)->format('F j, Y, g:i:s a');
                    });
            } elseif ($userDept === 'csr') {
                $pending = ModelsRequest::where('status', 'pending')
                    ->where('request_to', 'csr')
                    ->orderByDesc('updated_at')->get()->each(function ($pending) {
                        $pending->formatted_date = Carbon::parse($pending->updated_at)->format('F j, Y, g:i:s a');
                    });
                $accepted = ModelsRequest::where('status', 'accepted')
                    ->where('request_to', 'csr')
                    ->orderByDesc('updated_at')->get()->each(function ($accepted) {
                        $accepted->formatted_date = Carbon::parse($accepted->updated_at)->format('F j, Y, g:i:s a');
                    });
                $delivered = ModelsRequest::where('status', 'delivered')
                    ->where('request_to', 'csr')
                    ->orderByDesc('updated_at')->get()->each(function ($accepted) {
                        $accepted->formatted_date = Carbon::parse($accepted->updated_at)->format('F j, Y, g:i:s a');
                    });
                $completed = ModelsRequest::where('status', 'completed')
                    ->where('request_to', 'csr')
                    ->orderByDesc('updated_at')->get()->each(function ($accepted) {
                        $accepted->formatted_date = Carbon::parse($accepted->updated_at)->format('F j, Y, g:i:s a');
                    });
            }

            return view('manager.userRequest')->with([
                'pending' => $pending,
                'accepted' => $accepted,
                'delivered' => $delivered,
                'completed' => $completed,
            ]);
        }
    }

    //this will show the requested items
    public function requestedItems($id)
    {
        $request = ModelsRequest::where('id', $id)->first();
        $request->formatted_date =
            Carbon::parse($request->updated_at)->format('F j, Y, g:i:s a');

        $items = Request_Item::where('request_id', $id)->get();

        $requestItems = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            // ->join('item_stocks', 'request_items.stock_id', '=', 'item_stocks.id')
            ->select('request_items.*', 'items.*')
            ->where('request_items.request_id', $id)
            ->orderBy('request_items.created_at', 'asc')
            ->get();

        foreach ($requestItems as $item) {
            $exp_date = Carbon::createFromFormat('Y-m-d', $item->exp_date);
            $item->exp_date = $exp_date->format('m-d-Y');
        }

        return view('manager.sub-page.requests.requested-items')->with([
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
        $user_dept = $user->dept;
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
            if ($user_type === 'manager') {
                Log::create([
                    'user_id' => $user_id,
                    'user_type' => $user_type . " (" . $user_dept . ")",
                    'message' => $message,
                    'query' => $last_query,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                Log::create([
                    'user_id' => $user_id,
                    'user_type' => $user_type,
                    'message' => $message,
                    'query' => $last_query,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }


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
        $user_dept = $user->dept;
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

                    //Get Query
                    $sql = DB::getQueryLog();

                    if (is_array($sql) && count($sql) > 0) {
                        $last_query = end($sql)['query'];
                    } else {
                        $last_query = 'No query log found.';
                    }

                    //Log Message
                    $message = "Stock id: " . $stock_id . " is fully consumed, batch deleted. ";

                    // Log the data to the logs table
                    if ($user_type === 'manager') {
                        Log::create([
                            'user_id' => $user_id,
                            'user_type' => $user_type . " (" . $user_dept . ")",
                            'message' => $message,
                            'query' => $last_query,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    } else {
                        Log::create([
                            'user_id' => $user_id,
                            'user_type' => $user_type,
                            'message' => $message,
                            'query' => $last_query,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
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
                    if ($user_type === 'manager') {
                        Log::create([
                            'user_id' => $user_id,
                            'user_type' => $user_type . " (" . $user_dept . ")",
                            'message' => $message,
                            'query' => $last_query,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    } else {
                        Log::create([
                            'user_id' => $user_id,
                            'user_type' => $user_type,
                            'message' => $message,
                            'query' => $last_query,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
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
            if ($user_type === 'manager') {
                Log::create([
                    'user_id' => $user_id,
                    'user_type' => $user_type . " (" . $user_dept . ")",
                    'message' => $message,
                    'query' => $last_query,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                Log::create([
                    'user_id' => $user_id,
                    'user_type' => $user_type,
                    'message' => $message,
                    'query' => $last_query,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            return back()->with('success', 'Requested items successfully delivered');
        }
    }
}
