<?php

namespace OCA\ContactsToFb\AppInfo;

use \OCP\AppFramework\App;
use \OCA\ContactsToFb\Controller\PageController;
use \OCA\ContactsToFb\Lib\SettingsService;
use \OCA\ContactsToFb\Lib\SyncService;

require_once(dirname(__FILE__) . '/../vendor/fritzbox_api_php/lib/fritzbox_api.class.php');
require_once dirname(__FILE__) . '/../../files_encryption/lib/crypt.php';

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
                $c->query('Request'),
                $c->query('ServerContainer')->getURLGenerator(),
                $c->query('SettingsService'),
                $c->query('SyncService')
            );
        });

        /* Other services ... */
        $container->registerService('SettingsService', function($c) {
            return new SettingsService(
                $c->query('ServerContainer')->getConfig(),
                $c->query('AppName')
            );
        });
        $container->registerService('SyncService', function($c) {
            return new SyncService(
                $c->query('SettingsService')
            );
        });
    }
}
