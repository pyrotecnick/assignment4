<?php

/*
 * @author Nick Lashinski
 */

namespace controller;

require_once("model/LoginModel.php");
require_once("model/RegisterModel.php");

require_once("controller/LoginController.php");
require_once("controller/RegisterController.php");

require_once("view/NavigationView.php");
require_once("view/LoginView.php");
require_once("view/RegisterView.php");

class MasterController {

    private $navigationView;
    private $loginModel;

    public function __construct(\model\LoginModel $model) {
        $this->navigationView = new \view\NavigationView();
        $this->loginModel = $model;
    }

    public function handleInput() {
        $this->checkView();
        if ($this->navigationView->inRegistration()) {
            $rm = new \model\RegisterModel();
            $rv = new \view\RegisterView($rm);
            $rc = new \controller\RegisterController($rm, $rv);

            $rc->doControl();

            if ($rv->getRegisterSucceeded()) {
                $uc = $rv->getCredentials();
                $this->LoginViewSetup(TRUE, $uc->getName());
            } else {
                $this->view = $rc->getView();
            }
        } else {
            $empty = "";
            $this->LoginViewSetup(FALSE, $empty);
        }
    }

    public function generateOutput() {
        return $this->view;
    }

    private function checkView() {
        if ($this->navigationView == NULL) {
            $this->navigationView = new \view\NavigationView();
        }
    }

    private function LoginViewSetup($register, $name) {
        $v = new \view\LoginView($this->loginModel);
        if ($register == TRUE) {
            $v->setUserName($name);
            $v->setMessage();
        }
        $c = new \controller\LoginController($this->loginModel, $v);
        $c->doControl();

        $this->view = $c->getView();
    }

}
