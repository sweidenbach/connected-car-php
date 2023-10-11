<?php

require_once(ROOT_PATH . '/inc/auth.inc.php');
require_once(ROOT_PATH . '/inc/config.inc.php');
require_once(ROOT_PATH . '/inc/logging.inc.php');

class AIAuth extends Auth
{
	const TOKEN_URL 	= 'https://accounts.autonomic.ai/v1/auth/oidc/token';

	protected $pass_auth;

	function __construct($pass_auth) {
		parent::__construct();
                $this->pass_auth = $pass_auth;
        }

	public function getPassAuth(){
		return $this->pass_auth;
	}

	public function getToken(){
		$curl = getCURLSession(self::TOKEN_URL);

		curl_setopt($curl, CURLOPT_POST, 		1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, 
			"subject_token=" 	. $this->pass_auth->getAccessToken() . 
			"&subject_issuer=" 	. ISSUER 	. 
			"&client_id=" 		. AI_CLIENT_ID 	. 
			"&grant_type=" 		. GRANT_TYPE 	.
			"&subject_token_type=" 	. TOKEN_TYPE);

		$headers = [
			'accept: */*',
			'Content-Type: application/x-www-form-urlencoded'
		];

		curl_setopt($curl, CURLOPT_HTTPHEADER, 		$headers);

		$data = curl_exec($curl);

		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if($http_code != 200){
			log_error('Unexpected response (http code ' . $http_code . ') from token endpoint: ' . curl_error($curl), 1);
		}

		curl_close($curl);

		$this->setJWT(json_decode($data));		
	}

	public function refreshToken(){
		$this->getToken();
	}
}

?>
