<?php

namespace Model;

use \Silex\Application;
use \Silex\ServiceProviderInterface;

/**
 * A cache factory
 *
 * Return a new instance with provided $namespace, $defaultExpiration and $fileExtension
 * Also, it remember all caches used, and clear them at application shutdown
 *
 * @see \Model\Cache
 */
class CacheServiceProvider implements ServiceProviderInterface {

    private $caches = [];

    public function register(Application $app) {

        // Use reference, as PHP copy arrays...
        $caches = &$this->caches;

        $app['cache'] = $app->protect(function ($namespace, $defaultExpiration, $fileExtension) use ($app, &$caches) {
            if (!isset($caches[$namespace])) {
                $caches[$namespace] = new Cache($namespace, $defaultExpiration, $fileExtension);
            }
            return $caches[$namespace];
        });

    }

    public function boot(Application $app) {

        $caches = &$this->caches;

        $app->finish(function () use (&$caches) {
            foreach ($caches as $cache) {
                $cache->clearExpired();
            }
        });
    }

}
