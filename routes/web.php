<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\StocksController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/', [HomeController::class, 'login'])->name('login');
});


Auth::routes();

/*------------------------------------------
--------------------------------------------
All Normal Users Routes List
--------------------------------------------
--------------------------------------------*/
Route::prefix('user')->middleware(['auth', 'user-access:user'])->group(function () {

    Route::get('/home', [UserController::class, 'userHome'])->name('user.home');
    Route::get('/my-requests', [UserController::class, 'userRequest'])->name('user.request');
});

/*------------------------------------------
--------------------------------------------
All Admin Routes List
--------------------------------------------
--------------------------------------------*/

Route::prefix('admin')->middleware(['auth', 'user-access:admin'])->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/home', [AdminController::class, 'adminHome'])->name('admin.home');
    Route::get('/deployment', [AdminController::class, 'deployment'])->name('admin.deployment');
    Route::get('/requests', [AdminController::class, 'userRequest'])->name('admin.requests');
    Route::get('/log', [AdminController::class, 'log'])->name('admin.log');

    //User module routes
    Route::get('/users', [UsersController::class, 'users'])->name('admin.users');
    Route::get('/new-user', [UsersController::class, 'newUser'])->name('admin.new-user');
    Route::get('/show-user/{id}', [UsersController::class, 'showUser'])->name('admin.show-user');
    Route::post('/save-user', [UsersController::class, 'saveUser'])->name('admin.save-user');
    Route::post('/update-user/{id}', [UsersController::class, 'updateUser'])->name('admin.update-user');
    Route::get('/to-delete-user/{id}', [UsersController::class, 'toDeleteUser'])->name('admin.to-delete-user');
    Route::post('/delete-user/{id}', [UsersController::class, 'deleteUser'])->name('admin.delete-user');

    //Item module routes
    Route::get('/items', [ItemController::class, 'showAllItems'])->name('admin.items');
    Route::get('/new-item', [ItemController::class, 'newItem'])->name('admin.new-item');
    Route::get('/show-item/{id}', [ItemController::class, 'showItem'])->name('admin.show-item');
    Route::post('/save-item', [ItemController::class, 'saveItem'])->name('admin.saveItem');
    Route::post('/update-item/{id}', [ItemController::class, 'updateItem'])->name('admin.update-item');
    Route::get('/delete-item/{id}', [ItemController::class, 'deleteItem'])->name('admin.delete-item');

    //Stocks module routes
    Route::get('/stocks', [StocksController::class, 'stocks'])->name('admin.stocks');
    Route::get('/add-to-stocks/{id}', [StocksController::class, 'addToStocks'])->name('admin.add-to-stocks');
    Route::post('/save-stock', [StocksController::class, 'saveStock'])->name('admin.save-stock');
});

/*------------------------------------------
--------------------------------------------
All Manager Routes List
--------------------------------------------
--------------------------------------------*/
Route::prefix('manager')->middleware(['auth', 'user-access:manager'])->group(function () {

    Route::get('/home', [ManagerController::class, 'managerHome'])->name('manager.home');
    Route::get('/item-stocks', [ManagerController::class, 'stocks'])->name('manager.stocks');
    Route::get('/deployment', [ManagerController::class, 'deployment'])->name('manager.deployment');
    Route::get('/requests', [ManagerController::class, 'userRequest'])->name('manager.requests');
});
