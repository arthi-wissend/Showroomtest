<?php
require_once("globalFunctions.php");

function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo){
	$earthRadius = 6371; // in kilometers

	// convert from degrees to radians
	$latFrom = deg2rad($latitudeFrom);
	$lonFrom = deg2rad($longitudeFrom);
	$latTo = deg2rad($latitudeTo);
	$lonTo = deg2rad($longitudeTo);

	$latDelta = $latTo - $latFrom;
	$lonDelta = $lonTo - $lonFrom;

	$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
	cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
	return $angle * $earthRadius;
}

function getDirections($url, $originStoreId, $destinationStoreId, $destinationStoreName) {
	$filename = $destinationStoreId . '.json';
	if (!file_exists('cache/directions/' . $originStoreId)) {
		mkdir('cache/directions/' . $originStoreId, 0777, true);
	}
	if (file_exists('cache/directions/' . $originStoreId . '/' . $filename) && time()-filemtime('cache/directions/' . $originStoreId . '/' . $filename) < 7 * 24 * 3600) {
		$directions = json_decode(file_get_contents('cache/directions/' . $originStoreId . '/' . $filename), true);
	} else {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		if(isJson($response)) {
			$directions = array();
			array_push($directions, json_decode($response, true)['routes'][0]['legs'][0]['distance']);
			array_push($directions, json_decode($response, true)['routes'][0]['legs'][0]['duration']);

			file_put_contents('cache/directions/' . $originStoreId . '/' . $filename, json_encode($directions));
		}
	}
	return $directions;
}

foreach($stores as $store) {
	if($store['storeId'] == "007" . str_pad($originStoreId, 5, '0', STR_PAD_LEFT) . str_pad($originStoreId, 5, '0', STR_PAD_LEFT)) {
		$originStoreLocation = $store['address']['gps']['latitude'] . ',' . $store['address']['gps']['longitude'];
	}
}

$nextStores = array();
foreach($stores as $store) {
	if($store['storeId'] != "007" . str_pad($originStoreId, 5, '0', STR_PAD_LEFT) . str_pad($storeId, 5, '0', STR_PAD_LEFT) && isset($store['address']['gps']) && $store['address']['gps']['latitude'] != "0" && $store['address']['gps']['longitude'] != "0") {
		$destinationStoreLocation = $store['address']['gps']['latitude'] . ',' . $store['address']['gps']['longitude'];

		$originCoords = explode(",",$originStoreLocation);
		if(round(haversineGreatCircleDistance($originCoords[0], $originCoords[1], $store['address']['gps']['latitude'], $store['address']['gps']['longitude'])) < 100) {

			$url = signUrl("https://maps.googleapis.com/maps/api/directions/json?origin=" . $originStoreLocation . "&alternatives=false&units=metric&destination=" . $destinationStoreLocation . "&client=" . $GLOBALS['config']["googleMapsAPI"]["client"] . "&channel=" . $GLOBALS['config']["googleMapsAPI"]["channel"], $GLOBALS['config']["googleMapsAPI"]["cryptoKey"]);

			$directions = getDirections($url, $originStoreId, ltrim(substr($store['storeId'],-5),'0'), $store['name']);
			if($directions[1]['value'] < 2400) {
				array_push($nextStores, array('storeId' => ltrim(substr($store['storeId'],-5),'0'), 'name' => $store['name'], 'distance' => $directions[0]['text'], 'duration' => $directions[1]['text'], 'address' => $store['address'], 'hours' => $store['openingHours']));
			}
		}
	}
}

usort($nextStores, function($a, $b) {
    return $a['distance'] <=> $b['distance'];
});

return $nextStores;
?>