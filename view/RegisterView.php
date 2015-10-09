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
    private static $PasswordRepeat = "RegisterView::PasswordRepeat";
    private static $messageId = "RegisterView::Message";
    public $modelMessage;

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

    public function getClass() {
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
        return new \model\UserCredentials($this->getUserName(), $this->getPassword(), $this->getPasswordRepeat(), $this->getUserClient());
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
     * used in master controller to check if registration attempt succedes
     * 
     */
    public function getRegisterSucceeded(){
        return $this->registerHasSucceeded;
    }

    /**
     * Create HTTP response
     *
     * Should be called after a register attempt has been determined
     * @return String HTML
     * 
     */
    public function response() {
        return $this->doRegisterForm();
    }

    /**
     * @return String HTML
     */
    private function doRegisterForm() {
        $message = "";
        if ($this->userWantsToRegister() && $this->getRequestUserName() == "" && $this->getPassword() == "") {
            $message = "Username has too few characters, at least 3 characters. Password has too few characters, at least 6 characters.";
        } else if ($this->userWantsToRegister() && strlen($this->getRequestUserName()) < 3) {
            $message = "Username has too few characters, at least 3 characters.";
        } else if ($this->userWantsToRegister() && $this->dirtyUsername($this->getRequestUserName())) {
            $_POST[self::$name] = strip_tags($this->getRequestUserName());  //cleans up the requested username
            $message = "Username contains invalid characters.";
        } else if ($this->userWantsToRegister() && strlen($this->getPassword()) < 6) {
            $message = "Password has too few characters, at least 6 characters.";
        } else if ($this->userWantsToRegister() && strcmp($this->getPassword(), $this->getPasswordRepeat()) !== 0) {
            $message = "Passwords do not match.";
        } else if ($this->modelMessage != "") {
            $message = $this->modelMessage;
            $this->modelMessage = "";
        } else {
            $message = $this->getSessionMessage();
        }

        //generate HTML
        return $this->generateRegisterFormHTML($message);
    }

    /**
     * Checks for invalid characters in the requested username
     * @param type $username
     */
    private function dirtyUsername($username) {
        if (preg_match('/[^a-zA-Z0-9]+/', $username, $matches)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function getSessionMessage() {
        if (isset($_SESSION[self::$sessionSaveLocation])) {
            $message = $_SESSION[self::$sessionSaveLocation];
            unset($_SESSION[self::$sessionSaveLocation]);
            return $message;
        }
        return "";
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
					<label for='" . self::$PasswordRepeat . "'>Repeat Password :</label>
					<input type='password' id='" . self::$PasswordRepeat . "' name='" . self::$PasswordRepeat . "'/>
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

    private function getPasswordRepeat() {
        if (isset($_POST[self::$PasswordRepeat]))
            return trim($_POST[self::$PasswordRepeat]);
        return "";
    }

}
