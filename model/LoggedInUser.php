<?php
/**
  * Solution for assignment 2
  * @author Daniel Toll
  */
namespace model;
/**
 * Contains information of the user that is logged in
 */
class LoggedInUser {
	/**
	 * Only username is saved since the credentials contains passwords
	 * @var String
	 */
	private $userName;
	/**
	 * UserClient of the logged in user
	 * @var UserClient
	 */
	private $client;
	public function __construct(UserCredentials $uc) {
		$this->userName = $uc->getName();
		$this->client = $uc->getClient();
		
	}
	/**
	 * Checks if the UserClient is the same as last session
	 * @param  UserClient $client 
	 * @return boolean
	 */
	public function sameAsLastTime(UserClient $client) {
		return $client->isSame($this->client);
	}
	public function getUserName() {
		return $this->userName;
	}
}