#!/usr/bin/php -q
<?php

define('ROOT_PATH', dirname(__DIR__) . '/connected-car-php/');

require_once(ROOT_PATH . '/inc/pass.auth.inc.php');
require_once(ROOT_PATH . '/inc/ai.auth.inc.php');
require_once(ROOT_PATH . '/inc/vehicle.inc.php');
require_once(ROOT_PATH . '/inc/status.inc.php');

$pass_auth = new PassAuth();
$ai_auth = new AIAuth($pass_auth);
$vehicle = new Vehicle($ai_auth);
$status = loadStatus();

$car_status = $vehicle->getStatus();

$force = false;
$night = false;

if(date("H") < 6 && date("H") >= 0){
	$night = true;
}

if(count($argv) > 1 && $argv[1] == '--force'){
	$force = true;
}

if($car_status->metrics->ignitionStatus->value == "RUN" || ($status->last_update + 3600 < time() and !$night) or ($status->last_update + 14400 < time() and $night) or $force)
{
	$vehicle->refreshStatus();
}

echo json_encode($vehicle->getStatus());

?>
