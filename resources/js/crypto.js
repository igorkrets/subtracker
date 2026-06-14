/**
 * SubTracker client-side encryption module.
 *
 * Algorithm: AES-256-GCM with PBKDF2 key derivation (SHA-256, 310 000 iterations).
 * All encrypt/decrypt happens in the browser via Web Crypto API.
 * The server only ever stores the ciphertext — it cannot read your notes.
 *
 * Ciphertext format: v1:<base64(16-byte salt)>:<base64(12-byte IV)>:<base64(ciphertext+GCM-tag)>
 */

const PBKDF2_ITERATIONS = 310_000;
const LS_KEY = 'subtracker_enc_password';

function b64enc(buf) {
    return btoa(String.fromCharCode(...new Uint8Array(buf)));
}

function b64dec(str) {
    return Uint8Array.from(atob(str), c => c.charCodeAt(0));
}

async function deriveKey(password, salt) {
    const raw = await crypto.subtle.importKey(
        'raw',
        new TextEncoder().encode(password),
        'PBKDF2',
        false,
        ['deriveKey']
    );
    return crypto.subtle.deriveKey(
        { name: 'PBKDF2', salt, iterations: PBKDF2_ITERATIONS, hash: 'SHA-256' },
        raw,
        { name: 'AES-GCM', length: 256 },
        false,
        ['encrypt', 'decrypt']
    );
}

export async function encrypt(plaintext, password) {
    const salt = crypto.getRandomValues(new Uint8Array(16));
    const iv   = crypto.getRandomValues(new Uint8Array(12));
    const key  = await deriveKey(password, salt);
    const ct   = await crypto.subtle.encrypt(
        { name: 'AES-GCM', iv },
        key,
        new TextEncoder().encode(plaintext)
    );
    return `v1:${b64enc(salt)}:${b64enc(iv)}:${b64enc(ct)}`;
}

export async function decrypt(ciphertext, password) {
    const parts = ciphertext.split(':');
    if (parts.length !== 4 || parts[0] !== 'v1') throw new Error('bad_format');
    const salt = b64dec(parts[1]);
    const iv   = b64dec(parts[2]);
    const ct   = b64dec(parts[3]);
    const key  = await deriveKey(password, salt);
    const plain = await crypto.subtle.decrypt({ name: 'AES-GCM', iv }, key, ct);
    return new TextDecoder().decode(plain);
}

export function hasPassword() {
    return !!localStorage.getItem(LS_KEY);
}

export function getPassword() {
    return localStorage.getItem(LS_KEY) || null;
}

export function setPassword(pwd) {
    if (pwd) localStorage.setItem(LS_KEY, pwd);
    else localStorage.removeItem(LS_KEY);
}

/** Simple visual strength: 0-4 */
export function strength(pwd) {
    if (!pwd) return 0;
    let s = 0;
    if (pwd.length >= 10) s++;
    if (pwd.length >= 16) s++;
    if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd)) s++;
    if (/[0-9]/.test(pwd)) s++;
    if (/[^A-Za-z0-9]/.test(pwd)) s++;
    return Math.min(s, 4);
}
