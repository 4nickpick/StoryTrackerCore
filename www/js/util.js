// Version 10.03.10

function gebi(id)
{
	return document.getElementById(id);
}

function addEvent(obj, evt, fn)
{
	if (obj.addEventListener)
		obj.addEventListener(evt, fn, false);
	else if (obj.attachEvent)
		obj.attachEvent('on'+evt, fn);
	else
		obj['on'+evt] = fn;
}

function removeEvent(obj, evt, fn)
{
	if (obj.removeEventListener)
		obj.removeEventListener(evt, fn, false);
	else if (obj.detachEvent)
		obj.detachEvent('on'+evt, fn);
	else
		obj['on'+evt] = null;
}

Function.prototype.partial = function(/* 0..n args */)
{
    var fn = this, args = Array.prototype.slice.call(arguments);
    return function()
    {
        var arg = 0;
        for(var i = 0; i < args.length && arg < arguments.length; i++)
        {
            if(args[i] === undefined)
            args[i] = arguments[arg++];
        }
        return fn.apply(this, args);
    };
}

Function.prototype.bind = function(object)
{
	var fn = this;
	var args = Array.prototype.slice.apply(arguments).slice.apply(arguments, [1]); 
	return function()
	{
		return fn.apply(object, args);
	}; 
}

Function.prototype.defer = function(condition, poll_interval)
{
	var interval;
	
	if(!poll_interval)
		poll_interval=100;
	
	if(typeof(condition) != 'function') // Just wait until the DOM is loaded
	{
		setTimeout(this, 0); // This will make it fire after DOM load. Who knew?
		return;
	}
	
	if(condition())
		this();
	else
	{
		interval=setInterval(function()
		{
			if(condition())
			{
				clearInterval(interval);
				this();
			}
		}.bind(this), poll_interval);
	}
	
	return this;
}

document.getElementsByClassName = function(className)
{
	var classes = className.split(' ');
	var classesToCheck = '';
	var returnElements = [];
	var match, node, elements;
	
	if (document.evaluate)
	{    
		var xhtmlNamespace = 'http://www.w3.org/1999/xhtml';
		var namespaceResolver = (document.documentElement.namespaceURI === xhtmlNamespace)? xhtmlNamespace:null;
		
		for(var j=0, jl=classes.length; j<jl;j+=1)
			classesToCheck += "[contains(concat(' ', @class, ' '), ' " + classes[j] + " ')]"; 
		
		try
		{
			elements = document.evaluate(".//*" + classesToCheck, document, namespaceResolver, 0, null);
		}
		catch(err)
		{
			elements = document.evaluate(".//*" + classesToCheck, document, null, 0, null);
		}

		while((match = elements.iterateNext()))
			returnElements.push(match);
	}
	else
	{
		classesToCheck = [];
		elements = (document.all) ? document.all : document.getElementsByTagName("*");
		
		for (var k=0, kl=classes.length; k<kl; k+=1)
			classesToCheck.push(new RegExp("(^|\\s)" + classes[k] + "(\\s|$)"));
		
		for (var l=0, ll=elements.length; l<ll;l+=1)
		{
			node = elements[l];
			match = false;
			for (var m=0, ml=classesToCheck.length; m<ml; m+=1)
			{
				match = classesToCheck[m].test(node.className);
				if (!match) break;
			}
			if (match) returnElements.push(node);
		}
	}
	return returnElements;
}

function setDisplayByClassName(classname, visible)
{
	var objs = document.getElementsByClassName(classname);
	for(var i=0; i < objs.length; i++)
	{
		objs[i].style.display = (visible?'':'none');	
	}
}

function getAvailWidthHeight()
{
	var w = 0, h = 0;
	if(typeof(window.innerWidth) == 'number') //Non-IE
	{
		w = window.innerWidth;
		h = window.innerHeight;
	}
	else if(window.document.documentElement && (window.document.documentElement.clientWidth || window.document.documentElement.clientHeight)) //IE 6+ in strict mode
	{
		w = window.document.documentElement.clientWidth;
		h = window.document.documentElement.clientHeight;
	}
	else if(window.document.body && (window.document.body.clientWidth || window.document.body.clientHeight)) //IE 4 compatible
	{
		w = window.document.body.clientWidth;
		h = window.document.body.clientHeight;
	}
	
	return {
		'w': parseInt(w),
		'h': parseInt(h)
	};
}

