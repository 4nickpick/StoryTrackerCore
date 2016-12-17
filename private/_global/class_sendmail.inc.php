<?php
if(!function_exists('recaptcha_get_html'))
	include_once('e:/websites/_global/recaptcha.inc.php');
	
if( !class_exists('eCaptcha') )
	include_once('eCaptcha.inc.php');

class Sendmail
{
	var $forms = array();
	var $recaptchaPublicKey, $recaptchaPrivateKey;
	
	function Sendmail($publicKey='', $privateKey='', $recaptchaText='reCAPTCHA Security Code', $recaptchaInstructions='', $recaptchaCantReadIt='Can\'t read it?', $recaptchaSwitchToAudio='Switch to audio CAPTCHA')
	{
		$this->useEcaptcha = false;
		
		$this->useRecaptcha = true;
		if(empty($publicKey) || empty($privateKey))
			$this->useRecaptcha = false;
		
		$this->recaptchaPublicKey = $publicKey;
		$this->recaptchaPrivateKey = $privateKey;
		$this->recaptchaText = $recaptchaText;
		$this->recaptchaInstructions = $recaptchaInstructions;
		$this->recaptchaCantReadIt = $recaptchaCantReadIt;
		$this->recaptchaSwitchToAudio = $recaptchaSwitchToAudio;
	}
	
	function addForm($formName, $emailConfirm, $labelStyle='2-column')
	{
		$this->forms[$formName] = array(
			'emailConfirm'=>$emailConfirm,
			'labelStyle'=>$labelStyle,
			'fields'=>array(),
			'recipients'=>array(),
			'callbacks'=>array(),
			'errmsg'=>''
		);
	}
	
	function addField($formName, $label, $name, $type, $required=false, $value='', $selectOptions='')
	{
		if(@$_POST['ContactFormName']==$formName)
			$value=dirtify(@$_POST[$name]);
		$this->forms[$formName]['fields'][] = array('name'=>$name, 'type'=>$type, 'required'=>$required, 'label'=>$label, 'value'=>$value, 'selectOptions'=>$selectOptions);
	}
	
	function getHiddenInputs($formName)
	{
		$inputs = '';
		for($i=0; $i < count($this->forms[$formName]['fields']); $i++)
		{
			if($this->forms[$formName]['fields'][$i]['type']=='hidden')
				$inputs .= '<input type="hidden" name="'.$this->forms[$formName]['fields'][$i]['name'].'" id="'.$this->forms[$formName]['fields'][$i]['name'].'" value="'.$this->forms[$formName]['fields'][$i]['value'].'" />'."\r\n";
		}
		return $inputs;
	}
	
