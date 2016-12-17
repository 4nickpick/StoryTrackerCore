<?php
ob_start();

class TemplateSet
{
	public static $debug=false;
	
	protected static $current_block_names=array();
	protected static $current_option_names=array();
	protected static $current_option_defaults=array();
	protected static $blocks=array();
	protected static $options=array();
	protected static $displaying=false;
	
	static function begin($block_name, $option_name=null, $option_default=false)
	{
		if(empty($block_name))
			throw new Exception('No block name specified.');
		if(!self::$displaying && $option_name!=null)
			throw new Exception('Calls to begin() in pages are for content only; use select() to select an option.');
		
		if(self::$debug)
			echo '[Starting block: '. $block_name .', '. $option_name .']';
		
		self::$current_block_names[]=$block_name;
		self::$current_option_names[]=$option_name;
		self::$current_option_defaults[]=$option_default;
		ob_start();
	}
	
	static function end()
	{
		if(count(self::$current_block_names) == 0)
			throw new Exception('Call to end() with no matching call to begin().');
		
		$block_name=array_pop(self::$current_block_names);
		$option_name=array_pop(self::$current_option_names);
		$option_default=array_pop(self::$current_option_defaults);
		
		if(self::$displaying)
		{
			if(isset(self::$blocks[$block_name]))
			{
				ob_end_clean();
				echo self::$blocks[$block_name];
			}
			else if(!empty($option_name))
			{
				if(isset(self::$options[$block_name]))
				{
					if($option_name==self::$options[$block_name])
						ob_end_flush();
					else
						ob_end_clean();
				}
				else if($option_default)
					ob_end_flush();
				else
					ob_end_clean();
			}
			else
				ob_end_flush();
		}
		else
			self::$blocks[$block_name]=ob_get_clean();
		
		if(self::$debug)
			echo '[Ending block: '. $block_name .', '. $option_name .']';
	}
	
	static function clear($block_name)
	{
		if(self::$displaying)
			throw new Exception('Call to clear() made in template.');
		if(empty($block_name))
			throw new Exception('No block name specified.');
		
		self::$blocks[$block_name]='';
	}
	
	static function select($block_name, $option_name)
	{
		if(self::$displaying)
			throw new Exception('Call to select() made in template.');
		if(empty($block_name))
			throw new Exception('No block name specified.');
		if(empty($option_name))
			throw new Exception('No option name specified.');
		
		self::$options[$block_name]=$option_name;
	}
	
	static function display($template_file)
	{
		if(count(self::$current_block_names) > 0)
			throw new Exception('Call to begin() without matching call to end().');
		if(!file_exists($template_file))
			throw new Exception('Template file not found.');
		
		if(ob_get_level()>0)
			ob_end_clean(); // This is to catch any content on the page outside of TemplateSet blocks (mostly whitespace) and discard it.
		self::$displaying=true;
		include $template_file;
		self::$displaying=false;
	}
}
?>