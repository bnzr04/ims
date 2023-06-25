<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    //this function is for login in the account
    public function login(Request $request)
    {
        // Enable query logging
        DB::enableQueryLog();

        $input = $request->all(); //get all the input including username and password input

        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        if (auth()->attempt(array('username' => $input['username'], 'password' => $input['password']))) { //if the username and password is exist

            $user = auth()->user(); //get the authenticated user information

            $user_id = $user->id; // Get the ID of the authenticated user
            $userName = $user->name; // Get user name
            $user_type = $user->type; // get the user type


            // Get the SQL query being executed
            $sql = DB::getQueryLog();
            if (is_array($sql) && count($sql) > 0) {
                $last_query = end($sql)['query'];
            } else {
                $last_query = "No query log found.";
            }

            //Log Message
            if ($user_type === 'user' || $user_type === 'manager') { //if user type is 'user' or 'manager'
                $message = $user_type . " (" . $userName . ") logged in.";
            } else {
                $message = $user_type . " logged in.";
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

            if ($user->type == 'admin') { //if user type is admin
                return redirect()->route('admin.dashboard'); //redirect to admin dashboard route
            } else if ($user->type == 'manager') { //if user type is manager
                return redirect()->route('manager.home'); //redirect to manager dashboard route
            } else if ($user->type == 'user') { //if user type is user
                return redirect()->route('user.request'); //redirect to user request route
            }
        } else { //if the credetial that enter is not exist
            return redirect()->route('login')
                ->withErrors([
                    'username' => 'Username or password is incorrect.',
                ]);
        }
    }

    //this the logout function
    public function logout(Request $request)
    {
        // Enable query logging
        DB::enableQueryLog();

        $user = auth()->user(); //get the authenticated user information

        $user_id = $user->id; // Get the ID of the authenticated user
        $userName = $user->name; // Get user name
        $user_type = $user->type; //get the user type



        // Get the SQL query being executed
        $sql = DB::getQueryLog();
        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
        } else {
            $last_query = "No query log found.";
        }

        //Log Message
        if ($user_type === 'user' || $user_type === 'manager') {
            $message = $user_type . " (" . $userName . ") logged out.";
        } else {
            $message = $user_type . " logged out.";
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

        Auth::logout(); //logout the authenticated user

        $request->session()->invalidate(); //remove all the data stored in the session

        $request->session()->regenerateToken(); //regenerate token for the next session

        return redirect('/login'); //redirect the user to login
    }
}
