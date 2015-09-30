<?php

/**
 * @author Nick Lashinski
 */

namespace view;

class RegisterView {

    /**
     * These names are used in $_POST
     * @var string
     */
    private static $register = "RegisterView::Register";
    private static $name = "RegisterView::UserName";
    private static $password = "RegisterView::Password";
    private static $password2 = "RegisterView::Password2";
    private static $cookieName = "RegisterView::CookieName";
    private static $CookiePassword = "RegisterView::CookiePassword";
    private static $messageId = "RegisterView::Message";

    /**
     * This name is used in session
     * @var string
     */
    private static $sessionSaveLocation = "\\view\\RegisterView\\message";

    /**
     * view state set by controller through setters
     * @var boolean
     */
    private $registerHasFailed = false;
    private $registerHasSucceeded = false;

    /**
     * @var \model\RegisterModel
     */
    private $model;

    /**
     * @param \model\RegisterModel $model
     */
    public function __construct(\model\RegisterModel $model) {
        self::$sessionSaveLocation .= \Settings::APP_SESSION_NAME;
        $this->model = $model;
    }
    
    public function getClass(){
        return "RegisterView";
    }

    /**
     * accessor method for register attempts
     * both by cookie and by form
     * 
     * @return boolean true if user did try to register
     */
    public function userWantsToRegister() {
        return isset($_POST[self::$register]);
    }

    /**
     * Accessor method for register credentials
     * @return \model\UserCredentials
     */
    public function getCredentials() {
        return new \model\UserCredentials($this->getUserName(), $this->getPassword(), $this->getTempPassword(), $this->getUserClient());
    }

    public function getUserClient() {
        return new \model\UserClient($_SERVER["REMOTE_ADDR"], $_SERVER["HTTP_USER_AGENT"]);
    }

    /**
     * Tell the view that register has failed so that it can show correct message
     *
     * call this when register has failed
     */
    public function setRegisterFailed() {
        $this->registerHasFailed = true;
    }

    /**
     * Tell the view that register succeeded so that it can show correct message
     *
     * call this if register succeeds
     */
    public function setRegisterSucceeded() {
        $this->registerHasSucceeded = true;
    }

    /**
     * Create HTTP response
     *
     * Should be called after a register attempt has been determined
     * @sideeffect Sets cookies!
     * @return String HTML
     * 
     * TODO: fix
     */
    public function response() {
        return $this->doRegisterForm();
    }

    /**
     * @sideeffect Sets cookies!
     * @return [String HTML
     * 
     * TODO: fix
     * 
     */
    private function doRegisterForm() {
        $message = "";
        //Correct messages
        if ($this->userWantsToRegister() && $this->getRequestUserName() == "" && $this->getPassword() == ""){
            $message = "Username has too few characters, at least 3 characters. Password has too few characters, at least 6 characters.";
        }else if ($this->userWantsToRegister() && strlen($this->getRequestUserName()) < 3) {
            $message = "Username has too few characters, at least 3 characters.";
        }else if ($this->userWantsToRegister() && strlen($this->getPassword()) < 6) {
            $message = "Password has too few characters, at least 6 characters.";
        }else if ($this->userWantsToRegister() && strcmp($this->getPassword(), $this->getPassword2()) !== 0) {
            $message = "Passwords do not match.";
        } else {
            $message = $this->getSessionMessage();
        }

        //generate HTML
        return $this->generateRegisterFormHTML($message);
    }

    private function redirect($message) {
        $_SESSION[self::$sessionSaveLocation] = $message;
        $actual_link = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        header("Location: $actual_link");
    }

    private function getSessionMessage() {
        if (isset($_SESSION[self::$sessionSaveLocation])) {
            $message = $_SESSION[self::$sessionSaveLocation];
            unset($_SESSION[self::$sessionSaveLocation]);
            return $message;
        }
        return "";
    }

    /**
     * unset cookies both locally and on the client
     */
    private function unsetCookies() {
        setcookie(self::$cookieName, "", time() - 1);
        setcookie(self::$CookiePassword, "", time() - 1);
        unset($_COOKIE[self::$cookieName]);
        unset($_COOKIE[self::$CookiePassword]);
    }

    private function setNewTemporaryPassword() {
        //set New Cookie
        $tempCred = $this->model->getTempCredentials();
        if ($tempCred) {
            setcookie(self::$cookieName, $this->getUserName(), $tempCred->getExpire());
            setcookie(self::$CookiePassword, $tempCred->getPassword(), $tempCred->getExpire());
        }
    }

    private function generateRegisterFormHTML($message) {
        return "<form method='post' > 
				<fieldset>
					<legend>Register a new user - Write username and password</legend>
					<p id='" . self::$messageId . "'>$message</p>
					<label for='" . self::$name . "'>Username :</label>
					<input type='text' id='" . self::$name . "' name='" . self::$name . "' value='" . $this->getRequestUserName() . "'/>
                                        <br/>
					<label for='" . self::$password . "'>Password :</label>
					<input type='password' id='" . self::$password . "' name='" . self::$password . "'/>
                                        <br/>
					<label for='" . self::$password2 . "'>Repeat Password :</label>
					<input type='password' id='" . self::$password2 . "' name='" . self::$password2 . "'/>
					<br/>
					<input type='submit' name='" . self::$register . "' value='Register'/>
				</fieldset>
			</form>
		";
    }

    private function getRequestUserName() {
        if (isset($_POST[self::$name]))
            return trim($_POST[self::$name]);
        return "";
    }

    private function getUserName() {
        if (isset($_POST[self::$name]))
            return trim($_POST[self::$name]);
        if (isset($_COOKIE[self::$cookieName]))
            return trim($_COOKIE[self::$cookieName]);
        return "";
    }

    private function getPassword() {
        if (isset($_POST[self::$password]))
            return trim($_POST[self::$password]);
        return "";
    }
    
    private function getPassword2() {
        if (isset($_POST[self::$password2]))
            return trim($_POST[self::$password2]);
        return "";
    }

    private function getTempPassword() {
        if (isset($_COOKIE[self::$CookiePassword]))
            return trim($_COOKIE[self::$CookiePassword]);
        return "";
    }

    private function rememberMe() {
        return isset($_POST[self::$keep]) ||
                isset($_COOKIE[self::$CookiePassword]);
    }

}
