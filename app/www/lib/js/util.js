/*! util class
 * Put javascript plugin depedencies below (see main.js for an exmaple)
 * 
 */
var ma = ma || {};
ma.util = function () {
	// =================================================
	// = Private variables (example: var _foo = bar; ) =
	// =================================================

	
	
	// =================================================
	// = public functions                              =
	// =================================================
	var self = {
		// vars

		is_tablet: (/ipad|android|android 3.0|xoom|sch-i800|playbook|tablet|kindle/i.test(navigator.userAgent.toLowerCase())),
		is_iframe: (window.self != window.top)

		// functions		
		
	};
	
	return self;

	
	
	// ================================================
	// = Private functionse (function _private () {}) =
	// ================================================

}();
//ma.main.queue(ma.util.init);


