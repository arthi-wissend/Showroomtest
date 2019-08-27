<?php
$pathLevel = '../';

$storeId = $params[0];
$sport = $params[1];

foreach($stores as $store) {
	if($store['storeId'] == "007" . str_pad($storeId, 5, '0', STR_PAD_LEFT) . str_pad($storeId, 5, '0', STR_PAD_LEFT)) {
		$storeName = $store['name'];
		$originStoreId = $storeId;
	}
}
$nextStores = require("func/getDirections.php");

switch ($sport) {
	case 'cycling':
		$sportImage = "https://contents.mediadecathlon.com/s596605/k$14e41b43259d4592acdeb7001336fc97/BG+mountainbike+desktop.jpg";
		break;
}
?>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<link href='//fonts.googleapis.com/css?family=Roboto+Condensed:400,400i,700,700i' rel='stylesheet' type='text/css'>
<link href='//fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet' type='text/css'>

<?php
$cssFiles = array(
	dirname(__FILE__, 1) . '/assets/css/slick-min.css',
	dirname(__FILE__, 1) . '/assets/css/showroom.css'
);
echo '<style type="text/css">
@font-face {
	font-family: "DecathlonCube";
	src: url("../assets/fonts/DecathlonCube.eot");
	src: url("../assets/fonts/DecathlonCube.eot?#iefix") format("embedded-opentype"), url("../assets/fonts/DecathlonCube.woff2") format("woff2"), url("../assets/fonts/DecathlonCube.woff") format("woff"), url("fonts/DecathlonCube.ttf") format("truetype"), url("../assets/fonts/DecathlonCube.svg#DecathlonCube") format("svg");
	font-weight: normal;
	font-style: normal
}' . compressCSS($cssFiles) . '</style>';

echo '<script>
var ecomWebsite = "'.$GLOBALS['config']['website'].'";
var freeShippingThreshold = '.$GLOBALS['config']['freeShippingThreshold'].';
var shippingFrom = "'.$GLOBALS['config']['shippingFrom'].'";
var screenSaverTimeout = '.$GLOBALS['config']['screenSaverTimeout'].';
var applicationPath = "'.$GLOBALS['config']['path'].'";
var locale = "'.$GLOBALS['config']['locale'].'";
</script>';
?>

<?php
if(file_exists(dirname(__FILE__, 1) . "/cache/sports/" . $sport . ".json")) {
	$productList = json_decode(file_get_contents(dirname(__FILE__, 1) . "/cache/sports/" . $sport . ".json"), true);
	if(isset($_COOKIE['ds_cat_sort'])) {
		$catSorting = json_decode(base64_decode($_COOKIE['ds_cat_sort']), true);
		$productList['categories'] = array_merge(array_flip($catSorting), $productList['categories']);
	}
?>
	<script>
	localStorage.setItem('storeId', '<?php echo $storeId;?>');
	localStorage.setItem('sport', '<?php echo $sport;?>');
	</script>
<?php } else { ?>
	<script>
	window.localStorage.clear();
	deleteCookie('ds_cat_sort');
	window.location.href = applicationPath;
	</script>
<?php } ?>

<!-- Google Analytics -->
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

<?php
if($GLOBALS['config']["googleAnalytics"]["trackingActive"] === true) {
	echo "ga('create', '" . $GLOBALS['config']["googleAnalytics"]["propertyID"] . "', 'auto');";
	echo "ga('send', 'pageview');";
}
?>
</script>
<!-- End Google Analytics -->
</head>

