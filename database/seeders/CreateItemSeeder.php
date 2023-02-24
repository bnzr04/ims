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
                    'price' => 45.99,
                ],
                [
                    'name' => 'Ethyl Alcohol',
                    'description' => '70% solution, 150ml',
                    'category' => 'medical supply',
                    'price' => 25,
                ],
                [
                    'name' => 'Dextrose',
                    'description' => '1 liter',
                    'category' => 'medical supply',
                    'price' => 100,
                ],
                [
                    'name' => 'Omeprazole',
                    'description' => '500mg',
                    'category' => 'medicine',
                    'price' => 24.50,
                ],
            ];

        foreach ($items as $key => $item) {
            Item::create($item);
        }
    }
}
