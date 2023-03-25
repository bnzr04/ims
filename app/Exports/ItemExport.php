<?php

namespace App\Exports;

use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ItemExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithMapping, WithColumnFormatting
{

    public function fileName(): string
    {
        return $this->filename();
    }

    public function headings(): array
    {
        return [
            [
                'As of: ',
                date('h:i A, F d, Y'),
            ],
            [],
            [
                'ID', 'Item name', 'Description', 'Category', 'Unit', 'Total Stocks'
            ]
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    //data collection
    public function collection()
    {
        $user = Auth::user();
        $user_type = $user->type;

        if ($user_type === 'manager') {
            $user_dept = $user->dept;

            if ($user_dept === 'pharmacy') {
                $items = Item::leftjoin('item_stocks', 'items.id', '=', 'item_stocks.item_id')
                    ->select('items.id', 'items.name', 'items.description', 'items.category', 'items.unit', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'))
                    ->groupBy('items.id', 'items.name', 'items.description', 'items.category', 'items.unit',)
                    ->where('items.category', '!=', 'medical supply')
                    ->orderBy('items.name')->get();

                return $items;
            } elseif ($user_dept === 'csr') {
                $items = Item::leftjoin('item_stocks', 'items.id', '=', 'item_stocks.item_id')
                    ->select('items.id', 'items.name', 'items.description', 'items.category', 'items.unit', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'))
                    ->groupBy('items.id', 'items.name', 'items.description', 'items.category', 'items.unit',)
                    ->where('items.category', 'medical supply')
                    ->orderBy('items.name')->get();

                return $items;
            }
        } else {
            $items = Item::leftjoin('item_stocks', 'items.id', '=', 'item_stocks.item_id')
                ->select('items.id', 'items.name', 'items.description', 'items.category', 'items.unit', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'))
                ->groupBy('items.id', 'items.name', 'items.description', 'items.category', 'items.unit',)
                ->orderBy('items.name')->get();

            return $items;
        }
    }

    //style of excel file
    public function styles(Worksheet $sheet)
    {
        // Apply bold font to the header row
        $sheet->getStyle('A3:F3')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);

        // Center align all cells
        $sheet->getStyle('A1:F100')->getAlignment()->setHorizontal('center')->setVertical('center');
    }

    public function map($row): array
    {
        return [
            [
                $row->id,
                $row->name,
                $row->description,
                $row->category,
                $row->unit,
                ($row->total_quantity !== null) ? $row->total_quantity : '~',
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
