<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('{{ route("pwa.serviceworker") }}', { scope: '/' })
            .then(function (registration) {
                console.log('[PWA] Service Worker registered:', registration.scope);
            })
            .catch(function (error) {
                console.error('[PWA] Service Worker registration failed:', error);
            });
    });
}

window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    window.dispatchEvent(new CustomEvent('pwa-installable', { detail: { prompt: e } }));
});
</script>
