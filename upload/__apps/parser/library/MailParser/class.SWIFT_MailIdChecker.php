<?php

namespace Parser\Library\MailParser;

use SWIFT_Library;
use SWIFT_CacheStore;
use SWIFT_Log;

class SWIFT_MailIdChecker extends SWIFT_Library
{
    const MAIL_CACHED_ID = 'mail_cached_message_ids';
    const MAX_CACHED = 1000;

    const TABLE_NAME        =    'mailmessageid';
    const PRIMARY_KEY       =    'messageid';
    const DEFAULT_QUERY_NUMERIC_ID = 3;

    /**
     * @var SWIFT_CacheStore
     */
    protected $Cache;

    /**
     * @var array
     */
    protected $message_ids;

    public function __construct($Cache)
    {
        parent::__construct();
        $this->Cache = $Cache;

        $this->message_ids = $this->Cache->Get(self::MAIL_CACHED_ID);
        if (!$this->message_ids) {
            $this->message_ids = array();
        }
    }

    public function checkMessageId($messageid)
    {

        if (in_array($messageid, $this->message_ids)) {
            // Email Message already processed and present in Cache 
            return true;
        }

        $this->message_ids[] = $messageid;
        // Trim Cache size upto the limit defined
        if (count($this->message_ids) > self::MAX_CACHED) {
            $this->message_ids = array_slice($this->message_ids, count($this->message_ids) - self::MAX_CACHED);
        }
        // Add Email message Id in Cache.
        $this->Cache->Update(self::MAIL_CACHED_ID, $this->message_ids);

        return $this->isMessageIdExist($messageid);
    }

    protected function isMessageIdExist($messageid)
    {
        try {
            $sql = "SELECT * FROM " . TABLE_PREFIX . self::TABLE_NAME . " WHERE messageid = " . $this->Database->Param(0) . "";
            $message = $this->Database->QueryFetch($sql, self::DEFAULT_QUERY_NUMERIC_ID, array($messageid));

            if ($message == false) {
                $this->addMessageId($messageid);
                return false;
            }
            return true;
        } catch (\Throwable $ex) {
            $this->Log->Log(
                'Error when check message id exist. Error: ' . $ex->getMessage() . ' Trace: ' . $ex->getTraceAsString(),
                SWIFT_Log::TYPE_ERROR,
                'Parser\Library\MailParser\SWIFT_MailIdChecker'
            );
            return false;
        }
    }

    protected function addMessageId($messageid)
    {
        $sql = "INSERT INTO " . TABLE_PREFIX . self::TABLE_NAME . " (messageid, dateline) values (" . $this->Database->Param(0) . ", " . $this->Database->Param(1) . ")";
        try {
            $this->Database->StartTrans();
            $this->Database->Execute($sql, array(array($messageid, time())));
            $this->Database->CompleteTrans();
        } catch (\Throwable $ex) {
            $this->Database->Rollback();
            throw $ex;
        }
    }
}
