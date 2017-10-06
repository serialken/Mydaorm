<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
$loader->add('Html2Pdf_', __DIR__.'/../vendor/html2pdf/lib'); //ajout html to pdf
$loader->add('FirePHP', __DIR__.'/../vendor/bundles'); //ajout html to pdf
$loader->register();


// Change nested level max for xdebug with clean method
//if (false !== ini_get('xdebug.max_nesting_level')) {
//    ini_set('xdebug.max_nesting_level', 500);
//}

return $loader;

