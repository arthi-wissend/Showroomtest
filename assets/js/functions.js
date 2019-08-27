/* COOKIE HANDLING */
function createCookie(e, i, t) {
	var a;
	if (t) {
		var o = new Date;
		o.setTime(o.getTime() + 24 * t * 60 * 60 * 1e3), a = "; expires=" + o.toGMTString()
	} else a = "";
	document.cookie = escape(e) + "=" + escape(i) + a + "; path=/"
}

function readCookie(e) {
	for (var i = escape(e) + "=", t = document.cookie.split(";"), a = 0; a < t.length; a++) {
		for (var o = t[a];
			" " === o.charAt(0);) o = o.substring(1, o.length);
		if (0 === o.indexOf(i)) return unescape(o.substring(i.length, o.length))
	}
	return null
}

function deleteCookie(e) {
	document.cookie = e + "=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;"
}

function b64EncodeUnicode(str) {
    return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
        return String.fromCharCode(parseInt(p1, 16))
    }))
}