<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Disposisi
        Schema::create('disposisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_masuk_id')->constrained('surat_masuk')->cascadeOnDelete();
            $table->foreignId('dari_user_id')->constrained('users');
            $table->text('isi_disposisi');
            $table->enum('status', ['pending', 'diteruskan', 'selesai', 'dibatalkan'])->default('pending');
            $table->date('tanggal_deadline')->nullable();
            $table->foreignId('parent_disposisi_id')->nullable()->constrained('disposisi')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Penerima Disposisi (many-to-many)
        Schema::create('disposisi_penerima', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disposisi_id')->constrained('disposisi')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Laporan Pelaksanaan Disposisi
        Schema::create('laporan_disposisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disposisi_id')->constrained('disposisi')->cascadeOnDelete();
            $table->foreignId('dari_user_id')->constrained('users');
            $table->text('isi_laporan');
            $table->enum('status', ['draft', 'terkirim'])->default('draft');
            $table->text('tanggapan')->nullable();
            $table->enum('status_tanggapan', ['pending', 'approved', 'rejected'])->nullable();
            $table->foreignId('ditanggapi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditanggapi_at')->nullable();
            $table->timestamps();
        });

        // File Bukti Laporan
        Schema::create('laporan_file_bukti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('laporan_disposisi')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_file_bukti');
        Schema::dropIfExists('laporan_disposisi');
        Schema::dropIfExists('disposisi_penerima');
        Schema::dropIfExists('disposisi');
    }
};
