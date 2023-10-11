<?php

require_once(ROOT_PATH . '/inc/auth.inc.php');
require_once(ROOT_PATH . '/inc/config.inc.php');
require_once(ROOT_PATH . '/inc/logging.inc.php');
require_once(ROOT_PATH . '/inc/oauth.inc.php');
require_once(ROOT_PATH . '/inc/http.inc.php');

class PassAuth extends Auth
{
	const AUTHORIZE_URL 	= 'https://sso.ci.ford.com/v1.0/endpoint/default/authorize?redirect_uri=' . PASS_REDIRECT_URI . '&response_type=code&scope=openid&max_age=3600&login_hint=eyJyZWFsbSI6ICJjbG91ZElkZW50aXR5UmVhbG0ifQ==&code_challenge=$challenge&code_challenge_method=S256&client_id=' . PASS_CLIENT_ID;
	const MID_TOKEN_URL	= 'https://sso.ci.ford.com/oidc/endpoint/default/token';
	const TOKEN_URL		= 'https://api.mps.ford.com/api/token/v2/cat-with-ci-access-token';
	const REFRESH_TOKEN_URL	= 'https://api.mps.ford.com/api/token/v2/cat-with-refresh-token';
	const COOKIE_PATH 	= ROOT_PATH .'/db/pass_cookie.db';

	protected $login_url = "";
	protected $code_url = "";
	protected $verifier = "";
	protected $mid_token = "";
	protected $code = "";

	public function getToken(){
		$this->clearAuthCookies();
		$this->authorize();
		$this->login();
		$this->getCode();
		$this->getMidToken();
		
		$curl = getCURLSession(self::TOKEN_URL);

                curl_setopt($curl, CURLOPT_POST,                1);
                curl_setopt($curl, CURLOPT_POSTFIELDS,		'{"ciToken": "' . $this->mid_token . '"}');

                $headers = [
                        'accept: */*',
                        'Content-Type: application/json',
			'application-id: ' . APP_ID
                ];

                curl_setopt($curl, CURLOPT_HTTPHEADER,          $headers);

                $data = curl_exec($curl);

                $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                if($http_code != 200){
                        log_error('Unexpected response (http code ' . $http_code . ') from token endpoint: ' . curl_error($curl), 1);
                }

                curl_close($curl);

		$this->setJWT(json_decode($data));
	}

	public function refreshToken(){
                $curl = getCURLSession(self::REFRESH_TOKEN_URL);

                curl_setopt($curl, CURLOPT_POST,                1);
                curl_setopt($curl, CURLOPT_POSTFIELDS,          '{"refresh_token": "' . $this->jwt->refresh_token . '"}');

                $headers = [
                        'accept: */*',
                        'Content-Type: application/json',
                        'application-id: ' . APP_ID
                ];

                curl_setopt($curl, CURLOPT_HTTPHEADER,          $headers);

                $data = curl_exec($curl);

                $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                if($http_code != 200){
                        log_error('Unexpected response (http code ' . $http_code . ') from token endpoint: ' . curl_error($curl), 1);
                }

                curl_close($curl);

                $this->setJWT(json_decode($data));
	}

	public function getMidToken(){

		$curl = getCURLSession(self::MID_TOKEN_URL);

                curl_setopt($curl, CURLOPT_POST,                1);
                curl_setopt($curl, CURLOPT_POSTFIELDS,
                        "code_verifier="        . $this->verifier 	.
                        "&code="      		. $this->code		.
                        "&client_id="           . PASS_CLIENT_ID  	.
                        "&grant_type="          . PASS_GRANT_TYPE    	.
                        "&redirect_uri="  	. PASS_REDIRECT_URI	.
			"&scope=openid" 	.
			"&resource=");

                $headers = [
                        'accept: */*',
                        'Content-Type: application/x-www-form-urlencoded'
                ];

                curl_setopt($curl, CURLOPT_HTTPHEADER,          $headers);

                $data = curl_exec($curl);

                $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                if($http_code != 200){
                        log_error('Unexpected response (http code ' . $http_code . ') from mid token endpoint: ' . curl_error($curl), 1);
                }

                curl_close($curl);

                $this->mid_token = json_decode($data)->access_token;
	}

	public function clearAuthCookies(){
		file_put_contents(self::COOKIE_PATH, '');
	}

	public function getCode(){

		$curl = getCURLSession($this->token_url);

                curl_setopt($curl, CURLOPT_COOKIEJAR,           self::COOKIE_PATH);
                curl_setopt($curl, CURLOPT_COOKIEFILE,          self::COOKIE_PATH);
                curl_setopt($curl, CURLOPT_HEADER,              true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION,      false);

                $headers = [
                        'accept: */*',
                        'x-requested-with: com.ford.fordpasseu',
                        'Accept-Language: en-us'
                ];

                curl_setopt($curl, CURLOPT_HTTPHEADER,          $headers);

                $data = curl_exec($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if($http_code != 302){
                        log_error('Unexpected response (http code ' . $http_code . ') from login endpoint: ' . curl_error($curl), 1);
                }

		$location = getResponseLocation($data);
		$query = parse_url($location, PHP_URL_QUERY);

		$output = array();
		parse_str($query, $output);

		$this->code = $output['code'];

		curl_close($curl);
	}

	public function login(){
                $curl = getCURLSession($this->login_url);

		curl_setopt($curl, CURLOPT_COOKIEJAR, 		self::COOKIE_PATH);
		curl_setopt($curl, CURLOPT_COOKIEFILE, 		self::COOKIE_PATH);
		curl_setopt($curl, CURLOPT_HEADER,              true);
                curl_setopt($curl, CURLOPT_POST,                1);
                curl_setopt($curl, CURLOPT_POSTFIELDS,
                        "operation=verify"	.
                        "&login-form-type=pwd"	.
                        "&username="		. urlencode(PASS_USERNAME)	.
                        "&password="		. urlencode(PASS_PASSWORD)
                );

                $headers = [
                        'accept: */*',
			'x-requested-with: com.ford.fordpasseu',
                        'content-type: application/x-www-form-urlencoded'
                ];

                curl_setopt($curl, CURLOPT_HTTPHEADER,          $headers);

                $data = curl_exec($curl);

                $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                if($http_code != 302){
                        log_error('Unexpected response (http code ' . $http_code . ') from login endpoint: ' . curl_error($curl), 1);
                }

                curl_close($curl);

		$this->token_url = getResponseLocation($data);
        }

	public function authorize(){

		$this->verifier = genVerifier();
		$challenge = genCodeChallenge($this->verifier);

		$curl = getCURLSession(str_replace('$challenge', $challenge, self::AUTHORIZE_URL));

		curl_setopt($curl, CURLOPT_COOKIEJAR,           self::COOKIE_PATH);
		curl_setopt($curl, CURLOPT_COOKIEFILE,          self::COOKIE_PATH);
		curl_setopt($curl, CURLOPT_HEADER, 		true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 	true);

		$headers = [
			'accept: */*',
			'x-requested-with: com.ford.fordpasseu',
			'Accept-Language: en-us'
		];

		curl_setopt($curl, CURLOPT_HTTPHEADER, 		$headers);

		$data = curl_exec($curl);

		$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if($http_code != 200){
			log_error('Unexpected response (http code ' . $http_code . ') from authorize endpoint: ' . curl_error($curl), 1);
		}

		curl_close($curl);

		$url = substr($data, strpos($data, 'data-ibm-login-url="') + 20);
		$url = substr($url, 0, strpos($url, '"'));

		$this->login_url = 'https://sso.ci.ford.com' . $url;
	}
}

?>
