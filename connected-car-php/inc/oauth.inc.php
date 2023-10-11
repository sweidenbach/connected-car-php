<?php

require_once(ROOT_PATH . '/inc/enc.inc.php');

function genVerifier(){
	$random = bin2hex(openssl_random_pseudo_bytes(32));
	return base64url_encode(pack('H*', $random));
}

function genCodeChallenge($verifier){
	return base64url_encode(pack('H*', hash('sha256', $verifier)));
}

?>
