<?php
/**
  * @author Nick Lashinski
  */
namespace model;
require_once("UserCredentials.php");
require_once("TempCredentials.php");
require_once("TempCredentialsDAL.php");
require_once("LoggedInUser.php");
require_once("UserClient.php");
class RegisterModel {
	//TODO: Remove static to enable several sessions
	private static $sessionUserLocation = "RegisterModel::loggedInUser";
	/**
	 * @var null | TempCredentials
	 */
	private $tempCredentials = null;
	private $tempDAL;
	public function __construct() {
		self::$sessionUserLocation .= \Settings::APP_SESSION_NAME;
		if (!isset($_SESSION)) {
			//Alternate check with newer PHP
			//if (\session_status() == PHP_SESSION_NONE) {
			assert("No session started");
		}
		$this->tempDAL = new TempCredentialsDAL();
		
	}
	/**
	 * Attempts to authenticate
	 * @param  UserCredentials $uc
	 * @return boolean
         * 
         * TODO: fix
	 */
	public function doRegister(UserCredentials $uc) {
		
		$this->tempCredentials = $this->tempDAL->load($uc->getName());
		$registerByUsernameAndPassword = \Settings::USERNAME === $uc->getName() && \Settings::PASSWORD === $uc->getPassword();
		if ( $registerByUsernameAndPassword) {
			$user = new LoggedInUser($uc); 
			$_SESSION[self::$sessionUserLocation] = $user;
			return true;
		}
		return false;
	}
	
}