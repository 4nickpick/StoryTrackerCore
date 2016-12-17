<?php
class Tabmin
{
	public static $db = NULL;
	public static function load($config_xml, $user)
	{
		$modules = array();
		foreach($config_xml->module as $module)
		{
			$tabs = array();
			foreach($module->tab as $tab)
			{
				if(((string)$tab['login_required']=='false') || (!empty($user) && $user->tabPermission((string)$module['name'], (string)$tab['name'])) || ($module['name']=='developer' && @$_SESSION['tabmin_developer']))
				{
					$name = (string)$tab['name']; // PHP crashes if you don't assign this to a variable before you put it into the array
					$tabs[$name] = new Tab(array
					(
						'name'=>$name,
						'title'=>(string)$tab['title'],
						'autoreload'=>((string)$tab['autoreload']=='true'),
						'closebutton'=>((string)$tab['closebutton']=='true'),
						'warnonclose'=>((string)$tab['warnonclose']=='true'),
						'hidden'=>((string)$tab['hidden']=='true'),
						'file'=>(string)$tab['file']
					));
				}
			}
			
			if(count($tabs) > 0)
			{
				/*
				$classes=array();		
				foreach($module->class as $class)
					$classes[] = (string)$class['file'];
				*/
				$name=(string)$module['name']; // PHP crashes if you don't assign this to a variable before you put it into the array
				$modules[$name] = new Module(array
				(
					'title'=>(string)$module['title'],
					'name'=>$name,
					'path'=>MODULE_ROOT_TRANSLATED.$name,
					'tabs'=>$tabs,
					'mobile'=>((string)$module['mobile']=='true')/*,
					'classes'=>$classes*/
				));
			}
		}
		
		return $modules;
	}
	
	public static function drawTabs($modules, $theme='default', $history=true, $mobile=false)
	{
		$tab_js='';
		
		$moduleArray=array('parentID'=>'tabmin', 'theme'=>$theme, 'history'=>$history, 'tabs'=>array());
		$i=0;
		foreach($modules as $module)
		{
			if(!$mobile || $module->mobile)
			{
				$moduleArray['tabs'][$i]=array
				(
					'name'=>$module->name,
					'title'=>$module->title,
					'type'=>'TabSet.Type.STATIC',
					'content'=>'<div id="tabmin_'. $module->name .'"></div>',
					'icon'=>$module->getIcon('module'),
					'onshow'=>''
				);

				$tabArray=array('parentID'=>'tabmin_'.$module->name, 'theme'=>$theme, 'history'=>$history, 'vertical'=>(!$mobile), 'tabs'=>array());
				foreach($module->tabs as $tab)
				{
					if(!$tab->hidden)
						$tabArray['tabs'][]=$tab->toArray($module);
					if($tab->autoreload)
						$moduleArray['tabs'][$i]['onshow'] .= 'tabset_'. $module .'.getTab(\''. $tab .'\').reload();';
				}

				$tab_js.='var tabset_'. $module->name .' = new TabSet('. json_encode($tabArray) .');'."\n";
			}
			$i++;
		}
		echo
			'<style type="text/css">
				/*body, html {
					height: 100%;
					margin: 0;
					padding: 0;
					overflow: hidden;
				}*/
			</style>
			<div id="tabmin"></div>
			<script type="text/javascript">
				var tabset = new TabSet('. json_encode($moduleArray) .');
				'. $tab_js .'
				/*addEvent(window, \'resize\', function()
				{
					tabset._TabSetContentDivContainer.style.height = (getAvailWidthHeight().h-getTopLeft(tabset._TabSetContentDivContainer).top-30)+\'px\';
				});*/
			</script>';
	}
}

class Module
{
	public $path, $name, $title, $tabs, $mobile;
	
	function __construct($properties)
	{
		foreach($properties as $property=>$value)
		{
			if(property_exists($this, $property))
				$this->{"$property"}=$value;
		}
	}
	
	function getIcon($name)
	{
		return $this->path.'/icons/'.$name.'.png';
	}
	
	function __toString()
	{
		return $this->name;
	}
}

class Tab
{
	public $name, $title, $autoreload, $closebutton, $warnonclose, $hidden, $file;
	function __construct($properties)
	{
		foreach($properties as $property=>$value)
		{
			if(property_exists($this, $property))
				$this->{"$property"}=$value;
		}
	}
	
	function toArray($module)
	{
		$options=array
		(
			'name'=>$this->name,
			'title'=>$this->title,
			'type'=>'TabSet.Type.AJAX',
			'url'=>$module->path.'/'.$this->file,
			'icon'=>$module->getIcon($this->name),
			'showCloseButton'=>$this->closebutton,
			'hidden'=>$this->hidden,
			'oncontentload'=>'if(!!Tabmin.onTabLoad) {Tabmin.onTabLoad();} else {Tabmin.appendTooltips();}'
		);
		
		if($this->autoreload)
			$options['onshow'] = 'tabset_'. $module .'.getTab(\''. $this .'\').reload();';
		
		if($this->warnonclose)
		{
			$options['onclose'] = 'AlertSet.confirm(\'Are you sure you want to close this form? Any changes you have made will be lost.\', function(){this.close(false);}.bind(this)); return false;';
			$options['onreload'] = 'AlertSet.confirm(\'Are you sure you want to close this form? Any changes you have made will be lost.\', function(){this.reload(false);'. (isset($module->tabs['view'])? 'this.parent.getTab(\''. $module->tabs['view'] .'\').show();' : '') .'}.bind(this)); return false;';
		}
		
		return $options;
	}
	
	function __toString()
	{
		return $this->name;
	}
}
?>