<div class="main-container" data-store-id="<?php echo $storeId;?>" data-store-name="<?php echo $storeName;?>">
	<div class="touch-me" data-tc="navigation" data-tid="discover-<?php echo $sport;?>-background">
		<div class="center">
			<div class="wrap">
				<div class="hello" data-tc="navigation" data-tid="discover-<?php echo $sport;?>-headline"><?php echo _("Even more choice?");?></div>
				<div class="touch" data-tc="navigation" data-tid="discover-<?php echo $sport;?>-cta"><?php echo _("Just touch the screen");?></div>
			</div>
		</div>
	</div>
	<div class="overview">
		<div class="header">
			<div class="settings" data-tc="configuration" data-tid="settings-button"></div>
			<h1 data-tc="configuration" data-tid="reload"></h1>
			<div class="header-image">
				<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="<?php echo $sportImage;?>" class="lazy" data-tc="other" data-tid="header-image">
			</div>
			<div class="nav">
				<div class="categories">
				<?php
					$nav = '';
					$refList = array();
					foreach($productList["categories"] as $key => $value) {
						$key = array_search(explode("_", $GLOBALS['config']['locale'])[0], array_column($value["translations"], 'language'));
						$category = $value["translations"][$key]["label"];
						$refList[$category] = array();
						if(!empty($value["itemIds"])) {
							foreach($value["itemIds"] as $dsmCode) {
								foreach(glob(dirname(__FILE__, 1) . "/cache/products/" . $dsmCode . "_*.json") as $file) {
									array_push($refList[$category], $file);
								}
							}
						}
					}
					foreach($refList as $cat => $ref) {
						$filterImage = json_decode(file_get_contents($ref[0]), true)['media']['images'][0] . '?f=80x80';
						$nav .= '<div class="filter" data-filter=".'.strtolower(clear_string($cat)).'" data-tc="navigation" data-tid="' . strtolower(str_replace('_',' ',$cat)) . '"><div class="thumbnail"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="'.$filterImage.'" class="lazy"></div><div class="title">'.str_replace('_',' ',$cat).'</div></div>';
					}
					echo $nav;
				?>
				</div>
			</div>	
		</div>
		
		<div id="ChangeLayout" class="cta blue light" data-tc="navigation" data-tid="compare-button"><?php echo _("Comparison");?></div>
		
		<div class="listing">
			<?php
				$products = '';
				$comparison = '';
				foreach($refList as $cat => $ref) {
					foreach($ref as $file) {
						if(file_exists($file)) {
							$productData = json_decode(file_get_contents($file), true);
							$tmpRef = str_replace(dirname(__FILE__, 1) . "/cache/products/", "", $file);
							$tmpRef = str_replace(".json", "", $tmpRef);
							$tmpRef = explode("_", $tmpRef);
							$item = $tmpRef[1];
							
							$variants = array();
							foreach($productData['skus'] as $variant => $val) {
								array_push($variants, $val['id']);
							}
							$price = explode('.', $productData['skus'][0]['activePrice']);
							if(count($price) > 1) {
								$price = $price[0] . '.' . (strlen($price[1]) == 1 ? $price[1] . '0' : $price[1]);
							} else {
								$price = $price[0];
							}

							$products .= '<div class="product mix '.strtolower(clear_string($cat)).'" data-dsm-code="' . $tmpRef[0] .'" data-product-id="'.$item.'" data-variants="'.json_encode($variants, true).'" data-price="'. $price .'" style="display:none;" data-tc="listing" data-tid="product-'.$item.'">';
							$products .= '<div class="preview">';
							$products .= '<div class="loader"></div>';
							$products .= '<div class="price"><div class="shape"><div class="content">'. str_replace('.', ',', $price) .'€</div></div></div>';
							if($productData['media']['images'] !== '') {
								$products .= '<div class="thumbnail" data-tc="listing" data-tid="product-image-'.$item.'"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="'.$productData['media']['images'][0].'?f=200x200" class="lazy" /></div>';
							} else {
								$products .= '<div class="thumbnail" data-tc="listing" data-tid="product-image-'.$item.'"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"/></div>';
							}
							$products .= '<div class="brand">'.$productData['productInfo']['brandId'].'</div><div class="name">'.$productData['productInfo']['description'].'</div>';

							if($productData['productInfo']['ratingNumber'] > 0) {
								$products .= '<div class="reviews" data-tc="listing" data-tid="product-reviews-'.$item.'">';	
								$products .= '<div class="stars">';
								$ct = 0;
								while($ct < 5) {
									if($ct < $productData['productInfo']['averageRating']) {
										if($ct < $productData['productInfo']['averageRating'] && $productData['productInfo']['averageRating'] < $ct+1) {
											$products .= '<span class="icon icon-star half"></span>';
										} else {
											$products .= '<span class="icon icon-star"></span>';
										}
									} else {							
										$products .= '<span class="icon icon-star disabled"></span>';
									}
									$ct++;
								}
								$products .= '</div>';
								$products .= '<div class="rating-number">(' . $productData['productInfo']['ratingNumber'] . ')</div>';
								$products .= '</div>';									
							}

							$products .= '</div>';	
							$products .= '</div>';

							$offer = '';
							$benefits = $productData["productAdvantages"];
							foreach($benefits as $benefit) {
								if(isset($benefit["contentValue"])) {
									$offer .= '<div data-title="'.$benefit["contentTitle"].'"><h3>'.$benefit["contentTitle"].'</h3>'.links2QR($benefit["contentValue"]).'</div>';
								} else {
									$offer .= '<div class="blank"><div></div></div>';
								}
							}
							$comparison .= '<div class="comparator mix '.strtolower(clear_string($cat)).'" data-product-id="'.$item.'" data-price="'.$price.'">'.$offer.'</div>';
						}
					}
				}
			?>
			<div id="ProductContainer" class="container" data-ref="product-container"><?php echo $products?></div>
			<div class="comparator-wrapper">
				<div id="ComparatorContainer" class="container" data-ref="comparator-container"><?php echo $comparison?></div>
			</div>
		</div>
	</div>
	<div class="product-detail">
		<div class="image-slider">
			<div class="slider main"></div>
			<div class="slider nav"></div>
		</div>
		<div class="product-content">
			<div class="back-button cta left blue light" data-tc="navigation" data-tid="product-back-to-overview"><?php echo _("Back to overview");?></div>
