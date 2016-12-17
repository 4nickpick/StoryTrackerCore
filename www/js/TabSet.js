/*
Version 10.03.24
	- v2 rewrite
Version 10.04.06
	- Fixed issue with eval() and .bind() in callbacks
*/

/*
Class: TabSet
Mothership for a bunch of little Tab tikes

Parameters:
	Object options - object containing configuration options
*/
function TabSet(options)
{
	var tabSetDiv, tabSetNavDiv, tabSetUL, tabSetContentDivContainer;
	//var ajaxObj, tabSetContentIFrame, fileref, tabSetLISpan;
	
	this._parentID = null;
	this._tabs = {};
	this._tabIndices=[];
	this._previousTab = null;
	this.activeTab = null;
	this._height = '';
	
	if(!!options.parentID)
		this._parentID = options.parentID;
	else if(!!options.tabID && !!options.contentID)
	{
		this._tabID = options.tabID;
		this._contentID = options.contentID;
	}
	
	if(!!options.ontabchange)
		this.ontabchange = options.ontabchange;
	
	if(!!options.oncreatetab)
		this.oncreatetab = options.oncreatetab;
	
	if(!!options.height)
		this._height = options.height;
		
	this._vertical=false;
	if(!!options.vertical)
		this._vertical=true;
	
	this._history=false;
	if(!!options.history)
		this._history=true;
	
	this._theme = 'default';
	if(!!options.theme)
		this._theme = options.theme;
	
	this.setTheme(this._theme);
	
	//create main tabset container
	if(!!this._parentID)
	{
		tabSetDiv = document.createElement('div');
		tabSetDiv.className = 'TabSet';
	}
	
	//create div that holds all the tabs
	tabSetNavDiv = document.createElement('div');
	if(this._vertical)
		tabSetNavDiv.className = 'TabSetNav_vertical';
	else
		tabSetNavDiv.className = 'TabSetNav_horizontal';
	tabSetUL = document.createElement('ul');
	
	//create the main content container that will be the home of all tab content divs
	tabSetContentDivContainer = document.createElement('div');
	if(this._vertical)
		tabSetContentDivContainer.className = 'TabSetContent_vertical';
	else
		tabSetContentDivContainer.className = 'TabSetContent_horizontal';
	
	if(!!this._height && !isNaN(parseInt(this._height)))
		tabSetContentDivContainer.style.height = parseInt(this._height)+'px';
	
	//set private variables so I can reference the nav and the tabset main container
	this._TabSetNav = tabSetNavDiv.appendChild(tabSetUL);
	
	if(!this._tabID)
		tabSetDiv.appendChild(tabSetNavDiv);
	else
		document.getElementById(this._tabID).appendChild(tabSetNavDiv);
	
	if(!this._contentID)
		this._TabSetContentDivContainer = tabSetDiv.appendChild(tabSetContentDivContainer);
	else
		this._TabSetContentDivContainer = document.getElementById(this._contentID).appendChild(tabSetContentDivContainer);
	
	if(!!this._parentID)
		this._TabSet = document.getElementById(this._parentID).appendChild(tabSetDiv);
	
	for(tab in options.tabs)
		this.addTab(new TabSet.Tab(options.tabs[tab]));
	/*
	TODO: History
	if(this._history)
	{
		TabSet._addEvent(window, 'load', function()
		{
			var div;
			
			div=document.createElement('div');
			div.innerHTML='<iframe style="display:none" src="/tabset_history.html?'+ this.activeTab.name +','+ escape(this.activeTab.url) +'" onload="this.onload_handler();"></iframe>';
			document.body.appendChild(div);
			
			this._historyIframe=div.childNodes[0];
			this._historyIframe.onload_handler=function()
			{
				var url, search, num, url;
				
				if(this._historyIframe.contentWindow)
					url=this._historyIframe.contentWindow.location;
				else
					url=this._historyIframe.location;
				
				if(url.toString().indexOf('?'+ this.activeTab.name +','+ escape(this.activeTab.url))==-1)
				{
					search=url.search.replace(/^\?/, '');
					
					if(search.length > 0)
					{
						search=search.split(',');
						name=parseInt(search[0]);
						url=unescape(search[1]);
						this._tabs[name].url=new TabSet.Location(url);
						this._tabs[name].show(false);
					}
				}
			}.bind(this);
		}.bind(this));
	}
	*/
	if(this._tabIndices.length > 0)
	{
		//this._tabs[this._tabIndices[0]].reload();
		this._tabs[this._tabIndices[0]].show(false);
	}
}

