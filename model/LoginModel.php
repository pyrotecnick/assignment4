<?php

/**
 * Solution for assignment 2
 * @author Daniel Toll 
 * modified by Nick Lashinski
 */

namespace model;

require_once("UserCredentials.php");
require_once("TempCredentials.php");
require_once("TempCredentialsDAL.php");
require_once("LoggedInUser.php");
require_once("UserClient.php");

class LoginModel {

    //TODO: Remove static to enable several sessions
    private static $sessionUserLocation = "LoginModel::loggedInUser";

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
     * Checks if user is logged in
     * @param  UserClient $userClient The current calls Client
     * @return boolean                true if user is logged in.
     */
    public function isLoggedIn(UserClient $userClient) {
        if (isset($_SESSION[self::$sessionUserLocation])) {
            $user = $_SESSION[self::$sessionUserLocation];
            if ($user->sameAsLastTime($userClient) == false) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Attempts to authenticate
     * @param  UserCredentials $uc
     * @return boolean
     */
    public function doLogin(UserCredentials $uc) {

        $this->tempCredentials = $this->tempDAL->load($uc->getName());
        $loginByUsernameAndPassword = \Settings::USERNAME === $uc->getName() && \Settings::PASSWORD === $uc->getPassword();
        $loginByUsernameAndPasswordRegistered = $this->checkRegistration($uc->getName(), $uc->getPassword());
        $loginByTemporaryCredentials = $this->tempCredentials != null && $this->tempCredentials->isValid($uc->getTempPassword());
        if ($loginByUsernameAndPassword || $loginByTemporaryCredentials || $loginByUsernameAndPasswordRegistered) {
            $user = new LoggedInUser($uc);
            $_SESSION[self::$sessionUserLocation] = $user;
            return true;
        }
        return false;
    }

    public function doLogout() {
        unset($_SESSION[self::$sessionUserLocation]);
    }

    /**
     * @return TempCredentials
     */
    public function getTempCredentials() {
        return $this->tempCredentials;
    }

    /**
     * renew the temporary credentials
     * 
     * @param  UserClient $userClient 
     */
    public function renew(UserClient $userClient) {
        if ($this->isLoggedIn($userClient)) {
            $user = $_SESSION[self::$sessionUserLocation];
            $this->tempCredentials = new TempCredentials($user);
            $this->tempDAL->save($user, $this->tempCredentials);
        }
    }

    /**
     * checks for a valid login attempt for a user that has been registed
     * 
     * @param type $requestedUserName
     * @param type $requestedPassword
     * @return boolean
     */
    private function checkRegistration($requestedUserName, $requestedPassword) {
        $storage = fopen("users.txt", "r");
        while (!feof($storage) && $requestedPassword !== "" && $requestedUserName !== "") {
            $entry = fgets($storage);
            //again I know using a static salt completly ruins the security of hashing but I'm running out of time to implement this properly
                if (strpos($entry, $requestedUserName) !== FALSE && strpos($entry, crypt($requestedPassword, "feje3-#GS")) !== FALSE) {
                    fclose($storage);
                    return TRUE;
            }
        }
        fclose($storage);
        return FALSE;
    }

}