	function getNonHiddenInputs($formName)
	{
		$inputs = '';
		for($i=0; $i < count($this->forms[$formName]['fields']); $i++)
		{
			if($this->forms[$formName]['fields'][$i]['type']=='hidden')
				continue;
			$inputs .= '
				<tr>
					<td'. ($this->forms[$formName]['labelStyle']=='2-column' && $this->forms[$formName]['fields'][$i]['type']=='html' ? ' colspan="2"' : '') .'>'.($this->forms[$formName]['fields'][$i]['type']!='html'?'<label for="'.$this->forms[$formName]['fields'][$i]['name'].'">'.$this->forms[$formName]['fields'][$i]['label'].'</label>
					'.($this->forms[$formName]['labelStyle']=='2-column'? '</td><td>' : '<br />') : '');
			switch($this->forms[$formName]['fields'][$i]['type'])
			{
				case 'text':
				case 'email':
				case 'phone':
					$inputs .= '<input type="text" name="'.$this->forms[$formName]['fields'][$i]['name'].'" id="'.$this->forms[$formName]['fields'][$i]['name'].'" value="'.htmlentities($this->forms[$formName]['fields'][$i]['value']).'" />';
				break;
				case 'textarea':
					$inputs .= '<textarea name="'.$this->forms[$formName]['fields'][$i]['name'].'" id="'.$this->forms[$formName]['fields'][$i]['name'].'">'.htmlentities($this->forms[$formName]['fields'][$i]['value']).'</textarea>';
				break;
				case 'select':
					$inputs .= '<select name="'.$this->forms[$formName]['fields'][$i]['name'].'" id="'.$this->forms[$formName]['fields'][$i]['name'].'">';
					$options = preg_replace('/([^\\\\]),/', '$1!,', $this->forms[$formName]['fields'][$i]['selectOptions']);
					$options = preg_split('/\s*!,\s*/', $options);
					for($j=0; $j < count($options); $j++)
					{
						$options[$j]=str_replace('\,', ',', $options[$j]);
						$inputs .= '<option value="'.htmlentities($options[$j]).'"'.($options[$j]==$this->forms[$formName]['fields'][$i]['value'] ? ' selected' : '').'>'.htmlentities($options[$j]).'</option>';
					}
					$inputs .= '</select>';
				break;
				case 'checkbox':
					$options = preg_split('/\s*,\s*/', $this->forms[$formName]['fields'][$i]['selectOptions']);
					$suffix='';
					if(count($options)>1)
						$suffix='[]';
					for($j=0; $j < count($options); $j++)
					{
						$inputs .= '<input type="checkbox" name="'.$this->forms[$formName]['fields'][$i]['name'].$suffix.'" '.($options[$j]==$this->forms[$formName]['fields'][$i]['value'] ? ' checked' : '').' value="'.htmlentities($options[$j]).'" class="checkbox" /> ';
						$inputs .= htmlentities($options[$j]) .'<br />';
					}
				break;
				case 'radio':
					$options = preg_split('/\s*,\s*/', $this->forms[$formName]['fields'][$i]['selectOptions']);
					for($j=0; $j < count($options); $j++)
					{
						$inputs .= '<input type="radio" name="'.$this->forms[$formName]['fields'][$i]['name'].'" '.($options[$j]==$this->forms[$formName]['fields'][$i]['value'] ? ' checked' : '').' value="'.htmlentities($options[$j]).'" class="radio" /> ';
						$inputs .= htmlentities($options[$j]) .'<br />';
					}
				break;
				case 'html':
					$inputs .= $this->forms[$formName]['fields'][$i]['label'];
				break;
			}
			$inputs .= '</td></tr>'."\r\n";
		}
		return $inputs;
	}
	
