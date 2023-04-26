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

namespace Tickets\Api;

use Controller_api;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use Base\Models\User\SWIFT_UserGroupAssign;
use SWIFT_XML;

/**
 * The TicketPriority API Controller
 *
 * @property SWIFT_XML $XML
 * @author Varun Shoor
 */
class Controller_TicketPriority extends Controller_api implements SWIFT_REST_Interface
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
     * Retrieve & Dispatch the Ticket Priorities
     *
     * @author Varun Shoor
     * @param bool|int $_ticketPriorityID (OPTIONAL) The Ticket Priority ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessTicketPriorities($_ticketPriorityID = false) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketPriorityContainer = $_ticketPriorityIDList = array();

        if (!empty($_ticketPriorityID)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities WHERE priorityid = '" . (int) ($_ticketPriorityID) . "'");
        } else {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities ORDER BY priorityid ASC");
        }

        while ($this->Database->NextRecord()) {
            $_ticketPriorityContainer[$this->Database->Record['priorityid']] = $this->Database->Record;

            $_ticketPriorityIDList[] = $this->Database->Record['priorityid'];
        }

        $_userGroupAssignMap = SWIFT_UserGroupAssign::RetrieveMap(SWIFT_UserGroupAssign::TYPE_TICKETPRIORITY, $_ticketPriorityIDList);

        $this->XML->AddParentTag('ticketpriorities');
            foreach ($_ticketPriorityContainer as $_tID => $_ticketPriority) {
                $this->XML->AddParentTag('ticketpriority');
                    $this->XML->AddTag('id', $_tID);
                    $this->XML->AddTag('title', $_ticketPriority['title']);
                    $this->XML->AddTag('displayorder', $_ticketPriority['displayorder']);
                    $this->XML->AddTag('frcolorcode', $_ticketPriority['frcolorcode']);
                    $this->XML->AddTag('bgcolorcode', $_ticketPriority['bgcolorcode']);
                    $this->XML->AddTag('type', $_ticketPriority['type']);
                    $this->XML->AddTag('uservisibilitycustom', $_ticketPriority['uservisibilitycustom']);

                    if (isset($_userGroupAssignMap[$_tID]) && _is_array($_userGroupAssignMap[$_tID])) {
                        foreach ($_userGroupAssignMap[$_tID] as $_userGroupID) {
                            $this->XML->AddTag('usergroupid', $_userGroupID);
                        }
                    }
                $this->XML->EndParentTag('ticketpriority');
            }
        $this->XML->EndParentTag('ticketpriorities');

        return true;
    }

    /**
     * Get a list of Ticket Priorities
     *
     * Example Output:
     *
     * <ticketpriorities>
     *    <ticketpriority>
     *        <id>6</id>
     *        <title>Critical</title>
     *        <displayorder>6</displayorder>
     *        <frcolorcode>#ffffff</frcolorcode>
     *        <bgcolorcode>#d6000e</bgcolorcode>
     *        <type>public</type>
     *        <uservisibilitycustom>1</uservisibilitycustom>
     *        <usergroupid>2</usergroupid>
     *    </ticketpriority>
     * </ticketpriorities>
     *
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

        $this->ProcessTicketPriorities(false);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve the Ticket Priority
     *
     * Example Output:
     *
     * <ticketpriorities>
     *    <ticketpriority>
     *        <id>6</id>
     *        <title>Critical</title>
     *        <displayorder>6</displayorder>
     *        <frcolorcode>#ffffff</frcolorcode>
     *        <bgcolorcode>#d6000e</bgcolorcode>
     *        <type>public</type>
     *        <uservisibilitycustom>1</uservisibilitycustom>
     *        <usergroupid>2</usergroupid>
     *    </ticketpriority>
     * </ticketpriorities>
     *
     * @author Varun Shoor
     * @param int $_ticketPriorityID The Ticket Priority ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_ticketPriorityID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessTicketPriorities(($_ticketPriorityID));

        $this->XML->EchoXML();

        return true;
    }
}
