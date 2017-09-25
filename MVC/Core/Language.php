<?php

namespace Core;

use App\Config;

class Language
{
    public static $lang;

    public static function get($param, $args = false){
        if(empty(self::$param)){
            self::$lang = self::loadLang();
        }
        
        if(!empty($param) and isset(self::$lang[$param])){
            return $args?vsprintf(self::$lang[$param],$args):self::$lang[$param];
        }

        return false;
    }


    private static function loadLang() {
        $lang = Config::LANG;
        $file = "../App/Language/" . $lang . ".php";

        require_once $file;

        return $lang::getLang();
    }
}
