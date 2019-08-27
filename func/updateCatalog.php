<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once("globalFunctions.php");

$showroomsId = $GLOBALS['config']['showroomsId'];

function getCatalog($showroomsId) {
	$filepath = dirname(__FILE__, 2) . "/cache/tesseract/catalog.json";
	if (file_exists($filepath) && time()-filemtime($filepath) < 1 * 3600) {
		$content = file_get_contents($filepath);
		$catalog = json_decode($content, true);
	} else {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.decathlon.net/catalogs/categories/export",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array(
				"Authorization: Bearer " . checkToken()["access_token"],
				"Cache-Control: no-cache",
				"Geographic-entity: " . $GLOBALS['config']['tesseractAPI']['country'],
				"x-api-key: " . $GLOBALS['config']['tesseractAPI']['apiKey']
			),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			if(isJson($response)) {
				$catalog = json_decode($response, true);
				$tmp_catalog = array();
				$tmp_sports = array();
				$tmp_categories = array();
				
				foreach($catalog as $category) {
					if($category["id"] == $showroomsId) {
						
						// get childIds (sports) from the main showroom boutique
						foreach($category["childCategoryIds"] as $sport) {
							$tmp_catalog[$category["id"]] = array();
							$tmp_catalog[$category["id"]][] = $category;
							$tmp_sports[] = $sport;
						}
					}
				}
				
				foreach($catalog as $category) {
					if(in_array_r($category["id"], $tmp_sports)) {
						$tmp_catalog[$category["id"]] = array();
						$tmp_catalog[$category["id"]][] = $category;
						
						// get childIds (categories) per sport
						foreach($category["childCategoryIds"] as $category) {
							$tmp_categories[] = $category;
						}
					}
				}
				
				foreach($catalog as $category) {
					if(in_array_r($category["id"], $tmp_categories)) {
						$tmp_catalog[$category["id"]] = array();
						$tmp_catalog[$category["id"]][] = $category;
					}
				}
				
				if(!empty($tmp_catalog)) {
					file_put_contents($filepath, json_encode($tmp_catalog));
				}
				
				$content = file_get_contents($filepath);
				$catalog = json_decode($content, true);
			}
		}
	}
	return $catalog;
}

function updateCatalog($showroomsId) {
	$catalog = getCatalog($showroomsId);
	$all_products = array();
	foreach($catalog[$showroomsId][0]["childCategoryIds"] as $sport) {
		$parentName = $catalog[$sport][0]["name"];
		$tmp_sports = array();
		$tmp_sports["translations"] = $catalog[$sport][0]["translations"];
		$filledCategories = 0;
		$ct = 0;
		foreach($catalog[$sport][0]["childCategoryIds"] as $category) {
			$tmp_products = array();	
			$categoryName = $catalog[$category][0]["name"];
			foreach($catalog[$category][0]["itemIds"] as $dsmCode) {
				$tmp_products[] = $dsmCode;
				$all_products[] = $dsmCode;
			}
			if(count($tmp_products) > 0 ) {
				$tmp_sports["categories"][$categoryName]["itemIds"] = $tmp_products;
				$tmp_sports["categories"][$categoryName]["translations"] = $catalog[$category][0]["translations"];
				$filledCategories++;
			}
			$ct++;
		}
		if($filledCategories > 0 && $ct == count($catalog[$sport][0]["childCategoryIds"])) {
			file_put_contents(dirname(__FILE__, 2) . "/cache/sports/" . str_replace(" ", "_", strtolower(clear_string(str_replace(",", "", $parentName)))) . ".json", json_encode($tmp_sports, JSON_UNESCAPED_UNICODE));
		}
	}
	getAllProductInfos($all_products, 'processResponses');
}

