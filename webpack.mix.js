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

// Combine main SnipeIT JS files
mix
  .js(
    [
      "./resources/assets/js/snipeit.js", //this is the actual Snipe-IT JS - require()s bootstrap.js
      "./resources/assets/js/snipeit_modals.js",
      "./node_modules/canvas-confetti/dist/confetti.browser.js",
    ],
    "./public/js/build/app.js" //because of compiling - this does not work very well :(
  )

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
 * Combine JS
 */
mix.combine(
  [
    // lots of node_modules here - should this be subsumed by require()?
    "./node_modules/jquery/dist/jquery.js",
    "./node_modules/select2/dist/js/select2.full.min.js",
    "./node_modules/admin-lte/dist/js/adminlte.min.js",
    "./node_modules/tether/dist/js/tether.js",
    "./node_modules/jquery-ui/dist/jquery-ui.js",
    "./node_modules/jquery-slimscroll/jquery.slimscroll.js",
    "./node_modules/jquery.iframe-transport/jquery.iframe-transport.js",
    "./node_modules/blueimp-file-upload/js/jquery.fileupload.js",
    "./node_modules/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.js",
    "./node_modules/bootstrap-datepicker/dist/js/bootstrap-datepicker.js",
    "./node_modules/ekko-lightbox/dist/ekko-lightbox.js",
    "./resources/assets/js/extensions/pGenerator.jquery.js",
    "./node_modules/chart.js/dist/Chart.js",
      "./resources/assets/js/signature_pad.js", //dupe?
    "./node_modules/jquery-validation/dist/jquery.validate.js",
    "./node_modules/list.js/dist/list.js",
    "./node_modules/clipboard/dist/clipboard.js",
  ],
  "public/js/build/vendor.js" // this file seems OK!
);

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

mix
  .combine(
    ["./public/js/build/vendor.js", "./public/js/build/app.js"],
    "./public/js/dist/all.js"
  )
  .version();