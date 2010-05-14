<?php
	require_once(str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']). '../lib/shopify_api.php'); 
	require_once(str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']). '../lib/shopify_api_config.php');

	class TestingFunctions extends UnitTestCase{		
		function testConfiguration(){				
			$this->assertTrue(defined('API_KEY'));
			$this->assertTrue(defined('SECRET'));
		}
		
		function testIsEmpty(){
			$this->assertTrue(isEmpty(''));
		}
		
		function testURLEncodeArray(){
			$array = array('one' => 1, 'two' => 'too', 'three' => array('blah'));
			$this->assertTrue(url_encode_array($array) == "one=1&two=too");
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
		
		function testSendToAPI(){
			//This test should return a 500 error
			$this->assertTrue(sendToAPI('testshop.myshopify.com/admin/orders.xml', 'POST') == 500);
		}
	}
	
	class TestSessionClass extends UnitTestCase{
		private $session;
		
		public function testIsValid(){
			$this->session = new Session('', '', '', '');
			$this->assertFalse($this->session->valid());
			$this->session = new Session('schneider-and-sons2771.myshopify.com', '31191cf000f9d1ee2bc97ddcdd5a76fd', API_KEY, SECRET);
			$this->assertTrue($this->session->valid());
		}
	}

?>