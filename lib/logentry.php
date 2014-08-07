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
    const TYPE_MANUALLY = 'manually';
    const TYPE_CRON = 'cron';

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    /**
     * Type of the synchronization (manually, cron)
     *
     * @var string
     */
    protected $type;

    /**
     * Date of the synchronization.
     *
     * @var stribng
     */
    protected $date;

    /**
     * Number of of the synced items.
     *
     * @var int
     */
    protected $synceditems;

    /**
     * Status of the synchronization (success, failed).
     *
     * @var string
     */
    protected $status;

    /**
     * Constructor of LogEntry.
     */
    public function __construct()
    {
        $this->addType('synceditems', 'integer');
    }

    /**
     * Sets wether or not the run is manually triggered.
     *
     * @param boolean $manually
     */
    public function setIsManually($manually)
    {
        $this->setType($manually ? self::TYPE_MANUALLY : self::TYPE_CRON);
    }
}
