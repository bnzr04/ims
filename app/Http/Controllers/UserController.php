<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function userHome()
    {
        return view('user.home');
    }

    public function userRequest()
    {
        return view('user.my-request');
    }
}