	function getForm($formName, $action=null)
	{
		//check for valid template
		for($i=0; $i<count($this->forms[$formName]['recipients']); $i++)
		{
			$template_valid=file_get_contents($this->forms[$formName]['recipients'][$i]['htmlTemplate']);
			if( !$template_valid )
				echo ('<span style="color:red;">Could not get Email Template: <strong>' . $this->forms[$formName]['recipients'][$i]['htmlTemplate'] . '</strong></span>');
		}
		
		if(empty($action))
			$action=htmlentities($_SERVER['REQUEST_URI']);
		$html = '
		<a name="sendmail_form"></a>
		<form method="post" name="'.htmlentities($formName).'" id="'.htmlentities($formName).'" action="'.$action.'">
			<input type="hidden" name="ContactFormName" value="'.htmlentities($formName).'" />
			'.$this->getHiddenInputs($formName).'
			<table class="sendmail_table">
				'.(!empty($this->forms[$formName]['errmsg']) ? '<tr><td'.($this->forms[$formName]['labelStyle']=='2-column'? ' colspan="2"':'').'><ul>'.$this->forms[$formName]['errmsg'].'</ul></td></tr>' : '').'
				'.$this->getNonHiddenInputs($formName);
		if($this->useRecaptcha)
		{
			$html .= '
				<tr>
					<td>
						<label for="recaptcha_response_field" class="recaptcha_image_label">'.$this->recaptchaText.'</label>
						<label for="recaptcha_response_field" class="recaptcha_image_label">'.
						(
							!empty($this->recaptchaInstructions) ?
							$this->recaptchaInstructions :
							('Please type the words in the image '.($this->forms[$formName]['labelStyle']=='2-column'? 'here' : 'into the text field below').':')
						)
						.'</label>
					'.($this->forms[$formName]['labelStyle']=='2-column'? '</td><td>' : '<br />').'
						<div id="custom_theme_widget" style="display:none;">
							<div id="recaptcha_image"></div>
							'.($this->forms[$formName]['labelStyle']=='2-column'? '' : '<br />').'
							<input type="text" name="recaptcha_response_field" id="recaptcha_response_field" /><br />
							<span class="recaptcha_only_if_image">
								<a href="javascript:Recaptcha.reload();">'. $this->recaptchaCantReadIt .'</a><br />
								<a href="javascript:Recaptcha.switch_type(\'audio\');">'. $this->recaptchaSwitchToAudio .'</a><br />
							</span>
							<span class="recaptcha_only_if_audio">
								<a href="javascript:Recaptcha.reload();">Can\'t hear it?</a><br />
								<a href="javascript:Recaptcha.switch_type(\'image\');">Switch to image CAPTCHA</a><br />
							</span>
						</div>
						'.recaptcha_get_html($this->recaptchaPublicKey).'
					</td>
				</tr>';
		}
		else if($this->useEcaptcha)
		{
			$html .='
				<tr>
					<td>
						' . Ecaptcha::getImgCaptcha() . '
						<input type="text" name="ecaptcha_response_field" /><br /> 
					</td>
				</tr>';
		}
		
		$html .='
				<tr>
					'.($this->forms[$formName]['labelStyle']=='2-column'? '<td></td>' : '').'
					<td><input type="submit" value="Submit" class="button" /></td>
				</tr>
			</table>
		</form>
		';
		

		return $html;
	}
	
	function getErrors($formName)
	{
		return '<ul>'.$this->forms[$formName]['errmsg'].'</ul>';
	}
	
	function addCallback($formName, $function, $args=array())
	{
		$this->forms[$formName]['callbacks'][] = array(
			'function'=>$function,
			'args'=>$args
		);
	}
	
	function addRecipient($formName, $emailTo, $emailFrom, $emailSubject, $htmlTemplate, $replyTo=false)
	{
		$this->forms[$formName]['recipients'][] = array(
			'emailTo'=>$emailTo,
			'emailFrom'=>$emailFrom,
			'emailSubject'=>$emailSubject,
			'htmlTemplate'=>$htmlTemplate,
			'replyTo'=>$replyTo
		);
	}
	
