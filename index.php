<?php
	include('lib/shopify_api.php');
	
	if (!file_exists('lib/shopify_api_config.php')) die('lib/shopify_api_config.php is missing!');
	include('lib/shopify_api_config.php');
	if (!defined('API_KEY') || !defined('SECRET') || isEmpty(API_KEY) || isEmpty(SECRET)) die('Both constants API_KEY and SECRET must be defined in the config file.');
		
	/* GET VARIABLES */
	$url = (isset($_GET['shop'])) ? mysql_escape_string($_GET['shop']) : '';
	$token = (isset($_GET['t'])) ? mysql_escape_string($_GET['t']) : '';
	$timestamp = (isset($_GET['timestamp'])) ? mysql_escape_string($_GET['timestamp']) : '';
	$signature = (isset($_GET['signature'])) ? mysql_escape_string($_GET['signature']) : '';
	$params = array('timestamp' => $timestamp, 'signature' => $signature);
	$id = (is_numeric($_GET['id']) && isset($_GET['id'])) ? $_GET['id'] : 0;
	
	/*
		This was done for testing purposes. A table was created to store shop authorizations.
		It allowed the API to be tested on more than one store at a time.
		
		Table structure
		-authorized_shops-
		id 			int(11)			auto_increment
		shop		varchar(255)
		token		varchar(32)
		signature	varchar(32)
		timestamp	int(11)
	*/

	$conn = mysql_connect('127.0.0.1', 'root', '');
	mysql_select_db('php_api_test');
	
	if ($id > 0){
		$query = "SELECT * FROM authorized_shops WHERE id = $id";
		$result = mysql_query($query);
		if (!$result) die(mysql_error().'<br />LINE: ' . __LINE__);
		$result = mysql_fetch_array($result);
		$url = $result['shop'];
		$token = $result['token'];
		$timestamp = $result['timestamp'];
		$signature = $result['signature'];
	}else{
		$check = "SELECT * FROM authorized_shops WHERE shop = '$url'";
		$result = mysql_query($check);
		if (mysql_num_rows($result) > 0){
			$result = mysql_fetch_array($result);
			$url = $result['shop'];
			$token = $result['token'];
			$timestamp = $result['timestamp'];
			$signature = $result['signature'];
		}else{		
			$query = "INSERT INTO authorized_shops (shop, token, signature, timestamp) VALUES ('$url', '$token', '$signature', $timestamp)";
			$result = mysql_query($query);
			if (!$result) die(mysql_error().'<br />LINE: ' . __LINE__);
		}
	}
	
	mysql_close($conn);
	/*
		Step 1:
		Create a new Shopify API object with the $url, $token, $api_key, and $secret, and [$params]
		
		This will automatically create a session to the Shopify website. You will then be able to make calls to
		the Shopify API.
	*/
	$api = new Session($url, $token, API_KEY, SECRET);
	
	//if the Shopify connection is valid
	if ($api->valid()){
		print_r($api->blog->get());
	}
?>