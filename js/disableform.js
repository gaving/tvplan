function init(showseeding, enablerss) {  
	if (showseeding) { 
		document.prefs.torrentfile.disabled = false;
		document.prefs.showseeding.disabled = false;
	}
	else {  
		document.prefs.torrentfile.disabled = true;
		document.prefs.showseeding.disabled = true;
	}

	if (enablerss) {
		document.prefs.rssserver.disabled = false;
		document.prefs.rssusername.disabled = false;
		document.prefs.rsspassword.disabled = false;
	}
	else { 
		document.prefs.rssserver.disabled = true;
		document.prefs.rssusername.disabled = true;
		document.prefs.rsspassword.disabled = true;	  
	}
}

function toggle(id) {
	obj = document.getElementById(id);
	obj.disabled = (obj.disabled) ? false : true;
}

function setState(obj_checkbox, obj_checkbox2) {  
	obj_checkbox.checked = (obj_checkbox2.disabled) ? false : true;
}