/*
Function: addTab
Adds a tab to the TabSet object and adds the necessary HTML nodes and junk to the page.

Parameters:
	TabSet.Tab tab - The tab to be added
*/
TabSet.prototype.addTab=function(tab)
{
	var name, contentDiv;
	
	name=tab.name;
	
	if(!!this._tabs[name])
		throw('A tab with this name already exists.');
	
	tab.parent=this;
	this._tabs[name]=tab;
	this._tabIndices.push(name);
	
	this._TabSetNav.appendChild(tab.li);
	this._TabSetContentDivContainer.appendChild(tab.contentDiv);
	
	if(!!this.oncreatetab)
		this.oncreatetab(this._tabs[name]);
	
	this._tabs[name].reload(false);
	
	return this;
}

/*
Function: getTab
Returns the Tab Object with the given name

Parameters:
	string name - The name of the tab to be returned
*/
TabSet.prototype.getTab=function(name)
{
	if(!!this._tabs[name])
		return this._tabs[name];
	
	return null;
}

TabSet.prototype._showTab = function(tab, callbacks) // TODO: firstLoad, ignoreHistory (?)
{
	var j;
	
	if(tab!=this._tabs[tab.name])
		return this;
	
	if(callbacks!==false)
		callbacks=true;
	
	if(this.activeTab!=tab)
		this._previousTab = this.activeTab;
	
	//if(!ignoreHistory)
	//	ignoreHistory=false;
	
	this.activeTab = tab;
	if(this.activeTab != this._previousTab && this._previousTab!=null && !!this.ontabchange)
	{
		if(typeof(tab.ontabchange)=='function')
			(tab.ontabchange.bind(tab))();
		else
			(new Function(tab.ontabchange).bind(tab))();
	}
	
	if(this._previousTab!=null && !!this._previousTab.onleave && this._previousTab!=tab)
	{
		if(!!this._previousTab.onleave)
			this._previousTab.onleave();
	}
	
	for(j in this._tabs)
	{
		this._tabs[j].a.className='';
		this._tabs[j].contentDiv.className='';
	}
		
	tab.a.className += ' active';
	tab.a.blur();
	tab.li.style.display = '';
	tab.contentDiv.className = 'active';
	
	if(callbacks && !!tab.onshow)
	{
		if(typeof(tab.onshow)=='function')
			(tab.onshow.bind(tab))();
		else
			(new Function(tab.onshow).bind(tab))();
	}
	
	// TODO: History
	//if(this._history && (!ignoreHistory) && (!!this._historyIframe))
	//	this._historyIframe.src=this._historyIframe.src.replace(/\?.*$/, '')+'?'+ i +','+ escape(this._tabs[i].url);
}

TabSet.prototype._closeTab = function(tab, callbacks)
{
	var i, j, tabToShow;
	
	if(callbacks!==false && !!tab.onclose)
	{
		if(typeof(tab.onclose)=='function')
		{
			if((tab.onclose.bind(tab))()===false)
				return this;
		}
		else
		{
			if((new Function(tab.onclose).bind(tab))()===false)
				return this;
		}
	}
	
	this._TabSetNav.removeChild(tab.li);
	this._TabSetContentDivContainer.removeChild(tab.contentDiv);
	delete this._tabs[tab.name];
	for(i=0; i<this._tabIndices.length; i++)
	{
		if(this._tabIndices[i]==tab.name)
		{
			this._tabIndices.splice(i, 1);
			break;
		}
	}
	if(this._previousTab==tab)
		this._previousTab=null;
	
	if(tab == this.activeTab)
	{
		if(!!this._previousTab && !!this._tabs[this._previousTab.name])
			this._previousTab.show();
		else
		{
			for(j=i; j>-this._tabIndices.length; j--)
			{
				//Jarett says, "Don't ask." If you want to make the tabs loop around when you close the left-most tab then uncomment this line 
				//tabToShow=this._tabs[this._tabIndices[this._tabIndices.length - (Math.abs(j-this._tabIndices.length)%this._tabIndices.length)) % this._tabIndices.length]];
				
				//If you want the tabs to move right, like firefox, use the following line
				tabToShow=this._tabs[this._tabIndices[Math.abs(j)]];
				
				if(!!tabToShow)
				{
					tabToShow.show();
					break;
				}
			}
		}
	}
	
	return this;
}

