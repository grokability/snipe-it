/*
 * Vite entry for the bootstrap-table bundle. Mirrors the 13-file combine()
 * in webpack.mix.js, in the same order. Order matters:
 *   - dragtable must load before bootstrap-table so it can extend the widget
 *   - jspdf-dejavu-fonts.js must load AFTER jspdf.umd.min.js because it
 *     reaches into window.jspdf.jsPDF.API.events at module top level.
 *   - table extensions after the base module.
 *
 * jQuery is supplied by the blocking <script src="build/vendor/jquery.min.js">
 * the layout emits before @vite. Every `import 'jquery'` in this bundle
 * resolves to a shim that returns window.jQuery via vite.config.js's
 * resolve.alias, so bootstrap-table registers on the same instance the
 * inline blade scripts use.
 */

// Base extensions
import './dragtable.js';
import 'bootstrap-table';
import 'bootstrap-table/dist/extensions/mobile/bootstrap-table-mobile.js';
import 'bootstrap-table/dist/extensions/export/bootstrap-table-export.js';
import 'bootstrap-table/dist/extensions/cookie/bootstrap-table-cookie.js';
import 'bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.js';
import 'bootstrap-table/dist/extensions/fixed-columns/bootstrap-table-fixed-columns.min.js';
import 'bootstrap-table/dist/extensions/addrbar/bootstrap-table-addrbar.js';
import 'bootstrap-table/dist/extensions/print/bootstrap-table-print.min.js';
import 'bootstrap-table/dist/extensions/custom-view/bootstrap-table-custom-view.js';

// Local util used by the export extension for base64 encoding
import './extensions/jquery.base64.js';

// tableExport + jsPDF + DejaVu font loader + FileSaver + xlsx are NOT
// imported here — they're loaded as classic <script> tags by
// resources/views/partials/bootstrap-table.blade.php. Under Vite's ESM
// strict mode top-level `this` is undefined, and every one of those
// legacy blobs does `$jscomp.getGlobal(this)` or an equivalent trick to
// find `window`. Loading them as classic scripts keeps `this === window`
// so their internal polyfill infrastructure initializes correctly.

// Duplicated in mix's combine() — sticky-header again + toolbar
import 'bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.js';
import 'bootstrap-table/dist/extensions/toolbar/bootstrap-table-toolbar.js';

// Locales. Under mix these were separate `<script src>`s in the blade;
// bringing them into the bundle preserves the same "loaded everywhere the
// table is used" contract with one fewer network request.
// The en-US import must come LAST so it wins the locale-registration race
// against the all-locales bundle — otherwise bootstrap-table falls back to
// its first-in-order locale (Chinese) instead of English on unknown locales.
// See: https://bootstrap-table.com/docs/api/table-options/#locale
import 'bootstrap-table/dist/bootstrap-table-locale-all.min.js';
import 'bootstrap-table/dist/locale/bootstrap-table-en-US.min.js';
