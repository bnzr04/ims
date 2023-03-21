<?php

namespace App\Http\Controllers;

use App\Models\Request as ModelsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

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
}
