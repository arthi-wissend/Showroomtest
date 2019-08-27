<?php
require_once("globalFunctions.php");

function getAvailability($url) {
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER => array(
			"Authorization: Bearer " . checkToken()["access_token"],
			"Cache-Control: no-cache",
			"Accept-Language: " . $GLOBALS['config']["storesAPI"]["acceptLanguage"],
			"x-api-key: " . $GLOBALS['config']["storesAPI"]["apiKey"]
		),
	));
	$response = curl_exec($curl);
	curl_close($curl);

	if(isJson($response)) {
		return $response;
	}
}

if(!defined('SHOWROOM_APP')) {
	$storeId = $_GET['storeId'];
	$modelId = $_GET['modelId'];
	$articleId = $_GET['articleId'];

	if(isset($modelId)) {
		$url = "https://api.decathlon.net/stores-stock/v3/stores/".$storeId."/models/".$modelId."/items/stocks";
	}
	if(isset($articleId)) {
		$url = "https://api.decathlon.net/stores-stock/v3/stores/".$storeId."/items/".$articleId."/stocks";
	}
	echo getAvailability($url);
}
?>