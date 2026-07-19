/*
 * Vite entry for the main Snipe-IT bundle. Replaces the top of snipeit.js:
 * the plugin `require()` calls that lived there (jQuery UI, bootstrap-less,
 * admin-lte, select2, etc.) are re-expressed here as ESM imports so Vite +
 * Rollup can pre-bundle them.
 *
 * jQuery + moment come in through vite.config.js's resolve.alias, which
 * points both packages at tiny shim files that return window.jQuery /
 * window.moment. Those globals are set by blocking classic <script> tags
 * in the layout <head> BEFORE @vite loads, so plugins imported below and
 * inline scripts scattered through the blades all share ONE jQuery
 * instance. See vite.config.js for the full rationale.
 */

import jQuery from 'jquery';

// jQuery UI (widget factory + interactions). Some AdminLTE modules and
// eonasdan's collapse animation rely on `$.widget`.
import 'jquery-ui/dist/jquery-ui';
// AdminLTE calls .tooltip(); jquery-ui also has a .tooltip() and the two
// collide. The pre-Vite entry aliased jquery-ui's version out of the way
// with `jQuery.fn.uitooltip = jQuery.fn.tooltip` right after import.
jQuery.fn.uitooltip = jQuery.fn.tooltip;

// Bootstrap 3 + AdminLTE + positioning. Load order copied from snipeit.js.
import 'bootstrap-less';

// select2's UMD wrapper (dist/js/select2.js) exports an *installer* under
// CJS — module.exports = function(root, jQuery) { installOnto(jQuery); }.
// Under classic <script> the same wrapper detects a browser + global
// jQuery and calls the installer immediately. Rolldown treats it as CJS
// (its module has `exports`), so the installer becomes the return value
// but is never called. We import the default and invoke it explicitly
// with (window, window.jQuery) so `$.fn.select2` actually gets attached.
import select2Installer from 'select2';
if (typeof select2Installer === 'function') {
    select2Installer(window, window.jQuery);
}

import 'admin-lte';
import 'tether';
import 'jquery-slimscroll';
import 'jquery.iframe-transport';
import 'blueimp-file-upload';
import 'bootstrap-colorpicker';
import 'eonasdan-bootstrap-datetimepicker';
import 'ekko-lightbox';
import './extensions/pGenerator.jquery';

// Signature pad is a UMD wrapper; we still expose it via window so page-level
// scripts (asset acceptance modal, etc.) can `new window.SignaturePad(...)`.
import SignaturePad from './signature_pad';
window.SignaturePad = SignaturePad;

import 'jquery-validation';
import List from 'list.js';
window.List = List;

import ClipboardJS from 'clipboard';
window.ClipboardJS = ClipboardJS;

// canvas-confetti (loose script under laravel-mix); its default export is
// the confetti fn. Expose on window so success-notification blades can call
// `confetti(...)`.
import confetti from 'canvas-confetti';
window.confetti = confetti;

// The bulk of the site's JS (jQuery DOM code, select2 AJAX plumbing, the
// snipeitInitDatetimepickers helper, the Livewire select2 bridge, etc.) still
// lives in snipeit.js. The require() calls at the top of that file are
// duplicated above; the file itself only relies on window.jQuery / window.$
// being set, which we just did.
import './snipeit.js';
import './snipeit_modals.js';
