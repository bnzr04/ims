<?php

namespace App\Http\Controllers;

use App\Models\Request as ModelsRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function PHPSTORM_META\type;

class TransactionController extends Controller
{
    public function transaction() //this function will return the transaction view 
    {
        $user = Auth::user(); //get the authencated user information
        $user_type = $user->type; //get the user type

        if ($user_type === 'manager') { //if the user type is manager
            return view('manager.sub-page.transaction.transaction');
        } else {
            return view('admin.sub-page.transaction.transaction');
        }
    }

    public function allTransaction() //this function will return the all-transaction view
    {
        $user = Auth::user(); //get the authencated user information
        $user_type = $user->type; //get the user type

        if ($user_type === 'manager') { //if the user type is manager
            return view("manager.sub-page.transaction.all-transaction");
        } else {
            return view("admin.sub-page.transaction.all-transaction");
        }
    }


    public function showTransaction() //this function will return the request with request status column where value is 'completed'
    {
        $user = Auth::user(); //get the authenticated user information
        $user_type = $user->type; //get the user type
        $user_id = $user->id; //get the user id

        $from = Carbon::now()->startOfDay(); //store the start time of the current day
        $to = Carbon::now()->endOfDay(); //store the end time of the current day

        //get all the request where the status is equal to 'completed' and retrieve the created_at that happened today
        $data = ModelsRequest::where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'asc'); //order the created_at as ascending

        if ($user_type === 'manager') {
            $data = $data->where('accepted_by_user_id', $user_id); //get the request where the accepted_by_user_id is equal to authenticated user id
        }

        $data = $data->get(); //get the data

        foreach ($data as $trans) {
            $trans->formatted_date =
                Carbon::parse($trans->updated_at)->format('F j, Y, g:i:s a'); //format the updated_at to a readable formated
        }

        return response()->json($data); //return the $data as json format
    }

    //filter completed transaction
    public function filterTransaction(Request $request) //this function will retrieve all the transaction or request where status is equal to 'completed' and depends on the selected date period
    {
        $user = Auth::user(); // get the authenticated user information
        $user_type = $user->type; //get the user type
        $user_id = $user->id; //get the user id

        $today = $request->input('today'); //get the request input 'today'
        $yesterday = $request->input('yesterday'); //get the request input 'yesterday'
        $thisMonth = $request->input('this-month'); //get the request input 'this-month'
        $filter = $request->input('filter'); //get the request input 'filter'

        if ($today) { //if $today is true
            $from = Carbon::now()->startOfDay(); //store the start of the day
            $to = Carbon::now()->endOfDay(); //store the end of the day
        } else if ($yesterday) { //if $yesterday is true
            $from = Carbon::yesterday()->startOfDay(); //store the start of the day yesterday
            $to = Carbon::yesterday()->endOfDay(); //store the end of the day yesterday
        } else if ($thisMonth) { //if $thisMonth is true
            $from = Carbon::now()->startOfMonth(); //store the start date of the month
            $to = Carbon::now()->endOfMonth();  //store the end date of the month
        } else if ($filter) { //if $filter is true
            $from = $request->input('from'); //store the value of input 'from'
            $to = $request->input('to'); //store the value of input 'to'
            $to = date('Y-m-d', strtotime($to . ' +1 day')); //format the value of $to and add 1 day
        } else {
            $from = Carbon::now()->startOfDay(); //store the start of the day
            $to = Carbon::now()->endOfDay(); //store the end of the day
        }

        if ($user_type === 'manager') { //if the user type is manager
            $data = ModelsRequest::where('status', 'completed')
                ->whereBetween('created_at', [$from, $to])
                ->where('accepted_by_user_id', $user_id)
                ->orderBy('created_at', 'desc')
                ->get();
        } else { //else the user is admin
            $data = ModelsRequest::where('status', 'completed')
                ->whereBetween('created_at', [$from, $to])
                ->orderBy('created_at', 'desc')
                ->get();
        }


        // Loop through the data and format the created_at timestamp
        foreach ($data as $item) {
            $item->formatted_date = date('F j, Y, g:i:s a', strtotime($item->created_at)); //format the created_at to a readble format
            $item->formatted_update_date = date('F j, Y, g:i:s a', strtotime($item->updated_at)); //format the updated_at to a readble format
        }

        return response()->json($data); //return the $data as json format
    }


    public function filterAllTransaction(Request $request) //this function will get all the transaction or request but the status pending is excluded and filter by the date period
    {
        $thisDay = $request->input('this-day'); //get the request 'this-day' input
        $thisWeek = $request->input('this-week'); //get the request 'this-week' input
        $thisMonth = $request->input('this-month'); //get the request 'this-month' input
        $filter = $request->input('filter'); //get the request 'filter' input

        if ($thisDay) {
            $from = Carbon::now()->startOfDay(); //store the start of the day
            $to = Carbon::now()->endOfDay(); //store the end of the day
        } else if ($thisWeek) {
            $from = Carbon::now()->startOfWeek(); //store the start date of the week
            $to = Carbon::now()->endOfWeek(); //store the end date of the week
        } else if ($thisMonth) {
            $from = Carbon::now()->startOfMonth(); //store the start date of the month
            $to = Carbon::now()->endOfMonth(); //store the end date of the month
        } else if ($filter) {
            $from = $request->input('from'); //store the value of input 'from' 
            $to = $request->input('to'); //store the value of input 'to' 
            $to = date('Y-m-d', strtotime($to . ' +1 day')); //format the value of $to and add 1 day
        } else {
            $from = Carbon::now()->startOfDay(); //store the start of the day
            $to = Carbon::now()->endOfDay(); //store the end of the day
        }

        //retrieve all the request but not the request with 'pending' status and filter it by the date period
        $query = ModelsRequest::where('status', '!=', 'pending')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc'); //order the created_at by ascending

        $data = $query->get(); //get the query data

        // Loop through the data and format the created_at timestamp
        foreach ($data as $req) {
            $req->formatted_date = date('F j, Y, g:i:s a', strtotime($req->created_at)); //format the created_at in a readble format
        }

        return response()->json($data); //return the $data in json format
    }

    public function searchRequestCode(Request $request) //this function will retrive the request by searching for its request code/id
    {
        $searchReqCode = $request->input('req-code'); //get the value of the 'req-code' input

        //retrieve all the request but not the request with 'pending' status
        $query = ModelsRequest::where('status', '!=', 'pending')
            ->orderBy('created_at', 'asc'); //order the request created_at by ascending

        if ($searchReqCode) { //if the $searchReqCode is true and it has a value
            $query->where('id', 'like', $searchReqCode); //find the request id where value is $searchReqCode value 
        }

        $data = $query->get(); //get the query data

        foreach ($data as $req) {
            $req->formatted_date = date('F j, Y, g:i:s a', strtotime($req->created_at)); //format the created_at to a readble format
        }

        return response()->json($data); //return the $data in json format
    }
}
