<?php
/*
Version 10.06.10
	- Added javascript console...w00t
Version 10.07.28
	- Tweaked the format that Console emails get sent in to be more readable
Version 10.10.12
	- Added a render time counter
*/
class ErrorSet
{
	private static $constants = array
	(
		1=>'Fatal Error -- E_ERROR',
		2=>'E_WARNING',
		4=>'E_PARSE',
		8=>'E_NOTICE',
		16=>'Fatal Error -- E_CORE_ERROR',
		32=>'E_CORE_WARNING',
		64=>'Fatal Error -- E_COMPILE_ERROR',
		128=>'E_COMPILE_WARNING',
		256=>'E_USER_ERROR',
		512=>'E_USER_WARNING',
		1024=>'E_USER_NOTICE',
		2048=>'E_STRICT',
		4096=>'E_RECOVERABLE_ERROR',
		8192=>'E_DEPRECATED',
		16384=>'E_USER_DEPRECATED',
		30719=>'E_ALL'
	);
	
	private static $errors=array();
	private static $sql_log=array();
	public static $display=false;
	public static $json=array();
	public static $ajax=false;
	public static $email=NULL;
	public static $email_subject=NULL;
	public static $encrypt_email = true;
	public static $backtrace = array();
	public static $start_timestamp = NULL;
	public static $old_handler;
		
	public static function disable()
	{
		restore_error_handler();
	}
	
	public static function error_handler($errno, $errstr, $errfile, $errline)
	{
		
		//Console::add('error_reporting() & $errno: '.(error_reporting() & $errno), 'Error No: '.$errno, 'Error Reporting: '.error_reporting());
		if(error_reporting() & $errno)
		{
			if($errno!=E_STRICT)
			{
				$i=count(self::$errors);
				self::$errors[$i]['type']=$errno;
				self::$errors[$i]['message']=$errstr;
				self::$errors[$i]['file']=$errfile;
				self::$errors[$i]['line']=$errline;
				self::$backtrace[$i] = debug_backtrace();
			}
		}
		return true;
	}
	
	public static function addErrorsToAlertSet()
	{
		for($i=0; $i<count(self::$errors);$i++)
		{
			switch (self::$errors[$i]['type']) 
			{
				case E_USER_ERROR:
					$type = 'ERROR: ';
					break;
				case E_WARNING:
				case E_USER_WARNING:
					$type = 'WARNING: ';
					break;
				case E_USER_NOTICE:
					$type = 'NOTICE: ';
					break;
				default:
					if(isset(self::$constants[self::$errors[$i]['type']]))
						$type = self::$constants[self::$errors[$i]['type']].':';
					break;
			}
			
			AlertSet::addError($type.self::$errors[$i]['message'].' on line '.self::$errors[$i]['line'].' in file '.self::$errors[$i]['file']);
		}
	}
	
	public static function setAJAX($b)
	{
		if($b)
		{
			ob_start();
			self::$ajax = true;
		}
		else
		{
			if(self::$ajax)
				ob_end_clean();
			self::$ajax = false;
		}
	}
	
