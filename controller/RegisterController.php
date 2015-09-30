<?php
/**
  * @author Nick Lashinski
  */
namespace controller;
require_once("model/RegisterModel.php");
require_once("view/RegisterView.php");
class RegisterController {
	private $model;
	private $view;
	public function __construct(\model\RegisterModel $model, \view\RegisterView $view) {
		$this->model = $model;
		$this->view =  $view;
	}
	public function doControl() {
		
		$userClient = $this->view->getUserClient();
		if ($this->model->isLoggedIn($userClient)) {
			if ($this->view->userWantsToLogout()) {
				$this->model->doLogout();
				$this->view->setUserLogout();
			}
		} else {
			
			if ($this->view->userWantsToRegister()) {
				$uc = $this->view->getCredentials();
				if ($this->model->doRegister($uc) == true) {
					$this->view->setRegisterSucceeded();
				} else {
					$this->view->setRegisterFailed();
				}
			}
		}
		$this->model->renew($userClient);
	}
}