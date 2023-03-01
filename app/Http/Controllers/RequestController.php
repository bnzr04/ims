<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Request as ModelsRequest;
use Illuminate\Http\Request;

class RequestController extends Controller
{

    public function newRequest()
    {
        $items = Item::join('item_stocks', 'items.id', '=', 'item_stocks.item_id')
            ->select('items.*', 'item_stocks.*')
            ->get();
        return view('user.sub-page.new-request')->with(['items' => $items]);
    }

    public function saveRequest(Request $request)
    {
        $model = new ModelsRequest;

        $model->user_id = $request->user_id;
        $model->office = $request->office;
        $model->request_by = $request->request_by;
        $model->request_to = $request->request_to;
        $model->save();

        return back()->with('success', 'Request successfully created');
    }
}
