function jumpToURL(src) {
	jumpToCheckedURL(src, null);
}

function jumpToCheckedURL(src, event) {
	if (event != null) {
		if((event.keyCode||event.which) != 9 ) window.location = src; 
	} else {
		window.location = src;
	}
}
