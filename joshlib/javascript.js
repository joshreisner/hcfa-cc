function map_marker(latitude, longitude, html, icon) {
	var point = new GLatLng(latitude, longitude);
	var marker = new GMarker(point, icon);
	GEvent.addListener(marker, 'click', function() {
		marker.openInfoWindowHtml(html);
	});
	return marker;
}

function url_query_set(key, value) {
	var query = window.location.search.substring(1);
	var pairs = query.split("&");
	var found = false;
	for (var i = 0; i < pairs.length; i++) {
		var pair = pairs[i].split("=");
		if (pair[0] == key) {
			pairs[i] = pair[0] + "=" + encodeURIComponent(value);
			found = true;
		}
    } 
    if (!found) pairs[i] = key + "=" + encodeURIComponent(value);
    location.href = location.href.replace(query, pairs.join("&"));
}

function showErrors(errors) {
	var error;
	if (errors.length == 0) return true;
	if (errors.length == 1) {
		error = "This form could not go through because " + errors[0] + ".  Please fix this before continuing.";
	} else {
		var numbers = new Array('two','three','four','five','six','seven','eight','nine');
		var errornumber = (errors.length < 10) ? numbers[errors.length-2] : errors.length;
		error = "This form could not go through because of the following " + errornumber + " errors:\n\n";
		for (var i = 0; i < errors.length; i++) {
			error += " - " + errors[i] + "\n";
		}
		error += "\nPlease fix before continuing.";
	}
	
	alert(error);
	return false;
}

function initTinyMCE(cssLocation) {
	tinyMCE.init({
		mode : "textareas",
		theme : "advanced",
		skin : "default",
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,separator,undo,redo,separator,link,unlink,image,|,code",
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "center",
		extended_valid_elements : "a[href|target],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[align|style],p[style|align]",
		content_css : cssLocation + "?" + new Date().getTime(),
		editor_selector : "mceEditor",
		editor_deselector : "mceNoEditor"
	});
}

function cssAdd(object, what) {
	if (!cssCheck(object, what)) {
		object.className += (object.className) ? " " + what : what;
	}
}

function cssCheck(object, what) {
	return new RegExp('\\b' + what + '\\b').test(object.className)
}

function cssRemove(object, what) {
	var str = object.className.match(" " + what) ? " " + what : what;
	object.className = object.className.replace(str, "");
}

function form_checkbox_toggle(which) {
	document.getElementById(which).checked = !document.getElementById(which).checked;
}

function form_field_value_default(which, clear, str) {
	if (clear && (which.value == str)) {
		which.value = "";
	} else if (!clear && (which.value == "")) {
		which.value = str;
	}
}
