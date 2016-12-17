<?php
class PayPal
{
	public $cc_firstname, $cc_lastname, $cc_type, $cc_number, $cc_exp_month, $cc_exp_year, $cc_cvv, $cc_address;
	public $cc_address_2, $cc_city, $cc_state, $cc_zip, $amount, $shipping, $API_UserName, $API_Password, $API_Signature;
	public $environment = '', $response=array(), $cc_email;
	private $items=array();
	
	function __construct($api_username, $api_password, $api_signature, $environment='')
	{
		$this->API_UserName = $api_username;
		$this->API_Password = $api_password;
		$this->API_Signature = $api_signature;
		$this->environment = $environment;
		
		if(empty($this->API_UserName))
			throw new Exception('API Username not specified');
		if(empty($this->API_Password))
			throw new Exception('API Password not specified');
		if(empty($this->API_Signature))
			throw new Exception('API Signature not specified');
	}
	/**
	 * Send HTTP POST Request
	 *
	 * @param	string	The API method name
	 * @param	string	The POST Message fields in &name=value pair format
	 * @return	array	Parsed HTTP Response body
	 */
	function _post($methodName_, $nvpStr_) 
	{
		// or 'beta-sandbox' or 'live'
		// Set up your API credentials, PayPal end point, and API version.
		
		$API_Endpoint = "https://api-3t.paypal.com/nvp";
		if("sandbox" === $this->environment || "beta-sandbox" === $this->environment) 
			$API_Endpoint = 'https://api-3t.'.$this->environment.'.paypal.com/nvp';

		$version = urlencode('51.0');
	
		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
	
		// Set the API operation, version, and API signature in the request.
		$nvpreq = 
			'METHOD='.$methodName_.
			'&VERSION='.$version.
			'&PWD='.$this->API_Password.
			'&USER='.$this->API_UserName.
			'&SIGNATURE='.$this->API_Signature.$nvpStr_.
			'&IPADDRESS='.$_SERVER['REMOTE_ADDR'];
	
		// Set the request as a POST FIELD for curl.
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
	
		// Get response from the server.
		$httpResponse = curl_exec($ch);
	
		if(!$httpResponse) 
			throw new Exception("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
	
		// Extract the response details.
		$httpResponseAr = explode("&", $httpResponse);
	
		$httpParsedResponseAr = array();
		foreach ($httpResponseAr as $i => $value) 
		{
			$tmpAr = explode("=", $value);
			if(sizeof($tmpAr) > 1) 
			{
				$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
			}
		}
	
		if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) 
			throw new Exception("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
	
		return $httpParsedResponseAr;
	}
	
	function process()
	{
		// Set request-specific fields.
		$paymentType = urlencode('Sale');				//'Authorization' or 'Sale'
		$firstName = urlencode($this->cc_firstname);
		$lastName = urlencode($this->cc_lastname);
		$email = urlencode($this->cc_email);
		$creditCardType = urlencode($this->cc_type);
		$creditCardNumber = urlencode($this->cc_number);
		// Month must be padded with leading zero
		$expDateMonth = urlencode(str_pad($this->cc_exp_month, 2, '0', STR_PAD_LEFT));
		
		$expDateYear = urlencode($this->cc_exp_year);
		$cvv2Number = urlencode($this->cc_cvv);
		$address1 = urlencode($this->cc_address);
		$address2 = urlencode($this->cc_address_2);
		$city = urlencode($this->cc_city);
		$state = urlencode($this->cc_state);
		$zip = urlencode($this->cc_zip);
		$country = urlencode('US');				// US or other valid country code
		$currencyID = urlencode('USD');							// or other currency ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
		$amount = 0;
		$shipping = urlencode($this->shipping);
		$item_string = '';
		for($i=0; $i<count($this->items); $i++)
		{
			$amount	+= $this->items[$i]->amount*$this->items[$i]->qty;
			$item_string .= 
			'&L_NAME'.$i.'='.urlencode($this->items[$i]->name).
			'&L_DESC'.$i.'='.urlencode($this->items[$i]->description).
			'&L_QTY'.$i.'='.urlencode($this->items[$i]->qty).
			'&L_AMT'.$i.'='.urlencode(number_format($this->items[$i]->amount, 2));
		}
		$item_amount = urlencode($amount);
		$amount = urlencode($amount+$this->shipping);
		
		// Add request-specific fields to the request string.
		$nvpStr =	
			'&PAYMENTACTION='.$paymentType.
			'&AMT='.$amount.
			'&SHIPPINGAMT='.$shipping.
			'&ITEMAMT='.$item_amount.
			'&CREDITCARDTYPE='.$creditCardType.
			'&ACCT='.$creditCardNumber.
			'&EXPDATE='.$expDateMonth.$expDateYear.
			'&CVV2='.$cvv2Number.
			'&FIRSTNAME='.$firstName.
			'&LASTNAME='.$lastName.
			'&EMAIL='.$email.
			'&STREET='.$address1.
			'&STREET2='.$address2.
			'&CITY='.$city.
			'&STATE='.$state.
			'&ZIP='.$zip.
			'&COUNTRYCODE='.$country.
			'&CURRENCYCODE='.$currencyID.
			$item_string;
		
		// Execute the API operation; see the PPHttpPost function above.
		$httpParsedResponseAr = $this->_post('DoDirectPayment', $nvpStr);
		$this->response = $httpParsedResponseAr;
		
		if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) 
		{
			return true;
		} 
		
		return false;		
	}
	
