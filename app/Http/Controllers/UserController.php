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
    //this will show the user dashboard
    public function userHome()
    {
        //get the user id
        $user_id = Auth::user()->id;

        //this will get the total number of pending
        $pending = ModelsRequest::where('user_id', $user_id)
            ->where('status', '!=', 'completed')
            ->count('user_id');

        //this will get the total number of completed or received requests
        $completed = ModelsRequest::where('user_id', $user_id)
            ->where('status', 'completed')
            ->count('user_id');



        return view('user.home')->with([
            'pending' => $pending,
            'completed' => $completed
        ]);
    }

    public function viewRequest($request)
    {
        $user_id = Auth::user()->id;

        if ($request === 'all') {
            $items = ModelsRequest::where('user_id', $user_id)
                ->where('status', '!=', 'completed')
                ->get();
        } else if ($request === 'completed') {
            $items = ModelsRequest::where('user_id', $user_id)
                ->where('status', 'completed')
                ->get();
        }

        foreach ($items as $item) {
            $item->formatted_date = Carbon::parse($item->updated_at)->format('F j, Y, g:i:s a');
        }


        return view('user.sub-page.view-request')->with([
            'items' => $items,
            'request' => $request
        ]);
    }

    public function searchItem(Request $request)
    {

        $searchItem = $request->search_item;


        if ($searchItem) {
            $search = Item::where('name', 'like', '%' . $searchItem . '%')->get();
        }



        return back()->with('items', $search);
    }
}
