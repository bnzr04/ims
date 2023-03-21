<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ManagerRequestController;
use App\Http\Controllers\RequestController;
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

Auth::routes();

Route::middleware('guest')->group(function () {
    Route::get('/', [HomeController::class, 'login'])->name('home');
    Route::get('/login', [HomeController::class, 'login'])->name('login');
});


/*------------------------------------------
--------------------------------------------
All Normal Users Routes List
--------------------------------------------
--------------------------------------------*/
Route::prefix('user')->middleware(['auth', 'user-access:user'])->group(function () {

    Route::get('/home', [UserController::class, 'userHome'])->name('user.home');

    //Request routes
    Route::get('/new-request', [RequestController::class, 'newRequest'])->name('user.newRequest');
    Route::get('/my-requests', [RequestController::class, 'userRequest'])->name('user.request');
    Route::post('/save-requests', [RequestController::class, 'saveRequest'])->name('user.save-request');
    Route::get('/delete-request/{id}', [RequestController::class, 'deleteRequest'])->name('user.delete-request');
    Route::get('/request-items/{id}', [RequestController::class, 'itemRequest'])->name('user.request-items');
    Route::get('/search-item', [UserController::class, 'searchItem'])->name('user.search-item');
    Route::post('/add-item', [RequestController::class, 'addItem'])->name('user.add-item');
    Route::get('/remove-item/{sid}/{id}', [RequestController::class, 'removeItem'])->name('user.remove-item');
    Route::post('/submit-request/{rid}', [RequestController::class, 'submitRequest'])->name('user.submit-request');
    Route::post('/receive-request/{rid}', [RequestController::class, 'receiveRequest'])->name('user.receive-request');
});

/*------------------------------------------
--------------------------------------------
All Admin Routes List
--------------------------------------------
--------------------------------------------*/

Route::prefix('admin')->middleware(['auth', 'user-access:admin'])->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/deployment', [AdminController::class, 'deployment'])->name('admin.deployment');

    //Users Log Activity
    Route::get('/log', [LogController::class, 'index'])->name('admin.log-index');

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

    //Stocks module routes
    Route::get('/stocks', [StocksController::class, 'stocks'])->name('admin.stocks');
    Route::get('/add-to-stocks/{id}', [StocksController::class, 'addToStocks'])->name('admin.add-to-stocks');
    Route::post('/save-stock', [StocksController::class, 'saveStock'])->name('admin.save-stock');
    Route::get('/add-stock/{id}', [StocksController::class, 'addStock'])->name('admin.add-stock');
    Route::post('/update-stock/{id}', [StocksController::class, 'updateStock'])->name('admin.update-stock');
    Route::get('/edit-stock/{id}', [StocksController::class, 'editStock'])->name('admin.edit-stock');
    Route::get('/delete-stock/{id}', [StocksController::class, 'deleteStock'])->name('admin.delete-stock');

    //Request module routes
    Route::get('/requests', [AdminRequestController::class, 'adminRequest'])->name('admin.requests');
    Route::get('/requested-items/{id}', [AdminRequestController::class, 'requestedItems'])->name('admin.requested-items');
    Route::post('/accept-request/{rid}', [AdminRequestController::class, 'acceptRequest'])->name('admin.accept-request');
    Route::post('/deliver-request/{rid}', [AdminRequestController::class, 'deliverRequest'])->name('admin.deliver-request');
});

/*------------------------------------------
--------------------------------------------
All Manager Routes List
--------------------------------------------
--------------------------------------------*/
Route::prefix('manager')->middleware(['auth', 'user-access:manager'])->group(function () {

    Route::get('/dashboard', [ManagerController::class, 'managerHome'])->name('manager.home');
    Route::get('/item-stocks', [ItemController::class, 'showAllItems'])->name('manager.stocks');
    Route::get('/deployment', [ManagerController::class, 'deployment'])->name('manager.deployment');
    Route::get('/requests', [ManagerRequestController::class, 'userRequest'])->name('manager.requests');
    Route::get('/requested-items/{id}', [ManagerRequestController::class, 'requestedItems'])->name('manager.requested-items');
    Route::post('/accept-request/{rid}', [ManagerRequestController::class, 'acceptRequest'])->name('manager.accept-request');
    Route::post('/deliver-request/{rid}', [ManagerRequestController::class, 'deliverRequest'])->name('manager.deliver-request');
});

//Logout
Route::get('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout1');
