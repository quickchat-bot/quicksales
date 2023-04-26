<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Varun Shoor
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Tickets\Staff;

use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Base\Models\User\SWIFT_User;

trait Controller_TicketHistoryTrait
{

    /**
     * Render the History for this Ticket
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function History($_ticketID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

// Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

// Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanviewtickets') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;
        }

        $this->View->RenderHistory($_SWIFT_TicketObject);

        return true;
    }

    /**
     * Render the History for this Ticket
     *
     * @author Varun Shoor
     * @param int $_userID The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function HistoryUser($_userID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));

// Did the object load up?
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->View->RenderHistory($_SWIFT_UserObject);

        return true;
    }

    /**
     * Render the History for this Ticket based on a list of emails
     *
     * @author Varun Shoor
     * @param string $_baseData The Base64 Encoded Data
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function HistoryEmails($_baseData)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalEmailList = [];

        parse_str(base64_decode($_baseData), $_dataContainer);

        if (isset($_dataContainer['email']) && _is_array($_dataContainer['email'])) {
            foreach ($_dataContainer['email'] as $_email) {
                if (IsEmailValid($_email) && !in_array($_email, $_finalEmailList)) {
                    $_finalEmailList[] = $_email;
                }
            }
        }

        $this->View->RenderHistory($_finalEmailList);

        return true;
    }
}
