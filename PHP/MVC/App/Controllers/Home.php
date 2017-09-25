<?php

namespace App\Controllers;

use Core\Controller;
use Core\Language;
use Core\View;

class Home extends Controller
{
    public function indexAction(){

        $welcome['name'] = "Visitor"; //used by example 1

        $data['name'] = "Visitor"; //used by example 2
        $data['welcome'] = Language::get("welcome", $welcome);

        View::renderTemplate('index.twig', $data);
    }
}
