// public/sw.js
const CACHE_NAME = 'ujipetik-v1';
const urlsToCache = [
  '/login',
  '/manifest.json',
  '/assets/logo_dishub.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return caches.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(event.request);
      })
  );
});
