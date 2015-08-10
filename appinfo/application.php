<?php

namespace OCA\ContactsToFb\AppInfo;

use \OCP\AppFramework\App;
use \OCA\ContactsToFb\Controller\PageController;
use \OCA\ContactsToFb\Lib\SettingsService;
use \OCA\ContactsToFb\Lib\SyncService;
use \OCA\ContactsToFb\Lib\LogService;

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

        /* Autoloading vendor components. */
        $this->initAutoloading();

        $container = $this->getContainer();

        /* Application PageController */
        $container->registerService('PageController', function($c) {
            return new PageController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('ServerContainer')->getURLGenerator(),
                $c->query('SettingsService'),
                $c->query('SyncService'),
                $c->query('LogService')
            );
        });

        /* Other services ... */
        $container->registerService('SettingsService', function($c) {
            return new SettingsService(
                $c->query('ServerContainer')->getConfig(),
                $c->query('ServerContainer')->getUserSession(),
                $c->query('AppName')
            );
        });
        $container->registerService('SyncService', function($c) {
            return new SyncService(
                $c->query('SettingsService'),
                $c->query('LogService'),
                $c->query('AppStorage'),
                $c->query('Logger'),
                $c->query('AppName')
            );
        });
        $container->registerService('LogService', function($c) {
            return new LogService(
                $c->query('ServerContainer')->getDb()
            );
        });
        $container->registerService('Logger', function($c) {
            return $c->query('ServerContainer')->getLogger();
        });
        $container->registerService('AppStorage', function($c) {
            return $c->query('ServerContainer')->getAppFolder();
        });
    }

    /**
     * Initializes the autoloading of the vendor components.
     */
    protected function initAutoloading()
    {
        $loader = new \Composer\Autoload\ClassLoader();
        $loader->add('libphonenumber', dirname(__FILE__) . '/../vendor/libphonenumber-for-php/src/');

        $loader->addClassMap(array(
            'OCA\\Encryption\\Crypt' => dirname(__FILE__) . '/../../files_encryption/lib/crypt.php'
        ));

        $loader->register();
    }
}
