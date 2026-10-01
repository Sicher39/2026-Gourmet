// Filament and Laravel stay network-only. Do not intercept, cache, or replay
// navigations, authenticated HTML, Livewire, uploads, or mutation requests.
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));
self.addEventListener('fetch', () => {
    // Intentionally let the browser make every request normally.
});
