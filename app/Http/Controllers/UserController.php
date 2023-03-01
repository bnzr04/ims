<?php

namespace App\Http\Controllers;

use App\Models\Request as ModelsRequest;
use App\Models\Request_Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function userHome()
    {
        return view('user.home');
    }

    public function userRequest()
    {
        $userId = Auth::id();
        $request = ModelsRequest::where('user_id', $userId)->get();
        return view('user.my-request')->with(['requests' => $request]);
    }

    public function itemRequest($id)
    {
        $request = ModelsRequest::where('id', $id)->first();
        $items = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->select('request_items.*', 'items.*')
            ->where('request_id', $id)
            ->get();
        return view('user.sub-page.view-items')->with(['items' => $items, 'request' => $request]);
    }
}
