<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Seller;
use App\Models\User;

class sellerseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         User::factory(3)->create()->each(function ($user) {
            $user->assignRole('seller');

           
        });
    }
}
