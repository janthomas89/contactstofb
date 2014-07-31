<?php

/**
 * The application configuration.
 *
 * @author Jan Thomas <jan.thomas@rwth-aachen.de>
 * @copyright Jan Thomas 2014
 * @license https://github.com/janthomas89/contactstofb/blob/master/LICENSE
 */

namespace OCA\ContactsToFb\AppInfo;

/* Add an navigation entry. */
\OCP\App::addNavigationEntry(array(
    'id' => 'contactstofb',
    'order' => 100,
    'href' => \OCP\Util::linkToRoute('contactstofb.page.index'),
    'icon' => \OCP\Util::imagePath('contactstofb', 'app.svg'),
    'name' => \OC_L10N::get('contactstofb')->t('Contacts to FRITZ!Box')
));

/* Register cron job for syncing contacts to FRRITZ!Box */
\OCP\Backgroundjob::addRegularTask('OCA\ContactsToFb\Cron\SyncContacts', 'run');