function setCookie(cookieName, value, expireminutes)
{
	var expDate=new Date();
	expDate.setMinutes(expDate.getMinutes()+expireminutes);
	document.cookie=cookieName+'='+escape(value)+';'+((expireminutes==null) ? '' : 'expires='+expDate.toGMTString()+';')+'path=/;';
}

function deleteCookie(cookieName)
{
    document.cookie = cookieName + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/;';
}

function getCookie(cookieName, defaultVal)
{
	if(document.cookie.length > 0)
	{
		start=document.cookie.indexOf(cookieName+'=');
		if(start != -1)
		{
			start+=(cookieName.length+1); 
			end=document.cookie.indexOf(";",start);
			if(end == -1)
				end=document.cookie.length;
			return unescape(document.cookie.substring(start, end));
		}
	}
	return defaultVal;
}

function getTopLeft(obj) 
{
	var curleft = curtop = 0;
	if (obj.offsetParent) 
	{
		do 
		{
			curleft += obj.offsetLeft;
			curtop += obj.offsetTop;
		} while (obj = obj.offsetParent);
	}
	return {'left':curleft,'top':curtop};
}

function php_date(format, timestamp) {
    // http://kevin.vanzonneveld.net
    // +   original by: Carlos R. L. Rodrigues (http://www.jsfromhell.com)
    // +      parts by: Peter-Paul Koch (http://www.quirksmode.org/js/beat.html)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: MeEtc (http://yass.meetcweb.com)
    // +   improved by: Brad Touesnard
    // +   improved by: Tim Wiel
    // +   improved by: Bryan Elliott
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: David Randall
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +  derived from: gettimeofday
    // +      input by: majak
    // +   bugfixed by: majak
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Alex
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +   improved by: Thomas Beaucourt  (http://www.webapp.fr)
    // +   improved by: JT
    // +   improved by: Theriault
    // %        note 1: Uses global: php_js to store the default timezone
    // *     example 1: date('H:m:s \\m \\i\\s \\m\\o\\n\\t\\h', 1062402400);
    // *     returns 1: '09:09:40 m is month'
    // *     example 2: date('F j, Y, g:i a', 1062462400);
    // *     returns 2: 'September 2, 2003, 2:26 am'
    // *     example 3: date('Y W o', 1062462400);
    // *     returns 3: '2003 36 2003'
    // *     example 4: x = date('Y m d', (new Date()).getTime()/1000); 
    // *     example 4: (x+'').length == 10 // 2009 01 09
    // *     returns 4: true
    // *     example 5: date('W', 1104534000);
    // *     returns 5: '53'
    // *     example 6: date('B t', 1104534000);
    // *     returns 6: '999 31'
    // *     example 7: date('W', 1293750000); // 2010-12-31
    // *     returns 7: '52'
    // *     example 8: date('W', 1293836400); // 2011-01-01
    // *     returns 8: '52'
    // *     example 9: date('W Y-m-d', 1293974054); // 2011-01-02
    // *     returns 9: '52 2011-01-02'
    var that = this,
        jsdate, f, formatChr = /\\?([a-z])/gi, formatChrCb,
        // Keep this here (works, but for code commented-out
        // below for file size reasons)
        //, tal= [],
        _pad = function (n, c) {
            if ((n = n + "").length < c) {
                return new Array((++c) - n.length).join("0") + n;
            } else {
                return n;
            }
        },
        txt_words = ["Sun", "Mon", "Tues", "Wednes", "Thurs", "Fri", "Satur",
        "January", "February", "March", "April", "May", "June", "July",
        "August", "September", "October", "November", "December"],
        txt_ordin = {
            1: "st",
            2: "nd",
            3: "rd",
            21: "st", 
            22: "nd",
            23: "rd",
            31: "st"
        };
    formatChrCb = function (t, s) {
        return f[t] ? f[t]() : s;
    };
    f = {
    // Day
        d: function () { // Day of month w/leading 0; 01..31
            return _pad(f.j(), 2);
        },
        D: function () { // Shorthand day name; Mon...Sun
            return f.l().slice(0, 3);
        },
        j: function () { // Day of month; 1..31
            return jsdate.getDate();
        },
        l: function () { // Full day name; Monday...Sunday
            return txt_words[f.w()] + 'day';
        },
        N: function () { // ISO-8601 day of week; 1[Mon]..7[Sun]
            return f.w() || 7;
        },
        S: function () { // Ordinal suffix for day of month; st, nd, rd, th
            return txt_ordin[f.j()] || 'th';
        },
        w: function () { // Day of week; 0[Sun]..6[Sat]
            return jsdate.getDay();
        },
        z: function () { // Day of year; 0..365
            var a = new Date(f.Y(), f.n() - 1, f.j()),
                b = new Date(f.Y(), 0, 1);
            return Math.round((a - b) / 864e5) + 1;
        },

    // Week
        W: function () { // ISO-8601 week number
            var a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3),
                b = new Date(a.getFullYear(), 0, 4);
            return 1 + Math.round((a - b) / 864e5 / 7);
        },

    // Month
        F: function () { // Full month name; January...December
            return txt_words[6 + f.n()];
        },
        m: function () { // Month w/leading 0; 01...12
            return _pad(f.n(), 2);
        },
        M: function () { // Shorthand month name; Jan...Dec
            return f.F().slice(0, 3);
        },
        n: function () { // Month; 1...12
            return jsdate.getMonth() + 1;
        },
        t: function () { // Days in month; 28...31
            return (new Date(f.Y(), f.n(), 0)).getDate();
        },

    // Year
        L: function () { // Is leap year?; 0 or 1
            var y = f.Y(), a = y & 3, b = y % 4e2, c = y % 1e2;
            return 0 + (!a && (c || !b));
        },
        o: function () { // ISO-8601 year
            var n = f.n(), W = f.W(), Y = f.Y();
            return Y + (n === 12 && W < 9 ? -1 : n === 1 && W > 9);
        },
        Y: function () { // Full year; e.g. 1980...2010
            return jsdate.getFullYear();
        },
        y: function () { // Last two digits of year; 00...99
            return (f.Y() + "").slice(-2);
        },

    // Time
        a: function () { // am or pm
            return jsdate.getHours() > 11 ? "pm" : "am";
        },
        A: function () { // AM or PM
            return f.a().toUpperCase();
        },
        B: function () { // Swatch Internet time; 000..999
            var H = jsdate.getUTCHours() * 36e2, // Hours
                i = jsdate.getUTCMinutes() * 60, // Minutes
                s = jsdate.getUTCSeconds(); // Seconds
            return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3);
        },
        g: function () { // 12-Hours; 1..12
            return f.G() % 12 || 12;
        },
        G: function () { // 24-Hours; 0..23
            return jsdate.getHours();
        },
        h: function () { // 12-Hours w/leading 0; 01..12
            return _pad(f.g(), 2);
        },
        H: function () { // 24-Hours w/leading 0; 00..23
            return _pad(f.G(), 2);
        },
        i: function () { // Minutes w/leading 0; 00..59
            return _pad(jsdate.getMinutes(), 2);
        },
        s: function () { // Seconds w/leading 0; 00..59
            return _pad(jsdate.getSeconds(), 2);
        },
        u: function () { // Microseconds; 000000-999000
            return _pad(jsdate.getMilliseconds() * 1000, 6);
        },

    // Timezone
        e: function () { // Timezone identifier; e.g. Atlantic/Azores, ...
// The following works, but requires inclusion of the very large
// timezone_abbreviations_list() function.
/*              var abbr = '', i = 0, os = 0;
            if (that.php_js && that.php_js.default_timezone) {
                return that.php_js.default_timezone;
            }
            if (!tal.length) {
                tal = that.timezone_abbreviations_list();
            }
            for (abbr in tal) {
                for (i = 0; i < tal[abbr].length; i++) {
                    os = -jsdate.getTimezoneOffset() * 60;
                    if (tal[abbr][i].offset === os) {
                        return tal[abbr][i].timezone_id;
                    }
                }
            }
*/
            return 'UTC';
        },
        I: function () { // DST observed?; 0 or 1
            // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
            // If they are not equal, then DST is observed.
            var a = new Date(f.Y(), 0), // Jan 1
                c = Date.UTC(f.Y(), 0), // Jan 1 UTC
                b = new Date(f.Y(), 6), // Jul 1
                d = Date.UTC(f.Y(), 6); // Jul 1 UTC
            return 0 + ((a - c) !== (b - d));
        },
        O: function () { // Difference to GMT in hour format; e.g. +0200
            var a = jsdate.getTimezoneOffset();
            return (a > 0 ? "-" : "+") + _pad(Math.abs(a / 60 * 100), 4);
        },
        P: function () { // Difference to GMT w/colon; e.g. +02:00
            var O = f.O();
            return (O.substr(0, 3) + ":" + O.substr(3, 2));
        },
        T: function () { // Timezone abbreviation; e.g. EST, MDT, ...
// The following works, but requires inclusion of the very
// large timezone_abbreviations_list() function.
/*              var abbr = '', i = 0, os = 0, default = 0;
            if (!tal.length) {
                tal = that.timezone_abbreviations_list();
            }
            if (that.php_js && that.php_js.default_timezone) {
                default = that.php_js.default_timezone;
                for (abbr in tal) {
                    for (i=0; i < tal[abbr].length; i++) {
                        if (tal[abbr][i].timezone_id === default) {
                            return abbr.toUpperCase();
                        }
                    }
                }
            }
            for (abbr in tal) {
                for (i = 0; i < tal[abbr].length; i++) {
                    os = -jsdate.getTimezoneOffset() * 60;
                    if (tal[abbr][i].offset === os) {
                        return abbr.toUpperCase();
                    }
                }
            }
*/
            return 'UTC';
        },
        Z: function () { // Timezone offset in seconds (-43200...50400)
            return -jsdate.getTimezoneOffset() * 60;
        },

    // Full Date/Time
        c: function () { // ISO-8601 date.
            return 'Y-m-d\\Th:i:sP'.replace(formatChr, formatChrCb);
        },
        r: function () { // RFC 2822
            return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb);
        },
        U: function () { // Seconds since UNIX epoch
            return Math.round(jsdate.getTime() / 1000);
        }
    };
    this.date = function (format, timestamp) {
        that = this;
        jsdate = (
            (typeof timestamp === 'undefined') ? new Date() : // Not provided
            (timestamp instanceof Date) ? new Date(timestamp) : // JS Date()
            new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
        );
        return format.replace(formatChr, formatChrCb);
    };
    return this.date(format, timestamp);
}

