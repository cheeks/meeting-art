/*! homePage class
 * Put javascript plugin depedencies below (see main.js for an exmaple)
 * 
 */
var ma = ma || {};
ma.homePage = function () {
	// =================================================
	// = Private variables (example: var _foo = bar; ) =
	// =================================================

	var cur = 0;	
	
	// =================================================
	// = public functions                              =
	// =================================================
	var self = {
		things : '',
		init : function () {

			debug.group("# [homePage.js]");

				debug.log('[homePage.js] - initialized'); 

				//--> sof private functions
					ma.homePage.addStuff();
					_setupBinds();

				//--> eof private functions

			debug.groupEnd();

		},

		addStuff : function () {
			debug.log('addStuff');
			$.ajax({
				dataType: 'json',
				url: '/lib/json/dummy.json',
				success: function (data) {
					self.things = data;
				},
				error: function (data) {
					debug.log('error', data);
				}
			});
		}
	};
	
	return self;
	
	// ================================================
	// = Private functionse (function _private () {}) =
	// ================================================
	function _setupBinds () {
		$(document.body)
			.on('click', '#previous', prev)
			.on('click', '#next', next);
	}

	function next (event) {
		event.preventDefault();
		cur = (cur < (self.things.length - 1)) ? (cur + 1) : 0;
		update();
	}

	function prev (event) {
		event.preventDefault();
		cur = (cur > 0) ? (cur - 1) : (self.things.length - 1);
		update();
	}

	function update () {
		$('.handle').attr('href', 'http://www.instagram.com/'+self.things[cur].name).text('@' + self.things[cur].name);
		$('.desc').text(self.things[cur].desc);
		$('img').attr('src', self.things[cur].img).fadeIn(300);
	}

}();
//ma.main.queue(ma.homePage.init);


