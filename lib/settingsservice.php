<?php

namespace OCA\ContactsToFb\Lib;

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


    public function __construct()
    {
        /**
         * @todo complete implementation!
         */

        $this->setPassword('test123');
        $this->setUrl('fritz.box');
    }

    public function getSettingsArray()
    {
        return array(
            'url' => $this->getUrl(),
            'password' => $this->getPassword(),
        );
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
