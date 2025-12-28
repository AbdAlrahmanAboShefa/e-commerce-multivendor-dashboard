<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\user;
use App\Models\Category;
use Spatie\Permission\Models\Role;
class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $sellers = User::role('seller')->get();
        $categories = Category::all();

        foreach ($sellers as $seller) {
            Product::factory(5)->create([
                'seller_id' => $seller->id,
                'category_id' => $categories->random()->id,
                'stock' => rand(5, 50),
            ]);
        }
    }
}
