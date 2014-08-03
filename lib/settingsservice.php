<?php

namespace OCA\ContactsToFb\Lib;

use \OCP\IConfig;
use \OCA\Encryption\Crypt;

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
     * The FRITZ!Box password.
     *
     * @var string
     */
    protected $password;


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
            'password' => $this->getPassword(),
        );
    }

    /**
     * Loads and decrypts the settings.
     */
    public function load()
    {
        $appName = $this->appName;

        $this->setUrl($this->config->getAppValue($appName, 'url'));

        $pwEnc = $this->config->getAppValue($appName, 'password');
        $this->setPassword($password = Crypt::symmetricDecryptFileContent($pwEnc, ''));
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

        if (isset($settings['password'])) {
            $password = Crypt::symmetricEncryptFileContent($settings['password'], '');
            $this->config->setAppValue($appName, 'password', $password);
        }

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
}
