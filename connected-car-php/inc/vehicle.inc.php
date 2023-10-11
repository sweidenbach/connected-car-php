<?php

require_once(ROOT_PATH . '/inc/config.inc.php');
require_once(ROOT_PATH . '/inc/random.inc.php');
require_once(ROOT_PATH . '/inc/logging.inc.php');
require_once(ROOT_PATH . '/inc/ai.auth.inc.php');
require_once(ROOT_PATH . '/inc/status.inc.php');

class Vehicle
{
	const STATUS_URL 	= 'https://api.autonomic.ai/v1beta/telemetry/sources/fordpass/vehicles/' . VIN . ':query';
	const COMMAND_URL	= 'https://api.autonomic.ai/v1/command/vehicles/' . VIN . '/commands';

	protected $ai_auth;
	protected $dyna;

	function __construct($ai_auth) {
		$this->ai_auth = $ai_auth;
		$this->dyna = str_replace('$uuid', genUUIDV4(), DYNA);
	}

	public function getStatus(){
		$curl = getCURLSession(self::STATUS_URL);

                curl_setopt($curl, CURLOPT_POST,                1);
                curl_setopt($curl, CURLOPT_POSTFIELDS,          '{}');

                $headers = [
                        'accept: */*',
                        'Content-Type: application/json',
			'application-id: ' . APP_ID,
			'x-dynatrace: ' . $this->dyna,
			'authorization: Bearer ' . $this->ai_auth->getAccessToken(),
               	];

                curl_setopt($curl, CURLOPT_HTTPHEADER,          $headers);

                $data = curl_exec($curl);

                $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                if($http_code != 200){
			log_error('Unexpected response (http code ' . $http_code . ') from status endpoint: ' . curl_error($curl), 1);
                }

                curl_close($curl);

                return json_decode($data);
	}

	public function refreshStatus(){
		$curl = getCURLSession(self::COMMAND_URL);

                curl_setopt($curl, CURLOPT_POST,                1);
                curl_setopt($curl, CURLOPT_POSTFIELDS,          '
		{
			"properties": {},
			"tags": {},
			"type": "statusRefresh",
			"wakeUp": "true"
		}
		');

                $headers = [
                        'accept: */*',
                        'Content-Type: application/json',
                        'application-id: ' . APP_ID,
                        'x-dynatrace: ' . $this->dyna,
                        'authorization: Bearer ' . $this->ai_auth->getAccessToken()
                ];

                curl_setopt($curl, CURLOPT_HTTPHEADER,          $headers);

                $data = curl_exec($curl);

                $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                if($http_code != 201){
                        log_error('Unexpected response (http code ' . $http_code . ') from refresh status endpoint: ' . curl_error($curl), 1);
		}
		
		updateStatus();

                curl_close($curl);

                return json_decode($data);
	}
}

?>
