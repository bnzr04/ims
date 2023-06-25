<?php

namespace App\Http\Controllers;

use App\Models\Canceled_Request;
use App\Models\Item;
use App\Models\Log;
use App\Models\Request as ModelsRequest;
use App\Models\Request_Item;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function login() //this login function will redirect to login view
    {
        return view('auth.login');
    }

    public function showInfo(Request $request) //this showInfo function will redirect to info view where item stocks can see 
    {
        //This will initiate all the categories available
        $categories = Item::distinct('category')->pluck('category');

        //This will will get the request from search input
        $search = $request->input("search");

        //This will will get the request from filter input
        $filter = $request->input('filter');

        //get the requested category
        $category = $request->category;

        $stocks = Item::leftJoin('item_stocks', 'items.id', '=', 'item_stocks.item_id') //this query will fetch all the items and its quantity in stocks
            ->select(
                'items.id',
                'items.name',
                'items.description',
                'items.category',
                'items.unit',
                'items.max_limit',
                'items.warning_level',
                'items.price',
                DB::raw('SUM(item_stocks.stock_qty) as total_quantity'),
                DB::raw('COUNT(item_stocks.item_id) as stocks_batch'),
                DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as latest_stock")
            )
            ->groupBy('items.id', 'items.name', 'items.description', 'items.category', 'items.unit', 'items.max_limit', 'items.warning_level', 'items.price')
            ->orderBy('items.name');

        if ($category) { //if the category filter is activated the items will filtered to the selected category
            $stocks = $stocks->where('items.category', $category)
                ->orderBy('name');
        } else if ($search) { //if the user search an item 
            $stocks = $stocks->where(function ($query) use ($search) {
                $query->where('items.name', 'like', "%" . $search . "%")
                    ->orWhere('item_stocks.item_id', $search);
            })->orderBy('name');
        } else if ($filter === 'max') { //if the user clicked the 'Over Max Limit' to filter all the items with over the max limit stocks
            $stocks = $stocks->havingRaw('total_quantity > items.max_limit')->orderBy('items.name');
        } else if ($filter === 'safe') { //if the user clicked the 'Safe Level' to filter all the items with safe stocks
            $stocks = $stocks->havingRaw('total_quantity <= items.max_limit AND total_quantity >= (items.max_limit * (items.warning_level / 100))')->orderBy('items.name');
        } else if ($filter === 'warning') { //if the user clicked the 'Warning Level' to filter all the items with warning level stocks
            $stocks = $stocks->havingRaw('total_quantity < items.max_limit * (warning_level / 100)')->orderBy('items.name');
        } else if ($filter === 'no-stocks') { //if the user clicked the 'No Stocks' to filter all the items with no stocks
            $stocks = $stocks->whereNotIn('items.id', function ($query) {
                $query->select('item_id')
                    ->from('item_stocks')
                    ->groupBy('item_id')
                    ->havingRaw('SUM(stock_qty) IS NOT NULL');
            })
                ->orderBy('items.name');
        }

        $stocks = $stocks->get(); // fetch the data to database

        foreach ($stocks as $item) {
            $hasExpiredStocks = Stock::where('item_id', $item->id) //get if the item has a expired stock batch
                ->where('exp_date', '<', Carbon::now()->format('Y-m-d'))
                ->exists();

            $isExpiringSoon = Stock::where('item_id', $item->id) //get if the item has a stock batch that expiring in the next month
                ->where('exp_date', '<=', Carbon::now()->addMonth()->format('Y-m-d'))
                ->exists();

            $item->hasExpiredStocks = $hasExpiredStocks;
            $item->isExpiringSoon = $isExpiringSoon;
        }

        return view('auth.info')->with(['stocks' => $stocks, 'categories' => $categories, 'category' => $category, 'search' => $search, 'filter' => $filter]);
    }

    public function addToStocks(Request $request, $id) //this function will view the stock batches of an item
    {
        $pettyCash = $request->input("petty-cash"); //get the request if the petty-cash is clicked
        $donation = $request->input("donation"); //get the request if the donation is clicked
        $lgu = $request->input("lgu"); //get the request if the lgu is clicked

        //find item by its id
        $item = Item::find($id);

        $stocks = DB::table('item_stocks') //get the item details of the selected item using its item_id
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->select('items.*', 'item_stocks.*', DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as created_at"), DB::raw("DATE_FORMAT(MAX(item_stocks.updated_at), '%M %d, %Y, %h:%i:%s %p') as updated_at"))->where('item_stocks.item_id', $id)
            ->groupBy('item_stocks.id', 'item_stocks.item_id', 'item_stocks.stock_qty', 'item_stocks.exp_date', 'item_stocks.mode_acquisition', 'item_stocks.created_at', 'item_stocks.updated_at', 'items.id', 'items.name', 'items.category', 'items.description', 'items.unit', 'items.max_limit', 'items.warning_level', 'items.price', 'items.created_at', 'items.updated_at',)
            ->orderByDesc('item_stocks.created_at');

        if ($pettyCash) { //if the petty-cash is clicked
            $stocks->where('item_stocks.mode_acquisition', '=', 'Petty Cash'); //filter the stock batch where mode_acquisition with the value of 'Petty Cash'
        } else if ($donation) { //if the donation is clicked
            $stocks->where('item_stocks.mode_acquisition', '=', 'Donation'); //filter the stock batch where mode_acquisition with the value of 'Donation'
        } else if ($lgu) { //if the lgu is clicked
            $stocks->where('item_stocks.mode_acquisition', '=', 'LGU'); //filter the stock batch where mode_acquisition with the value of 'LGU'
        }

        $stocks = $stocks->get(); //get the query

        foreach ($stocks as $stock) {
            $stock->exp_date = Carbon::parse($stock->exp_date)->format("Y-m-d"); //format the exp_date to 'Y-m-d'
        }

        $totalStocks = DB::table('item_stocks') //get the total stocks of the selected item
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->select(DB::raw('SUM(item_stocks.stock_qty) as total_stocks'))->where('item_stocks.item_id', $id)->get();

        if ($stocks) { //if there is a stock in an item return the add-to-stock view with the fetched data
            return view('auth.stocks.add-to-stock')->with([
                'item' => $item,
                'stocks' => $stocks,
                'total_stocks' => $totalStocks[0]->total_stocks,
            ]);
        } else { //if there is no stock batch return to add-to-stock view with only item information
            return view('auth.stocks.add-to-stock')->with('item', $item);
        }
    }

    public function dispenseView() // will return the dispense view
    {
        return view('auth.dispense');
    }

    public function getDispense()
    {
        $completedAndDeliveredId = ModelsRequest::whereIn('status', ['completed', 'delivered'])->pluck('id');

        // Filter dispensed items that occurred today
        $from = Carbon::now()->startOfDay();
        $to = Carbon::now()->endOfDay();

        $data = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->select('request_items.item_id', 'items.name', 'items.description', 'items.category', 'items.unit', DB::raw('SUM(request_items.quantity) as total_dispense'))
            ->distinct()
            ->whereIn('request_id', $completedAndDeliveredId)
            ->whereBetween('request_items.updated_at', [$from, $to])
            ->groupBy('request_items.item_id', 'items.name', 'items.description', 'items.category', 'items.unit')
            ->orderBy('items.name', 'asc')
            ->get();

        return response()->json($data);
    }

    public function dispenseFilter(Request $request) // this will fetch all the dispensed items
    {
        $completedAndDeliveredId = ModelsRequest::whereIn('status', ['completed', 'delivered'])->pluck('id'); //select all the request with the status of 'completed' and 'delivered' and pluck the id's

        $today = $request->input('today'); //get the request if the today is clicked
        $yesterday = $request->input('yesterday'); //get the request if the yesterday is clicked
        $thisMonth = $request->input('this-month'); //get the request if the this-month is clicked


        if ($today) { //if the today is true/clicked

            // Filter dispensed items that occurred today
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        } elseif ($yesterday) { //if the yesterday is true/clicked

            // Filter dispensed items that occurred yesterday
            $from = Carbon::yesterday()->startOfDay();
            $to = Carbon::yesterday()->endOfDay();
        } elseif ($thisMonth) { //if the this-month is true/clicked

            // Filter dispensed items that occurred this month
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfMonth();
        } else { //else the date input is true or the user select the date

            // Filter dispensed items that occurred from selected date
            $from = $request->input('date_from'); //get the value from date_from input
            $to = $request->input('date_to'); //get the value from date_to input
            $to = date('Y-m-d', strtotime($to . ' +1 day')); //+1 day to date_to value
        }

        $data = Request_Item::join('items', 'request_items.item_id', '=', 'items.id') //join the request_items and items table
            ->select('request_items.item_id', 'items.name', 'items.description', 'items.category', 'items.unit', DB::raw('SUM(request_items.quantity) as total_dispense')) //get the item information of the requested items
            ->distinct()
            ->whereIn('request_id', $completedAndDeliveredId) //select all the request_id with the status of 'completed' and 'delivered'
            ->whereBetween('request_items.updated_at', [$from, $to]) //filter the data with the updated_at
            ->groupBy('request_items.item_id', 'items.name', 'items.description', 'items.category', 'items.unit')
            ->orderBy('items.name', 'asc')
            ->get();

        return response()->json($data); //return the $data by json format
    }

    public function fetchRecord(Request $request, $id)
    {
        $today = $request->input('today'); //get the request if the today is clicked
        $yesterday = $request->input('yesterday'); //get the request if the yesterday is clicked
        $thisMonth = $request->input('this-month'); //get the request if the this-month is clicked
        $filter = $request->input('filter'); //get the request if the filter is clicked

        if ($today) {
            //set the $from and $to to the start and the end of the day
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        } else if ($yesterday) {
            //set the $from and $to to the start and the end of the day of yesterday
            $from = Carbon::yesterday()->startOfDay();
            $to = Carbon::yesterday()->endOfDay();
        } else if ($thisMonth) {
            //set the $from and $to to the start and the end of the month
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfMonth();
        } else if ($filter) {
            //set the value of $from to the value if request input 'from' and the $to to request input 'to'
            $from = $request->input('from');
            $to = $request->input('to');
            $to = Carbon::createFromFormat('Y-m-d', $to)->addDay();
        } else {
            //set the $from and $to to the start and the end of the day as default
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        }

        //get all the request items of the request where the request status is completed and delivered with the selected date
        $record = Request_Item::join('request', 'request_items.request_id', '=', 'request.id')
            ->whereIn('request.status', ['completed', 'delivered'])
            ->where('request_items.item_id', $id)
            ->whereBetween('request_items.created_at', [$from, $to])
            ->get();

        foreach ($record as $rec) {
            $rec->formatDate = Carbon::parse($rec->created_at)->format("F d, Y h:i:s A"); // format the created_at to the readable date format
        }

        return response()->json($record); //return the $record by json format
    }

    public function viewRequest($id) //this function will return the view-request view with the request and its requested items data
    {
        $request = ModelsRequest::where('id', $id)->first(); //get the request information of the selected request $id

        $request->formatted_date = Carbon::parse($request->created_at)->format('F j, Y, g:i:s a'); //format the created_at to the readable format

        $items = Request_Item::where('request_id', $id)->get(); //get all the requested items of the request $id

        $requestItems = Request_Item::join('items', 'request_items.item_id', '=', 'items.id') //join the request_items and items table
            ->select('request_items.*', 'items.*') //select all the request_items and items column
            ->where('request_items.request_id', $id) //get the data where the request_id is equal to $id
            ->orderBy('request_items.created_at', 'asc') //order the created_at as ascending
            ->get();

        $canceled = Canceled_Request::where('request_id', $id)->first(); //get the the canceled request by the $id to canceled_requests table

        if ($canceled) { //if the canceled is true meaning the request $id is canceled
            $canceled->format_date = Carbon::parse($canceled->created_at)->format('F j, Y, g:i:s a'); //format the created_at to the readable format

            return view('auth.view-request')->with([ //return the view-request view with all the request data and $canceled
                'request' => $request,
                'items' => $items,
                'requestItems' => $requestItems,
                'canceled' => $canceled,
            ]);
        } else {
            return view('auth.view-request')->with([ //return the view-request view with all the request data
                'request' => $request,
                'items' => $items,
                'requestItems' => $requestItems,
            ]);
        }
    }

    public function transaction() //this function will return the all-transaction view where the user can find all the request
    {
        return view('auth.all-transaction');
    }

    public function filterAllTransaction(Request $request)
    {
        $thisDay = $request->input('this-day'); //get the request if the 'this-day' is clicked
        $thisWeek = $request->input('this-week'); //get the request if the 'this-week' is clicked
        $thisMonth = $request->input('this-month'); //get the request if the 'this-month' is clicked
        $filter = $request->input('filter'); //get the request if the filter is clicked or the submit button choice date

        if ($thisDay) { //if $thisDay is true

            //set the $from and $to to the start and the end of the day
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        } else if ($thisWeek) { //if $thisWeek is true

            //set the $from and $to to the start and the end of the week
            $from = Carbon::now()->startOfWeek();
            $to = Carbon::now()->endOfWeek();
        } else if ($thisMonth) { //if $thisMonth is true

            //set the $from and $to to the start and the end of the month
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfMonth();
        } else if ($filter) { //if $filter is true

            //set the value of $from to the value if request input 'from' and the $to to request input 'to'
            $from = $request->input('from');
            $to = $request->input('to');
            $to = date('Y-m-d', strtotime($to . ' +1 day'));
        } else {

            //set the $from and $to to the start and the end of the day as default
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        }

        $query = ModelsRequest::where('status', '!=', 'pending') //get all the request status but not the request with 'pending' status
            ->whereBetween('created_at', [$from, $to]) //filter the created_at to $from and $to
            ->orderBy('created_at', 'asc');

        $data = $query->get(); //get the query

        // Loop through the data and format the created_at timestamp
        foreach ($data as $req) {
            $req->formatted_date = date('F j, Y, g:i:s a', strtotime($req->created_at));
        }

        return response()->json($data);
    }

    public function searchRequestCode(Request $request) //this function will fetch the request by the request code/id
    {
        $searchReqCode = $request->input('req-code'); //get the value of the req-code input

        $query = ModelsRequest::where('status', '!=', 'pending') //fetch all the request, request status 'pending' is excluded
            ->orderBy('created_at', 'asc');

        if ($searchReqCode) { //if the $searchReqCode is true
            $query->where('id', 'like', $searchReqCode); //find the request id by the $searchReqCode value
        }

        $data = $query->get(); //get query and fetch the data

        foreach ($data as $req) {
            $req->formatted_date = date('F j, Y, g:i:s a', strtotime($req->created_at)); //format the created_at to a readable format
        }

        return response()->json($data);
    }
}
