<?php

namespace OCA\ContactsToFb\Lib;

/**
 * API class for changes in the FRITZS!Box web interface.
 * Inspired by https://github.com/carlos22/fritzbox_api_php
 *
 * @author Jan Thomas <jan.thomas@rwth-aachen.de>
 * @copyright Jan Thomas 2014
 * @license https://github.com/janthomas89/contactstofb/blob/master/LICENSE
 */
class FritzboxAPI
{
    const EMPTY_SID = '0000000000000000';

    /**
     * @var string The Fritz!Box url.
     */
    protected $fritzboxURL;

    /**
     * @var string The Fritz!Box password.
     */
    protected $password;

    /**
     * @var string The Fritz!Box username.
     */
    protected $username;

    /**
     * @var string The Fritz!Box session ID.
     */
    protected $sid;

    /**
     * @var string The last response body.
     */
    protected $respBody;

    /**
     * @var string The last response header.
     */
    protected $respHeader;

    /**
     * Instanciates the API.
     *
     * @param string $fritzboxURL
     * @param string $password
     * @param string $username
     */
    public function __construct($fritzboxURL = 'http://fritz.box', $password = '', $username= '')
    {
        $this->fritzboxURL = $fritzboxURL;
        $this->password = $password;
        $this->username = $username;

        $this->login();
    }

    /**
     * Logout on destructing the API.
     */
    public function __destruct()
    {
        $this->logout();
    }

    /**
     * Performes login with the given username and password. Sets the
     * Session-ID as a property.
     *
     * Newer firmwares (xx.04.74 and newer) need a challenge-response mechanism
     * to prevent Cross-Site Request Forgery attacks see
     * http://www.avm.de/de/Extern/Technical_Note_Session_ID.pdf for details.
     */
    protected function login()
    {
        $this->sid = self::EMPTY_SID;

        /* Return if we are logge din already. */
        $sessionStatus = $this->querySessionStatus();
        if ($sessionStatus->iswriteaccess == 1) {
            $this->sid = $sessionStatus->SID;
            return;
        }

        /* Perform login */
        $response = $this->post(array(
            'getpage'  => '/login.lua',
            'response' => $this->getResponseByChallenge((string)$sessionStatus->Challenge),
            'username' => $this->username,
        ));

        /* Extract an error message, if given */
        preg_match('@<p class="error_text">(.*?)</p>@is', $response, $matches);
        if (isset($matches[1])) {
            $this->error(str_replace('&nbsp;', ' ', $matches[1]));
        }

        /* Extract the SID from the response */
        $location = isset($this->respHeader['Location']) ? $this->respHeader['Location'] : '';
        preg_match('@sid=([A-Fa-f0-9]{16})@i', $location, $matches);
        if (isset($matches[1]) && $matches[1] != self::EMPTY_SID) {
            $this->sid = $matches[1];
        } else {
            $this->error('ERROR: Login failed with an unknown response');
        }
    }

    /**
     * Performs a logout request on the Fritz!Box.
     */
    protected function logout()
    {
        $this->post(array(
            'getpage' => '../html/de/menus/menu2.html',
            'security:command/logout' => 'logout',
        ));
    }

    /**
     * Queries the FRITZ!Box session status.
     *
     * @return SimpleXMLElement
     */
    protected function querySessionStatus()
    {
        $ch = curl_init($this->fritzboxURL . '/login_sid.lua');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        return simplexml_load_string($response);
    }

    /**
     * Generates a response for a given challenge.
     *
     * @param string $challenge
     * @return string
     */
    protected function getResponseByChallenge($challenge)
    {
        return $challenge . '-' . md5(mb_convert_encoding($challenge . '-' . $this->password, 'UCS-2LE', 'UTF-8'));
    }

    /**
     * Perform a POST request on the FRITZ!Box.
     *
     * @param array $formFields An associative array with the POST fields.
     * @return string The raw HTML returned by the Fritz!Box.
     */
    public function post(array $formFields = array())
    {
        $ch = curl_init();
        if (strpos($formFields['getpage'], '.lua') > 0) {
            curl_setopt($ch, CURLOPT_URL, $this->fritzboxURL . $formFields['getpage'] . '?sid=' . $this->sid);
            unset($formFields['getpage']);
        } else {
            /* Add the sid, if it is already set. */
            if ($this->sid != self::EMPTY_SID) {
                $formFields['sid'] = $this->sid;
            }
            curl_setopt($ch, CURLOPT_URL, $this->fritzboxURL . '/cgi-bin/webcm');
        }

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formFields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $this->respBody = curl_exec($ch);

        if ($this->respBody === false) {
            throw new Exception(curl_error($ch) . "\n" . curl_errno($ch));
        }

        $this->buildHeaderArray($ch, $this->respBody);

        curl_close($ch);

        return $this->respBody;
    }

