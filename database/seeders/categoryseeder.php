<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = ['Fruits', 'Vegetables', 'Drinks'];

        foreach ($categories as $cat) {
            Category::create(['name' => $cat]);
        }
    }
}


