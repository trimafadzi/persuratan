<?php

namespace App\Events;

use App\Models\Disposisi;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DisposisiBaru implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $disposisi;
    public $penerimaIds;

    public function __construct(Disposisi $disposisi, array $penerimaIds = [])
    {
        $this->disposisi = $disposisi->load(['suratMasuk', 'pemberi']);
        $this->penerimaIds = $penerimaIds;
    }

    public function broadcastOn(): array
    {
        $channels = [];
        foreach ($this->penerimaIds as $userId) {
            $channels[] = new PrivateChannel("user.{$userId}");
        }
        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->disposisi->id,
            'surat_masuk_id' => $this->disposisi->surat_masuk_id,
            'perihal' => $this->disposisi->suratMasuk->perihal ?? '',
            'dari' => $this->disposisi->pemberi->display_name ?? '',
            'isi_disposisi' => $this->disposisi->isi_disposisi,
            'created_at' => $this->disposisi->created_at->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'disposisi.baru';
    }
}
