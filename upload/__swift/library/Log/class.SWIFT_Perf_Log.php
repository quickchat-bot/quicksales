<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Thanh Dinh
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The SWIFT Performance Log
 *
 * @author Thanh Dinh
 */
class SWIFT_Perf_Log extends SWIFT_Library {
    const TABLE_NAME        =    'perflog';
    const PRIMARY_KEY       =    'ID';
    const MAX_MSG_LEN       = 500;
    const MIN_DURATION      = 5;

    public function __construct()
    {
        parent::__construct();
    }

    public function addLog($functionName, $startTime, $endTime, $message = '')
    {
        $duration = $endTime - $startTime;
        if ($duration < self::MIN_DURATION) {
            return;
        }
        if (strlen($message) > self::MAX_MSG_LEN) {
            $message = substr($message, 0, self::MAX_MSG_LEN);
        }
        $sql = "INSERT INTO " . TABLE_PREFIX . self::TABLE_NAME . " (FUNCTION_NAME, START_TIME, END_TIME, DURATION, MESSAGE) values ("
            . $this->Database->Param(0) . ", "
            . $this->Database->Param(1) . ", "
            . $this->Database->Param(2) . ", "
            . $this->Database->Param(3) . ", "
            . $this->Database->Param(4) . ")";

        try {
            $this->Database->StartTrans();
            $this->Database->Execute($sql, array(array($functionName, $startTime, $endTime, $duration, $message)));
            $this->Database->CompleteTrans();
        } catch (\Throwable $ex) {
            // just rollback and ignore error regard performance log
            $this->Database->Rollback();
        }
    }
}
