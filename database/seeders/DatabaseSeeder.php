<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Laravel\Passport\Client;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $privateKeyPath = storage_path('oauth-private.key');
        $publicKeyPath = storage_path('oauth-public.key');

        if (! File::exists($privateKeyPath) || ! File::exists($publicKeyPath)) {
            Artisan::call('passport:keys', ['--force' => true]);
            $this->command->info('Passport keys generated.');
        } else {
            $this->command->info('Passport keys already exist. Skipping generation.');
        }

        if (! Client::where('name', 'wg_personal')->exists()) {
            Artisan::call('passport:client', [
                '--personal' => true,
                '--name' => 'wg_personal',
                '--provider' => 'users',
            ]);
        }

        if (! User::where('email', 'admin@admin.com')->exists()) {
            User::factory()->create([
                'email' => 'admin@admin.com',
                'is_admin' => 1,
            ]);
        }
    }
}