	public function addItem($paypal_item)
	{
		if(!($paypal_item instanceof PayPalItem))
		{
			throw new Exception('Argument must be type PayPalItem');
			return false;
		}
		$this->items[] = $paypal_item;
		return true;
	}
	
	public function generateReceipt($template='')
	{
		if(empty($template) || !file_exists($template))
			$html = 
				'<p>This a reciept for your purchase on [[DATE]] at [[TIME]]</p>
				<p>Your PayPal transaction ID is [[TRANSACTION_ID]]</p>
				[[ITEM_TABLE]]<br><br>[[BILLING_INFORMATION]]';
		else
			$html = file_get_contents($template);
		
		$billing_info = '<table>
			<tr>
				<th colspan="2"><h3>Billing Information</h3></th>
			</tr>
			<tr>
				<th align="left">Name on Card</th>
				<td>[[CC_FIRSTNAME]] [[CC_LASTNAME]]</td>
			</tr>
			<tr>
				<th align="left">Card Number</th>
				<td>XXXXXXXXXXXX[[CC_NUMBER]]</td>
			</tr>
			<tr>
				<th align="left">Expiration Date</th>
				<td>[[CC_EXP_DATE]]</td>
			</tr>
			<tr>
				<th align="left">Address</th>
				<td>[[CC_ADDRESS]]</td>
			</tr>
			<tr>
				<th align="left">Address 2</th>
				<td>[[CC_ADDRESS_2]]</td>
			</tr>
			<tr>
				<th align="left">City</th>
				<td>[[CC_CITY]]</td>
			</tr>
			<tr>
				<th align="left">State</th>
				<td>[[CC_STATE]]</td>
			</tr>
			<tr>
				<th align="left">Zip/Postal Code</th>
				<td>[[CC_ZIP]]</td>
			</tr>
		</table>';
		
		$item_table = '<table cellspacing="0" border="1" style="width: 100%">
			<tr>
				<th>Qty</th>
				<th>Item</th>
				<th>Price</th>
			</tr>';
		$amount = 0;
		for($i=0; $i<count($this->items); $i++)
		{
			$item_table .=
			'<tr>
				<td>'.$this->items[$i]->qty.'</td>
				<td>'.$this->items[$i]->name.'<br />'.$this->items[$i]->description.'</td>
				<td>$'.number_format($this->items[$i]->amount*$this->items[$i]->qty, 2).'</td>
			</tr>';
			$amount += $this->items[$i]->amount*$this->items[$i]->qty;
		}
		$amount+=$this->shipping;
		$item_table .= 
			'<tr>
				<th colspan="2" align="right">Shipping</th>
				<td>$'.number_format($this->shipping, 2).'</td>
			</tr><tr>
				<th colspan="2" align="right">Total</th>
				<td>$'.number_format($amount, 2).'</td>
			</tr>
		</table>';
		$search = array
		(
			'[[ITEM_TABLE]]',
			'[[BILLING_INFORMATION]]',
			'[[TRANSACTION_ID]]',
			'[[DATE]]',
			'[[TIME]]',
			'[[CC_FIRSTNAME]]',
			'[[CC_LASTNAME]]',
			'[[CC_NUMBER]]',
			'[[CC_EXP_DATE]]',
			'[[CC_ADDRESS]]',
			'[[CC_ADDRESS_2]]',
			'[[CC_CITY]]',
			'[[CC_STATE]]',
			'[[CC_ZIP]]'
		);
		$replace = array
		(
			$item_table,
			$billing_info,
			$this->response['TRANSACTIONID'],
			date('m/d/Y', time()),
			date('h:i:s A', time()),
			htmlentities($this->cc_firstname),
			htmlentities($this->cc_lastname),
			htmlentities(substr($this->cc_number, strlen($this->cc_number)-4)),
			htmlentities($this->cc_exp_month).'/'.htmlentities($this->cc_exp_year),
			htmlentities($this->cc_address),
			htmlentities($this->cc_address_2),
			htmlentities($this->cc_city),
			htmlentities($this->cc_state),
			htmlentities($this->cc_zip)
		);
		
		$html = str_replace($search, $replace, $html);
		return $html;
	}
	
	public function emailReceipt($to, $from, $subject, $template='', $headers='')
	{
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: '.$from. "\r\n";
		
		$html = $this->generateReceipt($template);
		mail($to, $subject, $html, $headers);
	}
}

class PayPalItem
{
	public $name, $amount, $description, $qty;
	public function __construct($name, $description, $amount, $qty=1)
	{
		$this->name = $name;
		$this->amount = $amount;
		$this->description = $description;
		$this->qty = $qty;
	}
}

?>
