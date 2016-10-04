/**
 * Step definitions (E2E)
 */
module.exports = function () {

    this.When('I click on the header logo', function (callback) {
        var self = this;
        var selector = this.browser.hasShadowDom
            ? 'polymorph-app-header::shadow a'
            : 'polymorph-app-header a';
        this.browser
            .waitFor(selector)
            .then(function () {
                var $logo = $(self.browser.document).find(selector);
                $logo.trigger('click');
                $logo[0].click();
                callback();
            });
    });

    this.Then('I should see the home page', function (callback) {
        var self = this;
        this.browser
            .waitFor('polymorph-page[path="/"]')
            .then(function () {
                callback();
            });
    });

};
