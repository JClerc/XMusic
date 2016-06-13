<?php

// Url
// -------------------------------

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());


// Form
// -------------------------------

$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());


// Twig
// -------------------------------

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => PHP_ROOT . 'src/views',
));


// Our assets manager
// -------------------------------

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addFunction(new \Twig_SimpleFunction('asset', function ($asset) use ($app) {

        $parts = explode('/', $asset);
        
        // Detect if we asked for a vendor resource or not
        $vendor = $parts[0] !== 'app';

        // File extension
        $extension = $parts[1];

        // And his path
        $assetPath = $app['request']->getBasePath() . '/assets/' . $asset;
        
        // Only CSS and JS are minied
        if ($extension === 'css' or $extension === 'js') {
            
            // Vendor resources are already minified
            if (!$vendor) {

                // Input and output
                $assetFile = PHP_ROOT . 'web/assets/' . $asset . '.' . $extension;
                $minifiedFile = PHP_ROOT . 'web/assets/' . $asset . '.min.' . $extension;

                // Minify only if input exists and if output is outdated
                if (is_file($assetFile) and (!is_file($minifiedFile) or filemtime($minifiedFile) < filemtime($assetFile))) {
                    $minifier = $extension === 'css' ? new \MatthiasMullie\Minify\CSS($assetFile) : new \MatthiasMullie\Minify\JS($assetFile);
                    $minifier->minify($minifiedFile);
                }

            }
            
            // Return minified path
            return $assetPath . '.min.' . $extension;
        } else {
            return $assetPath;
        }
    }));

    return $twig;
}));
