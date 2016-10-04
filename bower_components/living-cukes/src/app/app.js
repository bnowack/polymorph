/**
 * Living Cukes
 * 
 * @author Benjamin Nowack
 * 
 * @param {jQuery} $ - jQuery
 * @param {Object} layout - App layout library
 * @param {Object} mdConverter - Converter: from MD
 * @param {Object} jsonConverter - Converter: from Cucumber JSON
 * @param {Object} htmlConverter - Converter: from HTML
 */
require([
    // dependencies
    'jquery',
    // app
    'lcsrc/layout/layout',
    'lcsrc/md-converter/md-converter',
    'lcsrc/json-converter/json-converter',
    'lcsrc/html-converter/html-converter',
    // css
    'css!bower_components/normalize-css/normalize',
    'css!lcsrc/app/css/app'
], 
function($, layout, mdConverter, jsonConverter, htmlConverter) {
    
    var app = {
        
        namespace: 'living-cukes',
        
        /**
         * Initialises the library
         */ 
        init: function() {
            layout.init(this);
            mdConverter.init(this);
            jsonConverter.init(this);
            htmlConverter.init(this);
            this.convertDocs();
        },
        
        /**
         * Converts all doc links to living documentation
         */
        convertDocs: function() {
			$('body > a').each(function(index) {
                app.convertDoc($(this), index + 1);
			});
        },
        
        /**
         * Converts a doc link to living documentation
         * 
         * @param {HTMLElement} $link - Link to be converted
         * @param {number} index - Doc position in the page
         */
        convertDoc: function($link, index) {
            var url = $link.prop('href');
            var format = app.getSectionFormat(url);
            var title = $link.text() || '';
            var $section = $('<section/>')
                .addClass('doc')
                .data('index', index)
                .attr('data-index', index)
                .data('url', url)
                .html(title ? '<h2>' + title + '</h2>' : '')
                .appendTo('#content')
            ;
            $link.remove();
            switch (format) {
                case 'markdown': return mdConverter.convertSection($section);
                case 'json': return jsonConverter.convertSection($section);
                case 'html': return htmlConverter.convertSection($section);
            }
        },
        
        /**
         * Detects a doc's format
         * 
         * @param {String} url - Source document path or URL
         * @returns {String} Document type
         */
        getSectionFormat: function(url) {
            if (url.match(/\.(mark|markdown|md|mdml|mdown|text|mdtext|mdtxt|mdwn|mkd|mkdn)(\.|\#|\?|$)/i)) {
                return 'markdown';
            }
            if (url.match(/\.(json)(\.|\#|\?|$)/i)) {
                return 'json';
            }
            return 'html';
        }
        
    };
    
    // make app available globally
    window.livingCukes = app;
    
    // init the app
    app.init();
    
});
