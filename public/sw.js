// Trading Journal - Service Worker
const CACHE_NAME = 'trading-journal-v1';
const OFFLINE_URL = '/offline.html';

// Assets to cache on install
const PRECACHE_ASSETS = [
  '/',
  '/offline.html',
  '/css/app.css',
  '/js/app.js',
  '/manifest.json',
  '/images/icons/icon-72x72.png',
  '/images/icons/icon-192x192.png',
  '/images/icons/icon-512x512.png'
];

// Install event - precache assets
self.addEventListener('install', event => {
  console.log('[Service Worker] Installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[Service Worker] Caching app shell');
        return cache.addAll(PRECACHE_ASSETS);
      })
      .then(() => {
        console.log('[Service Worker] Skip waiting');
        return self.skipWaiting();
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('[Service Worker] Activating...');
  
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('[Service Worker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      console.log('[Service Worker] Claiming clients');
      return self.clients.claim();
    })
  );
});

// Fetch event - network first, fallback to cache
self.addEventListener('fetch', event => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') return;
  
  // Skip Chrome extensions
  if (event.request.url.startsWith('chrome-extension://')) return;
  
  // Skip analytics and external resources
  if (event.request.url.includes('google-analytics') || 
      event.request.url.includes('googletagmanager')) {
    return;
  }
  
  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Cache successful responses
        if (response.status === 200) {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseClone);
          });
        }
        return response;
      })
      .catch(() => {
        // Network failed, try cache
        return caches.match(event.request)
          .then(cachedResponse => {
            if (cachedResponse) {
              return cachedResponse;
            }
            
            // If not in cache and request is for a page, show offline page
            if (event.request.headers.get('accept').includes('text/html')) {
              return caches.match(OFFLINE_URL);
            }
            
            // For other resources, return a fallback
            return new Response('Offline', {
              status: 503,
              statusText: 'Service Unavailable',
              headers: new Headers({
                'Content-Type': 'text/plain'
              })
            });
          });
      })
  );
});

// Background sync for offline data
self.addEventListener('sync', event => {
  console.log('[Service Worker] Background sync:', event.tag);
  
  if (event.tag === 'sync-trades') {
    event.waitUntil(syncTrades());
  }
});

// Push notifications
self.addEventListener('push', event => {
  console.log('[Service Worker] Push received');
  
  const data = event.data ? event.data.json() : {};
  const title = data.title || 'Trading Journal';
  const options = {
    body: data.body || 'You have a new notification',
    icon: '/images/icons/icon-192x192.png',
    badge: '/images/icons/badge-72x72.png',
    tag: data.tag || 'general',
    data: data.data || {},
    actions: data.actions || [],
    requireInteraction: data.requireInteraction || false,
    silent: data.silent || false
  };
  
  event.waitUntil(
    self.registration.showNotification(title, options)
  );
});

// Notification click handler
self.addEventListener('notificationclick', event => {
  console.log('[Service Worker] Notification click:', event.notification.tag);
  
  event.notification.close();
  
  const urlToOpen = event.notification.data.url || '/';
  
  event.waitUntil(
    clients.matchAll({
      type: 'window',
      includeUncontrolled: true
    }).then(windowClients => {
      // Check if there's already a window/tab open with the target URL
      for (let client of windowClients) {
        if (client.url === urlToOpen && 'focus' in client) {
          return client.focus();
        }
      }
      
      // If not, open a new window/tab
      if (clients.openWindow) {
        return clients.openWindow(urlToOpen);
      }
    })
  );
});

// Helper function to sync trades when back online
async function syncTrades() {
  console.log('[Service Worker] Syncing trades...');
  
  // Get trades from IndexedDB
  const db = await openTradesDB();
  const trades = await getAllTrades(db);
  
  // Sync each trade
  for (const trade of trades) {
    try {
      const response = await fetch('/api/trades/sync', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': trade.csrf_token
        },
        body: JSON.stringify(trade)
      });
      
      if (response.ok) {
        // Remove from IndexedDB after successful sync
        await deleteTrade(db, trade.id);
      }
    } catch (error) {
      console.error('[Service Worker] Sync error:', error);
    }
  }
}

// IndexedDB helper functions
function openTradesDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('trading-journal-offline', 1);
    
    request.onupgradeneeded = event => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains('trades')) {
        db.createObjectStore('trades', { keyPath: 'id' });
      }
    };
    
    request.onsuccess = event => resolve(event.target.result);
    request.onerror = event => reject(event.target.error);
  });
}

function getAllTrades(db) {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['trades'], 'readonly');
    const store = transaction.objectStore('trades');
    const request = store.getAll();
    
    request.onsuccess = event => resolve(event.target.result);
    request.onerror = event => reject(event.target.error);
  });
}

function deleteTrade(db, id) {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['trades'], 'readwrite');
    const store = transaction.objectStore('trades');
    const request = store.delete(id);
    
    request.onsuccess = () => resolve();
    request.onerror = event => reject(event.target.error);
  });
}