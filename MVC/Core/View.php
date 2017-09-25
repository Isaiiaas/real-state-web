<?php

namespace Core;
use App\Config;
use Utils\Utils;

/**
 * View
 *
 * PHP version 5.4
 */
class View
{

    /**
     * Render a view file
     *
     * @param string $view  The view file
     * @param array $args  Associative array of data to display in the view (optional)
     *
     * @return void
     */

    public static function jsonOutput($data){
        header('Content-Type: application/json');
        echo json_encode($data);
    }


    /**
     * Render a view template using Twig
     *
     * @param string $template  The template file
     * @param array $args  Associative array of data to display in the view (optional)
     *
     * @return void
     */
    public static function renderTemplate($template, $args = [])
    {
        static $twig = null;

        //Global VIEW vars
        $args['base_url'] = Config::URL;

        //Load Language
        $args['lang'] = self::getLang();

        if ($twig === null) {
            $loader = new \Twig_Loader_Filesystem('../App/Views');
            $twig = new \Twig_Environment($loader, array('debug' => true));

            $twig->addExtension(new \Twig_Extension_Debug());
        }

        echo $twig->render($template, $args);
    }

    private static function getLang(){
        $lang = Config::LANG;
        $file = "../App/Language/".$lang.".php";

        require_once $file;

        return $lang::getLang();
    }
}
