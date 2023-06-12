<?php

namespace App\Http\Controllers;

use App\Models\Request as ModelsRequest;
use App\Models\Request_Item;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    public function generate_receipt($rid)
    {
        $user = Auth::user();
        $user_name = $user->name;

        $request = ModelsRequest::find($rid);

        if ($request) {
            $request->format_created_at = Carbon::parse($request->created_at)->format('F d, Y h:i:s A');
        }

        $items = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->where('request_id', $rid)->get();
        $total_amount = 0; // initialize total amount to 0

        foreach ($items as $item) {
            $stock = Stock::select('stock_qty')
                ->where('id', $item->stock_id)
                ->first();
            $item->remaining = empty($stock->stock_qty) ? "0" : $stock->stock_qty;
            $item->amount = number_format($item->quantity * $item->price, 2);
            $total_amount += $item->quantity * $item->price; // add item amount to total amount
        }

        $total_amount = number_format($total_amount, 2); // format total amount with 2 decimal places

        return view("pdf.request")->with([
            'request' => $request,
            'items' => $items,
            'total_amount' => $total_amount, // pass total amount to view
        ]);
    }
}
