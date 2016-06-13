<?php

namespace Controller;

use \Silex\Application;
use \Silex\ControllerProviderInterface;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * This route is used to download songs
 */
class Download implements ControllerProviderInterface {

    private $config = [
        'title' => 'Download'
    ];

    public function connect(Application $app) {

        $controllers = $app['controllers_factory'];

        // This route searchs if a download is available or not
        // And if needed, it prepare the download (= cache)
        $controllers->post('/{artist}/{track}', function (Application $app, Request $request, $artist, $track) {

            // Do we need to prepare our file ?
            $prepare = $request->get('prepare') !== 'false';

            // Find music
            $match = $app['music_provider']->match($artist, $track);

            // Success is true if we don't download anything, false otherwise
            $success = !$prepare;

            if ($prepare and !$match->isEmpty()) {
                // If we downloaded the file, then it's a success !
                $success = !empty($app['music_provider']->download($match));
            }

            // It should be ajax
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => $success,
                    'id' => $match->getData('id'),
                    'artist' => $artist,
                    'track' => $track
                ]);
            } else {
                return $app['twig']->render('pages/download.twig', $this->config);
            }

        })->bind('download_search');

        // This is fake route, as it has no content, but used to generate link to download page
        $controllers->get('/', function (Application $app) {
            return $app->redirect($app['url_generator']->generate('home'));
        })->bind('download');

        // Here is the route providing cached download
        $controllers->get('/{id}/{artist}/{track}', function (Application $app, $id, $artist, $track) {

            $file = $app['music_provider']->get($id);

            if (empty($file)) {
                return $app['twig']->render('pages/download.twig', $this->config);
            } else {
                return $app->sendFile($file)
                           ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $artist . ' - ' . $track . '.mp3');
            }

        })->bind('download_get');

        return $controllers;
    }

}
