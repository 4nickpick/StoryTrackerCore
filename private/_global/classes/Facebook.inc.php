<?php
class Facebook
{
	const OAUTH_TOKEN='59114836597|OyjuLmBRMIZnw_FjkOpYPuWAf0M';
	
	private $id, $cache_dir, $cache_time, $info;
	
	function __construct($id, $cache_dir=null, $cache_time=600)
	{
		$this->id=$id;
		$this->cache_dir=$cache_dir;
		$this->cache_time=$cache_time;
		$this->info=null;
	}
	
	private function getObject($object=null, $limit=null, $offset=null, $until=null, $since=null)
	{
		$url='https://graph.facebook.com/'.urlencode($this->id);
		$query=array('date_format=U', 'access_token='.self::OAUTH_TOKEN);
		if(!empty($object))
		{
			$url.='/'.$object;
			
			if(!empty($limit))
				$query[]='limit='.intval($limit);
			if(!empty($offset))
				$query[]='offset='.intval($offset);
			if(!empty($until))
				$query[]='until='.urlencode($until);
			if(!empty($since))
				$query[]='since='.urlencode($since);
		}
		if(count($query) > 0)
			$url.='?'.implode('&', $query);
		
		return file_get_contents($url);
	}
	
	function getInfo()
	{
		if(empty($this->info))
			$this->info=json_decode($this->getObject());
		return $this->info;
	}
	
	function getPageUrl()
	{
		return 'http://www.facebook.com/'.urlencode($this->id);
	}
	
	function getPhotoUrl($type=null)
	{
		return 'https://graph.facebook.com/'.urlencode($this->id).'/picture'.(!empty($type)? '?type='.urlencode($type) : '');
	}
	
	function getPosts($limit=null, $offset=null, $until=null, $since=null)
	{
		return json_decode($this->getObject('posts', $limit, $offset, $until, $since));
	}
}
?>
