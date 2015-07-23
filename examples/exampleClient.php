<?php

	require_once("../src/Client.php");

	$request = new Client(true);
	$configRequest = array(
		"proxy"			=>	"exampleCustomProxy.webhosting.com/index.php",
		"url"			=>	"example.urlRequest.com",
		"post"			=>	array(
			"param1"	=>	"value1"
			),
		"id"			=>	"firstRequest",
		"headers"		=>	array(
			"Referer : google.com"
			),
		"cookie"		=>	"",
		"showCookie"	=>	true
		);
	try {	
		$arrayRequest = $request->sendRequest($configRequest);
	} catch (Exception $e) {
	    echo $e->getMessage();
	}

	var_dump($arrayRequest);

?>