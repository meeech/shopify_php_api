<?php
	echo '<!--';
	//error_reporting(0); 
	//ini_set("display_errors", 0);
	include('lib/shopify_api.php');
	
	if (!file_exists('lib/shopify_api_config.php')) die('lib/shopify_api_config.php is missing!');
	include('lib/shopify_api_config.php');
	if (!defined('API_KEY') || !defined('SECRET') || isEmpty(API_KEY) || isEmpty(SECRET)) die('Both constants API_KEY and SECRET must be defined in the config file.');
		
	/* GET VARIABLES */
	$url = $_GET['shop'];
	$token = $_GET['t'];
	$timestamp = $_GET['timestamp'];
	$signature = $_GET['signature'];
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
	echo '-->';
	/*
		Step 1:
		Create a new Shopify API object with the $url, $token, $api_key, and $secret, and [$params]
		
		This will automatically create a session to the Shopify website. You will then be able to make calls to
		the Shopify API.
	*/
	$api = new Session($url, $token, API_KEY, SECRET);
	
	//if the Shopify connection is valid
	if ($api->valid()){
		//All objects need to be passed $api->site();
		$article = new Article(597742, $api->site());
		$result = $article->createNewArticle(array('title' => 'Testing from the PHP API!', 'body' => 'Hey there! I posted this article from the API!'));
		print_r($result);
	}
?>