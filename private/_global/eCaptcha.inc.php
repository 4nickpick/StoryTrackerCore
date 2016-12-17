<?php
class eCaptcha
{
	private static $width = 80;
	private static $height = 22;
	private static $dotCoeff = 0.2;
	private static $bgColor = array('red'=>250, 'green'=>250, 'blue'=>250);
	private static $bgColorRandomizationFactor = 10;
	
	private static $textColor = array('red'=>233, 'green'=>14, 'blue'=>91);
	private static $textColorRandomizationFactor = 50;
	
	private static $textAngleRandomizationFactor = 10;

	public static function setWidth($w)
	{
		self::$width = $w;
	}
	public static function setHeight($h)
	{
		self::$height = $h;
	}
	
	public static function setDotFrequency($df)
	{
		self::$dotCoeff = $df;
	}
	
	public static function setBgColor($red, $green, $blue)
	{
		self::$bgColor = array('red'=>$red, 'green'=>$green, 'blue'=>$blue);
	}
	
	public static function setTextColor($red, $green, $blue)
	{
		self::$textColor = array('red'=>$red, 'green'=>$green, 'blue'=>$blue);
	}
	
	public static function setBgColorRandomizationFactor($factor)
	{
		self::$bgColorRandomizationFactor = $factor;
	}
	
	public static function setTextColorRandomizationFactor($factor)
	{
		self::$textColorRandomizationFactor = $factor;
	}
	
	
	public static function getTextCaptcha($storeProblem = true)
	{			
		$code = self::generateCode($storeProblem);
	}
	
	public static function getImgCaptcha($storeProblem = true)
	{			
		//generate the code
		$code = self::generateCode($storeProblem);	
		//create image stub	
		$im = @imagecreate(self::$width, self::$height)
			or die("Cannot Initialize new GD image stream");	
		//set image backgorund color to a random offseet form the defiend color	
		$color = self::randomizeColor(self::$bgColor, self::$bgColorRandomizationFactor);
		imagecolorallocate($im, $color['red'], $color['green'], $color['blue']);
		
		//set image text color to a random offseet form the defiend color	
		$color = self::randomizeColor(self::$textColor, self::$textColorRandomizationFactor);
		$text_color =  imagecolorallocate($im, $color['red'], $color['green'], $color['blue']);
		//imagestring($im,5, 5, 2,  $code, $text_color);
		$randRot = mt_rand(0, self::$textAngleRandomizationFactor) - intval(self::$textAngleRandomizationFactor / 2);
		
		imagettftext($im,13, $randRot, 10, 17, $text_color,"arial.ttf", $code);
		//set noise color
		$color = self::randomizeColor(self::$textColor, self::$textColorRandomizationFactor);
		$ink = imagecolorallocate($im, $color['red'], $color['green'], $color['blue']);
		//create some noise. Amount of noise is controlled by defCoeff
		for($i=0;$i< self::$dotCoeff * (self::$width);$i++) 
		{
			for($j=0;$j< self::$dotCoeff * (self::$height);$j++) 
			{			
				imagesetpixel($im, mt_rand(1,self::$width), rand(1,self::$height), $ink);
			}
		}
		
		ob_start();
		imagepng($im);
		$imdata = ob_get_contents();
		ob_end_clean();
		$imdata = base64_encode($imdata);		
		imagedestroy($im);
		//spit out the image tag with a base64 image in it
		return '<img class="captcha-code" src="data:image/x-png;base64,'.$imdata.'" alt="Please add the two numbers">';
	}
	
	
	/* 
	 * Ranomizes the color, by shifting the defined color be a random offset of no more than randFactor/2
	 */
	private static function randomizeColor($color, $randFactor)
	{
		$bgrandRed = mt_rand(0, $randFactor) - intval($randFactor / 2);
		$bgrandGreen = mt_rand(0, $randFactor) - intval($randFactor / 2);
		$bgrandBlue = mt_rand(0, $randFactor) - intval($randFactor / 2);
		
		$red = $color['red'] + $bgrandRed;
		$red = ($red > 255) ? 255 : $red;
		$red = ($red < 0) ? 0 : $red;
		
		$green = $color['green'] + $bgrandGreen;
		$green = ($green > 255) ? 255 : $green;
		$green = ($green < 0) ? 0 : $green;
		
		$blue = $color['blue'] + $bgrandBlue;
		$blue = ($blue > 255) ? 255 : $blue;
		$blue = ($blue < 0) ? 0 : $blue;
		
		return array('red'=>$red, 'green'=>$green, 'blue'=>$blue);
	}
	
	
	/*
	 * generateCode(storeProblem)
	 * generates security code in a form of a simple addition problem
	 * storeProblem - if set, no new problem will be generated on each reload, unless captcha check is called
	 */
	private static function generateCode($storeProblem)
	{		
		$ret = "";
		$sum1 = mt_rand(1,29);
		if ($sum1 < 10)
			$sum2 = mt_rand(10,29);
		else
			$sum2 = mt_rand(1,9);
		$sum3 = $sum1 + $sum2;
		
		if ($storeProblem)
		{
			if (empty($_SESSION['answ']))
			{	
				$_SESSION['answ'] = $sum3;			
				$ret = $_SESSION['prob'] = $sum1 . ' + ' . $sum2 . ' = ';			
			}
			else
				$ret = @$_SESSION['prob'];
		}
		else
		{
			$_SESSION['answ'] = $sum3;		
			$ret =  $sum1 . ' + ' . $sum2 . ' = ';
		}

		return $ret;
	}
	
	public static function  verifyCaptcha($code, $consumeProblemOnError=true)
	{
		$ret = false;
		
		if (strcmp($code, $_SESSION['answ']) === 0)
		{
			$ret = true;
			session_unset('answ');
			session_unset('prob');
		}
		else 
		{
			if ($consumeProblemOnError)
			{
				session_unset('answ');
				session_unset('prob');
			}
		}
		return $ret;
	}

}

?>