<?php

/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser\Console;
use Controller_console;
use SWIFT_Exception;

/**
 * The Controller for getting email queue info
 *
 * @author Varun Shoor
 */
class Controller_FetchDaemon extends Controller_console
{
    /**
     * Return all pop/imap email queues as json
     *
     * @author Ravinder Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_queueCache = $this->Cache->Get('queuecache');
        $_queueData = array();


        foreach ($_queueCache['pointer'] as $_pointer) {
            if (($_queueCache['list'][$_pointer]['fetchtype'] == 'imap' || $_queueCache['list'][$_pointer]['fetchtype'] == 'imapssl') && $_queueCache['list'][$_pointer]['isenabled'] == 1) {
                $_queueData[] = array(
                    'email' => $_queueCache['list'][$_pointer]['email'],
                    'host' => $_queueCache['list'][$_pointer]['host'],
                    'port' => $_queueCache['list'][$_pointer]['port'],
                    'username' => $_queueCache['list'][$_pointer]['username'],
                    'userpassword' => $_queueCache['list'][$_pointer]['userpassword'],
                    'fetchtype' => $_queueCache['list'][$_pointer]['fetchtype'],
                );
            }
        }

        echo json_encode($_queueData);

        return true;
    }
}
