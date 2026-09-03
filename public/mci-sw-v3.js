const CACHE_NAME = "mci-app-v3";

const CORE = [
  "/manifest.webmanifest",
  "/css/site.css",
  "/css/mci-app-footer-v3.css",
  "/js/navigation.js",
  "/js/site.js",
  "/js/mci-app-installer-v3.js",
  "/images/mci-logo.webp",
  "/images/app-icon-192.png",
  "/images/app-icon-512.png"
];

self.addEventListener("install", event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(CORE))
  );
  self.skipWaiting();
});

self.addEventListener("activate", event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => key !== CACHE_NAME)
          .map(key => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

self.addEventListener("fetch", event => {
  const request = event.request;

  if (
    request.method !== "GET" ||
    new URL(request.url).origin !== location.origin
  ) return;

  if (request.mode === "navigate") return;

  event.respondWith(
    fetch(request)
      .then(response => {
        const copy = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(request, copy));
        return response;
      })
      .catch(() => caches.match(request))
  );
});
