<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Request;
use App\Models\Stock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function showInfo()
    {
        return view('auth.info');
    }

    public function dashboardDisplay()
    {
        $totalItems = Item::count("id");
        $inStocks = Stock::select(DB::raw('COUNT(DISTINCT item_id) as count'))
            ->get()
            ->first();
        $pendingRequest = Request::where('status', 'pending')->count();

        $from = Carbon::now()->startOfDay();
        $to = Carbon::now()->endOfDay();

        $todayCompletedReq = Request::where('status', 'completed')
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        $admins = User::where('type', 1)->count();
        $managers = User::where('type', 2)->count();
        $users = User::where('type', 0)->count();

        return response()->json([
            'total_items' => $totalItems,
            'in_stocks' => $inStocks,
            'pending_request' => $pendingRequest,
            'completed_today' => $todayCompletedReq,
            'admins' => $admins,
            'managers' => $managers,
            'users' => $users,
        ]);
    }
}
