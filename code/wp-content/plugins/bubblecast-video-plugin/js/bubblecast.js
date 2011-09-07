var opened_playerId = null;
var opened_is_wide;

function bubblecastHideElement(elem) {
    elem.style.display = 'none';
}

function bubblecastShowElement(elem) {
    elem.style.display = 'block';
}

function bubblecastRecreateElement(elem) {
    var parentNode = elem.parentNode;
    var oldInnerHTML = parentNode.innerHTML;
    parentNode.innerHTML = oldInnerHTML;
    var width = parentNode.innerHTML.match(/\bwidth=\"?(\d+)\D/)[1];
    var height = parentNode.innerHTML.match(/\bheight=\"?(\d+)\D/)[1];
    var videoId = parentNode.innerHTML.match(/\bid=\"?quickcast(\d+)_/)[1];
    var videoNum = parentNode.innerHTML.match(/\bid=\"?quickcast\d+_(\d+)\D/)[1];
    var siteId = parentNode.innerHTML.match(/\bsiteId=(\d*)/)[1];
    var languages = parentNode.innerHTML.match(/\blanguages=([^&]*)/)[1];
    code = bubblecastFlashObjectCode(width, height, videoId, videoNum, siteId, languages);
    var open = oldInnerHTML.indexOf('<object');
    if (open < 0) {
	open = oldInnerHTML.indexOf('<OBJECT');
    }
    var close = oldInnerHTML.indexOf('</object>');
    if (close < 0) {
	close = oldInnerHTML.indexOf('</OBJECT>');
    }
    var newInnerHTML = oldInnerHTML.substring(0, open) + code + oldInnerHTML.substring(close + '</object>'.length);
    parentNode.innerHTML = newInnerHTML;
}

function bubblecastChangeFlashesVisibility(visible, idToIgnore) {
	var els = document.getElementsByTagName('object');
	for (var i = 0; i < els.length; i++) {
	    var el = els[i];
        var elToCheck = el;
        var found = false;
        while (elToCheck.parentNode != null && idToIgnore != null) {
            if (elToCheck.id == idToIgnore) {
                found = true;
                break;
            }
            elToCheck = elToCheck.parentNode;
        }
        if (!found || idToIgnore == null) {
            el.style.display = visible ? 'block' : 'none';
        }
	}
}

function bubblecastShowFlashes() {
	bubblecastChangeFlashesVisibility(true, null);
}

function bubblecastHideFlashes(idToIgnore) {
	bubblecastChangeFlashesVisibility(false, idToIgnore);
}

function bubblecastPositionElementAtScreenCenter(elem) {
    var windowWidth;
    var windowHeight;
    if (window.innerWidth) {
        windowWidth = window.innerWidth;
        windowHeight = window.innerHeight;
    } else if (document.documentElement.clientWidth) {
        windowWidth = document.documentElement.clientWidth;
        windowHeight = document.documentElement.clientHeight;
    } else {
        windowWidth = document.body.clientWidth;
        windowHeight = document.body.clientHeight;
    }
    var left = Math.round((windowWidth - elem.offsetWidth) / 2);
    var top = Math.round((windowHeight - elem.offsetHeight) / 2);
    if (left < 0) left = 0;
    if (top < 0) top = 0;
    left += Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
    top += Math.max(document.documentElement.scrollTop, document.body.scrollTop);
    elem.style.left = left + 'px';
    elem.style.top = top + 'px';
}

function bubblecastPositionElementAtPoint(elem, point) {
    var windowWidth;
    var windowHeight;
    if (window.innerWidth) {
        windowWidth = window.innerWidth;
        windowHeight = window.innerHeight;
    } else if (document.documentElement.clientWidth) {
        windowWidth = document.documentElement.clientWidth;
        windowHeight = document.documentElement.clientHeight;
    } else {
        windowWidth = document.body.clientWidth;
        windowHeight = document.body.clientHeight;
    }
    
    var pointX = point.x;
    var pointY = point.y;
    
    var left = pointX - Math.round(elem.offsetWidth / 2);
    var top = pointY - Math.round(elem.offsetHeight / 2);
    if (left + elem.offsetWidth > windowWidth) {
    	left = windowWidth - elem.offsetWidth - 30;
    }
    if (top + elem.offsetHeight > windowHeight) {
    	top = windowHeight - elem.offsetHeight - 30;
    }
    if (left < 0) left = 0;
    if (top < 0) top = 0;
    left += Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
    top += Math.max(document.documentElement.scrollTop, document.body.scrollTop);
    elem.style.left = left + 'px';
    elem.style.top = top + 'px';
}

function showBubblecastComment() {
    var elem = document.getElementById('bubblecast_comment');
    // moving element to the top level in the hierarchy to avoid clipping in
    // some themes
    elem.parentNode.removeChild(elem);
    document.body.appendChild(elem);

    bubblecastHideFlashes('bubblecast_comment');
    bubblecastShowElement(elem);
    bubblecastPositionElementAtScreenCenter(elem);
}

function hideBubblecastComment() {
    var elem = document.getElementById('bubblecast_comment');
    bubblecastHideElement(elem);
    bubblecastShowFlashes();
}

function insertAtCaret(doc, areaId, formId, areaName, text) {
    var txtarea;
    // first trying to find by form, element name
    var form = doc.getElementById(formId);
    if (form && form.tagName == 'FORM') {
        txtarea = form.elements[areaName];
    }
    if (!txtarea) {
	// falling back to ID
        txtarea = doc.getElementById(areaId);
    }
    
    var scrollPos = txtarea.scrollTop;
    var strPos = 0;
    var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? "ff" : (doc.selection ? "ie" : false ) );
    if (br == "ie") {
        txtarea.focus();
        var range = doc.selection.createRange();
        range.moveStart('character', -txtarea.value.length);
        strPos = range.text.length;
    } else if (br == "ff") strPos = txtarea.selectionStart;
    var front = (txtarea.value).substring(0, strPos);
    var back = (txtarea.value).substring(strPos, txtarea.value.length);
    txtarea.value = front + text + back;
    strPos = strPos + text.length;
    if (br == "ie") {
        txtarea.focus();
        var range = doc.selection.createRange();
        range.moveStart('character', -txtarea.value.length);
        range.moveStart('character', strPos);
        range.moveEnd('character', 0);
        range.select();
    } else if (br == "ff") {
        txtarea.selectionStart = strPos;
        txtarea.selectionEnd = strPos;
        txtarea.focus();
    }
    txtarea.scrollTop = scrollPos;
}
function bubblecastShowPlayer(playerId, is_wide, point) {
    if (opened_playerId != null){
        bubblecastHidePlayer(opened_playerId, opened_is_wide);
    }
	
    var telem = document.getElementById('t' + playerId);
    if (!is_wide) {
    	bubblecastHideElement(telem);
    }
    var pelem = document.getElementById('p' + playerId);

    if(is_wide){
        pelem.parentNode.removeChild(pelem);
        document.body.appendChild(pelem);
        bubblecastShowElement(pelem);
        if (point == null) {
        	bubblecastPositionElementAtScreenCenter(pelem);
        } else {
        	bubblecastPositionElementAtPoint(pelem, point);
        }
    }
    else{
        bubblecastShowElement(pelem);
    }
    
    opened_playerId = playerId;
    opened_is_wide = is_wide;
}
function bubblecastHidePlayer(playerId, is_wide){
    var telem = document.getElementById('t' + playerId);
    var pelem = document.getElementById('p' + playerId);
    bubblecastHideElement(pelem);
    if (!is_wide) {
    	bubblecastShowElement(telem);
    }
    opened_pelem = null;
    opened_telem = null;
    opened_playerId = null;
}