/**
 * LivingCukes Markdown Converter
 *
 * @author Benjamin Nowack
 * @param {Object} utils - Utilities lib
 * @param {function} MarkdownConverter - Pagedown
 */
define([
    'lcsrc/utils/utils',
    'mdconv'
],
function(utils, MarkdownConverter) {

    var lib = {

        /**
         * Initializes the component
         *
         * @param {Object} app - Application object
         */
        init: function (app) {
            this.app = app;
            this.namespace = app.namespace + '.md-converter';
        },

        /**
         * Converts a markdown document
         *
         * @param {jQuery} $section - Section element
         */
        convertSection: function($section) {
            $section.addClass('markdown');
            var url = $section.data('url');
            utils.load(url, function(markdown) {
                var html = (new MarkdownConverter()).makeHtml(markdown)
                    .replace(/h5>/g, 'h6>')
                    .replace(/h4>/g, 'h5>')
                    .replace(/h3>/g, 'h4>')
                    .replace(/h2>/g, 'h3>')
                    .replace(/h1>/g, 'h2>')
                ;
                var hasCustomHeading = !!$section.find('h2').length;
                $section.append(html);
                // remove first converted heading if a custom heading was already present in the section
                if (hasCustomHeading) {
                    $section.find('h2:nth-child(2)').remove();
                }
                // inject links to previous designs
                lib.injectDesignLinks($section);
                // emit event
                utils.trigger('converted.section.' + lib.app.namespace, $section);
            });
        },

        /**
         * Injects a navigation below design images (@alt="design") for switching between different versions
         *
         * The method auto-detects image URLs of previous design versions, trailing zeros are supported,
         * e.g. `img@src="design-03.png"` will result in additional links to `design-02.png` and `design-01.png`.
         *
         * @param {jQuery} $section - Section element
         */
        injectDesignLinks: function($section) {
            $section.find('img[alt="design"]').each(function () {
                var $img = $(this);
                // make self clickable
                if (!$img.parent().is('a')) {
                    $img.wrap('<a href="' + $img.attr('src') + '" target="design"></a>');
                }
                // inject links to versions
                var matches = $img.attr('src').match(/^(.*-)([0-9]+)(\.[^.]+)$/);
                if (matches) {
                    var $nav = $('<nav/>').addClass('design-nav').insertAfter($img.parent('a'));
                    var pathPrefix = matches[1];
                    var pathSuffix = matches[3];
                    var versionString = matches[2];
                    var versionNumber = parseInt(versionString);
                    var maxVersionWidth = versionString.length;
                    while (versionNumber > 0) {
                        var versionStrings = ["" + versionNumber];
                        while (versionStrings[0].length < maxVersionWidth) {
                            versionStrings.unshift('0' + versionStrings[0]);
                        }
                        versionStrings.forEach(function (versionString) {
                            var src = pathPrefix + versionString + pathSuffix;
                            var label = 'v' + versionString;
                            lib.injectDesignLink($nav, src, label);
                        });
                        versionNumber--;
                    }
                }
            });
        },

        /**
         * Injects a single design image link for displaying a particular design version
         *
         * @param {jQuery} $nav - Design nav container
         * @param {string} src - Design URL
         * @param {string} label - Version label, e.g. "v02"
         */
        injectDesignLink: function($nav, src, label) {
            var $link = $('<a/>')
                    .attr('href', src)
                    .html(label)
                    .on('click', function(event) {
                        event.preventDefault();
                        var $designContainer = $nav.prev('a');
                        $designContainer
                            .attr('href', src)
                            .find('> img').attr('src', src)
                        ;
                    })
                    .appendTo($nav)
                ;
            // verify img existence because we are guessing version paths
            $('<img/>')
                .on('error', function() {
                    $link.remove()
                })
                .attr('src', src)
            ;
        }

    };

    return lib;

});
