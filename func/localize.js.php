<?php
$config = file_get_contents(dirname(__FILE__, 2) . '/.config');
$GLOBALS['config'] = json_decode($config, true);

// set language/locale
$language = $GLOBALS['config']['locale'];
putenv("LANG=" . $language);
setlocale(LC_ALL, $language);

// Set the text domain as "messages"
$domain = "messages";
bindtextdomain($domain, dirname(__FILE__, 2) . "/locale");
bind_textdomain_codeset($domain, 'UTF-8');

textdomain($domain);
?>

var lang = {};
lang.available= '<?php echo _("available");?>';
lang.temporarily_not_available = '<?php echo _("Temporarily not available");?>';
lang.not_available_in_this_store = '<?php echo _("Not available in this store");?>';
lang.immediately_available = '<?php echo _("immediately avaialable");?>';

lang.available_online = '<?php echo _("Available online");?>';
lang.not_available_online = '<?php echo _("Not available online");?>';
lang.less_than_available_online = '<?php echo _("Less than 10 available online");?>';

lang.comparison = '<?php echo _("Comparison");?>';
lang.free = '<?php echo _("Free");?>';
lang.from = '<?php echo _("From");?>';
lang.overview = '<?php echo _("Overview");?>';

lang.order_online = '<?php echo _("Order online");?>';
lang.take_now = '<?php echo _("Take now");?>';

lang.review = '<?php echo _("Review");?>';
lang.reviews = '<?php echo _("Reviews");?>';
lang.no_reviews = '<?php echo _("No reviews");?>';

lang.scan_qr_link = '<?php echo _("Scan the QR code to open the link.");?>';
lang.scan_qr_addtocart = '<?php echo _("Scan the QR code to add the product to your <span>cart</span>.");?>';
lang.scan_qr_infos = '<?php echo _("<span>Scan</span> the QR code to get all product informations!");?>';
lang.scan_qr_takenow = '<?php echo _("Scan the QR code to take the product <span>right now</span>.");?>';
lang.scan_qr_reserve = '<?php echo _("Scan the QR code to reserve the article in <span>%%STORE_NAME%%</span>.");?>';

lang.variants = '<?php echo _("Variants");?>';