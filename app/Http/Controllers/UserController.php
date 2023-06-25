<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Request as ModelsRequest;
use App\Models\Request_Item;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function userHome() //this function will return the home view or the user dashboard
    {
        return view('user.home');
    }

    public function dashboardData() //this function will fetch request count of every status 
    {
        //get the user id
        $user_id = Auth::user()->id; //get the authenticated user id

        //this will get the total number of pending request
        $pending = ModelsRequest::where('user_id', $user_id)
            ->where('status', 'pending')
            ->count('user_id');

        //this will get the total number of accepted request
        $accepted = ModelsRequest::where('user_id', $user_id)
            ->where('status', 'accepted')
            ->count('user_id');

        //this will get the total number of delivered request
        $delivered = ModelsRequest::where('user_id', $user_id)
            ->where('status', 'delivered')
            ->count('user_id');

        //this will get the total number of completed or received request
        $completed = ModelsRequest::where('user_id', $user_id)
            ->where('status', 'completed')
            ->count('user_id');

        //this will get the total number of canceled request
        $canceled = ModelsRequest::where('user_id', $user_id)
            ->where('status', 'canceled')
            ->count('user_id');

        return response()->json([
            'pending' => $pending,
            'accepted' => $accepted,
            'delivered' => $delivered,
            'completed' => $completed,
            'canceled' => $canceled,
        ]);
    }
}
