<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DispenseController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ManagerRequestController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\StocksController;
use App\Http\Controllers\TransactionController;
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


///////////////////
/////DEV NOTE////// FOR VSCODE IDE, ex. [HomeController::class, 'login'] you can ctrl + click the function 'login' to redirect on the function you want to go. 
///////////////////

Auth::routes();

/*------------------------------------------
--------------------------------------------
All Guest Routes List
--------------------------------------------
--------------------------------------------*/
Route::middleware('guest')->group(function () {
    Route::get('/', [HomeController::class, 'login'])->name('home'); //will redirect to login if they access "/"
    Route::get('/login', [HomeController::class, 'login'])->name('login'); //will redirect to login if they access "/login";

    /////guest view access (info)/////
    Route::get('/info', [HomeController::class, 'showInfo'])->name('info'); //guest info
    Route::get('/add-to-stocks/{id}', [HomeController::class, 'addToStocks'])->name('add-to-stocks'); //will view stock batches

    /////guest dispense module/////
    Route::get('/dispense', [HomeController::class, 'dispenseView'])->name('dispense'); //guest will view dispensed items
    Route::get('/filter-dispense', [HomeController::class, 'dispenseFilter'])->name('filter-dispense'); //will filter the dispense from today, yesterday, this-month
    Route::get('/fetch-record/{id}', [HomeController::class, 'fetchRecord'])->name('fetch-record'); //will fetch the dispensed record
    Route::get('/view-request/{id}', [HomeController::class, 'viewRequest'])->name('view-request'); //will view the selected record

    /////guest transaction module/////
    Route::get('/transaction', [HomeController::class, 'transaction'])->name('transaction'); // will show the transaction module
    Route::get('/filter-all-transaction', [HomeController::class, 'filterAllTransaction'])->name('filter-all-transaction'); // will fetch and filter the transactions
    Route::get('/search-transaction', [HomeController::class, 'searchRequestCode'])->name('search-transaction'); // will search for transaction by request code or id
});


/*------------------------------------------
--------------------------------------------
All Normal Users Routes List
--------------------------------------------
--------------------------------------------*/
Route::prefix('user')->middleware(['auth', 'user-access:user'])->group(function () {

    /////Dashboard module/////
    Route::get('/home', [UserController::class, 'userHome'])->name('user.home'); // will show the user dashboard
    Route::get('/dashboard-data', [UserController::class, 'dashboardData'])->name('user.dashboard-data'); // will show the dashboard data count

    ////////Request modules////////
    Route::get('/request', [RequestController::class, 'request'])->name('user.request'); // will show the request module where user can order item/s and fetch the items available in stocks
    Route::get('/request-items/{id}', [RequestController::class, 'itemRequest'])->name('user.request-items'); // will view the selected request
    Route::post('/submit-request', [RequestController::class, 'submitRequest'])->name('user.submit-request'); // will submit the order request
    Route::post('/cancel-request/{rid}', [RequestController::class, 'cancelRequest'])->name('user.cancel-request'); // will cancel the pending request
    Route::post('/receive-request/{rid}', [RequestController::class, 'receiveRequest'])->name('user.receive-request'); // will receive the delivered request
    Route::get('/view-request/{request}/{filter}', [RequestController::class, 'viewRequest'])->name('user.viewRequest'); // will show the request depending on its status and data filter

    Route::get('/available-items', [RequestController::class, 'showList'])->name('user.available-items'); //will fetch the available items on stocks
});

/*------------------------------------------
--------------------------------------------
All Admin Routes List
--------------------------------------------
--------------------------------------------*/

