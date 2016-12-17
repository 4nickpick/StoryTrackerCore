<?php
class ConstantContact
{
	const COLLECTION_ACTIVITIES='activities';
	const COLLECTION_CAMPAIGNS='campaigns';
	const COLLECTION_CONTACTS='contacts';
	const COLLECTION_EMAIL_ADDRESSES='emailaddresses';
	const COLLECTION_EVENTS='events';
	const COLLECTION_LISTS='lists';
	const COLLECTION_MEMBERS='members';
	
	protected $api_key, $username, $password;
	
	function __construct($username, $password, $api_key)
	{
		$this->api_key=$api_key;
		$this->username=$username;
		$this->password=$password;
	}
	
	function getCampaigns()
	{
		if($o=$this->sendRequest(self::COLLECTION_CAMPAIGNS))
		{
			$a=array();
			$i=0;
			foreach($o->entry as $entry)
			{
				$a[$i]=$entry->content->Campaign;
				$a[$i]->id=preg_replace('/^.*\//', '', (string)$a[$i]['id']);
				/*$a[$i]->Name=(string)$entry->content->Campaign->Name;
				$a[$i]->Status=(string)$entry->content->Campaign->Status;
				$a[$i]->Date=strtotime((string)$entry->content->Campaign->Date);*/
				$i++;
			}
			
			return $a;
		}
		return false;
	}
	
	function getCampaign($id)
	{
		if($o=$this->sendRequest(self::COLLECTION_CAMPAIGNS, 'get', $id))
		{
			$a=$o->content->Campaign;
			$a->id=preg_replace('/^.*\//', '', (string)$a['id']);
			return $a;
		}
		return false;
	}
	
	function getLists()
	{
		// TODO: Make this return an actual object instead of a bunch of Atom crap
		return $this->sendRequest(self::COLLECTION_LISTS);
	}
	
	function addContact($list, $email, $first_name='', $last_name='')
	{
		$xml=
			'<entry xmlns="http://www.w3.org/2005/Atom">
				<title type="text"></title>
				<updated>'.date('c').'</updated>
				<author>'.$this->username.'</author>
				<id>data:,none</id>
				<summary type="text">Contact</summary>
				<content type="application/vnd.ctct+xml">
					<Contact xmlns="http://ws.constantcontact.com/ns/1.0/">
						<EmailAddress>'.htmlentities($email).'</EmailAddress>
						<FirstName>'.htmlentities($first_name).'</FirstName>
						<LastName>'.htmlentities($last_name).'</LastName>
						<OptInSource>ACTION_BY_CONTACT</OptInSource>
						<ContactLists>
							<ContactList id="http://api.constantcontact.com/ws/customers/'.$this->username.'/lists/'.htmlentities($list).'" />
						</ContactLists>
					</Contact>
				</content>
			</entry>';
		
		return $this->sendRequest(self::COLLECTION_CONTACTS, 'post', $xml);
	}
	
	protected function sendRequest($collection, $request_method='get', $request='')
	{
		$curl=curl_init();
		curl_setopt($curl, CURLOPT_URL, 'https://api.constantcontact.com/ws/customers/'.urlencode($this->username).'/'.urlencode($collection));
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, $this->api_key.'%'.$this->username.':'.$this->password);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/atom+xml'));
		curl_setopt($curl, CURLOPT_FAILONERROR, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		switch(strtolower($request_method))
		{
			case 'get':
				if(!empty($request))
					curl_setopt($curl, CURLOPT_URL, 'https://api.constantcontact.com/ws/customers/'.urlencode($this->username).'/'.urlencode($collection).'/'.urlencode($request));
				curl_setopt($curl, CURLOPT_HTTPGET, 1);
			break;
			case 'post':
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
			break;
			/*
			case 'put':
				$tmpfile = tmpfile();
				fwrite($tmpfile, $parameter);
				fseek($tmpfile, 0);
				curl_setopt($curl, CURLOPT_INFILE, $tmpfile);
				curl_setopt($curl, CURLOPT_PUT, 1);
				curl_setopt($curl, CURLOPT_INFILESIZE, strlen($parameter));
				fclose($tmpfile);
			break;
			case 'delete':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
			break;*/
		}
		$response=curl_exec($curl);
		$http_code=curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if(!($xml_response=strstr($response, '<?xml')))
			$xml_response=false;
		
		curl_close($curl);
		
		switch($http_code)
		{
			case 200:
				if(!empty($xml_response))
					return simplexml_load_string($xml_response);
			break;
			case 201:
			case 202:
			case 409:
				return true;
			break;
		}
		
		Console::add($response, $http_code);
		return false;
	}
}

/*
I took the easy way out here. Please forgive me!
class ConstantContactObject
{
	function __construct($content)
	{
		
	}
}*/
?>
