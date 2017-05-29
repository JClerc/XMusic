<?php

namespace Controller;

use \Silex\Application;
use \Silex\ControllerProviderInterface;

/**
 * See list of tracks in an album
 */
class Album implements ControllerProviderInterface {

    private $config = [
        'title' => 'Album'
    ];

    public function connect(Application $app) {

        $controllers = $app['controllers_factory'];

        // We must have the album id
        $controllers->get('/{id}', function (Application $app, $id) {
            
            return $app['twig']->render('pages/album.twig', $this->config + [
                // Return album data and all his tracks
                'album' => $app['music_searcher']->getAlbum($id)
            ]);

        })->bind('album');

        return $controllers;
    }

}
