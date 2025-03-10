<?php
namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name'       => 'Sprinter',
                'icon'=>  'category/sprinter.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Car Transporter',
                'icon'=>  'category/car_transporter.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Trailer',
                'icon'=>  'category/trailer.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        Category::insert($categories);
    }
}
