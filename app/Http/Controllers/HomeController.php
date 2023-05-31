<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Log;
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
        $from = Carbon::now()->startOfDay();
        $to = Carbon::now()->endOfDay();
        $logs = Log::whereIn('user_type', ['user', 'manager'])->whereBetween('created_at', [$from, $to])->orderByDesc('created_at')->get();

        foreach ($logs as $log) {
            $username = User::select('username')->where('id', $log->user_id)->first();
            $log->username = $username ? $username->username : 'N/A';
            $log->format_date = Carbon::parse($log->created_at)->format('F j, Y, g:i:s a');
        }

        return view('auth.info')->with(['logs' => $logs]);
    }

    public function dashboardDisplay()
    {
        $log = Log::where('user_type', 'user');

        return view('auth.info')->with(['logs' => $log]);
    }
}
