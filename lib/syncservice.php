<?php

namespace OCA\ContactsToFb\Lib;

use  \OpenCloud\Common\Log\Logger;

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
        Logger $logger,
        $appName
    ) {
        $this->settingsService = $settingsService;
        $this->logService = $logService;
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

        if (!$manually) {
            /**
             * @todo Only sync, when new contacts are available
             */
            $this->logEntry->setStatus(LogEntry::STATUS_SKIPPED);
        }

        try {
            /* Try to upload the contacts */
            $tmpContactsFile = $this->getContactsXMLPath();
            $this->getApi()->doPostFile(array(
                'PhonebookId' => self::PHONE_BOOK_ID,
                'PhonebookImportFile' => '@' . $tmpContactsFile . ';type=text/xml'
            ));

            // Upload some contacts

            // Another service? => Read all Contacts!

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
     * Generates the XML file for contact upload.
     *
     * @return string
     */
    protected function getContactsXMLPath()
    {
        /**
         * @todo Generate XML dynamically
         * @see http://doc.owncloud.org/server/7.0/developer_manual/app/filesystem.html
         */

        $file = dirname(__FILE__) . '/../../../data/root/files/TelefonbuchTest.xml';

        $writer = new \XMLWriter();
        $writer->openURI($file);

        $writer->startDocument('1.0', 'iso-8859-1');
        $writer->setIndent(4);
        $writer->startElement('phonebooks');
        $writer->startElement('phonebook');

        $contacts = \OCP\Contacts::search('', array('TEL'));

        $this->logEntry->setSynceditems(count($contacts));

        foreach ($contacts as $contact) {
            $writer->startElement('contact');
            $writer->writeElement('category', 0);
            $writer->writeElement('id', $contact['id']);

            $writer->startElement('person');
            $writer->writeElement('realName', $contact['FN']);
            $writer->endElement();

            $writer->startElement('telephony');
            $writer->writeAttribute('nid', 3);
            foreach ($contact['TEL'] as $key => $tel) {
                if ($key > 0) {
                    break;
                }

                $writer->startElement('number');
                $writer->writeAttribute('type', 'home');
                $writer->writeAttribute('id', $key);
                $writer->writeRaw($tel);
                $writer->endElement();
            }

            $writer->endElement();


            //var_export($contact);

            /*
            <contact modified="0">

        <services nid="1">
            <email id="0" />
        </services>
        <telephony nid="3">
            <number type="home" id="0" vanity="" prio="1">02408959432</number>
            <number type="mobile" id="1" prio="0" />
            <number type="work" id="2" prio="0" />
        </telephony>
        <services />
        <setup />
        <mod_time>1361899612</mod_time>
        <uniqueid>18</uniqueid>
    </contact>
             */

            $writer->endElement();
        }

        $writer->endElement();
        $writer->endElement();
        $writer->endDocument();

        $writer->flush();

        return $file;
    }

    /**
     * Instanciates an FRITZ!Box API.
     *
     * @return \fritzbox_api
     */
    protected function getApi()
    {
        return new \fritzbox_api(
            $this->settingsService->getPassword(),
            $this->settingsService->getUrl()
        );
    }
}
