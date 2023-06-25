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

    public function users() //this function will return admin users view with the users data
    {
        $users = User::all(); //get all the users data from users table
        return view('admin.users')->with('users', $users);
    }

    public function newUser() //this will return to new user view where you will create new user
    {
        return view('admin.sub-page.users.new-user');
    }

    public function saveUser(Request $request) //this function will save the new user information
    {
        //Enable Query log
        DB::enableQueryLog();

        $admin = auth()->user(); //get the authenticated admin password

        if (!Hash::check($request->admin_password, $admin->password)) { //this will check the admin password if correct or not
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

        $user = new User; //get the users table
        $user->name = $request->name; //store the value of 'name' input to name column
        $user->username = $request->username; //store the value of 'username' input to username column
        $user->password = Hash::make($request->password); //store the value of 'password' input and make it hash to password column
        $user->type = $request->type; //store the value of 'type' input to type column
        $user->dept = $request->dept; //store the value of 'dept' input to dept column
        $user->save(); //save the data 

        $authUser = auth()->user(); //get the information of the authenticated user

        $user_id = $authUser->id; // store the ID of the authenticated user to $user_id
        $user_type = $authUser->type; // store the user type of the authenticated user to $user_type

        // Get the SQL query being executed
        $sql = DB::getQueryLog();

        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
        } else {
            $last_query = 'No query log found.';
        }

        $newUserId = $user->id; //Get user type of new inserted user
        $newUserType = $request->type; //Get user type of new inserted user
        $newUsername = $request->username; //Get username

        if ($newUserType == 0) {
            $userType = 'user';
        } elseif ($newUserType == 1) {
            $userType = 'admin';
        } elseif ($newUserType == 2) {
            $userType = 'manager';
        }

        //Log Message
        $message = "New user created, user type: " . $userType . ", username: " . $newUsername . ", user ID: " . $newUserId;

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

    public function showUser($id) //this function will return the edit user view where depends on user $id parameter
    {
        $user = User::find($id); //find the user id to users table
        return view('admin.sub-page.users.edit-user')->with('user', $user);
    }

    //this will update user information
    public function updateUser(Request $request, $id) //this function will update the user information, parameter $id is the user id
    {
        $admin = auth()->user(); //get data of authenticated user

        $user = User::find($id); //get the user information by the user $id

        //Enable Query Log
        DB::enableQueryLog();

        if (!Hash::check($request->admin_password, $admin->password)) { //this will check the admin password if correct or not

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
            $message = "Update to user ID: (" . $user->id . "), user type: (" . $user->type . ") is failed, Incorrect admin password.";


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

        $rawUsertype = $user->type; //type of the selected user type

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

        $user->name = $request->name; //update the user name from 'name' input
        $user->type = $request->type; //update the user type from 'type' input
        $user->dept = $request->dept; //update the user dept from 'dept' input
        $user->username = $request->username; //update the user username from 'username' input
        if (!empty($request->password)) { //if the 'password' input is not empty
            $user->password = Hash::make($request->password); //update the user password from 'password' input
        }
        $user->save(); //save or update the user information

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
        $message = "User ID: (" . $user->id . "), user type: (" . $rawUsertype . ") updated.";

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


    public function toDeleteUser($id) //this function will return the delete-user view with the selected user information to delete. pass the parameter of user $id
    {
        $user = User::find($id); //find the user information using user $id
        return view('admin.sub-page.users.delete-user')->with('user', $user);
    }

    public function deleteUser(Request $request, $id) //this function will delete the selected user with the parameter $id
    {
        //Enable Query log
        DB::enableQueryLog();

        $user = User::find($id); //find the user information with the $id

        $admin = auth()->user(); //get the authenticated admin information

        $usertype = $user->type; //store the user type
        $userID = $user->id; //store the user id

        if (!Hash::check($request->admin_password, $admin->password)) { //this will check the admin password if correct or not

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
            $message = "Deleting user ID: (" . $userID . "), user type: (" . $usertype . ") is failed, Incorrect admin password.";

            // Log the data to the logs table
            Log::create([
                'user_id' => $user_id,
                'user_type' => $user_type,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->back()->with('error', 'Admin password is incorrect'); //return error message
        }

        if ($user->delete() == true) { //if the user is deleted

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
            $message = "User ID: (" . $userID . "),  user type: (" . $usertype . ") deleted.";


            // Log the data to the logs table
            Log::create([
                'user_id' => $user_id,
                'user_type' => $user_type,
                'message' => $message,
                'query' => $last_query,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->route('admin.users')->with('success', 'User successfully deleted'); //return to users view with success message of user deletion
        } else {
            return redirect()->back()->with('error', 'User deletion failed'); //return to users view with error message of failed user deletion
        }
    }
}
