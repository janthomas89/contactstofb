<?php

namespace OCA\ContactsToFb\Lib;

/**
 * A representation of the FRITZ!Box Addressbook XML.
 *
 * @author Jan Thomas <jan.thomas@rwth-aachen.de>
 * @copyright Jan Thomas 2014
 * @license https://github.com/janthomas89/contactstofb/blob/master/LICENSE
 */
class AddressBookXML
{
    /**
     * @var \XMLWriter
     */
    protected $writer;

    /**
     * Instantiates the XML addressbook.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initializees the XML writer.
     */
    protected function init()
    {
        $this->writer = new \XMLWriter();
        $this->writer->openMemory();

        $this->writer->startDocument('1.0', 'iso-8859-1');
        $this->writer->setIndent(4);
        $this->writer->startElement('phonebooks');
        $this->writer->startElement('phonebook');
    }

    /**
     * Writes a contact to the XML writer.
     *
     * @param int $id
     * @param string $name
     * @param string $number
     * @param string $type
     */
    public function writeContact($id, $name, $number, $type)
    {
        $w = $this->writer;
        $w->startElement('contact');
            $w->writeElement('category', 0);
            $w->writeElement('id', $id);
            $w->startElement('person');
                $w->writeElement('realName', $name);
            $w->endElement();
            $w->startElement('telephony');
                $w->writeAttribute('nid', 3);
                $w->startElement('number');
                    $w->writeAttribute('type', $type);
                    $w->writeAttribute('id', 0);
                    $w->writeRaw($number);
                $w->endElement();
            $w->endElement();
        $w->endElement();
    }

    /**
     * Outputs the XML as a string.
     *
     * @return string
     */
    public function toString()
    {
        $this->writer->endElement();
        $this->writer->endElement();
        $this->writer->endDocument();

        $output = $this->writer->outputMemory(true);
        $this->init();
        return $output;
    }
}
