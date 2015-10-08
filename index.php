<?php
 /**
  * Solution for assignment 2
  * @author Daniel Toll
  */
require_once("Settings.php");
require_once("controller/MasterController.php");
require_once("view/DateTimeView.php");
require_once("view/LayoutView.php");
if (Settings::DISPLAY_ERRORS) {
	error_reporting(-1);
	ini_set('display_errors', 'ON');
}
//session must be started before LoginModel is created
session_start(); 
//Controller must be run first since state is changed
$m = new \model\LoginModel();
$mc = new \controller\MasterController($m);
$mc->handleInput();

//Generate output
$view = $mc->generateOutput();
$dtv = new \view\DateTimeView();
$layoutView = new \view\LayoutView();
$layoutView->render($m->isLoggedIn($view->getUserClient()), $view, $dtv);