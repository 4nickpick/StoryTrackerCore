/*
Version 09.12.11
Version 10.01.20
	- Fixed vertical align issue
Version 10.02.03
	- Fixed the automatic relinking of class="AlertSet" links to allow the target to be specified without breaking AlertSet
Version 10.03.09
	- Added AlertSet.confirm() method
	- Fixed buttons appearing in the reverse order that they're added
Version 10.04.28
	- Added show-picture.php to the list of image links that will autolink when given a class of AlertSet
	- Added minimum width of 250 if only images and captions are added to the alert
Version 13.08.31
	- removed dynamic CSS, moved to AlertSet.css
*/

var AlertSet =
{
	_ready: false,
	_container: document.createElement('div'),
	_modalOverlay: document.createElement('div'),
	_objects: [],
	_buttons: [],
	_loader: new Image(),
	_closeButton: null,
	_timer: null,
	
	clear: function(clear_buttons, clear_content, clear_alerts)
	{
		var i;
		
		i=0;
		while(i<this._objects.length)
		{
			if((clear_content!==false && (this._objects[i] instanceof AlertSet.Image || this._objects[i] instanceof AlertSet.Static || this._objects[i] instanceof AlertSet.AJAX || this._objects[i] instanceof AlertSet.Iframe || this._objects[i] instanceof AlertSet.Caption)) || (clear_alerts!==false && (this._objects[i] instanceof AlertSet.Alert)))
				this._objects.splice(i, 1);
			else
				i++;
		}
		
		if(clear_buttons!==false)
			this._buttons = [];
		this._emptyContainer();
		
		return this;
	},
	
	_emptyContainer: function()
	{
		while(this._container.childNodes.length > 0)
		{
			this._container.childNodes[0].removeAttribute('id'); // Just in case
			this._container.removeChild(this._container.childNodes[0]);
		}
	},
	
	_appendObject: function(obj)
	{
		this._container.appendChild(obj);
	},
	
	show: function(default_width)
	{
		//cleanup after showInCorner, if necessary
		$('.AlertSetCornerContainer').stop(true).hide().fadeIn('fast');
        this._container.className = 'AlertSetContainer';


        this._modalOverlay.style.display = '';
	
		if( this._timer )
			clearTimeout(this._timer);
	
		var fn;
		fn=function()
		{
			var i, only_images, max_image_width, fn;
			
			this._emptyContainer();
			
			if(!default_width)
				default_width = 630;
			
			only_images=true;
			only_alerts=true;
			max_image_width=64;
			for(i=0; i<this._objects.length; i++)
			{
				this._objects[i].write();
				
				if(this._objects[i] instanceof AlertSet.Image)
				{
					only_alerts=false;
					
					fn=function(img)
					{
						img.replace_loader();
						
						if(img.width > max_image_width)
							max_image_width=img.width;
						if((only_images || max_image_width > default_width) && max_image_width > parseInt(this._container.style.width))
						{
							this._container.style.width=max_image_width+'px';
							this._container.style.top = (this._getAvailWidthHeight().height/2 - this._container.scrollHeight/2)+'px';
							this._container.style.left = (this._getAvailWidthHeight().width/2 - this._container.scrollWidth/2)+'px';
						}
					}.partial(this._objects[i]).bind(this).defer(function(img)
					{
						return (img.width > 0); // Wait until image is loaded
					}.partial(this._objects[i]));
				}
				else if(this._objects[i] instanceof AlertSet.Static || this._objects[i] instanceof AlertSet.AJAX || this._objects[i] instanceof AlertSet.Iframe)
				{
					only_images=false;
					only_alerts=false;
					
					fn=function()
					{
						this._container.style.top = (this._getAvailWidthHeight().height/2 - this._container.scrollHeight/2)+'px';
						this._container.style.left = (this._getAvailWidthHeight().width/2 - this._container.scrollWidth/2)+'px';
					}.bind(this).defer(function(ajax)
					{
						return ajax._ready; // Wait until AJAX is loaded
					}.partial(this._objects[i]), 50);
				}
				else if(this._objects[i] instanceof AlertSet.Caption)
				{
					if(max_image_width < 250)
						max_image_width=250;
				}
				else
					only_images=false;
			}
			if(only_images || max_image_width > default_width)
				this._container.style.width=max_image_width +'px';
			else
				this._container.style.width=default_width+'px';
				
			if(this._buttons.length==0 || (this._buttons.length==1 && this._buttons[0]==this._closeButton))
			{
				if(this._buttons.length==0)
					this._buttons.push(this._closeButton);
				this._modalOverlay.onclick=function()
				{
					AlertSet.hide();
				};
				this._modalOverlay.style.cursor='pointer';
			}
			else
			{
				this._modalOverlay.onclick=null;
				this._modalOverlay.style.cursor='';
			}
			
			// Loop backwards through the array because the buttons are floated right and will appear in the wrong order otherwise
			i=this._buttons.length;
			while(i--)
				this._buttons[i].write();
			
			this._container.style.display = '';
			
			if(only_alerts && this._getAvailWidthHeight().height - this._container.scrollHeight - 200 > 200)
				this._container.style.top = '200px';
			else
				this._container.style.top = (this._getAvailWidthHeight().height/2 - this._container.scrollHeight/2)+'px';
			this._container.style.left = (this._getAvailWidthHeight().width/2 - this._container.scrollWidth/2)+'px';
			this._modalOverlay.style.display = '';
		}.bind(this).defer(function() {return AlertSet._ready;});
	},
	
	
	showInCorner: function(default_width, clear)
	{
        if( clear !== false )
            clear = true;

		this._container.className = 'AlertSetCornerContainer';
		this._container.style = '';
		// no modalOverlay for AlertSetCorner
		this._modalOverlay.style.display = 'none';

		var fn;
		fn=function()
		{
			var i, only_images, max_image_width, fn;
			
			this._emptyContainer();
			
			if(!default_width)
				default_width = 630;
			
			only_images=true;
			only_alerts=true;
			max_image_width=64;
			for(i=0; i<this._objects.length; i++)
			{
				this._objects[i].write();
				
				if(this._objects[i] instanceof AlertSet.Image)
				{
					only_alerts=false;
					
					fn=function(img)
					{
						img.replace_loader();
						
						if(img.width > max_image_width)
							max_image_width=img.width;
						if((only_images || max_image_width > default_width) && max_image_width > parseInt(this._container.style.width))
						{
							// handled by AlertSet.css now
							//this._container.style.width=max_image_width+'px';
							//this._container.style.top = (this._getAvailWidthHeight().height/2 - this._container.scrollHeight/2)+'px';
							//this._container.style.right = (this._getAvailWidthHeight().width/2 - this._container.scrollWidth/2)+'px';
						}
					}.partial(this._objects[i]).bind(this).defer(function(img)
					{
						return (img.width > 0); // Wait until image is loaded
					}.partial(this._objects[i]));
				}
				else if(this._objects[i] instanceof AlertSet.Static || this._objects[i] instanceof AlertSet.AJAX || this._objects[i] instanceof AlertSet.Iframe)
				{
					only_images=false;
					only_alerts=false;
					
					fn=function()
					{
						// handled by AlertSet.css now
						//this._container.style.top = (this._getAvailWidthHeight().height/2 - this._container.scrollHeight/2)+'px';
						//this._container.style.left = (this._getAvailWidthHeight().width/2 - this._container.scrollWidth/2)+'px';
					}.bind(this).defer(function(ajax)
					{
						return ajax._ready; // Wait until AJAX is loaded
					}.partial(this._objects[i]), 50);
				}
				else if(this._objects[i] instanceof AlertSet.Caption)
				{
					if(max_image_width < 250)
						max_image_width=250;
				}
				else
					only_images=false;
			}
			
			
			// handled by AlertSet.css now
			//if(only_images || max_image_width > default_width)
			//	this._container.style.width=max_image_width +'px';
			//else
			//	this._container.style.width=default_width+'px';
				
			if(this._buttons.length==0 || (this._buttons.length==1 && this._buttons[0]==this._closeButton))
			{
				//removed close Button, Alerts now go away on their own
				//if(this._buttons.length==0)
				//	this._buttons.push(this._closeButton);
				
				var hideAlertSet = function()
				{
					AlertSet.hide();
				};
				
				this._container.onclick = hideAlertSet;
				
				//no modal overlay
				//this._modalOverlay.style.cursor='pointer';
				this._container.style.cursor='pointer';
			}
			else
			{
				// no modal overlay
				//this._modalOverlay.onclick=null;
				//this._modalOverlay.style.cursor='';
			}
			
			// Loop backwards through the array because the buttons are floated right and will appear in the wrong order otherwise
			i=this._buttons.length;
			while(i--)
				this._buttons[i].write();
			
			$('.'+this._container.className).fadeIn('fast');
			
			// handled by AlertSet.css now
			//if(only_alerts && this._getAvailWidthHeight().height - this._container.scrollHeight - 200 > 200)
			//	this._container.style.top = '200px';
			//else
			//	this._container.style.top = (this._getAvailWidthHeight().height/2 - this._container.scrollHeight/2)+'px';
			//this._container.style.left = (this._getAvailWidthHeight().width/2 - this._container.scrollWidth/2)+'px';
			
			//this._modalOverlay.style.display = '';
			
			//add a timer to make message hide in a few seconds
			if( this._timer )
				clearTimeout(this._timer);
			this._timer = setTimeout(function(){AlertSet.fade(clear);}, 3500);
		
		}.bind(this).defer(function() {return AlertSet._ready;});
	},
	
	_getAvailWidthHeight: function()
	{
		var w = 0, h = 0, dimens;
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
		return {width: w, height: h};
	},

	hide: function(clear)
	{
		if(clear!==false)
			this.clear();
	
		this._container.style.display = 'none';
		this._modalOverlay.style.display = 'none';
		
		return this;
	},
	
	fade: function(clear)
	{
		var fade_complete = function() {
			if(clear!==false)
				AlertSet.clear();
		}
	
		//fade.init(this._container.id, 1);
		$('.'+this._container.className).fadeOut('slow', fade_complete);
		
		//this._container.style.display = 'none';
		//this._modalOverlay.style.display = 'none';
		
		return this;
	},
	
	add: function(obj)
	{
		if(obj instanceof AlertSet.Button)
		{
			this._buttons.push(obj);
		}
		else
		{
			this._objects.push(obj);
		}
		
		return this;
	},
	
	addJSON: function(json)
	{
		var i;
		
		if(typeof(json)=='string')
		{
			try
			{
				json = JSON.parse(json);
			}
			catch(err)
			{
				AlertSet.add(new AlertSet.Error('JSON Error: '+err.message));	
			}
		}
		
		if(!!json.alerts)
		{
			for(i in json.alerts)
			{
				if(json.alerts[i].length > 0)
				{
					switch(i)
					{
						case 'error':
							AlertSet.add(new AlertSet.Error(json.alerts[i]));
						break;
						case 'warning':
							AlertSet.add(new AlertSet.Warning(json.alerts[i]));
						break;
						case 'validation':
							AlertSet.add(new AlertSet.Validation(json.alerts[i]));
						break;
						case 'info':
							AlertSet.add(new AlertSet.Info(json.alerts[i]));
						break;
						case 'success':
							AlertSet.add(new AlertSet.Success(json.alerts[i]));
						break;
						case 'question':
							AlertSet.add(new AlertSet.Question(json.alerts[i]));
						break;
						case 'debug':
							AlertSet.add(new AlertSet.Debug(json.alerts[i]));
						break;
						case 'mysql_debug':
							AlertSet.add(new AlertSet.MySQLDebug(json.alerts[i]));
						break;
					}
				}
			}
		}
		
		return AlertSet;
	},
	
	// redirectWithAlert is used to pass an alert generated on a page to a new page
	redirectWithAlert: function(url, resp)
	{
		setCookie('resp', JSON.stringify(resp));
		window.location.href = url;
	},
	
	handleRedirectWithAlert: function()
	{
		var resp_cookie = getCookie('resp', false);
		
		if( resp_cookie === false )
			return false;

		AlertSet.clear().addJSON(resp_cookie).showInCorner();
		deleteCookie('resp');
	},
	
	Button: function(text, callback)
	{
		this.text = text;
		this.write = function()
		{
			var div;
			div = document.createElement('div');
			div.className = 'AlertSet_button';
			div.appendChild(document.createTextNode(this.text));
			div.onclick=callback;
			AlertSet._appendObject(div);
		}
	},
	
	Image: function(src)
	{
		var fn;
		
		this.image = new Image();
		this.image.src = src;
		this.width = 0;
		this.height = 0;
		this.div=null;
		
		fn=function()
		{
			this.width=this.image.width;
			this.height=this.image.height;
		}.bind(this).defer(function()
		{
			return (this.image.width > 0);
		}.bind(this));
		
		this.write=function()
		{
			this.div = document.createElement('div');
			this.div.className = 'AlertSet_image';
			this.div.appendChild(AlertSet._loader);
			
			AlertSet._appendObject(this.div);
		}
		
		this.replace_loader=function()
		{
			if(this.div)
			{
				while(this.div.childNodes.length > 0)
					this.div.removeChild(this.div.childNodes[0]);
				this.div.appendChild(this.image);
			}
		}
	},
	
	Static: function(html)
	{
		this.write = function()
		{
			var container_div, div;
			
			container_div = document.createElement('div');
			container_div.className='AlertSet_AJAX';
			div=document.createElement('div');
			div.innerHTML=html;
			
			container_div.appendChild(div);
			AlertSet._appendObject(container_div);
		}
	},
	
	AJAX: function(url)
	{
		this._ready=false;
		
		this.write = function()
		{
			var container_div, div;
			
			container_div = document.createElement('div');
			container_div.className='AlertSet_AJAX';
			div=document.createElement('div');
			
			try
			{	
				var a=new Ajax(true, 'txt');
				
				try
				{
					a.get(url, function()
					{
						if(a.ready())
						{
							if(a.status()==200)
								div.innerHTML=a.response();
							else if(a.status()!=0)
								div.innerHTML = 'Couldn\'t load page: HTTP Error '+ a.status() +'<br /><br />'+ a.response();
							
							this._ready=true;
						}
					}.bind(this));
				}
				catch(err)
				{
					div.innerHTML = 'Couldn\'t load page: AJAX Error ('+ err.name +') - '+ err.message;
				}
			}
			catch(err)
			{
				div.innerHTML = 'AJAX library not loaded.';
			}
			
			container_div.appendChild(div);
			AlertSet._appendObject(container_div);
		}
	},
	
	Iframe: function(url)
	{
		this.write = function()
		{
			var container_div, iframe;
			
			container_div=document.createElement('div');
			container_div.className='AlertSet_iframe';
			
			iframe=document.createElement('iframe');
			iframe.frameBorder='0';
			iframe.src=url;
			
			container_div.appendChild(iframe);
			AlertSet._appendObject(container_div);
		}
	},
	
	Alert: function(msg, type)
	{
		this.message=msg;
		this.write = function()
		{
			var div, inner_div, ul, li, i;
			div = document.createElement('div');
			div.className = 'AlertSet_'+ type;
			if(!!this.message.length && this.message.length == 1)
				this.message=this.message[0];
			
			if(typeof(this.message)=='object')
			{
				{
					ul = document.createElement('ul');
					for(i=0; i<this.message.length; i++)
					{
						li = document.createElement('li');
						li.innerHTML = this.message[i];
						ul.appendChild(li);
					}
					div.appendChild(ul);
				}
			}
			else if(typeof(this.message)=='string')
			{
				inner_div=document.createElement('div');
				inner_div.innerHTML = this.message;
				div.appendChild(inner_div);
			}
			AlertSet._appendObject(div);
		}
	},
	Error: function(msg)
	{
		return(new AlertSet.Alert(msg, 'error'));
	},
	Warning: function(msg)
	{
		return(new AlertSet.Alert(msg, 'warning'));		
	},
	Validation: function(msg)
	{
		return(new AlertSet.Alert(msg, 'validation'));
	},
	Info: function(msg)
	{
		return(new AlertSet.Alert(msg, 'info'));
	},
	Success: function(msg)
	{
		return(new AlertSet.Alert(msg, 'success'));
	},
	Question: function(msg)
	{
		return(new AlertSet.Alert(msg, 'question'));
	},
	Debug: function(msg)
	{
		return(new AlertSet.Alert(msg, 'debug'));
	},
	MySQLDebug: function(msg)
	{
		this.message = msg;
		this.write = function()
		{
			var div, ul, li, i, j, pre;
			div = document.createElement('div');
			div.className = 'AlertSet_mysql_debug';
			if(typeof(this.message)=='object')
			{
				for(i=0; i<this.message.length; i++)
				{
					ul = document.createElement('ul');
					//for(j=0; j<this.message[i].length; j++)
					//{
						li = document.createElement('li');
						li.innerHTML = this.message[i][0];
						ul.appendChild(li);
						li = document.createElement('li');
						pre = document.createElement('code');
						pre.appendChild(document.createTextNode(this.message[i][1]));
						li.appendChild(pre);
						ul.appendChild(li);
					//}
					div.appendChild(ul);
				}
			}
			else if(typeof(this.message)=='string')
				div.innerHTML = this.message;
			else
				alert(typeof(this.message));
			AlertSet._appendObject(div);
		}
	},
	Caption: function (msg)
	{
		// FIXME: Figure out how to fake inheritance in Javascript
		var a=(new AlertSet.Alert(msg, 'caption'));
		this.message=a.message;
		this.write=a.write;
	},
	
	_addEvent: function(obj, evt, fn)
	{
		if (obj.addEventListener)
			obj.addEventListener(evt, fn, false);
		else if (obj.attachEvent)
			obj.attachEvent('on'+evt, fn);
		else
			obj['on'+evt] = fn;
	},
	
	confirm: function(question, ontrue, onfalse)
	{
		this.clear().add(new this.Question(question)).add(new this.Button('Yes', function(){AlertSet.hide(); if(!!ontrue){ontrue();}})).add(new this.Button('No', function(){AlertSet.hide(); if(!!onfalse){onfalse();}})).show();
	}
};

