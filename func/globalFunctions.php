<?php
$config = file_get_contents(dirname(__FILE__, 2) . '/.config');
$GLOBALS['config'] = json_decode($config, true);

// set language/locale
$language = $GLOBALS['config']['locale'];
putenv("LANG=" . $language);
setlocale(LC_MESSAGES, $language);

// Set the text domain as "messages"
$domain = "messages";
bindtextdomain($domain, dirname(__FILE__, 2) . "/locale");
bind_textdomain_codeset($domain, 'UTF-8');

textdomain($domain);

function compressCSS($cssFiles) {
	$buffer = "";
	foreach ($cssFiles as $cssFile) {
	  $buffer .= file_get_contents($cssFile);
	}
	// Remove comments
	$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
	// Remove space after colons
	$buffer = str_replace(': ', ':', $buffer);
	// Remove whitespace
	$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

	return $buffer;
}

function checkToken() {
	$filepath = dirname(__FILE__, 2) . '/cache/JWTtoken.json';
	if (file_exists($filepath) && time()-filemtime($filepath) < 2 * 3600) {
		$content = file_get_contents($filepath);
		$token = json_decode($content, true);
	} else {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://idpdecathlon.oxylane.com/as/token.oauth2',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => 'grant_type=password&username=' . $GLOBALS['config']['oauth2']['username'] .'&password=' . $GLOBALS['config']['oauth2']['password'] .'&scope=openid%20profile',
			CURLOPT_HTTPHEADER => array(
				'Authorization: Basic ' . base64_encode($GLOBALS['config']['oauth2']['username'] . ':' . $GLOBALS['config']['oauth2']['secret']),
				'Cache-Control: no-cache',
				'Content-Type: application/x-www-form-urlencoded'
			),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			echo 'cURL Error #:' . $err;
		} else {
			if(isJson($response)) {
				$token = json_decode($response, true);
				file_put_contents($filepath, $response);
			}
		}
	}
	return $token;
}

function isJson($string) {
	json_decode($string);
	return (json_last_error() === JSON_ERROR_NONE);
}

function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }
    return false;
}

function clear_string($str, $how = '-'){
	$search = array("ä", "ö", "ü", "ß", "Ä", "Ö",
					"Ü", "&", "é", "á", "ó", "É", "Á", "Ó",
					" :)", " :D", " :-)", " :P",
					" :O", " ;D", " ;)", " ^^",
					" :|", " :-/", ":)", ":D",
					":-)", ":P", ":O", ";D", ";)",
					"^^", ":|", ":-/", "(", ")", "[", "]",
					"<", ">", "!", "\"", "§", "$", "%", "&",
					"/", "(", ")", "=", "?", "`", "´", "*", "'",
					"_", ":", ";", "²", "³", "{", "}",
					"\\", "~", "#", "+", ".", ",",
					"=", ":", "=)");
	$replace = array("ae", "oe", "ue", "ss", "Ae", "Oe",
					 "Ue", "und", "e", "a", "o", "E", "A", 
					 "O", "", "", "", "", "", "", "", "", 
					 "", "", "", "", "", "", "", "", "",
					 "", "", "", "", "", "", "", "", "",
					 "", "", "", "", "", "", "", "", "",
					 "", "", "", "", "", "", "", "", "",
					 "", "", "", "", "", "", "", "", "", 
					 "", "", "", "");
	$str = str_replace($search, $replace, $str);
	$str = strtolower(preg_replace("/[^a-zA-Z0-9]+/", trim($how), $str));
	return $str;
}

function links2QR($str) {
	return preg_replace_callback('/<a[^>]*>([^<]+)<\/a>/', function($links){
		preg_match_all('~<a(.*?)href="([^"]+)"(.*?)>~', $links[0], $link);
		return ' <a class="qr-link" data-target="' . $link[2][0] . '">' . $links[1] . '</a> ';
	}, $str);
}

function getLatestUpdate($dir, &$results = array()){
    $files = scandir($dir);
    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
		if(!strpos($path, 'cache')) {
			if(!is_dir($path)) {
				$results[] = filemtime($path);
			} else if($value != "." && $value != "..") {
				getLatestUpdate($path, $results);
			}
		}
    }
	rsort($results);
    return $results[0];
}

