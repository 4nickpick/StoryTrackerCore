<?php
class Display
{
	public static function beginJavascript()
	{
		ob_start();
	}
	
	public static function endJavascript()
	{
		echo str_replace('"', '&quot;', preg_replace('/[\r\n\t]+/', ' ', preg_replace('/\/\/.*?\n/', '', ob_get_clean())));
	}
	
	public static function swfPopupHref($swf, $width, $height)
	{
		return htmlentities($swf).'" target="_blank" onclick="AlertSet.clear().add(new AlertSet.Static(\''. htmlentities('<div style="margin:-10px; max-height:9999999px;"><object width="'.intval($width).'" height="'.intval($height).'"><param name="movie" value="'.htmlentities($swf).'"></param><param name="allowFullScreen" value="true"></param><param name="allowScriptAccess" value="always"></param><embed src="'.htmlentities($swf).'" type="application/x-shockwave-flash" allowfullscreen="true" allowScriptAccess="always" width="'.intval($width).'" height="'.intval($height).'"></embed></object></div>') .'\')).show('.intval($width).'); return false;';
	}
	
	public static function elapsedTime($ptime) 
	{
		$etime = time() - $ptime;
		
		if($etime < 1)
			return 'less than 1 second';
		
		$a = array
		(
			12 * 30 * 24 * 60 * 60  =>  'year',
			30 * 24 * 60 * 60       =>  'month',
			24 * 60 * 60            =>  'day',
			60 * 60                 =>  'hour',
			60                      =>  'minute',
			1                       =>  'second'
		);
		
		foreach($a as $secs => $str) 
		{
			$d = $etime / $secs;
			if($d >= 1) 
			{
				$r = round($d);
				return $r .' '. $str . ($r > 1 ? 's' : '');
			}
		}
	}
	
	public static function firstNWords($str, $n, $add_ellipses = true)
	{
		if(preg_match('/(\S+\s*){'.intval($n).'}/', $str, $matches) > 0)
		{
			$newString = trim($matches[0]);
			if($matches[0] != $str && $add_ellipses)
				$newString .= '...';
		}
		else
			$newString = trim($str);
		
		return $newString;
	}

}
?>