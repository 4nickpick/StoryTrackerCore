/*
Version 10.01.29
Version 10.02.01
Version 10.02.02
Version 10.02.03
	- Added "Add Another Picture" javascript
Version 10.02.12
	- Fixed picture disappearing when there is only one picture and the up or down arrow is clicked
Version 10.03.31
	- Added the inlineEdit function
Version 10.04.05
	- Added module and verb parameters to the movePicture function
*/

var Tabmin =
{
	_suggest: null,
	_suggestTimeout: null,
	
	sortTab: function(tab, sort, page)
	{
		if(!!sort)
		{
			if(tab.url.search.sort==sort && !!tab.url.search.sort_order && tab.url.search.sort_order=='asc')
				tab.url.search.sort_order='desc';
			else
				tab.url.search.sort_order='asc';
			tab.url.search.sort=sort;
		}
		if(!!page)
			tab.url.search.page=page;
		tab.reload();
	},
	
	addEditTab: function(tabset, json, id, search)
	{
		var tab=new TabSet.Tab(JSON.parse(json));
		
		tab.name+=id;
		for(var i in search)
		{
			if(search.hasOwnProperty(i))
				tab.url.search[i]=search[i];
		}
		
		try
		{
			tabset.addTab(tab);
		}
		catch(ex) {}
		tabset.getTab(tab.name).show();
	},
	
	// mode: true=on, false=off, undefined=toggle
	inlineEdit: function(form, mode)
	{
		var forms, parent, i;
		
		if(form.tagName.toLowerCase()!='form')
		{
			forms=form.getElementsByTagName('form');
			if(forms.length==0)
			{
				parent=form;
				while(parent=parent.parentNode)
				{
					if(parent.tagName.toLowerCase()=='form')
					{
						forms=[parent];
						break;
					}
				}
			}
		}
		else
			forms=[form];
		
		for(i=0; i<forms.length; i++)
		{
			if(forms[i].className.match(/(^|\s)edit_mode(\s|$)/))
			{
				if(mode!==true)
					forms[i].className=forms[i].className.replace(/(^|\s+)edit_mode(\s+|$)/g, ' ')+' display_mode';
			}
			else
			{
				if(mode!==false)
					forms[i].className=forms[i].className.replace(/(^|\s+)display_mode(\s+|$)/g, ' ')+' edit_mode';
			}
		}
	},
	
	appendTooltips: function()
	{
		var action_buttons, i, j, button, span, ie6;
		
		action_buttons=document.getElementsByClassName('action_button');
		
		for(var i=0; i<action_buttons.length; i++)
		{
			button = action_buttons[i];
			if(button.title.length > 0)
			{
				for(j=0; j<button.childNodes.length; j++)
				{
					if(button.childNodes[j].nodeType!=3)
					{
						button.className+=' tool';
						
						span = document.createElement('span');
						span.className='tip';
						span.innerHTML=button.title;
						button.appendChild(span);
						
						ie6=('\v'=='v' && navigator.appVersion.match(/MSIE [56]\./));
						if(ie6)
						{
							button.onmouseover=function(tip)
							{
								tip.className='tip_hover';
							}.partial(span);
							
							button.onmouseout=function(tip)
							{
								tip.className='tip';
							}.partial(span);
						}
						break;
					}
				}
				
				button.title='';
			}
		}
	},
	
	suggest: function(input, module, verb)
	{
		if(!!this._suggestTimeout)
			clearTimeout(this._suggestTimeout);
		
		if(input.value=='')
		{
			if(!!this._suggest)
			{
				this._suggest.parentNode.removeChild(this._suggest);
				this._suggest=null;
			}
		}
		else
		{
			this._suggestTimeout=setTimeout(function()
			{
				var a, json, i, select_box, resp, err, option, options;
				
				this._suggestTimeout = null;
				
				if(!!module)
					url='modules/'+module+'/ajax.php';
				else
					url='suggest_ajax.php';
				if(!verb)
					verb='suggest';
				
				a=new Ajax(true, 'txt');
				a.get('/tabmin/'+url+'?verb='+verb+'&q='+input.value, function()
				{
					if(a.ready())
					{
						if(a.status()==200)
						{
							resp = a.response();
							
							try
							{
								json = JSON.parse(resp);
							}
							catch(err)
							{
								alert('Error parsing JSON: '+resp);
								return;
							}
							if(json['query']!=input.value)
								return;
							
							if(!!this._suggest)
								this._suggest.parentNode.removeChild(this._suggest);
							this._suggest=document.createElement('div');
							this._suggest.className='tabmin_suggest';
							select_box=document.createElement('select');
							select_box.size = 11;//(json.length > 1 ? json.length : 2);
							
							this._suggest.appendChild(select_box);
							input.parentNode.insertBefore(this._suggest, input.nextSibling);
							
							for(i=0; i<json.suggestions.length; i++)
							{
								option = document.createElement('option');
								option.appendChild(document.createTextNode(json.suggestions[i].name));
								option.value='1';
								
								try
								{
									select_box.add(option);
								}
								catch(err)
								{
									select_box.appendChild(option);
								}
							}
							
							for(; i<11; i++)
							{
								option = document.createElement('option');
								if(i==10)
									option.appendChild(document.createTextNode('Cancel'));
								option.value='0';
								
								try
								{
									select_box.add(option);
								}
								catch(err)
								{
									select_box.appendChild(option);
								}
							}
							
							select_box.onchange = function()
							{
								var fields, i;
								
								if(select_box.value == '1')
								{
									fields=json.suggestions[select_box.selectedIndex].fields;
									for(i=0; i<fields.length; i++)
									{
										if(!!(input=select_box.form.elements[fields[i].name]))
										{
											input.value=fields[i].value;
											if(fields[i].disabled===true || fields[i].disabled===false)
												input.disabled=fields[i].disabled;
										}
									}
								}
								
								this._suggest.parentNode.removeChild(this._suggest);
								this._suggest=null;
							}.bind(this);
							
							if(select_box.options.length==0)
							{
								option = document.createElement('option');
								option.appendChild(document.createTextNode('Could not find address'));
								try
								{
									select_box.add(option);
								}
								catch(err)
								{
									select_box.appendChild(option);
								}
							}
						}
						else if(a.status()!=0)
							alert('HTTP Error: '+a.status()+'; '+a.response());
					}
				}.bind(this));
			}.bind(this), 500);
		}
	},
	
	addPicture: function(element, class_name)
	{
		var li;
		
		li = document.getElementsByClassName(class_name)[0];
		li = li.cloneNode(true);
		li.getElementsByTagName('input').item(0).value = '';
		li.getElementsByTagName('textarea').item(0).value = '';
		element.appendChild(li);
		window.scrollBy(0, 200);
	},
	
	movePicture: function(element, steps, module, verb)
	{
		var lis, index, i, sibling, sort_order, a;
		
		lis = element.parentNode.getElementsByTagName('li');
		if(lis.length<=1)
			return;
		
		index=false;
		for(i=0; i<lis.length; i++)
		{
			if(lis[i]==element)
				index=i;
		}
		
		if(index===false)
			return;
		
		sibling = element;
		if(steps>0)
		{
			if(index==lis.length-1)
				sibling = lis[0];
			else if(index==lis.length-2)
				sibling = lis[lis.length-1].nextSibling;
			else
				sibling = lis[(index+2) % lis.length];
		}
		else
		{
			if(index==0)
				sibling=lis[lis.length-1].nextSibling;
			else
				sibling = lis[(index-1) % lis.length];
		}
		element.parentNode.insertBefore(element.parentNode.removeChild(element, true), sibling);
		
		sort_order = [];
		lis = element.parentNode.getElementsByTagName('li');
		for(i=0; i<lis.length; i++)
			sort_order.push(lis[i].getAttribute('data-id'));
		
		a=new Ajax(true, 'txt');
		a.post('/tabmin/modules/'+ module +'/ajax.php','verb='+ verb +'&order='+sort_order.join(','), function()
		{
			if(a.ready())
			{
				if(a.status()==200)
					resp = a.response();
				else if(a.status()!=0)
					alert('HTTP Error: '+a.status()+' -- '+a.response());
			}
		});
	},
	
	Format:
	{
		phone: function(num) 
		{
			num = num.replace(/^[01]|\D/g, "");
			if (num.length > 10) 
			{
				num = num.substr(0, 10);
			}
			if (num.length > 6) 
			{
				num = num.replace(/^(\d{6})/, "$1-");
			}
			if (num.length > 3) 
			{
				num = num.replace(/^(\d{3})/, "$1-");
			}
			return num;
		},
		
		brthdate: function(num) 
		{
			
			if (num.replace(/\D/g, "").length >= 4)
			{
				num = num.replace(/\D/g, "");
				num = num.substr(0, 4);
				num = num.replace(/^(\d{2})(\d{2})/, "$1/$2");
			}
			return num;
		}
	}
};
