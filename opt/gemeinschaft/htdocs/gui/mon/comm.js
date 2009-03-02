/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
\*******************************************************************/


var AST_EXTSTATE_UNKNOWN   = -1 ;  // no hint for the extension
var AST_EXTSTATE_IDLE      =  0 ;  // registered, idle
var AST_EXTSTATE_INUSE     =  1 ;  // busy
var AST_EXTSTATE_BUSY      =  2 ;  // busy
var AST_EXTSTATE_OFFLINE   =  4 ;  // unreachable, not registered
var AST_EXTSTATE_RINGING   =  8 ;  // ringing
var AST_EXTSTATE_RINGINUSE =  9 ;  // busy + ringing
var AST_EXTSTATE_ONHOLD    = 16 ;  // hold


function get_transport_iframe()
{
	var iframe = $('transport');
	if (iframe) return iframe;
	
	// IE 5 Mac:
	if (document.frames && document.frames['transport'])
		return document.frames['transport'];
	
	return null;
}

/*
function get_iframe_document_element( iframe )
{
	if (! iframe) return null;
	
	// NS 6, FF:
	if (iframe.contentDocument       ) return iframe.contentDocument;
	
	// IE 5.5, 6:
	if (iframe.contentWindow
	&&  iframe.contentWindow.document) return iframe.contentWindow.document;
	
	// IE 5:
	if (iframe.document              ) return iframe.document;
	
	return null;
}

function get_transport_document_element()
{
	var iframe = get_transport_iframe();
	if (iframe) {
		return get_iframe_document_element( iframe );
	}
	return null;
}
*/

function add_extension( ext )
{
	/*
	try { document.location.reload(); }
	catch(e) { alert('Please reload this page.'); }
	*/
}

function gs_m( exts )
{
	var el = null;
	var newclass = '';
	var classes = '';
	var link = '';
	
	for (ext in exts) {
		try {
			if (!( el = $('e'+ext) )) {
				//add_extension( ext );
				continue;
			}
			if ((typeof el.className) != 'string') continue;
			
			switch (exts[ext]['s']) {
				case AST_EXTSTATE_IDLE     :  newclass = 'e_idl'; break;
				case AST_EXTSTATE_INUSE    :
				case AST_EXTSTATE_BUSY     :
					newclass = (exts[ext]['e'] ? 'e_bse' : 'e_bsi'); break;
				case AST_EXTSTATE_ONHOLD   :  newclass = 'e_hld'; break;
				case AST_EXTSTATE_RINGING  :
				case AST_EXTSTATE_RINGINUSE:  newclass = 'e_rng'; break;
				case AST_EXTSTATE_OFFLINE  :  newclass = 'e_off'; break;
				default                    :  newclass = 'e_ukn';
			}
			
			classes  = el.className.replace(/\be_[a-z]+/g, '').replace(/\s{2,}/g, '');
			classes += ' '+ newclass;
			if (el.className != classes) el.className = classes;
			
			if (!( el = $('e'+ext+'l') )) continue;
			
			if (exts[ext]['l']) {
				if (exts[ext]['s'] === AST_EXTSTATE_ONHOLD) {
					link = 'hold';
				} else {
					link = '&rarr; '+ exts[ext]['l'];
				}
			} else {
				if (exts[ext]['s'] === AST_EXTSTATE_ONHOLD) {
					link = '(hold)';
					// see bug http://bugs.digium.com/view.php?id=10474
				} else {
					link = '';
				}
			}
			el.innerHTML = link;
		}
		catch(e){}
	}
}

var gs_mon_err = 'uninitialized';
var gs_mon_reconnect_after = 1000;

function gs_e( err )
{
	gs_mon_err = err;
	
	var status_html = '';
	
	switch (gs_mon_err) {
		case ''           :
			status_html = '<span class="ok">verbunden</span>';
			gs_mon_reconnect_after = 250;
			break;
		case 'connecting' :
			status_html = '<span class="ok">verbinden ...</span>';
			break;
		case 'daemondown' :
			status_html = '<b class="err">Keine Verbindung!</b>';
			gs_mon_reconnect_after = 9000;
			break;
		default           :
			status_html = '<b class="err">unbekannter Fehler!</b>';
			gs_mon_reconnect_after = 5000;
	}
	try {
		$('mon-status').innerHTML = status_html;
	}
	catch(e){}
}

function req_msg_stream()
{
	// the transport is done like "Comet" but with an iframe and
	// script tags instead for cross-browser compatibility
	// (stupid  MSIE!)
	// disadvantage: a browser will only load one of such streams
	// at a time, so viewing more than one monitor is not possible.
	// and the page seems to be loading for about half a minute
	var iframe = get_transport_iframe();
	if (iframe) {
		iframe.src = 'proxy.php?rand='+ parseInt(Math.random()*99999999);
		return true;
	}
	
	alert('There\'s a problem with your browser.');
	return false;
}


Event.observe( window, 'load', function(){
	var iframe = get_transport_iframe();
	if (iframe) {
		Event.observe( iframe, 'load', function(){
			// our transport iframe is done with loading
			
			if (iframe.contentWindow
			&&  iframe.contentWindow.document
			&&  iframe.contentWindow.document.documentElement
			&&  iframe.contentWindow.document.documentElement.innerHTML
			&&  iframe.contentWindow.document.documentElement.innerHTML.length
			&&  iframe.contentWindow.document.documentElement.innerHTML.length < 250
			) {
				gs_e('unknown');
			}
			
			window.setTimeout('req_msg_stream();', gs_mon_reconnect_after);
		});
	}
	
	gs_e('connecting');
	window.setTimeout('req_msg_stream();', 1500);
});


