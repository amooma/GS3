/* $Revision$ */

function arr_nav( evt )
{
	evt = evt || window.event;  // MSIE compatibility
	if (evt.target.nodeName.toLowerCase() != 'html') return;
	if      (evt.keyCode==37) var id='arr-prev';  // left arr key
	else if (evt.keyCode==39) var id='arr-next';  // right arr key
	else return;
	if (evt.metaKey || evt.altKey || evt.shiftKey || evt.ctrlKey) return;
	var a = document.getElementById(id);
	if (a) {
		this.removeEventListener('keydown', arguments.callee, false);
		evt.preventDefault();
		window.location.href = a.href;
	}
}
window.addEventListener('keydown', arr_nav, false);
