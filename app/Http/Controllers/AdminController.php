<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Request;
use App\Models\Stock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard() //this function will return the admin dashboard view
    {
        return view('admin.dashboard');
    }

    public function dashboardDisplay() //this function will get the count of the total items, in stocks count, pending request, today completed request, and the user count of admin, manager and user
    {
        $totalItems = Item::count("id"); //get the total count of all the items 

        $inStocks = Stock::select(DB::raw('COUNT(DISTINCT item_id) as count')) //get the total count of all the items that has stocks 
            ->get()
            ->first();

        $pendingRequest = Request::where('status', 'pending')->count(); //get the count of pending request

        $from = Carbon::now()->startOfDay(); //set the $from value to the start of the day
        $to = Carbon::now()->endOfDay(); //set the $to value to the end of the day

        $todayCompletedReq = Request::where('status', 'completed')
            ->whereBetween('updated_at', [$from, $to])
            ->count(); //get the count of all the request with the column status 'completed' and filter the request created_at between $from and $to 

        $admins = User::where('type', 1)->count(); //get the count of the admin type
        $managers = User::where('type', 2)->count(); //get the count of the manager type
        $users = User::where('type', 0)->count(); //get the count of the user type

        return response()->json([ //return all the counts as json format
            'total_items' => $totalItems,
            'in_stocks' => $inStocks,
            'pending_request' => $pendingRequest,
            'completed_today' => $todayCompletedReq,
            'admins' => $admins,
            'managers' => $managers,
            'users' => $users,
        ]);
    }

    public function editItem($id)
    {
        $item = Item::find($id);
        return view('admin.sub-page.items.edit-item')->with('item', $item);
    }

    public function editUser($id)
    {
        $user = User::find($id);
        return print($user);
    }

    public function log()
    {
        return view('admin.log');
    }
}
