/**
 * LivingCukes HTML Converter
 * 
 * @author Benjamin Nowack
 * @param {jQuery} $ - jQuery
 * @param {Object} utils - Utilities
 */
define([
    'jquery',
    'lcsrc/utils/utils',
    // css
    'css!lcsrc/html-converter/css/html-converter'
], 
function($, utils) {
    
    var lib = {

        /**
         * Initializes the component
         * 
         * @param {Object} app - Application object
         */
        init: function (app) {
            this.app = app;
            this.namespace = app.namespace + '.html-converter';
            this.initEvents();
        },
        
        /**
         * Inititializes component events
         */
        initEvents: function () {
            var self = this;
            utils.on('shown.section.nav.' + this.app.namespace, this.scaleIframes, this);
            $(window).on('resize', function () { self.scaleIframes(); });
        },

        /**
         * Converts an HTML doc
         * 
         * @param {jQuery} $section - HTML section
         */
        convertSection: function ($section) {
            var url = $section.data('url');
            $section.addClass('html');
            $section.append($('<iframe/>').attr('src', url));
            $section.append($('<a/>').addClass('source').attr('href', url).attr('target', '_ext').html(url.replace(/^.*\/([^\/]+)$/, '$1')));
            utils.trigger('converted.section.' + this.app.namespace, $section);
        },
        
        /**
         * Makes sure the iframe fits in the available space
         */
        scaleIframes: function () {
            utils.throttle(250, function() {
                var $win = $(window);
                var sections = $('.doc.html').filter(function () { return $(this).css('display') === 'block'; });
                sections.each(function () {
                    var $iframe = $(this).find('iframe');
                    var iframeTop = $iframe.offset().top;
                    var targetBottom = $win.height() - 75;
                    $iframe.height(targetBottom - iframeTop);
                });
            }, 'scaleIframes.' + this.namespace);
        }
                
    };
    
    return lib;
    
});