function simplifyOpeningHours($hours) {
	$week = $hours;
	foreach($week as $day) {
		if(!empty($day['amStartTime']) || !empty($day['pmStartTime'])) {
			$openingHours[$day['day']] = array(!empty($day['amStartTime']) ? substr_replace($day['amStartTime'],':',-2,0) : substr_replace($day['pmStartTime'],':',-2,0), !empty($day['amEndTime']) ? substr_replace($day['amEndTime'],':',-2,0) : substr_replace($day['pmEndTime'],':',-2,0));
		} else {
			$openingHours[$day['day']] = array('00:00', '00:00');
		}
	}
//	$exceptionalOpeningHours = array();
//	$exceptionalWeek = unserialize($row['exceptionalSchedules']);
//	$ct = 0;
//	foreach($exceptionalWeek['exceptionalOpenning'] as $exceptionalDay) {
//		if(!empty($exceptionalDay)) {
//			if(!empty($exceptionalDay['amStartTime']) || !empty($exceptionalDay['pmStartTime'])) {
//				$exceptionalOpeningHours[$ct]['date'] = $exceptionalDay['date'];
//				$exceptionalOpeningHours[$ct]['name'] = $exceptionalDay['name'];
//				$exceptionalOpeningHours[$ct][0] = !empty($exceptionalDay['amStartTime']) ? substr_replace($exceptionalDay['amStartTime'],':',-2,0) : substr_replace($exceptionalDay['pmStartTime'],':',-2,0);
//				$exceptionalOpeningHours[$ct][1] = !empty($exceptionalDay['amEndTime']) ? substr_replace($exceptionalDay['amEndTime'],':',-2,0) : substr_replace($exceptionalDay['pmEndTime'],':',-2,0);
//			}
//		}
//		$ct++;
//	}

	$amountOfDays = count($openingHours);
	$arrayKeys = array_keys($openingHours);
	$days = array();
	$dayNames = array('SUNDAY' => 'So', 'MONDAY' => 'Mo','TUESDAY' => 'Di','WEDNESDAY' => 'Mi','THURSDAY' => 'Do','FRIDAY' => 'Fr','SATURDAY' => 'Sa');
	for($dayCount = 0; $dayCount < $amountOfDays; $dayCount++) {
		$DayAmountOfConsecutiveSameHours = 1;
		while(isset($arrayKeys[($dayCount+$DayAmountOfConsecutiveSameHours)]) && ($openingHours[$arrayKeys[$dayCount]] === $openingHours[$arrayKeys[($dayCount+$DayAmountOfConsecutiveSameHours)]]))
			$DayAmountOfConsecutiveSameHours++;

		if($DayAmountOfConsecutiveSameHours > 1)
			$days[$dayNames[$arrayKeys[$dayCount]] . ' - ' . $dayNames[$arrayKeys[($dayCount+$DayAmountOfConsecutiveSameHours-1)]]] = $openingHours[$arrayKeys[$dayCount]];
		else
			$days[$dayNames[$arrayKeys[$dayCount]]] = $openingHours[$arrayKeys[$dayCount]];

		$dayCount += ($DayAmountOfConsecutiveSameHours - 1);
	}

	$opening = array();
	$keys = array_keys($days);
	for($ct = 0; $ct < count($keys); $ct++) {
		if($days[$keys[$ct]][0] == '00:00' && $days[$keys[$ct]][1] == '00:00') {
			if($keys[$ct] != 'So') {
				array_push($opening, array($keys[$ct], $days[$keys[$ct]][0].' - '.$days[$keys[$ct]][1]));
			}
		} else {
			array_push($opening, array($keys[$ct], $days[$keys[$ct]][0].' - '.$days[$keys[$ct]][1]));
			if($ct >= 0 && $ct < (count($keys)-2)) {
				//$rawopening .= ',';
			}
		}
	}
	return $opening;
}

function getStores() {
	$filepath = dirname(__FILE__, 2) . '/cache/stores.json';
	if (file_exists($filepath) && time()-filemtime($filepath) < 12 * 3600) {
		$content = file_get_contents($filepath);
		$stores = json_decode($content, true);
	} else {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'http://mystore.oxylane.com/mystore-server-mvc/ajax/store?callerId=' . $GLOBALS['config']["mystoreAPI"]["username"] . '&type=standard&country=' . $GLOBALS['config']["mystoreAPI"]["country"] . '&extraInfos=address,openingHours',
			CURLOPT_RETURNTRANSFER => true,
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			echo 'cURL Error #:' . $err;
		} else {
			if(isJson($response)) {
				$stores = json_decode($response, true)['data'];
				usort($stores, function($a, $b) {
					return $a['name'] <=> $b['name'];
				});

				foreach($stores as $store => $values) {
					if($stores[$store]['temporaryClosed'] == 1) {
						unset($stores[$store]);
					}
				}
				file_put_contents($filepath, json_encode($stores));
			}
		}
	}
	return $stores;
}

// Encode a string to URL-safe base64
function encodeBase64UrlSafe($value) {
  return str_replace(array('+', '/'), array('-', '_'),
    base64_encode($value));
}

// Decode a string from URL-safe base64
function decodeBase64UrlSafe($value) {
  return base64_decode(str_replace(array('-', '_'), array('+', '/'),
    $value));
}

// Sign a URL with a given crypto key
// Note that this URL must be properly URL-encoded
function signUrl($myUrlToSign, $privateKey) {
  // parse the url
  $url = parse_url($myUrlToSign);

  $urlPartToSign = $url['path'] . "?" . $url['query'];

  // Decode the private key into its binary format
  $decodedKey = decodeBase64UrlSafe($privateKey);

  // Create a signature using the private key and the URL-encoded
  // string using HMAC SHA1. This signature will be binary.
  $signature = hash_hmac("sha1",$urlPartToSign, $decodedKey,  true);

  $encodedSignature = encodeBase64UrlSafe($signature);

  return $myUrlToSign."&signature=".$encodedSignature;
}
?>