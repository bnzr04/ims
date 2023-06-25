<?php

namespace App\Imports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Item([ //insert every item $row from the imported item file
            'name' => $row['name'],
            'description' => $row['description'],
            'category' => $row['category'],
            'unit' => $row['unit'],
            'max_limit' => $row['max_limit'],
            'warning_level' => $row['warning_level'],
        ]);
    }
}
