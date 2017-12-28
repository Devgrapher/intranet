const CACHE_NAME = 'ridibooks';
const urlsToCache = [
  '/static/bower_components/bootstrap/dist/css/bootstrap.min.css',
  '/static/bower_components/jquery-ui/themes/base/jquery-ui.min.css',
];

self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(urlsToCache);
    })
  )
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(response => {
      return response || fetch(event.request);
    })
  );
});
