<?php

namespace OCA\ContactsToFb\Lib;

use \OCP\IConfig;
use \OCA\Encryption\Crypt;
use \OCA\Contacts\App as ContactsApp;

/**
 * Service for providing and manipulating the settings.
 *
 * @author Jan Thomas <jan.thomas@rwth-aachen.de>
 * @copyright Jan Thomas 2014
 * @license https://github.com/janthomas89/contactstofb/blob/master/LICENSE
 */
class SettingsService
{
    /**
     * @var IConfig
     */
    protected $config;

    /**
     * @var string
     */
    protected $appName;

    /**
     * The FRITZ!Box url.
     *
     * @var string
     */
    protected $url;

    /**
     * The FRITZ!Box user.
     *
     * @var string
     */
    protected $user;

    /**
     * The FRITZ!Box password.
     *
     * @var string
     */
    protected $password;

    /**
     * ID of the addressbook to sync.
     *
     * @var string
     */
    protected $addressbook;


    public function __construct(IConfig $config, $appName)
    {
        $this->config = $config;
        $this->appName = $appName;

        $this->load();
    }

    /**
     * Returns the settings as an array.
     *
     * @return array
     */
    public function getSettingsArray()
    {
        return array(
            'url' => $this->getUrl(),
            'user' => $this->getUser(),
            'password' => $this->getPassword(),
            'addressbook' => $this->getAddressbook(),
        );
    }

    /**
     * Loads and decrypts the settings.
     */
    public function load()
    {
        $appName = $this->appName;

        $this->setUrl($this->config->getAppValue($appName, 'url'));

        $this->setUser($this->config->getAppValue($appName, 'user'));

        $password = '';
        $pwEnc = $this->config->getAppValue($appName, 'password');
        if ($pwEnc != '') {
            $password = Crypt::symmetricDecryptFileContent($pwEnc, '');
        }
        $this->setPassword($password);

        $this->setAddressbook($this->config->getAppValue($appName, 'addressbook'));
    }

    /**
     * Saves the settings giveen as an array.
     *
     * @param array $settings
     */
    public function save(array $settings)
    {
        $appName = $this->appName;

        if (isset($settings['url'])) {
            $this->config->setAppValue($appName, 'url', $settings['url']);
        }

        if (isset($settings['user'])) {
            $this->config->setAppValue($appName, 'user', $settings['user']);
        }

        if (isset($settings['password'])) {
            $password = Crypt::symmetricEncryptFileContent($settings['password'], '');
            $this->config->setAppValue($appName, 'password', $password);
        }

        if (isset($settings['addressbook'])) {
            $this->config->setAppValue($appName, 'addressbook', $settings['addressbook']);
        }

        /* Remember the current user in order to run the cron as this user */
        $userId = \OC::$session->get('user_id');
        $this->config->setAppValue($appName, 'user_id', $userId);

        return true;
    }

    /**
     * Returns the url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets the url.
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Returns the user.
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the user.
     *
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Returns the password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets the password.
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Returns the ID of the addressbook.
     *
     * @return string
     */
    public function getAddressbook()
    {
        return $this->addressbook;
    }

    /**
     * Sets the ID of the addressbook.
     *
     * @param string $addressbook
     */
    public function setAddressbook($addressbook)
    {
        $this->addressbook = $addressbook;
    }

    /**
     * Returns the Addressbok which should be synced.
     *
     * @return \OCA\Contacts\Addressbook
     */
    public function getAddressBookInstance()
    {
        $parts = explode(':', $this->getAddressbook());

        $backendName = isset($parts[0]) ? $parts[0] : '';
        $addressBookId = isset($parts[1]) ? $parts[1] : '';

        /* Perform the addressbook retrival as an admin. */
        $origUserId = \OC::$session->get('user_id');
        $userId = $this->config->getAppValue($this->appName, 'user_id');
        \OC::$session->set('user_id', $userId);

        $contactsApp = new ContactsApp();
        $addressBook = $contactsApp->getAddressBook($backendName, $addressBookId);

        /* Reset the original user. */
        \OC::$session->set('user_id', $origUserId);

        return $addressBook;
    }
}
