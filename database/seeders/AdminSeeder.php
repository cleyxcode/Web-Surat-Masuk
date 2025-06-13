<?php

// database/seeders/AdminSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin
        User::create([
            'name' => 'Administrator TVRI',
            'email' => 'admin@tvri-maluku.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'nip' => '19800101 200001 1 001',
            'phone' => '081234567890',
            'position' => 'Administrator Sistem',
            'division' => 'IT',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create sample karyawan
        User::create([
            'name' => 'John Doe',
            'email' => 'john@tvri-maluku.com',
            'password' => Hash::make('password123'),
            'role' => 'karyawan',
            'nip' => '19900101 201001 1 001',
            'phone' => '081234567891',
            'position' => 'Staff Produksi',
            'division' => 'Produksi',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@tvri-maluku.com',
            'password' => Hash::make('password123'),
            'role' => 'karyawan',
            'nip' => '19950101 201501 2 001',
            'phone' => '081234567892',
            'position' => 'Staff Teknik',
            'division' => 'Teknik',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}

