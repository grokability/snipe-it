import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import commonjs from '@rollup/plugin-commonjs';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import path from 'path';

/*
 * Snipe-IT's frontend is a jQuery + Bootstrap 3 + AdminLTE 2 stack that
 * pre-dates ES modules by several years. The blades are dotted with inline
 * <script>$('.select2').select2();</script> tags that run synchronously
 * during document parse — BEFORE @vite's `type="module"` bundle (which is
 * deferred by browser spec) gets a chance to execute. If jQuery is bundled
 * only inside the module chunk, those inline scripts throw "Can't find
 * variable: jQuery" before the page even paints.
 *
 * The fix has two parts:
 *   1. jQuery + moment are copied to public/build/vendor/ and loaded by
 *      the layout as blocking classic <script> tags BEFORE @vite. That
 *      makes window.jQuery / window.moment available to inline scripts
 *      the moment the parser hits them.
 *   2. The bundle's `import jQuery from 'jquery'` is aliased to a shim
 *      module that returns window.jQuery. Same story for moment. That
 *      way plugins loaded inside the bundle (select2, colorpicker,
 *      eonasdan datetimepicker) register onto the SAME jQuery instance
 *      inline scripts use, instead of a bundled second copy.
 */
export default defineConfig({
    resolve: {
        alias: {
            // Route bundle-side imports of jquery / moment to the shims so
            // the bundle shares the SAME jQuery / moment instance the
            // layout's blocking <script> tag defined on window. .cjs
            // extension (CommonJS) is deliberate — rolldown treats CJS
            // modules as EAGERLY evaluated on first import, so
            // module.exports = window.jQuery captures the real value at
            // the moment the bundle's first import touches the module.
            // The equivalent .js file with `export default window.jQuery`
            // was compiled into a lazy-init cell that stayed undefined
            // long enough for jQuery UI's UMD wrapper to see `undefined`
            // and blow up with `e.each is not a function`.
            jquery: path.resolve(__dirname, 'resources/assets/js/jquery-window-shim.cjs'),
            moment: path.resolve(__dirname, 'resources/assets/js/moment-window-shim.cjs'),
        },
    },

    plugins: [
        laravel({
            input: [
                // Main JS + CSS: replaces webpack.mix.js's `all.js` and `all.css`.
                'resources/assets/js/app.js',
                'resources/assets/less/vite-main.less',
                // Separate bundle: bootstrap-table stack (13 concatenated JS files
                // + 4 CSS sources). Kept out of the main bundle to save weight on
                // pages that don't render any bootstrap-table.
                'resources/assets/js/bootstrap-table.js',
                'resources/assets/less/vite-bootstrap-table.less',
            ],
            refresh: false,
        }),
        // TODO: re-enable if any of the jQuery plugins turn out to mix ESM and CJS.
        // In practice Vite's built-in CJS handling has been enough for this
        // stack in Phase A; we can drop back to the explicit plugin later.
        // commonjs({
        //     transformMixedEsModules: true,
        // }),

        viteStaticCopy({
            // `rename: { stripBase: true }` flattens the src path — without
            // it the plugin preserves node_modules/... under the dest dir.
            targets: [
                // jQuery + moment shipped as classic blocking scripts. Loaded
                // by the layout <head> BEFORE @vite so inline scripts scattered
                // through the blades find $ and moment on the parser's first
                // pass. Version is baked into the filename to invalidate the
                // browser cache when either package is bumped.
                {
                    src: 'node_modules/jquery/dist/jquery.min.js',
                    dest: 'vendor',
                    rename: { stripBase: true },
                },
                {
                    src: 'node_modules/moment/min/moment-with-locales.min.js',
                    dest: 'vendor',
                    rename: { stripBase: true },
                },
                // Chart.js v2.9.4 — huge (350 KB), UMD, used on exactly two
                // pages (dashboard + reports/index). Referenced directly via
                // <script src="build/vendor/Chart.min.js"> on those two pages.
                {
                    src: 'node_modules/chart.js/dist/Chart.min.js',
                    dest: 'vendor',
                    rename: { stripBase: true },
                },
                // tableExport + jsPDF + DejaVu font loader + FileSaver + xlsx.
                // These all `$jscomp.getGlobal(this)` or reference bare
                // `jQuery` at module top level, which under ESM strict mode
                // resolves to `undefined` (no ambient this = window). Loading
                // them as classic <script> tags via the bootstrap-table
                // blade partial gives them the browser-global `this` they
                // expect. Load order at the partial: tableExport, jsPDF,
                // DejaVu fonts (MUST come AFTER jsPDF because it reaches
                // into window.jspdf.jsPDF.API.events), FileSaver, xlsx.
                {
                    src: 'node_modules/tableexport.jquery.plugin/tableExport.min.js',
                    dest: 'vendor',
                    rename: { stripBase: true },
                },
                {
                    src: 'node_modules/tableexport.jquery.plugin/libs/jsPDF/jspdf.umd.min.js',
                    dest: 'vendor',
                    rename: { stripBase: true },
                },
                {
                    src: 'resources/assets/js/jspdf-dejavu-fonts.js',
                    dest: 'vendor',
                    rename: { stripBase: true },
                },
                {
                    src: 'resources/assets/js/FileSaver.min.js',
                    dest: 'vendor',
                    rename: { stripBase: true },
                },
                {
                    src: 'node_modules/xlsx/dist/xlsx.core.min.js',
                    dest: 'vendor',
                    rename: { stripBase: true },
                },
                // select2 i18n files — one JS file per locale. Default layout
                // picks the current locale at runtime and loads
                // build/select2/i18n/{locale}.js directly (no @vite).
                {
                    src: 'node_modules/select2/dist/js/i18n/*.js',
                    dest: 'select2/i18n',
                    rename: { stripBase: true },
                },
            ],
        }),
    ],

    optimizeDeps: {
        include: [
            'jquery-ui/dist/jquery-ui',
            'bootstrap-less',
            'admin-lte',
            'tether',
            'select2',
            'bootstrap-colorpicker',
            'eonasdan-bootstrap-datetimepicker',
            'ekko-lightbox',
            'jquery.iframe-transport',
            'blueimp-file-upload',
            'jquery-slimscroll',
            'jquery-validation',
            'list.js',
            'clipboard',
            'canvas-confetti',
        ],
    },

    build: {
        sourcemap: true,
        outDir: 'public/build',
        emptyOutDir: true,
        // Pin output filenames (no content hash). Snipe-IT commits the
        // compiled build/ directory into git so end-users who download or
        // clone the repo get a working app without needing Node/npm.
        // Content-hashed filenames would churn every rebuild and create
        // massive commit diffs + orphaned files. Cache-busting on user
        // upgrades falls back to ETag / Last-Modified from the web server;
        // a hard-refresh may be needed once after an upgrade.
        rollupOptions: {
            output: {
                entryFileNames: 'assets/[name].js',
                chunkFileNames: 'assets/[name].js',
                assetFileNames: 'assets/[name].[ext]',
            },
        },
    },

    css: {
        preprocessorOptions: {
            less: {
                // Bootstrap 3's less mixes .1 into url() paths; javascriptEnabled
                // is required for the eval() inside those mixins.
                javascriptEnabled: true,
            },
        },
    },
});
