<?php
	require_once('../simpletest/autorun.php');
	
	class AllTest extends TestSuite{
		function AllTest(){
			$this->TestSuite('All Tests');
			$this->addFile(str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']). 'shopify_api_test.php');
		}
	}
?>