<?php

namespace OCA\ContactsToFb\Lib;

use \OC\Log;
use \OCA\Contacts\Utils\JSONSerializer;
use \libphonenumber\PhoneNumberUtil;
use \libphonenumber\PhoneNumberFormat;

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
        \OC\Files\Node\Folder $appStorage,
        Log $logger,
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

        foreach ($this->addressBook->getChildren() as $contact) {
            $data = JSONSerializer::serializeContact($contact);
            $name = $data['metadata']['displayname'];

            if (!isset($data['data']['TEL'])) {
                continue;
            }

            foreach ($data['data']['TEL'] as $tmpNumber) {
                $number = $this->normalizeNumber($tmpNumber['value']);

                $type = null;
                if (isset($tmpNumber['parameters']['TYPE'][0])) {
                    $type = strtolower($tmpNumber['parameters']['TYPE'][0]);
                }

                $tmpName = $this->buildName($name, $type);

                $xml->writeContact($contact['id'], $tmpName, $number, $type);
                $this->logEntry->incSyncedItems();
            }
        }

        return $xml->toString();
    }

    /**
     * Normalizes a given phone number.
     *
     * @param stringe $number
     * @return string
     */
    protected function normalizeNumber($number)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $numberInstance = $phoneUtil->parse($number, 'DE');
            return $phoneUtil->format($numberInstance, PhoneNumberFormat::INTERNATIONAL);
        } catch (\libphonenumber\NumberParseException $e) {
            $this->logger->error($e->getMessage(), array('app' => $this->appName));
        }

        return $number;
    }

    /**
     * Generates a readable name from the original name and its number type.
     *
     * @param string $name
     * @param string $type
     * @return string
     */
    protected function buildName($name, $type)
    {
        $typeReadable = $type;
        switch ($type) {
            case 'cell':
            case 'iphone':
                $typeReadable = 'm';
                break;

            case 'fax':
                $typeReadable = 'f';
                break;

            case 'work':
                $typeReadable = 'w';
                break;

            case 'home':
            case 'main':
                $typeReadable = '';
                break;
        }

        if ($typeReadable == '') {
            return $name;
        }

        return $name . ' (' . $typeReadable . ')';
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
