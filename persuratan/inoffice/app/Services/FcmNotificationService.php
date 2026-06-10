<?php

namespace App\Services;

use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\Message;
use Illuminate\Support\Facades\Log;

class FcmNotificationService
{
    /**
     * Kirim push notification ke satu device.
     */
    public function sendToDevice(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (empty($fcmToken)) {
            return false;
        }

        try {
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $messaging = app('firebase.messaging');
            $messaging->send($message);

            return true;
        } catch (\Exception $e) {
            Log::warning('FCM sendToDevice failed', [
                'error' => $e->getMessage(),
                'token' => substr($fcmToken, 0, 10) . '...',
            ]);

            return false;
        }
    }

    /**
     * Kirim push notification ke satu user (semua device terdaftar).
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): int
    {
        if (empty($user->fcm_token)) {
            return 0;
        }

        $sent = 0;
        $tokens = $this->parseTokens($user->fcm_token);

        foreach ($tokens as $token) {
            if ($this->sendToDevice($token, $title, $body, $data)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Kirim push notification ke banyak user.
     */
    public function sendToUsers(array $users, string $title, string $body, array $data = []): int
    {
        $totalSent = 0;

        foreach ($users as $user) {
            if ($user instanceof User) {
                $totalSent += $this->sendToUser($user, $title, $body, $data);
            }
        }

        return $totalSent;
    }

    /**
     * Kirim notifikasi disposisi baru ke penerima.
     */
    public function notifyDisposisiBaru(User $penerima, string $perihal, int $disposisiId): int
    {
        return $this->sendToUser(
            $penerima,
            '📋 Disposisi Baru',
            "Anda menerima disposisi: {$perihal}",
            [
                'type' => 'disposisi',
                'disposisi_id' => (string) $disposisiId,
                'action' => 'open_disposisi',
            ]
        );
    }

    /**
     * Kirim notifikasi surat masuk baru ke pimpinan.
     */
    public function notifySuratMasukBaru(User $pimpinan, string $perihal, int $suratId): int
    {
        return $this->sendToUser(
            $pimpinan,
            '📬 Surat Masuk Baru',
            "Surat masuk baru: {$perihal}",
            [
                'type' => 'surat_masuk',
                'surat_id' => (string) $suratId,
                'action' => 'open_surat_masuk',
            ]
        );
    }

    /**
     * Kirim notifikasi laporan disposisi ke pemberi disposisi.
     */
    public function notifyLaporanDisposisi(User $pemberiDisposisi, string $perihal, int $laporanId): int
    {
        return $this->sendToUser(
            $pemberiDisposisi,
            '📝 Laporan Disposisi',
            "Laporan pelaksanaan untuk: {$perihal}",
            [
                'type' => 'laporan',
                'laporan_id' => (string) $laporanId,
                'action' => 'open_laporan',
            ]
        );
    }

    /**
     * Kirim notifikasi disposisi diteruskan ke penerima baru.
     */
    public function notifyDisposisiDiteruskan(User $penerima, string $perihal, int $disposisiId): int
    {
        return $this->sendToUser(
            $penerima,
            '🔄 Disposisi Diteruskan',
            "Disposisi diteruskan kepada Anda: {$perihal}",
            [
                'type' => 'disposisi',
                'disposisi_id' => (string) $disposisiId,
                'action' => 'open_disposisi',
            ]
        );
    }

    /**
     * Register FCM token untuk user.
     * Mendukung multi-device (token disimpan sebagai JSON array).
     */
    public function registerToken(User $user, string $fcmToken): bool
    {
        $tokens = $this->parseTokens($user->fcm_token);

        // Tambahkan token baru jika belum ada
        if (!in_array($fcmToken, $tokens)) {
            $tokens[] = $fcmToken;
            $user->fcm_token = json_encode($tokens);
            $user->save();

            return true;
        }

        return false;
    }

    /**
     * Unregister FCM token untuk user.
     */
    public function unregisterToken(User $user, string $fcmToken): bool
    {
        $tokens = $this->parseTokens($user->fcm_token);
        $index = array_search($fcmToken, $tokens);

        if ($index !== false) {
            unset($tokens[$index]);
            $user->fcm_token = empty($tokens) ? null : json_encode(array_values($tokens));
            $user->save();

            return true;
        }

        return false;
    }

    /**
     * Parse token dari JSON string atau string biasa.
     */
    private function parseTokens(?string $fcmToken): array
    {
        if (empty($fcmToken)) {
            return [];
        }

        // Coba decode JSON (format multi-device)
        $decoded = json_decode($fcmToken, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Format lama: single token string
        return [$fcmToken];
    }
}
