<?php

namespace App\Exports;

use App\Models\Request;
use App\Models\Request_Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DispenseExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function headings(): array //this is the headings of the report
    {
        $filter = request()->input('filter'); //get the value of 'filter' input

        if ($filter === 'today') { //if $filter value is 'today'
            $title = Carbon::now()->format('F d, Y'); //store the current date and format like 'March 10, 2023'
        } elseif ($filter === 'yesterday') { //if $filter value is 'yesterday'
            $title = Carbon::yesterday()->format('F d, Y'); //store the yesterday date and format like 'March 10, 2023'
        } elseif ($filter === 'this-month') { //if $filter value is 'this-month'
            $from = Carbon::now()->startOfMonth()->format('F d, Y'); //store the start of the month date format like 'March 10, 2023'
            $to = Carbon::now()->startOfDay()->format('F d, Y'); //store the current date format like 'March 10, 2023'
            $title = "From: " . $from . " - To: " . $to; //store format like 'From: March 1, 2023 - To: March 15, 2023'
        } else {
            $from = Carbon::parse(request()->input('date_from'))->format('F d, Y'); //store the value of 'date_from' and format the value like 'March 15, 2023'
            $to = Carbon::parse(request()->input('date_to'))->format('F d, Y'); //store the value of 'date_to' and format the value like 'March 15, 2023'
            $title = "From: " . $from . " - To: " . $to; //store format like 'From: March 1, 2023 - To: March 15, 2023'
        }

        //add this to the header of the excel file
        return [
            [
                'DISPENSE ITEMS REPORT' //show this to 1st row
            ],
            [$title], //show the $title value on 2nd row
            [], //add blank space on 3rd row
            [ //show this to header of the data
                'Item ID',
                'Name',
                'Description',
                'Category',
                'Unit',
                'Total Dispense',
            ]
        ];
    }

    public function collection()
    {
        $completedAndDeliveredId = Request::whereIn('status', ['completed', 'delivered'])->pluck('id');

        $filter = request()->input('filter'); //store the value of 'filter' input

        $moaFilter = request()->input('moa'); //store the value of 'moa' input

        if ($filter === 'today') {
            // Filter dispensed items that occurred today
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        } elseif ($filter === 'yesterday') {
            // Filter dispensed items that occurred yesterday
            $from = Carbon::yesterday()->startOfDay();
            $to = Carbon::yesterday()->endOfDay();
        } elseif ($filter === 'this-month') {
            // Filter dispensed items that occurred this month
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfMonth();
        } else {
            // Filter dispensed items that occurred from selected date
            $from = request()->input('date_from');
            $to = request()->input('date_to');
            $to = date('Y-m-d', strtotime($to . ' +1 day'));
        }

        //join the request_items and items table, this will get all the items that requested and with the request status of 'completed' or 'delivered', and retrieve the data between the date period $from and $to
        $data = Request_Item::join('items', 'request_items.item_id', '=', 'items.id')
            ->select('request_items.item_id', 'items.name', 'items.description', 'items.category', 'items.unit', DB::raw('SUM(request_items.quantity) as total_dispense'))
            ->distinct()
            ->whereIn('request_id', $completedAndDeliveredId)
            ->whereBetween('request_items.updated_at', [$from, $to])
            ->groupBy('request_items.item_id', 'items.name', 'items.description', 'items.category', 'items.unit')
            ->orderBy('items.name', 'asc')
            ->get();

        return $data;
    }

    public function map($row): array
    {
        //return the value of data column of every row
        return [
            $row->item_id,
            $row->name,
            $row->description,
            $row->category,
            $row->unit,
            $row->total_dispense,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        //set the font as bold from A1 to F1
        $style = $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        // Apply bold font to the header row from A4 to F4
        $sheet->getStyle('A4:F4')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);

        // Center align all cells
        $sheet->getStyle('A:F')->getAlignment()->setHorizontal('center')->setVertical('center');
    }


    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
