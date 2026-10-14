<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $username = mb_strtolower(trim((string) env('ADMIN_USERNAME')));
        $password = (string) env('ADMIN_PASSWORD');

        if ($username === '' || $password === '') {
            throw new \RuntimeException('ADMIN_USERNAME y ADMIN_PASSWORD son obligatorios.');
        }

        User::query()->updateOrCreate(
            ['username' => $username],
            ['id' => (string) Str::uuid(), 'password_hash' => Hash::make($password), 'role' => 'admin']
        );
    }
}
