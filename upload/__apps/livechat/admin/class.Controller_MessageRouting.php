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

namespace LiveChat\Admin;

use Controller_admin;
use SWIFT;
use LiveChat\Models\Message\SWIFT_MessageRouting;
use SWIFT_Session;

/**
 * The Live Chat Message Routing Controller Class
 *
 * @author Varun Shoor
 *
 * @property View_MessageRouting $View
 */
class Controller_MessageRouting extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 13;

    /** @var SWIFT_MessageRouting */
    public $MessageRouting;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Model('Message:MessageRouting', [], true, false, 'livechat'); // Load the Library

        $this->Language->Load('admin_livesupport');
    }

    /**
     * The Main Display Handler
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Index()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('livesupport') . ' > ' . $this->Language->Get('messagerouting'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_lrcanviewrouting') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render($this->_GetMessageRoutingData());
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Retrieve the Message Routing Data
     *
     * @author Varun Shoor
     * @return mixed "_messageRoutingData" (ARRAY) on Success, "false" otherwise
     */
    private function _GetMessageRoutingData()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_messageRoutingData = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "messagerouting ORDER BY messageroutingid ASC");
        while ($this->Database->NextRecord()) {
            $_messageRoutingData[$this->Database->Record['departmentid']] = $this->Database->Record;
        }

        return $_messageRoutingData;
    }

    /**
     * Prepares the Message Routing Table based on $_POST Data
     *
     * @author Varun Shoor
     * @return array "_routingTable" (ARRAY) on Success, "false" otherwise
     */
    private function _PrepeareMessageRoutingTable()
    {
        if (!$this->GetIsClassLoaded()) {
            return [];
        }

        $_routingTable = array();

        if (isset($_POST['preservemessage']) && _is_array($_POST['preservemessage'])) {
            foreach ($_POST['preservemessage'] as $_key => $_val) {
                if (is_numeric($_key)) {
                    $_routingTable[$_key][SWIFT_MessageRouting::PRESERVE_MESSAGE] = 1;
                }
            }
        }

        if (isset($_POST['routetotickets']) && _is_array($_POST['routetotickets'])) {
            foreach ($_POST['routetotickets'] as $_key => $_val) {
                $_departmentID = $_POST['routedepartmentid'][$_key];
                if (is_numeric($_departmentID) && is_numeric($_departmentID)) {
                    $_routingTable[$_key][SWIFT_MessageRouting::ROUTE_TICKETS] = 1;
                    $_routingTable[$_key][SWIFT_MessageRouting::ROUTE_TICKETDEPARTMENT] = (int)($_departmentID);
                }
            }
        }

        if (isset($_POST['routetoemail']) && _is_array($_POST['routetoemail']) && isset($_POST['emailroute']) && !empty($_POST['emailroute'])) {
            foreach ($_POST['routetoemail'] as $_key => $_val) {
                if (is_numeric($_key) && !empty($_val) && isset($_POST['emailroute'][$_key]) && !empty($_POST['emailroute'][$_key])) {
                    $_routingTable[$_key][SWIFT_MessageRouting::ROUTE_EMAIL] = 1;
                    $_routingTable[$_key][SWIFT_MessageRouting::ROUTE_FORWARDEMAILS] = $_POST['emailroute'][$_key];
                }
            }
        }

        return $_routingTable;
    }

    /**
     * The Message Routing Post Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function IndexSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            $this->Load->Index();

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_lrcanupdaterouting') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Index();

            return false;
        }

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');

        $_routingTable = $this->_PrepeareMessageRoutingTable();
        $this->MessageRouting->UpdateMessageRoutingTable($_routingTable);

        $_index = 1;
        $_infoText = '';
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "messagerouting ORDER BY messageroutingid ASC");
        while ($this->Database->NextRecord()) {
            $_departmentTitle = $this->Language->Get('na');
            if (isset($_departmentCache[$this->Database->Record['departmentid']])) {
                $_departmentTitle = $_departmentCache[$this->Database->Record['departmentid']]['title'];
            }

            $_infoText .= $_index . '. ' . text_to_html_entities($_departmentTitle) . '<br>';
            $_infoText .= '&nbsp;&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" border="0" align="absmiddle" /> ' . $this->Language->Get('routetotickets') . ': ' . IIF($this->Database->Record['routetotickets'] == 1, $this->Language->Get('yes'), $this->Language->Get('no')) . IIF($this->Database->Record['routetotickets'] == 1, ' (' . $this->Language->Get('mrdepartment') . ': ' . text_to_html_entities($_departmentTitle) . ')') . '<br>';
            $_infoText .= '&nbsp;&nbsp;&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/linkdownarrow_blue.gif" border="0" align="absmiddle" /> ' . $this->Language->Get('routetoemail') . ': ' . IIF($this->Database->Record['routetoemail'] == 1, $this->Language->Get('yes'), $this->Language->Get('no')) . IIF($this->Database->Record['routetoemail'] == 1, ' (' . $this->Language->Get('mremail') . ': ' . htmlspecialchars($this->Database->Record['forwardemails']) . ')') . '<br>';

            $_index++;
        }

        SWIFT::Info($this->Language->Get('titleupdmr'), $this->Language->Get('msgupdmr') . '<br>' . $_infoText);

        $this->Load->Index();

        return true;
    }
}
