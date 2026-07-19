/*
 * CommonJS shim aliased to `moment` in vite.config.js. See jquery-window-shim.cjs
 * for why CJS instead of ESM.
 */
if (!window.moment) {
    throw new Error(
        'window.moment is undefined when the Vite bundle started evaluating. ' +
        'The layout must load moment-with-locales.min.js as a blocking <script src="..."> BEFORE @vite.'
    );
}
module.exports = window.moment;