    /**
     * Builds the header array for the last request.
     *
     * @param Curl-Handle $ch
     */
    protected function buildHeaderArray($ch)
    {
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = explode("\n", substr($this->respBody, 0, $headerSize));

        foreach ($header as $h) {
            $pos = strpos($h, ':');
            if ($pos > 0) {
                $this->respHeader[substr($h, 0, $pos)] = substr($h, $pos);
            } else {
                $this->respHeader[] = $h;
            }
        }

        $this->respBody = substr($this->respBody, $headerSize);
    }

    /**
     * Upload a file to the FRITZ!Box. All files are uploaded to the
     * firmwarecfg cgi-program.
     *
     * @param array $params The GET parameter.
     * @param array $files The upload files (filename, type, content). For instance:
     *     $fileFields = array(
     *         file1' => array(
     *             'type' => 'text/xml',
     *             'content' => '...your raw file content goes here...',
     *             'filename' = 'filename.xml'
     *         )
     * @return string The raw HTML returned by the Fritz!Box.
     */
    public function postFile(array $params = array(), array $files = array())
    {
        $ch = curl_init();

        /* 'sid' MUST be the first POST variable (otherwise it will not work). */
        if ($this->sid != self::EMPTY_SID) {
            $params = array_merge(array('sid' => $this->sid), $params);
        }

        curl_setopt($ch, CURLOPT_URL, $this->fritzboxURL . '/cgi-bin/firmwarecfg');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if(count($files) == 0) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        } else {
            $header = $this->buildMultipartRequest($params, $files);
            curl_setopt($ch, CURLOPT_HTTPHEADER , array(
                'Content-Type: multipart/form-data; boundary=' . $header['delimiter'],
                'Content-Length: ' . strlen($header['data'])
            ));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $header['data']);
        }

        $output = curl_exec($ch);

        if(curl_errno($ch)) {
            $this->error(curl_error($ch) . ' ('.curl_errno($ch) . ')');
        }

        /* Extract an error message, if given */
        preg_match('@<p class="ErrorMsg">(.*?)</p>@is', $output, $matches);
        if (isset($matches[1])) {
            $this->error(str_replace('&nbsp;', ' ', $matches[1]));
        }

        curl_close($ch);

        return $output;
    }

    /**
     * Builds a multipart request.
     *
     * @param array $params
     * @param array $fieles
     * @return array
     */
    protected function buildMultipartRequest(array $params, array $fieles)
    {
        $delimiter = '-------------' . uniqid();
        $data = '';

        /* Build post fields */
        foreach ($params as $name => $content) {
            $data .= "--" . $delimiter . "\r\n";
            $data .= 'Content-Disposition: form-data; name="' . urlencode($name) . '"';
            $data .= "\r\n\r\n";
            $data .= $content;
            $data .= "\r\n";
        }

        /* Build file fields */
        foreach ($fieles as $name => $file) {
            $data .= "--" . $delimiter . "\r\n";
            $data .= 'Content-Disposition: form-data; name="' . urlencode($name) . '";';
            $data .= ' filename="' . $file['filename'] . '"' . "\r\n";
            //$data .= 'Content-Transfer-Encoding: binary'."\r\n";
            $data .= 'Content-Type: ' . $file['type'] . "\r\n";
            $data .= "\r\n";
            $data .= $file['content'] . "\r\n";
        }

        /* last delimiter */
        $data .= '--' . $delimiter . "--\r\n";

        return array(
            'delimiter' => $delimiter,
            'data' => $data
        );
    }

    /**
     * Perform a GET request on the FRITZ!Box.
     *
     * @param array $params The GET parameter.
     * @return string The raw HTML returned by the Fritz!Box.
     */
    public function get($params = array())
    {
        if ($this->sid != self::EMPTY_SID) {
            $params['sid'] = $this->sid;
        }

        $ch = curl_init();
        if (strpos($params['getpage'], '.lua') > 0) {
            $getpage = $params['getpage'] . '?';
            unset($params['getpage']);
        } else {
          $getpage = '/cgi-bin/webcm?';
        }

        curl_setopt($ch, CURLOPT_URL, $this->fritzboxURL . $getpage . http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    /**
     * Throws an error.
     *
     * @param string $msg The error message.
     */
    protected function error($msg)
    {
        throw new \Exception($msg);
    }

    /**
     * Returns the FRITZ!Box session id.
     * @return type
     */
    public function getSID()
    {
        return $this->sid;
    }
}
