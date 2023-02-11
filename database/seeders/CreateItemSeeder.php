<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;
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
        $items = [
            [
                'item_name' => 'Rgb Mouse',
                'item_description' => 'Mouse Computer with rgb lights',
                'category' => 'Computer Equipment',
                'item_cost' => 550,
                'item_salvage_cost' => 30,
                'item_useful_life' => 1
            ],
            [
                'item_name' => 'Computer Monitor',
                'item_description' => '24 inch Computer monitor',
                'category' => 'Computer Equipment',
                'item_cost' => 3450,
                'item_salvage_cost' => 500,
                'item_useful_life' => 2
            ],
            [
                'item_name' => 'HDMI Cable',
                'item_description' => '6 meters hdmi cable',
                'category' => 'Computer Equipment',
                'item_cost' => 250,
                'item_salvage_cost' => 20,
                'item_useful_life' => 2
            ],
        ];

        foreach ($items as $key => $item) {
            Item::create($item);
        }
    }
}
