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

    //this will show all the users in users view
    public function users()
    {
        $data = User::all();
        return view('admin.users')->with('users', $data);
    }

    //this will redirect to new user view where you will create new user
    public function newUser()
    {
        return view('admin.sub-page.users.new-user');
    }

    //this will save new user
    public function saveUser(Request $request)
    {
        $admin = auth()->user();

        //this will check the admin password if correct or not
        if (!Hash::check($request->admin_password, $admin->password)) {
            return redirect()->back()->with('error', 'Admin password is incorrect');
        }

        //this will check the new user requirements
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

        return redirect()->back()->with('success', 'User is Added');
    }

    //this will show the edit user view where depends on user id
    public function showUser($id)
    {
        $user = User::find($id);
        return view('admin.sub-page.users.edit-user')->with('user', $user);
    }

    //this will update user information
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

        return redirect()->back()->with('success', 'Account successfully updated.');
    }

    //this will show the user ready for deletion
    public function toDeleteUser($id)
    {
        $user = User::find($id);
        return view('admin.sub-page.users.delete-user')->with('user', $user);
    }

    //this will delete the user
    public function deleteUser(Request $request, $id)
    {
        $user = User::find($id);
        $admin = auth()->user();

        if (!Hash::check($request->admin_password, $admin->password)) {
            return redirect()->back()->with('error', 'Admin password is incorrect');
        }

        if ($user->delete() == true) {
            return redirect()->route('admin.users')->with('success', 'User successfully deleted');
        } else {
            return redirect()->back()->with('error', 'User deletion failed');
        }
    }
}
