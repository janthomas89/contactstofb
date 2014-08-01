<?php

namespace OCA\ContactsToFb\Lib;

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
     * Constructor of the SyncService.
     *
     * @param fritzbox_api $api
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Runs the synchronization process.
     *
     * @return array
     */
    public function run()
    {
        $result = array('status' => 'success');


        /**
         * @todo Only sync, when new contacts are available
         */

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
            /**
             * @todo Log error!
             */

            $result['status'] = 'failure';
            $result['msg'] = $e->getMessage();
        }

        return $result;
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