Route::prefix('admin')->middleware(['auth', 'user-access:admin'])->group(function () {

    ////////Dashboard////////
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard'); //will show admin dashboard module
    Route::get('/dashboard-display', [AdminController::class, 'dashboardDisplay'])->name('admin.dashboard-display'); // this will get the dashboard data count

    ////////Log module////////
    Route::get('/log', [LogController::class, 'log'])->name('admin.log-index'); //will show the log module

    ////////User module routes////////
    Route::get('/users', [UsersController::class, 'users'])->name('admin.users'); //will show the users module
    Route::get('/new-user', [UsersController::class, 'newUser'])->name('admin.new-user'); //will show the module where you can add new user
    Route::get('/show-user/{id}', [UsersController::class, 'showUser'])->name('admin.show-user'); //will show the details of the user when you click the edit button
    Route::post('/save-user', [UsersController::class, 'saveUser'])->name('admin.save-user'); //will save the data of a new user
    Route::post('/update-user/{id}', [UsersController::class, 'updateUser'])->name('admin.update-user'); //will update the information of existing user
    Route::get('/to-delete-user/{id}', [UsersController::class, 'toDeleteUser'])->name('admin.to-delete-user'); // will show the user before deleting
    Route::post('/delete-user/{id}', [UsersController::class, 'deleteUser'])->name('admin.delete-user'); // delete the selected user

    ////////Item module routes////////
    Route::get('/items', [ItemController::class, 'showAllItems'])->name('admin.items'); //will show the items module
    Route::get('/new-item', [ItemController::class, 'newItem'])->name('admin.new-item'); //will show the module where you can add new item
    Route::get('/show-item/{id}', [ItemController::class, 'showItem'])->name('admin.show-item'); //will show the information of the item when you click the edit button of the item
    Route::post('/save-item', [ItemController::class, 'saveItem'])->name('admin.saveItem'); // will save the new item
    Route::post('/update-item/{id}', [ItemController::class, 'updateItem'])->name('admin.update-item'); // will update the information of the item
    Route::post('/insert-items', [ItemController::class, 'import'])->name('admin.insert-items'); // to import items data using csv file
    Route::post('/export-items', [ItemController::class, 'export'])->name('admin.export-items'); // to export items information and generate excel file

    ////////Stocks module routes////////
    Route::get('/stocks', [StocksController::class, 'stocks'])->name('admin.stocks'); // will show the stocks module
    Route::get('/add-to-stocks/{id}', [StocksController::class, 'addToStocks'])->name('admin.add-to-stocks'); //will show the stock batches of an item where you can also add new stock batch
    Route::post('/save-stock', [StocksController::class, 'saveStock'])->name('admin.save-stock'); //will save the new stock batch
    Route::get('/add-stock/{id}', [StocksController::class, 'addStock'])->name('admin.add-stock'); //will show the module where you can remove or add in a stock batch
    Route::post('/update-stock/{id}', [StocksController::class, 'updateStock'])->name('admin.update-stock'); //will update the stock batch quantity
    Route::get('/edit-stock/{id}', [StocksController::class, 'editStock'])->name('admin.edit-stock'); //will show the edit stock module where admin can edit the stock batch quantity
    Route::post('/admin-update-stock/{id}', [StocksController::class, 'adminUpdateStock'])->name('admin.admin-update-stock'); //will update and save the edited stock batch quantity of the admin
    Route::get('/delete-stock/{id}', [StocksController::class, 'deleteStock'])->name('admin.delete-stock'); //will delete the selected stock batch
    Route::post('/export-stocks', [StocksController::class, 'export'])->name('admin.export-stocks'); //will export or generate a excel file of a stock report of all the stock batches of every item

    ////////Dispense Report////////
    Route::get('/dispense', [DispenseController::class, 'dispense'])->name('admin.dispense'); // will show the dispense module
    // Route::get('/get-dispense', [DispenseController::class, 'getDispense'])->name('admin.get-dispense');
    Route::get('/filter-dispense', [DispenseController::class, 'dispenseFilter'])->name('admin.filter-dispense'); //will fetch the dispensed items data from database
    Route::post('/export-dispense', [DispenseController::class, 'dispenseExport'])->name('admin.export-dispense'); //will generate a excel file of a dispense report
    Route::get('/fetch-record/{id}', [DispenseController::class, 'fetchRecord'])->name('admin.fetch-record'); //will fetch the request record of the dipensed items

    ////////Request module routes////////
    Route::get('/requests', [AdminRequestController::class, 'adminRequest'])->name('admin.requests'); // will show the request module
    // Route::get('/show-requests', [AdminRequestController::class, 'showRequest'])->name('admin.show-requests'); // will fetch the request from database
    Route::get('/requested-items/{id}', [AdminRequestController::class, 'requestedItems'])->name('admin.requested-items'); //will show the request and its requested items
    Route::post('/accept-request/{rid}', [AdminRequestController::class, 'acceptRequest'])->name('admin.accept-request'); //will mark as accepted the request
    Route::post('/deliver-request/{rid}', [AdminRequestController::class, 'deliverRequest'])->name('admin.deliver-request'); //will mark as delivered the request
    // Route::post('/complete-request/{rid}', [AdminRequestController::class, 'completeRequest'])->name('admin.complete-request');
    Route::get('/show-pending-requests', [AdminRequestController::class, 'showPendingRequest'])->name('admin.show-pending-requests'); //will fetch the pending requests
    Route::get('/show-accepted-requests', [AdminRequestController::class, 'showAcceptedRequest'])->name('admin.show-accepted-requests'); //will fetch the accepted requests
    Route::get('/show-delivered-requests', [AdminRequestController::class, 'showDeliveredRequest'])->name('admin.show-delivered-requests'); //will fetch the delivered requests
    Route::get('/view-request/{id}', [AdminRequestController::class, 'viewRequest'])->name('admin.view-request'); //will view the selected request

    //Receipt routes
    Route::get('/generate-receipt/{rid}', [ReceiptController::class, 'generate_receipt'])->name('admin.generate-receipt'); //will generate a charge slip to print of a request

    ////////Request transaction////////
    Route::get('/transaction', [TransactionController::class, 'transaction'])->name('admin.transaction'); //will show the completed transactions in requests module
    Route::get('/all-transaction', [TransactionController::class, 'allTransaction'])->name('admin.all-transaction'); //will show the transactions module where you can find all the transaction
    Route::get('/show-transaction', [TransactionController::class, 'showTransaction'])->name('admin.show-transaction'); //will fetch the request transactions today
    Route::get('/filter-transaction', [TransactionController::class, 'filterTransaction'])->name('admin.filter-transaction'); //will fetch the completed transactions
    Route::get('/filter-all-transaction', [TransactionController::class, 'filterAllTransaction'])->name('admin.filter-all-transaction'); // will fetch all the transactions depending on the selected date
    Route::get('/search-transaction', [TransactionController::class, 'searchRequestCode'])->name('admin.search-transaction'); // will fetch the searched transaction by request code/id 
});

