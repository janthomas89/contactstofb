<?php

namespace OCA\ContactsToFb\Lib;

use \OCP\AppFramework\Db\Entity;

/**
 * A Entity class for log entries.
 *
 * @author Jan Thomas <jan.thomas@rwth-aachen.de>
 * @copyright Jan Thomas 2014
 * @license https://github.com/janthomas89/contactstofb/blob/master/LICENSE
 */
class LogEntry extends Entity
{
    protected $type;
    protected $date;
    protected $synceditems;
    protected $status;

    public function __construct()
    {
        $this->addType('synceditems', 'integer');
    }
}
