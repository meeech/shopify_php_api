<?php
	define('USERNAME', 'boardroom');
	require_once('../simpletest/autorun.php');
	
	class AllTest extends TestSuite{
		function AllTest(){
			$this->TestSuite('All Tests');
			$this->addFile('/Users/'.USERNAME.'/Sites/shopify_php_api/test/shopify_api_test.php');
		}
	}
?>