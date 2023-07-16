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
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Borders;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageMargins;

class StocksExport implements FromCollection, WithEvents, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function fileName(): string
    {
        return $this->filename();
    }

    //style of excel file
    public function styles(Worksheet $sheet)
    {
        $style = $sheet->getStyle('A1:I1')->getFont()->setBold(true); //set the first row to bold text from A1 to I1

        // Apply bold font to the header row
        $sheet->getStyle('A4:I4')->applyFromArray([ //set the A4 - I4 row to bold text
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
                'Expiration Date (YYYY-MM-DD)',
                'Mode of Acquisition',
                'Quantity',
                'Name',
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
        $items = Stock::leftjoin('items', 'item_stocks.item_id', '=', 'items.id')
            ->select('items.*', 'item_stocks.id', 'item_stocks.item_id', 'item_stocks.stock_qty', 'item_stocks.exp_date', 'item_stocks.mode_acquisition', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'))
            ->where('item_stocks.stock_qty', '>', 0)
            ->groupBy('item_stocks.item_id', 'items.id', 'items.name', 'items.description', 'items.category', 'items.unit', 'items.price', 'items.created_at', 'items.updated_at', 'item_stocks.id', 'item_stocks.item_id', 'item_stocks.stock_qty', 'item_stocks.exp_date', 'item_stocks.mode_acquisition', 'items.max_limit', 'items.warning_level', 'item_stocks.created_at', 'item_stocks.updated_at',)
            ->orderBy('items.name')->get();

        foreach ($items as $item) {
            $item_id = $item->item_id;
            $item->total_quantity = Stock::select(DB::raw('SUM(stock_qty) as total_quantity'))->groupBy('item_id')->where('item_id', $item_id)->get();
        }

        return $items;
    }

    public function map($row): array
    {
        $today = Carbon::now()->format('Y-m-d');

        return [
            $row->exp_date,
            $row->mode_acquisition,
            $row->stock_qty,
            $row->name,
            $row->category,
            $row->unit,
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
                        $sheet->getStyle("A$row:I$row")->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFF0000');
                        $sheet->getStyle("A$row:I$row")->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
                    }
                }

                $event->sheet->getDelegate()->setPrintGridlines(true);

                $event->sheet->getPageSetup()
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
                    ->setFitToPage(true)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setHorizontalCentered(true)
                    ->setVerticalCentered(true);

                $event->sheet->getPageMargins()
                    ->setTop(0)
                    ->setRight(0)
                    ->setBottom(0)
                    ->setLeft(0);
            },
        ];
    }
}
