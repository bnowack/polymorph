/**
 * RequireJS config for LivingCukes
 */
require.config({
    urlArgs: 'appVersion=' + (location.host.match(/^(local|192.168|bnowack.github.io|$)/) ? Math.random() : '0.1.7'),
    paths: {
        jquery: 'bower_components/jquery/dist/jquery.min',
        text: 'bower_components/requirejs-plugins/lib/text',
        css: 'bower_components/require-css/css.min',
        mdconv: 'bower_components/pagedown/Markdown.Converter',
        prettify: 'bower_components/prettify/src/prettify',
        lcsrc: 'src'
    },
    shim: {
        mdconv: {
            exports: "Markdown.Converter"
        },
        prettify: {
            deps: ['css!bower_components/prettify/src/prettify'],
            exports: "prettyPrint"
        }
    }
});