function seconds_to_time(time)
{
	
	if(parseInt(time)==time)
	{
		value = 
		{
			'years': 0, 
			'days': 0, 
			'hours': 0,
			'minutes': 0, 
			'seconds': 0
		};
		
		if(time >= 31556926)
		{
			value.years = Math.floor(time/31556926);
			time = (time % 31556926);
		}
		if(time >= 86400)
		{
			value.days = Math.floor(time/86400);
			time = (time % 86400);
		}
		if(time >= 3600)
		{
			value.hours = Math.floor(time/3600);
			time = (time % 3600);
		}
		if(time >= 60)
		{
			value.minutes = Math.floor(time/60);
			time = (time % 60);
		}
		
		value.seconds = Math.floor(time);
		//alert(value.hours+':'+value.minutes+':'+value.seconds);
		return value;
	}
	else
		return false;
}

function switch_payment(checkbox, cell1, cell2)
{
	//alert(checkbox.checked);
	if(checkbox.checked == true){
	document.getElementById(cell1).style.visibility='visible';
	document.getElementById(cell1).style.display='table-row';
	}else{
    document.getElementById(cell1).style.visibility='hidden';
    document.getElementById(cell1).style.display='none';
	}
}

function cloneRow(rowToCloneId, tableToAppendId, newCloneId)
{
	var row = document.getElementById(rowToCloneId); // find row to copy
	var table = document.getElementById(tableToAppendId); // find table to append to
	var clone = row.cloneNode(true); // copy children too
	clone.id = newCloneId; // change id or other attributes/contents
	table.appendChild(clone); // add new row to end of table
}

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}

function partial(func /*, 0..n args */) {
    var args = Array.prototype.slice.call(arguments, 1);
    return function() {
        var allArguments = args.concat(Array.prototype.slice.call(arguments));
        return func.apply(this, allArguments);
    };
}

function throb(display_type)
{
    if( !display_type )
        display_type = 'inline';

    var throbber = document.getElementById('throbber');
    if( !!throbber )
    {
        throbber.style.display=display_type;
    }
}

function arrayCompare(a1, a2) {
    if (a1.length != a2.length) return false;
    var length = a2.length;
    for (var i = 0; i < length; i++) {
        if (a1[i] !== a2[i]) return false;
    }
    return true;
}

function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(typeof haystack[i] == 'object') {
            if(arrayCompare(haystack[i], needle)) return true;
        } else {
            if(haystack[i] == needle) return true;
        }
    }
    return false;
}