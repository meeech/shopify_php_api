<?php
	/*
		ShopifyAPI Config File
		You can find your API Key, and Secret in your Shopify partners account (http://www.shopify.com/partners/)
	*/

	define('API_KEY', '');
	define('SECRET', '');
	define('FORMAT', 'xml'); // xml || json
	define('GZIP_ENABLED', true); // set to false if you do not want gzip encoding. If false GZIP_PATH is not needed to be set
	define('GZIP_PATH', '/tmp'); // path for gzip decoding (this file will need write permissions)
	
	/*
		Note that all XML tags with an - in the tag name are returned with a _ (underscore) in JSON	
	*/
?>