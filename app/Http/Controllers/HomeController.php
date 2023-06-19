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
    public function login()
    {
        return view('auth.login');
    }

    public function showInfo(Request $request)
    {
        //This will initiate all the categories available
        $categories = Item::distinct('category')->pluck('category');

        //This will will get the request from search input
        $search = $request->input("search");

        //This will will get the request from filter input
        $filter = $request->input('filter');

        //get the requested category
        $category = $request->category;

        $stocks = Item::leftJoin('item_stocks', 'items.id', '=', 'item_stocks.item_id')
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

        if ($category) {
            $stocks = $stocks->where('items.category', $category)
                ->orderBy('name');
        } else if ($search) {
            $stocks = $stocks->where(function ($query) use ($search) {
                $query->where('items.name', 'like', "%" . $search . "%")
                    ->orWhere('item_stocks.item_id', $search);
            })->orderBy('name');
        } else if ($filter === 'max') {
            $stocks = $stocks->havingRaw('total_quantity > items.max_limit')->orderBy('items.name');
        } else if ($filter === 'max') {
            $stocks = $stocks->havingRaw('total_quantity > items.max_limit')->orderBy('items.name');
        } else if ($filter === 'safe') {
            $stocks = $stocks->havingRaw('total_quantity <= items.max_limit AND total_quantity >= (items.max_limit * (items.warning_level / 100))')->orderBy('items.name');
        } else if ($filter === 'warning') {
            $stocks = $stocks->havingRaw('total_quantity < items.max_limit * (warning_level / 100)')->orderBy('items.name');
        } else if ($filter === 'no-stocks') {
            $stocks = $stocks->whereNotIn('items.id', function ($query) {
                $query->select('item_id')
                    ->from('item_stocks')
                    ->groupBy('item_id')
                    ->havingRaw('SUM(stock_qty) IS NOT NULL');
            })
                ->orderBy('items.name');
        }

        $stocks = $stocks->get();

        foreach ($stocks as $item) {
            $hasExpiredStocks = Stock::where('item_id', $item->id)
                ->where('exp_date', '<', Carbon::now()->format('Y-m-d'))
                ->exists();

            $isExpiringSoon = Stock::where('item_id', $item->id)
                ->where('exp_date', '<=', Carbon::now()->addMonth()->format('Y-m-d'))
                ->exists();

            $item->hasExpiredStocks = $hasExpiredStocks;
            $item->isExpiringSoon = $isExpiringSoon;
        }

        return view('auth.info')->with(['stocks' => $stocks, 'categories' => $categories, 'category' => $category, 'search' => $search, 'filter' => $filter]);
    }

    public function addToStocks(Request $request, $id)
    {
        $pettyCash = $request->input("petty-cash");
        $donation = $request->input("donation");
        $lgu = $request->input("lgu");

        //find item by id set to request
        $item = Item::find($id);

        $stocks = DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->select('items.*', 'item_stocks.*', DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as created_at"), DB::raw("DATE_FORMAT(MAX(item_stocks.updated_at), '%M %d, %Y, %h:%i:%s %p') as updated_at"))->where('item_stocks.item_id', $id)
            ->groupBy('item_stocks.id', 'item_stocks.item_id', 'item_stocks.stock_qty', 'item_stocks.exp_date', 'item_stocks.mode_acquisition', 'item_stocks.created_at', 'item_stocks.updated_at', 'items.id', 'items.name', 'items.category', 'items.description', 'items.unit', 'items.max_limit', 'items.warning_level', 'items.price', 'items.created_at', 'items.updated_at',)
            ->orderByDesc('item_stocks.created_at');

        if ($pettyCash) {
            $stocks->where('item_stocks.mode_acquisition', '=', 'Petty Cash');
        } else if ($donation) {
            $stocks->where('item_stocks.mode_acquisition', '=', 'Donation');
        } else if ($lgu) {
            $stocks->where('item_stocks.mode_acquisition', '=', 'LGU');
        }

        $stocks = $stocks->get();

        foreach ($stocks as $stock) {
            $stock->exp_date = Carbon::parse($stock->exp_date)->format("Y-m-d");
        }

        $totalStocks = DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->select(DB::raw('SUM(item_stocks.stock_qty) as total_stocks'))->where('item_stocks.item_id', $id)->get();

        if ($stocks) {
            return view('auth.stocks.add-to-stock')->with([
                'item' => $item,
                'stocks' => $stocks,
                'total_stocks' => $totalStocks[0]->total_stocks,
            ]);
        } else {
            return view('auth.stocks.add-to-stock')->with('item', $item);
        }
    }

    public function dispenseView()
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

    public function dispenseFilter(Request $request)
    {
        $completedAndDeliveredId = ModelsRequest::whereIn('status', ['completed', 'delivered'])->pluck('id');

        $today = $request->input('today');
        $yesterday = $request->input('yesterday');
        $thisMonth = $request->input('this-month');


        if ($today) {
            // Filter dispensed items that occurred today
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        } elseif ($yesterday) {
            // Filter dispensed items that occurred yesterday
            $from = Carbon::yesterday()->startOfDay();
            $to = Carbon::yesterday()->endOfDay();
        } elseif ($thisMonth) {
            // Filter dispensed items that occurred this month
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfMonth();
        } else {
            // Filter dispensed items that occurred from selected date
            $from = $request->input('date_from');
            $to = $request->input('date_to');
            $to = date('Y-m-d', strtotime($to . ' +1 day'));
        }


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

    public function fetchRecord(Request $request, $id)
    {
        $today = $request->input('today');
        $yesterday = $request->input('yesterday');
        $thisMonth = $request->input('this-month');
        $filter = $request->input('filter');

        if ($today) {
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        } else if ($yesterday) {
            $from = Carbon::yesterday()->startOfDay();
            $to = Carbon::yesterday()->endOfDay();
        } else if ($thisMonth) {
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfMonth();
        } else if ($filter) {
            $from = $request->input('from');
            $to = $request->input('to');
            $to = Carbon::createFromFormat('Y-m-d', $to)->addDay();
        } else {
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
            // $from = Carbon::now()->startOfMonth();
            // $to = Carbon::now()->endOfMonth();
        }

        $record = Request_Item::join('request', 'request_items.request_id', '=', 'request.id')
            ->whereIn('request.status', ['completed', 'delivered'])
            ->where('request_items.item_id', $id)
            ->whereBetween('request_items.created_at', [$from, $to])
            ->get();

        foreach ($record as $rec) {
            $rec->formatDate = Carbon::parse($rec->created_at)->format("F d, Y h:i:s A");
        }


        return response()->json($record);
    }

    public function viewRequest($id)
    {
        $request = ModelsRequest::where('id', $id)->first();
        $request->formatted_date = Carbon::parse($request->created_at)->format('F j, Y, g:i:s a');

        $items = Request_Item::where('request_id', $id)->get();

        $requestItems = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->select('request_items.*', 'items.*')
            ->where('request_items.request_id', $id)
            ->orderBy('request_items.created_at', 'asc')
            ->get();

        $canceled = Canceled_Request::where('request_id', $id)->first();

        if ($canceled) {
            $canceled->format_date = Carbon::parse($canceled->created_at)->format('F j, Y, g:i:s a');

            return view('auth.view-request')->with([
                'request' => $request,
                'items' => $items,
                'requestItems' => $requestItems,
                'canceled' => $canceled,
            ]);
        } else {
            return view('auth.view-request')->with([
                'request' => $request,
                'items' => $items,
                'requestItems' => $requestItems,
            ]);
        }
    }

    public function transaction()
    {
        return view('auth.all-transaction');
    }

    public function filterAllTransaction(Request $request)
    {
        $thisDay = $request->input('this-day');
        $thisWeek = $request->input('this-week');
        $thisMonth = $request->input('this-month');
        $filter = $request->input('filter');

        if ($thisDay) {
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        } else if ($thisWeek) {
            $from = Carbon::now()->startOfWeek();
            $to = Carbon::now()->endOfWeek();
        } else if ($thisMonth) {
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfMonth();
        } else if ($filter) {
            $from = $request->input('from');
            $to = $request->input('to');
            $to = date('Y-m-d', strtotime($to . ' +1 day'));
        } else {
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        }

        $query = ModelsRequest::where('status', '!=', 'pending')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'asc');

        $data = $query->get();

        // Loop through the data and format the created_at timestamp
        foreach ($data as $req) {
            $req->formatted_date = date('F j, Y, g:i:s a', strtotime($req->created_at));
        }

        return response()->json($data);
    }

    public function searchRequestCode(Request $request)
    {
        $searchReqCode = $request->input('req-code');

        $query = ModelsRequest::where('status', '!=', 'pending')
            ->orderBy('created_at', 'asc');

        if ($searchReqCode) {
            $query->where('id', 'like', $searchReqCode);
        }

        $data = $query->get();

        foreach ($data as $req) {
            $req->formatted_date = date('F j, Y, g:i:s a', strtotime($req->created_at));
        }

        return response()->json($data);
    }
}
