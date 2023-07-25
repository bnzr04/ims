<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\Stock_Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class ItemExport implements FromCollection, WithHeadings, WithEvents, WithStyles, ShouldAutoSize, WithMapping, WithColumnFormatting
{

    public static $filter; //get the value of $filter
    public static $date; //get the value of $filter

    public function fileName(): string
    {
        return $this->filename();
    }

    public function headings(): array //this will generate the headings of the excel file 
    {

        $date = self::$date; //store the value of public static $date
        $filter = self::$filter; //store the value of public static $filter


        if ($date) { //if date is true means $date has value
            $formatted_date = Carbon::parse($date)->format('F d, Y');
        } else {
            $formatted_date = Carbon::now()->format('F d, Y');
        }

        switch ($filter) {
            case 'max':
                $filterTitle = 'Over Max Level';
                break;
            case 'safe':
                $filterTitle = 'Safe Level';
                break;
            case 'warning':
                $filterTitle = 'Warning Level';
                break;
            case 'no-stocks':
                $filterTitle = 'No Stocks';
                break;
            default:
                $filterTitle = '';
                break;
        }

        return [
            [
                'As of: ', $formatted_date, $filterTitle //will generate current date
            ],
            [],
            [
                'Item name', 'Description', 'Category', 'Unit', 'Total Stocks' //data header or the table head
            ]
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    //data collection
    public function collection()
    {
        //join the item_stocks and items table to get every items and its total quantity in stocks
        $query = Item::join('item_stocks', 'items.id', '=', 'item_stocks.item_id')
            ->select(
                'items.id',
                'items.name',
                'items.description',
                'items.category',
                'items.unit',
                'items.max_limit',
                'items.warning_level',
                DB::raw('SUM(item_stocks.stock_qty) as total_quantity'),
                'item_stocks.status',
            )
            ->groupBy(
                'items.id',
                'items.name',
                'items.description',
                'items.category',
                'items.unit',
                'items.max_limit',
                'items.warning_level',
                'item_stocks.status'
            )
            ->where('item_stocks.stock_qty', '>', 0)
            ->where('item_stocks.status', 'active')
            ->orderBy('items.name');

        $items = $query->get();

        if (self::$date) {
            $date = self::$date;
            $formattedDate = Carbon::parse($date)->endOfDay()->toDateTimeString();
        } else {
            $formattedDate = now()->endOfDay();
        }

        foreach ($items as $item) {
            $item->warning_level = $item->warning_level / 100;
        }

        if (self::$filter) {
            switch (self::$filter) {
                case 'max':
                    $query->havingRaw('total_quantity > max_limit');
                    break;
                case 'safe':
                    $query->havingRaw('total_quantity > max_limit * (warning_level / 100)')
                        ->havingRaw('total_quantity <= max_limit');
                    break;
                case 'warning':
                    $query->havingRaw('total_quantity <= (warning_level / 100) * max_limit');
                    break;
                case 'no-stocks':
                    $query->whereNotExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('item_stocks')
                            ->whereRaw('items.id = item_stocks.item_id');
                    });
                    break;
            }
        }

        $items = $query->get();

        foreach ($items as $item) {
            $stock = DB::table('stock_logs')
                ->where('item_id', $item->id)
                ->where('created_at', '<=', $formattedDate)
                ->orderByDesc('created_at')
                ->value('current_quantity');

            $item->current_quantity = $stock ?? 'out of stock';
        }

        return $items;
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

        $sheet->getStyle('B1:C1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);

        // Center align all cells
        $sheet->getStyle('A1:F1000')->getAlignment()->setHorizontal('center')->setVertical('center');
    }

    public function map($row): array
    {
        return [
            [
                $row->name,
                $row->description,
                $row->category,
                $row->unit,
                $row->current_quantity, //if the total_quantity is not null, show the value of total_quantity else return 'out of stock'
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

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