	function endForm($formName)
	{
		if(@$_POST['ContactFormName']==$formName)
		{
			for($i=0; $i<count($this->forms[$formName]['fields']); $i++)
			{
				if(empty($_POST[$this->forms[$formName]['fields'][$i]['name']]))
				{
					if($this->forms[$formName]['fields'][$i]['required']!==false)
						$this->forms[$formName]['errmsg'] .= '<li>"'.$this->forms[$formName]['fields'][$i]['label'].'" is required.</li>';
				}
				else if($this->forms[$formName]['fields'][$i]['type']=='email' && !isEmail($_POST[$this->forms[$formName]['fields'][$i]['name']]))
					$this->forms[$formName]['errmsg'] .= '<li>"'.$this->forms[$formName]['fields'][$i]['label'].'" must be a valid email address.</li>';
				else if($this->forms[$formName]['fields'][$i]['type']=='phone' && !isPhone($_POST[$this->forms[$formName]['fields'][$i]['name']]))
					$this->forms[$formName]['errmsg'] .= '<li>"'.$this->forms[$formName]['fields'][$i]['label'].'" must be a valid phone number.</li>';
				else if($this->forms[$formName]['fields'][$i]['required']!==false && $_POST[$this->forms[$formName]['fields'][$i]['name']]!=$this->forms[$formName]['fields'][$i]['required'])
					$this->forms[$formName]['errmsg'] .= '<li>"'.$this->forms[$formName]['fields'][$i]['label'].'" was not given a valid answer.</li>';
			}
			
			/* Check security code */
			if($this->useRecaptcha)
			{
				$resp = recaptcha_check_answer($this->recaptchaPrivateKey, $_SERVER['REMOTE_ADDR'], @$_POST['recaptcha_challenge_field'], @$_POST['recaptcha_response_field']);
			}
			else if($this->useEcaptcha)
			{
				$ecaptcha_valid = Ecaptcha::verifyCaptcha(@$_POST['ecaptcha_response_field']);
			}
			
			$recaptcha_ok = ($this->useRecaptcha && $resp->is_valid) || !$this->useRecaptcha;
			$ecaptcha_ok = ($this->useEcaptcha && $ecaptcha_valid) || !$this->useEcaptcha;
			
			if($recaptcha_ok && $ecaptcha_ok)
			{
				if(empty($this->forms[$formName]['errmsg']))
				{
					for($i=0; $i<count($this->forms[$formName]['callbacks']); $i++)
						call_user_func_array($this->forms[$formName]['callbacks'][$i]['function'], $this->forms[$formName]['callbacks'][$i]['args']);
					
					for($i=0; $i<count($this->forms[$formName]['recipients']); $i++)
					{
						$msg=file_get_contents($this->forms[$formName]['recipients'][$i]['htmlTemplate']);
						
						if( $msg !== false )
						{
							foreach($_POST as $key => $val)
							{
								if(is_array($val))
									$val = implode(', ', $val);
								$msg = str_replace('[['.$key.']]', htmlentities(dirtify($val)), $msg);
							}
							$msg = preg_replace('/\[\[.*?\]\]/', '', $msg);
						}
						else
						{
							$msg = '<h2>Contact Form Request</h2>';
							foreach($_POST as $key => $val)
							{
								if( strpos($key, 'recaptcha') === false )
								{
									if(is_array($val))
										$val = implode(', ', $val);
									$msg .= $key . ': ' . htmlentities(dirtify($val)) . '<br />';
								}
							}
						}
						$to=$this->forms[$formName]['recipients'][$i]['emailTo'];
						$to_addresses=preg_split('/[\,;]/', $this->forms[$formName]['recipients'][$i]['emailTo']);
						if(!isEmail($to_addresses[0]))
							$to=$_POST[$this->forms[$formName]['recipients'][$i]['emailTo']];
						
						$from=$this->forms[$formName]['recipients'][$i]['emailFrom'];
						if(!isEmail($from))
							$from=$_POST[$from];
						
						$replyTo=$this->forms[$formName]['recipients'][$i]['replyTo'];
						if(empty($replyTo))
							$replyTo=$from;
						else if(!isEmail($replyTo))
							$replyTo=$_POST[$replyTo];
							
					
						mail($to, $this->forms[$formName]['recipients'][$i]['emailSubject'], $msg, 'From: '.$from."\r\n".'Reply-to: '.$replyTo."\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=iso-8859-1\r\n");
					}
					header('Location: '.$this->forms[$formName]['emailConfirm']);
					exit();
				}
			}
			else
				$this->forms[$formName]['errmsg'] .= '<li>The security code you entered was incorrect.</li>';
		}
	}
	
	function useEcaptcha($use=true)
	{
		$this->useEcaptcha = $use;	
	}
}

if(!function_exists('dirtify'))
{
	function dirtify($cleanTrash)
	{
		if(get_magic_quotes_gpc() == 1)
		{
			if(!is_array($cleanTrash))
				return stripslashes($cleanTrash);
			return stripslashes_deep($cleanTrash);
		}
		return $cleanTrash;
	}
}

?>
