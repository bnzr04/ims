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
    public function managerHome()
    {
        return view('manager.dashboard');
    }

    public function dashboardDisplay()
    {
        $totalItems = Item::count("id");
        $inStocks = Stock::select(DB::raw('COUNT(DISTINCT item_id) as count'))
            ->get()
            ->first();
        $pendingRequest = ModelsRequest::where('status', 'pending')->count();

        $from = Carbon::now()->startOfDay();
        $to = Carbon::now()->endOfDay();

        $todayCompletedReq = ModelsRequest::where('status', 'completed')
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        return response()->json([
            'total_items' => $totalItems,
            'in_stocks' => $inStocks,
            'pending_request' => $pendingRequest,
            'completed_today' => $todayCompletedReq,
        ]);
    }

    public function stocks()
    {
        return view('manager.stocks');
    }

    public function deployment()
    {
        return view('manager.deployment');
    }
}
