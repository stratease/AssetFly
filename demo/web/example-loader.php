<?php
use stratease\AssetFly\AssetLoader;
use stratease\AssetFly\Filter\SassFilter;
use stratease\AssetFly\Filter\UglifyCssFilter;
/**
 * Generate composers autoload file, and point your web root to this demo/web/ folder.
 */
require_once("../../vendor/autoload.php");

// Instantiate loader, we are defining the css destination directory for the compiled files. It expects a path relative to web root
$assetLoader = new AssetLoader(['dumpCssDirectory' => '/css/'
                                                    'cache' => false]);

// Debug mode affects the output. Our compiler is smart enough to always run precompiles even in debug mode and leave the raw css/js use original files for troubleshooting purposes.
$assetLoader->setDebug(true); // try changing this to false and watch the network 

// our document root
$assetLoader->setWebDirectory(__DIR__);


// We have a default filter for a few libs under predefined filter groups "css", "js", and "sass".

// Lets setup a new sass filter group, and configure it appropriately for our environment.
$assetLoader->addFilter('sass1',
                                    new SassFilter('/usr/bin/sass', ['options' => '--debug-info']));
// Lets add some css minification to the same group. Filters are run in sequence
$assetLoader->addFilter('sass1',
                                    new UglifyCssFilter());

// setup our twig 
Twig_Autoloader::register();

// our templates are in twig-view...
$loader = new Twig_Loader_Filesystem(__DIR__.'/../twig-view/');

// lets see any errors...
$twig = new Twig_Environment($loader, ['strict_variables' => true, 'debug' => true, 'cache' => false]);
// ok register our extension..
$twig->addExtension(new TwigExtension($assetLoader));

// show our example!
$twig->display("page.twig");