<?php

namespace OCA\ContactsToFb\Lib;

use \OCP\IDb;
use \OCP\AppFramework\Db\Mapper;

/**
 * A Service for logging sync actions and retrieving sync
 * actions done in the past.
 *
 * @author Jan Thomas <jan.thomas@rwth-aachen.de>
 * @copyright Jan Thomas 2014
 * @license https://github.com/janthomas89/contactstofb/blob/master/LICENSE
 */
class LogService extends Mapper
{
    /**
     * Limit Log entries.
     */
    const LIMIT = 128;

    public function __construct(IDb $db)
    {
        parent::__construct(
            $db,
            'contactstofb_log',
            '\OCA\ContactsToFb\Lib\LogEntry'
        );
    }

    /**
     * Queries a single og entry.
     *
     * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
     * @param int $id
     * @return Log
     */
    public function find($id)
    {
        $sql = 'SELECT * FROM `*PREFIX*contactstofb_log` WHERE id = ?';
        return $this->findEntity($sql, array((int)$id));
    }

    /**
     * Returns a page of log entries.
     *
     * @param int $page
     * @return array
     */
    public function getPage($page = 1)
    {
        $offset = $page > 1 ? (self::LIMIT * ($page-1)) : 0;
        $sql = 'SELECT * FROM `*PREFIX*contactstofb_log` ORDER BY id DESC';
        return $this->findEntities($sql, array(), self::LIMIT, (int)$offset);
    }
}
