<?php

class Status
{
	const STATUS_FILE       = ROOT_PATH . '/db/status.bin';
	public $last_update;

	function __construct(){
		$this->last_update = time();
	}
}

function loadStatus()
{
	if(file_exists(Status::STATUS_FILE)){
		$status = file_get_contents(Status::STATUS_FILE);
	        return unserialize($status);
	}
	
	return updateStatus();
}

function updateStatus()
{
	$status = new Status();
        file_put_contents(Status::STATUS_FILE, serialize($status));
	
	return $status;
}

?>
