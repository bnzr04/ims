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

    public function headings(): array
    {
        $filter = request()->input('filter');

        if ($filter === 'today') {
            $title = Carbon::now()->format('F d, Y');
        } elseif ($filter === 'yesterday') {
            $title = Carbon::yesterday()->format('F d, Y');
        } elseif ($filter === 'this-month') {
            $from = Carbon::now()->startOfMonth()->format('F d, Y');
            $to = Carbon::now()->endOfMonth()->format('F d, Y');
            $title = "From: " . $from . " - To: " . $to;
        } else {
            $from = Carbon::parse(request()->input('date_from'))->format('F d, Y');
            $to = Carbon::parse(request()->input('date_to'))->format('F d, Y');
            $title = "From: " . $from . " - To: " . $to;
        }

        return [
            [
                'DISPENSE ITEMS REPORT'
            ],
            [$title],
            [],
            [
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

        $filter = request()->input('filter');

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
        $style = $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        // Apply bold font to the header row
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
