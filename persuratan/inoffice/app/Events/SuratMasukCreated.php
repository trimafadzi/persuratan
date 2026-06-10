<?php

namespace App\Events;

use App\Models\SuratMasuk;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuratMasukCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $suratMasuk;

    public function __construct(SuratMasuk $suratMasuk)
    {
        $this->suratMasuk = $suratMasuk->load(['creator', 'unitKerja']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('pimpinan'),
            new PrivateChannel('admin'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->suratMasuk->id,
            'nomor_surat' => $this->suratMasuk->nomor_surat,
            'pengirim' => $this->suratMasuk->pengirim,
            'perihal' => $this->suratMasuk->perihal,
            'sifat' => $this->suratMasuk->sifat,
            'created_at' => $this->suratMasuk->created_at->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'surat.masuk.created';
    }
}
