/**
 * inOffice Service Worker
 * Strategy: Network-first untuk API dan dynamic content,
 * Cache-first untuk static assets (CSS, JS, fonts, icons).
 */

const CACHE_NAME     = 'inoffice-v1';
const API_PREFIX     = '/api/';

// Static assets yang di-cache saat install
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

// ── Install: cache static assets ──────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch((err) => {
                console.warn('[SW] Beberapa static asset gagal di-cache:', err);
            });
        })
    );
    self.skipWaiting();
});

// ── Activate: hapus cache lama ─────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

// ── Fetch: network-first untuk API, cache-first untuk static ───────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET dan API requests (jangan cache API responses)
    if (request.method !== 'GET') return;
    if (url.pathname.startsWith(API_PREFIX)) return;

    // Untuk halaman navigasi: network-first, fallback ke cache
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Cache response navigasi yang berhasil
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return response;
                })
                .catch(() => caches.match(request).then((cached) => cached || caches.match('/')))
        );
        return;
    }

    // Untuk static assets (JS, CSS, fonts, images): cache-first
    if (
        url.pathname.match(/\.(js|css|png|jpg|jpeg|svg|ico|woff|woff2|ttf)$/)
    ) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) return cached;
                return fetch(request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }
});

// ── Push Notification (dipersiapkan untuk Fase 2 — FCM) ───────────────────
self.addEventListener('push', (event) => {
    if (!event.data) return;

    let payload;
    try {
        payload = event.data.json();
    } catch (e) {
        payload = { title: 'inOffice', body: event.data.text() };
    }

    event.waitUntil(
        self.registration.showNotification(payload.title || 'inOffice', {
            body:    payload.body || '',
            icon:    '/icons/icon-192.png',
            badge:   '/icons/icon-192.png',
            data:    payload.data || {},
            vibrate: [200, 100, 200],
        })
    );
});

// ── Notification Click: navigasi ke deep link ─────────────────────────────
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const url = event.notification.data?.url || '/';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            return clients.openWindow(url);
        })
    );
});
