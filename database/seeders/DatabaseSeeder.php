<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Database\Seeders\RoleSeeder;
use Database\Seeders\userseeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SellerSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
