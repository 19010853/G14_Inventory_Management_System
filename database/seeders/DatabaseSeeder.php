<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First, run the seeder that creates all the permissions
        $this->call(PermissionSeeder::class);

        // Read the admin credentials from the .env file
        $adminName = env('ADMIN_NAME');
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        // Only proceed if all three environment variables are set
        if ($adminName && $adminEmail && $adminPassword) {
            
            // Find the user by email, or create a new one if they don't exist.
            // Update their name and password.
            $admin = User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'password' => Hash::make($adminPassword),
                ]
            );

            // Find the 'Super Admin' role, or create it if it doesn't exist.
            $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

            // Assign the 'Super Admin' role to the user.
            $admin->assignRole($superAdminRole);
        }

        // Optional: seed dummy users for local development
        // User::factory(10)->create();
    }
}
