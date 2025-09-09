const mix = require("laravel-mix");
const fs = require("node:fs");

// This generates a file called app.css, which we use
// later on to build all.css
mix
  .options({
    processCssUrls: false,
    processFontUrls: true,
    clearConsole: false,
  })
  .less("./node_modules/admin-lte/build/less/AdminLTE.less", "css/build")
  .less("./resources/assets/less/app.less", "css/build")
  .less("./resources/assets/less/overrides.less", "css/build")
  .styles(
    [

      "./node_modules/bootstrap/dist/css/bootstrap.css",
      "./node_modules/@fortawesome/fontawesome-free/css/all.css",
      "./public/css/build/AdminLTE.css",
      "./node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.standalone.css",
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

var skins = fs.readdirSync("resources/assets/less/skins");

// Convert the skins to CSS
for (var i in skins) {
    mix.less(
        "resources/assets/less/skins/" + skins[i],
        "css/dist/skins"
    )
}

var css_skins = fs.readdirSync("public/css/dist/skins");
for (var i in css_skins) {
    if (css_skins[i].endsWith(".min.css")) {
        //don't minify already minified skinns
        continue;
    }
    if (css_skins[i].endsWith(".css")) {
        // only minify files ending with '.css'
        mix.minify("public/css/dist/skins/" + css_skins[i]).version();
    }
    //TODO - if we only ever use the minified versions, this could be simplified down to one line (above)
    // but it stays like this so we have the minified and non-minified versions of the skins
    // right now the code seems to use the un-minified skins
}

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
            './resources/assets/js/extensions/jquery.base64.js',
            './node_modules/tableexport.jquery.plugin/tableExport.min.js',
            './node_modules/tableexport.jquery.plugin/libs/jsPDF/jspdf.umd.min.js',
            './resources/assets/js/FileSaver.min.js',
            './node_modules/xlsx/dist/xlsx.core.min.js',
            './node_modules/bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.js',
            './node_modules/bootstrap-table/dist/extensions/toolbar/bootstrap-table-toolbar.js'
        ],
        'public/js/dist/bootstrap-table.js'
 ).version();