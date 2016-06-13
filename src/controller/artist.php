<?php

namespace Controller;

use \Silex\Application;
use \Silex\ControllerProviderInterface;

/**
 * See list of tracks produced by an artist
 */
class Artist implements ControllerProviderInterface {

    private $config = [
        'title' => 'Artist'
    ];

    public function connect(Application $app) {

        $controllers = $app['controllers_factory'];

        // We must have the artist id
        $controllers->get('/{id}', function (Application $app, $id) {
            
            return $app['twig']->render('pages/artist.twig', $this->config + [
                // Return artist data and few tracks
                'artist' => $app['music_searcher']->getArtist($id)
            ]);

        })->bind('artist');

        return $controllers;
    }

}
