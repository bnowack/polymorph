/**
 * LivingCukes Layout Library
 * 
 * @author Benjamin Nowack
 * @param {jQuery} $ jQuery
 * @param {string} template - Module template
 */

define([
    'jquery',
    'lcsrc/utils/utils',
    'lcsrc/header/header',
    'lcsrc/nav/nav',
    // template
    'text!./layout.html',
    // css
    'css!./css/layout'
], 
function($, utils, header, nav, template) {

    var lib = {

        init: function (app) {
            $('body').append($(template).html());
            header.init(app);
            nav.init(app);
            $(window).on('resize', this.onResize);
            $(window).trigger('resize');
        },

        onResize: function () {
            utils.debounce(50, function() {
                $('#content').css('min-height', $(window).height());
            });
        }

    };

    return lib;

});
