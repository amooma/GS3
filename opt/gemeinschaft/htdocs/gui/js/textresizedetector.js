/* $Revision$ */
/* public domain */

var TextResizeDetector = function()
{
	var el = null;
	var interval_delay = 250;
	var interval = null;
	var curr_size = -1;
	var listeners = [];
	
	function _create_control_el()
	{
		el = document.createElement('span');
		el.id = 'text_resize_control';
		el.innerHTML = '&nbsp;';
		el.style.position = 'absolute';
		el.style.top = '0px';
		el.style.left= '0px';
		
		var el_container = TextResizeDetector.target_el;
		if ((typeof el_container) === 'string')
			el_container = document.getElementById( TextResizeDetector.target_el );
		if (el_container)
			el_container.insertBefore( el, el_container.firstChild );
		curr_size = TextResizeDetector.get_size();
	};
	
	function _start_detector()
	{
		if (! interval) {
			interval = window.setInterval('TextResizeDetector.detect()', interval_delay);
		}
	};
	function _stop_detector()
 	{
		window.clearInterval( interval );
		interval = null;
	};
	
	function _detect()
	{
		var new_size = TextResizeDetector.get_size();
		
		if (new_size !== curr_size) {
			var listener = null;
			for (var i=0; i<listeners.length; i++) {
				listener = listeners[i];
				var args = {
					'old': curr_size +'px',
					'new': new_size +'px',
					'diff': ((curr_size!=-1) ? new_size - curr_size + 'px' : '0px')
				};
				curr_size = new_size;
				if (listener['obj'])
					listener['fn'].apply( listener['obj'], ['textsizechanged',[args]] );
				else
				if (listener['fn'])
					listener['fn']( 'textsizechanged', args );
			}
		}
		return curr_size;
	};
	
	function on_avail()
	{
		if (! TextResizeDetector.on_avail_cnt )
			TextResizeDetector.on_avail_cnt = 0;
		
		if (document.getElementById( TextResizeDetector.target_el )) {
			TextResizeDetector.init();
			if (TextResizeDetector.user_init_fn)
				TextResizeDetector.user_init_fn();
			TextResizeDetector.on_avail_cnt = 0;
		}
		else if (TextResizeDetector.on_avail_cnt < 500) {
			TextResizeDetector.on_avail_cnt++;
			window.setTimeout( on_avail, 250 );
		}
	};
	
	window.setTimeout( on_avail, 500 );
	
	return {
		init: function()
		{
			_create_control_el();
			_start_detector();
		},
		
		addEventListener: function( fn, obj )
		{
			listeners[listeners.length] = {
				'fn' : fn,
				'obj': obj
			}
		},
		
		detect: function()
		{
			return _detect();
		},
		
		get_size: function()
		{
			return el.offsetHeight;
		},
		
		startDetector: function()
		{
			_start_detector()
		},
		stop_detector: function()
		{
			_stop_detector();
		}
	}
}();

TextResizeDetector.target_el = document;
TextResizeDetector.user_init_fn = null;
