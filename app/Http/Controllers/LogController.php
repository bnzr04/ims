<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use Carbon\Carbon;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $requestDate = $request->date;

        $logs = Log::query();

        if ($requestDate === 'yesterday') {
            $start = Carbon::yesterday()->startOfDay();
            $end = Carbon::yesterday()->endOfDay();

            //Yesterday Date and time
            $dateAndTime = Carbon::yesterday()->format('F j, Y');
        } elseif ($requestDate === 'this_month') {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();

            //This month
            $dateAndTime = Carbon::now()->format('F Y');
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
                $log->formatted_created_at = Carbon::parse($log->created_at)->format('F j, Y, g:i a');
            });


        return view('admin.log')->with(['logs' => $logs, 'dateAndTime' => $dateAndTime, 'requestDate' => $requestDate]);
    }
}
