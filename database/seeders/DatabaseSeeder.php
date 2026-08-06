<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $superAdminRole = Role::where('slug', 'super_admin')->first();

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('ChangeMe123!'),
                'role_id' => $superAdminRole?->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