AlertSet._closeButton=new AlertSet.Button('Close', function()
{
	AlertSet.hide();
}),
AlertSet._loader.src='/images/AlertSet/loader.gif';

AlertSet.clear();
AlertSet._container.style.display='none';
//this container's className is set by the show/showInCorner method that calls it
//AlertSet._container.className='AlertSetContainer';

AlertSet._modalOverlay.style.display='none';
AlertSet._modalOverlay.className='AlertSetModalOverlay';

AlertSet._modalOverlay.onmousedown = function() {return false};
AlertSet._modalOverlay.onclick = function() {AlertSet.hide()};

AlertSet._addEvent(window, 'load', function()
{
	document.body.appendChild(AlertSet._modalOverlay);
	document.body.appendChild(AlertSet._container);
	AlertSet._ready=true;
});

(function()
{
	var interval;
	
	interval=setInterval(function()
	{
		var links, i, onclick;
		
		try
		{
			links=document.getElementsByTagName('a');
			for(i=0; i<links.length; i++)
			{
				if(/(^|\s)AlertSet($|\s)/.test(links[i].className))
				{
					onclick=function(href, title)
					{
						AlertSet.clear();
						
						if(/(\.png|\.jpg|\.jpeg|\.gif|showimage.php\?.*?|show-picture.php\?.*?)$/.test(href))
							AlertSet.add(new AlertSet.Image(href));
						else
							AlertSet.add(new AlertSet.AJAX(href));
						
						if(!!title)
							AlertSet.add(new AlertSet.Caption(title));
						
						AlertSet.show();
					}.partial(links[i].href, links[i].title);
					
					links[i].className=links[i].className.replace(/(^|\s)AlertSet($|\s)/, ' ');
					links[i].href='javascript:;';
					links[i].title='';
					links[i].target='';
					links[i].onclick=onclick;
				}
			}
		}
		catch(err) {}
		
		if(AlertSet._ready)
			clearInterval(interval);
	}.bind(this), 50);
})();