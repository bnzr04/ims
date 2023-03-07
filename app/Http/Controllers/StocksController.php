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

    public function editStock($id)
    {
        return view('admin.sub-page.stocks.edit-stock');
    }

    public function addStock($id)
    {
        $stock = Stock::find($id);
        $item = Item::find($id);
        $stockItem = $stock->item_id;

        if ($stock !== null) {
            return view('admin.sub-page.stocks.add-stock')->with(['stock' => $stock, 'item' => $stockItem]);
        }

        return view('admin.sub-page.stocks.add-stock')->with(['item' => $item]);
    }

    public function updateStock(Request $request, $id)
    {
        $stock = Stock::find($id);

        $operation = $request->operation;
        $currentStockQty = $stock->stock_qty;
        $toStockQty = $request->new_stock;
        if ($operation == 'remove') {
            $newStockQty = $currentStockQty - $toStockQty;
        } else {
            $newStockQty = $currentStockQty + $toStockQty;
        }

        $stock->stock_qty = $newStockQty;
        $stock->save();

        return back()->with('success', 'Stock Successfully Updated');
    }

    public function deleteStock($id)
    {
        $item = Stock::find($id);
        $item->delete();

        if ($item) {
            return back()->with('success', 'Stock successfully deleted.');
        } else {
            return back()->with('error', 'Stock failed to delete.');
        }
    }
}
