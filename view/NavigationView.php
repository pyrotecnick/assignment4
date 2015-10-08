<?php

/* 
 * @author Nick Lashinski
 */

namespace view;

class NavigationView {
    
    private static $registerURL = "register";
    
    public function getRegistrURL(){
        return "?".self::$registerURL;
    }
    
    public function getLoginURL(){
        return "<a href=?>Back to login</a>";
    }
    
    public function userWantsToRegister(){
        if (isset($_GET[self::$registerURL])){
            return TRUE;
        }
        return FALSE;
    }
    
    public function inRegistration(){
        return isset($_GET[self::$registerURL]) == true;
    }
}