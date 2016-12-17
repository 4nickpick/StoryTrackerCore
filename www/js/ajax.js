/* 
Version 09.11.16
Version 10.06.04
	-- Made AIM not break JSON (uses innerText/contentText instead of innerHTML)
Version 13.06.12
	-- Added LoadPieceByURL and LoadPieceFromForm functions to allow replacing chunks of content on the pages
*/
Ajax=function(addrandomnumber, filetype)
{
	this.basedomain='http://'+location.hostname;
	this.filetype=(filetype!=undefined ? filetype : 'txt');
	this.addrandomnumber=(addrandomnumber!=undefined ? addrandomnumber : false);
	
	this.ajaxobj=false;
	if (window.ActiveXObject) // if IE
	{
		try{
			this.ajaxobj=new ActiveXObject("Msxml2.XMLHTTP");
		}catch (err)
		{
			try{
				this.ajaxobj=new ActiveXObject("Microsoft.XMLHTTP");
			}catch (err) {}
		}
	}
	else if (window.XMLHttpRequest) // if Mozilla, Safari etc
	{
		this.ajaxobj=new XMLHttpRequest();
		if (this.ajaxobj.overrideMimeType);
			this.ajaxobj.overrideMimeType(this.filetype=='xml' ? 'text/xml' : 'text/plain');
	}
}

Ajax.prototype.get=function(url, callbackfunc)
{
	if (this.addrandomnumber) // To stop IE caching
		var url=url+(url.indexOf('?')!= -1 ? '&' : '?')+"ts="+new Date().getTime();
	if (this.ajaxobj)
	{
		this.ajaxobj.onreadystatechange=callbackfunc;
		this.ajaxobj.open('GET', url, true);
		this.ajaxobj.send(null);
	}
}

Ajax.prototype.post=function(url, parameters, callbackfunc)
{
	if (this.ajaxobj)
	{
		this.ajaxobj.onreadystatechange = callbackfunc;
		this.ajaxobj.open('POST', url, true);
		this.ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		//this.ajaxobj.setRequestHeader("Content-length", parameters.length);
		//this.ajaxobj.setRequestHeader("Connection", "close");
		this.ajaxobj.send(parameters);
	}
}

Ajax.prototype.request=function(url, parameters, calltype, callbackfunc)
{
	if(calltype.toLowerCase()=='post')
		this.post(url, parameters, callbackfunc);
	else
		this.get(url+'?'+parameters, callbackfunc);
}

Ajax.prototype.ready=function()
{
	return (this.ajaxobj.readyState == 4);
}

Ajax.prototype.status=function()
{
	return (this.ajaxobj.status);
}

Ajax.prototype.header=function(header)
{
	return (this.ajaxobj.getResponseHeader(header));
}

Ajax.prototype.response=function()
{
	return (this.filetype=='txt' ? this.ajaxobj.responseText : this.ajaxobj.responseXml);
}

function handleAjaxForm(form, onsuccess, onfailure)
{
	var useAIM=false, ajax, params, resp;
	for(var i=0; i<form.elements.length; i++)
	{
		if(form.elements[i].type=='file')
			useAIM=true;
	}
	
	if(useAIM)
	{
		return AIM.submit(form,
		{
			onComplete: function(response)
			{
				try
				{
					resp=JSON.parse(response);
				}
				catch(err)
				{
					try
					{
						resp=eval("{" + response + "}");		
					}
					catch(err2)
					{
						try
						{
							if(!!YAHOO)
							{
								resp = YAHOO.lang.JSON.parse(response);
							}
						}
						catch(err3)
						{
							alert('Error parsing JSON: '+response);
						}
					}
				}
				
				if(!!resp)
				{
					if(resp.success)
					{
						if(!!onsuccess)
							onsuccess(resp);
					}
					else
					{
						if(!!onfailure)
							onfailure(resp);
						if(!!resp.msg)
							alert('Error: '+resp.msg);
					}
				}
			}
		});
	}
	else
	{
		ajax=new Ajax(true, 'txt');
		
		params='';
		for(var i=0; i<form.elements.length; i++)
		{
			if(form.elements[i].name != '')
			{
				if(form.elements[i].type=='checkbox' || form.elements[i].type=='radio')
				{
					if(!form.elements[i].checked)
						continue;
				}
				if(form.elements[i].type=='select-multiple')
				{
					for(var j=0; j<form.elements[i].options.length; j++)
					{
						if(form.elements[i].options[j].selected)
							params+=encodeURIComponent(form.elements[i].name)+'='+encodeURIComponent(form.elements[i].options[j].value)+'&';
					}
					continue;
				}
				params += encodeURIComponent(form.elements[i].name)+'='+encodeURIComponent(form.elements[i].value)+'&';
			}
		}
		
		ajax.request(form.action, params, form.method, function()
		{
			if(ajax.ready())
			{
				if(ajax.status()==200)
				{
					try
					{
						resp=JSON.parse(ajax.response());
					}
					catch(err)
					{
						try
						{
							resp=eval("{" + ajax.response() + "}");
						}
						catch(err2)
						{
							try
							{
								if(!!YAHOO)
								{
									resp = YAHOO.lang.JSON.parse(ajax.response());
								}
							}
							catch(err3)
							{
								alert('Error parsing JSON: '+ajax.response());
							}
						}
					}
					
					if(!!resp)
					{
						if(resp.success)
						{
							if(!!onsuccess)
								onsuccess(resp);
						}
						else
						{
							if(!!onfailure)
								onfailure(resp);
						}
					}
				}
				else if(ajax.status() == 403 && !!ajax.header('Location'))
					location.href=ajax.header('Location');
				else if(ajax.status()!=0)
					alert('HTTP error '+ajax.status()+': '+resp);
			}
		});
	}
	
	return false;
}


