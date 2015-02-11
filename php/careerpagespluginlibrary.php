<?php
class CareerpagesLibrary {


	public static function testEmail($email) {
		$output = true; 
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$output = false; 
		}
		
		return $output;
	}

	public static function extractVanityurl($vanityurl, $baseurl) {
		$urlArr = explode($baseurl, $vanityurl);
		$vanityurl = $urlArr[count($urlArr) - 1];
		
		return $vanityurl;
	}
	
	public static function getStaticmap($latitude, $longitude, $zoom, $w, $h, $marker) {
		return '//maps.googleapis.com/maps/api/staticmap?center='.$latitude.','.$longitude.'&zoom='.$zoom.'&size='.$w.'x'.$h.'&maptype=roadmap&markers=color:'.$marker.'%7Ccolor:'.$marker.'%7C'.$latitude.','.$longitude.'&sensor=false';
	}

	public static function isSecure() {
		$isSecure = false;
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
				$isSecure = true;
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
				$isSecure = true;
		}

		return $isSecure;
	}

	public static function get_client_ip() {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) { // check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { // to check if ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	
	public static function randomString($length) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";	

		$str = "";
		$size = strlen($chars);
		for($i = 0; $i < $length; $i++) {
			$str .= $chars[rand(0, $size - 1)];
		}

		return $str;
	}
	
	public static function randomStringLowercase($length) {
		$chars = "abcdefghijklmnopqrstuvwxyz";	

		$str = "";
		$size = strlen($chars);
		for($i = 0; $i < $length; $i++) {
			$str .= $chars[rand(0, $size - 1)];
		}

		return $str;
	}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////                                //////////////////////////////////////////////////////////////////////////////////////////
