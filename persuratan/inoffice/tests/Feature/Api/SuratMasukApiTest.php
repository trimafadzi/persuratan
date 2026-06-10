<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\SuratMasuk;
use App\Models\UnitKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuratMasukApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->user = User::factory()->create(['is_active' => true]);
    }

    /** @test */
    public function test_index_mengembalikan_list_surat_masuk_paginated(): void
    {
        Sanctum::actingAs($this->user);

        SuratMasuk::factory()->count(5)->create(['created_by' => $this->user->id]);

        $response = $this->getJson('/api/v1/surat-masuk');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [['id', 'nomor_surat', 'perihal', 'status', 'sifat']],
                     'meta' => ['current_page', 'last_page', 'total'],
                 ]);
    }

    /** @test */
    public function test_index_tanpa_token_ditolak(): void
    {
        $response = $this->getJson('/api/v1/surat-masuk');
        $response->assertStatus(401);
    }

    /** @test */
    public function test_store_membuat_surat_masuk_baru(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/surat-masuk', [
            'nomor_surat'    => '001/TEST/2026',
            'tanggal_surat'  => '2026-06-01',
            'tanggal_terima' => '2026-06-02',
            'pengirim'       => 'Kementerian Kesehatan',
            'perihal'        => 'Surat Edaran Test',
            'sifat'          => 'biasa',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.nomor_surat', '001/TEST/2026')
                 ->assertJsonPath('data.status', 'belum_dibaca');

        $this->assertDatabaseHas('surat_masuk', ['nomor_surat' => '001/TEST/2026']);
    }

    /** @test */
    public function test_store_dengan_file_scan_berhasil_upload(): void
    {
        Sanctum::actingAs($this->user);

        $file = UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/v1/surat-masuk', [
            'nomor_surat'    => '002/TEST/2026',
            'tanggal_surat'  => '2026-06-01',
            'tanggal_terima' => '2026-06-02',
            'pengirim'       => 'Pengirim Test',
            'perihal'        => 'Test Upload',
            'sifat'          => 'penting',
            'file_scan'      => $file,
        ]);

        $response->assertStatus(201);
        $this->assertNotNull($response->json('data.file_url'));
    }

    /** @test */
    public function test_store_validasi_field_wajib(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/surat-masuk', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nomor_surat', 'tanggal_surat', 'pengirim', 'perihal', 'sifat']);
    }

    /** @test */
    public function test_show_mengembalikan_detail_surat(): void
    {
        Sanctum::actingAs($this->user);

        $surat = SuratMasuk::factory()->create([
            'created_by' => $this->user->id,
            'status'     => 'belum_dibaca',
        ]);

        $response = $this->getJson("/api/v1/surat-masuk/{$surat->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $surat->id);

        // Pastikan show() TIDAK mengubah status (berbeda dari web controller)
        $this->assertEquals('belum_dibaca', $surat->fresh()->status);
    }

    /** @test */
    public function test_tandai_baca_mengubah_status_menjadi_dibaca(): void
    {
        Sanctum::actingAs($this->user);

        $surat = SuratMasuk::factory()->create([
            'created_by' => $this->user->id,
            'status'     => 'belum_dibaca',
        ]);

        $response = $this->patchJson("/api/v1/surat-masuk/{$surat->id}/baca");

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'dibaca');

        $this->assertEquals('dibaca', $surat->fresh()->status);
    }

    /** @test */
    public function test_destroy_menghapus_surat(): void
    {
        Sanctum::actingAs($this->user);

        $surat = SuratMasuk::factory()->create(['created_by' => $this->user->id]);

        $response = $this->deleteJson("/api/v1/surat-masuk/{$surat->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('surat_masuk', ['id' => $surat->id, 'deleted_at' => null]);
    }

    /** @test */
    public function test_show_returns_404_for_nonexistent_surat(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/surat-masuk/99999');
        $response->assertStatus(404);
    }

    /** @test */
    public function test_index_filter_by_status(): void
    {
        Sanctum::actingAs($this->user);

        SuratMasuk::factory()->create(['status' => 'belum_dibaca', 'created_by' => $this->user->id]);
        SuratMasuk::factory()->create(['status' => 'selesai', 'created_by' => $this->user->id]);

        $response = $this->getJson('/api/v1/surat-masuk?status=belum_dibaca');

        $response->assertStatus(200);

        // Semua item yang dikembalikan harus berstatus belum_dibaca
        foreach ($response->json('data') as $item) {
            $this->assertEquals('belum_dibaca', $item['status']);
        }
    }
}
