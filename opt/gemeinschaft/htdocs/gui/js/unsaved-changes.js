/*
Gemeinschaft
(c) Amooma GmbH, Philipp Kempgen
GNU GPL
$Revision$
*/

function have_unsaved_changes()
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
			switch (input.tagName.toLowerCase())
			{
			case 'input':
				if (input.type) {
					switch (input.type.toLowerCase())
					{
					case 'checkbox':
					case 'radio':
						if (input.checked != input.defaultChecked) {
							return true;
						}
						break;
					case 'text':
					case 'password':
					case 'file':
						if (input.value != input.defaultValue) {
							return true;
						}
					}
				}
				break;
			case 'textarea':
				if (input.value != input.defaultValue) {
					return true;
				}
				break;
			case 'select':
				if (input.options) {
					for (var k=0; k<input.options.length; ++k) {
						if (input.options[k].selected != input.options[k].defaultSelected) {
							return true;
						}
					}
				}
				break;
			}
		}
	}
	}
	} catch(e){}
	return false;
}
