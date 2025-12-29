<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
        $adminName = env('ADMIN_NAME');
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        // Only create/update seeded admin if the env vars are provided.
        // This keeps production/staging flexible and avoids accidentally creating users.
        if ($adminName && $adminEmail && $adminPassword) {
            $admin = User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'password' => Hash::make($adminPassword),
                ]
            );

            // If the app uses Spatie Permission, assign a role if it exists.
            // Prefer 'Super Admin', otherwise fall back to 'Admin'.
            if (method_exists($admin, 'assignRole')) {
                $roleName = Role::where('name', 'Super Admin')->where('guard_name', 'web')->value('name')
                    ?? Role::where('name', 'Admin')->where('guard_name', 'web')->value('name');

                if ($roleName) {
                    $admin->assignRole($roleName);
                }
            }
        }

        // Optional: seed dummy users for local development
        // User::factory(10)->create();
    }
}
