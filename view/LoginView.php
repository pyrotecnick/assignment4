<?php
/**
  * Solution for assignment 2
  * @author Daniel Toll
  */
namespace view;
class LoginView {
	/**
	 * These names are used in $_POST
	 * @var string
	 */
	private static $login = "LoginView::Login";
	private static $logout = "LoginView::Logout";
	private static $name = "LoginView::UserName";
	private static $password = "LoginView::Password";
	private static $cookieName = "LoginView::CookieName";
	private static $CookiePassword = "LoginView::CookiePassword";
	private static $keep = "LoginView::KeepMeLoggedIn";
	private static $messageId = "LoginView::Message";
	/**
	 * This name is used in session
	 * @var string
	 */
	private static $sessionSaveLocation = "\\view\\LoginView\\message";
	/**
	 * view state set by controller through setters
	 * @var boolean
	 */
	private $loginHasFailed = false;
	private $loginHasSucceeded = false;
	private $userDidLogout = false;
	/**
	 * @var \model\LoginModel
	 */
	private $model;
	/**
	 * @param \model\LoginModel $model
	 */
	public function __construct(\model\LoginModel $model) {
		self::$sessionSaveLocation .= \Settings::APP_SESSION_NAME;
		$this->model = $model;
	}
        
        public function getClass(){
            return "LoginView";
        }
	/**
	 * accessor method for login attempts
	 * both by cookie and by form
	 * 
	 * @return boolean true if user did try to login
	 */
	public function userWantsToLogin() {
		return isset($_POST[self::$login]) || 
			   isset($_COOKIE[self::$cookieName]);
	}
	/**
	 * Accessor method for logout events
	 * 
	 * @return boolean true if user tried to logout
	 */
	public function userWantsToLogout() {
		return isset($_POST[self::$logout]);	
	}
	/**
	 * Accessor method for login credentials
	 * @return \model\UserCredentials
	 */
	public function getCredentials() {
		return new \model\UserCredentials($this->getUserName(), 
									$this->getPassword(),
									$this->getTempPassword(),
									$this->getUserClient());
	}
	public function getUserClient() {
		return new \model\UserClient($_SERVER["REMOTE_ADDR"], $_SERVER["HTTP_USER_AGENT"]);
	}
	/**
	 * Tell the view that login has failed so that it can show correct message
	 *
	 * call this when login has failed
	 */
	public function setLoginFailed() {
		$this->loginHasFailed = true;
	}
	/**
	 * Tell the view that login succeeded so that it can show correct message
	 *
	 * call this if login succeeds
	 */
	public function setLoginSucceeded() {
		$this->loginHasSucceeded = true;	
	}
	/**
	 * Tell the view that logout happened so that it can show correct message
	 *
	 * call this when user logged out
	 */
	public function setUserLogout() {
		$this->userDidLogout = true;	
	}
	/**
	 * Create HTTP response
	 *
	 * Should be called after a login attempt has been determined
	 * @sideeffect Sets cookies!
	 * @return String HTML
	 */
	public function response() {
		if ($this->model->isLoggedIn($this->getUserClient())) {
			return $this->doLogoutForm();
		} else {
			return $this->doLoginForm();
		}
	}
	/**
	 * @sideeffect Sets cookies!
	 * @return [String HTML
	 */
	private function doLogoutForm() {
		$message = "";
		//Correct Login Message
		if ($this->loginHasSucceeded === true) {
			$message = "Welcome";
			if ($this->rememberMe()) {
				if (isset($_COOKIE[self::$CookiePassword])) {
					$message .= " back with cookie";
				} else {
					$message .= " and you will be remembered";
				}
			}
			$this->redirect($message);
		} else {
			$message = $this->getSessionMessage();
			
		}
		//Set new cookies
		if ($this->rememberMe()) {
			$this->setNewTemporaryPassword(); 
		} else {
			$this->unsetCookies();
		}
		//generate HTML
		return $this->getLogoutButtonHTML($message);
	}
	/**
	 * @sideeffect Sets cookies!
	 * @return [String HTML
	 */
	private function doLoginForm() {
		$message = "";
		//Correct messages
		if ($this->userWantsToLogout() && $this->userDidLogout) {
			$message = "Bye bye!";
			$this->redirect($message);
		} else if ($this->userWantsToLogin() && $this->getTempPassword() != "") {
			$message =  "Wrong information in cookies";
		} else if ($this->userWantsToLogin() && $this->getRequestUserName() == "") {
			$message =  "Username is missing";
		} else if ($this->userWantsToLogin() && $this->getPassword() == "") {
			$message =  "Password is missing";
		} else if ($this->loginHasFailed === true) {
			$message =  "Wrong name or password";
		} else {
			$message = $this->getSessionMessage();
		}
		//cookies
		$this->unsetCookies();
		
		//generate HTML
		return $this->generateLoginFormHTML($message);
	}
	private function redirect($message) {
		$_SESSION[self::$sessionSaveLocation] = $message;
		$actual_link = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
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
		setcookie(self::$cookieName, "", time()-1);
		setcookie(self::$CookiePassword, "", time()-1);
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
	private function getLogoutButtonHTML($message) {
		return "<form  method='post' >
			<p id='" . self::$messageId . "'>$message</p>
			<input type='submit' name='" . self::$logout . "' value='logout'/>
			</form>";
	}
	private function generateLoginFormHTML($message) {
		return "<form method='post' > 
				<fieldset>
					<legend>Login - enter Username and password</legend>
					<p id='".self::$messageId."'>$message</p>
					<label for='".self::$name."'>Username :</label>
					<input type='text' id='".self::$name."' name='".self::$name."' value='".$this->getRequestUserName()."'/>
					<label for='".self::$password."'>Password :</label>
					<input type='password' id='".self::$password."' name='".self::$password."'/>
					<label for='".self::$keep."'>Keep me logged in  :</label>
					<input type='checkbox' id='".self::$keep."' name='".self::$keep."'/>
					
					<input type='submit' name='".self::$login."' value='login'/>
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