/**
 * LivingCukes Navigation
 *
 * @author Benjamin Nowack
 * @param {jQuery} $ - jQuery
 * @param {string} template - Module template
 * @param {Object} utils - Utilities
 */
define([
    'jquery',
    'lcsrc/utils/utils',
    // template
    'text!./nav.html',
    // css
    'css!./css/nav'
],
function($, utils, template) {

    var lib = {

        namespace: null,

        selectedPageIndex: 1,

        /**
         * Initializes the component
         *
         * @param {Object} app - Application object
         */
        init: function (app) {
            this.namespace = app.namespace + '.nav';
            this.selectedPageIndex = this.getPageIndex();
            $('#nav').append($(template).html());
            this.initEvents();
        },

        /**
         * Inititializes component events
         */
        initEvents: function () {
            var self = this;
            utils.on('converted.section.' + this.namespace, this.onSectionConverted, this);
            $(window).on('scroll resize', function () { self.onScroll(); });
            window.onhashchange = function () { self.onHashChange(); };
        },

        /**
         * Returns the index of the currently selected page
         *
         * @returns {Number} Page index (starting with "1")
         */
        getPageIndex: function () {
            if (window.location.hash && window.location.hash.match(/\#([0-9]+)/)) {
                return parseInt(window.location.hash.replace(/\#([0-9]+).*$/, '$1'));
            } else {
                return 1;
            }
        },

        /**
         * Adds a nav entry for each heading (h2|h3) in a converted documentation section
         *
         * @param {jQuery.Event} event - Conversion event
         * @param {HTMLElement} section - Converted section
         */
        onSectionConverted: function(event, section) {
            var $section = $(section);
            // extract nav items
            var $nav = $('#nav ul');
            $section.find('h2, h3').each(function(index) {
                var $heading = $(this);
                var tagName = $heading.prop('tagName').toLowerCase();
                var label = $heading.text();
                var sectionId = $section.data('index') + '.' + (index + 1);
                $('<li/>')
                    .addClass(tagName)
                    .data('sectionId', sectionId)
                    .attr('data-section', sectionId)
                    .attr('data-index', $section.data('index'))
                    .attr('data-section-index', index + 1)
                    .append($('<a/>').html(label).attr('href', '#' + sectionId))
                    .data('ref', $heading)
                    .appendTo($nav)
                ;
            });
            this.sortNavItems();
            // activate current page
            this.showActiveSection();
        },

        /**
         * Sorts the (asynchronously injected) nav items
         */
        sortNavItems: function () {
            var $nav = $('#nav ul');
            $nav.find('> li').sort(function(a, b) {
                var $a = $(a);
                var $b = $(b);
                var pageIndexA = parseInt($a.attr('data-index'));
                var pageIndexB = parseInt($b.attr('data-index'));
                if (pageIndexA < pageIndexB) {
                    return -1;
                } else if (pageIndexA > pageIndexB) {
                    return 1;
                } else {
                    var sectionIdA = parseInt($a.attr('data-section-index'));
                    var sectionIdB = parseInt($b.attr('data-section-index'));
                    return sectionIdA < sectionIdB
                        ? -1
                        : 1
                    ;
                }
            }).appendTo($nav);
        },

        /**
         * Shows the currently hash-selected section as the only visible page
         */
        showActiveSection: function () {
            var $section = $('section.doc[data-index="' + this.selectedPageIndex + '"]');
            $section.fadeIn(500);
            $('#nav li.h3').hide().filter('[data-index="' + this.selectedPageIndex + '"]').show();
            $('#nav li.h2').removeClass('active').filter('[data-index="' + this.selectedPageIndex + '"]').addClass('active');
            utils.trigger('shown.section.' + this.namespace, $section);
            // update flags
            $(window).trigger('scroll');
            // scroll to active section
            this.scrollToActiveSection();
        },

        /**
         * Hides the currently visible page
         *
         * @param {function} callback - Callback after fade-out effect has finished
         */
        hideActiveSection: function (callback) {
            $('section.doc[data-index="' + this.selectedPageIndex + '"]').fadeOut(250, callback);
        },

        /**
         * Flags the nav item whise section is currently visible in the viewport
         */
        onScroll: function() {
            var self = this;
            utils.throttle(250, function() {
                var $win = $(window);
                var viewport = {
                    top: $win.scrollTop() + $('#header').outerHeight(),
                    bottom: $win.scrollTop() + $win.height()
                };
                var selected = false;
                $('#nav li').each(function() {
                    var $item = $(this);
                    $item.removeClass('first');
                    if (!selected && parseInt($item.attr('data-index')) === self.selectedPageIndex) {
                        var $heading = $item.data('ref');
                        var pos = $heading.offset();
                        if (pos.top > viewport.top && pos.top < viewport.bottom - $heading.outerHeight()) {
                            $item.addClass('first');
                            selected = true;
                        }
                    }
                });
            }, 'nav.onScroll.' + this.namespace);
        },

        /**
         * Triggers a page change or scroll-effect depending on the window's current hash value
         */
        onHashChange: function () {
            var self = this;
            // page change
            var newPageIndex = this.getPageIndex();
            if (newPageIndex !== this.selectedPageIndex) {
                this.hideActiveSection(function () {
                    self.selectedPageIndex = newPageIndex;
                    self.showActiveSection();
                });
            } else {
                this.scrollToActiveSection();
            }
        },

        /**
         * Scrolls the page to the section specified in the window's hash value
         *
         * e.g. #2.4 => scroll to section 4
         */
        scrollToActiveSection: function () {
            var $heading = $('#nav li[data-section="' + location.hash.replace('#', '')+ '"]').data('ref');
            if ($heading) {
                var scrollTop = $heading.offset().top - $('#header').outerHeight() - 20;
                $('html, body').animate({
                    scrollTop: scrollTop
                }, 500);
            }
        }

    };

    return lib;

});
