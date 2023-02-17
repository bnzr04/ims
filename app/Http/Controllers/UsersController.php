<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function users()
    {
        $data = User::all();
        return view('admin.users')->with('users', $data);
    }

    public function saveUser(Request $request)
    {
        $model = new User;
        $model->name = $request->name;
        $model->username = $request->username;
        $model->password = Hash::make($request->password);
        $model->department = $request->userdept;
        $model->type = $request->usertype;
        $model->save();

        return back();
    }
}
