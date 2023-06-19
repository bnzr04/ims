<?php

namespace App\Http\Controllers;

use App\Exports\DispenseExport;
use App\Models\Item;
use App\Models\Log;
use App\Models\Request as ModelsRequest;
use App\Models\Request_Item;
use App\Models\Stock;
use App\Models\Stock_Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DispenseController extends Controller
{
    //////////LOG//////////

    /////Log first part//////////
    public function startLog()
    {
        DB::enableQueryLog();

        $user = Auth::user();
        $user_id = $user->id;
        $user_type = $user->type;
        if ($user_type === 'manager') {
            $user_dept = $user->dept;
        } else {
            $user_dept = "";
        }

        return [$user_id, $user_type, $user_dept];
    }

    /////Log last part//////////
    public function endLog($user_id, $user_type, $user_dept, $message)
    {
        if ($user_type === 'manager') {
            $user_type = $user_type;
        }

        // Get the SQL query being executed
        $sql = DB::getQueryLog();
        if (is_array($sql) && count($sql) > 0) {
            $last_query = end($sql)['query'];
        } else {
            $last_query = 'No query log found.';
        }

        // Log the data to the logs table
        Log::create([
            'user_id' => $user_id,
            'user_type' => $user_type,
            'message' => $message,
            'query' => $last_query,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    //////////LOG END////////////

    ///////////////Dispense report///////////////
    public function dispense()
    {
        $user = Auth::user();

        if ($user->type === 'manager') {
            return view('manager.sub-page.dispense.dispense');
        } else {
            return view('admin.sub-page.dispense.dispense');
        }
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

        $moaFilter = $request->input('moa');


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
            ->orderBy('items.name', 'asc');

        if ($moaFilter) {

            if ($moaFilter == 'petty-cash') {
                $data =  $data->where('request_items.mode_acquisition', 'Petty Cash');
            } else if ($moaFilter == 'donation') {
                $data =  $data->where('request_items.mode_acquisition', 'Donation');
            } else if ($moaFilter == 'lgu') {
                $data =  $data->where('request_items.mode_acquisition', 'LGU');
            }
        }

        $data = $data->get();

        foreach ($data as $stock) {
            $stockQty = Stock_Log::where('item_id', $stock->item_id);

            $stockQty = $stockQty->where('transaction_type', 'addition')
                ->whereBetween('created_at', [$from, $to])
                ->value(DB::raw('SUM(quantity)'));

            $stock->stock_qty = $stockQty;

            if ($moaFilter) {
                $acquired = Stock_Log::where('item_id', $stock->item_id);

                if ($moaFilter == 'petty-cash') {
                    $acquired =  $acquired->where('mode_acquisition', 'Petty Cash');
                } else if ($moaFilter == 'donation') {
                    $acquired =  $acquired->where('mode_acquisition', 'Donation');
                } else if ($moaFilter == 'lgu') {
                    $acquired =  $acquired->where('mode_acquisition', 'LGU');
                }

                $acquired = $acquired->where('transaction_type', 'addition')
                    ->whereBetween('created_at', [$from, $to])
                    ->value(DB::raw('SUM(quantity)'));

                $stock->acquired = $acquired;
            }
        }

        return response()->json($data);
    }


    public function dispenseExport(Request $request)
    {
        $req =  ucfirst($request->input('filter'));

        list($user_id, $user_type, $user_dept) = $this->startLog();

        $message = "Dispensed " . $req . " Report Downloaded";

        $filename = 'Pharma_Dispensed_Item' . Carbon::now()->format('Ymd-His') . '.xlsx';
        $response = Excel::download(new DispenseExport($filename), $filename, \Maatwebsite\Excel\Excel::XLSX, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);

        $this->endLog($user_id, $user_type, $user_dept, $message);

        return $response;
    }

    public function fetchRecord(Request $request, $id)
    {
        $today = $request->input('today');
        $yesterday = $request->input('yesterday');
        $thisMonth = $request->input('this-month');
        $filter = $request->input('filter');

        $moaFilter = $request->input('moa');

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
        }

        $record = Request_Item::join('request', 'request_items.request_id', '=', 'request.id')
            ->whereIn('request.status', ['completed', 'delivered'])
            ->where('request_items.item_id', $id)
            ->whereBetween('request_items.created_at', [$from, $to]);
        // ->where('request_items.mode_acquisition', 'Petty Cash');

        if ($moaFilter) {

            if ($moaFilter == 'petty-cash') {
                $record =  $record->where('request_items.mode_acquisition', 'Petty Cash');
            } else if ($moaFilter == 'donation') {
                $record =  $record->where('request_items.mode_acquisition', 'Donation');
            } else if ($moaFilter == 'lgu') {
                $record =  $record->where('request_items.mode_acquisition', 'LGU');
            }
        }

        $record = $record->get();

        foreach ($record as $rec) {
            $rec->formatDate = Carbon::parse($rec->created_at)->format("F d, Y h:i:s A");
        }


        return response()->json($record);
    }
}