/////////////       Geo time functions       //////////////////////////////////////////////////////////////////////////////////////////
/////////////                                //////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public static function getTime($date, $timezoneid) {
		$timeUTC = array();
		if ($date && $timezoneid) {
			$dateObj = new DateTime("@$date");
			/*if ($timezoneid) {*/ $dateObj->setTimezone(new DateTimeZone($timezoneid)); //}
			$timeUTC["date"] = $dateObj->format("j. M. Y");
			$timeUTC["shortdate"] = $dateObj->format("M. Y");
			$timeUTC["year"] = $dateObj->format("Y");
			$timeUTC["time"] = $dateObj->format("h:i a");
			$timeUTC["exacttime"] = $dateObj->format("H:i:s");
			$timeUTC["offset"] = $dateObj->format("P");
			$timeUTC["unix"] = $dateObj->format("U");
			$timeUTC["test"] = time($date);
			$timeUTC["twitter"] = $dateObj->format("j M");
		} else {
			$timeUTC["date"] = "";
			$timeUTC["shortdate"] = "";
			$timeUTC["year"] = "";
			$timeUTC["time"] = "";
			$timeUTC["exacttime"] = "";
			$timeUTC["offset"] = "";
			$timeUTC["unix"] = "";
			$timeUTC["test"] = "";
			$timeUTC["twitter"] = "";
		}
		
		return $timeUTC;
	}
	
	public static function getDiffTime($startdate, $enddate) {
		$diff = array();
 		if ($startdate && $enddate) {
			$diffStart = new DateTime("@$startdate");
			$diffEnd = new DateTime("@$enddate");
			$diffDate = date_diff($diffStart, $diffEnd);
			$diffYears = $diffDate->format("%y");
			$diffMonths = $diffDate->format("%m");
			$diffDays = $diffDate->format("%d");
			$diff["year"] = $diffYears.($diffYears > 1 ? " years" : " year");
			$diff["yearmonth"] = ($diffYears == "0" ? "" : ($diffYears == "1" ? $diffYears." year " : $diffYears." years ")).$diffMonths.($diffMonths == 1 ? " month" : " months");
			$diff["yearmonthshort"] = ($diffYears == "0" ? "" : ($diffYears == "1" ? $diffYears." yrs " : $diffYears." yrs ")).$diffMonths.($diffMonths == 1 ? " mth" : " mth");
			$diff["yearmonthday"] = ($diffYears == "0" ? "" : ($diffYears == "1" ? $diffYears." year " : $diffYears." years ")).($diffMonths == "0" ? "" : ($diffMonths == "1" ? $diffMonths." month " : $diffMonths." months ")).($diffDays == "0" ? "0 days" : ($diffDays == "1" ? $diffDays." day " : $diffDays." days"));
		} else {
			$diff["year"] = "";
			$diff["yearmonth"] = "";
			$diff["yearmonthday"] = "";
		}
		
		return $diff;
	}

	public static function getFormattedaddress($addresscomponents) {
		$formattedaddress = array();

		$formattedaddress["CO"] = null;
		$formattedaddress["CI, CO"] = null;
		$formattedaddress["ST-NU"] = null;
		$formattedaddress["PO-CI"] = null;
		$formattedaddress["ST, CI, CO"] = null;
		$formattedaddress["ST-NU, CI, CO"] = null;
		$formattedaddress["ST, CI, AL3, AL2, AL1"] = null;
		$formattedaddress["ST, CI, AL3, AL2, AL1, CO"] = null;
		$formattedaddress["ST-NU, CI, AL3, AL2, AL1, CO"] = null;

		if ($addresscomponents) {
			$addresscomponents = json_decode($addresscomponents, true);
			$adressArray = self::getAddressFromAddresscomponents($addresscomponents);

			$streetnumberArr = array();
			if(isset($adressArray["street"]["long"]) && $adressArray["street"]["long"]) $streetnumberArr[] = $adressArray["street"]["long"];
			if(isset($adressArray["number"]["long"]) && $adressArray["number"]["long"]) $streetnumberArr[] = $adressArray["number"]["long"];
			$streetnumber = implode(" ", $streetnumberArr);
			
			$zipcityArr = array();
			if(isset($adressArray["zipcode"]["long"]) && $adressArray["zipcode"]["long"]) $zipcityArr[] = $adressArray["zipcode"]["long"];
			if(isset($adressArray["city"]["long"]) && $adressArray["city"]["long"]) $zipcityArr[] = $adressArray["city"]["long"];
			$zipcity = implode(" ", $zipcityArr);

			if ($adressArray) {
				// CO (country)
				$addressArr = array();
				if(isset($adressArray["country"]["long"]) && $adressArray["country"]["long"]) $addressArr[] = $adressArray["country"]["long"];
				$formattedaddress["CO"] = implode(", ", $addressArr);
				// CI, CO (city, country)
				$addressArr = array();
				if(isset($adressArray["city"]["long"]) && $adressArray["city"]["long"]) $addressArr[] = $adressArray["city"]["long"];
				if(isset($adressArray["country"]["long"]) && $adressArray["country"]["long"]) $addressArr[] = $adressArray["country"]["long"];
				$formattedaddress["CI, CO"] = implode(", ", $addressArr);
				// ST-NU (street, number)
				$addressArr = array();
				if ($streetnumber) $addressArr[] = $streetnumber;
				$formattedaddress["ST-NU"] = implode(" ", $addressArr);
				// PO-CI (zip, city)
				$addressArr = array();
				if(isset($adressArray["zipcode"]["long"]) && $adressArray["zipcode"]["long"]) $addressArr[] = $adressArray["zipcode"]["long"];
				if(isset($adressArray["city"]["long"]) && $adressArray["city"]["long"]) $addressArr[] = $adressArray["city"]["long"];
				$formattedaddress["PO-CI"] = implode(" ", $addressArr);
				// ST, CI, CO (street, city, country)
				$addressArr = array();
				if(isset($adressArray["street"]["long"]) && $adressArray["street"]["long"]) $addressArr[] = $adressArray["street"]["long"];
				if(isset($adressArray["city"]["long"]) && $adressArray["city"]["long"]) $addressArr[] = $adressArray["city"]["long"];
				if(isset($adressArray["country"]["long"]) && $adressArray["country"]["long"]) $addressArr[] = $adressArray["country"]["long"];
				$formattedaddress["ST, CI, CO"] = implode(", ", $addressArr);
				// ST-NU, CI, CO (street-number, city, country)
				$addressArr = array();
				if ($streetnumber) $addressArr[] = $streetnumber;
				if(isset($adressArray["city"]["long"]) && $adressArray["city"]["long"]) $addressArr[] = $adressArray["city"]["long"];
				if(isset($adressArray["country"]["long"]) && $adressArray["country"]["long"]) $addressArr[] = $adressArray["country"]["long"];
				$formattedaddress["ST-NU, CI, CO"] = implode(", ", $addressArr);
				// ST-NU, PO-CI (street-number, zip-city)
				$addressArr = array();
				if ($streetnumber) $addressArr[] = $streetnumber;
				if ($zipcity) $addressArr[] = $zipcity;
				$formattedaddress["ST-NU, PO-CI"] = implode(", ", $addressArr);
				// ST, CI, AL3, AL2, AL1, CO (street, city, al3, al2, al1, country)
				$addressArr = array();
				if(isset($adressArray["street"]["long"]) && $adressArray["street"]["long"]) $addressArr[] = $adressArray["street"]["long"];
				if(isset($adressArray["city"]["long"]) && $adressArray["city"]["long"]) $addressArr[] = $adressArray["city"]["long"];
				if(isset($adressArray["al3"]["long"]) && $adressArray["al3"]["long"]) $addressArr[] = $adressArray["al3"]["long"];
				if(isset($adressArray["al2"]["long"]) && $adressArray["al2"]["long"]) $addressArr[] = $adressArray["al2"]["long"];
				if(isset($adressArray["al1"]["long"]) && $adressArray["al1"]["long"]) $addressArr[] = $adressArray["al1"]["long"];
				if(isset($adressArray["country"]["long"]) && $adressArray["country"]["long"]) $addressArr[] = $adressArray["country"]["long"];
				$formattedaddress["ST, CI, AL3, AL2, AL1, CO"] = implode(", ", $addressArr);
				// ST-NU, CI, AL3, AL2, AL1, CO (street, city, al3, al2, al1, country)
				$addressArr = array();
				if ($streetnumber) $addressArr[] = $streetnumber;
				if(isset($adressArray["city"]["long"]) && $adressArray["city"]["long"]) $addressArr[] = $adressArray["city"]["long"];
				if(isset($adressArray["al3"]["long"]) && $adressArray["al3"]["long"]) $addressArr[] = $adressArray["al3"]["long"];
				if(isset($adressArray["al2"]["long"]) && $adressArray["al2"]["long"]) $addressArr[] = $adressArray["al2"]["long"];
				if(isset($adressArray["al1"]["long"]) && $adressArray["al1"]["long"]) $addressArr[] = $adressArray["al1"]["long"];
				if(isset($adressArray["country"]["long"]) && $adressArray["country"]["long"]) $addressArr[] = $adressArray["country"]["long"];
				$formattedaddress["ST-NU, CI, AL3, AL2, AL1, CO"] = implode(", ", $addressArr);
			}
		}
		
		return $formattedaddress;
	}
	
	public static function getAddressFromAddresscomponents($addresscomponents) {
		$typesAccepted = array("street_number" => "number", "route" => "street", "neighborhood" => "area", "locality" => "city", "administrative_area_level_3" => "al3", "administrative_area_level_1" => "al2", "administrative_area_level_1" => "al1", "country" => "country", "postal_code_prefix" => "zipcode");
		
		$address = array();

		if (is_array($addresscomponents)) {
			foreach($addresscomponents as $component) {
				foreach($component["types"] as $type) {
					if (array_key_exists ($type, $typesAccepted)) {
						$longshort = array();
						$longshort["long"] = $component["long_name"];
						$longshort["short"] = $component["short_name"];
						$address[$typesAccepted[$type]] = $longshort;
						break;
					}
				}
			}
		}

		return $address;
	}
	
	/*// Gets the url to an uploaded image, if no image is present returns an image placeholder
	public static function getCareerpagesImage($filename, $template) {
		return $filename ? self::getUploadimageurl($filename) : self::getCareerpagesImageurl('image-placeholder900x600.png', $template);
	}

	// Gets an the url to a profile image, if no image is present returns an image placeholder
	public static function getCareerpagesProfileimage($filename, $mediaid, $template) {
		return $filename ? ($mediaid ? $filename : self::getSiteUrl()."/common/uploadimages/".$filename) : self::getCareerpagesImageurl('image-placeholder310x310.png', $template);
	}

	// Gets an the url to an template image, if no image is present returns an image placeholder
	public static function getCareerpagesImageurl($filename, $template) {
		return self::getSiteUrl().'/common/careerpages/templates/'.$template.'/images/'.$filename;
	}

	public static function getUploadimageurl($filename) {
		return self::getSiteUrl().'/common/uploadimages/'.$filename;
	}*/

	public static function getProfileimageurl($imageurl, $mediaid) {
		global $cp_fix;
		
		if ($imageurl) {
			if($mediaid) {
				$url = $imageurl;
			} else {
				$url = 'https://'.(isset($cp_fix["subdir"]) && $cp_fix["subdir"] ? $cp_fix["subdir"].'.' : '').'prodii.com/common/uploadimages/'.$imageurl;
			}
		} else {
			if($cp_fix["local"]) {
				$url = $cp_fix["localpluginurl"].'templates/'.$cp_fix["template"].'/images/'.$cp_fix["profileimgplaceholder"];
			} else {
				$url = 'https://'.(isset($cp_fix["subdir"]) && $cp_fix["subdir"] ? $cp_fix["subdir"].'.' : '').'prodii.com/common/careerpages/templates/'.$cp_fix["template"].'/images/'.$cp_fix["profileimgplaceholder"];
			}
		}
		
		return $url;
	}

	public static function getTeamimageurl($filename) {
		global $cp_fix;

		if ($filename) {
			$url = 'https://'.(isset($cp_fix["subdir"]) && $cp_fix["subdir"] ? $cp_fix["subdir"].'.' : '').'prodii.com/common/uploadimages/'.$filename;
		} else {
			if($cp_fix["local"]) {
				$url = $cp_fix["localpluginurl"].'templates/'.$cp_fix["template"].'/images/'.$cp_fix["teamimgplaceholder"];
			} else {
				$url = 'https://'.(isset($cp_fix["subdir"]) && $cp_fix["subdir"] ? $cp_fix["subdir"].'.' : '').'prodii.com/common/careerpages/templates/'.$cp_fix["template"].'/images/'.$cp_fix["teamimgplaceholder"];
			}
		}
		
		return $url;
	}

	public static function getCompanyimageurl($filename) {
		global $cp_fix;
		
		if ($filename) {
			$url = 'https://'.(isset($cp_fix["subdir"]) && $cp_fix["subdir"] ? $cp_fix["subdir"].'.' : '').'prodii.com/common/uploadimages/'.$filename;
		} else {
			if($cp_fix["local"]) {
				$url = $cp_fix["localpluginurl"].'templates/'.$cp_fix["template"].'/images/'.$cp_fix["companyimgplaceholder"];
			} else {
				$url = 'https://'.(isset($cp_fix["subdir"]) && $cp_fix["subdir"] ? $cp_fix["subdir"].'.' : '').'prodii.com/common/careerpages/templates/'.$cp_fix["template"].'/images/'.$cp_fix["companyimgplaceholder"];
			}
		}
		
		return $url;
	}
	
	public static function getSiteUrl() {
		$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
		$protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")) . $s;
		$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
		$uri = $protocol . "://" . $_SERVER['SERVER_NAME']; // . $port;

		return $uri;
	}
}
?>