<?php
$allFiles = scandir(dirname(__FILE__, 1) . "/cache/sports/");
$files = array_diff($allFiles, array('.', '..'));
?>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<link href='//fonts.googleapis.com/css?family=Roboto+Condensed:400,700' rel='stylesheet' type='text/css'>
<?php
$cssFiles = array(
	dirname(__FILE__, 1) . '/assets/css/start.css'
);
echo '<style type="text/css">
@font-face {
	font-family: "DecathlonCube";
	src: url("assets/fonts/DecathlonCube.eot");
	src: url("assets/fonts/DecathlonCube.eot?#iefix") format("embedded-opentype"), url("assets/fonts/DecathlonCube.woff2") format("woff2"), url("assets/fonts/DecathlonCube.woff") format("woff"), url("fonts/DecathlonCube.ttf") format("truetype"), url("assets/fonts/DecathlonCube.svg#DecathlonCube") format("svg");
	font-weight: normal;
	font-style: normal
}' . compressCSS($cssFiles) . '</style>';
?>

<!-- Google Analytics -->
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

<?php
if($GLOBALS['config']['googleAnalytics']['trackingActive'] === true) {
	echo "ga('create', '" . $GLOBALS['config']['googleAnalytics']['propertyID'] . "', 'auto');";
	echo "ga('send', 'pageview');";
}
?>

var locale = '<?php echo $GLOBALS['config']['locale'];?>';
</script>
<!-- End Google Analytics -->
</head>

<div class="main">
	<div class="step-1">
		<h1><?php echo _("Choose your <span>showroom</span>");?></h1>

		<select>
			<option>––– <?php echo _("Choose your store");?> –––</option>
			<?php
				foreach($stores as $store) {
					echo '<option data-store-id="' . ltrim(substr($store['storeId'],-5), '0') . '">' . $store['name'] . '</option>';
				}
			?>
		</select>

		<div class="sport-selection">
			<?php
				foreach($files as $file) {
					$sport = json_decode(file_get_contents("cache/sports/" . $file), true)["translations"];
					$key = array_search(explode("_", $GLOBALS['config']['locale'])[0], array_column($sport, 'language'));
					echo '<div class="sport" data-global-name="' . str_replace('.json', '', $file) . '">' . $sport[$key]["label"] . '</div>';
				}
			?>
			<div style="flex:0 0 30%;"></div>
		</div>
		<?php if($GLOBALS['config']['support']) {
			echo '<div class="support">Support: ' . $GLOBALS['config']['support'] . '</div>';
		}?>
	</div>

	<div class="step-2">
		<h1><?php echo _("Choose the <span>order</span> of your sports");?></h1>

		<div class="category-list"></div>

		<div class="cta-wrapper">
			<div class="cta left grey" id="backToSport"><?php echo _("Back");?></div>
			<div class="cta right blue" id="launchShowroom"><?php echo _("Launch showroom");?></div>
		</div>
	</div>
</div>

<div class="community">
	<div id="qr-code"></div>
	<div class="text">G+ Community</div>
</div>

<!-- 1.11.3 -->
<script defer type="text/javascript" src="assets/js/jquery.min.js?v=<?php echo $latestUpdate;?>"></script>
<script defer type="text/javascript" src="assets/js/qrcode.min.js?v=<?php echo $latestUpdate;?>"></script>
<script defer type="text/javascript" src="assets/js/TweenMax.min.js?v=<?php echo $latestUpdate;?>"></script>
<script defer type="text/javascript" src="assets/js/Draggable.min.js?v=<?php echo $latestUpdate;?>"></script>
<script defer type="text/javascript" src="assets/js/functions.js?v=<?php echo $latestUpdate;?>"></script>
<script defer type="text/javascript" src="assets/js/start.js?v=<?php echo $latestUpdate;?>"></script>
<script defer type="text/javascript">
if(localStorage.getItem('storeId') && localStorage.getItem('sport')) {
	location.href = '<?php echo $GLOBALS['config']["path"];?>' + localStorage.getItem('storeId') + '/' + localStorage.getItem('sport');
}
</script>