<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['administrador', 'usuario'] as $name) {
            Role::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        // Breeze roles are stored directly in users.role.
        // Ensure existing users have at least the "usuario" role.
        User::query()->whereNull('role')->update(['role' => 'usuario']);

        // Force user #1 to be administrator when present.
        $adminUser = User::query()->find(1);
        if ($adminUser && $adminUser->role !== 'administrador') {
            $adminUser->update(['role' => 'administrador']);
        }
    }
}
