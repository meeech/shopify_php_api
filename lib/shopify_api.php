<?php
/*
	Shopify PHP API
	Created: May 4th, 2010
	Modified: May 9th, 2010
	Version: 1.20100509
*/
	//namespace ShopifyAPI;
	
	//this function is just to make the code a little cleaner
	function isEmpty($string){
		return (strlen(trim($string)) == 0);
	}
	
	//this function will url encode paramaters assigned to API calls
	function url_encode_array($params){
		$string = '';
		if (sizeof($params) > 0){
			foreach($params as $k => $v) if (!is_array($v)) $string .= $k.'='.$v.'&';
			$string = substr($string, 0, strlen($string) - 1);
		}
		return $string;
	}
	
	/*
		organizeArray applies some changes to the array that is generated from returned XML
		This is done so that traversing the result is easier to manipulate by setting the index
		of returned data to the actual ID of the record
	*/
	function organizeArray($array, $type){
		/* no organizing needed */
		if (!isset($array[$type][0])){
			$temp = $array[$type];
			$id = $temp['id'];
			$array[$type] = array();
			$array[$type][$id] = $temp;
		}else{
			foreach($array[$type] as $k => $v){
				$id = $v['id'];
				$array[$type][$id] = $v;
				unset($array[$type][$k]);
			}		
		}
		
		return $array;
	}
	
	function arrayToXML($array, $xml = ''){
		if ($xml == "") $xml = '<?xml version="1.0" encoding="UTF-8"?>';
		foreach($array as $k => $v){
			if (is_array($v)){
				$xml .= '<' . $k . '>';
				$xml = arrayToXML($v, $xml);
				$xml .= '</' . $k . '>';
			}else{
				$xml .= '<' . $k . '>' . $v . '</' . $k . '>';
			}
		}	
		return $xml;
	}
	
	function sendToAPI($url, $request = 'GET', $successCode = SUCCESS, $xml = array()){
		$xml = arrayToXML($xml);
		$ch = new miniCURL();
		$data = $ch->send($url, $request, $xml);

		if ($data[0] == $successCode){
			return $ch->loadString($data[1]);
		}
					
		return $data[0]; //returns the HTTP Code (200, 201 etc) if the expected $successCode was not met
	}

	class ApplicationCharge{
		private $prefix = "/application_charges";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site .  $this->prefix;
		}
		
		public function getCharges($cache = false){
			if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . ".xml"), 'record');
			return $this->array['record'];
		}
		
		public function createCharge($fields){
			$fields = array('application-charge' => $fields);
			return sendToAPI($this->prefix . ".xml", 'POST', CREATED, $fields);
		}
		
		public function activateCharge($id){
			return sendToAPI($this->prefix . "/" . $id . "/activate.xml", 'PUT', SUCCESS);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class RecurringApplicationCharge{
		private $prefix = "/recurring_application_charges";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site .  $this->prefix;
		}
		
		public function getCharges($cache = false){
			if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . ".xml"), 'recurring-application-charge');		
			return $this->array['recurring-application-charge'];
		}
		
		public function createCharge($fields){
			$fields = array('recurring-application-charge' => $fields);
			return sendToAPI($this->prefix . ".xml", 'POST', CREATED, $fields);
		}
		
		public function activateCharge($id, $fields){
			$fields = array('recurring-application-charge' => $fields);
			return sendToAPI($this->prefix . "/" . $id . "/activate.xml", 'PUT', SUCCESS, $fields);
		}
		
		public function cancelCharge($id){
			return sendToAPI($this->prefix . "/" . $id . ".xml", 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			empty($this);
		}
	}	

	class Article{
		private $prefix = "/blogs/:blog_id/";
		private $array = array();
		
		public function __construct($blog, $site){
			$this->prefix = $site . str_replace(':blog_id', $blog, $this->prefix);
		}
		
		public function getArticles($cache = false, $params = array()){
			if (!$cache){
				$params = url_encode_array($params);
				$this->array = organizeArray(sendToAPI($this->prefix . 'articles.xml?' . $params), 'article');
			}
			
			return $this->array['article'];
		}
		
		public function getCount($params = array()){
			$params = url_encode_array($params);
			return sendToAPI($this->prefix . 'articles/count.xml?' . $params);
		}
		
		public function getArticle($id, $cache = false){			
			if (!$cache){
				$temp = sendToAPI($this->prefix . 'articles/' . $id . '.xml');
				$this->array['article'][$id] = $temp;
			}			
			if (!isset($this->array['article'][$id])) throw new Exception("Article is not in cache. Set cache to false.");
			return $this->array['article'][$id];
		}
		
		public function createNewArticle($fields = array()){
			$fields = array('article' => $fields);
			return sendToAPI($this->prefix . "articles.xml", 'POST', CREATED, $fields);
		}
		
		public function modifyArticle($id, $fields = array()){
			$fields = array('article' => $fields);
			return sendToAPI($this->prefix . "articles/" . $id . ".xml", 'PUT', SUCCESS, $fields);
		}
		
		public function removeArticle($id){
			return sendToAPI($this->prefix . "articles/" . $id . ".xml", 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class Asset{
		private $prefix = "/assets.xml";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
			
		public function getAssets($cache = false){
			if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix), 'asset');		
			return $this->array['asset'];
		}
		
		public function getAsset($key, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . '?asset[key]=' . $key);
				$this->array['asset'][$key] = $temp;
			}
			if (!isset($this->array['asset'][$key])) throw new Exception("Asset does not exist in cache. Change cache to false.");		
			return $this->array['asset'][$key];
		}
		
		public function getThemeImage($key, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . '?asset[key]=' . $key);
				$this->array['asset'][$key] = $temp;
			}
			if (!isset($this->array['asset'][$key])) throw new Exception("Asset does not exist in cache. Change cache to false.");		
			return $this->array['asset'][$key];
		}
		
		public function modifyAsset($fields){
			$fields = array('asset' => $fields);
			return sendToAPI($this->prefix, 'PUT', SUCCESS, $fields);
		}
		
		public function copyAsset($fields){
			$fields = array('asset' => $fields);
			return sendToAPI($this->prefix, 'PUT', SUCCESS, $fields);			
		}
		
		public function createImage($fields){
			$fields = array('asset' => $fields);
			return sendToAPI($this->prefix, 'PUT', SUCCESS, $fields);			
		}
		
		public function modifyImage($fields){
			$fields = array('asset' => $fields);
			return sendToAPI($this->prefix, 'PUT', SUCCESS, $fields);
		}
		
		public function removeAsset($key){
			return sendToAPI($this->prefix . "?asset[key]=" . $key, 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			empty($this);
		}		
	}
	
	class Blog{
		private $prefix = "/blogs";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site .  $this->prefix;
		}
		
		public function getBlogs($cache = false){
			if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . ".xml"), 'blog');
			return $this->array['blog'];
		}
		
		public function blogCount(){
			return sendToAPI($this->prefix . "/count.xml");
		}
		
		public function getBlog($id, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . "/" . $id . ".xml");
				$this->array['blog'][$id] = $temp;
			}
			if (!isset($this->array['blog'][$id])) throw new Exception("Blog doesn't exist in cache. Turn cache to false.");		
			return $this->array['blog'][$id];
		}
		
		public function createBlog($fields){
			$fields = array('blog' => $fields);
			return sendToAPI($this->prefix . ".xml", 'POST', CREATED, $fields);
		}
		
		public function modifyBlog($id, $fields){
			$fields = array('blog' => $fields);
			return sendToAPI($this->prefix . "/" . $id . ".xml", 'PUT', SUCCESS, $fields);
		}
		
		public function removeBlog($id){
			return sendToAPI($this->prefix . "/" . $id . ".xml", 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class CustomCollection{
		private $prefix = "/";	
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function getCollections($params =  array(), $cache = false){
			if (!$cache){
				$params = url_encode_array($params);			
				$this->array = organizeArray(sendToAPI($this->prefix . "custom_collections.xml?" . $params), 'custom-collection');
			}
			
			return $this->array['custom-collection'];
		}
		
		public function collectionCount($params = array(), $product_id = 0){
			$params = url_encode_array($params);			
			return sendToAPI($this->prefix . "custom_collections/count.xml?" . $params);
		}
		
		public function getCollection($id, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . "/custom_collections/" . $id . ".xml");
				$this->array['custom-collection'][$id] = $temp;				
			}
			if (!isset($this->array['custom-collection'][$id])) throw new Exception("Collection not in the cache. Set cache to false.");			
			return $this->array['custom-collection'][$id];
		}
		
		public function createCollection($fields){
			$fields = array('custom-collection' => $fields);
			return sendToAPI($this->prefix . "custom_collections.xml", 'POST', CREATED, $fields);
		}
		
		public function modifyCollection($id){
			$fields = array('custom-collection' => $fields);
			return sendToAPI($this->prefix . "custom_collections/" . $id . ".xml", 'PUT', SUCCESS, $fields);
		}
		
		public function removeCollection($id){
			return sendToAPI($this->prefix . "custom_collections/" . $id . ".xml", 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class Collect{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function getCollects($params = array(), $cache = false){
			if (!$cache){
				$params = url_encode_array($params);
				$this->array = organizeArray(sendToAPI($this->prefix . "collects.xml?" . $params), 'collect');
			}
			
			return $this->array['collect'];
		}
		
		public function countCollects($params = array()){
			$params = url_encode_array($params);
			return sendToAPI($this->prefix . "collects.xml?" . $params);
		}
		
		public function getCollect($id = 0, $params = array(), $cache = false){
			$collect = array();
			
			if (!$cache){
				$params = url_encode_array($params);
				if ($id > 0){
					$temp = sendToAPI($this->prefix . "collects/" . $id . ".xml?" . $params);
					$this->array['collect'][$id] = $temp;
					$collect = $temp;
				}else{
					if (isset($params['product_id']) && isset($params['collection_id'])){
						$temp = sendToAPI($this->prefix . "/collects.xml?" . $params);
						
						if (isset($temp['collect'][0])){
							$id = $temp['collect'][0]['id'];
							$this->array['collect'][$id] = $temp['collect'][0];
							$collect = $temp['collect'][0];
						}
					}else{
						throw new Exception("Must specify a collect id or product_id and collection_id.");										
					}
				}
			}
			
			return $collect;
		}
		
		public function createCollect($fields){
			$fields = array('collect' => $fields);
			return sendToAPI($this->prefix . "collects.xml", 'POST', CREATED, $fields);
		}
		
		public function removeCollect($id){
			return sendToAPI($this->prefix . "custom_collections.xml", 'POST', CREATED);
		}
					
		public function __destruct(){
			empty($this);
		}
	}
	
	class Comment{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function getComments($params = array(), $cache = false){
			if (!$cache){
				$params = url_encode_array($params);
				$this->array = organizeArray(sendToAPI($this->prefix . "comments.xml?" . $params), 'comment');
			}			
			
			return $this->array['comment'];
		}
		
		public function commentCount($params = array()){
			$params = url_encode_array($params);
			return sendToAPI($this->prefix . "comments/count.xml?" . $params);
		}
		
		public function getComment($id, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . "comments/" . $id . ".xml");
				$this->array['comment'][$id] = $temp;
			}
			if (!isset($this->array['comment'][$id])) throw new Exception("Comment is not in cache. Set cache to false.");
			return $this->array['comment'][$id];
		}
		
		public function createComment($fields){
			$fields = array('comment' => $fields);
			return sendToAPI($this->prefix . "comments.xml", 'POST', CREATED, $fields);
		}
		
		public function modifyComment($id, $fields){
			$fields = array('comment' => $fields);
			return sendToAPI($this->prefix . "comments/" . $id . ".xml", 'POST', SUCCESS, $fields);
		}
		
		public function markAsSpam($id){
			return sendToAPI($this->prefix . "comments/" . $id . "/spam.xml", 'POST', SUCCESS);
		}

		public function approveComment($id){
			return sendToAPI($this->prefix . "comments/" . $id . "/approve.xml", 'POST', SUCCESS);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class Country{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function getCountries($cache = false){
			if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . "countries.xml"), 'country');
			return $this->array['country'];
		}
		
		public function countryCount(){
			return sendToAPI($this->prefix . "countries/count.xml");
		}
		
		public function getCountry($id, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . "countries/" . $id . ".xml");
				$this->array['country'][$id] = $temp;
			}			
			if (!isset($this->array['country'][$id])) throw new Exception("Country not in cache. Set cache to false.");		
			return $this->array['country'][$id];
		}
		
		public function createCountry($fields){
			$fields = array('country' => $fields);
			return sendToAPI($this->prefix . "countries.xml", 'POST', CREATED, $fields);
		}
		
		public function modifyCountry($id, $fields){
			$fields = array('country' => $fields);
			return sendToAPI($this->prefix . "countries/" . $id . ".xml", 'PUT', SUCCESS, $fields);
		}
		
		public function deleteCountry($id){
			return sendToAPI($this->prefix . "countries/" . $id . ".xml", 'DELETE', SUCCESS, $fields);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class Event{
		private $prefix = "/";
		private $array;
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function getEvents($params = array()){
			$params = url_encode_array($params);
			$this->array = organizrArray(sendToAPI($this->prefix . "events.xml?" . $params), 'event');			
			return $this->array['event'];
		}
		
		public function getOrderEvents($id, $params = array()){
			$params = url_encode_array($params);
			$this->array = organizeArray(sendToAPI($this->prefix . "orders/" . $id . "/events.xml?" . $params), 'event');			
			return $this->array['event'];
		}
		
		public function getProductEvents($id, $params = array()){
			$params = url_encode_array($params);			
			$this->array = organizeArray(sendToAPI($this->prefix . "products/" . $id . "/events.xml?" . $params), 'event');			
			return $this->array['event'];
		}
		
		public function getEvent($id, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . "events/" . $id . ".xml");
				$this->array['event'][$id] = $temp;
			}			
			if (!isset($this->array['event'][$id])) throw new Exception("Event not found in the cache. Set cache to false.");
			return $this->array['event'][$id];
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class Fullfillment{
		private $prefix = "/orders/:order_id/";
		private $array = array();
		
		public function __construct($order_id, $site){
			$this->prefix = $site . str_replace(':order_id', $order_id, $this->prefix);
		}
	
		public function getFullfillments($params = array(), $cache = false){
			if (!$cache){
				$params = url_encode_array($params);
				$this->array = organizeArray(sendToAPI($this->prefix . "fullfillments.xml?" . $params), 'fullfillment');
			}			
			return $this->array['fullfillment'];
		}
		
		public function getFullfillmentCount($params = array()){
			$params = url_encode_array($params);
			return sendToAPI($this->prefix . "fullfillments/count.xml?" . $params);
		}
		
		public function getFullfillment($id, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . "fullfillments/" . $id . ".xml");
				$this->array['fullfillment'][$id] = $temp;
			}			
			if (!isset($this->array['fullfillment'][$id])) throw new Exception("Fullfillment not in cache. Set cache to false.");		
			return $this->array['fullfillent'][$id];
		}
		
		public function createFullfillment($fields){
			$fields = array('fullfillment' => $fields);
			return sendToAPI($this->prefix . "fullfillments.xml", 'POST', CREATED, $fields);
		}
		
		public function fullfillItems($id, $fields){
			$fields = array('fullfillment' => $fields);
			return sendToAPI($this->prefix . "fullfillments.xml", 'POST', CREATED, $fields);
		}
		
		public function modifyFullfillment($id, $fields){
			$fields = array('article' => $fields);
			return sendToAPI($this->prefix . "fullfillments/" . $id . ".xml", 'PUT', SUCCESS, $fields);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class Metafield{
		private $prefix = "/";
		private $array;
		private $product_id;
		
		public function __construct($product_id, $site){
			$this->prefix = $site . $this->prefix;
			$this->product_id = $product_id;
		}
		
		public function getMetafields($params = array(), $cache = false){
			if (!$cache){
				$params = url_encode_array($params);
				$xmlObj = ($product_id > 0) ? sendToAPI($this->prefix . "metafields.xml?" . $params) : sendToAPI($this->prefix . "products/" . $this->product_id . "/metafields.xml?" . $params);				
				$this->array = organizeArray($xmlObj, 'metafield');
			}
			
			return $this->array['metafield'];
		}
		
		public function getMetafield($metafield_id, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . "products/" . $this->product_id . "/metafields/" . $metafield_id . ".xml");
				$this->array['metafield'][$metafield_id] = $temp;
			}			
			if (!isset($this->array['metafield'][$metafield_id])) throw new Exception("Metafield not found in cache. Set cache to false.");
			return $this->array['metafield'][$metafield_id];
		}
		
		public function newMetafield($fields){
			$fields = array('metafield' => $fields);
			return ($product_id > 0) ? sendToAPI($this->prefix . "products/" . $this->product_id . "/metafields.xml", 'POST', CREATED, $fields) : sendToAPI($this->prefix . "metafields.xml", 'POST', CREATED, $fields);
		}
		
		public function modifyMetafield($id, $fields){
			$fields = array('metafield' => $fields);
			return ($product_id > 0) ? sendToAPI($this->prefix . "products/" . $this->product_id . "/metafields/" . $id . ".xml", 'PUT', SUCCESS) : sendToAPI($this->prefix . "metafields/" . $id . ".xml", 'PUT', SUCCESS);
		}
		
		public function removeMetafield($id){
			return ($product_id > 0) ? sendToAPI($this->prefix . "products/" . $this->product_id . "/metafields/" . $id . ".xml", 'DELETE', SUCCESS) : sendToAPI($this->prefix . "metafields/" . $id . ".xml", 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class Order{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function getOrders($params = array(), $cache = false){
			if (!$cache){
				$params = url_encode_array($params);
				$this->array = organizeArray(sendToAPI($this->prefix . "orders.xml?" . $params), 'order');
			}
			
			return $this->array['order'];
		}		
		
		public function getOrder($id, $cache = false){
			if (!$cache){
				$temp = semdToAPI($this->prefix . "orders/" . $id . ".xml");
				$this->array['order'][$id] = $temp;
			}
			if (!isset($this->array['order'][$id])) throw new Exception("Order not in cache. Set cache to false.");			
			return $this->array['order'][$id];
		}
		
		public function countOrders($params = array()){
			$params = url_encode_array($params);
			return sendToAPI($this->prefix . "orders/count.xml?" . $params);
		}
		
		public function openOrder($id){
			return sendToAPI($this->prefix . "orders/" . $id . "/open.xml", 'POST', SUCCESS);
		}
		
		public function closeOrder($id){
			return sendToAPI($this->prefix . "orders/" . $id . "/close.xml", 'POST', SUCCESS);
		}
		
		public function modifyOrder($id, $fields){
			$fields = array('order' => $fields);
			return sendToAPI($this->prefix . "orders/" . $id . ".xml", 'PUT', SUCCESS, $fields);
		}
		
		public function setNoteAttributes($id, $fields){
			$fields = array('order' => array('id' => $id, 'note-attributes' => array('note-attribute' => $fields)));
			return sendToAPI($this->prefix . "orders/" . $id . ".xml", 'PUT', SUCCESS, $fields);
		}
			
		public function __destruct(){
			empty($this);
		}
	}
	
	class Page{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;		
		}
		
		public function getPages($cache = false, $params = array()){
			if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . "pages.xml?" . $params), 'page');
			return $this->array['page'];
		}
		
		public function pageCount($params = array()){
			return sendToAPI($this->prefix . "pages/count.xml?" . $params);
		}
		
		public function getPage($id, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . "pages/" . $id . ".xml");
				$this->array['page'][$id] = $temp;
			}			
			if (!isset($this->array['page'][$id])) throw new Exception("Page not in cache. Set cache to false.");
			return $this->array['page'][$id];
		}
		
		public function createPage($fields){
			$fields = array('page' => $fields);
			return sendToAPI($this->prefix . "pages.xml", 'POST', CREATED, $fields);
		}
		
		public function modifyPage($id, $fields){
			$fields = array('page' => $fields);
			return sendToAPI($this->prefix . "pages/" . $id .".xml", 'PUT', SUCCESS, $fields);
		}
		
		public function removePage($id){
			return sendToAPI($this->prefix . "pages/" . $id . ".xml", 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class Product{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function getProducts($params = array(), $collection_id = 0, $cache = false){
			if (!$cache){				
				$xmlObj = ($collection_id > 0) ? sendToAPI($this->prefix . "products.xml?collection_id=" . $collection_id . "&" . $params) : sendToAPI($this->prefix . "products.xml?" . $params);
				$this->array = organizeArray($xmlObj, 'product');
			}			
			return $this->array['product'];
		}
		
		public function productCount($params = array(), $collection_id = 0){
			return ($collection_id > 0) ? sendToAPI($this->prefix . "products/count.xml?collection_id=" . $collection_id . "&" . $params) : sendToAPI($this->prefix . "products/count.xml?" . $params);
		}
		
		public function getProduct($id, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . "products/" . $id . ".xml");
				$this->array['product'][$id] = $temp;
			}
			if (!isset($this->array['product'][$id])) throw new Exception("Product not in cache. Set cache to false.");		
			return $this->array['product'][$id];
		}
			
		public function createProduct(){
			$fields = array('product' => $fields);
			return sendToAPI($this->prefix . "product.xml", 'POST', CREATED, $fields);
		}
		
		public function modifyProduct(){
			$fields = array('product' => $fields);
			return sendToAPI($this->prefix . "products/" . $id . ".xml", 'PUT', SUCCESS, $fields);
		}
		
		public function removeProduct(){
			return sendToAPI($this->prefix . "products/". $id . ".xml", 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class ProductImage{
		private $prefix = "/products/:product_id/";
		private $array;
		
		public function __construct($product_id, $site){
			$this->prefix = $site . str_replace(':product_id', $product_id, $this->prefix);
		}
		
		public function getImages($cache = false){
			if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . "images.xml"), 'image');
			return $this->array['image'];
		}
		
		public function createImage($fields){
			$fields = array('image' => $fields);
			return sendToAPI($this->prefix . "images.xml", 'POST', CREATED, $fields);
		}
		
		public function removeImage($id){
			return sendToAPI($this->prefix . "images/". $id . ".xml", 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class ProductVariant{
		private $prefix = "/products/:product_id/";
		private $array = array();
		
		public function __construct($product_id, $site){
			$this->prefix = $site . str_replace(':product_id', $product_id, $this->prefix);
		}
		
		public function getVariants($cache = false){
			if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . "/variants.xml"), 'variant');
			return $this->array['variant'];
		}
		
		public function variantCount(){
			return sendToAPI($this->prefix . $this->product_id . "/variants/count.xml");
		}
		
		public function getVariant($id, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . $this->product_id . "/variants/" . $id . ".xml");
				$this->array['variant'][$id] = $temp;
			}			
			if (!isset($this->array['variant'][$id])) throw new Exception("Variant not in cache. Change cache to false.");
			return $this->array['variant'][$id];
		}
		
		public function createVariant($fields){
			$fields = array('variant' => $fields);
			return sendToAPI($this->prefix . "variants.xml", 'POST', CREATED, $fields);
		}
		
		public function modifyVariant($id, $fields){
			$fields = array('variant' => $fields);
			return sendToAPI($this->prefix . "variants/" . $id . ".xml", 'PUT', SUCCESS, $fields);
		}
		
		public function removeVariant($id){
			return sendToAPI($this->prefix . "variants/" . $id . "xml", 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class Province{
		private $prefix = "/countries/:country_id/";
		private $array = array();
		
		
		public function __construct($country_id, $site){
			$this->prefix = $site . str_replace(':country_id', $country_id, $this->prefix);
		}
		
		public function getProvinces(){
			$this->array = organizeArray(sendToAPI($this->prefix . "provinces.xml"), 'pronvince');
			return $this->array['province'];
		}
		
		public function provinceCount(){
			return sendToAPI($this->prefix . "provinces/count.xml");
		}
		
		public function getProvince($id, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . "provinces/" . $id . ".xml");
				$this->array['province'][$id] = $temp;
			}			
			if (!isset($this->array['province'][$id])) throw new Exception("Province not in cache. Set cache to false.");
			return $this->array['province'][$id];
		}
		
		public function modifyProvince($id, $fields){
			$fields = array('province' => $fields);
			return sendToAPI($this->prefix . "provinces/" . $id . ".xml", 'PUT', SUCCESS, $fields);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class Redirect{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function getRedirects($params = array(), $cache = false){			
			if (!$cache){
				$params = url_encode_array($params);
				$this->array = organizeArray(sendToAPI($this->prefix . "redirects.xml?" . $params), 'redirect');
			}		
			return $this->array['redirect'];
		}
		
		public function redirectCount($params){
			return sendToAPI($this->prefix . "redirects/count.xml?" . $params);
		}
		
		public function getRedirect($id, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . "redirects/" . $id .".xml");
				$this->array['redirect'][$id] = $temp;
			}			
			if (!isset($this->array['redirect'][$id])) throw new Exception("Redirect not found in cache. Set cache to false.");
			return $this->array['redirect'][$id];
		}
		
		public function createRedirect($fields){
			$fields = array('redirect' => $fields);
			return sendToAPI($this->prefix . "redirects.xml", 'POST', CREATED, $fields);
		}
		
		public function modifyRedirect($id, $fields){
			$fields = array('redirect' => $fields);
			return sendToAPI($this->prefix . "redirects/" . $id . ".xml", 'PUT', SUCCESS, $fields);
		}
		
		public function removeRedirect($id){
			return sendToAPI($this->prefix . "redirects/" . $id . ".xml", 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class Shop{
		private $prefix = "/";
		public $shop = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
			$this->shop = sendToAPI($this->prefix . "shop.xml");
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class SmartCollection{
		private $prefix = "/";	
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function getCollections($params =  array(), $cache = false){
			if (!$cache){
				$params = url_encode_array($params);
				$this->array = organizeArray(sendToAPI($this->prefix . "smart_collections.xml?" . $params), 'smart-collection');
			}
			return $this->array['smart-collection'];
		}
		
		public function collectionCount($params = array()){
			$params = url_encode_array($params);			
			return sendToAPI($this->prefix . "smart_collections/count.xml?" . $params);			
		}
		
		public function getCollection($id, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . "/smart_collections/" . $id . ".xml");
				$this->array['smart-collection'][$id] = $temp;				
			}			
			if (!isset($this->array['smart-collection'][$id])) throw new Exception("Collection not in the cache. Set cache to false.");
			return $this->array['smart-collection'][$id];
		}
		
		public function createCollection($fields){
			$fields = array('smart-collection' => $fields);
			return sendToAPI($this->prefix . "smart_collections.xml", 'POST', CREATED, $fields);
		}
		
		public function modifyCollection($id, $fields){
			$fields = array('smart-collection' => $fields);
			return sendToAPI($this->prefix . "smart_collections/" . $id . ".xml", 'PUT', SUCCESS, $fields);
			
		}
		
		public function deleteCollection($id){
			return sendToAPI($this->prefix . "smart_collections/" . $id . ".xml", 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			empty($this);
		}		
	}
	
	class Transaction{
		private $prefix = "/orders/:order_id/";
		private $array = array();
		
		public function __construct($order_id, $site){
			$this->prefix = $site . str_replace(':order_id', $order_id, $this->prefix);
		}
		
		public function getTransactions($cache = false){
			if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . "transactions.xml"), 'transaction');			
			return $this->array['transaction'];
		}
		
		public function transactionCount($id){
			return sendToAPI($this->prefix . "transactions/count.xml");
		}
		
		public function getTransaction($id, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . "transactions/" . $id . ".xml");
				$this->array['transaction'][$id] = $temp;
			}			
			if (!isset($this->array['transaction'][$id])) throw new Exception("Transaction not in cache. Set cache to false.");			
			return $this->array['transaction'][$id];
		}
		
		public function createTransaction($fields){
			$fields = array('transaction' => $fields);
			return sendToAPI($this->prefix . "transactions.xml", 'POST', CREATED, $fields);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class Webhook{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function getHooks($cache = false, $params = array()){
			if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . "webhooks.xml?" . $params), 'webhook');
			return $this->array['webhok'];
		}
		
		public function hookCount($params = array()){
			$xmlObj = new parser($this->prefix . "webhooks/count.xml?" . $params);
			return $xmlObj->resultArray();
		}
		
		public function getHook($id, $cache = false){
			if (!$cache){
				$temp = sendToAPI($this->prefix . "webhooks/" . $id . ".xml");
				$this->array['webhook'][$id] = $temp;
			}
			if (!isset($this->array['webhook'][$id])) throw new Exception("Webhook not in cache. Set cache to false.");
			return $this->array['webhook'][$id];
		}
		
		public function createHook(){
			$fields = array('webhook' => $fields);
			return sendToAPI($this->prefix . "webhooks.xml", 'POST', CREATED, $fields);
		}
		
		public function modifyHook($id, $fields){
			$fields = array('webhook' => $fields);
			return sendToAPI($this->prefix . "webhooks/" . $id . ".xml", 'PUT', SUCCESS, $fields);
		}
		
		public function removeHook($id){
			return sendToAPI($this->prefix . "webhooks/". $id . ".xml", 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			empty($this);
		}
	}
	
	class Session{
		private $api_key;
		private $secret;
		private $protocol = 'https';
		private $format;
		
		private $shop;
		
		private $url;
		private $token;
		private $name;
						
		/*
			BEGIN PUBLIC
		*/
		
		public function __construct($url, $token = '', $api_key, $secret, $params = array(), $format = 'xml'){
			$this->url = $url;
			$this->token = (isEmpty($token)) ? $url : $token;
			$this->api_key = $api_key;
			$this->secret = $secret;
			$this->format = $format;
			if (isset($params['signature'])){
				$timestamp = $params['timestamp'];
				$expireTime = time() - (24 * 86400);
				if (!$this->validate_signature($params) || $expireTime > $timestamp){
					throw new Exception('Invalid signature: Possible malicious login.');
				}
			}
			$this->prepare_url($this->url);
		}
		
		public function shop(){
			return $this->shop;
		}
		
		public function create_permission_url(){
			return (isEmpty($this->url) || isEmpty($this->api_key)) ? '' : 'http://' . $this->url . '/admin/api/auth?api_key=' . $this->api_key;
		}
		
		/* Used to make all non-authetication calls */
		public function site(){
			return $this->protocol . '://' . $this->api_key . ':' . $this->computed_password() . '@' . $this->url . '/admin';
		}
		
		public function valid(){
			return (!isEmpty($this->url) && !isEmpty($this->token));
		}
			
		public function __destruct(){
			empty($this);
		}
		
		/*
			END PUBLIC
			BEGIN PRIVATE
		*/
		
		private function computed_password(){
			return md5($this->secret . $this->token);
		}
		
		private function prepare_url($url){
			if (isEmpty($url)) return '';
			$url = preg_replace('/https?:\/\//', '', $url);
		}
		
		private function validate_signature($params){	
			$this->signature = $params['signature'];
			$genSig = $this->secret;
			ksort($params);
			foreach($params as $k => $v){
				if ($k != "signature" && $k != "action" && $k != "controller" && !is_numeric($k)){
					$genSig .= $k . '=' . $v;
				}
			}
			return (md5($genSig) == $this->signature);
		}		

		/*
			END PRIVATE
		*/	
	}
	
	class miniCURL{
		
		private $ch;
		
		public function send($url, $request = 'GET', $xml_payload = '', $headers = array('Accept-Encoding: gzip')){
			$this->ch = curl_init($url);
		
			// _HEADER _RETURNTRANSFER -- Return output as string including HEADER information
			$options = array(
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_CUSTOMREQUEST => $request,
//				CURLOPT_HTTPHEADER => $headers
			);
			
			if ($request != "GET"){ 
				$options[CURLOPT_POSTFIELDS] = $xml_payload; 
				$options[CURLOPT_HTTPHEADER] = array('Content-Type: application/xml; charset=utf-8');
			}
			
			curl_setopt_array($this->ch, $options);
			curl_exec($this->ch);
			$data = curl_multi_getcontent($this->ch);
			$code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
			curl_close($this->ch);
			
			return array($code, $data);
		}
		
		public function loadString($data){
			$xml = simplexml_load_string($data);
			$array = array();
			$this->recurseXML($xml, $array);
			return $array;
		}
		
		public function recurseXML($xml, &$array){ 
	        $children = $xml->children(); 
	        $executed = false;

	        foreach ($children as $k => $v){ 
				if (is_array($array)){
	            	if (array_key_exists($k , $array)){ 		
	                	if (array_key_exists(0 ,$array[$k])){ 
	                    	$i = count($array[$k]); 
	                    	$this->recurseXML($v, $array[$k][$i]);     
	                	}else{ 
	                    	$tmp = $array[$k]; 
	                    	$array[$k] = array(); 
	                    	$array[$k][0] = $tmp; 
	                    	$i = count($array[$k]); 
	                    	$this->recurseXML($v, $array[$k][$i]); 
	                	} 
	            	}else{ 
	                	$array[$k] = array(); 
	                	$this->recurseXML($v, $array[$k]);    
	            	}
				}else{
					$array[$k] = array(); 
                	$this->recurseXML($v, $array[$k]);
				} 
				$executed = true; 
	        } 
	
	        if (!$executed && isEmpty($children->getName())){ 
	            $array = (string)$xml; 
	        } 
		}
		
		public function __destruct(){
			empty($this);			
		}
	}
?>