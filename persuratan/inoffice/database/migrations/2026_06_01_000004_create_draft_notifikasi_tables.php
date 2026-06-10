<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Template Surat
        Schema::create('template_surat', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jenis'); // surat_masuk, surat_keluar, memo, dll
            $table->longText('konten_html');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Draft Surat
        Schema::create('draft_surat', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->foreignId('template_id')->nullable()->constrained('template_surat')->nullOnDelete();
            $table->string('file_docx_path')->nullable();
            $table->longText('konten_html')->nullable();
            $table->enum('status', ['draft', 'review', 'revisi', 'approved'])->default('draft');
            $table->integer('version')->default(1);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Versi Draft
        Schema::create('draft_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draft_id')->constrained('draft_surat')->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->longText('konten_html')->nullable();
            $table->integer('versi_ke');
            $table->text('catatan_perubahan')->nullable();
            $table->foreignId('saved_by')->constrained('users');
            $table->timestamp('saved_at')->useCurrent();
            $table->timestamps();
        });

        // Notifikasi
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul');
            $table->text('pesan');
            $table->string('tipe'); // surat_masuk, disposisi, laporan, dll
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Log Aktivitas
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('detail')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('timestamp')->useCurrent();
        });

        // Nomor Surat Counter
        Schema::create('nomor_surat_counter', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->string('jenis'); // SK = Surat Keluar, SM = Surat Masuk
            $table->integer('counter')->default(0);
            $table->unique(['tahun', 'jenis']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('draft_versions');
        Schema::dropIfExists('draft_surat');
        Schema::dropIfExists('template_surat');
        Schema::dropIfExists('nomor_surat_counter');
    }
};
