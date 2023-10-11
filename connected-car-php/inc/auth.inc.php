<?php

abstract class Auth {
	
	const JWT_FILE = ROOT_PATH . '/db/jwt_';
	protected $jwt = null;	

	abstract protected function getToken();
	abstract protected function refreshToken();

	public function __construct(){
		log_message('Loading JWT for ' . get_class($this) . '...');
		$jwt = "";
		if(file_exists(self::JWT_FILE . get_class($this) . '.bin')){
			$jwt = file_get_contents(self::JWT_FILE . get_class($this) . '.bin');
			$this->setJWT(unserialize($jwt));
		}
	}

	public function getAccessToken(){
		if(is_null($this->jwt)){
			$this->getToken();
		} else if ($this->jwt->expiry <= time()){
			log_message('Refreshing token...');
			$this->refreshToken();
		} else {
			log_message('Token still valid');
		}
		return $this->jwt->access_token;
	}

	public function setJWT($jwt){
		$this->jwt = $jwt;

		if(!property_exists($this->jwt, 'expiry')){
			$this->jwt->expiry = time() + $this->jwt->expires_in - 5;
			$this->jwt->time = time();
		}

		log_message('Storing JWT for ' . get_class($this) . ':');

		file_put_contents(self::JWT_FILE . get_class($this) . '.bin', serialize($this->jwt));
	}
}

?>
