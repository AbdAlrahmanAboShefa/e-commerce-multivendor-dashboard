<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class userseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com'
        ]);
        $admin->assignRole('admin');

        User::factory(5)->create()->each(function ($user) {
            $user->assignRole('user');
        });
    }
}
