<?php

namespace Database\Seeders;

use App\Models\Vahicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VahicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Vahicle::factory()->count(10)->create();
    }
}
