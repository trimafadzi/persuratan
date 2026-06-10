<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Surat Masuk
        Schema::create('surat_masuk', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat');
            $table->date('tanggal_surat');
            $table->date('tanggal_terima');
            $table->string('pengirim');
            $table->string('perihal');
            $table->enum('sifat', ['biasa', 'penting', 'rahasia', 'segera'])->default('biasa');
            $table->text('ringkasan')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['belum_dibaca', 'dibaca', 'didisposisi', 'selesai'])->default('belum_dibaca');
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerja')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // Tautan antar surat
        Schema::create('surat_masuk_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surat_masuk')->cascadeOnDelete();
            $table->foreignId('linked_surat_id')->constrained('surat_masuk')->cascadeOnDelete();
            $table->timestamps();
        });

        // Surat Keluar
        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat_otomatis')->unique();
            $table->date('tanggal');
            $table->string('penerima');
            $table->string('perihal');
            $table->enum('sifat', ['biasa', 'penting', 'rahasia', 'segera'])->default('biasa');
            $table->longText('isi')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['draft', 'approved', 'terkirim'])->default('draft');
            $table->foreignId('draft_id')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_masuk_links');
        Schema::dropIfExists('surat_keluar');
        Schema::dropIfExists('surat_masuk');
    }
};
