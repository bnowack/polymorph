/**
 * LivingCukes Header
 * 
 * @author Benjamin Nowack
 * @param {jQuery} $ - jQuery
 */
define([
    'jquery',
    // css
    'css!./css/header'
],
function($) {
    
    var lib = {
        
        init: function (app) {
           this.$header = $('#header');
           this.$header.find('h1').empty().append(
               $('<a/>').attr('href', location.pathname).html(document.title)
            );
        }
                
    };
    
    return lib;
    
});
