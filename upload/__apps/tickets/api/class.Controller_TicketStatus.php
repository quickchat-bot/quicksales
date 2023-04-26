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
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Tickets\Api;

use Controller_api;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use SWIFT_XML;

/**
 * The TicketStatus API Controller
 *
 * @property SWIFT_XML $XML
 * @author Varun Shoor
 */
class Controller_TicketStatus extends Controller_api implements SWIFT_REST_Interface
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');
    }

    /**
     * Retrieve & Dispatch the Ticket Status
     *
     * @author Varun Shoor
     * @param bool|int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessTicketStatuses($_ticketStatusID = false) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketStatusContainer = $_ticketStatusIDList = array();

        if (!empty($_ticketStatusID)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketstatus WHERE ticketstatusid = '" . (int) ($_ticketStatusID) . "'");
        } else {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketstatus ORDER BY ticketstatusid ASC");
        }

        while ($this->Database->NextRecord()) {
            $_ticketStatusContainer[$this->Database->Record['ticketstatusid']] = $this->Database->Record;

            $_ticketStatusIDList[] = $this->Database->Record['ticketstatusid'];
        }

        $_staffGroupLinkMap = SWIFT_StaffGroupLink::RetrieveMap(SWIFT_StaffGroupLink::TYPE_TICKETSTATUS, $_ticketStatusIDList);

        $this->XML->AddParentTag('ticketstatuses');
            foreach ($_ticketStatusContainer as $_tID => $_ticketStatus) {
                $this->XML->AddParentTag('ticketstatus');
                    $this->XML->AddTag('id', $_tID);
                    $this->XML->AddTag('title', $_ticketStatus['title']);
                    $this->XML->AddTag('displayorder', $_ticketStatus['displayorder']);
                    $this->XML->AddTag('departmentid', $_ticketStatus['departmentid']);
                    $this->XML->AddTag('displayicon', $_ticketStatus['displayicon']);
                    $this->XML->AddTag('type', $_ticketStatus['statustype']);
                    $this->XML->AddTag('displayinmainlist', $_ticketStatus['displayinmainlist']);
                    $this->XML->AddTag('markasresolved', $_ticketStatus['markasresolved']);
                    $this->XML->AddTag('displaycount', $_ticketStatus['displaycount']);
                    $this->XML->AddTag('statuscolor', $_ticketStatus['statuscolor']);
                    $this->XML->AddTag('statusbgcolor', $_ticketStatus['statusbgcolor']);
                    $this->XML->AddTag('resetduetime', $_ticketStatus['resetduetime']);
                    $this->XML->AddTag('triggersurvey', $_ticketStatus['triggersurvey']);
                    $this->XML->AddTag('staffvisibilitycustom', $_ticketStatus['staffvisibilitycustom']);

                    if (isset($_staffGroupLinkMap[$_tID]) && _is_array($_staffGroupLinkMap[$_tID])) {
                        foreach ($_staffGroupLinkMap[$_tID] as $_staffGroupID) {
                            $this->XML->AddTag('staffgroupid', $_staffGroupID);
                        }
                    }
                $this->XML->EndParentTag('ticketstatus');
            }
        $this->XML->EndParentTag('ticketstatuses');

        return true;
    }

    /**
     * Get a list of Ticket Status'es
     *
     * Example Output:
     *
     * <ticketstatuses>
     *    <ticketstatus>
     *        <id>3</id>
     *        <title>Closed</title>
     *        <displayorder>3</displayorder>
     *        <departmentid>0</departmentid>
     *        <displayicon>{$themepath}icon_ticketstatusclosed.png</displayicon>
     *        <type>private</type>
     *        <displayinmainlist>0</displayinmainlist>
     *        <markasresolved>1</markasresolved>
     *        <displaycount>0</displaycount>
     *        <statuscolor>#5f5f5f</statuscolor>
     *        <statusbgcolor>#5f5f5f</statusbgcolor>
     *        <resetduetime>0</resetduetime>
     *        <triggersurvey>1</triggersurvey>
     *        <staffvisibilitycustom>1</staffvisibilitycustom>
     *        <staffgroupid>1</staffgroupid>
     *    </ticketstatus>
     * </ticketstatuses>
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessTicketStatuses(false);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve the Ticket Status
     *
     * Example Output:
     *
     * <ticketstatuses>
     *    <ticketstatus>
     *        <id>3</id>
     *        <title>Closed</title>
     *        <displayorder>3</displayorder>
     *        <departmentid>0</departmentid>
     *        <displayicon>{$themepath}icon_ticketstatusclosed.png</displayicon>
     *        <type>private</type>
     *        <displayinmainlist>0</displayinmainlist>
     *        <markasresolved>1</markasresolved>
     *        <displaycount>0</displaycount>
     *        <statuscolor>#5f5f5f</statuscolor>
     *        <statusbgcolor>#5f5f5f</statusbgcolor>
     *        <resetduetime>0</resetduetime>
     *        <triggersurvey>1</triggersurvey>
     *        <staffvisibilitycustom>1</staffvisibilitycustom>
     *        <staffgroupid>1</staffgroupid>
     *    </ticketstatus>
     * </ticketstatuses>
     *
     * @author Varun Shoor
     * @param int $_ticketStatusID The Ticket Status ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_ticketStatusID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessTicketStatuses(($_ticketStatusID));

        $this->XML->EchoXML();

        return true;
    }
}
