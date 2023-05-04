<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $requestDate = $request->date;
        $date_from = $request->date_from;
        $date_to = $request->date_to;

        $logs = Log::query();

        if ($requestDate === 'today') {
            $start = Carbon::today()->startOfDay();
            $end = Carbon::today()->endOfDay();

            //Current Date and time
            $dateAndTime = Carbon::now()->format('F j, Y, g:i a');
        } else if ($requestDate === 'yesterday') {
            $start = Carbon::yesterday()->startOfDay();
            $end = Carbon::yesterday()->endOfDay();

            //Yesterday Date and time
            $dateAndTime = Carbon::yesterday()->format('F j, Y');
        } elseif ($requestDate === 'this_month') {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();

            //This month
            $dateAndTime = Carbon::now()->format('F Y');
        } else if ($date_from && $date_to) {
            $start = $date_from;
            $end = date('Y-m-d', strtotime($date_to . '+1day'));

            $dateAndTime = date('F j, Y', strtotime($date_from)) . " - " . date('F j, Y', strtotime($date_to));
        } else {
            $start = Carbon::today()->startOfDay();
            $end = Carbon::today()->endOfDay();

            //Current Date and time
            $dateAndTime = Carbon::now()->format('F j, Y, g:i a');
        }

        //Date Format
        $logs = $logs->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->get()
            ->each(function ($log) {
                $log->formatted_created_at = Carbon::parse($log->created_at)->format('F j, Y, g:i:s a');
            });


        return view('admin.log')->with(['logs' => $logs, 'dateAndTime' => $dateAndTime, 'requestDate' => $requestDate]);
    }

    /////Log first part//////////
    public function startLog()
    {
        DB::enableQueryLog();

        $user = Auth::user();
        $user_id = $user->id;
        $user_type = $user->type;
        if ($user_type === 'manager') {
            $user_dept = $user->dept;
        } else {
            $user_dept = "";
        }

        return [$user_id, $user_type, $user_dept];
    }

    /////Log last part//////////
    public function endLog($user_id, $user_type, $user_dept, $message)
    {
        if ($user_type === 'manager') {
            $user_type = $user_type . " (" . $user_dept . ")";
        }

        // Get the SQL query being executed
        $sql = DB::getQueryLog();
        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
        } else {
            $last_query = 'No query log found.';
        }

        // Log the data to the logs table
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
