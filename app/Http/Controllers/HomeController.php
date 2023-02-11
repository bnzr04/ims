<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }
}
