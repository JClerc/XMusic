<?php

// Home
// -------------------------------

$app->mount('/', new Controller\Home());


// Search
// -------------------------------

$app->mount('/tracks', new Controller\Search('track'));
$app->mount('/albums', new Controller\Search('album'));
$app->mount('/artists', new Controller\Search('artist'));


// Artist's tracks
// -------------------------------

$app->mount('/artist', new Controller\Artist());


// Album's tracks
// -------------------------------
$app->mount('/album', new Controller\Album());


// Download
// -------------------------------

$app->mount('/download', new Controller\Download());


// Error handling
// -------------------------------

$app->error(function (\Exception $e, $code) use ($app) {

    if ($app['config']['debug']) return;

    return $app['twig']->render('pages/error.twig', [
        'title' => 'Error'
    ]);

});
