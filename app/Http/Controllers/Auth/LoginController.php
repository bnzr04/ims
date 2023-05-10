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
    // public function __construct()
    // {
    //     $this->middleware('guest')->except('logout');
    // }

    public function login(Request $request)
    {
        // Enable query logging
        DB::enableQueryLog();

        $input = $request->all();

        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        if (auth()->attempt(array('username' => $input['username'], 'password' => $input['password']))) {
            //QUERY LOG

            $user = auth()->user();

            $user_id = $user->id; // Get the ID of the authenticated user
            $userName = $user->name; // Get user name
            $dept = $user->dept; // Get the depart if the user is manager

            if ($user->type === "manager") {
                $user_type = $user->type . " (" . $dept . ")"; // Get the Type of the authenticated user
            } else {
                $user_type = $user->type;
            }


            // Get the SQL query being executed
            $sql = DB::getQueryLog();
            if (is_array($sql) && count($sql) > 0) {
                $last_query = end($sql)['query'];
            } else {
                $last_query = "No query log found.";
            }

            //Log Message
            if ($user_type === 'user') {
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

            if ($user->type == 'admin') {
                return redirect()->route('admin.dashboard');
            } else if ($user->type == 'manager') {
                return redirect()->route('manager.requests');
            } else if ($user->type == 'user') {
                return redirect()->route('user.request');
            }
        } else {
            return redirect()->route('login')
                ->withErrors([
                    'username' => 'Username or password is incorrect.',
                ]);
        }
    }

    public function logout(Request $request)
    {
        //QUERY LOG

        // Enable query logging
        DB::enableQueryLog();

        $user = auth()->user();

        $user_id = $user->id; // Get the ID of the authenticated user
        $dept = $user->dept; // Get the depart if the user is manager

        if ($user->type === "manager") {
            $user_type = $user->type . " (" . $dept . ")"; // Get the dept of the authenticated manager
        } else {
            $user_type = $user->type;
        }



        // Get the SQL query being executed
        $sql = DB::getQueryLog();
        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
        } else {
            $last_query = "No query log found.";
        }


        $userName = $user->name; // Get user name

        //Log Message
        if (
            $user_type === 'user'
        ) {
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

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
