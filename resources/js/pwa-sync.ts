/**
 * PWA Background Sync — Queue requests for offline replay.
 *
 * Usage:
 *   import { pwaSync } from '@/../../packages/laravelplus/pwa/resources/js/pwa-sync';
 *   await pwaSync('/api/posts', { method: 'POST', body: JSON.stringify(data) });
 */

interface SyncEntry {
    url: string;
    method: string;
    headers: Record<string, string>;
    body: string | null;
    timestamp: number;
}

const DB_NAME = 'pwa-sync';
const STORE_NAME = 'requests';
const SYNC_TAG = 'pwa-background-sync';

function openDB(): Promise<IDBDatabase> {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, 1);
        request.onupgradeneeded = () => {
            request.result.createObjectStore(STORE_NAME, {
                keyPath: 'id',
                autoIncrement: true,
            });
        };
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

async function queueRequest(entry: SyncEntry): Promise<void> {
    const db = await openDB();
    const tx = db.transaction(STORE_NAME, 'readwrite');
    tx.objectStore(STORE_NAME).add(entry);

    return new Promise((resolve, reject) => {
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
    });
}

async function registerSync(): Promise<void> {
    if ('serviceWorker' in navigator && 'SyncManager' in window) {
        const registration = await navigator.serviceWorker.ready;
        await (registration as any).sync.register(SYNC_TAG);
    }
}

/**
 * Send a fetch request, falling back to background sync when offline.
 */
export async function pwaSync(
    url: string,
    options: RequestInit = {},
): Promise<Response | void> {
    try {
        const response = await fetch(url, options);
        return response;
    } catch {
        const headers: Record<string, string> = {};
        if (options.headers) {
            const h = new Headers(options.headers);
            h.forEach((value, key) => {
                headers[key] = value;
            });
        }

        const entry: SyncEntry = {
            url,
            method: (options.method || 'GET').toUpperCase(),
            headers,
            body: typeof options.body === 'string' ? options.body : null,
            timestamp: Date.now(),
        };

        await queueRequest(entry);
        await registerSync();
    }
}
