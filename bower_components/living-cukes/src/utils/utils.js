/**
 * LivingCukes Utils
 * 
 * @author Benjamin Nowack
 * @param {jQuery} $ jQuery
 */

define([
    'jquery'
], 
function($) {
    
    var lib = {
        
        timeouts: {},

		/**
		 * Defines an event callback
		 * 
		 * @param {string} eventName - Event name, e.g. `window:resized`
		 * @param {function} callback - Callback function
		 * @param {Object} context - Callback context
		 * @returns {module:utils} Utils lib
		 */
        on: function(eventName, callback, context) {
			var self = this;
            $(window).on(eventName, function(event, data) {
				if (!callback) {
					throw 'callback not defined for event "' + eventName + '"';
				} else {
					callback.call(context || self, data.event || event, data);
				}
			});
			return this;
        },
        
		/**
		 * Triggers an event
		 * 
		 * @param {string} eventName - Event name, e.g. `window:resized`
		 * @param {Object} [data={}] - Event data
		 * @returns {module:utils} Utils lib
		 */
        trigger: function(eventName, data) {
            $(window).trigger(eventName, data || {});
			return this;
        },
        
        /**
         * Debounces or delays function execution
         * 
         * @param {number} delay - Delay in msec
         * @param {function} callback - Callback
         * @param {string|null} timeoutId - Thread/Timeout ID for debouncing an event
         * @returns {undefined}
         */
		debounce: function(delay, callback, timeoutId) {
			timeoutId = timeoutId || Math.random();
			if (this.timeouts[timeoutId]) {
				clearTimeout(this.timeouts[timeoutId]);
			}
			this.timeouts[timeoutId] = setTimeout(callback, delay || 1);
		},
        
        /**
         * Throttles function execution
         * 
         * @param {number} delay - Release-delay in msec
         * @param {function} callback - Callback
         * @param {string|null} callbackId - Function identifier for throttling the callback
         */
		throttle: function(delay, callback, callbackId) {
			var self = this;
			if (callbackId) {
				var eventId = 1 + Math.random();
				if (this.timeouts[callbackId]) {// still blocked
					this.timeouts[callbackId] = eventId;// set event id to indicate there was a blocked call
				} else {
					this.timeouts[callbackId] = eventId;
					callback();
					// release throttle
					setTimeout(function() {
						// call callback another time if there were blocked calls after this one, e.g. to make sure the last scroll position is applied, etc. 
						if (self.timeouts[callbackId] !== eventId) {
							self.debounce(delay, callback, callbackId);
						}
						self.timeouts[callbackId] = null;
					}, delay);
				}
			} else {// throttle via animation frames
				this.animate(callback);
			}
		},
		
		/**
		 * Executes the callback when the browser's layout engine is ready for a redraw
		 * 
		 * @param {function} callback - Callback function
		 * @param {string} callbackId - (optional) identifier for debouncing the callback
		 */
		animate: function(callback, callbackId) {
			callbackId = callbackId || Math.random();
			if (window.requestAnimationFrame) {
				if (this.timeouts[callbackId]) {
					cancelAnimationFrame(this.timeouts[callbackId]);
				}
				this.timeouts[callbackId] = requestAnimationFrame(callback);
			} else {
				this.debounce(1000 / 60, callback, callbackId);
			}
		},
        
        /**
         * Loads a file from the given url
         * 
         * @param {string} path - File URL
         * @param {function} callback - Callback after successful loading
         */
        load: function(path, callback) {
			$.ajax({
				url: path,
				mimeType: 'text/plain',// prevent "not well-formed" message
				dataType: 'text',
				success: callback,
                error: function(xhr, message, error) {
                    console.log('AJAX error:', path, error.message || message);
                }
            });
        }
                
    };
    
    return lib;
    
});
