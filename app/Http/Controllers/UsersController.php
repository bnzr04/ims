<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function users()
    {
        $data = User::all();
        return view('admin.users')->with('users', $data);
    }

    public function newUser()
    {
        return view('admin.sub-pages.users.new-user');
    }

    public function saveUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
            'type' => 'required|in:0,1,2',
            'department' => $request->usertype == 0 ? 'required|in:0,1' : '',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }


        $model = new User;
        $model->name = $request->name;
        $model->username = $request->username;
        $model->password = Hash::make($request->password);
        $model->department = $request->department;
        $model->type = $request->type;
        $model->save();

        return redirect()->route('admin.users');
    }

    public function showUser($id)
    {
        $user = User::find($id);
        return view('admin.sub-pages.users.edit-user')->with('user', $user);
    }

    public function updateUser(Request $request, $id)
    {
        $admin = auth()->user();
        $user = User::find($id);

        if (!Hash::check($request->admin_password, $admin->password)) {
            return redirect()->back()->with('error', 'Admin password is incorrect.');
        }

        $user->name = $request->name;
        $user->type = $request->type;
        $user->department = $request->department;
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with('success', 'User account successfully updated.');
    }
}
