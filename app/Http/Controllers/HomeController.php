<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Log;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

    public function addToStocks($id)
    {

        //find item by id set to request
        $item = Item::find($id);

        $stocks = DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->select('items.*', 'item_stocks.*', DB::raw("DATE_FORMAT(MAX(item_stocks.created_at), '%M %d, %Y, %h:%i:%s %p') as created_at"), DB::raw("DATE_FORMAT(MAX(item_stocks.updated_at), '%M %d, %Y, %h:%i:%s %p') as updated_at"))->where('item_stocks.item_id', $id)
            ->groupBy('item_stocks.id', 'item_stocks.item_id', 'item_stocks.stock_qty', 'item_stocks.exp_date', 'item_stocks.mode_acquisition', 'item_stocks.created_at', 'item_stocks.updated_at', 'items.id', 'items.name', 'items.category', 'items.description', 'items.unit', 'items.max_limit', 'items.warning_level', 'items.price', 'items.created_at', 'items.updated_at',)
            ->orderByDesc('item_stocks.created_at')
            ->get();

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

    public function dashboardDisplay()
    {
        $log = Log::where('user_type', 'user');

        return view('auth.info')->with(['logs' => $log]);
    }
}