TabSet.prototype.setTheme = function(theme)
{
	if(!!this._themeLinkScreen)
		document.getElementsByTagName('head')[0].removeChild(this._themeLinkScreen);
	if(!!this._themeLinkPrint)
		document.getElementsByTagName('head')[0].removeChild(this._themeLinkPrint);
	
	fileref=document.createElement('link');
	fileref.setAttribute('rel', 'stylesheet');
	fileref.setAttribute('type', 'text/css');
	fileref.setAttribute('href', '/themes/TabSet/'+theme+'/theme.css');
	fileref.setAttribute('media', 'screen');
	this._themeLinkScreen = document.getElementsByTagName('head')[0].appendChild(fileref);
	
	fileref=document.createElement('link');
	fileref.setAttribute('rel', 'stylesheet');
	fileref.setAttribute('type', 'text/css');
	fileref.setAttribute('href', '/themes/TabSet/'+theme+'/print.css');
	fileref.setAttribute('media', 'print');
	this._themeLinkPrint = document.getElementsByTagName('head')[0].appendChild(fileref);
}

/*
Enum: TabSet.Type
Constants to specify the type of content a tab is displaying.
*/
TabSet.Type=
{
	STATIC: {},
	AJAX: {},
	IFRAME: {}
}

/*
Class: TabSet.Tab
Represents a tab and its associated content

Parameters:
	Object options - Possible values:
		String name (required)
		TabSet.Type type (required)
		String title
		String content
		String url
		String icon
		boolean showIcon
 */
TabSet.Tab = function(options)
{
	var tabSetLIA, tabSetSpan, tabSetClose, tabSetIcon;
	
	//if(!!options.name)
	//	throw('name property is required');
	//if(!!options.type)
	//	throw('type property is required');
	
	for(option in options)
	{
		if(options.hasOwnProperty(option))
			this[option]=options[option];
	}
	
	if(typeof(this.type)=='string')
		this.type=eval(this.type);
	
	if(!!options.url)
		this.url=new TabSet.Location(options.url);
	
	this.li = document.createElement('li');
	
	tabSetLIA = document.createElement('a');
	tabSetLIA.href = 'javascript:;';
	
	tabSetSpan = document.createElement('span');
	
	this._throbber=document.createElement('div');
	
	if(!!this.icon || this.type==TabSet.Type.IFRAME || typeof(this.showIcon) == 'undefined' || this.showIcon==true)
	{
		tabSetIcon = document.createElement('img');
		tabSetIcon.className = 'TabSetThrobber';
		tabSetIcon.style.border = 'none';
		tabSetIcon.className = 'iepngfix';
		
		if(typeof(this.showIcon)=='undefined' || this.showIcon==true)
		{
			if(!!this.icon)
				tabSetIcon.src = this.icon;
			else if(this.type==TabSet.Type.IFRAME)
				tabSetIcon.src = this.url.protocol +'//'+ this.url.host +'/favicon.ico';
		}
		
		this.icon = this._throbber.appendChild(tabSetIcon);
	}
	tabSetSpan.appendChild(this._throbber);
	
	TabSet._addEvent(this.li, 'click', function()
	{
		this.show();
		
		if(!!this.onclick)
		{
			if(typeof(this.onclick)=='function')
				(this.onclick.bind(this))();
			else
				(new Function(this.onclick).bind(this))();
		}
	}.bind(this));
	
	tabSetSpan.appendChild(document.createTextNode(options.title));
	tabSetLIA.appendChild(tabSetSpan);
	
	if(!!this.showCloseButton && this.showCloseButton === true)
	{
		tabSetClose = document.createElement('div');
		tabSetClose.className = 'TabSetCloseButton iepngfix';
		
		TabSet._addEvent(tabSetClose, 'click', function()
		{
			this.close();
		}.bind(this));
		
		this.closeButton = tabSetLIA.appendChild(tabSetClose);
	}
	
	this.contentDiv=document.createElement('div');
	if(this.type==TabSet.Type.IFRAME)
	{
		this.iFrame = document.createElement('iframe');
		this.iFrame.style.width = '100%';
		//if(tabset._height!='' && tabset._height!=0)
		//	tabSetContentIFrame.style.height = parseInt(tabset._height)+'px';
		this.iFrame.style.border = 'none';
		this.iFrame.scrolling = 'no';
		this.iFrame.border = '0';
		this.iFrame.frameBorder = '0';
		this.iFrame.onload = function(i)
		{
			if(this.iFrame.src!='')
			{
				if(!!this.oncontentload) 
					this.oncontentload(this); 
				this.hideThrobber()
			}
		}.bind(this);
		this.contentDiv.appendChild(this.iFrame);
	}
	
	this.a = this.li.appendChild(tabSetLIA);
	if(!!this.hidden)
		this.li.style.display = 'none';
}

