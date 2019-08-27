<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

define('SHOWROOM_APP', true);
require_once("func/globalFunctions.php");

$latestUpdate = getLatestUpdate(dirname(__FILE__, 1));
setcookie("ds_time", $latestUpdate, time() + (86400 * 30), "/");

$stores = getStores();

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$request = str_replace($GLOBALS["config"]["path"], "", $protocol . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
$params  = explode("/", strtok($request, '?'));

if(empty($params[0]) || $params[0] == 'undefined') {
	require('start.php');
} else {
	require('showroom.php');
}