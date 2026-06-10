<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Super Admin
        $userId = DB::table('users')->insertGetId([
            'name'          => 'Super Admin',
            'username'      => 'superadmin',
            'nama_lengkap'  => 'Super Administrator',
            'email'         => 'admin@rsuuki.ac.id',
            'password'      => Hash::make('Admin@RSU2026!'),
            'unit_kerja_id' => 1,
            'is_active'     => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Assign role Super Admin
        $roleId = DB::table('roles')->where('slug', 'super-admin')->value('id');
        if ($roleId) {
            DB::table('user_roles')->insert([
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
