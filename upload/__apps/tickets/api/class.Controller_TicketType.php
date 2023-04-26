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
use Base\Models\User\SWIFT_UserGroupAssign;
use SWIFT_XML;

/**
 * The TicketType API Controller
 *
 * @property SWIFT_XML $XML
 * @author Varun Shoor
 */
class Controller_TicketType extends Controller_api implements SWIFT_REST_Interface
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
     * Retrieve & Dispatch the Ticket Types
     *
     * @author Varun Shoor
     * @param bool|int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessTicketTypes($_ticketTypeID = false) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketTypeContainer = $_ticketTypeIDList = array();

        if (!empty($_ticketTypeID)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickettypes WHERE tickettypeid = '" . (int) ($_ticketTypeID) . "'");
        } else {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickettypes ORDER BY tickettypeid ASC");
        }

        while ($this->Database->NextRecord()) {
            $_ticketTypeContainer[$this->Database->Record['tickettypeid']] = $this->Database->Record;

            $_ticketTypeIDList[] = $this->Database->Record['tickettypeid'];
        }

        $_userGroupAssignMap = SWIFT_UserGroupAssign::RetrieveMap(SWIFT_UserGroupAssign::TYPE_TICKETTYPE, $_ticketTypeIDList);

        $this->XML->AddParentTag('tickettypes');
            foreach ($_ticketTypeContainer as $_tID => $_ticketType) {
                $this->XML->AddParentTag('tickettype');
                    $this->XML->AddTag('id', $_tID);
                    $this->XML->AddTag('title', $_ticketType['title']);
                    $this->XML->AddTag('displayorder', $_ticketType['displayorder']);
                    $this->XML->AddTag('departmentid', $_ticketType['departmentid']);
                    $this->XML->AddTag('displayicon', $_ticketType['displayicon']);
                    $this->XML->AddTag('type', $_ticketType['type']);
                    $this->XML->AddTag('uservisibilitycustom', $_ticketType['uservisibilitycustom']);

                    if (isset($_userGroupAssignMap[$_tID]) && _is_array($_userGroupAssignMap[$_tID])) {
                        foreach ($_userGroupAssignMap[$_tID] as $_userGroupID) {
                            $this->XML->AddTag('usergroupid', $_userGroupID);
                        }
                    }
                $this->XML->EndParentTag('tickettype');
            }
        $this->XML->EndParentTag('tickettypes');

        return true;
    }

    /**
     * Get a list of Ticket Types
     *
     * Example Output:
     *
     * <tickettypes>
     *    <tickettype>
     *        <id>5</id>
     *        <title>Feedback</title>
     *        <displayorder>5</displayorder>
     *        <departmentid>0</departmentid>
     *        <displayicon>{$themepath}icon_lightbulb.png</displayicon>
     *        <type>public</type>
     *        <uservisibilitycustom>1</uservisibilitycustom>
     *        <usergroupid>2</usergroupid>
     *    </tickettype>
     * </tickettypes>
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

        $this->ProcessTicketTypes(false);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve the Ticket Type
     *
     * Example Output:
     *
     * <tickettypes>
     *    <tickettype>
     *        <id>5</id>
     *        <title>Feedback</title>
     *        <displayorder>5</displayorder>
     *        <departmentid>0</departmentid>
     *        <displayicon>{$themepath}icon_lightbulb.png</displayicon>
     *        <type>public</type>
     *        <uservisibilitycustom>1</uservisibilitycustom>
     *        <usergroupid>2</usergroupid>
     *    </tickettype>
     * </tickettypes>
     *
     * @author Varun Shoor
     * @param int $_ticketTypeID The Ticket Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_ticketTypeID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessTicketTypes( ($_ticketTypeID));

        $this->XML->EchoXML();

        return true;
    }
}
