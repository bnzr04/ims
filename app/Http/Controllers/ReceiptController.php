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
    public function generate_receipt($rid) //this function will return the request information, the parameter $rid is the request id
    {
        $user = Auth::user(); // get the authenticated user information
        $user_name = $user->name;

        $request = ModelsRequest::find($rid); //find the request by request id 

        if ($request) {
            $request->format_created_at = Carbon::parse($request->created_at)->format('F d, Y h:i:s A'); //format the created_at of request
        }

        //join the request_items and items table, this will get the items that requested in the request and will get also the item information
        $items = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->where('request_id', $rid)->get();

        $total_amount = 0; // initialize total_amount to 0

        foreach ($items as $item) {

            //get the stock_qty of the stock_id
            $stock = Stock::select('stock_qty')
                ->where('id', $item->stock_id)
                ->first();

            $item->remaining = empty($stock->stock_qty) ? "0" : $stock->stock_qty; //if the stock_qty is empty store the value '0' else if its not empty store the stock_qty value
            $item->amount = number_format($item->quantity * $item->price, 2); //multiply the item quantity and item price and format the value to decimal of 2
            $total_amount += $item->quantity * $item->price; // add item amount to total amount
        }

        $total_amount = number_format($total_amount, 2); // format total amount with 2 decimal places

        return view("pdf.request")->with([
            'request' => $request,
            'items' => $items,
            'total_amount' => $total_amount,
        ]);
    }
}
