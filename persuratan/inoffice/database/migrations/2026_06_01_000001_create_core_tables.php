<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Unit Kerja (hierarki)
        Schema::create('unit_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('unit_kerja')->nullOnDelete();
            $table->integer('level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nama_role');
            $table->string('slug')->unique();
            $table->json('permissions')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Extend users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('name');
            $table->string('nama_lengkap')->nullable()->after('username');
            $table->string('jabatan')->nullable();
            $table->string('foto_profil')->nullable();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerja')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login')->nullable();
        });

        // User Roles (many-to-many)
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username','nama_lengkap','jabatan','foto_profil','unit_kerja_id','is_active','last_login']);
        });
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('unit_kerja');
    }
};
