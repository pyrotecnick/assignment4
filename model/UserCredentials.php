<?php
/**
  * Solution for assignment 2
  * @author Daniel Toll
  */
namespace model;
class UserCredentials {
	private $userName;
	private $password;
	private $tempPassword;
	private $client;
	
	public function __construct($name, $password, $tempPassword, UserClient $client) {
		$this->userName = htmlspecialchars($name);
		$this->password = htmlspecialchars($password);
		$this->tempPassword = $tempPassword;
		$this->client = $client;
	}
	public function getName() {
		return $this->userName;
	}
	public function getPassword() {
		return $this->password;
	}
	public function getTempPassword() {
		return $this->tempPassword;
	}
	public function getClient()  {
		return $this->client;
	}
}