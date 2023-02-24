<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function GuzzleHttp\Promise\all;

class StocksController extends Controller
{
    public function stocks()
    {
        $stocks = DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->select('items.*', 'item_stocks.*')
            ->get();

        return view('admin.stocks')->with('stocks', $stocks);
    }

    public function addToStocks($id)
    {
        $item = Item::find($id);
        $stocks = DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->select('items.*', 'item_stocks.*')->where('item_stocks.item_id', $id)->get();

        if ($stocks) {
            return view('admin.sub-page.stocks.add-to-stock')->with([
                'item' => $item,
                'stocks' => $stocks
            ]);
        } else {
            return view('admin.sub-page.stocks.add-to-stock')->with('item', $item);
        }
    }

    public function saveStock(Request $request)
    {

        $save = new Stock;

        $save->item_id = $request->item_id;
        $save->stock_qty = $request->stock_qty;
        $save->exp_date = $request->exp_date;
        $save->save();

        return back()->with('success', 'Item is successfully added to stocks.');
    }
}
