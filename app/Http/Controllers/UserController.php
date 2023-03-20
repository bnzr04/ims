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
    public function userHome()
    {
        return view('user.home');
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
