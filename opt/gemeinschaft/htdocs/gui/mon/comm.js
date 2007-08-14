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



/* ------------- misc ------------- */

function $(el) {
	if ((typeof el)=='string') return document.getElementById(el);
	return el;
}

function nop(){}

var Class = {
	create: function(){
		return function(){ this.init.apply(this, arguments); }
	}
}

function extend( dst, src ) {
	if (src) for (var p in src) dst[p]=src[p];
	return dst;
}


/* ------------- function ------------- */

extend(Function.prototype, {
	bind: function() {
		var __method=this,
		args=Array.from(arguments), obj=args.shift();
		return function() {
			return __method.apply(obj, args.concat());
		}
	},
	bindAsEventHandler: function(obj) {
		var __method=this;
		return function(evt) {
			return __method.call(obj, evt||window.event);
		}
	}
});


/* ------------- string ------------- */

extend(String.prototype, {
	trim: function() {
		return this.replace(/^\s+|\s+$/g, '');
	}
});


/* ------------- array ------------- */

var $A = Array.from = function(iterable) {
	if (!iterable) return [];
	if (iterable.toArray) return iterable.toArray();
	var arr=[];
	for (var i=0; i<iterable.length; ++i) arr.push(iterable[i]);
	return arr;
}


/* ------------- event ------------- */

if (!window.Event) var Event={};
extend(Event, {
	_trackListeners: false,
	observe: function(el, eName, fn, capture) {
		if (!(el=$(el))) return;
		if (eName=='keypress' &&
			(navigator.userAgent.match(/KHTML/)
			|| el.attachEvent)) eName='keydown';
		if (this._trackListeners) this._track(el,eName,fn,capture);
		if (el.addEventListener)
			el.addEventListener(eName, fn, capture||false);
		else if (el.attachEvent)
			el.attachEvent('on'+ eName, fn);
	},
	stopObserving: function(el, eName, fn, capture) {
		if (!(el=$(el))) return;
		if (eName=='keypress' &&
			(navigator.userAgent.match(/KHTML/)
			|| el.detachEvent)) eName='keydown';
		if (el.removeEventListener)
			el.removeEventListener(eName, fn, capture||false);
		else if (el.detachEvent)
			el.detachEvent('on'+ eName, fn);
	},
	_track: function(el, eName, fn, capture) {
		if (!this._listeners) this._listeners = [];
		this._listeners.push([el, eName, fn, capture]);
	},
	_removeAll: function() {
		if (Event._listeners) {
			for (var i=0; i<Event._listeners.length; ++i) {
				Event.stopObserving.apply(this, Event._listeners[i]);
				Event._listeners[i][0] = null;
			}
			Event._listeners = false;
		}
	}
});
// prevent memory leaks in IE:
if (navigator.userAgent.indexOf('IE')!=-1) {
	Event._trackListeners = true;
	Event.observe(window, 'unload', Event._removeAll, false);
}


/* ------------- ajax ------------- */

var Ajax = {
	getHttpReq: function() {
		try{ return new XMLHttpRequest(); }catch(e){}
		try{ return new ActiveXObject('Msxml2.XMLHTTP'); }catch(e){}
		try{ return new ActiveXObject('Microsoft.XMLHTTP'); }catch(e){}
		return null;
	},
	activeReqCnt: 0
};

