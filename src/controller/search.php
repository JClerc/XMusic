<?php

namespace Controller;

use \Silex\Application;
use \Silex\ControllerProviderInterface;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\Form\Extension\Core\Type\FormType;

/**
 * This controller is used to search either a track, an album or an artist by specifing $mode
 */
class Search implements ControllerProviderInterface {

    private $config = [
        'title' => 'Search',
        'theme' => 'primary',
    ];

    private $theme = [
        'track' => 'success',
        'album' => 'primary',
        'artist' => 'warning',
    ];

    private $mode;

    // Mode has to be one of these: ["track", "album", "artist"]
    function __construct($mode) {
        $this->mode = $mode;
        $this->config['title'] = ucfirst($mode) . 's';
        $this->config['theme'] = $this->theme[$mode] ?: $this->config['theme'];
    }

    public function connect(Application $app) {

        $controllers = $app['controllers_factory'];


        // This route represents a search results
        $controllers->get('/{query}/{page}', function (Application $app, $query, $page) {

            return $app['twig']->render('pages/search.twig', $this->config + [

                // Form with current query
                'search' => $this->getSearchForm($app, [
                    'query' => $query
                ])->createView(),

                // And results
                'results' => $app['music_searcher']->search($this->mode, $query, $page),

                // Indicate how to display results
                'mode' => $this->mode,

                // Query is used for pagination
                'query' => $query

            ]);

        })->bind('results_' . $this->mode)->assert('page', '\d+')->value('page', 1);


        // Default search page, with no results
        // Also used as form action
        $controllers->match('/', function (Application $app, Request $request) {

            $form = $this->getSearchForm($app);
            $form->handleRequest($request);

            // If form is submitted, we redirect to results
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    
                    $formData = $form->getData();

                    // We need to redirect to correct mode and appends query
                    $url = $app['url_generator']->generate('results_' . $this->mode, [
                        'query' => $formData['query']
                    ]);

                    return $app->redirect($url);

                }
            }

            // Default search, no results here
            return $app['twig']->render('pages/search.twig', $this->config + [
                'search' => $form->createView()
            ]);

        })->bind('search_' . $this->mode);

        return $controllers;
    }

    // It the search form used for each mode
    protected function getSearchForm(Application $app, $default = []) {

        // Create builder
        $builder = $app['form.factory']->createBuilder(FormType::class, $default);

        // Set method and action
        $builder->setMethod('post');
        $builder->setAction($app['url_generator']->generate('search_' . $this->mode));

        // Add input
        $builder->add(
            'query',
            'text', 
            [
                'trim'       => true,
                'max_length' => 60,
                'required'   => true,
            ]
        );

        $builder->add('submit', 'submit');

        // Create form
        return $builder->getForm();

    }

}
