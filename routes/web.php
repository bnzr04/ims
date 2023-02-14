<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\SidebarController;
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

    Route::get('/sidebar/{view_name}', [SidebarController::class, 'showView']);
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/home', [AdminController::class, 'adminHome'])->name('admin.home');
    Route::get('/items', [ItemController::class, 'showItems'])->name('admin.items');
    Route::get('/item-stocks', [AdminController::class, 'stocks'])->name('admin.stocks');
    Route::get('/deployment', [AdminController::class, 'deployment'])->name('admin.deployment');
    Route::get('/requests', [AdminController::class, 'userRequest'])->name('admin.requests');
    Route::get('/users', [UserController::class, 'showUser'])->name('admin.users');
    Route::get('/edit-user/{id}', [AdminController::class, 'editUser'])->name('admin.edit-user');
    Route::get('/log', [AdminController::class, 'log'])->name('admin.log');
    Route::post('/save-item', [ItemController::class, 'saveItem'])->name('admin.saveItem');
    Route::get('/edit-item/{id}', [AdminController::class, 'editItem'])->name('admin.edit-item');
    Route::get('/delete-item/{id}', [ItemController::class, 'deleteItem'])->name('admin.delete-item');
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
