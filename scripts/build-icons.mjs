import { readFileSync, writeFileSync, existsSync, mkdirSync, copyFileSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

const LUCIDE_SRC = join(ROOT, 'node_modules/lucide-static/icons');
const SIMPLE_SRC = join(ROOT, 'node_modules/simple-icons/icons');
const LUCIDE_DEST = join(ROOT, 'public/icons/lucide');
const BRANDS_DEST = join(ROOT, 'public/icons/brands');

mkdirSync(LUCIDE_DEST, { recursive: true });
mkdirSync(BRANDS_DEST, { recursive: true });

// Lucide icons needed (from service_types)
const lucideIcons = [
  'server', 'server-cog', 'hard-drive', 'globe', 'shield-check',
  'cloud', 'network', 'database', 'archive', 'mail', 'shield',
  'waypoints', 'activity', 'lock', 'box', 'clapperboard', 'music',
  'palette', 'code', 'sparkles', 'bar-chart-3', 'message-circle',
  'key', 'gamepad-2', 'credit-card', 'tag',
  // extra common ones
  'settings', 'user', 'bell', 'trash-2', 'edit-2', 'plus', 'x',
  'check', 'search', 'filter', 'download', 'upload', 'copy',
  'external-link', 'chevron-down', 'chevron-right', 'grip-vertical',
  'eye', 'eye-off', 'refresh-cw', 'calendar', 'clock', 'alert-triangle',
  'info', 'zap', 'dollar-sign', 'trending-up', 'layers', 'menu',
  'log-out', 'moon', 'sun', 'home', 'list', 'grid-3x3',
  'more-vertical', 'move', 'link', 'link-2', 'send', 'webhook',
];

let lucideOk = 0, lucideMiss = 0;
for (const slug of lucideIcons) {
  const src = join(LUCIDE_SRC, `${slug}.svg`);
  if (existsSync(src)) {
    copyFileSync(src, join(LUCIDE_DEST, `${slug}.svg`));
    lucideOk++;
  } else {
    console.warn(`[WARN] Lucide icon not found: ${slug}`);
    lucideMiss++;
  }
}
console.log(`Lucide: ${lucideOk} copied, ${lucideMiss} missing`);

// Simple-icons brands (from catalog_presets)
const simpleIcons = [
  'amazonwebservices', 'googlecloud', 'microsoftazure', 'digitalocean',
  'vultr', 'linode', 'hetzner', 'ovh', 'scaleway', 'oracle',
  'cloudflare', 'fastly', 'heroku', 'vercel', 'netlify', 'render',
  'railway', 'backblaze', 'namecheap', 'godaddy', 'letsencrypt',
  'google', 'slack', 'discord', 'zoom', 'telegram',
  'github', 'gitlab', 'bitbucket', 'docker', 'jetbrains',
  'mongodb', 'supabase', 'redis', 'datadog', 'sentry', 'grafana',
  'uptimerobot', 'nordvpn', 'expressvpn', 'surfshark', 'protonvpn',
  '1password', 'bitwarden', 'netflix', 'youtube', 'primevideo',
  'spotify', 'applemusic', 'openai', 'claude', 'adobe', 'figma',
  'canva', 'notion', 'dropbox', 'icloud', 'steam', 'playstation',
  'xbox', 'mailchimp', 'protonmail', 'vk',
];

let siOk = 0, siMiss = 0;
for (const slug of simpleIcons) {
  const src = join(SIMPLE_SRC, `${slug}.svg`);
  if (existsSync(src)) {
    copyFileSync(src, join(BRANDS_DEST, `${slug}.svg`));
    siOk++;
  } else {
    console.warn(`[WARN] Simple Icons not found: ${slug} — will use fallback`);
    siMiss++;
  }
}
console.log(`Simple Icons: ${siOk} copied, ${siMiss} missing (fallback applied)`);
console.log('Icons build complete.');
