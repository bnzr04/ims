<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use Illuminate\Http\Request;

class SidebarController extends Controller
{
    public function showView($view_name)
    {
        /* -------SIDE BAR LINKS--------- */

        //This will show view items with items data
        if ($view_name == 'admin.items') {
            $items = Item::all();
            return view($view_name)->with('items', $items);
        }

        //This will show view users with users data
        else if ($view_name == 'admin.users') {
            $users = User::all();
            return view('admin.users')->with('users', $users);
        }

        return view($view_name);
    }
}