/*
Function: TabSet.Tab.show
Sets the tab as the active tab

Parameters:
	boolean callbacks - Pass false to skip running callbacks
*/
TabSet.Tab.prototype.show=function(callbacks)
{
	this.parent._showTab(this, callbacks);
}

/*
Function: TabSet.Tab.close
Destroys the tab

Parameters:
	boolean callbacks - Pass false to skip running callbacks
*/
TabSet.Tab.prototype.close=function(callbacks)
{
	this.parent._closeTab(this, callbacks);
}

/*
Function: TabSet.Tab.reload
Reloads the tab content.

Parameters:
	boolean callbacks - Pass false to skip running callbacks
*/
TabSet.Tab.prototype.reload=function(callbacks)
{
	var a, err;
	
	if(callbacks!==false && !!this.onreload)
	{
		if(typeof(tab.onreload)=='function')
		{
			if((tab.onreload.bind(this))()===false)
				return this;
		}
		else
		{
			if((new Function(this.onreload).bind(this))()===false)
				return this;
		}
	}
	
	this.showThrobber();
	
	if(!!this.content)
	{
		this.contentDiv.innerHTML = this.content;
		this.contentDiv.className = 'active';
		if(this.type==TabSet.Type.STATIC)
		{
			this.hideThrobber();
			if(!!this.oncontentload)
				this.oncontentload(this);
		}
	}
	
	if(this.type==TabSet.Type.AJAX)
	{
		if(typeof(Ajax)!='function')
		{
			this.hideThrobber();
			this.contentDiv.innerHTML = 'The Ajax library is not loaded.';
		}
		else
		{
			a=new Ajax(true, 'txt');
			try
			{
				a.get(this.url.toString(), function()
				{
					if(a.ready())
					{
						this.hideThrobber();
						
						if(a.status()==200)
						{
							this.contentDiv.innerHTML=a.response();
							if(!!this.oncontentload) 
							{
								if(typeof(this.oncontentload)=='function')
									this.oncontentload(this);
								else
									eval(this.oncontentload);
							}
						}
						else if(a.status() == 403 && !!a.header('Location'))
							location.href=a.header('Location');
						else if(a.status()!=0)
							this.contentDiv.innerHTML = 'Couldn\'t load page: HTTP Error '+a.status()+'\n\n'+a.response();
					}
				}.bind(this));
			}
			catch(err)
			{
				this.hideThrobber();
				this.contentDiv.innerHTML = 'Couldn\'t load page: AJAX Error ('+err.name+') - '+err.message;
			}
		}
	}
	else if(this.type==TabSet.Type.IFRAME)
		this.iFrame.src = this.url;
	else
		this.hideThrobber();
	
	return this;
}

TabSet.Tab.prototype.showThrobber = function()
{
	this._throbber.className='TabSetThrobber';
}

TabSet.Tab.prototype.hideThrobber = function()
{
	this._throbber.className='TabSetIcon';
}

/*
Class: TabSet.Location
Class for structured URL locations

Parameters:
	String url - A URL that will be ripped apart into little bits.
*/
TabSet.Location=function(url)
{
	var parts, path, i, searchparts;
	
	parts=url.split('://');
	path='';
	if(parts.length==1) // Assume the entire thing is a path without a host
	{
		this.protocol=location.protocol;
		this.host=location.host;
		path=parts[0];
	}
	else
	{
		this.protocol=parts[0]+':';
		parts=parts[1].split('/');
		this.host=parts[0];
		if(parts.length > 1)
			path=parts[1];
	}
	
	this.search={};
	this.search.toString=function()
	{
		var s='';
		for(var i in this)
		{
			if(i!='toString' && this.hasOwnProperty(i))
				s+= (s==''? '' : '&') + encodeURIComponent(i) +'='+ encodeURIComponent(this[i]);
		}
		return '?'+ s;
	};
	
	parts=path.split('?');
	this.pathname=parts[0].replace(/^\//, '');
	if(parts.length>1)
	{
		parts=parts[1].split('&');
		for(i=0; i<parts.length; i++)
		{
			searchparts=parts[i].split('=');
			if(searchparts.length > 1)
				this.search[searchparts[0]]=searchparts[1];
			else
				this.search[searchparts[0]]='';
		}
	}
}

TabSet.Location.prototype.toString=function()
{
	return this.protocol+'//'+this.host+'/'+this.pathname+this.search;
}

/*
---------------------------------------------------------------------------
*/
TabSet._addEvent = function(obj, evt, fn)
{
	if (obj.addEventListener)
		obj.addEventListener(evt, fn, false);
	else if (obj.attachEvent)
		obj.attachEvent('on'+evt, fn);
	else
		obj['on'+evt] = fn;
}
