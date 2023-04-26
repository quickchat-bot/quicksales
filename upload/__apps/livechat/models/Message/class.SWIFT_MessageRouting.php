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
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace LiveChat\Models\Message;

use SWIFT_Model;

/**
 * The Message Routing Data Management Model Class
 *
 * @author Varun Shoor
 */
class SWIFT_MessageRouting extends SWIFT_Model
{
    const TABLE_NAME = 'messagerouting';
    const PRIMARY_KEY = 'messageroutingid';

    const TABLE_STRUCTURE = "messageroutingid I PRIMARY AUTO NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                preservemessage I2 DEFAULT '0' NOTNULL,
                                routetotickets I2 DEFAULT '0' NOTNULL,
                                routetoemail I2 DEFAULT '0' NOTNULL,
                                ticketdepartmentid I DEFAULT '0' NOTNULL,
                                forwardemails X2";

    const INDEX_1 = 'departmentid';


    // Core Constants
    const PRESERVE_MESSAGE = 1;
    const ROUTE_TICKETS = 2;
    const ROUTE_EMAIL = 3;
    const ROUTE_TICKETDEPARTMENT = 4;
    const ROUTE_FORWARDEMAILS = 5;

    /**
     * Empties the Message Routing Table
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function DeleteMessageRoutingTable()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "messagerouting");

        return true;
    }

    /**
     * Update the Message Routing Table
     *
     * @author Varun Shoor
     * @param array $_routingTable The Message Routing Table
     * @return bool "true" on Success, "false" otherwise
     */
    public function UpdateMessageRoutingTable($_routingTable = array())
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (!$this->DeleteMessageRoutingTable()) {
            return false;
        }

        if (!_is_array($_routingTable)) {
            return false;
        }

        $_departments = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments WHERE departmentapp = '" . APP_LIVECHAT . "' ORDER BY title ASC");
        while ($this->Database->NextRecord()) {
            $_departments[$this->Database->Record['departmentid']] = $this->Database->Record;
        }

        foreach ($_departments as $_key => $_val) {
            $_fields = array();

            if (!isset($_routingTable[$_key])) {
                continue;
            }

            // Preserve Message Record?
            if (isset($_routingTable[$_key][self::PRESERVE_MESSAGE]) && $_routingTable[$_key][self::PRESERVE_MESSAGE] == 1) {
                $_fields['preservemessage'] = 1;
            } else {
                $_fields['preservemessage'] = 0;
            }

            // Routing to Tickets Enabled?
            if (isset($_routingTable[$_key][self::ROUTE_TICKETS]) && $_routingTable[$_key][self::ROUTE_TICKETS] == 1 && isset($_routingTable[$_key][self::ROUTE_TICKETDEPARTMENT]) && !empty($_routingTable[$_key][self::ROUTE_TICKETDEPARTMENT])) {
                $_ticketDepartmentID = (int)($_routingTable[$_key][self::ROUTE_TICKETDEPARTMENT]);

                $_fields['routetotickets'] = 1;
                $_fields['ticketdepartmentid'] = $_ticketDepartmentID;
            } else {
                $_fields['routetotickets'] = 0;
                $_fields['ticketdepartmentid'] = 0;
            }

            // Routing to email enabled?
            if (isset($_routingTable[$_key][self::ROUTE_EMAIL]) && $_routingTable[$_key][self::ROUTE_EMAIL] == 1 && isset($_routingTable[$_key][self::ROUTE_FORWARDEMAILS]) && !empty($_routingTable[$_key][self::ROUTE_FORWARDEMAILS])) {
                $_forwardEmailList = $_routingTable[$_key][self::ROUTE_FORWARDEMAILS];
                if (!empty($_forwardEmailList)) {
                    $_fields['routetoemail'] = 1;
                    $_fields['forwardemails'] = $_forwardEmailList;
                } else {
                    $_fields['routetoemail'] = 0;
                    $_fields['forwardemails'] = '';
                }
            } else {
                $_fields['routetoemail'] = 0;
                $_fields['forwardemails'] = '';
            }

            $_fields['departmentid'] = $_key;

            $this->Database->AutoExecute(TABLE_PREFIX . 'messagerouting', $_fields, 'INSERT');
        }

        return true;
    }
}
