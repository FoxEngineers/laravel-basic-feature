<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! User::where('email', 'admin@admin.com')->exists()) {
            User::factory()->create([
                'email' => 'admin@admin.com',
                'is_admin' => 1,
            ]);
        }
    }
}
