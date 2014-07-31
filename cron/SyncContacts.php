<?php

namespace OCA\ContactsToFb\Cron;

use OCA\ContactsToFb\Application;

/**
 * Cronjob for syncing the contacts.
 *
 * @author Jan Thomas <jan.thomas@rwth-aachen.de>
 * @copyright Jan Thomas 2014
 * @license https://github.com/janthomas89/contactstofb/blob/master/LICENSE
 */
class SyncContacts
{
    public static function run()
    {
        $app = new Application();
        $container = $app->getContainer();
        //$container->query('SyncService')->run();
    }
}
