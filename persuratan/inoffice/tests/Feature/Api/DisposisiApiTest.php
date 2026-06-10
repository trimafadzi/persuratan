<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\SuratMasuk;
use App\Models\Disposisi;
use App\Models\Notifikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DisposisiApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $penerima;
    protected SuratMasuk $surat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user    = User::factory()->create(['is_active' => true]);
        $this->penerima = User::factory()->create(['is_active' => true]);
        $this->surat   = SuratMasuk::factory()->create([
            'created_by' => $this->user->id,
            'status'     => 'belum_dibaca',
        ]);
    }

    /** @test */
    public function test_store_membuat_disposisi_baru_dan_kirim_notifikasi(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/disposisi', [
            'surat_masuk_id'   => $this->surat->id,
            'isi_disposisi'    => 'Harap ditindaklanjuti segera.',
            'penerima_ids'     => [$this->penerima->id],
            'tanggal_deadline' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.status', 'pending')
                 ->assertJsonPath('data.isi_disposisi', 'Harap ditindaklanjuti segera.');

        // Status surat masuk harus berubah menjadi didisposisi
        $this->assertEquals('didisposisi', $this->surat->fresh()->status);

        // Notifikasi harus terkirim ke penerima
        $this->assertDatabaseHas('notifikasi', [
            'user_id'     => $this->penerima->id,
            'tipe'        => 'disposisi',
            'entity_type' => 'Disposisi',
        ]);
    }

    /** @test */
    public function test_store_validasi_field_wajib(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/disposisi', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['surat_masuk_id', 'isi_disposisi', 'penerima_ids']);
    }

    /** @test */
    public function test_index_tab_masuk_menampilkan_disposisi_untuk_penerima(): void
    {
        Sanctum::actingAs($this->penerima);

        // Buat disposisi yang ditujukan ke $penerima
        $disposisi = Disposisi::factory()->create([
            'surat_masuk_id' => $this->surat->id,
            'dari_user_id'   => $this->user->id,
            'status'         => 'pending',
        ]);
        $disposisi->penerima()->attach([$this->penerima->id => ['is_read' => false, 'created_at' => now(), 'updated_at' => now()]]);

        $response = $this->getJson('/api/v1/disposisi?tab=masuk');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    /** @test */
    public function test_show_menandai_dibaca_untuk_penerima(): void
    {
        Sanctum::actingAs($this->penerima);

        $disposisi = Disposisi::factory()->create([
            'surat_masuk_id' => $this->surat->id,
            'dari_user_id'   => $this->user->id,
        ]);
        $disposisi->penerima()->attach([$this->penerima->id => ['is_read' => false, 'created_at' => now(), 'updated_at' => now()]]);

        $response = $this->getJson("/api/v1/disposisi/{$disposisi->id}");

        $response->assertStatus(200);

        // Pivot is_read harus berubah menjadi true
        $this->assertTrue(
            (bool) $disposisi->penerima()->where('users.id', $this->penerima->id)->first()->pivot->is_read
        );
    }

    /** @test */
    public function test_teruskan_membuat_child_disposisi(): void
    {
        Sanctum::actingAs($this->user);

        $disposisi = Disposisi::factory()->create([
            'surat_masuk_id' => $this->surat->id,
            'dari_user_id'   => $this->user->id,
            'status'         => 'pending',
        ]);
        $disposisi->penerima()->attach([$this->user->id => ['is_read' => false, 'created_at' => now(), 'updated_at' => now()]]);

        $penerimaBaru = User::factory()->create(['is_active' => true]);

        $response = $this->postJson("/api/v1/disposisi/{$disposisi->id}/teruskan", [
            'isi_disposisi' => 'Diteruskan ke staf.',
            'penerima_ids'  => [$penerimaBaru->id],
        ]);

        $response->assertStatus(201);

        // Disposisi induk harus berubah status menjadi diteruskan
        $this->assertEquals('diteruskan', $disposisi->fresh()->status);

        // Child disposisi harus ada
        $this->assertDatabaseHas('disposisi', [
            'parent_disposisi_id' => $disposisi->id,
            'status'              => 'pending',
        ]);
    }

    /** @test */
    public function test_batalkan_hanya_bisa_oleh_pemberi(): void
    {
        Sanctum::actingAs($this->penerima); // Bukan pemberi

        $disposisi = Disposisi::factory()->create([
            'surat_masuk_id' => $this->surat->id,
            'dari_user_id'   => $this->user->id, // Pemberi adalah $user, bukan $penerima
            'status'         => 'pending',
        ]);

        $response = $this->patchJson("/api/v1/disposisi/{$disposisi->id}/batal");

        $response->assertStatus(403);
        $this->assertEquals('pending', $disposisi->fresh()->status); // Tidak berubah
    }

    /** @test */
    public function test_batalkan_berhasil_oleh_pemberi(): void
    {
        Sanctum::actingAs($this->user); // Login sebagai pemberi

        $disposisi = Disposisi::factory()->create([
            'surat_masuk_id' => $this->surat->id,
            'dari_user_id'   => $this->user->id,
            'status'         => 'pending',
        ]);

        $response = $this->patchJson("/api/v1/disposisi/{$disposisi->id}/batal");

        $response->assertStatus(200);
        $this->assertEquals('dibatalkan', $disposisi->fresh()->status);
    }
}
