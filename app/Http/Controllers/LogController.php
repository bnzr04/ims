<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function log(Request $request) //this function will return the admin log view with the log data
    {
        $requestDate = $request->date; //get the value of date input passed in the url
        $date_from = $request->date_from; //get the value of 'date_from' input
        $date_to = $request->date_to; //get the value of 'date_to' input

        $logs = Log::query(); //initiate a query from 'logs' table  

        if ($requestDate === 'today') { //if $requestDate is equal to 'today'
            $start = Carbon::today()->startOfDay();
            $end = Carbon::today()->endOfDay();

            //Current Date and time
            $dateAndTime = Carbon::now()->format('F j, Y, g:i a');
        } else if ($requestDate === 'yesterday') { //if $requestDate is equal to 'yesterday'
            $start = Carbon::yesterday()->startOfDay();
            $end = Carbon::yesterday()->endOfDay();

            //Yesterday Date and time
            $dateAndTime = Carbon::yesterday()->format('F j, Y');
        } elseif ($requestDate === 'this_month') { //if $requestDate is equal to 'this_month'
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();

            //This month
            $dateAndTime = Carbon::now()->format('F Y');
        } else if ($date_from && $date_to) { //if $date_from and $date_to is true or has a value
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
    public function startLog() //this function is the starting part of the query log
    {
        DB::enableQueryLog(); //enable the query log

        $user = Auth::user(); //get the authenticated user information
        $user_id = $user->id; //get the user id
        $user_type = $user->type; //get the user type

        if ($user_type === 'manager') { //if the user type is manager
            $user_dept = $user->dept; //set the $user_dept value is equal to authenticated user department
        } else { //else the $user_dept value is empty
            $user_dept = "";
        }

        return [$user_id, $user_type, $user_dept]; //return the authenticated user information
    }

    /////Log last part//////////
    public function endLog($user_id, $user_type, $user_dept, $message) //this function is the end part of the log
    {
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
