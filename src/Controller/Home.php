<?php

namespace Controller;

use \Silex\Application;
use \Silex\ControllerProviderInterface;

/**
 * Home page, that matchs webroot
 */
class Home implements ControllerProviderInterface {

    private $config = [
        'title' => 'Home'
    ];

    public function connect(Application $app) {

        $controllers = $app['controllers_factory'];

        $controllers->get('/', function (Application $app) {
            
            return $app['twig']->render('pages/home.twig', $this->config);

        })->bind('home');

        return $controllers;
    }

}
