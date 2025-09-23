<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Laptop Dell XPS 13',
                'sku' => 'DELL-XPS-13-001',
                'quantity' => 25,
            ],
            [
                'name' => 'Wireless Mouse',
                'sku' => 'MOUSE-WL-001',
                'quantity' => 150,
            ],
            [
                'name' => 'USB-C Cable',
                'sku' => 'USB-C-CABLE-001',
                'quantity' => 200,
            ],
            [
                'name' => 'Monitor 27 inch',
                'sku' => 'MONITOR-27-001',
                'quantity' => 45,
            ],
            [
                'name' => 'Mechanical Keyboard',
                'sku' => 'KEYBOARD-MECH-001',
                'quantity' => 75,
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