	public static function getErrorHTML()
	{
		$table = '
		<style>
			.ErrorSet_errors{
				border: 1px solid;
				width: 100%;
				font-family: "Courier New";
				font-size: 11px;
			}
			
			.ErrorSet_errors .backtrace td,
			.ErrorSet_errors .backtrace th {
				white-space: nowrap;
				vertical-align: top;
				font-family: "Courier New";
				font-size: 11px;
			}
			.ErrorSet_error_container {
				background-color: #FBFFE6;
			}
			
			.ErrorSet_console,
			.ErrorSet_query_log {
				border: 1px solid;
				background-color: #D1D3FF;
				padding: 5px 0px 5px 10px;
				margin-top: 10px;
				color: black;
				font-family: "Courier New";
				font-size: 11px;
			}
			
			.ErrorSet_query_log {
				background-color: #fd9;
				color: #b64;
			}
			
			.ErrorSet_console pre,
			.ErrorSet_query_log pre {
				padding: 0px;
				margin: 0px;
			}
			
			.ErrorSet_console p,
			.ErrorSet_query_log p {
				padding: 0px 0px 0px 0px;
				margin: 0px 0px 10px 0px;
				font-weight: bold;
				font-size: 11px;
			}
			
			.ErrorSet_query_log hr {
				height: 0;
				border-top: 1px solid #b64;
			}
		</style>';
		$table.= '<strong>Page requested</strong>: '.(empty($_SERVER['REQUEST_URI'])? $_SERVER['SCRIPT_FILENAME'] : $_SERVER['REQUEST_URI']).'<br />';
		$table.= '<strong>Request Method</strong>: '.@$_SERVER['REQUEST_METHOD'].'<br />';
		$table.= '<strong>Referrer</strong>: '.@$_SERVER['HTTP_REFERER'].'<br />';
		$table.= '<strong>User Agent</strong>: '.@$_SERVER['HTTP_USER_AGENT'].'<br />';
		$table.= '<strong>Remote Address</strong>: '.@$_SERVER['REMOTE_ADDR'].'<br />';
		$table.= '<strong>Request Time</strong>: '.(microtime(true)-self::$start_timestamp).' seconds<br />';
		if(count(self::$errors)>0)
		{
			for($i=0; $i<count(self::$errors);$i++)
			{
				$table .= '<table class="ErrorSet_errors">';
				$table .= '
					<tr>
						<td>';
				switch (self::$errors[$i]['type']) 
				{
					case E_USER_ERROR:
						$table .= '<b style="color:#A00">ERROR: </b>';
						break;
				
					case E_WARNING:
					case E_USER_WARNING:
						$table .= '<b style="color:#AA0">WARNING: </b>';
						break;
					case E_USER_NOTICE:
						$table .= '<b style="color: #00A">NOTICE: </b>';
						break;
				
					default:
						if(isset(self::$constants[self::$errors[$i]['type']]))
							$table .= '<b>'.self::$constants[self::$errors[$i]['type']].': </b>';
						break;
				}
				$table .=
					self::$errors[$i]['message'].' on line <strong>'.self::$errors[$i]['line'].'</strong> in file <strong>'.self::$errors[$i]['file'].'</strong></td>
				</tr>';
				$table.='<tr><td colspan="6">'.self::getBacktraceHTML(self::$backtrace[$i]).'</td></tr>';
				$table .='</table>';
			}
		}
		return $table;
	}
	/*
	public static function checkPath()
	{
		$err = false;
		$string = '<br><b>Possible Path Misconfiguration</b><br>';
		$server_root = str_replace('\\', '/', strtolower($_SERVER['DOCUMENT_ROOT']));
		if(defined('DOCUMENT_ROOT'))
		{
			$doc_root = strtolower(DOCUMENT_ROOT);
			if(strpos($doc_root, $server_root)===false)
			{
				$string .= '<b>DOCUMENT_ROOT</b>='.$doc_root.', <b>$_SERVER[\'DOCUMENT_ROOT\']</b>='.$server_root.'<br>';
				$err = true;
			}
		}
		
		if(defined('CLASS_ROOT'))
		{
			$doc_root = strtolower(CLASS_ROOT);
			if(strpos($doc_root, preg_replace('/\/www$/', '', $server_root))===false)
			{
				$string .= '<b>CLASS_ROOT</b>='.$doc_root.', <b>$_SERVER[\'DOCUMENT_ROOT\']</b>='.$server_root.'<br>';
				$err = true;
			}
		}
		
		if(defined('MODULE_ROOT'))
		{
			$doc_root = strtolower(MODULE_ROOT);
			if(strpos($doc_root, $server_root)===false)
			{
				$string .= '<b>MODULE_ROOT</b>='.$doc_root.', <b>$_SERVER[\'DOCUMENT_ROOT\']</b>='.$server_root.'<br>';
				$err = true;
			}
		}
		if($err)
			return $string;
		
		return '';
	}
	*/
	public static function php_errors()
	{
		if ($error = error_get_last())
		{
			switch($error['type'])
			{
				case E_ERROR:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
				case E_USER_ERROR:
					ob_end_clean();
					$i=count(self::$errors);
					self::$errors[$i]['type']=$error['type'];
					self::$errors[$i]['message']=$error['message'];
					self::$errors[$i]['file']=$error['file'];
					self::$errors[$i]['line']=$error['line'];
					self::$backtrace[$i] = debug_backtrace();
				break;
			}
		}
		
		$error_html = self::getErrorHTML();

		if(self::$display)
		{
			if(self::$ajax)
			{
				self::$json = json_decode(ob_get_clean(), true);
				self::addErrorsToAlertSet();
				Console::addToAlertSet();
				self::$json['alerts']=AlertSet::$alerts;
				echo json_encode(self::$json);
			}
			else
			{
				echo '
				<div class="ErrorSet">
					<div>
						<div>
							<div class="ErrorSet_error_container">'.$error_html.'</div>
							<div class="ErrorSet_console"><p>Console: </p>'.Console::toString().'</div>
							<div class="ErrorSet_query_log"><p>Query Log: </p>'.QueryLog::toString().'</div>
						</div>
					</div>
				</div><div class="ErrorSet_tab" style="display:none;"><b>['. (count(self::$errors)).', <span style="color: #0000AA">'.Console::size() .'</span>]</b> '. htmlentities($_SERVER['SCRIPT_NAME']) .'</div>

				<style type="text/css">
					.ErrorSet,
					.ErrorSet_javascript {
						min-height: 15px;
						margin: 0 0 0 0;
						padding: 0;
						bottom: 25px;
						left: 0px;
						width: 97%;
						display: none;
						position: fixed;
						z-index: 101;
					}

					.ErrorSet_selected {
						display: block;
					}

					.ErrorSet > div,
					.ErrorSet_javascript > div {
						margin: 0 auto;
						padding: 10px;
						max-height: 200px;
						overflow: auto;
						width: 100%;
						color: #D8000C;
						border: 1px solid;
						background-color: #FFBABA;
					}

					.ErrorSet_tab {
						width: 52px;
						height: 13px;
						font-size: 12px;
						font-family: "Tahoma", "Verdana", "Sans-Serif";
						margin: 0 5px 0 0;
						padding: 6px;
						overflow: hidden;
						position: fixed;
						bottom: 0px;
						background-color: #FFBABA;
						color: #D8000C;
						border: 1px solid;
						white-space: nowrap;
						filter: alpha(opacity=65);	/* For IE */
						opacity: .65;				/* For everyone else */
						cursor: pointer;
						z-index: 100;
					}

					.ErrorSet_tab_selected {
						width: auto;
						min-width: 52px;
						border-top: none;
						filter: alpha(opacity=100);
						opacity: 1.00;
						z-index: 101;
					}

					.ErrorSet_javascript_tab {
						background-color: #FFFFCF;
						border-top: 1px solid #aaaa77;
						left: 0px !important;
					}

					.ErrorSet_javascript img {
						float: right;
						margin: 5px;
					}

					.ErrorSet_javascript input[type=text] {
						top: 0px;
						width: 100%;
						height: 25px;
						font-family: Courier New;
						margin: 0px 0px 5px 0px;
					}

					.ErrorSet_javascript_error {
						padding: 3px;
						font-size: 12px;
					}

					.ErrorSet_javascript_console {
						border: 1px solid;
						background-color: #D1D3FF;
						padding: 5px 0px 5px 10px;
						margin-top: 0;
						color: black;
						font-family: "Courier New";
						font-size: 11px;
						min-height: 19px;
					}

					.ErrorSet_javascript_console  pre {
						padding: 0px;
						margin: 0px;
					}

					.ErrorSet_javascript_console table {
						font-size: 11px;
					}

					.ErrorSet_javascript_console table td {
						vertical-align: top;
					}

					.ErrorSet_javascript_console  p {
						padding: 0px 0px 0px 0px;
						margin: 0px 0px 10px 0px;
						font-weight: bold;
						font-size: 13px;
					}
				</style>

				<script type="text/javascript">
					var Console =
					{
						_console: null,
						_error: null,
						_textbox: null,
						_history: [],
						_current: 0,
						_table: null,

						parseCommand: function(str)
						{
							switch(str)
							{
								case \'clear\':
									Console.clear();
								break;
								default:
									return false;
							}
							return true;
						},

						execute: function(e, str)
						{
							e = (!!e)?e:event;
							/*if((e.keyCode==13) && (((!!e.modifiers) && (!(e.modifiers & Event.SHIFT_MASK))) || (!e.shiftKey)))
							{
								var src=(!!e.target) ? e.target : e.srcElement;
								return submitChat(src.form);
							}
							return true;
							*/
							switch(e.keyCode)
							{
								case 13: //enter
									if(str.replace(/^\\s+|\\s+$/g, \'\')==\'\')
										return;
									try
									{
										this._history.push(str);
										this._current = this._history.length;
										if(Console.parseCommand(str)==false)
										{
											var ret_val = eval(str);
											if(typeof(ret_val)!=\'undefined\')
												Console.add(ret_val, str);
										}
									}
									catch(err)
									{
										Console.add(err.message, str, true);
									}
									this._textbox.value = \'\';
								break;
								case 38: //up
									if(this._current==0)
										return;
									this._textbox.value = this._history[--this._current];
								break;
								case 40: //down
									Console.add(this._history.length+\', \'+this._current);
									if(this._current==this._history.length-1)
										return;
									if(this._history[++this._current]!=\'undefined\')
										this._textbox.value = this._history[this._current];
								break;
								case 27:
									this._textbox.value = \'\';
								break;
								default:
									//this._error.innerHTML = e.keyCode;
							}
							return false;
						},
						add: function(o, cmd, error)
						{
							var str=\'\';
							var tmp = document.createElement(\'div\');
							var tr = document.createElement(\'tr\');
							var td_command = document.createElement(\'td\');
							var td_equal = document.createElement(\'td\');
							var td_result = document.createElement(\'td\');
							td_command.innerHTML = cmd;
							td_equal.innerHTML = \'=\';

							if(typeof(o)==\'object\')
								str=o.toString().substr(8, o.toString().indexOf(\']\')-8)+\' \'+JSON.stringify(o);
							else
								str = o;

							var pre = document.createElement(\'pre\');
							pre.appendChild(document.createTextNode(str));
							if(error)
								pre.style.color = \'#AA0000\';
							td_result.appendChild(pre);

							tr.appendChild(td_command);
							tr.appendChild(td_equal);
							tr.appendChild(td_result);
							Console._table.insertBefore(tr, (Console._table.childNodes.length==0? null : Console._table.childNodes[0]));

						},
						clear: function()
						{
							while(this._table.childNodes.length > 0)
								this._table.removeChild(this._table.childNodes[0]);
						}
					}

					if(!Function.prototype.partial)
					{
						Function.prototype.partial = function(/* 0..n args */)
						{
							var fn = this, args = Array.prototype.slice.call(arguments);
							return function()
							{
								var arg = 0;
								for(var i = 0; i < args.length && arg < arguments.length; i++)
								{
									if(args[i] === undefined)
									args[i] = arguments[arg++];
								}
								return fn.apply(this, args);
							};
						}
					}

					var div, tmp_div1, tmp_div2, tab, img;
					div = document.createElement(\'div\');
					div.className = \'ErrorSet_javascript\';
					tmp_div1 = document.createElement(\'div\');
					tmp_div2 = document.createElement(\'div\');

					var js_console = document.createElement(\'div\');
					js_console.className = \'ErrorSet_javascript_console\';
					js_console.id = js_console.className;

					var js_console_table = document.createElement(\'table\');

					var js_text = document.createElement(\'input\');
					js_text.type = \'text\';

					js_text.onkeyup = function(e)
					{
						Console.execute(e, this.value);
					};

					js_text.onkeypress = function(e)
					{
						//Console.execute(e, this.value);
					};

					tmp_div2.appendChild(js_text);
					tmp_div2.appendChild(js_console);

					Console._textbox = js_text;
					Console._table = js_console_table;
					Console._console = js_console;
					Console._console.appendChild(Console._table);

					tmp_div1.appendChild(tmp_div2);
					div.appendChild(tmp_div1);

					tab = document.createElement(\'div\');
					tab.className = \'ErrorSet_tab\';
					tab.appendChild(document.createTextNode(\'Javascript\'));

					tab.onclick=function()
					{
						var divs = document.getElementsByClassName(\'ErrorSet\');
						for(var i=0; i<divs.length; i++)
						{
							divs[i].nextSibling.className=\'ErrorSet_tab\';
							divs[i].className=\'ErrorSet\';
						}
						if(this.className.indexOf(\'selected\')==-1)
						{
							this.className=\'ErrorSet_tab ErrorSet_tab_selected\';
							this.previousSibling.className+=\' ErrorSet_selected\';
							Console._textbox.focus();
						}
						else
						{
							this.className=\'ErrorSet_tab\';
							this.previousSibling.className=\'ErrorSet_javascript\';
						}
					};

					document.getElementsByTagName(\'body\')[0].appendChild(div);
					document.getElementsByTagName(\'body\')[0].appendChild(tab);

					setInterval(function()
					{
						var divs, i, tab;

						divs=document.getElementsByClassName(\'ErrorSet\');
						for(i=0; i<divs.length; i++)
						{
							tab=divs[i].nextSibling;
						    if( !tab.style )
						        continue;

							tab.style.left=(i*67+67)+\'px\';
							tab.style.display=\'\';
							tab.onclick=function(divs, i)
							{
								var j;
								document.getElementsByClassName(\'ErrorSet_javascript\')[0].nextSibling.className=\'ErrorSet_tab\';
								document.getElementsByClassName(\'ErrorSet_javascript\')[0].className=\'ErrorSet_javascript\';
								for(j=0; j<divs.length; j++)
								{
									if(i==j && divs[i].className.indexOf(\'selected\')==-1)
									{
										divs[i].nextSibling.className=\'ErrorSet_tab ErrorSet_tab_selected\';
										divs[i].className+=\' ErrorSet_selected\';
										continue;
									}
									divs[j].nextSibling.className=\'ErrorSet_tab\';
									divs[j].className=\'ErrorSet\';
								}
							}.partial(divs, i);
						}
					}, 1000);

					if(!document.getElementsByClassName)
					{
						document.getElementsByClassName = function(className)
						{
							var classes = className.split(/\\s+/);
							var classesToCheck = \'\';
							var returnElements = [];
							var match, node, elements;

							if (document.evaluate)
							{
								var xhtmlNamespace = \'http://www.w3.org/1999/xhtml\';
								var namespaceResolver = (document.documentElement.namespaceURI === xhtmlNamespace)? xhtmlNamespace:null;

								for(var j=0, jl=classes.length; j<jl;j+=1)
									classesToCheck += "[contains(concat(\' \', @class, \' \'), \' " + classes[j] + " \')]";

								try
								{
									elements = document.evaluate(".//*" + classesToCheck, document, namespaceResolver, 0, null);
								}
								catch(err)
								{
									elements = document.evaluate(".//*" + classesToCheck, document, null, 0, null);
								}

								while((match = elements.iterateNext()))
									returnElements.push(match);
							}
							else
							{
								classesToCheck = [];
								elements = (document.all) ? document.all : document.getElementsByTagName("*");

								for (var k=0, kl=classes.length; k<kl; k++)
									classesToCheck.push(new RegExp("(^|\\\\s)" + classes[k] + "(\\\\s|$)"));

								for (var l=0, ll=elements.length; l<ll;l+=1)
								{
									node = elements[l];
									match = false;
									for (var m=0, ml=classesToCheck.length; m<ml; m+=1)
									{
										match = classesToCheck[m].test(node.className);
										if (!match) break;
									}
									if (match) returnElements.push(node);
								}
							}
							return returnElements;
						}
					}
				</script>';
			}
		}
		
		if(self::$email!==NULL && (count(self::$errors)>0 || !Console::isEmpty()))
		{
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: errors@elykinnovation.com' . "\r\n";
			
			if(strtolower($_SERVER['REQUEST_METHOD'])=='post')
				Console::add($_POST);
			
			$html =
				'<p>
					<b>Errors ('.count(self::$errors).'):</b><br />
					'.$error_html.'
				</p>
				<p>
					<b>Console ('.Console::size().'):</b><br />
					<pre>'.Console::toString().'</pre>
				</p>
				<p>
					<b>Query Log ('.QueryLog::size().'):</b><br />
					<pre>'.QueryLog::toString().'</pre>
				</p>';
			
			if(self::$email_subject===NULL)
				self::$email_subject = 'Error Report: '.(empty($_SERVER['HTTP_HOST'])? 'CLI Script' : $_SERVER['HTTP_HOST']).' ['.count(self::$errors).', '.Console::size().'] - '.md5(json_encode(self::$errors).Console::toString());
			
			$mail_sent=false;
			if(self::$encrypt_email)
			{
				$key=
					'-----BEGIN CERTIFICATE-----
MIIHKTCCBhGgAwIBAgIDA6E1MA0GCSqGSIb3DQEBBQUAMIGMMQswCQYDVQQGEwJJ
TDEWMBQGA1UEChMNU3RhcnRDb20gTHRkLjErMCkGA1UECxMiU2VjdXJlIERpZ2l0
YWwgQ2VydGlmaWNhdGUgU2lnbmluZzE4MDYGA1UEAxMvU3RhcnRDb20gQ2xhc3Mg
MSBQcmltYXJ5IEludGVybWVkaWF0ZSBDbGllbnQgQ0EwHhcNMTIwMTMwMDYwOTM0
WhcNMTMwMTMwMTEwNjU3WjBpMRkwFwYDVQQNExB4SVNqTEFnWTM2ZUIzSjdxMSIw
IAYDVQQDDBllcnJvcnNAZWx5a2lubm92YXRpb24uY29tMSgwJgYJKoZIhvcNAQkB
FhllcnJvcnNAZWx5a2lubm92YXRpb24uY29tMIIBIjANBgkqhkiG9w0BAQEFAAOC
AQ8AMIIBCgKCAQEA0l6/kYhl9il/NgOqzWJ9qI6L/up0UrCz3Lf4xbrCj4SkfbFl
i6JRhfuDlQsE8Wi6JEqon3XFQeMViDmu7tkeopSSr/pGb472LKiFd+E8W3kQXPHo
uyHRu1hmVoH41Q/NADPATvDKZjnbnxskj4hkZBq+mD0LjYhruZxPnZTRg+F3RPeM
v+xLrL2Byn/KV4gvYVthlqUJSZWEzRRZLYRDLRq3scVuDd1ja7YLRI1uI9L5MXMT
o2syRzbGB8h9GgC5/XK4/Vw2sFL9H/H664C4pcP2pnQ4IFAeozd2qPc8OOEHfkln
Krr1MNyZOBVtQLjcMyVJ8JxE5uftI4GiWILkWwIDAQABo4IDtDCCA7AwCQYDVR0T
BAIwADALBgNVHQ8EBAMCBLAwHQYDVR0lBBYwFAYIKwYBBQUHAwIGCCsGAQUFBwME
MB0GA1UdDgQWBBRMpbC9AjqmC+TIpeoNjfhSLAWKiDAfBgNVHSMEGDAWgBRTcu2S
nODaywFcfH6WNU7y1LhRgjAkBgNVHREEHTAbgRllcnJvcnNAZWx5a2lubm92YXRp
b24uY29tMIICIQYDVR0gBIICGDCCAhQwggIQBgsrBgEEAYG1NwECAjCCAf8wLgYI
KwYBBQUHAgEWImh0dHA6Ly93d3cuc3RhcnRzc2wuY29tL3BvbGljeS5wZGYwNAYI
KwYBBQUHAgEWKGh0dHA6Ly93d3cuc3RhcnRzc2wuY29tL2ludGVybWVkaWF0ZS5w
ZGYwgfcGCCsGAQUFBwICMIHqMCcWIFN0YXJ0Q29tIENlcnRpZmljYXRpb24gQXV0
aG9yaXR5MAMCAQEagb5UaGlzIGNlcnRpZmljYXRlIHdhcyBpc3N1ZWQgYWNjb3Jk
aW5nIHRvIHRoZSBDbGFzcyAxIFZhbGlkYXRpb24gcmVxdWlyZW1lbnRzIG9mIHRo
ZSBTdGFydENvbSBDQSBwb2xpY3ksIHJlbGlhbmNlIG9ubHkgZm9yIHRoZSBpbnRl
bmRlZCBwdXJwb3NlIGluIGNvbXBsaWFuY2Ugb2YgdGhlIHJlbHlpbmcgcGFydHkg
b2JsaWdhdGlvbnMuMIGcBggrBgEFBQcCAjCBjzAnFiBTdGFydENvbSBDZXJ0aWZp
Y2F0aW9uIEF1dGhvcml0eTADAgECGmRMaWFiaWxpdHkgYW5kIHdhcnJhbnRpZXMg
YXJlIGxpbWl0ZWQhIFNlZSBzZWN0aW9uICJMZWdhbCBhbmQgTGltaXRhdGlvbnMi
IG9mIHRoZSBTdGFydENvbSBDQSBwb2xpY3kuMDYGA1UdHwQvMC0wK6ApoCeGJWh0
dHA6Ly9jcmwuc3RhcnRzc2wuY29tL2NydHUxLWNybC5jcmwwgY4GCCsGAQUFBwEB
BIGBMH8wOQYIKwYBBQUHMAGGLWh0dHA6Ly9vY3NwLnN0YXJ0c3NsLmNvbS9zdWIv
Y2xhc3MxL2NsaWVudC9jYTBCBggrBgEFBQcwAoY2aHR0cDovL2FpYS5zdGFydHNz
bC5jb20vY2VydHMvc3ViLmNsYXNzMS5jbGllbnQuY2EuY3J0MCMGA1UdEgQcMBqG
GGh0dHA6Ly93d3cuc3RhcnRzc2wuY29tLzANBgkqhkiG9w0BAQUFAAOCAQEAOGW6
04fCy3Wf1j4wKrUmN7x0Rnjc1UfX9QgVL5V8mx90APXwPfLIbg8EUEjfaJYN0mKs
R9ALKMebZZoCJg6n+r1Zhoi321QYqRIiZzO1C8xqb3ls5dij1HHjWcULHhnwoL5x
AhN2KpDdem4TEb5WXmHPEggnO7sdUXCchael7sucJdl3DcUzzBgIUffXRLNt0s5O
eW2zLlsrwGPvIiGC75V26chMLtiBvGDU0EFVww0umYVZMNXvtuQJXBiNHk32EeSq
3100+ZngBsnyH2aHHCHqmyRzYL7xxOTJQO7xqU9kDtD2DcG9xdUNgiQUO9xd9i3U
37qC+0GADm4wFZbZ4Q==
-----END CERTIFICATE-----
';
				
				$temp_file=tempnam(sys_get_temp_dir(), 'err');
				$temp_file_encrypted=tempnam(sys_get_temp_dir(), 'enc');
				file_put_contents($temp_file, $headers."\r\n".$html);
				
				if(openssl_pkcs7_encrypt($temp_file, $temp_file_encrypted, $key, array('From'=>'errors@elykinnovation.com'), 0, 1))
				{
					$parts=preg_split('/\r?\n\r?\n/', file_get_contents($temp_file_encrypted), 2);
					mail(self::$email, self::$email_subject, $parts[1], $parts[0]);
					$mail_sent=true;
				}
				unlink($temp_file);
				unlink($temp_file_encrypted);
			}
			
			if(!$mail_sent)
				mail(self::$email, self::$email_subject, $html, 'From: errors@elykinnovation.com'."\r\n");
		}
	}
	
	public static function getBacktraceHTML($backtrace_array)
	{
		$table = '
			<table class="backtrace">
				<tr>
					<th colspan="5" align="left">Stack Trace</th>
				</tr>';
		$i=0;
		foreach($backtrace_array as $backtrace)
		{
			if(isset($backtrace['class']) && $backtrace['class']=='ErrorSet')
				continue;
			$args = '';
			if(isset($backtrace['args']))
			{
				for($j=0; $j<count($backtrace['args']); $j++)
				{
					$args .= ($j!=0?', ':'');
					if(is_string($backtrace['args'][$j]))
						$args .= '<span style="color: #0000AA">"'.htmlentities($backtrace['args'][$j]).'"</span>';
					else if(is_numeric($backtrace['args'][$j]))
						$args .= '<span style="color: #009900">'.$backtrace['args'][$j].'</span>';
					else if(is_object($backtrace['args'][$j]))
						$args .= '<span  style="cursor: pointer" onclick="this.childNodes[1].style.display=(this.childNodes[1].style.display==\'none\'?\'\':\'none\')">Object...<span style="display: none">'.nl2br(htmlentities(print_r($backtrace['args'][$j], true))).'</span></span>';
					else if(is_array($backtrace['args'][$j]))
						$args .= '<span  style="cursor: pointer" onclick="this.childNodes[1].style.display=(this.childNodes[1].style.display==\'none\'?\'\':\'none\')">Array...<span style="display: none"><pre>'.htmlentities(print_r($backtrace['args'][$j], true)).'</pre></span></span>';
					else
					{
						ob_start();
						var_dump($backtrace['args'][$j]);
						$string = ob_get_clean()."\n";
						$args .= htmlentities($string);	
					}
				}
			}
			$table .= '
				<tr class="'.($i%2==0?'light':'dark').'">
					<td>#'.$i.'</td>
					<td>'.(isset($backtrace['class'])?$backtrace['class'].$backtrace['type']:'').$backtrace['function'].'('.$args.')</td>
					<td>'.(isset($backtrace['file'])?$backtrace['file']:'').'</td>
					<td>'.(isset($backtrace['line'])?$backtrace['line']:'').'</td>
					<td>'.(isset($backtrace['object'])?'<span style="cursor: pointer" onclick="this.childNodes[1].style.display=(this.childNodes[1].style.display==\'none\'?\'\':\'none\')">Object...<span style="display: none"><pre>'.print_r($backtrace['object'], true):'').'</pre></span></span></td>
				</tr>';
			$i++;
		}
		
		$table .= '</table>';
		return $table;
	}
}

class Console
{
	private static $array = array();

	public static function add()
	{
		$bk = debug_backtrace();
		for($i=0; $i<func_num_args(); $i++)
		{
			$j = count(self::$array);
			self::$array[$j]['item'] = func_get_arg($i);
			self::$array[$j]['file'] = $bk[0]['file'];
			self::$array[$j]['line'] = $bk[0]['line'];
		}
	}
	
	public static function toString()
	{
		$string = '';
		for($i = 0; $i<count(self::$array); $i++)
		{
			$string .= '<p><pre>From '.self::$array[$i]['file']. ' on line '.self::$array[$i]['line']."\n";
			
			if(is_object(self::$array[$i]['item']))
				$string .= htmlentities(self::displayObject(self::$array[$i]['item']));
			else if(is_array(self::$array[$i]['item']))
				$string .= htmlentities(self::displayArray(self::$array[$i]['item']));
			else if(is_string(self::$array[$i]['item']))
				$string .= htmlentities(self::$array[$i]['item']);
			else
				$string .= htmlentities(var_export(self::$array[$i]['item'], true));
			
			$string.='</pre></p>';
		}
		
		return $string;
	}
	
	public static function addToAlertSet()
	{
		for($i=0; $i<count(self::$array); $i++)
			AlertSet::addDebug(self::$array[$i]['file'].': '.self::$array[$i]['line'].' -- '.self::$array[$i]['item']);
	}
	
	private static function displayObject($object)
	{
		return print_r($object, true);
		/*if (is_object($Object)) {

        if ($ObjectDepth > $this->options['maxObjectDepth']) {
          return '** Max Object Depth ('.$this->options['maxObjectDepth'].') **';
        }
        
        foreach ($this->objectStack as $refVal) {
            if ($refVal === $Object) {
                return '** Recursion ('.get_class($Object).') **';
            }
        }
        array_push($this->objectStack, $Object);
                
        $return['__className'] = $class = get_class($Object);
        $class_lower = strtolower($class);

        $reflectionClass = new ReflectionClass($class);  
        $properties = array();
        foreach( $reflectionClass->getProperties() as $property) {
          $properties[$property->getName()] = $property;
        }
            
        $members = (array)$Object;
            
        foreach( $properties as $raw_name => $property ) {
          
          $name = $raw_name;
          if($property->isStatic()) {
            $name = 'static:'.$name;
          }
          if($property->isPublic()) {
            $name = 'public:'.$name;
          } else
          if($property->isPrivate()) {
            $name = 'private:'.$name;
            $raw_name = "\0".$class."\0".$raw_name;
          } else
          if($property->isProtected()) {
            $name = 'protected:'.$name;
            $raw_name = "\0".'*'."\0".$raw_name;
          }
          
          if(!(isset($this->objectFilters[$class_lower])
               && is_array($this->objectFilters[$class_lower])
               && in_array($raw_name,$this->objectFilters[$class_lower]))) {

            if(array_key_exists($raw_name,$members)
               && !$property->isStatic()) {
              
              $return[$name] = $this->encodeObject($members[$raw_name], $ObjectDepth + 1, 1);      
            
            } else {
              if(method_exists($property,'setAccessible')) {
                $property->setAccessible(true);
                $return[$name] = $this->encodeObject($property->getValue($Object), $ObjectDepth + 1, 1);
              } else
              if($property->isPublic()) {
                $return[$name] = $this->encodeObject($property->getValue($Object), $ObjectDepth + 1, 1);
              } else {
                $return[$name] = '** Need PHP 5.3 to get value **';
              }
            }
          } else {
            $return[$name] = '** Excluded by Filter **';
          }
        }
        
        // Include all members that are not defined in the class
        // but exist in the object
        foreach( $members as $raw_name => $value ) {
          
          $name = $raw_name;
          
          if ($name{0} == "\0") {
            $parts = explode("\0", $name);
            $name = $parts[2];
          }
          
          if(!isset($properties[$name])) {
            $name = 'undeclared:'.$name;
              
            if(!(isset($this->objectFilters[$class_lower])
                 && is_array($this->objectFilters[$class_lower])
                 && in_array($raw_name,$this->objectFilters[$class_lower]))) {
              
              $return[$name] = $this->encodeObject($value, $ObjectDepth + 1, 1);
            } else {
              $return[$name] = '** Excluded by Filter **';
            }
          }
        }
        
        array_pop($this->objectStack);*/
	}
	
	private static function displayArray($array)
	{
		//FIXME: Display pretty version of array
		return var_export($array, true);
	}
	
	public static function isEmpty()
	{
		return (count(self::$array)==0);
	}
	
	public static function size()
	{
		return count(self::$array);	
	}
}

class QueryLog
{
	private static $array=array();

	public static function add()
	{
		for($i=0; $i < func_num_args(); $i++)
			self::$array[]=func_get_arg($i);
	}

	public static function toString()
	{
		$string='';
		for($i=0; $i < count(self::$array); $i++)
		{
			$string.='<p><pre>'. htmlentities(
				preg_replace('/[\n\s]+$/', '', 
				preg_replace('/\t+/', "\t", 
				preg_replace('/(^|\n)\t*(SELECT|FROM|LEFT JOIN|OUTER JOIN|INNER JOIN|WHERE|ORDER BY|GROUP BY|LIMIT)[\n\s]*/', "$1$2\n\t",
			self::$array[$i])))) .'</pre></p>'.($i==count(self::$array)-1? '' : "<hr />\n");
		}

		return $string;
	}
	
	public static function isEmpty()
	{
		return (count(self::$array)==0);
	}
	
	public static function size()
	{
		return count(self::$array);
	}
}

ErrorSet::$start_timestamp=microtime(true);

ErrorSet::$old_handler = set_error_handler(array('ErrorSet', 'error_handler'));
register_shutdown_function(array('ErrorSet', 'php_errors'));
?>
