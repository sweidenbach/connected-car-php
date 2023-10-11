# connected-car-php

PHP script to call Ford connected-car API for vehicle status info via autonomic.ai

# Requirements
- PHP 7
- PHP Curl Modul

# Configuration (inc/config.inc.php)
~~~
define('CONSOLE_OUTPUT',        false); 									// Print infos/errors to STDOUT
define('USERAGENT',             'FordPass/32 CFNetwork/1410.0.3 Darwin/22.6.0'); // HTTP User Agent for all requests
define('ISSUER',                'fordpass'); 								// issuer for autonomic.ai token endpoint
define('AI_CLIENT_ID',          'fordpass-prod'); 							// client_id for autonomic.ai token endpoint
define('PASS_CLIENT_ID',        '9fb503e0-715b-47e8-adfd-ad4b7770f73b'); 	// client_id for FordPass OAuth Authorizatiuon Code Flow
define('PASS_GRANT_TYPE',       'authorization_code'); 						// grant_type for FordPass OAuth
define('PASS_REDIRECT_URI',     'fordapp://userauthorized'); 				// redirect_uri for FordPass OAuth
define('PASS_APP_ID',           '1E8C7794-FF5F-49BC-9596-A1E0C86C5B19'); 	// app_id for FordPass OAuth
define('PASS_USERNAME',         ''); 										// Your FordPass username/email
define('PASS_PASSWORD',         ''); 										// Your FordPass password
define('GRANT_TYPE',            'urn:ietf:params:oauth:grant-type:token-exchange'); //grant_type for autonomic.ai token endpoint
define('TOKEN_TYPE',            'urn:ietf:params:oauth:token-type:jwt'); 	// token_type for autonomic.ai token endpoint
define('CURL_VERIFYHOST',       1); 										// verify host TLS certificate matches with hostname
define('CURL_VERIFYPEER',       1); 										// verify host TLS certificate is valid
define('VIN',                   ''); 										// Your car VIN
define('APP_ID',                '667D773E-1BDC-4139-8AD0-2B16474E8DC7'); 	// app_id for autonomic.ai token endpoint
define('DYNA',                  'MT_3_30_2352378557_3-0_$uuid_0_789_87'); 	// dynatrace header template
~~~

# Usage

Using the included sample connected-car.php:
~~~
./connected-car.php [--force]
~~~

## Example Script:
~~~
<?php
define('ROOT_PATH', dirname(__DIR__) . '/connected-car-php/');

require_once(ROOT_PATH . '/inc/pass.auth.inc.php');
require_once(ROOT_PATH . '/inc/ai.auth.inc.php');
require_once(ROOT_PATH . '/inc/vehicle.inc.php');
require_once(ROOT_PATH . '/inc/status.inc.php');

$pass_auth = new PassAuth();
$ai_auth = new AIAuth($pass_auth);
$vehicle = new Vehicle($ai_auth);

$vehicle->refreshStatus();

echo json_encode($vehicle->getStatus());
?>
~~~