/*------------------------------------------
--------------------------------------------
All Manager Routes List
--------------------------------------------
--------------------------------------------*/
Route::prefix('manager')->middleware(['auth', 'user-access:manager'])->group(function () {

    Route::get('/dashboard', [ManagerController::class, 'managerHome'])->name('manager.home'); //will show manager dashboard module
    Route::get('/dashboard-display', [ManagerController::class, 'dashboardDisplay'])->name('manager.dashboard-display'); // this will get the dashboard data count
    Route::get('/show-pending', [ManagerRequestController::class, 'showPending'])->name('manager.show-pending'); // this will view the pending request to iframe on dashboard


    ////////Items module////////
    Route::get('/items', [ItemController::class, 'showAllItems'])->name('manager.items'); //will show the items module
    Route::get('/new-item', [ItemController::class, 'newItem'])->name('manager.new-item'); //will show the module where you can add new item
    Route::post('/save-item', [ItemController::class, 'saveItem'])->name('manager.saveItem'); //will save the new item to database
    Route::get('/show-item/{id}', [ItemController::class, 'showItem'])->name('manager.show-item'); //will show the items information on edit item module
    Route::post('/update-item/{id}', [ItemController::class, 'updateItem'])->name('manager.update-item'); //will update and save the edited information of an item
    Route::post('/insert-items', [ItemController::class, 'import'])->name('manager.insert-items'); // to import items and its information using csv format file
    Route::post('/export-items', [ItemController::class, 'export'])->name('manager.export-items'); // to export items information and generate excel file

    ////////Stocks module////////
    Route::get('/stocks', [StocksController::class, 'stocks'])->name('manager.stocks'); //will show the stocks module and the items available in stocks
    Route::get('/add-to-stocks/{id}', [StocksController::class, 'addToStocks'])->name('manager.add-to-stocks'); //will show all the stock batch of the selected item
    Route::post('/save-stock', [StocksController::class, 'saveStock'])->name('manager.save-stock'); //will save the new stock batch to the database
    Route::get('/add-stock/{id}', [StocksController::class, 'addStock'])->name('manager.add-stock'); //will show the add-stock module where you can return or remove to a stock batch
    Route::post('/update-stock/{id}', [StocksController::class, 'updateStock'])->name('manager.update-stock'); //will save or update added or removed quantity in a selected stock batch
    Route::get('/delete-stock/{id}', [StocksController::class, 'deleteStock'])->name('manager.delete-stock'); //will delete the selected stock batch
    Route::post('/export-stocks', [StocksController::class, 'export'])->name('manager.export-stocks'); //will export or generate a excel file of a stock report of all the stock batches of every item

    ////////Request module////////
    Route::get('/requests', [ManagerRequestController::class, 'managerRequest'])->name('manager.requests'); // will show the request module
    Route::get('/show-pending-requests', [ManagerRequestController::class, 'showPendingRequest'])->name('manager.show-pending-requests'); //will fetch the pending requests
    Route::get('/show-accepted-requests', [ManagerRequestController::class, 'showAcceptedRequest'])->name('manager.show-accepted-requests'); //will fetch the accepted requests
    Route::get('/show-delivered-requests', [ManagerRequestController::class, 'showDeliveredRequest'])->name('manager.show-delivered-requests'); //will fetch the completed transactions
    Route::get('/requested-items/{id}', [ManagerRequestController::class, 'requestedItems'])->name('manager.requested-items'); //will show the request and its requested items
    Route::post('/accept-request/{rid}', [ManagerRequestController::class, 'acceptRequest'])->name('manager.accept-request'); //will mark as accepted the request
    Route::post('/deliver-request/{rid}', [ManagerRequestController::class, 'deliverRequest'])->name('manager.deliver-request'); //will mark as delivered the request
    // Route::post('/complete-request/{rid}', [ManagerRequestController::class, 'completeRequest'])->name('manager.complete-request');
    Route::get('/view-request/{id}', [ManagerRequestController::class, 'viewRequest'])->name('manager.view-request'); //will view the selected request

    // Route::get('/doctor-list', [DoctorController::class, 'doctorList'])->name('manager.doctor-list');
    // Route::post('/save-doctor', [DoctorController::class, 'saveDoctor'])->name('manager.save-doctor');

    Route::get('/generate-receipt/{rid}', [ReceiptController::class, 'generate_receipt'])->name('manager.generate-receipt'); //will generate a charge slip to print of a request

    ////////Dispense Report////////
    Route::get('/dispense', [DispenseController::class, 'dispense'])->name('manager.dispense'); // will show the dispense module
    // Route::get('/get-dispense', [DispenseController::class, 'getDispense'])->name('manager.get-dispense');
    Route::get('/filter-dispense', [DispenseController::class, 'dispenseFilter'])->name('manager.filter-dispense'); //will fetch the dispensed items data from database
    Route::post('/export-dispense', [DispenseController::class, 'dispenseExport'])->name('manager.export-dispense'); //will generate a excel file of a dispense report
    Route::get('/fetch-record/{id}', [DispenseController::class, 'fetchRecord'])->name('manager.fetch-record'); //will fetch the request record of the dipensed items

    ////////Transaction////////
    Route::get('/transaction', [TransactionController::class, 'transaction'])->name('manager.transaction'); //will show the completed transactions in requests module
    Route::get('/all-transaction', [TransactionController::class, 'allTransaction'])->name('manager.all-transaction'); //will show the transactions module where you can find all the transaction
    Route::get('/show-transaction', [TransactionController::class, 'showTransaction'])->name('manager.show-transaction'); //will fetch the request transactions today
    Route::get('/filter-transaction', [TransactionController::class, 'filterTransaction'])->name('manager.filter-transaction'); //will fetch the completed transactions
    Route::get('/filter-all-transaction', [TransactionController::class, 'filterAllTransaction'])->name('manager.filter-all-transaction'); //will fetch all the transactions depending on the selected date
    Route::get('/search-transaction', [TransactionController::class, 'searchRequestCode'])->name('manager.search-transaction'); // will fetch the searched transaction by request code/id 
});

////////Logout////////
Route::get('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout1'); //will logout the current login account
