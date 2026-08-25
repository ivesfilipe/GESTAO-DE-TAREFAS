function registerServiceWorker() {
    if (!('serviceWorker' in navigator) || !import.meta.env.PROD) return;

    navigator.serviceWorker.register('/sw.js').catch((error) => {
        console.error('Falha ao registrar Service Worker:', error);
    });
}

registerServiceWorker();
