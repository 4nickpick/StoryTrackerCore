<?php
class IterateSet
{
	private $index, $data;
	
	function __construct($data)
	{
		$this->index=0;
		
		if(!is_array($data))
			$this->data=array($data);
		else
			$this->data=$data;
	}
	
	function hasNext()
	{
		$this->index++;
		return ($this->index <= count($this->data));
	}
	
	function reset()
	{
		$this->index=0;
	}
	
	function __get($name)
	{
		return $this->data[$this->index]->{$name};
	}
	
	function __set($name, $value)
	{
		$this->data[$this->index]->{$name}=$value;
	}
	
	function __isset($name)
	{
		return isset($this->data[$this->index]->{$name});
	}
	
	function __unset($name)
	{
		unset($this->data[$this->index]->{$name});
	}
	
	function __call($name, $arguments)
	{
		return call_user_func_array(array($this->data[$this->index], $name), $arguments);
	}
}
?>