<?php

namespace OCA\ContactsToFb\Lib;

use \OpenCloud\Common\Log\Logger;
use \libphonenumber\PhoneNumberUtil;
use \libphonenumber\PhoneNumberType;

/**
 * Service for syncing the contacts.
 *
 * @author Jan Thomas <jan.thomas@rwth-aachen.de>
 * @copyright Jan Thomas 2014
 * @license https://github.com/janthomas89/contactstofb/blob/master/LICENSE
 */
class SyncService
{
    const PHONE_BOOK_ID = 0;

    /**
     * @var SettingsService
     */
    protected $settingsService;

    /**
     * @var LogService
     */
    protected $logService;

    /**
     * @var OC\Files\Node\Folder
     */
    protected $appStorage;

    /**
     * @var LogEntry
     */
    protected $logEntry;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string The apps name
     */
    protected $appName;

    /**
     * Addressbook which should be synced.
     *
     * @var \OCA\Contacts\Addressbook
     */
    protected $addressBook;

    /**
     * Constructor of the SyncService.
     *
     * @param SettingsService $settingsService
     * @param LogService $logService
     * @param Logger $logger The OC Logger
     * @param string $appName
     */
    public function __construct(
        SettingsService $settingsService,
        LogService $logService,
        OC\Files\Node\Folder $appStorage,
        Logger $logger,
        $appName
    ) {
        $this->settingsService = $settingsService;
        $this->logService = $logService;
        $this->appStorage = $appStorage;
        $this->logger = $logger;
        $this->appName = $appName;
    }

    /**
     * Runs the synchronization process.
     *
     * @param boolean $manually
     * @return array
     */
    public function run($manually = false)
    {
        $result = array('status' => 'success');

        $this->initLogEntry($manually);
        $this->loadAddressBook();

        /* Only sync, when new/modified contacts are available. */
        if (!$manually
            && $this->addressBook->lastModified() < $this->logService->getLastLogDate()
        ) {
            $this->logEntry->setStatus(LogEntry::STATUS_SKIPPED);
            $this->logService->insert($this->logEntry);

            $result['msg'] = 'skipped';
            return $result;
        }

        /* Try to upload the contacts */
        try {
            $xml = $this->getAddressBookXML();

            $postFields = array('PhonebookId' => self::PHONE_BOOK_ID);
            $fileFields = array('PhonebookImportFile' => array(
                'filename' => 'addressbook.xml',
                'type' => 'text/xml',
                'content' => $xml,
            ));
            $this->getApi()->postFile($postFields, $fileFields);

        } catch (\Exception $e) {
            $this->logEntry->setStatus(LogEntry::STATUS_FAILED);
            $this->logger->error($e->getMessage(), array('app' => $this->appName));

            $result['status'] = 'failure';
            $result['msg'] = $e->getMessage();
        }

        $this->logService->insert($this->logEntry);

        return $result;
    }

    /**
     * Initializes the log entry.
     *
     * @param boolean $manually
     */
    protected function initLogEntry($manually)
    {
        $now = new \DateTime();
        $this->logEntry = new LogEntry();
        $this->logEntry->setIsManually($manually);
        $this->logEntry->setDate($now->format('Y-m-d H:i:s'));
        $this->logEntry->setStatus(LogEntry::STATUS_SUCCESS);
    }

    /**
     * Loads the addressbook which should be synced.
     */
    protected function loadAddressBook()
    {
        $this->addressBook = $this->settingsService->getAddressBookInstance();
        \OCP\Contacts::clear();
        \OCP\Contacts::registerAddressBook(
            $this->addressBook->getSearchProvider()
        );
    }

    /**
     * Generates the XML for contact upload.
     *
     * @return string
     */
    protected function getAddressBookXML()
    {
        $xml = new AddressBookXML();

        $contacts = \OCP\Contacts::search('', array('TEL'));
        foreach ($contacts as $contact) {
            foreach ($contact['TEL'] as $number) {
                $isMobile = $this->isMobileNumber($number);

                $type = $isMobile ? 'mobile' : 'home';
                $name = $contact['FN'] . ($isMobile ? ' (mobile)' : '');

                $xml->writeContact($contact['id'], $name, $number, $type);
                $this->logEntry->incSyncedItems();
            }
        }

        return $xml->toString();
    }

    /**
     * Checks, if the given number is a mobile number.
     *
     * @param string $number
     * @return boolean
     */
    protected function isMobileNumber($number)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $numberInstance = $phoneUtil->parse($number, 'DE');
            $type = $phoneUtil->getNumberType($numberInstance);
            return PhoneNumberType::MOBILE === $type;
        } catch (\libphonenumber\NumberParseException $e) {
            $this->logger->error($e->getMessage(), array('app' => $this->appName));
        }

        return false;
    }

    /**
     * Instanciates an FRITZ!Box API.
     *
     * @return \fritzbox_api
     */
    protected function getApi()
    {
        return new FritzboxAPI(
            $this->settingsService->getUrl(),
            $this->settingsService->getPassword(),
            $this->settingsService->getUser()
        );
    }
}
