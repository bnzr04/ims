<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Item;
use App\Models\Request as ModelsRequest;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ManagerController extends Controller
{
    public function managerHome() //this function will return the manager dashboard view
    {
        return view('manager.dashboard');
    }

    public function dashboardDisplay() //this function will return the count of total items, items in stocks, pending requests, and today completed request in json format
    {
        $totalItems = Item::count("id"); //get the count of saved items
        $inStocks = Stock::select(DB::raw('COUNT(DISTINCT item_id) as count')) //get the count of items that has stocks
            ->get()
            ->first();
        $pendingRequest = ModelsRequest::where('status', 'pending')->count(); //get the pending request count

        $from = Carbon::now()->startOfDay();
        $to = Carbon::now()->endOfDay();

        $todayCompletedReq = ModelsRequest::where('status', 'completed') //get the count of today completed request 
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        return response()->json([
            'total_items' => $totalItems,
            'in_stocks' => $inStocks,
            'pending_request' => $pendingRequest,
            'completed_today' => $todayCompletedReq,
        ]);
    }

    public function stocks() //return the stocks view 
    {
        return view('manager.stocks');
    }
}
