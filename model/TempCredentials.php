<?php
/**
  * Solution for assignment 2
  * @author Daniel Toll
  */
namespace model;
class TempCredentials {
	private $expire;
	private $tempPassword;
	const TEMP_CREDENTIALS_MAX_TIME = 60;
	public function __construct($userName) {
		
		$this->expire = time()+ self::TEMP_CREDENTIALS_MAX_TIME;
		$this->tempPassword = sha1(\Settings::SALT . rand() . time() );
	}
	public function isValid($tempPasswordGiven) {
		return $this->expire > time() &&  $tempPasswordGiven === $this->tempPassword;
	}
	public function getPassword() {
		return $this->tempPassword;
	}
	public function getExpire() {
		return $this->expire;
	}
	
}