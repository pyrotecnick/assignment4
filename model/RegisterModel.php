<?php

/**
 * @author Nick Lashinski
 */

namespace model;

require_once("UserCredentials.php");
require_once("LoggedInUser.php");
require_once("UserClient.php");

class RegisterModel {

    //TODO: Remove static to enable several sessions
    private static $sessionUserLocation = "RegisterModel::loggedInUser";
    private $storage;
    public $message;

    public function __construct() {
        self::$sessionUserLocation .= \Settings::APP_SESSION_NAME;
        $this->storage = "users.txt";
        if (!isset($_SESSION)) {
            //Alternate check with newer PHP
            //if (\session_status() == PHP_SESSION_NONE) {
            assert("No session started");
        }
        $this->tempDAL = new TempCredentialsDAL();
    }

    /**
     * Attempts to register
     * @param  UserCredentials $uc
     * @return boolean
     * 
     * TODO: fix
     */
    public function doRegister(UserCredentials $uc) {

        if ($uc->getName() == "") {
            return false;
        } else if ($uc->getPassword() == ""){
            return false;
        } else if (strlen($uc->getName()) < 3){
            return false;
        } else if (strlen($uc->getPassword()) < 6){
            return false;
        } else if (strcmp($uc->getPassword(), $uc->getTempPassword()) !== 0){
            return false;
        } else if ($this->usernameExists($uc->getName())){
            return false;
        }
         
        else {
            $this->registerUser($uc->getName(), $uc->getPassword());
            $this->message = "Registered new user.";
            return true;
        }
        return false;
    }

    private function usernameExists($username) {
        if (strpos(file_get_contents($this->storage), $username) !== FALSE) {
            $this->message = "User exists, pick another username.";
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function registerUser($username, $password) {
        file_put_contents($this->storage, "\r\n", FILE_APPEND);
        file_put_contents($this->storage, $username, FILE_APPEND);
        file_put_contents($this->storage, " ", FILE_APPEND);
        file_put_contents($this->storage, $password, FILE_APPEND);
    }

}
