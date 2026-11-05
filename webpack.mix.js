const mix = require("laravel-mix");
const fs = require("node:fs");

// Mix 6 generates css-loader 5 options. Keep its URL filtering and CSS
// module exports compatible with the css-loader 7 dependency override.
mix.override(config => {
    const rules = config.module.rules.flatMap(rule => rule.oneOf || [rule]);

    for (const rule of rules) {
        for (const loader of rule.use || []) {
            if (typeof loader !== 'object' || loader.loader !== require.resolve('css-loader')) {
                continue;
            }

            if (typeof loader.options.url === 'function') {
                loader.options.url = { filter: loader.options.url };
            }

            if (loader.options.modules) {
                loader.options.modules.namedExport = false;
                loader.options.modules.exportLocalsConvention = 'as-is';
            }
        }
    }
});

// This generates a file called app.css, which we use
// later on to build all.css
mix
  .options({
    processCssUrls: false,
    processFontUrls: true,
    clearConsole: false,
    // Turn off postcss-calc (bundled into cssnano-preset-default). It
    // chokes on CSS Level 5 relative color syntax such as
    // `hsl(from var(--foo) h s calc(l - 10))`, misreading the color-channel
    // keyword `l` as an undefined variable and emitting a "Lexical error"
    // warning per calc() expression.
    cssNano: {
        calc: false,
    },
  })
  .less("./node_modules/admin-lte/build/less/AdminLTE.less", "css/build")
  .less("./resources/assets/less/app.less", "css/build")
  .less("./resources/assets/less/overrides.less", "css/build")
  .styles(
    [

      "./node_modules/bootstrap/dist/css/bootstrap.css",
      "./node_modules/@fortawesome/fontawesome-free/css/all.css",
      "./public/css/build/AdminLTE.css",
      "./node_modules/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css",
      "./node_modules/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.css",
      "./node_modules/blueimp-file-upload/css/jquery.fileupload.css",
      "./node_modules/blueimp-file-upload/css/jquery.fileupload-ui.css",
      "./node_modules/ekko-lightbox/dist/ekko-lightbox.css",
      "./node_modules/bootstrap-table/dist/bootstrap-table.css",
      "./public/css/build/app.css",
      "./node_modules/select2/dist/css/select2.css",
      "./public/css/build/overrides.css",
    ],
    "./public/css/dist/all.css"
  )
  .version();


/**
 * Copy, minify and version signature-pad.css
 */
mix
  .copy("./resources/assets/css/signature-pad.css", "./public/css/dist")
  .minify("./public/css/dist/signature-pad.css");

/**
 * Copy and version select2
 */
mix
    .copy("./node_modules/select2/dist/js/i18n", "./public/js/select2/i18n")

/**
 * Copy and version fontawesome
 */
mix
    .copy("./node_modules/@fortawesome/fontawesome-free/webfonts", "./public/css/webfonts")

/**
 * Copy BS tables js file
 */
mix
    .copy( './node_modules/bootstrap-table/dist/bootstrap-table-locale-all.min.js', 'public/js/dist' )
    .copy( './node_modules/bootstrap-table/dist/locale/bootstrap-table-en-US.min.js', 'public/js/dist' )

/**
 * Copy Chart.js file (it's big, and used in only one place)
 */
mix
    .copy('./node_modules/chart.js/dist/Chart.min.js', 'public/js/dist')

// Combine main SnipeIT JS files
mix
  .js(
    [
        "./resources/assets/js/snipeit.js",
      "./resources/assets/js/snipeit_modals.js",
      "./node_modules/canvas-confetti/dist/confetti.browser.js",
        // The general direction we have been going is to pull these via require() directly
        // But this runs in only one place, is only 24k, and doesn't break the sourcemaps
        // (and it needs to run in 'immediate' mode, not in 'moar_scripts'), so let's just
        // leave it here. It *could* be moved to confetti-js.blade.php, but I don't think
        // it helps anything if we do that.
    ],
      "./public/js/dist/all.js"
  ).sourceMaps(true, 'source-map', 'source-map').version();

/**
 * Standalone chunk for calendar pages. FullCalendar v6 is
 * ES-module-first and ~200KB; keeping it out of the always-loaded
 * all.js bundle so pages that don't render a calendar don't pay the
 * cost. Exposes window.snipeitCalendar.init(elementId, config) so
 * per-entity calendar blades (maintenances, upcoming audits, expected
 * checkins, user end-dates, etc.) share the same init path and only
 * differ in which JSON events endpoint they hit.
 */
mix
  .js(
    './resources/assets/js/snipeit-calendar.js',
    './public/js/dist/snipeit-calendar.js'
  ).sourceMaps(true, 'source-map', 'source-map').version();



/**
 * Combine bootstrap table css
 */
mix
  .combine(
    [
      "./node_modules/bootstrap-table/dist/bootstrap-table.css",
      "./node_modules/bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.css",
     "./resources/assets/css/dragtable.css",
    ],
    "public/css/dist/bootstrap-table.css"
  )
  .version();

/**
 * Combine bootstrap table js
 */
mix
  .combine(
        [
            "./resources/assets/js/dragtable.js",
            './node_modules/bootstrap-table/dist/bootstrap-table.js',
            './node_modules/bootstrap-table/dist/extensions/mobile/bootstrap-table-mobile.js',
            './node_modules/bootstrap-table/dist/extensions/export/bootstrap-table-export.js',
            './node_modules/bootstrap-table/dist/extensions/cookie/bootstrap-table-cookie.js',
            './node_modules/bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.js',
            './node_modules/bootstrap-table/dist/extensions/addrbar/bootstrap-table-addrbar.js',
            './node_modules/bootstrap-table/dist/extensions/print/bootstrap-table-print.min.js',
            './node_modules/bootstrap-table/dist/extensions/custom-view/bootstrap-table-custom-view.js',
            './node_modules/bootstrap-table/dist/extensions/reorder-columns/bootstrap-table-reorder-columns.js',
            './resources/assets/js/extensions/jquery.base64.js',
            './node_modules/tableexport.jquery.plugin/tableExport.min.js',
            './node_modules/tableexport.jquery.plugin/libs/jsPDF/jspdf.umd.min.js',
            // DejaVuSans (regular + bold) registered into jsPDF's VFS so PDF
            // exports render Cyrillic / Greek / Hebrew / Arabic / etc. Must be
            // included AFTER jspdf.umd.min.js — the loader reaches into
            // window.jspdf.jsPDF.API.events to hook the font registration.
            './resources/assets/js/jspdf-dejavu-fonts.js',
            './resources/assets/js/FileSaver.min.js',
            './node_modules/xlsx/dist/xlsx.core.min.js',
            './node_modules/bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.js',
            './node_modules/bootstrap-table/dist/extensions/toolbar/bootstrap-table-toolbar.js'
        ],
        'public/js/dist/bootstrap-table.js'
 ).version();
