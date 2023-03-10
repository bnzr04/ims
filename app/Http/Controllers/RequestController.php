<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Request as ModelsRequest;
use App\Models\Request_Item;
use Illuminate\Http\Request;

class RequestController extends Controller
{

    public function newRequest(Request $request)
    {
        $items = Item::query();
        $searchItem = $request->search_item;

        if ($searchItem !== null) {
            $items =
                $items->where(function ($query) use ($searchItem) {
                    $query->where('name', 'like', '%' . $searchItem . '%')
                        ->orWhere('id', $searchItem);
                })->get();
        }

        return view('user.sub-page.new-request')->with(['items' => $items, 'search_item' => $searchItem]);
    }

    public function saveRequest(Request $request)
    {
        $model = new ModelsRequest;

        $model->user_id = $request->user_id;
        $model->office = $request->office;
        $model->request_by = $request->request_by;
        $model->request_to = $request->request_to;
        $model->save();

        foreach ($request->items as $itemId => $itemData) {
            if (isset($itemData['selected']) && $itemData['selected']) {
                $newItem = new Request_Item();
                $newItem->request_id = $model->id;
                $newItem->item_id = $itemId;
                $newItem->quantity = $itemData['quantity'];
                $newItem->save();
            }
        }

        return back()->with('success', 'Request successfully created');
    }

    public function deleteRequest($id)
    {
        $request = ModelsRequest::find($id);
        $request->delete();

        if ($request == true) {
            return back()->with('success', 'Request successfully deleted.');
        } else {
            return back()->with('error', 'Request failed to deleted.');
        }
    }
}
