<?php
	require_once('../lib/shopify_api.php'); 
	require_once('../lib/shopify_api_config.php');
	require_once('../simpletest/autorun.php');

	class TestingFunctions extends UnitTestCase{		
		function testConfiguration(){				
			$this->assertTrue(defined('API_KEY'));
			$this->assertTrue(defined('SECRET'));
			$this->assertTrue(defined('GZIP_ENABLED'));
			$this->assertTrue(defined('GZIP_PATH'));
			$this->assertTrue(defined('FORMAT'));
		}
		
		function testIsEmpty(){
			$this->assertTrue(isEmpty(''));
			$this->assertTrue(isEmpty('   '));
			$this->assertTrue(!isEmpty('This is not empty.'));
		}
		
		function testURLEncodeArray(){
			$array = array('one' => 1, 'two' => 'to o', 'three' => array('blah'));
			$this->assertTrue(url_encode_array($array) == "one=1&two=to%20o");
		}
		
		function testOrganizeArray(){
			$array1 = array('something' => array(array('id' => 4), array('id' => 5)));
			$array2 = array('something' => array(4 => array('id' => 4), 5 => array('id' => 5)));
			$this->assertTrue(organizeArray($array1, 'something') == $array2);
		}
		
		function testArrayToXML(){
			$array = array('something' => array('one' => 1, 'two' => array('too' => 2)));
			$xml = '<?xml version="1.0" encoding="UTF-8"?><something><one>1</one><two><too>2</too></two></something>';
			$this->assertTrue(arrayToXML($array) == $xml);
		}
				
		function testGZDecode(){
			$this->assertTrue(gzdecode(gzencode("this is some text")) == "this is some text");
		}
		
	}
	
	class TestSessionClass extends UnitTestCase{
		private $session;
		
		function testIsValid(){
			$this->session = new Session('', '', '', '');
			$this->assertFalse($this->session->valid());
			$this->session = new Session('schneider-and-sons2771.myshopify.com', '31191cf000f9d1ee2bc97ddcdd5a76fd', API_KEY, SECRET);
			$this->assertTrue($this->session->valid());
		}
		
		function testPrivateApp(){
			$this->session = new Session('schneider-and-sons2771.myshopify.com', '31191cf000f9d1ee2bc97ddcdd5a76fd', API_KEY, 'this is a secret', true);
			$this->assertTrue(substr_count($this->session->site(), 'this is a secret') > 0);
		}
	}

?>