function getAllProductInfos($dsms, $callback, $custom_options = null) {
	// increase max execution time limit by 60 seconds
	set_time_limit(600);
	
	$urls = array();
	for($i = 0; $i < count($dsms); $i++) {
		$urls[] = "https://api-eu.decathlon.net/cube_merchandising/v1/" . $GLOBALS['config']['cubeMerchandisingAPI']['country'] . "/product-details/_/A-m-" . $dsms[$i] . "?locale=" . $GLOBALS['config']['cubeMerchandisingAPI']['locale'] . "&format=json";
		$urls[] = "https://api-eu.decathlon.net/cube_merchandising/v1/" . $GLOBALS['config']['cubeMerchandisingAPI']['country'] . "/product-details/more/_/A-m-" . $dsms[$i] . "?locale=" . $GLOBALS['config']['cubeMerchandisingAPI']['locale'] . "&format=json";
	}
	
    // make sure the rolling window isn't greater than the # of urls
    $rolling_window = 4;
    $rolling_window = (count($urls) < $rolling_window) ? count($urls) : $rolling_window;

    $master = curl_multi_init();
    $curl_arr = array();

    // add additional curl options here
    $std_options = array(
    	CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER => array(
			"Authorization: Bearer " . checkToken()["access_token"],
			"Cache-Control: no-cache",
			"x-api-key: " . $GLOBALS['config']['cubeMerchandisingAPI']['apiKey']
		)
	);
    $options = ($custom_options) ? ($std_options + $custom_options) : $std_options;

    // start the first batch of requests
    for ($i = 0; $i < $rolling_window; $i++) {
        $ch = curl_init();
        $options[CURLOPT_URL] = $urls[$i];
        curl_setopt_array($ch,$options);
        curl_multi_add_handle($master, $ch);
    }

    do {
        while(($execrun = curl_multi_exec($master, $running)) == CURLM_CALL_MULTI_PERFORM);
        if($execrun != CURLM_OK)
            break;
        // a request was just completed -- find out which one
        while($done = curl_multi_info_read($master)) {
            $info = curl_getinfo($done['handle']);
            if ($info['http_code'] == 200)  {
                $output = curl_multi_getcontent($done['handle']);

                // request successful.  process output using the callback function.
                $callback($output);
				
				if($i < count($urls)) {
					// start a new request (it's important to do this before removing the old one)
					$ch = curl_init();
					$options[CURLOPT_URL] = $urls[$i++];  // increment i
					curl_setopt_array($ch, $options);
					curl_multi_add_handle($master, $ch);
				}

                // remove the curl handle that just completed
                curl_multi_remove_handle($master, $done['handle']);
				// close the curl handle that just completed
				curl_close($done['handle']);
            } else {
                // request failed.  add error handling.
				if($i < count($urls)) {
					// start a new request (it's important to do this before removing the old one)
					$ch = curl_init();
					$options[CURLOPT_URL] = $urls[$i++];  // increment i
					curl_setopt_array($ch, $options);
					curl_multi_add_handle($master, $ch);
				}
            }
        }
    } while ($running);
    
    curl_multi_close($master);
    return true;
}

$GLOBALS['tmpProducts'] = array();

function processResponses($response) {
	$response = json_decode($response, true);
	$requestPath = explode('_/', $response['endeca:siteState']['contentPath']);
	
	// extract ref from request
	$ref = str_replace('A-m-', '', $requestPath[1]);
	
	// extract type from request
	$type = (strpos($requestPath[0], 'more') !== false) ? 'moreInfo' : 'productPage';
	
	// merge the responses per ref async
	if(isset($GLOBALS['tmpProducts'][$ref])) {
		$GLOBALS['tmpProducts'][$ref][$type] = $response;
	} else {
		$GLOBALS['tmpProducts'][$ref] = array($type => $response);
	}
	
	// when all requests for the specific product are complete, process the output
	if(isset($GLOBALS['tmpProducts'][$ref]) && count($GLOBALS['tmpProducts'][$ref]) == 2) {
		translateProduct($GLOBALS['tmpProducts'][$ref]['productPage'], $GLOBALS['tmpProducts'][$ref]['moreInfo']);
		unset($GLOBALS['tmpProducts'][$ref]);
	}
}

