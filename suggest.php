<?php

header("Access-Control-Allow-Origin: *");

header('Content-type: application/json');

if(isset($_GET["term"])){

	$query = $_GET["term"];
	$query = strtolower($query);

	$query = trim($query);

	if(strpos($query,' ') !== false){

		$pieces = explode(" ",$query);

		$query=$pieces[sizeof($pieces)-1];

	}

	$str = "http://localhost:8983/solr/myexample/suggest?q=$query&wt=json";

	$response = file_get_contents($str);

	$response = json_decode($response,true);

	$response = $response['suggest']['suggest']["$query"]['suggestions'];

	$response = json_encode($response);

//var_dump($response);

	echo $response;

}

?>