var AjaxReq = Class.create();
AjaxReq.prototype = {
	init: function(url, opts) {
		this.x = Ajax.getHttpReq(); if(!this.x)return;
		this.url = url;
		this.setOpts(opts);
		this.msglastpos = 0;
		this.connect_timeout = null;
		this.send();
	},
	setOpts: function(opts) {
		this.opts = {
			method: 'GET',
			async: true,
			connect_timeout: 5,
			params: {},
			msgsep: "\n==\n"
			//,headers: {}
		};
		if(opts) extend(this.opts, opts);
	},
	check_timeout: function() {
		if (this.x.status < 100) {
			(this.opts['onErr'] || nop)(this);
		}
	},
	send: function() {
		//alert(this.el +', '+ this.url +', '+ this.opts);
		//var parms = this.parms || {};
		var params = this.opts.params;
		var q = '';
		
		// prevent caching by adding a random parameter:
		//...
		
		if (this.opts.method=='GET' && q.length>0)
			this.url += (this.url.match(/\?/)?'&':'?') + q;
		
		//try {
			try {
				this.x.open(this.opts.method, this.url, this.opts.async);
			} catch(e) {return;}
			if (this.opts.async) {
				this.x.onreadystatechange = this.onStateChange.bind(this);
				try {
					this.connect_timeout = window.setTimeout(
						this.check_timeout.bind(this), this.opts.connect_timeout*1000 );
				} catch(e){}
				//var me = this;
				//this.x.onreadystatechange = function(){me.onStateChange(me);}
			}
			
			var headers=[];
			if (this.opts.method=='POST')
				headers.push('Content-type',
					'application/x-www-form-urlencoded');
			if (this.x.overrideMimeType) {
				//headers.push('Connection', 'close');
				this.x.overrideMimeType( 'application/x-www-form-urlencoded' );
			}
			if (this.opts.headers)
				headers.push.apply(headers, this.opts.headers);
			for (var i=0; i<headers.length; i+=2)
				this.x.setRequestHeader(headers[i], headers[i+1]);
			
			var body = this.opts.postBody ? this.opts.postBody : q;
			body = '';
			this.x.send(this.opts.method=='POST'? body :null);
		//} catch(e){}
	},
	isOk: function() {
		var s = this.x.status;
		return (s>=200 && s<300) || s==undefined || s==0;
	},
	header: function(name) {
		try {return this.x.getResponseHeader(name);}
		catch(e){return null;}
	},
	onStateChange: function() {
		if (this.x.readyState==3 || this.x.readyState==4) {  // new data available
			try {
				if (this.isOk()) {
					try { window.clearTimeout( this.connect_timeout ); } catch(e){}
					
					(this.opts['onData'] || nop)(this);
					
					var pos = 0;
					while ((pos = this.x.responseText.indexOf(this.opts.msgsep, this.msglastpos)) != -1) {
						(this.opts['onMsg'] || nop)(
							this, this.x.responseText.substring(this.msglastpos, pos) );
						this.msglastpos = pos + this.opts.msgsep.length;
					}
				}
			} catch(e){}
		}
		if (this.x.readyState==4) {  // complete
			//alert( this.x.status );
			try {
				(this.opts['on'+(this.isOk()?'Ok':'Err')]
				|| nop)(this);
				(this.opts['onDone']
				|| nop)(this);
			} catch(e){}
			try { this.x.onreadystatechange = nop; } catch(e){}
			//Ajax.activeReqCnt--;
		}
	}
};

/* -------------------------- */




var AST_EXTSTATE_UNKNOWN  = -1  // no hint for the extension
var AST_EXTSTATE_IDLE     =  0  // registered, idle
var AST_EXTSTATE_INUSE    =  1  // busy
var AST_EXTSTATE_OFFLINE  =  4  // unreachable, not registered
var AST_EXTSTATE_RINGING  =  8  // ringing


//var req_start = 0;

function handle_msg( req, msg )
{
	try {
		eval('var exts=' + msg.trim());
	} catch(e){ document.write(msg.trim()); document.close(); }
	if (!exts || (typeof exts)!='object') return;
	
	var ext;
	var el;
	var newclass = 'e_ukn';
	for (ext in exts) {
		if (!( el = $('e'+ext) )) continue;
		switch (exts[ext]['s']) {
			case AST_EXTSTATE_INUSE  :
				newclass = (exts[ext]['e'] ? 'e_bse' : 'e_bsi'); break;
			case AST_EXTSTATE_IDLE   :  newclass = 'e_idl'; break;
			case AST_EXTSTATE_RINGING:  newclass = 'e_rng'; break;
			case AST_EXTSTATE_OFFLINE:  newclass = 'e_off'; break;
		}
		if (el.className != newclass)
			el.className = newclass;
	}
	
	/*
	var req_dur = (new Date).getTime() - req_start;
	if (req_dur < 1) req_dur = 1; // ms
	var bytes = req.x.responseText.length;
	$('bps').innerHTML = (bytes/req_dur).toFixed(1);
	*/
	try{ $('mon-status').innerHTML = 'Online'; }catch(e){}
}

var areq = null;

function req_msg_stream()
{
	//req_start = (new Date).getTime();
	areq = new AjaxReq( 'proxy.php', {
		'connect_timeout': 5,
		'msgsep': "\n==\n",
		'onMsg' : handle_msg,
		'onOk'  : function(){
			cleanup();
			window.setTimeout('req_msg_stream();', 50);
		},
		'onErr' : function(){
			try{
				$('mon-status').innerHTML = '<span style="background:#f10; color:#fff;">OFFLINE</span>';
			}catch(e){}
			cleanup();
			window.setTimeout('req_msg_stream();', 3000);
		}
	});
}

function cleanup()
{
	// try to avoid memory leaks
	areq.x.onreadystatechange = null;
	areq.x = null;
	delete(areq.x);
	areq = null;
}

Event.observe(window, 'load', function(){
	window.setTimeout('req_msg_stream();', 250);
});
Event.observe(window, 'pageshow', function(){
	window.setTimeout('if (!areq) req_msg_stream();', 500);
});


