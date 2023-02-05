<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function managerHome()
    {
        return view('manager.dashboard');
    }

    public function stocks()
    {
        return view('manager.stocks');
    }

    public function deployment()
    {
        return view('manager.deployment');
    }

    public function userRequest()
    {
        return view('manager.userRequest');
    }
}
