<?php
//3245 Estate Golden Rock, 00820, St. Croix
//592 Christina Drive

class Geocoder
{
	public static $placemarks = array();
	public static function geocodeAddress($address)
	{
		$url = 'http://maps.google.com/maps/geo?q='.urlencode($address).'&output=xml&oe=utf8&sensor=false&key='.GOOGLE_MAPS_API_KEY;
		$xml = simplexml_load_file($url);
		
		$xml->registerXPathNamespace('ge', 'http://earth.google.com/kml/2.0');
		$xml->registerXPathNamespace('oas', 'urn:oasis:names:tc:ciq:xsdschema:xAL:2.0');
		
		$status = $xml->xpath('//ge:Status/ge:code');
		
		if ($status[0] == "200") 
		{
			//$i = 1;
			$placemarks = array();
			// Successful geocode
			for($i=1; $i<=count($xml->Response->Placemark); $i++)
			{			
				$country_code = (string)$xml->xpath('//ge:Placemark['.$i.']//oas:CountryNameCode');
				
				$address = implode('', $xml->xpath('//ge:Placemark['.$i.']//ge:address[1]'));
				$street = implode('', $xml->xpath('//ge:Placemark['.$i.']//oas:ThoroughfareName[1]'));
				$locality = $xml->xpath('//ge:Placemark['.$i.']//oas:Locality');
				$subadminarea = $xml->xpath('//ge:Placemark['.$i.']//oas:SubAdministrativeArea');
				if(count($locality)!=0)
					$city = implode('', $xml->xpath('//ge:Placemark['.$i.']//oas:LocalityName[1]'));
				else
					$city = implode('', $xml->xpath('//ge:Placemark['.$i.']//oas:SubAdministrativeAreaName[1]'));					
				
				$state = implode('', $xml->xpath('//ge:Placemark['.$i.']//oas:AdministrativeAreaName'));
				$zip = implode('', $xml->xpath('//ge:Placemark['.$i.']//oas:PostalCodeNumber[1]'));
				$country_name = implode('', $xml->xpath('//ge:Placemark['.$i.']//oas:CountryName[1]'));
				

				$coordinates = implode('', $xml->xpath('//ge:Placemark['.$i.']//ge:coordinates[1]'));
				$coordinatesSplit = explode(",", $coordinates);
				
				$latitude = $coordinatesSplit[1];
				$longitude = $coordinatesSplit[0];
				
				self::$placemarks[] = new Placemark(array
				(
					'country_code'=>$country_code,
					'country_name'=>$country_name,
					'address'=>$address,
					'street'=>$street,
					'city'=>$city,
					'state'=>$state,
					'zip'=>$zip,
					'latitude'=>$latitude,
					'longitude'=>$longitude
				));
			}
		}
		else
			return false;
		
		return self::$placemarks;
	}
	/*
	static function drawMap($center_lat, $center_lng, $zoom, $type_control=true)
	{
		$map_canvas_id='map_canvas'. time();
		?>
		<script type="text/javascript">
			addEvent(window, 'load', function(){new MapSet('<?=$map_canvas_id?>', <?=$center_lat?>, <?=$center_lng?>, <?=$zoom?>, <?=$type_control? 'true':'false'?>)});
		</script>
		<div id="<?=$map_canvas_id?>" style="width:100%; height: 100%;"></div>
	<?			
	}
	*/
}

class Placemark
{
	public $street, $address, $city, $state, $zip, $country_code, $country_name, $latitude, $longitude;
	function __construct($properties)
	{
		foreach($properties as $property=>$value)
		{
			if(property_exists($this, $property))
				$this->{"$property"}=$value;
		}
	}
}

// Debug code - used when calling the include directly
if(isset($_GET['debug']) && isset($_GET['q']))
{
	echo '<pre>';
	print_r(Geocoder::geocodeAddress($_GET['q']));
	echo '</pre>';
}
if(isset($_GET['debug']) && isset($_GET['map']))
{
	drawMap();
}
?>