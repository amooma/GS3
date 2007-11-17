if ((typeof $) != 'function') {
	function $( el )
	{
		if ((typeof el) == 'string')
			return document.getElementById(el);
		return el;
	}
}
if ((typeof Event) != 'object') {
	if (! window.Event) {
		var Event = new Object();
	}
	var Event = {
		observe: function( el, name, cb, useCapture )
		{
			el = $(el);
			if (!el) return;
			useCapture = useCapture || false;
			if (name == 'keypress' &&
			    (/*Prototype.Browser.WebKit ||*/ el.attachEvent))
				name = 'keydown';
			if (el.addEventListener)
				el.addEventListener(name, cb, useCapture);
			else if (el.attachEvent)
				el.attachEvent('on'+name, cb);
		},
		stopObserving: function( el, name, cb, useCapture )
		{
			el = $(el);
			if (!el) return;
			useCapture = useCapture || false;
			if (name == 'keypress' &&
			    (/*Prototype.Browser.WebKit ||*/ el.attachEvent))
				name = 'keydown';
			if (el.removeEventListener)
				el.removeEventListener(name, cb, useCapture);
			else if (el.detachEvent) {
				try{ el.detachEvent('on'+name, cb); } catch(e){}
			}
		},
		el: function( evt )
		{
			if (evt && evt.target)
				return $(evt.target);
			if (! evt) evt = window.event;
			return $(evt.srcElement);
		},
		element: function( evt )
		{
			return Event.el( evt );
		},
		posX: function( evt )
		{
			if (! evt) evt = window.event;
			return evt.pageX || (evt.clientX +
				(document.documentElement.scrollLeft || document.body.scrollLeft));
		},
		pointerX: function( evt )
		{
			return Event.posX( evt );
		},
		posY: function( evt )
		{
			if (! evt) evt = window.event;
			return evt.pageY || (evt.clientY +
				(document.documentElement.scrollTop || document.body.scrollTop));
		},
		pointerY: function( evt )
		{
			return Event.posY( evt );
		}
	}
}


var Tooltip = {
	
	_getElement: function()
	{
		var el = $('tooltip');
		if (! el) {
			el = document.createElement('div');
			el.setAttribute('id', 'tooltip');
			el.style.display = 'none';
			var opacity = 0.92;
			el.style.opacity = opacity;
			el.style.MozOpacity = opacity;
			el.style.KHTMLOpacity = opacity;
			el.style.filter = 'alpha(opacity:'+(opacity*100)+')';
			el.style.position = 'absolute';
			el.style.left = '0px';
			el.style.top  = '0px';
			el.style.width = '200px';
			el.style.padding = '0px';
			//el.style.background = 'transparent url(tooltip.gif)';
			document.getElementsByTagName('body')[0].appendChild(el);
			
			var el1 = document.createElement('div');
			el1.setAttribute('id', 'tooltip-top');
			el.style.margin = '0px';
			el1.style.padding = '30px 8px 0px 8px';
			el1.style.background = 'transparent url(img/tooltip.gif) no-repeat top';
			el.style.color = '#000';
			el.style.textDecoration = 'none';
			el.style.textAlign = 'center';
			el.style.fontSize = '11px';
			el.style.lineHeight = '1.2em';
			el.appendChild(el1);
			
			var el2 = document.createElement('div');
			el2.setAttribute('id', 'tooltip-bottom');
			el.style.margin = '0px';
			el.style.padding = '0px';
			el2.style.height = '15px';
			el2.style.background = 'transparent url(img/tooltip.gif) no-repeat bottom';
			el.appendChild(el2);
		}
		return el;
	},
	show: function( evt, html )
	{
		var tooltip = Tooltip._getElement();
		tooltip.style.left = (Event.pointerX(evt)-30)+'px';
		tooltip.style.top  = (Event.pointerY(evt)+5)+'px';
		tooltip.style.display = 'block';
		
		var tooltipContent = $('tooltip-top');
		tooltipContent.innerHTML = html;
	},
	hide: function()
	{
		var el = Tooltip._getElement();
		el.style.display = 'none';
	}
	
}

function tip( evt, html )
{
	var el = Event.element(evt);
	Event.observe( el, 'mouseout', function(){window.setTimeout('Tooltip.hide()', 200);}, false );
	Tooltip.show( evt, html );
}

