<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StatusOrder;

class StatusOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'принят'],
            ['name' => 'передан в такси'],
            ['name' => 'отменён'],
            ['name' => 'закрыт'],
        ];
        foreach ($statuses as $status) {
            StatusOrder::firstOrCreate(['name' => $status['name']], $status);
        }
    }
}
