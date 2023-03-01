<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;
use Carbon\Carbon;
use GuzzleHttp\Promise\Create;

class CreateItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items =
            [
                [
                    'name' => 'Metronidazole',
                    'description' => '500mg',
                    'category' => 'medicine',
                    'unit' => 'pcs',
                ],
                [
                    'name' => 'Ethyl Alcohol',
                    'description' => '70% solution',
                    'category' => 'medical supply',
                    'unit' => 'gal',
                ],
                [
                    'name' => 'Dextrose',
                    'description' => '500ml',
                    'category' => 'medical supply',
                    'unit' => 'pcs',
                ],
                [
                    'name' => 'Omeprazole',
                    'description' => '500mg',
                    'category' => 'medicine',
                    'unit' => 'pcs',
                ],
            ];

        foreach ($items as $key => $item) {
            Item::create($item);
        }
    }
}
