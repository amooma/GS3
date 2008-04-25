/*
Gemeinschaft
(c) Amooma GmbH, Philipp Kempgen
GNU GPL
$Revision$
*/

function gs_higlight_submit_button( form_el )
{
	try {
		if (form_el && form_el.elements) {
			var input_els = form_el.elements;
			for (var j=0; j<input_els.length; ++j) {
				var input = input_els[j];
				if (input.type && input.type.toLowerCase() == 'submit') {
					input.style.MozOutline = '3px solid red';
					input.style.MozOutlineRadius = '14px';
					input.style.MozOutlineOffset = '0px';
					input.style.outline = '3px solid red';
					input.style.outlineRadius = '14px';
					input.style.outlineOffset = '0px';
				}
			}
		}
	}
	catch(e){}
}

function gs_have_unsaved_changes( higlight_submit_button )
{
	try {
	if (document.forms) {
	for (var i=0; i<document.forms.length; ++i) {
		var form = document.forms[i];
		var input_els = null;
		if (form.elements) {
			input_els = form.elements;
		} else {
			input_els = form;
		}
		for (var j=0; j<input_els.length; ++j) {
			var input = input_els[j];
			//switch (input.tagName.toLowerCase())
			//{
			//case 'input':
				if (input.type) {
					switch (input.type.toLowerCase())
					{
					case 'checkbox':
					case 'radio':
						if (input.checked != input.defaultChecked) {
							if (higlight_submit_button)
								gs_higlight_submit_button(form);
							return true;
						}
						break;
					case 'text':
					case 'textarea':
					case 'password':
					case 'file':
						if (input.value != input.defaultValue) {
							if (higlight_submit_button)
								gs_higlight_submit_button(form);
							return true;
						}
						break;
					case 'select-one':
					case 'select-multiple':
						for (var k=0; k<input.options.length; ++k) {
							if (input.options[k].selected != input.options[k].defaultSelected) {
								if (higlight_submit_button)
									gs_higlight_submit_button(form);
								return true;
							}
						}
						break;
					}
				}
			//	break;
			//case 'textarea':
			//	if (input.value != input.defaultValue) {
			//		return true;
			//	}
			//	break;
			//case 'select':
			//	if (input.options) {
			//		for (var k=0; k<input.options.length; ++k) {
			//			if (input.options[k].selected != input.options[k].defaultSelected) {
			//				return true;
			//			}
			//		}
			//	}
			//	break;
			//}
		}
	}
	}
	} catch(e){}
	return false;
}

var gs_unsaved_changes_warning = 'You have unsaved changes!';

function gs_check_unsaved_changes( evt )
{
	try {
		if (! evt) {
			if (window.event) evt = window.event;
		}
		if (! gs_is_submit(evt)) {
			if (gs_have_unsaved_changes()) {
				alert(gs_unsaved_changes_warning);
				if (evt) {
					if (evt.stopPropagation) evt.stopPropagation();
					if (evt.preventDefault) evt.preventDefault();
					evt.cancelBubble = true;
				}
				_gs_is_submit = false;
				return false;
			}
		}
	}
	catch(e){}
	return undefined;
}

var _gs_is_submit = false;

function gs_is_submit( evt )
{
	if (_gs_is_submit) {
		return true;
	}
	if (evt
	&&  evt.explicitOriginalTarget
	&&  evt.explicitOriginalTarget.type
	&&  evt.explicitOriginalTarget.type.toLowerCase() == 'submit')
	{
		return true;
	}
	return false;
}

function gs_suppress_unsaved_changes_check( evt )
{
	_gs_is_submit = true;
}

function gs_prevent_unsaved_changes( warning_text )
{
	try {
		if (warning_text) {
			gs_unsaved_changes_warning = warning_text;
		}
		
		if (document.forms) {
			for (var i=0; i<document.forms.length; ++i) {
				var form = document.forms[i];
				if (form.addEventListener) {
					form.addEventListener('submit', gs_suppress_unsaved_changes_check, true);
				} else if (form.attachEvent) {
					form.attachEvent('onsubmit', gs_suppress_unsaved_changes_check);
				} else {
					form['onsubmit'] = gs_suppress_unsaved_changes_check;
				}
			}
		}
		
		if (window.addEventListener) {
			window.addEventListener('beforeunload', gs_check_unsaved_changes, true);
		} else if (window.attachEvent) {
			window.attachEvent('onbeforeunload', gs_check_unsaved_changes);
		} else {
			window['onunload'] = gs_check_unsaved_changes;
		}
	}
	catch(e){}
	//window.onbeforeunload = gs_check_unsaved_changes;
}

