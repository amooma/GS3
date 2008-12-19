/*
Gemeinschaft
(c) Amooma GmbH, Soeren Sprenger
GNU GPL
$Revision$
*/

// global vars
var gs_dialog_fade_interval = 10;
var gs_dialog_fade_speed    = 10;
//var gs_dialog_container = 'content';

function calcPageWidth() {
	return window.innerWidth != null
		? window.innerWidth
		: document.documentElement && document.documentElement.clientWidth
			? document.documentElement.clientWidth
			: document.body != null
				? document.body.clientWidth
				: null;
}

function calcPageHeight() {
	return window.innerHeight != null
		? window.innerHeight
		: document.documentElement && document.documentElement.clientHeight
			? document.documentElement.clientHeight
			: document.body != null
				? document.body.clientHeight
				: null;
}

function calcTopPosition() {
	return typeof window.pageYOffset != 'undefined'
		? window.pageYOffset
		: document.documentElement && document.documentElement.scrollTop
			? document.documentElement.scrollTop
			: document.body.scrollTop
				? document.body.scrollTop
				: 0;
}

function calcLeftPosition() {
	return typeof window.pageXOffset != 'undefined'
		? window.pageXOffset
		: document.documentElement && document.documentElement.scrollLeft
			? document.documentElement.scrollLeft
			: document.body.scrollLeft
				? document.body.scrollLeft
				: 0;
}

function showDialog( title, message )
{
	try {
		var dialog;
		var dialogheader;
		var dialogtitle;
		var dialogcontent;
		var dialogmask;
		if (! document.getElementById('dialog')) {
			dialog = document.createElement('div');
			dialog.id = 'dialog';
			dialogheader = document.createElement('div');
			dialogheader.id = 'dialog-header';
			dialogtitle = document.createElement('div');
			dialogtitle.id = 'dialog-title';
			dialogcontent = document.createElement('div');
			dialogcontent.id = 'dialog-content';
			dialogmask = document.createElement('div');
			dialogmask.id = 'dialog-mask';
			document.body.appendChild(dialogmask);
			document.body.appendChild(dialog);
			dialog.appendChild(dialogheader);
			dialogheader.appendChild(dialogtitle);
			dialog.appendChild(dialogcontent);;
		} else {
			dialog = document.getElementById('dialog');
			dialogheader = document.getElementById('dialog-header');
			dialogtitle = document.getElementById('dialog-title');
			dialogcontent = document.getElementById('dialog-content');
			dialogmask = document.getElementById('dialog-mask');
			dialogmask.style.visibility = "visible";
			dialog.style.visibility = "visible";
		}
		
		dialog.style.opacity = .00;
		dialog.style.filter = 'alpha(opacity=0)';
		dialog.alpha = 0;
		var width  = calcPageWidth();
		var height = calcPageHeight();
		var left   = calcLeftPosition();
		var top    = calcTopPosition();
		var dialogwidth  = dialog.offsetWidth;
		var dialogheight = dialog.offsetHeight;
		var topposition  = parseInt( top  + (height / 3) - (dialogheight / 2) );
		var leftposition = parseInt( left + (width  / 2) - (dialogwidth  / 2) );
		dialog.style.top  = topposition  + "px";
		dialog.style.left = leftposition + "px";
		dialogheader.className = "header";
		dialogtitle.innerHTML = title;
		dialogcontent.innerHTML = message;
		//var container = document.getElementById(gs_dialog_container);
		var container = document.body;
		dialogmask.style.height = container.offsetHeight + 'px';
		dialogmask.style.filter = 'alpha(opacity=75)';
		dialog.timer = setInterval("fadeDialog(1)", gs_dialog_fade_interval);
	}
	catch(e){}
}

function hideDialog()
{
	try {
		var dialog = document.getElementById('dialog');
		clearInterval(dialog.timer);
		dialog.timer = setInterval("fadeDialog(0)", gs_dialog_fade_interval);
		var dialogmask = document.getElementById('dialog-mask');
		dialogmask.style.filter = 'alpha(opacity=75)';
	}
	catch(e){}
}

function fadeDialog( flag )
{
	try {
		if (flag == null) flag = 1;
		var dialog = document.getElementById('dialog');
		var value;
		if (flag == 1) {
			value = dialog.alpha + gs_dialog_fade_speed;
		} else {
			value = dialog.alpha - gs_dialog_fade_speed;
		}
		dialog.alpha = value;
		dialog.style.opacity = (value / 100);
		dialog.style.filter = 'alpha(opacity=' + value + ')';
		if (value >= 99) {
			clearInterval(dialog.timer);
			dialog.timer = null;
		} else if (value <= 1) {
			dialog.style.visibility = "hidden";
			document.getElementById('dialog-mask').style.visibility = "hidden";
			clearInterval(dialog.timer);
		}
	}
	catch(e){}
}
