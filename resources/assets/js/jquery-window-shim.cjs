/*
 * CommonJS shim aliased to `jquery` in vite.config.js. Returning
 * window.jQuery through `module.exports = ...` is important: rolldown
 * treats CJS modules as eagerly-evaluated on first import, whereas the
 * equivalent ESM `export default window.jQuery` gets compiled into a
 * lazy-init cell that races with jQuery-plugin code that reads it.
 *
 * Layout ships jquery.min.js as a blocking classic <script>, so by the
 * time this module first evaluates, window.jQuery is guaranteed defined.
 */
if (!window.jQuery) {
    throw new Error(
        'window.jQuery is undefined when the Vite bundle started evaluating. ' +
        'The layout must load jquery.min.js as a blocking <script src="..."> BEFORE @vite.'
    );
}
module.exports = window.jQuery;
