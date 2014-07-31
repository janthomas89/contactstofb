<?php

namespace OCA\ContactsToFb\AppInfo;

use \OCP\AppFramework\App;
use \OCA\ContactsToFb\Controller\PageController;

/**
 * The application definition.
 *
 * @author Jan Thomas <jan.thomas@rwth-aachen.de>
 * @copyright Jan Thomas 2014
 * @license https://github.com/janthomas89/contactstofb/blob/master/LICENSE
 */
class Application extends App
{
    public function __construct (array $urlParams = array())
    {
        parent::__construct('contactstofb', $urlParams);

        $container = $this->getContainer();

        /* Application PageController */
        $container->registerService('PageController', function($c) {
            return new PageController(
                $c->query('AppName'),
                $c->query('Request')
            );
        });

        /* Other services ... */
    }
}
