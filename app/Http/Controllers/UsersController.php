<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        //Enable Query log
        DB::enableQueryLog();

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
            'dept' => $request->type == 2 ? 'required|in:0,1' : '',
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
        $model->type = $request->type;
        $model->dept = $request->dept;
        $model->save();

        //QUERY LOG
        $user = auth()->user();

        $user_id = $user->id; // Get the ID of the authenticated user
        $dept = $user->dept; // Get the depart if the user is manager

        if ($user->type === "manager") {
            $user_type = $user->type . " (" . $dept . ")"; // Get the department (dept) of the authenticated manager
        } else {
            $user_type = $user->type;
        }

        // Get the SQL query being executed
        $sql = DB::getQueryLog();

        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
        } else {
            $last_query = 'No query log found.';
        }


        //Get user type of new inserted user
        $newUserType = $request->type;
        $message = "New " . $newUserType . " type is created, user name is " . $request->username;

        if ($newUserType === 0) {
            $newUserType = 'user';
            $newUserName = $request->name; //Get user name;

            //Log Message
            $message = "New user created, user type: " . $newUserType . " (" . $newUserName . ")";
        } elseif ($newUserType === 1) {
            $newUserType = 'admin';
            $message = "New user created, user type: " . $newUserType;
        } elseif ($newUserType === 2) {
            $newUserType = 'manager';
            $message = "New user created, user type: " . $newUserType;
        }

        // Log the data to the logs table
        Log::create([
            'user_id' => $user_id,
            'user_type' => $user_type,
            'message' => $message,
            'query' => $last_query,
            'created_at' => now(),
            'updated_at' => now()
        ]);


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

        //get data of authenticated user
        $admin = auth()->user();

        $user = User::find($id);

        //Enable Query Log
        DB::enableQueryLog();

        if (!Hash::check($request->admin_password, $admin->password)) {

            $user_type = $admin->type; // Get the type of the authenticated user
            $user_id = $admin->id; // Get the ID of the authenticated user

            // Get the SQL query being executed
            $sql = DB::getQueryLog();

            if (is_array($sql) && count($sql) > 0) {
                $last_query = end($sql)['query'];
            } else {
                $last_query = 'No query log found.';
            }

            //Log Message
            $message = "Update to user ( Type: " . $user->type . ") Failed, Incorrect admin password.";


            // Log the data to the logs table
            Log::create([
                'user_id' => $user_id,
                'user_type' => $user_type,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->back()->with('error', 'Admin password is incorrect.');
        }

        $rawUsertype = $user->type;

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required',
            'type' => 'required|in:0,1,2',
            'dept' => $request->type == 2 ? 'required|in:0,1' : '',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()->with('error', 'Account update Failed!');
        }

        $user->name = $request->name;
        $user->type = $request->type;
        $user->dept = $request->dept;
        $user->username = $request->username;
        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $user_type = $admin->type; // Get the type of the authenticated user
        $user_id = $admin->id; // Get the ID of the authenticated user

        // Get the SQL query being executed
        $sql = DB::getQueryLog();

        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
        } else {
            $last_query = 'No query log found.';
        }

        //Log Message
        $message = "User (ID: " . $user->id . ", Type: " . $rawUsertype . ") updated.";


        // Log the data to the logs table
        Log::create([
            'user_id' => $user_id,
            'user_type' => $user_type,
            'message' => $message,
            'query' => $last_query,
            'created_at' => now(),
            'updated_at' => now()
        ]);

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

        //Enable Query log
        DB::enableQueryLog();

        $user = User::find($id);
        $admin = auth()->user();

        $rawUsertype = $user->type;
        $rawUserID = $user->id;

        if (!Hash::check($request->admin_password, $admin->password)) {

            $user_type = $admin->type; // Get the type of the authenticated user
            $user_id = $admin->id; // Get the ID of the authenticated user

            // Get the SQL query being executed
            $sql = DB::getQueryLog();

            if (is_array($sql) && count($sql) > 0) {
                $last_query = end($sql)['query'];
            } else {
                $last_query = 'No query log found.';
            }

            //Log Message
            $message = "Deleting user ( ID: " . $rawUserID . ", Type: " . $rawUsertype . ") Failed, Incorrect admin password.";


            // Log the data to the logs table
            Log::create([
                'user_id' => $user_id,
                'user_type' => $user_type,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->back()->with('error', 'Admin password is incorrect');
        }

        if ($user->delete() == true) {

            $user_type = $admin->type; // Get the type of the authenticated user
            $user_id = $admin->id; // Get the ID of the authenticated user

            // Get the SQL query being executed
            $sql = DB::getQueryLog();

            if (is_array($sql) && count($sql) > 0) {
                $last_query = end($sql)['query'];
            } else {
                $last_query = 'No query log found.';
            }

            //Log Message
            $message = "User (ID: " . $rawUserID . ", Type: " . $rawUsertype . ") deleted.";


            // Log the data to the logs table
            Log::create([
                'user_id' => $user_id,
                'user_type' => $user_type,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->route('admin.users')->with('success', 'User successfully deleted');
        } else {
            return redirect()->back()->with('error', 'User deletion failed');
        }
    }
}
