<?php

namespace App\Events;

use App\Models\LaporanDisposisi;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LaporanDiterima implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $laporan;

    public function __construct(LaporanDisposisi $laporan)
    {
        $this->laporan = $laporan->load(['disposisi.suratMasuk', 'pelapor']);
    }

    public function broadcastOn(): array
    {
        $dariUserId = $this->laporan->disposisi->dari_user_id;
        return [
            new PrivateChannel("user.{$dariUserId}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->laporan->id,
            'disposisi_id' => $this->laporan->disposisi_id,
            'perihal' => $this->laporan->disposisi->suratMasuk->perihal ?? '',
            'dari' => $this->laporan->pelapor->display_name ?? '',
            'isi_laporan' => $this->laporan->isi_laporan,
            'created_at' => $this->laporan->created_at->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'laporan.diterima';
    }
}
