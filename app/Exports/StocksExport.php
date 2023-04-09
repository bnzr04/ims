<?php

namespace App\Exports;

use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StocksExport implements FromCollection, WithEvents, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function fileName(): string
    {
        return $this->filename();
    }

    //style of excel file
    public function styles(Worksheet $sheet)
    {
        $style = $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        // Apply bold font to the header row
        $sheet->getStyle('A4:I4')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);

        // Center align all cells
        $sheet->getStyle('A:I')->getAlignment()->setHorizontal('center')->setVertical('center');
    }

    public function headings(): array
    {
        return [
            [
                'STOCK REPORT'
            ],
            [
                'As of: ',
                date('h:i A, F d, Y'),
            ],
            [],
            [
                'Stock ID',
                'Expiration Date (YYYY-MM-DD)',
                'Quantity',
                'Item ID',
                'Name',
                'Descrption',
                'Category',
                'Unit',
            ]
        ];
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $user = Auth::user();
        $user_type = $user->type;

        if ($user_type === 'manager') {
            $user_dept = $user->dept;

            if ($user_dept === 'pharmacy') {
                $items = Stock::leftjoin('items', 'item_stocks.item_id', '=', 'items.id')
                    ->select('items.*', 'item_stocks.id', 'item_stocks.item_id', 'item_stocks.stock_qty', 'item_stocks.exp_date', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'))
                    ->groupBy('item_stocks.item_id', 'items.id', 'items.name', 'items.description', 'items.category', 'items.unit', 'items.created_at', 'items.updated_at', 'item_stocks.id', 'item_stocks.item_id', 'item_stocks.stock_qty', 'item_stocks.exp_date', 'item_stocks.created_at', 'item_stocks.updated_at',)
                    ->where('items.category', '!=', 'medical supply')
                    ->orderBy('items.name')->get();

                foreach ($items as $item) {
                    $item_id = $item->item_id;

                    $item->total_quantity = Stock::select(DB::raw('SUM(stock_qty) as total_quantity'))->groupBy('item_id')->where('item_id', $item_id)->get();
                }


                return $items;
            } elseif ($user_dept === 'csr') {
                $items = Stock::leftjoin('items', 'item_stocks.item_id', '=', 'items.id')
                    ->select('items.*', 'item_stocks.id', 'item_stocks.item_id', 'item_stocks.stock_qty', 'item_stocks.exp_date', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'))
                    ->groupBy('item_stocks.item_id', 'items.id', 'items.name', 'items.description', 'items.category', 'items.unit', 'items.created_at', 'items.updated_at', 'item_stocks.id', 'item_stocks.item_id', 'item_stocks.stock_qty', 'item_stocks.exp_date', 'item_stocks.created_at', 'item_stocks.updated_at',)
                    ->where('items.category', 'medical supply')
                    ->orderBy('items.name')->get();

                foreach ($items as $item) {
                    $item_id = $item->item_id;

                    $item->total_quantity = Stock::select(DB::raw('SUM(stock_qty) as total_quantity'))->groupBy('item_id')->where('item_id', $item_id)->get();
                }

                return $items;
            }
        } else {
            $items = Stock::leftjoin('items', 'item_stocks.item_id', '=', 'items.id')
                ->select('items.*', 'item_stocks.id', 'item_stocks.item_id', 'item_stocks.stock_qty', 'item_stocks.exp_date', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'))
                ->groupBy('item_stocks.item_id', 'items.id', 'items.name', 'items.description', 'items.category', 'items.unit', 'items.created_at', 'items.updated_at', 'item_stocks.id', 'item_stocks.item_id', 'item_stocks.stock_qty', 'item_stocks.exp_date', 'item_stocks.created_at', 'item_stocks.updated_at',)
                ->orderBy('items.name')->get();

            foreach ($items as $item) {
                $item_id = $item->item_id;
                $item->total_quantity = Stock::select(DB::raw('SUM(stock_qty) as total_quantity'))->groupBy('item_id')->where('item_id', $item_id)->get();
            }

            return $items;
        }
    }

    public function map($row): array
    {
        $today = Carbon::now()->format('Y-m-d');

        return [
            $row->id,
            $row->exp_date,
            $row->stock_qty,
            $row->item_id,
            $row->name,
            $row->description,
            $row->category,
            $row->unit,
            // $row->total_quantity[0]['total_quantity'],

        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->getSheet();
                $lastRow = $sheet->getHighestRow();

                // Initialize an array to keep track of merged cells and last row number for each item id
                // $mergedCells = [];
                // $lastRowWithItemId = [];

                // Apply conditional formatting to rows based on expiration date
                for ($row = 5; $row <= $lastRow; $row++) {
                    $expirationDate = $sheet->getCell("B$row")->getValue();
                    $today = now()->format('Y-m-d');



                    if ($expirationDate < $today) {
                        // Set background color to red and font color to white
                        $sheet->getStyle("A$row:H$row")->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFF0000');
                        $sheet->getStyle("A$row:H$row")->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
                    }

                    // $itemId = $sheet->getCell("D$row")->getValue();
                    // $totalQuantity = $sheet->getCell("I$row")->getValue();

                    // // If the current row has the same item_id as the previous row, merge the cells in column I
                    // if (isset($mergedCells[$itemId]) && $totalQuantity) {
                    //     $prevRow = $mergedCells[$itemId]['row'];
                    //     $last = $row;
                    //     $lastRowWithItemId[$itemId] = $last;

                    //     // return dd($last);

                    //     // $sheet->mergeCells("I$prevRow:I$last");
                    //     // $sheet->setCellValue("I$prevRow", $totalQuantity);
                    //     // $sheet->getStyle("I$prevRow:I$last")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    //     // $sheet->getStyle("I$prevRow:I$last")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    // } else {
                    //     $mergedCells[$itemId] = ['row' => $row];
                    // }

                    // // Fill in any remaining cells that were not merged
                    // foreach ($lastRowWithItemId as $itemId => $lastRow) {
                    //     if (isset($mergedCells[$itemId]) && $totalQuantity) {
                    //         $mergedRow = $mergedCells[$itemId]['row'];
                    //         $sheet->mergeCells("I$mergedRow:I$lastRow");
                    //         $sheet->setCellValue("I$mergedRow", $totalQuantity);
                    //         $sheet->getStyle("I$mergedRow:I$lastRow")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    //         $sheet->getStyle("I$mergedRow:I$lastRow")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    //     }
                    // }
                }
                // return dd($totalQuantity);

                // return dd($mergedCells[$itemId]['row'],  $lastRowWithItemId[$itemId]);
            },
        ];
    }
}
