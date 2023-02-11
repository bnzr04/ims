<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\User;

class AdminController extends Controller
{

    //This will show the homepage as dashboard
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function adminHome()
    {
        return view('admin.main');
    }

    public function editItem($id)
    {
        $item = Item::find($id);
        return view('admin.modals.edit-item')->with('item', $item);
    }

    public function deleteItem($id)
    {
        $item = Item::find($id);
        $item->delete();
        return back();
    }

    public function stocks()
    {
        $items = Item::all();
        return view('admin.stocks')->with(compact('items'));
    }

    public function deployment()
    {
        return view('admin.deployment');
    }

    public function userRequest()
    {
        return view('admin.userRequest');
    }

    public function users()
    {
        return view('admin.users');
    }

    public function editUser($id)
    {
        $user = User::find($id);
        return print($user);
    }

    public function log()
    {
        return view('admin.log');
    }
}
