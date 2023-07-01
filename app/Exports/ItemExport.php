<?php

namespace App\Exports;

use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
            ->select('items.id', 'items.name', 'items.description', 'items.category', 'items.unit', 'items.max_limit', 'items.warning_level', DB::raw('SUM(item_stocks.stock_qty) as total_quantity'))
            ->groupBy('items.id', 'items.name', 'items.description', 'items.category', 'items.unit', 'items.max_limit', 'items.warning_level')
            ->orderBy('items.name'); //order the names by ascending

        $items = $query->get();

        foreach ($items as $item) {
            $item->warning_level = $item->warning_level / 100; //divide the value of warning_level to 100
        }

        if (self::$filter) {
            switch (self::$filter) {
                case 'max': //if the $filter value is 'max'
                    $query->havingRaw('total_quantity > max_limit');
                    break;
                case 'safe': //if the $filter value is 'safe'
                    $query->havingRaw('total_quantity > max_limit * (warning_level / 100)')
                        ->havingRaw('total_quantity <= max_limit');
                    break;
                case 'warning': //if the $filter value is 'warning'
                    $query->havingRaw('total_quantity <= (warning_level / 100) * max_limit ');
                    break;
                case 'no-stocks': //if the $filter value is 'no-stocks'
                    $query->whereNotExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('item_stocks')
                            ->whereRaw('items.id = item_stocks.item_id');
                    });
                    break;
            }
        }

        if (self::$date) {
            $date = self::$date;
            $query = $query->where('item_stocks.created_at', '<=', $date . ' 23:59:59');
        }

        $items = $query->get();

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
                ($row->total_quantity !== null) ? $row->total_quantity : 'out of stock', //if the total_quantity is not null, show the value of total_quantity else return 'out of stock'
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
