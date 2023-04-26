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

/**
 * Generate Ticket Status'es
 *
 * @author Varun Shoor
 * @return string _optionHTML: The Generated Options
 */
function GenerateClientTicketStatus()
{
    $_SWIFT = SWIFT::GetInstance();

    $_optionHTML = '<option value="0"' . IIF($_SWIFT->Settings->Get('t_cstatusupd') == 0, ' selected') . '>' . htmlspecialchars($_SWIFT->Language->Get('tsnochange')) . '</option>' . SWIFT_CRLF;

    $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketstatus WHERE departmentid = 0 ORDER BY displayorder ASC", 3);
    while ($_SWIFT->Database->nextRecord(3)) {
        $_optionHTML .= '<option value="' . $_SWIFT->Database->Record3['ticketstatusid'] . '"' . IIF($_SWIFT->Settings->Get('t_cstatusupd') == $_SWIFT->Database->Record3['ticketstatusid'], ' selected') . '>' . htmlspecialchars($_SWIFT->Database->Record3["title"]) . '</option>' . SWIFT_CRLF;
    }

    return $_optionHTML;
}