function translateProduct($productPage, $moreInfo) {
	if(array_key_exists('productInfo', $productPage['PDMainContent'][0])) {
		$dsmCode = $productPage['PDMainContent'][0]['productInfo']['id'];
		
		$averageRating = NULL;
		$ratingDistribution = NULL;
		$ratingNumber = NULL;
		
		if(array_key_exists('averageRating', $productPage['PDMainContent'][0])) {
			$averageRating = $productPage['PDMainContent'][0]['averageRating'];
			$ratingDistribution = $productPage['PDMainContent'][0]['ratingCountDistribution'];
			$ratingNumber = intval($ratingDistribution['1']) + intval($ratingDistribution['2']) + intval($ratingDistribution['3']) + intval($ratingDistribution['4']) + intval($ratingDistribution['5']);
		}

		$brandName = $productPage['PDMainContent'][0]['productInfo']['brandId'];	
		$catchline = $productPage['PDMainContent'][0]['productInfo']['designFor'];
		$description = $productPage['PDMainContent'][0]['productInfo']['longDescription'];
		$title = $productPage['PDMainContent'][0]['productInfo']['description'];

		
		
		$userBenefits = array();
		foreach($moreInfo['PDSupplementalContent'][0]['productAdvantages'] as $advantage) {
			$benefit = array();
			$benefit['contentImage'] = $advantage['pictoLink'];
			$benefit['contentTitle'] = $advantage['title'];
			$benefit['contentValue'] = $advantage['description'];
			$userBenefits[] = $benefit;
		}
		
		$technicalInfo = array();
		foreach($moreInfo['PDSupplementalContent'][1]['productCharacteristic'] as $techInfo) {
			$info = array();
			$info['contentTitle'] = $techInfo['name'];
			$info['contentValue'] = $techInfo['description'];
			$technicalInfo[] = $info;
		}
		
		foreach($productPage['PDMainContent'][0]['models'] as $model) {
			$dest = array(
				'productInfo' => array(
					'averageRating' => $averageRating,
					'brandId' => str_replace('m_', '', $brandName),
					'description' => $title,
					'designFor' => $catchline,
					'id' => $dsmCode,
					'longDescription' => $description,
					'modelCode' => $model['id'],
					'ratingNumber' => $ratingNumber,
				),
				'productAdvantages' => $userBenefits,
				'productCharacteristic' => $technicalInfo,
				'skus' => array(),
				'media' => array(
					'images' => array(),
				),
				'modelUrl' => $model['modelUrl'],
			);

			$images = array();
			foreach($model['media']['images'] as $image) {
				$dest['media']['images'][] = $image['url'];
			}
			
			$tmpAvail = 0;
			foreach($model['skus'] as $variant) {
				if($variant['available'] !== true) {
					$tmpAvail++;
				}
				$dest['skus'][] = array(
					'size' => $variant['size'],
					'activePrice' => (string)$variant['price']['activePrice'],
					'listPrice' => (string)$variant['price']['listPrice'],
					'availableQuantity' => $variant['availableQuantity'],
					'id' => $variant['id'], 

				);
			}
			if($tmpAvail == count($model['skus'])) {
				return;
			}
			file_put_contents(dirname(__FILE__, 2) . "/cache/products/" . $dsmCode . "_" . $model['id'] . ".json", json_encode($dest));
		}
	}
}

function deleteOldFiles($folder, $threshold) {
	$allFiles = scandir($folder);
	$files = array_diff($allFiles, array('.', '..'));
	foreach($files as $file) {
		$filepath = $folder . $file;
		if(time()-filemtime($filepath) > $threshold) {
			unlink($filepath);
		}
	}
}

updateCatalog($showroomsId);
deleteOldFiles(dirname(__FILE__, 2) . "/cache/sports/", 72 * 3600);
deleteOldFiles(dirname(__FILE__, 2) . "/cache/products/", 72 * 3600);
?>