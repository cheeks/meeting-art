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

					ma.homePage.getPhotos();
					_setupBinds();

				//--> eof private functions

			debug.groupEnd();

		},

		getPhotos : function () {
			debug.log('addStuff');
			$.ajax({
				dataType: 'json',
				url: 'get_photos',
				success: function (data) {
					self.things = data;
					update();
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
		$('.caption').fadeOut(300, function () {
			$('.handle').attr('href', 'http://www.instagram.com/'+self.things[cur].photo_username).text('@' + self.things[cur].photo_username);
			$('.desc').text(self.things[cur].description);
			$(this).fadeIn(300);		
		})
		$('img').fadeOut(300, function() {
			$(this).attr('src', self.things[cur].photo_url).fadeIn(300);
		});
	}

}();
//ma.main.queue(ma.homePage.init);


