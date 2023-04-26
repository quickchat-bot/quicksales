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

namespace Base\Library\CustomField;

use Base\Models\CustomField\SWIFT_CustomField;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\CustomField\SWIFT_CustomFieldGroupPermission;
use Base\Models\CustomField\SWIFT_CustomFieldLink;
use Base\Models\CustomField\SWIFT_CustomFieldValue;
use Base\Models\CustomField\SWIFT_CustomFieldWorkflowValue;
use Base\Models\Staff\SWIFT_Staff;
use SWIFT;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_FileManager;
use SWIFT_Interface;
use SWIFT_Library;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Workflow\SWIFT_TicketWorkflow;

/**
 * The Custom Field Manager Class
 *
 * @author Varun Shoor
 */
class SWIFT_CustomFieldManager extends SWIFT_Library
{
    // Core Constants
    const MODE_POST = 1;
    const MODE_GET = 2;
    const MODE_REQUEST = 3;

    const CHECKMODE_STAFF = 1;
    const CHECKMODE_CLIENT = 2;
    const CHECKMODE_WORKFLOW = 3;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Rebuild the Custom Field Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();


        $_cacheContainer = $_customFieldGroupMap = $_customFieldGroupIDList = $_ticketCustomFieldIDList = $_ticketCustomFieldGroupIDList = $_userCustomFieldGroupIDList = $_userOrganizationCustomFieldGroupIDList = $_userCustomFieldIDList = $_userOrganizationCustomFieldIDList = array();

        $_ticketCustomFieldTypeList = array(SWIFT_CustomFieldGroup::GROUP_STAFFTICKET, SWIFT_CustomFieldGroup::GROUP_USERTICKET,
            SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET);
        $_userCustomFieldTypeList = array(SWIFT_CustomFieldGroup::GROUP_USER);
        $_userOrganizationCustomFieldTypeList = array(SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION);

