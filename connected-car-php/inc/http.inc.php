<?php
function getResponseLocation($response){
	$matches = array();
        preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $response, $matches);
        $url = $matches[1];

	return $url;
}

function getCURLSession($url){
	$curl = curl_init();

	curl_setopt($curl, CURLOPT_URL,                 $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,      CURL_VERIFYHOST);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,      CURL_VERIFYPEER);
        curl_setopt($curl, CURLOPT_USERAGENT,           USERAGENT);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER,      1);

	return $curl;
}
?>