<!--
			<div class="nav">
				<div class="item active" data-nav="product">
					<div>Produkt</div>
				</div>
				<div class="item" data-nav="technical">
					<div>Technische Informationen</div>
				</div>
				<div class="item" data-nav="reviews">
					<div>Bewertungen</div>
				</div>
			</div>
-->
			<div class="product">
				<div class="top">
					<div class="brand" data-tc="product" data-tid="brand">Brand</div>
					<div class="name" data-tc="product" data-tid="name">Product name</div>
					<div class="ref" data-tc="product" data-tid="reference">0000000</div>
					<div class="price" data-tc="product" data-tid="price">
						<div class="shape">
							<div class="content">00,00€</div>
						</div>
					</div>
					<div class="reviews" data-tc="product" data-tid="reviews"></div>
<!--
					<div class="qr-code">
						<div id="qrcode" data-tc="product" data-tid="qr-code"></div>
						<div class="text" data-tc="product" data-tid="qr-more-infos">Mehr Infos</div>
					</div>
-->
				</div>
				<div class="description"></div>
				<div class="variants"></div>
				<div class="availability">
					<h4><?php echo _("Availability");?> <span data-tc="product" data-tid="availability-<?php echo strtolower($storeName);?>">DECATHLON <?php echo $storeName;?></span></h4>
					<div class="status">
						<div class="instore" data-tc="product" data-tid="availability-instore"></div>
						<div class="online" data-tc="product" data-tid="availability-online"></div>
					</div>					
					<!--<div class="take-now"><div class="cta right">Sofort mitnehmen</div></div>-->
				</div>
				<div class="delivery">
					<h4><?php echo _("Delivery options");?></h4>
					<?php 
					if(count($nextStores) > 0) {
					?>
						<div class="shipping-method click-collect-1h" data-tc="product" data-tid="delivery-clickandcollect-1h">
							<div class="header">
								<div class="title"><?php echo _("Store reservation");?></div>
								<div class="delay"><?php echo _("Ready to pick up in 1h");?></div>
								<div class="shipping-costs"><span><?php echo _("Free");?></span></div>						
							</div>
	<!--						<input data-role="none" class="check-accordeon" type="checkbox" checked>-->
							<div class="nearby-stores">
								<?php
								$nextStoresContent = '';
								foreach($nextStores as $nextStore) {
									$nextStoresContent .= '<div class="store" data-store-id="' . $nextStore['storeId'] . '" data-store-name="' . $nextStore['name'] . '">';
									$nextStoresContent .= '<div class="store-name">DECATHLON ' . $nextStore['name'] . '<div class="distance"><span class="material-icons">directions_car</span>&nbsp;<span>' . str_replace('.', ',', $nextStore['distance']) . '</span></div></div>';
									$nextStoresContent .= '<div class="availability"></div>';
									$nextStoresContent .= '</div>';
								}
								echo $nextStoresContent;
								?>
							</div>
						</div>
					<?php } ?>
					<div class="shipping-method click-collect" data-tc="product" data-tid="delivery-clickandcollect">
						<div class="header">
							<div class="title"><?php echo _("Click & Collect");?></div>
