<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
        ]);

        // Em produção, o primeiro admin deve ser criado manualmente via `php artisan filament:user`.
        if (app()->environment(['local', 'testing'])) {
            User::query()->updateOrCreate([
                'email' => 'admin@bellacriativa.local',
            ], [
                'name' => 'Admin Bella Criativa',
                'password' => Hash::make('password'),
            ]);
        }
    }
}
