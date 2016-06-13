<?php

// Require files
// -------------------------------

define('DS', DIRECTORY_SEPARATOR);
define('PHP_ROOT', __DIR__ . DS . '..' . DS);
$app = require_once PHP_ROOT . 'app' . DS . 'bootstrap.php';


// Run
// -------------------------------

if (is_object($app)) {
    $app->run();
} else {
    echo 'Sorry, something went wrong..';
}
