import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import Fuse from 'fuse.js';
import * as SubCrypto from './crypto.js';

window.Sortable = Sortable;
window.Fuse = Fuse;
window.Alpine = Alpine;
window.SubCrypto = SubCrypto;

Alpine.start();
