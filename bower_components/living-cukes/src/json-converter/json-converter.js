/**
 * LivingCukes Cucumber JSON Converter
 * 
 * @author Benjamin Nowack
 * @param {jQuery} $ - jQuery
 * @param {Object} utils - Utilities
 * @param {String} featureTemplate - Feature template snippet
 * @param {String} scenarioTemplate - Scenario template snippet
 * @param {String} stepTemplate - Step template snippet
 */
define([
    'jquery',
    'lcsrc/utils/utils',
    // templates
    'text!./feature.html',
    'text!./scenario.html',
    'text!./step.html',
    // css
    'css!lcsrc/json-converter/css/json-converter'
], 
function($, utils, featureTemplate, scenarioTemplate, stepTemplate) {
    
    var lib = {
        
        /**
         * Initializes the component
         * 
         * @param {Object} app - Application object
         */
        init: function (app) {
            this.app = app;
            this.namespace = app.namespace + '.json-converter';
        },
        
        /**
         * Converts a Cucumber JSON document
         * 
         * @param {jQuery} $section - Section element
         */
        convertSection: function ($section) {
            var self = this;
            var url = $section.data('url');
            $section.addClass('json');
            utils.load(url, function(json) {
                var report = JSON.parse(json);
                var $report = $('<ul/>').addClass('features');
                self.convertFeatures(report, $report);
                $section.append($report);
                $section.append($('<a/>').addClass('source').attr('href', url).attr('target', '_ext').html(url.replace(/^.*\/([^\/]+)$/, '$1')));
                utils.trigger('converted.section.' + self.app.namespace, $section);
            });
        },
        
        /**
         * Converts feature objects
         * 
         * @param {Array} features - Cucumber report features
         * @param {jQuery} $features - Container element
         */
        convertFeatures: function (features, $features) {
            var self = this;
            features.forEach(function (feature) {
                var $feature = $($(featureTemplate).html());
                $feature.find('.heading').html(feature.name).attr('data-keyword', 'Feature: ');
                var description = feature.description || feature.name;
                $feature.find('.description').html(description.replace(/[\r\n]+/g, '<br/>'));
                $features.append($feature);
                self.injectFeatureStats(feature, $feature);
                self.convertScenarios(feature.elements || [], $feature.find('.scenarios'));
            });
        },
        
        /**
         * Converts scenario objects
         * 
         * @param {Array} scenarios - Cucumber report scenarios
         * @param {jQuery} $scenarios - Container element
         */
        convertScenarios: function (scenarios, $scenarios) {
            var self = this;
            scenarios.forEach(function (scenario) {
                var $scenario = $($(scenarioTemplate).html());
                $scenario.find('.heading').html(scenario.name).attr('data-keyword', scenario.keyword + ': ');
                $scenarios.append($scenario);
                self.injectScenarioStats(scenario, $scenario);
                self.convertSteps(scenario.steps || [], $scenario.find('.steps'));
            });
        },
        
        /**
         * Converts step objects
         * 
         * @param {Array} steps - Cucumber report steps
         * @param {jQuery} $steps - Container element
         */
        convertSteps: function (steps, $steps) {
            var self = this;
            steps.forEach(function (step) {
                var $step = $($(stepTemplate).html());
                $step.find('.heading').html(step.name).attr('data-keyword', step.keyword);
                $step.attr('title', (step.match && step.match.location) ? step.match.location : '');
                if (step.result && step.result.error_message) {
                    $step.find('.message').html(step.result.error_message.replace(/[\r\n]+/g, '<br/>'));
                } else {
                    $step.find('.message').remove();
                }
                $steps.append($step);
                self.injectStepStats(step, $step);
            });
        },
        
        /**
         * Populates a feature's step stats
         * 
         * @param {Object} feature - Cucumber report feature
         * @param {jQuery} $feature - Feature element
         */
        injectFeatureStats: function (feature, $feature) {
            var stats = this.getFeatureStats(feature);
            var $stats = $feature.find('> .stats');
            $stats
                .find('.complete').text(stats.complete).attr('data-value', stats.complete).end()
                .find('.incomplete').text(stats.incomplete).attr('data-value', stats.incomplete).end()
                .find('.failed').text(stats.failed).attr('data-value', stats.failed).end()
            ;
            if (stats.failed) {
                $feature.addClass('failed-scenarios').addClass('failed');
            } else if (stats.incomplete) {
                $feature.addClass('incomplete-scenarios').addClass('incomplete');
            } else {
                $feature.addClass('complete');
            }
        },
        
        /**
         * Populates a scenario's step stats
         * 
         * @param {Object} scenario - Cucumber report scenario
         * @param {jQuery} $scenario - Scenario element
         */
        injectScenarioStats: function (scenario, $scenario) {
            var stats = this.getScenarioStats(scenario);
            var $stats = $scenario.find('> .stats');
            $stats
                .find('.complete').text(stats.complete).attr('data-value', stats.complete).end()
                .find('.incomplete').text(stats.incomplete).attr('data-value', stats.incomplete).end()
                .find('.failed').text(stats.failed).attr('data-value', stats.failed).end()
            ;
            if (stats.failed) {
                $scenario.addClass('failed-steps').addClass('failed');
            } else if (stats.incomplete) {
                $scenario.addClass('incomplete-steps').addClass('incomplete');
            } else {
                $scenario.addClass('complete');
            }
        },

        /**
         * Populates a step's stats flags
         * 
         * @param {Object} step - Cucumber report step
         * @param {jQuery} $step - Step element
         */
        injectStepStats: function (step, $step) {
            var stats = this.getStepStats(step);
            if (stats.failed) {
                $step.addClass('failed');
            } else if (stats.incomplete) {
                $step.addClass('incomplete');
            } else {
                $step.addClass('complete');
            }
        },

        /**
         * Calculates a feature's stats
         * 
         * @param {Object} feature -  Cucumber report feature
         * @returns {Object} Stats
         */
        getFeatureStats: function (feature) {
            var self = this;
            var stats = {
                complete: 0,
                incomplete: 0,
                failed: 0
            };
            feature.elements.forEach(function (scenario) {
                var subStats = self.getScenarioStats(scenario);
                stats.complete += subStats.complete;
                stats.incomplete += subStats.incomplete;
                stats.failed += subStats.failed;
            });
            return stats;
        },
        
        /**
         * Calculates a scenario's stats
         * 
         * @param {Object} scenario -  Cucumber report scenario
         * @returns {Object} Stats
         */
        getScenarioStats: function (scenario) {
            var self = this;
            var stats = {
                complete: 0,
                incomplete: 0,
                failed: 0
            };
            scenario.steps.forEach(function (step) {
                var subStats = self.getStepStats(step);
                stats.complete += subStats.complete;
                stats.incomplete += subStats.incomplete;
                stats.failed += subStats.failed;
            });
            return stats;
        },
        
        /**
         * Calculates a step's stats
         * 
         * @param {Object} step -  Cucumber report step
         * @returns {Object} Stats
         */
        getStepStats: function (step) {
            var stats = {
                complete: 0,
                incomplete: 0,
                failed: 0
            };
            var status = step.result ? step.result.status : ''; // passed|failed|skipped|pending|missing|undefined
            if (status === 'failed') {
                stats.failed++;
            } else if (status === 'passed') {
                stats.complete++;
            } else {
                stats.incomplete++;
            }
            return stats;
        }
                
    };
    
    return lib;
    
});
