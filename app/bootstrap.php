<?php

// Autoloader
// -------------------------------

$loader = require_once PHP_ROOT . 'vendor/autoload.php';

$loader->add('Controller', PHP_ROOT . 'src');
$loader->add('Model',      PHP_ROOT . 'src');
$loader->add('Entity',     PHP_ROOT . 'src');


// Init app
// -------------------------------

$app = new Silex\Application();


// Config
// -------------------------------

// SERVER_NAME use server wide config, so it doesn't rely on client's headers
switch ($_SERVER['SERVER_NAME']) {
    case 'localhost':
        $configFile = 'development';
        break;
    
    case 'dev.website.fr':
        $configFile = 'pre-production';
        break;
    
    default:
        $configFile = 'production';
        break;
}

$config = require __DIR__ . '/config/' . $configFile . '.php';
if (!is_array($config)) return false;

$app['config'] = $config;
if ($app['config']['debug']) {
    $app['debug'] = true;
    ini_set('display_errors', true);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', false);
    error_reporting(0);
}


// Services
// -------------------------------

require_once __DIR__ . '/services.php';

foreach ($config['twig']['globals'] as $key => $value) {
    $app['twig']->addGlobal($key, $value);
}


// Providers
// -------------------------------

$app->register(new Model\CacheServiceProvider());


// Models
// -------------------------------

$factory = function($class) {
    return function($app) use ($class) {
        return new $class($app);
    };
};

$app['curl'] = $factory(Model\Curl::class);

$classname = 'Model\\' . $config['services']['music_searcher'] . 'Searcher';
$app['music_searcher'] = $factory($classname);

$classname = 'Model\\' . $config['services']['music_provider'] . 'Provider';
$app['music_provider'] = $factory($classname);


// Routes
// -------------------------------

require_once __DIR__ . '/routes.php';


// Done !
// -------------------------------

return $app;
