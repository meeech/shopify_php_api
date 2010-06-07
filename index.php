<?php
	ini_set('display_errors', 1); 
	error_reporting(E_ALL);
	include('lib/shopify_api.php');
	if (!defined('API_KEY') || !defined('SECRET') || isEmpty(API_KEY) || isEmpty(SECRET)) die('Both constants API_KEY and SECRET must be defined in the config file.');

		
	/* GET VARIABLES */
	$url = (isset($_GET['shop'])) ? mysql_escape_string($_GET['shop']) : '';
	$token = (isset($_GET['t'])) ? mysql_escape_string($_GET['t']) : '';
	$timestamp = (isset($_GET['timestamp'])) ? mysql_escape_string($_GET['timestamp']) : '';
	$signature = (isset($_GET['signature'])) ? mysql_escape_string($_GET['signature']) : '';
	$params = array('timestamp' => $timestamp, 'signature' => $signature);
	
	/*
		Step 1:
		Create a new Shopify API object with the $url, $token, $api_key, and $secret, and [$params]
		
		You must first ping the shop auth URL if you have not. You can do this by using Session::create_permission_url()
		Your application's Return URL will then be pinged with the shop, token, signature and timestamp.
		
		After this authorization is done you can then make requests to the API.
	*/
	$api = new Session($url, $token, API_KEY, SECRET);
	
	//if the Shopify connection is valid
	if ($api->valid()){
		if (isEmpty($token)){
			header("Location: " . $api->create_permission_url());
		}
	}
?>