function handleAjaxFromURL(url, parameters, method, onsuccess, onfailure)
{

	ajax=new Ajax(true, 'txt');	
	
	ajax.request(url, parameters, method, function()
	{
		if(ajax.ready())
		{
			if(ajax.status()==200)
			{
				try
				{
					resp=JSON.parse(ajax.response());
				}
				catch(err)
				{
					try
					{
						resp=eval("{" + ajax.response() + "}");
					}
					catch(err2)
					{
						try
						{
							if(!!YAHOO)
							{
								resp = YAHOO.lang.JSON.parse(ajax.response());
							}
						}
						catch(err3)
						{
							alert('Error parsing JSON: '+ajax.response());
						}
					}
				}
				
				if(!!resp)
				{
					if(resp.success)
					{
						if(!!onsuccess)
							onsuccess(resp);
					}
					else
					{
						if(!!onfailure)
							onfailure(resp);
					}
				}
			}
			else if(ajax.status() == 403 && !!ajax.header('Location'))
				location.href=ajax.header('Location');
			else if(ajax.status()!=0)
				alert('HTTP error '+ajax.status()+': '+resp);
		}
	});
	
	return false;
}


/**
* loadPieceByURL replaces the content of the container with response of the AJAX call to the url with parameters passed to it
**/
function loadPieceByURL(url, params, method, container, callback, throbber)
{
	if(!!throbber)
	{
		throbber.style.display='inline';		
	}
		
	ajax=new Ajax(true, 'txt');

	ajax.request( url, params, method, function()
	{
		if(ajax.ready())
		{
			resp=ajax.response();
			if(ajax.status()==200)
			{			
				if(!!resp)
				{
				
					if(!!container)
					{
						container.innerHTML = resp;	
					}
					if (!!callback)
						callback(resp);
				}
			}
			else if(ajax.status() == 403 && !!ajax.header('Location'))
				location.href=ajax.header('Location');
			else if(ajax.status()!=0)
				alert('HTTP error '+ajax.status()+': '+resp);
				
			if(!!throbber)
				throbber.style.display='none';
		}
	});
}


/**
* loadPieceFromForm replaces the content of the container with response of the AJAX call 
**/
function loadPieceFromForm(form, container, callback, throbber)
{
	var ajax, params, resp;

	ajax=new Ajax(true, 'txt');
	
	params='';
	for(var i=0; i<form.elements.length; i++)
	{
		if(form.elements[i].name != '')
		{
			if(form.elements[i].type=='checkbox' || form.elements[i].type=='radio')
			{
				if(!form.elements[i].checked)
					continue;
			}
			if(form.elements[i].type=='select-multiple')
			{
				for(var j=0; j<form.elements[i].options.length; j++)
				{
					if(form.elements[i].options[j].selected)
						params+=encodeURIComponent(form.elements[i].name)+'='+encodeURIComponent(form.elements[i].options[j].value)+'&';
				}
				continue;
			}
			params += encodeURIComponent(form.elements[i].name)+'='+encodeURIComponent(form.elements[i].value)+'&';
		}
	}
		
	ajax.request(form.action, params, form.method, function()
	{
		if(!!throbber)
			throbber.style.display='inline';
		if(ajax.ready())
		{
			if(ajax.status()==200)
			{
				resp = ajax.response();
								
				if(!!resp)
				{	
					if(!!container)
						container.innerHTML = resp;	
					if (!!callback)
						callback();
				}
			}
			else if(ajax.status() == 403 && !!ajax.header('Location'))
				location.href=ajax.header('Location');
			else if(ajax.status()!=0)
				alert('HTTP error '+ajax.status()+': '+resp);
				
			if(!!throbber)
				throbber.style.display='none';
		}
	});	
	return false;
}

/*
	AIM (AJAX IFrame Method) - Used for file uploads
*/
AIM = {
 
	frame : function(c) {
 
		var n = 'f' + Math.floor(Math.random() * 99999);
		var d = document.createElement('DIV');
		d.innerHTML = '<iframe style="display:none" src="about:blank" id="'+n+'" name="'+n+'" onload="AIM.loaded(\''+n+'\')"></iframe>';
		document.body.appendChild(d);
		
		var i = document.getElementById(n);
		if (c && typeof(c.onComplete) == 'function') {
			i.onComplete = c.onComplete;
		}
		
		return n;
	},
	
	form : function(f, name) {
		f.setAttribute('target', name);
	},
 
	submit : function(f, c) {
		AIM.form(f, AIM.frame(c));
		if (c && typeof(c.onStart) == 'function') {
			return c.onStart();
		} else {
			return true;
		}
	},
	
	loaded : function(id) 
	{
		var i = document.getElementById(id);
		if (i.contentDocument)
			var d = i.contentDocument;
		else if (i.contentWindow)
			var d = i.contentWindow.document;
		else 
			var d = window.frames[id].document;
		
		if (d.location.href == "about:blank")
			return;
		
		if (typeof(i.onComplete) == 'function')
			i.onComplete(!!d.body.textContent ? d.body.textContent : d.body.innerText);
	}
}