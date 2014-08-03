<?php

namespace OCA\ContactsToFb\AppInfo;

/**
 * Defining the applications' routes.
 *
 * @author Jan Thomas <jan.thomas@rwth-aachen.de>
 * @copyright Jan Thomas 2014
 * @license https://github.com/janthomas89/contactstofb/blob/master/LICENSE
 */
$application = new Application();

$application->registerRoutes($this, array('routes' => array(
    array('name' => 'page#index', 'url' => '/', 'verb' => 'GET'),
    array('name' => 'page#synchronize', 'url' => '/synchronize', 'verb' => 'POST'),
    array('name' => 'page#settings', 'url' => '/settings', 'verb' => 'POST'),
)));
