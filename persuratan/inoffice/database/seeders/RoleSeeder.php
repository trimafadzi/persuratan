<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nama_role'   => 'Super Admin',
                'slug'        => 'super-admin',
                'permissions' => json_encode(['*']),
                'description' => 'Akses penuh ke seluruh sistem',
            ],
            [
                'nama_role'   => 'Admin IT',
                'slug'        => 'admin-it',
                'permissions' => json_encode(['user.manage', 'unit.manage', 'role.manage', 'log.view']),
                'description' => 'Pengelola sistem, user, dan konfigurasi',
            ],
            [
                'nama_role'   => 'Pimpinan',
                'slug'        => 'pimpinan',
                'permissions' => json_encode([
                    'surat_masuk.view', 'surat_masuk.read',
                    'disposisi.create', 'disposisi.view',
                    'laporan.view', 'laporan.export',
                    'draft.approve',
                ]),
                'description' => 'Direktur/Pimpinan — menerima dan mendisposisi surat',
            ],
            [
                'nama_role'   => 'Kepala Unit',
                'slug'        => 'kepala-unit',
                'permissions' => json_encode([
                    'surat_masuk.view',
                    'disposisi.view', 'disposisi.forward', 'disposisi.report',
                    'laporan.view',
                    'draft.review',
                ]),
                'description' => 'Kepala Unit/Manajer — penerima dan penerus disposisi',
            ],
            [
                'nama_role'   => 'Operator',
                'slug'        => 'operator',
                'permissions' => json_encode([
                    'surat_masuk.create', 'surat_masuk.view',
                    'surat_keluar.create', 'surat_keluar.view',
                    'draft.create', 'draft.view',
                ]),
                'description' => 'Operator persuratan — input dan arsip surat',
            ],
            [
                'nama_role'   => 'Staf Pelaksana',
                'slug'        => 'staf',
                'permissions' => json_encode([
                    'disposisi.view', 'disposisi.report',
                    'surat_masuk.view',
                ]),
                'description' => 'Staf — eksekutor tindak lanjut disposisi',
            ],
            [
                'nama_role'   => 'Viewer',
                'slug'        => 'viewer',
                'permissions' => json_encode(['surat_masuk.view', 'laporan.view']),
                'description' => 'Auditor/Viewer — hanya bisa melihat',
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $role['slug']],
                array_merge($role, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