<!--							<div class="delay">24.12.2017</div>-->
							<div class="shipping-costs"><span><?php echo _("Free");?></span></div>						
						</div>
						<input data-role="none" class="check-accordeon" type="checkbox">
						<div class="nearby-stores">
							<?php
							$nextStoresContent = '';
							foreach($stores as $store) {
								if($store['storeId'] == "007" . str_pad($storeId, 5, '0', STR_PAD_LEFT) . str_pad($storeId, 5, '0', STR_PAD_LEFT)) {
									$nextStoresContent .= '<div class="store" data-store-id="' . $store['storeId'] . '" data-store-name="' . $store['name'] . '">';
									$nextStoresContent .= '<div class="store-name">DECATHLON ' . $store['name'] . '</div>';
									$nextStoresContent .= '<div class="opening-hours">';
									$hours = simplifyOpeningHours($store['openingHours']);
									foreach($hours as $row) {
										$nextStoresContent .= '<div class="row"><div class="day">' . $row[0] . '</div><div class="hours">' . $row[1] . ' h</div></div>';
									}
									$nextStoresContent .= '</div>';
									$nextStoresContent .= '</div>';
								}
							}
							
							foreach($nextStores as $nextStore) {
								$nextStoresContent .= '<div class="store" data-store-id="' . $nextStore['storeId'] . '" data-store-name="' . $nextStore['name'] . '">';
								$nextStoresContent .= '<div class="store-name">DECATHLON ' . $nextStore['name'] . '<div class="distance"><span class="material-icons">directions_car</span>&nbsp;<span>' . str_replace('.', ',', $nextStore['distance']) . '</span></div></div>';
								$nextStoresContent .= '<div class="opening-hours">';
								$hours = simplifyOpeningHours($nextStore['hours']);
								foreach($hours as $row) {
									$nextStoresContent .= '<div class="row"><div class="day">' . $row[0] . '</div><div class="hours">' . $row[1] . ' h</div></div>';
								}
								$nextStoresContent .= '</div>';
								$nextStoresContent .= '</div>';
							}
							echo $nextStoresContent;
							?>
						</div>
					</div>
					<div class="shipping-method home" data-tc="product" data-tid="delivery-home">
						<div class="header">
							<div class="title"><?php echo _("Home delivery");?></div>
<!--							<div class="delay">28.12.2017</div>-->
							<div class="shipping-costs"><span></span></div>					
						</div>
					</div>
				</div>
			</div>

			<div class="technical">
				<div class="benefits">
					<h2><?php echo _("Product benefits");?></h2>
					<div class="content"></div>
				</div>
				<div class="technical-infos">
					<h2><?php echo _("Technical informations");?></h2>
					<div class="content"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="overlay-qr">
		<div class="pop-up">
			<div class="headline"><?php echo _("<span>Scan</span> the QR code to get all product informations!");?></div>
			<div class="qr-code" data-tc="product" data-tid="overlay-qr-code"><div class="icon"></div><div id="qrcode-overlay" class="code"></div></div>	
			<div class="text">Mit der DECATHLON App hast du immer und überall die wichtigsten Infos rund um deinen Sport griffbereit.</div>
			<div class="cta outline" data-tc="navigation" data-tid="close-qr"><?php echo _("Close");?></div>
		</div>
	</div>
</div>

<!-- 1.11.3 -->
<script type="text/javascript" src="<?php echo $pathLevel;?>func/localize.js.php"></script>
<script defer type="text/javascript" src="<?php echo $pathLevel;?>assets/js/jquery.min.js"></script>
<script defer type="text/javascript" src="<?php echo $pathLevel;?>assets/js/mixitup.min.js"></script>
<script defer type="text/javascript" src="<?php echo $pathLevel;?>assets/js/slick.min.js"></script>
<script defer type="text/javascript" src="<?php echo $pathLevel;?>assets/js/qrcode.min.js"></script>
<script defer type="text/javascript" src="<?php echo $pathLevel;?>assets/js/functions.js?<?php echo time();?>"></script>
<script defer type="text/javascript" src="<?php echo $pathLevel;?>assets/js/showroom.js?<?php echo time();?>"></script>