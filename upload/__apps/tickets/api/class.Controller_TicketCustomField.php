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

namespace Tickets\Api;

use Controller_api;
use Base\Models\CustomField\SWIFT_CustomField;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use SWIFT_Exception;
use SWIFT_FileManager;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;
use Base\Library\UserInterface\SWIFT_UserInterface;
use SWIFT_XML;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The TicketCustomField API Controller
 *
 * @property SWIFT_XML $XML
 * @property SWIFT_RESTServer $RESTServer
 * @property SWIFT_CustomFieldManager $CustomFieldManager
 * @author Varun Shoor
 */
class Controller_TicketCustomField extends Controller_api implements SWIFT_REST_Interface
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

        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');
        $this->Language->Load('staff_ticketssearch');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * GetList
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Not Implemented, Call GET /Tickets/TicketCustomField/$ticketid$ instead.');

        return true;
    }

    /**
     * Get a list of custom fields for the given ticket
     *
     * Example Output: http://wiki.kayako.com/display/DEV/REST+-+TicketCustomField
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_ticketID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket not Found');

            return false;
        }

        $_customFieldCache = (array) $this->Cache->Get('customfieldcache');
        $_customFieldIDCache = (array) $this->Cache->Get('customfieldidcache');
        $_customFieldMapCache = (array) $this->Cache->Get('customfieldmapcache');
        $_customFieldOptionCache = (array) $this->Cache->Get('customfieldoptioncache');

        $_customFieldIDList = array();

        $_customFieldGroupTypeList = array(SWIFT_CustomFieldGroup::GROUP_STAFFTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET, SWIFT_CustomFieldGroup::GROUP_USERTICKET);

        /*
         * BUG FIX - Mahesh Salaria
         *
         * SWIFT-2530: API - GET /Tickets/TicketCustomField/$ticketid$ returns all custom field groups
         *
         * Comments: none
         */
        $_rawCustomFieldValueContainer = $_customFieldValueContainer = $_customArguments = $_linkedCustomFieldGroupIDList = array();

        /* Bug Fix : Saloni Dhall
         *
         * SWIFT-3133: SWIFT-2530 breaks getting department custom field groups in API for tickets created via mail parser
         *
         */
        $_departmentID = $_SWIFT_TicketObject->GetProperty('departmentid');
        $_creationMode = $_SWIFT_TicketObject->GetProperty('creationmode');

        if ($_creationMode == SWIFT_Ticket::CREATIONMODE_EMAIL || $_creationMode == SWIFT_Ticket::CREATIONMODE_API || $_creationMode == SWIFT_Ticket::CREATIONMODE_STAFFAPI || $_creationMode == SWIFT_Ticket::CREATIONMODE_MOBILE) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfielddeplinks WHERE departmentid = '" . (int) ($_departmentID) . "'");
            while ($this->Database->NextRecord())
            {
                $_linkedCustomFieldGroupIDList[] = $this->Database->Record['customfieldgroupid'];
            }
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldlinks WHERE grouptype IN (" . BuildIN($_customFieldGroupTypeList) . ") AND linktypeid = '" . ($_ticketID) . "'");
        while ($this->Database->NextRecord())
        {
            if (!in_array($this->Database->Record['customfieldgroupid'], $_linkedCustomFieldGroupIDList)) {
                    $_linkedCustomFieldGroupIDList[] = $this->Database->Record['customfieldgroupid'];
            }
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfields WHERE customfieldgroupid    IN (" . BuildIN($_linkedCustomFieldGroupIDList) . ")");
        while ($this->Database->NextRecord()) {
            $_customFieldIDList[] = $this->Database->Record['customfieldid'];
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldvalues WHERE customfieldid IN (" . BuildIN($_customFieldIDList) . ") AND typeid = '" . ($_ticketID) . "'");
        while ($this->Database->NextRecord()) {
            if (!isset($_customFieldMapCache[$this->Database->Record['customfieldid']])) {
                continue;
            }

            $_rawCustomFieldValueContainer[$this->Database->Record['customfieldid']] = $this->Database->Record;

            // If we already have data set from POST request then we continue as is
            if (isset($_customFieldValueContainer[$this->Database->Record['customfieldid']])) {
                continue;
            }

            $_fieldValue = '';
            if ($this->Database->Record['isencrypted'] == '1') {
                $_fieldValue = SWIFT_CustomFieldManager::Decrypt($this->Database->Record['fieldvalue']);
            } else {
                $_fieldValue = $this->Database->Record['fieldvalue'];
            }

            if ($this->Database->Record['isserialized'] == '1') {
                $_fieldValue = mb_unserialize($_fieldValue);
            }

            $_customField = $_customFieldMapCache[$this->Database->Record['customfieldid']];

            if (_is_array($_fieldValue) && ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_CHECKBOX || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE)) {
                foreach ($_fieldValue as $_key => $_val) {
                    if (isset($_customFieldOptionCache[$_val])) {
                        $_fieldValue[$_key] = $_customFieldOptionCache[$_val];
                    }
                }
            } else if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_RADIO || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECT) {
                if (isset($_customFieldOptionCache[$_fieldValue])) {
                    $_fieldValue = $_customFieldOptionCache[$_fieldValue];
                }
            } else if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTLINKED) {
                $_fieldValueInterim = '';
                if (isset($_fieldValue[0]) && isset($_customFieldOptionCache[$_fieldValue[0]])) {
                    $_fieldValueInterim = $_customFieldOptionCache[$_fieldValue[0]];

                    /**
                     * BUG FIX - Saloni Dhall, Ravi Sharma <ravi.sharma@kayako.com>
                     *
                     * SWIFT-2238: Linked Select fields are returning incorrect sub-values while fetching the custom fields via APIs
                     *
                     * Comments: Added check with Key
                     */
                    if (isset($_fieldValue[1])) {
                        foreach ($_fieldValue[1] as $_key => $_val) {
                            if (isset($_customFieldOptionCache[$_val]) && $_key == $_fieldValue[0]) {
                                $_fieldValueInterim .= ' &gt; ' . $_customFieldOptionCache[$_val];
                                break;
                            }
                        }
                    }
                }

                $_fieldValue = $_fieldValueInterim;
            } else if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_FILE) {
                $_fieldValueInterim = '';

                try {
                    $_SWIFT_FileManagerObject = new SWIFT_FileManager($_fieldValue);

                    $_fieldValueInterim = $_SWIFT_FileManagerObject->GetBase64();
                    $_customArguments[$_customField['customfieldid']]['filename'] = $_SWIFT_FileManagerObject->GetProperty('originalfilename');
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                }

                $_fieldValue = $_fieldValueInterim;
            }

            $_customFieldValueContainer[$this->Database->Record['customfieldid']] = $_fieldValue;
        }

        $this->XML->AddParentTag('customfields');

        if (_is_array($_customFieldCache)) {
            /**
             * @var int $_groupType
             * @var array $_customFieldGroupContainer
             */
            foreach ($_customFieldCache as $_groupType => $_customFieldGroupContainer) {
                if (!in_array($_groupType, $_customFieldGroupTypeList)) {
                    continue;
                }

                foreach ($_customFieldGroupContainer as $_customFieldGroupID => $_customFieldGroup) {
                    if (!in_array($_customFieldGroupID, $_linkedCustomFieldGroupIDList))
                    {
                        continue;
                    }

                    /*
                     * BUG FIX - Amarjeet Kaur
                     *
                     * SWIFT-2421: API - custom fields group displayorder
                     *
                     */

                    $this->XML->AddParentTag('group', array('id' => $_customFieldGroupID, 'title' => $_customFieldGroup['title'], 'displayorder' => $_customFieldGroup['displayorder']));

                    foreach ($_customFieldGroup['_fields'] as $_customFieldID => $_customField) {
                        $_customFieldValue = '';

                        /*
                         * BUG FIX - Varun Shoor
                         *
                         * SWIFT-2023 [Notice]: Undefined offset: 15 (api/class.Controller_TicketCustomField.php:279)
                         *
                         */
                        if (isset($_customFieldValueContainer[$_customFieldID])) {
                            if (_is_array($_customFieldValueContainer[$_customFieldID])) {

                                /*
                                 * BUG FIX - Amarjeet Kaur, Andriy Lesyuk
                                 *
                                 * SWIFT-1449: API - custom fields with multiple values and comma
                                 *
                                 */
                                array_walk($_customFieldValueContainer[$_customFieldID], function (&$_fieldValue) { $_fieldValue = str_replace(',', '\,', $_fieldValue); });

                                $_customFieldValue = implode(', ', $_customFieldValueContainer[$_customFieldID]);
                            } else {
                                $_customFieldValue = $_customFieldValueContainer[$_customFieldID];
                            }
                        }

                        $_fieldArguments = array('id' => $_customFieldID, 'title' => $_customField['title'], 'type' => $_customField['fieldtype'], 'name' => $_customField['fieldname']);

                        if (isset($_customArguments[$_customFieldID])) {
                            $_fieldArguments = array_merge($_fieldArguments, $_customArguments[$_customFieldID]);
                        }

                        $this->XML->AddTag('field', $_customFieldValue, $_fieldArguments);
                    }

                    $this->XML->EndParentTag('group');
                }
            }
        }

        $this->XML->EndParentTag('customfields');

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Create/Update a list of custom fields for the given ticket
     *
     * @author Pavel Titkov
     * @param int $_ticketID The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post($_ticketID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_NOTFOUND, 'Ticket not Found');

            return false;
        }

        $_customFieldCheckResultContainer = $this->CustomFieldManager->Check(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_EDIT, array(
                    SWIFT_CustomFieldGroup::GROUP_STAFFTICKET,
                    SWIFT_CustomFieldGroup::GROUP_USERTICKET,
                    SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET,
                ), SWIFT_CustomFieldManager::CHECKMODE_CLIENT, $_SWIFT_TicketObject->GetProperty('departmentid'));
        if (!$_customFieldCheckResultContainer[0]) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Custom Field Data is Invalid: ' . implode(', ', $_customFieldCheckResultContainer[1]));

            return false;
        }

        // Update Custom Field Values
        $this->CustomFieldManager->Update(
                SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_INSERT, array(
                    SWIFT_CustomFieldGroup::GROUP_STAFFTICKET,
                    SWIFT_CustomFieldGroup::GROUP_USERTICKET,
                    SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET,
                ), SWIFT_CustomFieldManager::CHECKMODE_CLIENT, $_SWIFT_TicketObject->GetTicketID(), $_SWIFT_TicketObject->GetProperty('departmentid'));

        return $this->Get($_ticketID);
    }

}
