<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'username' => 'testUser',
        //     'email' => 'test@gmail.com',
        //     'password' => bcrypt('testPassword'),
        // ]);

        // Role::create(['name' => 'admin']);
        Role::create(['name' => 'client']);
        
        $dummy_user = User::create([
            'username' => 'testUser',
            'email' => 'test@gmail.com',
            'password' => bcrypt('testPassword'),
        ]);

        $dummy_user->assignRole('client');
        Category::factory(4)->create();
        Transaction::factory(100)->create();

    }
}
