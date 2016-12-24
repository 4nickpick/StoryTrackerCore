<?php
class Modules
{
	public static function load($config_xml)
	{
		global $currentUser;
		
		$modules = array();
		$i=0;
		foreach($config_xml->module as $module)
		{
			$tabs = array();
			foreach($module->tab as $tab)
			{
				//echo('('.((bool)((string)$tab['login_required'])).')');
				if(((string)$tab['login_required']=='false') || (!empty($currentUser) && $currentUser->tabPermission((string)$module['name'], (string)$tab['name'])))
				{
					$name = (string)$tab['name'];
					$title = (string)$tab['title'];
					$autoreload = false;
					if((string)$tab['autoreload']=='true')
						$autoreload = true;
					$closebutton = false;
					if((string)$tab['closebutton']=='true')
						$closebutton=true;
					$hidden = false;
					if((string)$tab['hidden']=='true')
						$hidden=true;
					$file = (string)$tab->content['file'];
					$icon = (string)ICON_ROOT_TRANSLATED.$tab['icon'];
		
					$tabs[$name] = new Tab(array
					(
						'name'=>$name, 
						'number'=>$i++, 
						'title'=>$title, 
						'autoreload'=>$autoreload, 
						'closebutton'=>$closebutton, 
						'hidden'=>$hidden, 
						'file'=>$file, 
						'icon'=>$icon
					));
				}
			}
			
			$icons=array();
			foreach($module->icon as $icon)
				$icons[(string)$icon['name']]=(string)ICON_ROOT_TRANSLATED.$icon;
				
			$classes=array();		
			foreach($module->class as $class)
				$classes[] = (string)$class['file'];
			
			$path = (string)MODULE_ROOT_TRANSLATED.$module['path'];
			$name = (string)$module['name'];
			//$this->modules[] = new Module($docRoot, $name, $tabs);
			$modules[$name] = new Module(array
			(
				'path'=>$path, 
				'name'=>$name, 
				'tabs'=>$tabs, 
				'icons'=>$icons, 
				'classes'=>$classes
			));
		}
		
		return $modules;
	}
	
	public static function drawTabs($modules, $theme='default', $vertical=false, $history=true)
	{
		$tabArray = array('parentID'=>'tabSet', 'theme'=>$theme, 'history'=>$history, 'vertical'=>$vertical);
		$tabArray['tabs'] = array();

		$html='';
		
		$i=0;
		foreach($modules as $module)
		{
			foreach($module->tabs as $tab)
			{
				$options=array();
				
				$options['title'] = $tab->title;
				$options['showIcon'] = true;
				$options['contentURL'] = $module->path.'/'.$tab->file;
				if(!empty($tab->icon))
					$options['tabIcon'] = $tab->icon;
				$options['oncontentload']='Tabmin.appendTooltips();';
				if($tab->autoreload)
					$options['onshow'] = 'tabSet.tabs['.$tab->number.'].reload();';
				if($tab->closebutton)
					$options['showCloseButton'] = true;
				if($tab->hidden)
					$options['hidden'] = true;
				
				$tabArray['tabs'][]=$options;
			}
		}
		echo
			'<div id="tabSet"></div>
			<script type="text/javascript">
				var tabSet = new TabSet('.json_encode($tabArray).');
			</script>';
	}
}

class Module
{
	public $path, $name, $tabs, $icons, $classes;
	
	function __construct($properties)
	{
		foreach($properties as $property=>$value)
		{
			if(property_exists($this, $property))
				$this->{"$property"}=$value;
		}
	}
	
	function loadClasses()
	{
		foreach($this->classes as $class)
			require_once(CLASS_ROOT.$class);
	}
}

class Tab
{
	public $name, $number, $title, $autoreload, $closebutton, $hidden, $file, $icon;
	function __construct($properties)
	{
		foreach($properties as $property=>$value)
		{
			if(property_exists($this, $property))
				$this->{"$property"}=$value;
		}
	}
}

