/* $Revision$ */

function gs_sandbox_iframe( el )
{
	if ((typeof el) == 'string')
		el = document.getElementById(el);
	if (! el || ! el.contentWindow) return false;
	
	try{ el.contentWindow.parent  = undefined; }catch(e){}
	try{ el.contentWindow.top     = undefined; }catch(e){}
	try{ el.contentWindow.opener  = undefined; }catch(e){}
	try{ el.contentWindow.frames  = undefined; }catch(e){}
	
	try{ el.contentWindow.history = undefined; }catch(e){}
	try{ el.contentWindow.document.referer = undefined; }catch(e){}
	
	try{ el.contentWindow.open    = function(){return null;};  }catch(e){}
	try{ el.contentWindow.close   = function(){};              }catch(e){}
	try{ el.contentWindow.alert   = function(){};              }catch(e){}
	try{ el.contentWindow.confirm = function(){return false;}; }catch(e){}
	try{ el.contentWindow.prompt  = function(){return null;};  }catch(e){}
	try{ el.contentWindow.print   = function(){};              }catch(e){}
	
	try{ if (el.contentWindow.frameElement      ) {
			 el.contentWindow.frameElement        = undefined;                }}catch(e){}
	try{ if (el.contentWindow.ActiveXObject     ) {
			 el.contentWindow.ActiveXObject       = function(){return null;}; }}catch(e){}
	try{ if (el.contentWindow.captureEvents     ) {
			 el.contentWindow.captureEvents       = function(){};             }}catch(e){}
	try{ if (el.contentWindow.createPopup       ) {
			 el.contentWindow.createPopup         = function(){return null;}; }}catch(e){}
	try{ if (el.contentWindow.showModalDialog   ) {
			 el.contentWindow.showModalDialog     = function(){return null;}; }}catch(e){}
	try{ if (el.contentWindow.showModelessDialog) {
			 el.contentWindow.showModelessDialog  = function(){return null;}; }}catch(e){}
	
	return true;
}