        // First fetch the groups
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldgroups ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord()) {
            if (in_array($_SWIFT->Database->Record['grouptype'], $_ticketCustomFieldTypeList)) {
                $_ticketCustomFieldGroupIDList[] = $_SWIFT->Database->Record['customfieldgroupid'];
            } elseif (in_array($_SWIFT->Database->Record['grouptype'], $_userCustomFieldTypeList)) {
                $_userCustomFieldGroupIDList[] = $_SWIFT->Database->Record['customfieldgroupid'];
            } elseif (in_array($_SWIFT->Database->Record['grouptype'], $_userOrganizationCustomFieldTypeList)) {
                $_userOrganizationCustomFieldGroupIDList[] = $_SWIFT->Database->Record['customfieldgroupid'];
            }

            $_cacheContainer[$_SWIFT->Database->Record['grouptype']][$_SWIFT->Database->Record['customfieldgroupid']] = $_SWIFT->Database->Record;

            $_customFieldGroupIDList[] = $_SWIFT->Database->Record['customfieldgroupid'];

            $_cacheContainer[$_SWIFT->Database->Record['grouptype']][$_SWIFT->Database->Record['customfieldgroupid']]['_permissions'] = array();
            $_cacheContainer[$_SWIFT->Database->Record['grouptype']][$_SWIFT->Database->Record['customfieldgroupid']]['_fields'] = array();
            $_cacheContainer[$_SWIFT->Database->Record['grouptype']][$_SWIFT->Database->Record['customfieldgroupid']]['_departments'] = array();

            $_customFieldGroupMap[$_SWIFT->Database->Record['customfieldgroupid']] = &$_cacheContainer[$_SWIFT->Database->Record['grouptype']][$_SWIFT->Database->Record['customfieldgroupid']];
        }

        // Parse out the permissions
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldgrouppermissions WHERE customfieldgroupid IN (" . BuildIN($_customFieldGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            // Only save the permissions set to NO
            if (in_array($_SWIFT->Database->Record['accessmask'], [SWIFT_CustomFieldGroupPermission::ACCESS_YES, SWIFT_CustomFieldGroupPermission::ACCESS_NO])) {
                $_customFieldGroup = &$_customFieldGroupMap[$_SWIFT->Database->Record['customfieldgroupid']];
                $_customFieldGroup['_permissions'][$_SWIFT->Database->Record['cfgrouptype']][$_SWIFT->Database->Record['typeid']] = $_SWIFT->Database->Record['accessmask'];
            }
        }

        // Parse out the department links
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfielddeplinks WHERE customfieldgroupid IN (" . BuildIN($_customFieldGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_customFieldGroup = &$_customFieldGroupMap[$_SWIFT->Database->Record['customfieldgroupid']];
            $_customFieldGroup['_departments'][] = $_SWIFT->Database->Record['departmentid'];
        }

        // Time to work on the custom fields
        $_customFieldIDList = $_customFieldMap = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfields WHERE customfieldgroupid IN (" . BuildIN($_customFieldGroupIDList) . ") ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_customFieldGroup = &$_customFieldGroupMap[$_SWIFT->Database->Record['customfieldgroupid']];

            if (in_array($_customFieldGroup['customfieldgroupid'], $_ticketCustomFieldGroupIDList)) {
                $_ticketCustomFieldIDList[] = $_SWIFT->Database->Record['customfieldid'];
            } elseif (in_array($_customFieldGroup['customfieldgroupid'], $_userCustomFieldGroupIDList)) {
                $_userCustomFieldIDList[] = $_SWIFT->Database->Record['customfieldid'];
            } elseif (in_array($_customFieldGroup['customfieldgroupid'], $_userOrganizationCustomFieldGroupIDList)) {
                $_userOrganizationCustomFieldIDList[] = $_SWIFT->Database->Record['customfieldid'];
            }

            $_customFieldGroup['_fields'][$_SWIFT->Database->Record['customfieldid']] = $_SWIFT->Database->Record;
            $_customFieldGroup['_fields'][$_SWIFT->Database->Record['customfieldid']]['_options'] = array();

            $_customFieldMap[$_SWIFT->Database->Record['customfieldid']] = &$_customFieldGroup['_fields'][$_SWIFT->Database->Record['customfieldid']];

            $_customFieldIDList[] = $_SWIFT->Database->Record['customfieldid'];
        }

        // Fetch all the custom field options
        $_customFieldOptionIDList = $_customFieldOptionMap = $_customFieldOptionCache = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldoptions WHERE customfieldid IN (" . BuildIN($_customFieldIDList) . ") ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_customField = &$_customFieldMap[$_SWIFT->Database->Record['customfieldid']];

            $_customField['_options'][$_SWIFT->Database->Record['customfieldoptionid']] = $_SWIFT->Database->Record;
            $_customField['_options'][$_SWIFT->Database->Record['customfieldoptionid']]['_links'] = array();

            $_customFieldOptionIDList[] = $_SWIFT->Database->Record['customfieldoptionid'];

            $_customFieldOptionMap[$_SWIFT->Database->Record['customfieldoptionid']] = &$_customField['_options'][$_SWIFT->Database->Record['customfieldoptionid']];

            $_customFieldOptionCache[$_SWIFT->Database->Record['customfieldoptionid']] = $_SWIFT->Database->Record['optionvalue'];
        }

        // Fetch custom field option links
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldoptionlinks WHERE customfieldoptionid IN (" . BuildIN($_customFieldOptionIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_customFieldOption = &$_customFieldOptionMap[$_SWIFT->Database->Record['customfieldoptionid']];

            $_customFieldOption['_links'][] = $_SWIFT->Database->Record['customfieldid'];
        }

        $_customFieldIDCache = array();
        $_customFieldIDCache['ticketcustomfieldidlist'] = $_ticketCustomFieldIDList;
        $_customFieldIDCache['usercustomfieldidlist'] = $_userCustomFieldIDList;
        $_customFieldIDCache['userorganizationcustomfieldidlist'] = $_userOrganizationCustomFieldIDList;

        $_SWIFT->Cache->Update('customfieldcache', $_cacheContainer);

        $_SWIFT->Cache->Update('customfieldmapcache', $_customFieldMap);

        $_SWIFT->Cache->Update('customfieldidcache', $_customFieldIDCache);

        $_SWIFT->Cache->Update('customfieldoptioncache', $_customFieldOptionCache);

        return true;
    }

    /**
     * Check the values in the POST/GET/REQUEST variable
     *
     * @author Varun Shoor
     * @param mixed $_submissionMode The Submission Mode
     * @param mixed $_mode The UI Mode
     * @param array $_groupTypeList The Group Type List
     * @param mixed $_checkMode The Check Mode
     * @param int $_departmentID (OPTIONAL) The Department ID to filter on
     * @param int $_linkTypeID
     * @return array array(bool, fieldnamelist)
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Check($_submissionMode, $_mode, $_groupTypeList, $_checkMode, $_departmentID = 0, $_linkTypeID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_baseContainer = array();
        if ($_submissionMode == self::MODE_GET) {
            $_baseContainer = $_GET;
        } elseif ($_submissionMode == self::MODE_REQUEST) {
            $_baseContainer = $_REQUEST;
        } elseif ($_submissionMode == self::MODE_POST) {
            $_baseContainer = $_POST;
        } else {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (count($_FILES) > 0) {
            $_baseContainer = array_merge($_baseContainer, $_FILES);
        }

        $_customFieldGroupContainer = array();
        if ($_checkMode == self::CHECKMODE_STAFF) {
            $_customFieldGroupContainer = self::RetrieveOnStaff($_groupTypeList, $_SWIFT->Staff, 0, $_departmentID);
        } elseif ($_checkMode == self::CHECKMODE_CLIENT) {
            $_customFieldGroupContainer = self::Retrieve($_groupTypeList, 0, $_departmentID);
        } else if ($_checkMode == self::CHECKMODE_WORKFLOW) {
            $_customFieldGroupContainer = self::RetrieveOnWorkflow($_groupTypeList);
        } else {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // No groups? let em continue
        if (!_is_array($_customFieldGroupContainer)) {
            return array(true, array());
        }

        $_failureFields = array();

        // Itterate through each group and check for values of each custom field
        foreach ($_customFieldGroupContainer as $_customFieldGroupID => $_customFieldGroup) {
            foreach ($_customFieldGroup['_fields'] as $_customFieldID => $_customField) {
                if ($_mode == SWIFT_UserInterface::MODE_EDIT &&
                    (($_checkMode == self::CHECKMODE_CLIENT && $_customField['usereditable'] == '0') || ($_checkMode == self::CHECKMODE_STAFF && $_customField['staffeditable'] == '0'))) {
                    continue;
                }

                $_fieldValue = '';
                if ($_customField['fieldtype'] != SWIFT_CustomField::TYPE_FILE && isset($_baseContainer[$_customField['fieldname']])) {
                    $_fieldValue = $_baseContainer[$_customField['fieldname']];
                } elseif ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_FILE && isset($_FILES[$_customField['fieldname']])) {
                    $_fieldValue = $_FILES[$_customField['fieldname']]['name'];
                }

                /**
                 * BUG FIX : Simaranjit Singh
                 *
                 * SWIFT-2587 : Custom field values are not allowed if user inserts '0' in any required fields. It actully reads '0' as blank.
                 *
                 * Comments : Instead of empty used trim and null check.
                 */
                /**
                 * BUG FIX : Mansi Wason
                 *
                 * SWIFT-4627 : trim() expects parameter 1 to be string, array given(__swift/apps/base/library/CustomField/class.SWIFT_CustomFieldManager.php:272).
                 */
                // ======= IS REQUIRED CHECK =======
                if ($_customField['isrequired'] == '1' &&
                    (
                        (!isset($_baseContainer[$_customField['fieldname']])) ||
                        (isset($_baseContainer[$_customField['fieldname']]) && is_scalar($_baseContainer[$_customField['fieldname']]) && trim($_baseContainer[$_customField['fieldname']]) == '') ||
                        (isset($_baseContainer[$_customField['fieldname']]) && is_array($_baseContainer[$_customField['fieldname']]) && !count($_baseContainer[$_customField['fieldname']])) ||
                        (isset($_baseContainer[$_customField['fieldname']]) && is_array($_baseContainer[$_customField['fieldname']]) && isset($_baseContainer[$_customField['fieldname']]['error']) && !empty($_baseContainer[$_customField['fieldname']]['error']))
                    )
                ) {
                    // We ignore isrequired property for 'custom' fields in staff
                    if ($_checkMode == self::CHECKMODE_STAFF && $_customField['fieldtype'] == SWIFT_CustomField::TYPE_CUSTOM) {
                    } else {
                        $_failureFields[] = $_customField['fieldname'];
                    }
                } elseif (trim($_customField['regexpvalidate']) != '') {
                    if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECT || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTMULTIPLE || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTLINKED) {
                        if (_is_array($_fieldValue)) {
                            foreach ($_fieldValue as $_val) {
                                if (_is_array($_val)) { // If it is an array, seems it's a linked select field.
                                    foreach ($_val as $_pid => $_lid) {
                                        if (isset($_customField['_options'][$_lid]) && !@preg_match($_customField['regexpvalidate'],
                                                $_customField['_options'][$_lid]['optionvalue'])) {
                                            $_failureFields[] = $_customField['fieldname'];
                                        }
                                    }
                                    continue;
                                }
                                if (isset($_customField['_options'][$_val]) && !@preg_match($_customField['regexpvalidate'], $_customField['_options'][$_val]['optionvalue'])) {
                                    $_failureFields[] = $_customField['fieldname'];
                                }
                            }
                        } elseif (isset($_customField['_options'][$_fieldValue]) && !@preg_match($_customField['regexpvalidate'], $_customField['_options'][$_fieldValue]['optionvalue'])) {
                            $_failureFields[] = $_customField['fieldname'];
                        }
                    } else {
                        if (trim($_fieldValue) != '' && !@preg_match($_customField['regexpvalidate'], $_fieldValue)) {
                            $_failureFields[] = $_customField['fieldname'];
                        }
                    }
                    // Field is empty and required?
                    if (!_is_array($_fieldValue) && trim($_fieldValue) == '' && $_customField['isrequired'] == 1) {
                        $_failureFields[] = $_customField['fieldname'];
                    }
                }
            }
        }

        if (count($_failureFields)) {
            return array(false, $_failureFields);
        }

        return array(true, array());
    }

    /**
     * @param int $_ticketWorkflowID
     * @param array $_groupTypeList
     * @param int $_linkTypeId
     * @return bool
     * @throws SWIFT_Exception
     */
    public function UpdateForTicket($_ticketWorkflowID, $_groupTypeList, $_linkTypeId)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_customFieldGroupContainer = self::RetrieveOnWorkflow($_groupTypeList);

        // No groups? let em continue
        if (!_is_array($_customFieldGroupContainer)) {
            return true;
        }

        $_isTicketGroup = !empty(array_intersect([SWIFT_CustomFieldGroup::GROUP_STAFFTICKET,
                SWIFT_CustomFieldGroup::GROUP_USERTICKET,
                SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET,
            ], $_groupTypeList));
        $_ticketDepartmentID = 0;
        if ($_isTicketGroup) {
            $_SWIFT_TicketTypeObject = SWIFT_Ticket::GetObjectOnID($_linkTypeId);
            if ($_SWIFT_TicketTypeObject) {
                $_ticketDepartmentID = (int)($_SWIFT_TicketTypeObject->GetProperty('departmentid'));
            }
        }

        // Itterate through each group and retrieve the custom field value objects
        $_customFieldWorkflowValueObjectContainer = $_customFieldValueObjectContainer = $_customFieldLinkObjectContainer = $_customFieldIDList = $_customFieldGroupIDList = array();
        foreach ($_customFieldGroupContainer as $_customFieldGroupID => $_customFieldGroup) {
            // If there are no departments assigned, do not process
            if (empty($_customFieldGroup['_departments'])) {
                continue;
            }

            // Iterate through each group department, then compare it with the
            // ticket department. If there's no match, continue
            $_hasDeparment = false;
            foreach ($_customFieldGroup['_departments'] as $_departmentID) {
                if ($_ticketDepartmentID === (int) $_departmentID) {
                    $_hasDeparment = true;
                    break;
                }
            }

            if (!$_hasDeparment) {
                continue;
            }
            $_customFieldGroupIDList[] = $_customFieldGroupID;

            foreach ($_customFieldGroup['_fields'] as $_customFieldID => $_customField) {
                $_customFieldIDList[] = $_customFieldID;
            }
        }

        if (!_is_array($_customFieldGroupIDList) || !_is_array($_customFieldIDList)) {
            return false;
        }

        // Retrieve the default value objects
        $_finalCustomFieldIDList = [];
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldworkflowvalues WHERE customfieldid IN (" . BuildIN($_customFieldIDList) . ") AND ticketworkflowid = '" . $_ticketWorkflowID . "' and isincluded = 1");
        while ($this->Database->NextRecord()) {
            $customfieldid = $this->Database->Record['customfieldid'];
            $_customFieldWorkflowValueObjectContainer[$customfieldid] = new SWIFT_CustomFieldWorkflowValue(new SWIFT_DataStore($this->Database->Record));
            $_finalCustomFieldIDList[] = $customfieldid;
        }

        // Retrieve the link objects for users
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldlinks WHERE grouptype IN (" . BuildIN($_groupTypeList) . ") AND linktypeid = '" . $_linkTypeId . "'");
        while ($this->Database->NextRecord()) {
            $_customFieldLinkObjectContainer[$this->Database->Record['customfieldgroupid']] = new SWIFT_CustomFieldLink(new SWIFT_DataStore($this->Database->Record));
        }

        // Retrieve the value objects for users
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldvalues WHERE customfieldid IN (" . BuildIN($_finalCustomFieldIDList) . ") AND typeid = '" . $_linkTypeId . "'");
        while ($this->Database->NextRecord()) {
            $customfieldid = $this->Database->Record['customfieldid'];
            $_dataStore = $this->Database->Record;
            if (isset($_customFieldWorkflowValueObjectContainer[$customfieldid])) {
                $customfieldvalueid = $_dataStore['customfieldvalueid'];
                $_dataStore = $_customFieldWorkflowValueObjectContainer[$customfieldid]->GetDataStore();
                // need to use the same datastore, with different property names
                unset($_dataStore['customfieldworkflowvalueid'], $_dataStore['isincluded']);
                $_dataStore['customfieldvalueid'] =  $customfieldvalueid;
            }
            $_customFieldValueObjectContainer[$customfieldid] = new SWIFT_CustomFieldValue(new SWIFT_DataStore($_dataStore));
        }

        // Iterate through each group and check for values and objects
        foreach ($_customFieldGroupContainer as $_customFieldGroupID => $_customFieldGroup) {
            // Don't process fields that do not belong to the ticket department
            if (!in_array($_customFieldGroupID, $_customFieldGroupIDList)) {
                continue;
            }
            // This custom field group isnt linked to the type? link it so that the info shows up in static rendering
            if (!isset($_customFieldLinkObjectContainer[$_customFieldGroupID])) {
                SWIFT_CustomFieldLink::Create($_customFieldGroup['grouptype'], $_linkTypeId, $_customFieldGroupID);
            }

            foreach ($_customFieldGroup['_fields'] as $_customFieldID => $_customField) {
                if (!in_array($_customFieldID, $_finalCustomFieldIDList)) {
                    // don't process fields that are not included
                    continue;
                }
                $_fieldValue = '';
                $_isSerialized = false;
                $_isEncrypted = false;

                if ($_customField['fieldtype'] != SWIFT_CustomField::TYPE_FILE) {
                    $_baseValue = isset($_customFieldValueObjectContainer[$_customFieldID])? $_customFieldValueObjectContainer[$_customFieldID]->GetProperty('fieldvalue') : $_customFieldWorkflowValueObjectContainer[$_customFieldID]->GetProperty('fieldvalue');
                    $_isSerialized = isset($_customFieldValueObjectContainer[$_customFieldID])? $_customFieldValueObjectContainer[$_customFieldID]->GetProperty('isserialized') : $_customFieldWorkflowValueObjectContainer[$_customFieldID]->GetProperty('isserialized');
                    if ($_isSerialized) {
                        $_baseValue = unserialize($_baseValue);
                    }

                    if (is_array($_baseValue)) {
                        if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTLINKED && isset($_baseValue[1])) {
                            foreach ($_baseValue[1] as $_parentField => $_childField) {
                                if ($_parentField != $_baseValue[0]) {
                                    unset($_baseValue[1][$_parentField]);
                                }
                            }
                        }

                        $_fieldValue = serialize($_baseValue);
                        $_isSerialized = true;

                    } else {
                        $_fieldValue = $_baseValue;
                    }

                    // Encrypt the data for the password field
                    if (!empty($_fieldValue) && ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_PASSWORD || $_customField['encryptindb'] == '1')) {
                        $_fieldValue = self::Encrypt($_fieldValue);
                        $_isEncrypted = true;
                    }
                    // Is it a file custom field?
                } else if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_FILE) {
                    $_fieldValue = isset($_customFieldValueObjectContainer[$_customFieldID])? $_customFieldValueObjectContainer[$_customFieldID]->GetProperty('fieldvalue') : $_customFieldWorkflowValueObjectContainer[$_customFieldID]->GetProperty('fieldvalue');
                }

                if (empty($_fieldValue)) {
                    // ignore empty fields
                    continue;
                }

                // If the value is already set then update it
                if (isset($_customFieldValueObjectContainer[$_customFieldID])) {
                    $_customFieldValueObjectContainer[$_customFieldID]->Update($_fieldValue, $_isSerialized,
                        $_isEncrypted);
                } else {
                    SWIFT_CustomFieldValue::Create($_customFieldID, $_linkTypeId, $_fieldValue, $_isSerialized,
                        $_isEncrypted);
                }
            }
        }

        return true;
    }

    /**
     * Update the custom field values
     *
     * @author Varun Shoor
     * @param mixed $_submissionMode The Submission Mode
     * @param mixed $_mode The UI Mode
     * @param array $_groupTypeList The Group Type List
     * @param mixed $_checkMode The Check Mode
     * @param int $_linkTypeID
     * @param int $_departmentID (OPTIONAL) The Department ID to filter on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_submissionMode, $_mode, $_groupTypeList, $_checkMode, $_linkTypeID, $_departmentID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_baseContainer = array();
        if ($_submissionMode == self::MODE_GET) {
            $_baseContainer = $_GET;
        } elseif ($_submissionMode == self::MODE_REQUEST) {
            $_baseContainer = $_REQUEST;
        } elseif ($_submissionMode == self::MODE_POST) {
            $_baseContainer = $_POST;
        } else {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_customFieldGroupContainer = array();
        if ($_checkMode == self::CHECKMODE_STAFF) {
            $_customFieldGroupContainer = self::RetrieveOnStaff($_groupTypeList, $_SWIFT->Staff, 0, $_departmentID);
        } elseif ($_checkMode == self::CHECKMODE_CLIENT) {
            $_customFieldGroupContainer = self::Retrieve($_groupTypeList, 0, $_departmentID);
        } else if ($_checkMode == self::CHECKMODE_WORKFLOW) {
            $_customFieldGroupContainer = self::RetrieveOnWorkflow($_groupTypeList);
        } else {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // No groups? let em continue
        if (!_is_array($_customFieldGroupContainer)) {
            return true;
        }

        // Itterate through each group and retrieve the custom field value objects
        $_customFieldValueObjectContainer = $_customFieldLinkObjectContainer = $_customFieldIDList = $_customFieldGroupIDList = array();
        foreach ($_customFieldGroupContainer as $_customFieldGroupID => $_customFieldGroup) {
            $_customFieldGroupIDList[] = $_customFieldGroupID;

            foreach ($_customFieldGroup['_fields'] as $_customFieldID => $_customField) {
                $_customFieldIDList[] = $_customFieldID;
            }
        }

        if (!_is_array($_customFieldGroupIDList) || !_is_array($_customFieldIDList)) {
            return false;
        }

        if ($_checkMode == self::CHECKMODE_WORKFLOW) {
            // Retrieve the value objects
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldworkflowvalues WHERE customfieldid IN (" . BuildIN($_customFieldIDList) . ") AND ticketworkflowid = '" . ($_linkTypeID) . "'");
            while ($this->Database->NextRecord()) {
                $_customFieldValueObjectContainer[$this->Database->Record['customfieldid']] = new SWIFT_CustomFieldWorkflowValue(new SWIFT_DataStore($this->Database->Record));
            }
        } else {
            // Retrieve the link objects
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldlinks WHERE grouptype IN (" . BuildIN($_groupTypeList) . ") AND linktypeid = '" . $_linkTypeID . "'");
            while ($this->Database->NextRecord()) {
                $_customFieldLinkObjectContainer[$this->Database->Record['customfieldgroupid']] = new SWIFT_CustomFieldLink(new SWIFT_DataStore($this->Database->Record));
            }

            // Retrieve the value objects
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldvalues WHERE customfieldid IN (" . BuildIN($_customFieldIDList) . ") AND typeid = '" . $_linkTypeID . "'");
            while ($this->Database->NextRecord()) {
                $_customFieldValueObjectContainer[$this->Database->Record['customfieldid']] = new SWIFT_CustomFieldValue(new SWIFT_DataStore($this->Database->Record));
            }
        }

        // Itterate through each group and check for values and objects
        foreach ($_customFieldGroupContainer as $_customFieldGroupID => $_customFieldGroup) {
            // This custom field group isnt linked to the type? link it so that the info shows up in static rendering
            if (!isset($_customFieldLinkObjectContainer[$_customFieldGroupID]) &&
                $_checkMode != self::CHECKMODE_WORKFLOW) {
                SWIFT_CustomFieldLink::Create($_customFieldGroup['grouptype'], $_linkTypeID, $_customFieldGroupID);
            }

            foreach ($_customFieldGroup['_fields'] as $_customFieldID => $_customField) {
                if ($_mode == SWIFT_UserInterface::MODE_EDIT &&
                    (($_checkMode == self::CHECKMODE_CLIENT && $_customField['usereditable'] == '0') ||
                        ($_checkMode == self::CHECKMODE_STAFF && $_customField['staffeditable'] == '0') ||
                        ($_checkMode == self::CHECKMODE_STAFF && $_customField['fieldtype'] == SWIFT_CustomField::TYPE_CUSTOM))
                ) {
                    continue;
                }

                $_fieldValue = '';
                $_isSerialized = false;
                $_isEncrypted = false;
                if ($_customField['fieldtype'] != SWIFT_CustomField::TYPE_FILE && isset($_baseContainer[$_customField['fieldname']])) {
                    $_baseValue = $_baseContainer[$_customField['fieldname']];

                    if (is_array($_baseValue)) {
                        /*
                         * BUG FIX - Pankaj Garg, Andriy Lesyuk
                         *
                         * SWIFT-2506 Redundant data in database for linked select custom fields
                         *
                         * Comments: Removed extra fields from array to be stored in db
                         */

                        /**
                         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
                         *
                         * SWIFT-4282 Undefined Offset error occurs while updating the ticket under the Edit tab if there is no child under the parent link select node
                         */
                        if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTLINKED && isset($_baseValue[1])) {
                            foreach ($_baseValue[1] as $_parentField => $_childField) {
                                if ($_parentField != $_baseValue[0]) {
                                    unset($_baseValue[1][$_parentField]);
                                }
                            }
                        }

                        $_fieldValue = serialize($_baseValue);
                        $_isSerialized = true;

                    } else {
                        $_fieldValue = $_baseValue;
                    }

                    // Encrypt the data for the password field
                    if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_PASSWORD || $_customField['encryptindb'] == '1') {
                        $_fieldValue = self::Encrypt($_fieldValue);
                        $_isEncrypted = true;
                    }
                    // Is it a file custom field?
                } elseif ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_FILE) {
                    // Do we have an existing file uploaded before and a new one is specified?
                    if (isset($_customFieldValueObjectContainer[$_customFieldID]) && isset($_FILES[$_customField['fieldname']]['name']) && !empty($_FILES[$_customField['fieldname']]['name'])) {
                        SWIFT_FileManager::DeleteList([$_customFieldValueObjectContainer[$_customFieldID]->GetProperty('fieldvalue')]);
                        // If we have an already uploaded file and no new file was specified then make sure the fileid does not vanish..use the old file id
                    } elseif (isset($_customFieldValueObjectContainer[$_customFieldID])) {
                        $_fieldValue = $_customFieldValueObjectContainer[$_customFieldID]->GetProperty('fieldvalue');
                    }

                    // Attempt to create the file
                    if (isset($_FILES[$_customField['fieldname']]['name']) && !empty($_FILES[$_customField['fieldname']]['name']) && is_uploaded_file($_FILES[$_customField['fieldname']]['tmp_name'])) {
                        $_fieldValue = SWIFT_FileManager::Create($_FILES[$_customField['fieldname']]['tmp_name'], $_FILES[$_customField['fieldname']]['name'], false, true);
                    }
                }

                $incIdx = 'inc_' . $_customField['fieldname'];
                $_isIncluded = isset($_baseContainer[$incIdx])? $_baseContainer[$incIdx] : 0;

                /**
                 * Bug : Mansi Wason <mansi.wason@opencart.com.vn>
                 *
                 * SWIFT: 4020 Adjust for custom fields API (API/UpdateTicketCustomFields) to update only when data is provided
                 * SWIFT: 4870 File Type Custom field do not retain uploaded files
                 */
                // If the value is already set then update it
                if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_API) {
                    if (isset($_baseContainer[$_customField['fieldname']])) {
                        if (isset($_customFieldValueObjectContainer[$_customFieldID])) {
                            if ($_checkMode == self::CHECKMODE_WORKFLOW) {
                                $_customFieldValueObjectContainer[$_customFieldID]->Update($_fieldValue, $_isSerialized,
                                    $_isEncrypted, $_isIncluded);
                            } else {
                                $_customFieldValueObjectContainer[$_customFieldID]->Update($_fieldValue, $_isSerialized,
                                    $_isEncrypted);
                            }
                            // Or create a new one if it doesnt exist
                        } else {
                            if ($_checkMode == self::CHECKMODE_WORKFLOW) {
                                SWIFT_CustomFieldWorkflowValue::Create($_customFieldID, $_linkTypeID, $_fieldValue,
                                    $_isSerialized, $_isEncrypted, $_isIncluded);
                            } else {
                                SWIFT_CustomFieldValue::Create($_customFieldID, $_linkTypeID, $_fieldValue,
                                    $_isSerialized, $_isEncrypted);
                            }
                        }
                    }
                } else {
                    if (isset($_customFieldValueObjectContainer[$_customFieldID])) {
                        if ($_checkMode == self::CHECKMODE_WORKFLOW) {
                            $_customFieldValueObjectContainer[$_customFieldID]->Update($_fieldValue, $_isSerialized,
                                $_isEncrypted, $_isIncluded);
                        } else {
                            $_customFieldValueObjectContainer[$_customFieldID]->Update($_fieldValue, $_isSerialized,
                                $_isEncrypted);
                        }
                    } else {
                        if ($_checkMode == self::CHECKMODE_WORKFLOW) {
                            SWIFT_CustomFieldWorkflowValue::Create($_customFieldID, $_linkTypeID, $_fieldValue, $_isSerialized, $_isEncrypted, $_isIncluded);
                        } else {
                            SWIFT_CustomFieldValue::Create($_customFieldID, $_linkTypeID, $_fieldValue, $_isSerialized,
                                $_isEncrypted);
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Decrypt the String
     *
     * @author Varun Shoor
     * @param string $_stringValue The String Value
     * @return string The Decrypted String
     */
    public static function Decrypt($_stringValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_stringValue = base64_decode($_stringValue);
        $iv_size = openssl_cipher_iv_length('aes-256-ecb');
        $iv = $iv_size > 0 ? openssl_random_pseudo_bytes($iv_size) : '';
        $_decrypted = openssl_decrypt($_stringValue, 'aes-256-ecb', SWIFT::Get('InstallationHash'), OPENSSL_RAW_DATA, $iv);

        if (! $_decrypted) {
            // Fallback to mcrypt as this might have been encrypted from an older version using mcrypt.
            $_decrypted = self::DecryptMCrypt($_stringValue);
        }

        return trim($_decrypted);
    }

    /**
     * Encrypt the String
     *
     * @author Varun Shoor
     * @param string $_stringValue The Value
     * @return string The Encrypted String
     */
    public static function Encrypt($_stringValue)
    {
        $_SWIFT = SWIFT::GetInstance();

//        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
//        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
//
//        $_crypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, SWIFT::Get('InstallationHash'), $_stringValue, MCRYPT_MODE_ECB, $iv);

        $iv_size = openssl_cipher_iv_length('aes-256-ecb');
        $iv = $iv_size > 0 ? openssl_random_pseudo_bytes($iv_size) : '';
        $_crypted = openssl_encrypt($_stringValue, 'aes-256-ecb', SWIFT::Get('InstallationHash'), OPENSSL_RAW_DATA, $iv);

        return trim(base64_encode($_crypted));
    }

    /**
     * @param string $_decodedStringValue
     * @return bool|string
     */
    private static function DecryptMCrypt($_decodedStringValue)
    {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, SWIFT::Get('InstallationHash'), $_decodedStringValue, MCRYPT_MODE_ECB, $iv);
    }

    /**
     * Retrieve the Custom Fields Associated with Given Group Types
     *
     * @author Varun Shoor
     * @param array $_groupTypeList The Group Type List
     * @param int $_typeID (OPTIONAL) The Type ID to Load Values From
     * @param int $_departmentID (OPTIONAL) The Department ID to filter on
     * @param bool $_filterByLinks (OPTIONAL) Whether to filter the display by links
     * @return array|bool The Custom Field Group Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve($_groupTypeList, $_typeID = 0, $_departmentID = 0, $_filterByLinks = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_groupTypeList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        foreach ($_groupTypeList as $_groupType) {
            if (!SWIFT_CustomFieldGroup::IsValidGroupType($_groupType)) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }
        }

        $_customFieldCache = $_SWIFT->Cache->Get('customfieldcache');
        if (!_is_array($_customFieldCache)) {
            return true;
        }

        $_finalCustomFieldGroupContainer = $_linkedCustomFieldGroupIDList = array();
        if ($_filterByLinks == true && !empty($_typeID)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldlinks WHERE grouptype IN(" . BuildIN($_groupTypeList) . ") and linktypeid = '" . $_typeID . "'");
            while ($_SWIFT->Database->NextRecord()) {
                $_linkedCustomFieldGroupIDList[] = $_SWIFT->Database->Record['customfieldgroupid'];
            }
        }

        // Itterate through groups
        foreach ($_customFieldCache as $_groupType => $_customFieldGroupsContainer) {
            if (!in_array($_groupType, $_groupTypeList)) {
                continue;
            }

            foreach ($_customFieldGroupsContainer as $_customFieldGroupID => $_customFieldGroup) {
                if (!empty($_departmentID) && !in_array($_departmentID, $_customFieldGroup['_departments'])) {
                    continue;
                } elseif ($_filterByLinks == true && !in_array($_customFieldGroupID, $_linkedCustomFieldGroupIDList)) {
                    continue;

                    // Private Visibility?
                } elseif ($_customFieldGroup['visibilitytype'] == '0') {
                    continue;
                }

                $_finalCustomFieldGroupContainer[$_customFieldGroupID] = $_customFieldGroup;
            }
        }

        return $_finalCustomFieldGroupContainer;
    }

    /**
     * Retrieve the Custom Fields Associated with Given Group Type and Staff
     *
     * @author Varun Shoor
     * @param array $_groupTypeList The Group Type List
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param int $_typeID (OPTIONAL) The Type ID to Load Values From
     * @param int $_departmentID (OPTIONAL) The Department ID to filter on
     * @param bool $_filterByLinks (OPTIONAL) Whether to filter the display by links
     * @return array|bool The Custom Field Group Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnStaff($_groupTypeList, SWIFT_Staff $_SWIFT_StaffObject, $_typeID = 0, $_departmentID = 0, $_filterByLinks = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_groupTypeList) || !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        foreach ($_groupTypeList as $_groupType) {
            if (!SWIFT_CustomFieldGroup::IsValidGroupType($_groupType)) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }
        }

        $_customFieldCache = $_SWIFT->Cache->Get('customfieldcache');
        if (!_is_array($_customFieldCache)) {
            return true;
        }

        $_finalCustomFieldGroupContainer = $_linkedCustomFieldGroupIDList = array();
        if ($_filterByLinks == true && !empty($_typeID)) {
            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
             *
             * SWIFT-5097 Infinite loop with several custom fields.
             *
             * Comments: Optimizing query.
             */
            $_SWIFT->Database->Query("SELECT customfieldgroupid FROM " . TABLE_PREFIX . "customfieldlinks
                WHERE grouptype IN(" . BuildIN($_groupTypeList) . ") and linktypeid = '" . $_typeID . "'");
            while ($_SWIFT->Database->NextRecord()) {
                $_linkedCustomFieldGroupIDList[] = $_SWIFT->Database->Record['customfieldgroupid'];
            }
        }

        // Itterate through groups
        foreach ($_customFieldCache as $_groupType => $_customFieldGroupsContainer) {
            if (!in_array($_groupType, $_groupTypeList)) {
                continue;
            }

            foreach ($_customFieldGroupsContainer as $_customFieldGroupID => $_customFieldGroup) {
                $staff_permission = isset($_customFieldGroup['_permissions'][SWIFT_CustomFieldGroupPermission::TYPE_STAFF][$_SWIFT_StaffObject->GetStaffID()]) ?
                    $_customFieldGroup['_permissions'][SWIFT_CustomFieldGroupPermission::TYPE_STAFF][$_SWIFT_StaffObject->GetStaffID()] : '';
                $group_permission = isset($_customFieldGroup['_permissions'][SWIFT_CustomFieldGroupPermission::TYPE_STAFFGROUP][$_SWIFT_StaffObject->GetProperty('staffgroupid')]) ?
                    $_customFieldGroup['_permissions'][SWIFT_CustomFieldGroupPermission::TYPE_STAFFGROUP][$_SWIFT_StaffObject->GetProperty('staffgroupid')] : '';
                // Check for permission
                if ($staff_permission != 'yes' && ($staff_permission == 'no' || $group_permission == 'no')) {
                    continue;
                } elseif (!empty($_departmentID) && !in_array($_departmentID, $_customFieldGroup['_departments']) && $_filterByLinks == false) {
                    continue;
                } elseif ($_filterByLinks == true && !in_array($_customFieldGroupID, $_linkedCustomFieldGroupIDList)) {
                    continue;
                }

                $_finalCustomFieldGroupContainer[$_customFieldGroupID] = $_customFieldGroup;
            }
        }

        return $_finalCustomFieldGroupContainer;
    }

    /**
     * Retrieve the Custom Fields Associated with Given Group Type
     *
     * @author Werner Garcia
     * @param array $_groupTypeList The Group Type List
     * @return array|bool The Custom Field Group Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnWorkflow($_groupTypeList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_groupTypeList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        foreach ($_groupTypeList as $_groupType) {
            if (!SWIFT_CustomFieldGroup::IsValidGroupType($_groupType)) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }
        }

        $_customFieldCache = (array) $_SWIFT->Cache->Get('customfieldcache');
        if (!_is_array($_customFieldCache)) {
            return false;
        }

        $_finalCustomFieldGroupContainer = array();

        // Itterate through groups
        foreach ($_customFieldCache as $_groupType => $_customFieldGroupsContainer) {
            if (!in_array($_groupType, $_groupTypeList)) {
                continue;
            }

            foreach ($_customFieldGroupsContainer as $_customFieldGroupID => $_customFieldGroup) {
                $_finalCustomFieldGroupContainer[$_customFieldGroupID] = $_customFieldGroup;
            }
        }

        return $_finalCustomFieldGroupContainer;
    }

    /**
     * Dispatch a file after verifying all details
     *
     * @author Varun Shoor
     * @param int $_customFieldID The Custom Field ID
     * @param string $_uniqueHash The Unique Hash
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DispatchFile($_customFieldID, $_uniqueHash)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_customFieldID) || empty($_uniqueHash)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // First get the custom field value
        $_customFieldValueContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "customfieldvalues WHERE uniquehash = '" . $this->Database->Escape($_uniqueHash) . "'");
        if (!isset($_customFieldValueContainer['customfieldvalueid']) || empty($_customFieldValueContainer['customfieldvalueid'])) {
            echo 'Invalid File Request';
            log_error_and_exit();
        } elseif ($_customFieldValueContainer['customfieldid'] != $_customFieldID) {
            echo 'Access Denied';
            log_error_and_exit();
        }

        try {
            $_SWIFT_FileManagerObject = new SWIFT_FileManager($_customFieldValueContainer['fieldvalue']);
            $_SWIFT_FileManagerObject->Dispatch();
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            echo 'File does not exist';
            return false;
        }

        return true;
    }

    /**
     * FEATURE - Mansi Wason <mansi.wason@opencart.com.vn>
     *
     * SWIFT-3186 Custom field data in autoresponders, ticket notifications.
     */
    /**
     * BUG FIX - Mansi Wason <mansi.wason@opencart.com.vn>
     *
     * SWIFT-5018: Issue with Custom Fields rendering in Notification emails
     */
    /**
     * @author Mansi Wason <mansi.wason@opencart.com.vn>
     *
     * @param int $_ticketID
     *
     * @return string|array The Custom Field Value
     */
    public function GetCustomFieldValue($_ticketID)
    {
        $_customFields = array();
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        $_customFieldCache = $this->Cache->Get('customfieldcache');
        $_customFieldMapCache = $this->Cache->Get('customfieldmapcache');
        $_customFieldOptionCache = $this->Cache->Get('customfieldoptioncache');

        $_customFieldIDList = array();

        $_customFieldGroupTypeList = array(SWIFT_CustomFieldGroup::GROUP_STAFFTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET, SWIFT_CustomFieldGroup::GROUP_USERTICKET);

        $_rawCustomFieldValueContainer = $_customFieldValueContainer = $_customArguments = $_linkedCustomFieldGroupIDList = array();

        $_departmentID = $_SWIFT_TicketObject->GetProperty('departmentid');

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfielddeplinks WHERE departmentid = '" . $_departmentID . "'");
        while ($this->Database->NextRecord()) {
            $_linkedCustomFieldGroupIDList[] = $this->Database->Record['customfieldgroupid'];
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldlinks WHERE grouptype IN (" . BuildIN($_customFieldGroupTypeList) . ") AND linktypeid = '" . $_ticketID . "'");
        while ($this->Database->NextRecord()) {
            if (!in_array($this->Database->Record['customfieldgroupid'], $_linkedCustomFieldGroupIDList)) {
                $_linkedCustomFieldGroupIDList[] = $this->Database->Record['customfieldgroupid'];
            }
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfields WHERE customfieldgroupid    IN (" . BuildIN($_linkedCustomFieldGroupIDList) . ")");
        while ($this->Database->NextRecord()) {
            $_customFieldIDList[] = $this->Database->Record['customfieldid'];
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldvalues WHERE customfieldid IN (" . BuildIN($_customFieldIDList) . ") AND typeid = '" . $_ticketID . "'");
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
            } elseif ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_RADIO || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECT) {
                if (isset($_customFieldOptionCache[$_fieldValue])) {
                    $_fieldValue = $_customFieldOptionCache[$_fieldValue];
                }
            } elseif ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_SELECTLINKED) {
                $_fieldValueInterim = '';
                if (isset($_fieldValue[0]) && isset($_customFieldOptionCache[$_fieldValue[0]])) {
                    $_fieldValueInterim = $_customFieldOptionCache[$_fieldValue[0]];

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
            } elseif ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_FILE) {
                $_fieldValueInterim = '';

                try {
                    $_SWIFT_FileManagerObject = new SWIFT_FileManager($_fieldValue);

                    $_fieldValueInterim = $_SWIFT_FileManagerObject->GetProperty('originalfilename');
                    $_customArguments[$_customField['customfieldid']]['filename'] = $_SWIFT_FileManagerObject->GetProperty('originalfilename');
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                }

                $_fieldValue = $_fieldValueInterim;
            }

            $_customFieldValueContainer[$this->Database->Record['customfieldid']] = $_fieldValue;
        }
        if (_is_array($_customFieldCache)) {
            foreach ($_customFieldCache as $_groupType => $_customFieldGroupContainer) {
                if (!in_array($_groupType, $_customFieldGroupTypeList)) {
                    continue;
                }

                foreach ($_customFieldGroupContainer as $_customFieldGroupID => $_customFieldGroup) {
                    if (!in_array($_customFieldGroupID, $_linkedCustomFieldGroupIDList)) {
                        continue;
                    }

                    foreach ($_customFieldGroup['_fields'] as $_customFieldID => $_customField) {
                        $_customFieldValue = '';

                        if (isset($_customFieldValueContainer[$_customFieldID])) {
                            if (_is_array($_customFieldValueContainer[$_customFieldID])) {

                                array_walk($_customFieldValueContainer[$_customFieldID], function (&$_fieldValue) {
                                    $_fieldValue = str_replace(',', '\,', $_fieldValue);
                                });

                                $_customFieldValue = implode(', ', $_customFieldValueContainer[$_customFieldID]);
                            } else {
                                $_customFieldValue = $_customFieldValueContainer[$_customFieldID];
                            }
                        }
                        $_fieldArguments = array('title' => $_customField['title']);
                        if (isset($_customArguments[$_customFieldID])) {
                            $_fieldArguments = array_merge($_fieldArguments, $_customArguments[$_customFieldID]);
                        }
                        $_customFieldGroup['title'] = preg_replace('/[\s-]+/', '_', $_customFieldGroup['title']);
                        $_fieldArguments['title'] = preg_replace('/[\s-]+/', '_', $_fieldArguments['title']);
                        $_customFields[$_customFieldGroup['title']][$_fieldArguments['title']] = $_customFieldValue;
                    }
                }
            }
        }
        return $_customFields;
    }

    /**
     * BUG FIX - Andriy Lesyuk
     *
     * SWIFT-2506: Redundant data in database for linked select custom fields
     *
     * Comments: We need to fix values already stored in the database
     */
    /**
     * Remove redundant IDs from linked select values
     *
     * @author Andriy Lesyuk <andriy.lesyuk@opencart.com.vn>
     *
     * @return int The count of fixed values
     */
    public static function FixLinkedSelectValues()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->Query("SELECT customfieldvalueid, fieldvalue, isencrypted, customfieldvalues.customfieldid
                                  FROM " . TABLE_PREFIX . "customfieldvalues customfieldvalues
                                  JOIN " . TABLE_PREFIX . "customfields customfields ON customfields.customfieldid = customfieldvalues.customfieldid
                                  WHERE fieldtype = " . SWIFT_CustomField::TYPE_SELECTLINKED . " AND isserialized = 1");

        $_customFieldValues = array();
        while ($_SWIFT->Database->NextRecord()) {
            $_customFieldValues[] = $_SWIFT->Database->Record;
        }

        $_count = 0;

        foreach ($_customFieldValues as $_customFieldValue) {
            if ($_customFieldValue['isencrypted'] == '1') {
                $_fieldValue = self::Decrypt($_customFieldValue['fieldvalue']);
            } else {
                $_fieldValue = $_customFieldValue['fieldvalue'];
            }

            $_fieldValue = mb_unserialize($_fieldValue);

            if (!is_array($_fieldValue) || !isset($_fieldValue[0]) || !isset($_fieldValue[1])) {
                continue;
            }

            $_modified = false;
            foreach ($_fieldValue[1] as $_key => $_value) {
                if ($_key != $_fieldValue[0]) {
                    unset($_fieldValue[1][$_key]);
                    $_modified = true;
                }
            }

            if ($_modified) {
                $_fieldValue = serialize($_fieldValue);

                if ($_customFieldValue['isencrypted'] == '1') {
                    $_fieldValue = self::Encrypt($_fieldValue);
                }

                $_SWIFT->Database->Query("UPDATE " . TABLE_PREFIX . "customfieldvalues
                                          SET fieldvalue = '" . $_SWIFT->Database->Escape($_fieldValue) . "'
                                          WHERE customfieldvalueid = " . $_customFieldValue['customfieldvalueid']);

                $_count++;
            }
        }

        return $_count;
    }

}

?>
