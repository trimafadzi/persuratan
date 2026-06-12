/**
 * inOffice Persuratan — Laravel Echo + Reverb Client
 * Real-time notifikasi & update via WebSocket
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// ── Inisialisasi Echo ──────────────────────────────────
const echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST || 'localhost',
    wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT || 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});

// ── Notifikasi Badge Count ─────────────────────────────
function updateBadgeCount() {
    fetch('/api/v1/notifikasi/unread-count', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(r => r.json())
    .then(data => {
        const badge = document.getElementById('notifBadge');
        const count = data.unread || data.count || 0;
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline-block' : 'none';
        }
    })
    .catch(() => {});
}

// ── Tambah Notifikasi ke Dropdown ──────────────────────
function prependNotification(notif) {
    const container = document.getElementById('notifDropdown');
    if (!container || !container.querySelector('.notif-list')) return;

    const list = container.querySelector('.notif-list');
    const item = document.createElement('div');
    item.className = 'notif-item notif-item--new';
    item.innerHTML = `
        <div class="notif-item__icon">🔔</div>
        <div class="notif-item__body">
            <strong>${notif.judul || 'Notifikasi Baru'}</strong>
            <p>${notif.pesan || ''}</p>
            <small>Baru saja</small>
        </div>
    `;
    list.prepend(item);
    item.style.background = '#f0f7ff';
    setTimeout(() => { item.style.background = ''; }, 5000);
}

// ── Toast Notifikasi ───────────────────────────────────
function showToast(title, message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = `
        <div class="toast-notification__icon">🔔</div>
        <div class="toast-notification__body">
            <strong>${title}</strong>
            <p>${message}</p>
        </div>
        <button class="toast-notification__close" onclick="this.parentElement.remove()">×</button>
    `;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// ── Private Channel Listeners ──────────────────────────
// Channel untuk user spesifik
const userId = document.querySelector('meta[name="user-id"]')?.content;
const userRole = document.querySelector('meta[name="user-role"]')?.content;

if (userId) {
    // Listen: Disposisi Baru (dikirim ke penerima spesifik)
    echo.private(`user.${userId}`)
        .listen('DisposisiBaru', (e) => {
            console.log('[Echo] DisposisiBaru diterima', e);
            showToast('📋 Disposisi Baru', e.disposisi?.perihal || 'Anda menerima disposisi baru');
            updateBadgeCount();
            prependNotification({
                judul: 'Disposisi Baru',
                pesan: e.disposisi?.perihal || 'Anda menerima disposisi baru',
            });
        })
        .listen('LaporanDiterima', (e) => {
            console.log('[Echo] LaporanDiterima diterima', e);
            showToast('📝 Laporan Disposisi', e.laporan?.perihal || 'Laporan pelaksanaan diterima');
            updateBadgeCount();
        });

    // Listen: Surat Masuk Baru (broadcast ke pimpinan channel)
    if (userRole && ['pimpinan', 'direktur', 'superadmin'].includes(userRole)) {
        echo.private('pimpinan')
            .listen('SuratMasukCreated', (e) => {
                console.log('[Echo] SuratMasukCreated diterima', e);
                showToast('📬 Surat Masuk Baru', e.surat?.perihal || 'Surat masuk baru');
                updateBadgeCount();
                prependNotification({
                    judul: 'Surat Masuk Baru',
                    pesan: e.surat?.perihal || 'Surat masuk baru perlu ditinjau',
                });
            });
    }
}

// ── Toast Styles ───────────────────────────────────────
const toastStyles = document.createElement('style');
toastStyles.textContent = `
    .toast-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #fff;
        border-left: 4px solid #3b82f6;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 99999;
        max-width: 400px;
        animation: slideIn 0.3s ease;
        transition: opacity 0.3s, transform 0.3s;
    }
    .toast-notification__icon { font-size: 24px; }
    .toast-notification__body strong { display: block; margin-bottom: 4px; }
    .toast-notification__body p { margin: 0; font-size: 13px; color: #666; }
    .toast-notification__close {
        background: none; border: none; font-size: 20px;
        cursor: pointer; color: #999; padding: 0 4px;
    }
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;
document.head.appendChild(toastStyles);

// ── Initial Badge Count ────────────────────────────────
updateBadgeCount();

console.log('[inOffice Echo] Reverb connected — real-time notifikasi aktif ✅');
