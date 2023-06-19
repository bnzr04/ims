<?php

namespace App\Http\Controllers;

use App\Models\Request as ModelsRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function PHPSTORM_META\type;

class TransactionController extends Controller
{
    //Request transaction
    public function transaction()
    {
        $user = Auth::user();
        $user_type = $user->type;

        if ($user_type === 'manager') {
            return view('manager.sub-page.transaction.transaction');
        } else {
            return view('admin.sub-page.transaction.transaction');
        }
    }

    public function allTransaction()
    {
        $user = Auth::user();
        $user_type = $user->type;

        if ($user_type === 'manager') {
            return view("manager.sub-page.transaction.all-transaction");
        } else {
            return view("admin.sub-page.transaction.all-transaction");
        }
    }

    //get transactions/request data
    public function showTransaction()
    {
        $user = Auth::user();
        $user_type = $user->type;
        $user_id = $user->id;

        $from = Carbon::now()->startOfDay();
        $to = Carbon::now()->endOfDay();

        if ($user_type === 'manager') {
            $data = ModelsRequest::where('status', 'completed')
                ->whereBetween('created_at', [$from, $to])
                ->where('accepted_by_user_id', $user_id)
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            $data = ModelsRequest::where('status', 'completed')
                ->whereBetween('created_at', [$from, $to])
                ->orderBy('created_at', 'asc')
                ->get();
        }

        foreach ($data as $trans) {
            $trans->formatted_date =
                Carbon::parse($trans->updated_at)->format('F j, Y, g:i:s a');
        }

        return response()->json($data);
    }

    //filter completed transaction
    public function filterTransaction(Request $request)
    {
        $user = Auth::user();
        $user_type = $user->type;
        $user_id = $user->id;

        $today = $request->input('today');
        $yesterday = $request->input('yesterday');
        $thisMonth = $request->input('this-month');
        $filter = $request->input('filter');

        if ($today) {
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        } else if ($yesterday) {
            $from = Carbon::yesterday()->startOfDay();
            $to = Carbon::yesterday()->endOfDay();
        } else if ($thisMonth) {
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfMonth();
        } else if ($filter) {
            $from = $request->input('from');
            $to = $request->input('to');
            $to = date('Y-m-d', strtotime($to . ' +1 day'));
        } else {
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        }

        if ($user_type === 'manager') {
            $data = ModelsRequest::where('status', 'completed')
                ->whereBetween('created_at', [$from, $to])
                ->where('accepted_by_user_id', $user_id)
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            $data = ModelsRequest::where('status', 'completed')
                ->whereBetween('created_at', [$from, $to])
                ->orderBy('created_at', 'asc')
                ->get();
        }


        // Loop through the data and format the created_at timestamp
        foreach ($data as $item) {
            $item->formatted_date = date('F j, Y, g:i:s a', strtotime($item->created_at));
            $item->formatted_update_date = date('F j, Y, g:i:s a', strtotime($item->updated_at));
        }

        return response()->json($data);
    }

    //filter all the transaction
    public function filterAllTransaction(Request $request)
    {
        $thisDay = $request->input('this-day');
        $thisWeek = $request->input('this-week');
        $thisMonth = $request->input('this-month');
        $filter = $request->input('filter');

        if ($thisDay) {
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        } else if ($thisWeek) {
            $from = Carbon::now()->startOfWeek();
            $to = Carbon::now()->endOfWeek();
        } else if ($thisMonth) {
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfMonth();
        } else if ($filter) {
            $from = $request->input('from');
            $to = $request->input('to');
            $to = date('Y-m-d', strtotime($to . ' +1 day'));
        } else {
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        }

        $query = ModelsRequest::where('status', '!=', 'pending')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'asc');

        $data = $query->get();

        // Loop through the data and format the created_at timestamp
        foreach ($data as $req) {
            $req->formatted_date = date('F j, Y, g:i:s a', strtotime($req->created_at));
        }

        return response()->json($data);
    }

    public function searchRequestCode(Request $request)
    {
        $searchReqCode = $request->input('req-code');

        $query = ModelsRequest::where('status', '!=', 'pending')
            ->orderBy('created_at', 'asc');

        if ($searchReqCode) {
            $query->where('id', 'like', $searchReqCode);
        }

        $data = $query->get();

        foreach ($data as $req) {
            $req->formatted_date = date('F j, Y, g:i:s a', strtotime($req->created_at));
        }

        return response()->json($data);
    }
}
