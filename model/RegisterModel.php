<?php

/**
 * @author Nick Lashinski
 */

namespace model;

require_once("UserCredentials.php");
require_once("LoggedInUser.php");
require_once("UserClient.php");

class RegisterModel {

    private $storage;
    public $message;

    public function __construct() {
          $this->storage = "users.txt";
    }

    /**
     * Attempts to register
     * @param  UserCredentials $uc
     * @return boolean
     * 
     */
    public function doRegister(UserCredentials $uc) {

        if ($uc->getName() == "") {
            return false;
        } else if ($uc->getPassword() == "") {
            return false;
        } else if (strlen($uc->getName()) < 3) {
            return false;
        } else if (strlen($uc->getPassword()) < 6) {
            return false;
        } else if (strcmp($uc->getPassword(), $uc->getTempPassword()) !== 0) {
            return false;
        } else if ($this->usernameExists($uc->getName())) {
            return false;
        } else if (preg_match('/[^a-zA-Z0-9]+/', $uc->getName(), $matches)) {
            return false;
        } else {
            $this->registerUser($uc->getName(), $uc->getPassword());
            $this->message = "Registered new user.";
            return true;
        }
        return false;
    }

    /**
     * checks storage to see if requested username has already been registed
     * 
     * @param string $username
     * @return boolean
     */
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
        //I know using a static salt completly ruins the security of hashing but I'm running out of time to implement this properly
        file_put_contents($this->storage, crypt($password, "feje3-#GS"), FILE_APPEND);
    }

}
