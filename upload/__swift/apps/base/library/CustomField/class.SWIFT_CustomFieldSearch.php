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
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Library\CustomField;

use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Models\CustomField\SWIFT_CustomField;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;
use Base\Library\Rules\SWIFT_Rules;

/**
 * Custom Field Search Class
 *
 * @author Mahesh Salaria
 */
class SWIFT_CustomFieldSearch extends SWIFT_Library
{

    /**
     * Constructor
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Search Custom Fields by Advance Search Rules
     *
     * @author Mahesh Salaria
     *
     * @param string $_customFieldID Custom Filed Unique ID
     * @param string $_searchQuery Search String
     * @param bool $_operator Operator to compare and get ticket values
     * @param bool $_enforceLimit Enforce Limit
     *
     * @return array $_ticketIDList Return Ticket ID list (typeid)
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function SearchCustomFieldByRules($_customFieldID, $_searchQuery, $_operator = false, $_enforceLimit = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_isUserType = false;
        $_resultIDList = array();

        $_limitCount = $_SWIFT->Settings->Get('t_resultlimit');
        if (!$_enforceLimit) {
            $_limitCount = 0;
        }

        $_customFieldMapCache = $_SWIFT->Cache->Get('customfieldmapcache');

        $_customFieldGroupContainer = $_SWIFT->Database->QueryFetch("SELECT grouptype FROM " . TABLE_PREFIX . "customfieldgroups
                                                                     WHERE customfieldgroupid = '" . (int)($_customFieldMapCache[$_customFieldID]['customfieldgroupid']) . "'");

        if ($_customFieldGroupContainer['grouptype'] == SWIFT_CustomFieldGroup::GROUP_USER || $_customFieldGroupContainer['grouptype'] == SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION) {
            $_isUserType = true;
        }

        if (isset($_customFieldMapCache[$_customFieldID]['fieldtype'])) {
            $_CustomFieldType = $_customFieldMapCache[$_customFieldID]['fieldtype'];

            switch ($_CustomFieldType) {
                case SWIFT_CustomField::TYPE_DATE:
                    $_queryCalendarDateLine = GetCalendarDateline($_SWIFT->Database->Escape($_searchQuery));
                    $_SWIFT->Database->QueryLimit("SELECT customfieldvalues.typeid, customfieldvalues.fieldvalue FROM " . TABLE_PREFIX . "customfields AS customfields
                                                   LEFT JOIN " . TABLE_PREFIX . "customfieldvalues AS customfieldvalues ON (customfields.customfieldid = customfieldvalues.customfieldid)
                                                   WHERE customfields.customfieldid = '" . $_customFieldID . "'
                                                   ORDER BY customfieldvalues.typeid DESC", $_limitCount);
                    while ($_SWIFT->Database->NextRecord()) {
                        $_dataCalendarDateLine = GetCalendarDateline($_SWIFT->Database->Record['fieldvalue']);
                        if ($_operator != false) {
                            if ($_operator == SWIFT_Rules::OP_EQUAL && $_dataCalendarDateLine == $_queryCalendarDateLine) {
                                $_resultIDList[] = $_SWIFT->Database->Record['typeid'];
                            } elseif ($_operator == SWIFT_Rules::OP_NOTEQUAL && $_dataCalendarDateLine != $_queryCalendarDateLine) {
                                $_resultIDList[] = $_SWIFT->Database->Record['typeid'];
                            } elseif ($_operator == SWIFT_Rules::OP_LESS && $_dataCalendarDateLine < $_queryCalendarDateLine) {
                                $_resultIDList[] = $_SWIFT->Database->Record['typeid'];
                            } elseif ($_operator == SWIFT_Rules::OP_GREATER && $_dataCalendarDateLine > $_queryCalendarDateLine) {
                                $_resultIDList[] = $_SWIFT->Database->Record['typeid'];
                            }
                        }
                    }

                    break;

                case SWIFT_CustomField::TYPE_CHECKBOX:
                case SWIFT_CustomField::TYPE_SELECTMULTIPLE:
                    $_SWIFT->Database->QueryLimit("SELECT customfieldvalues.typeid, customfieldvalues.fieldvalue FROM " . TABLE_PREFIX . "customfields AS customfields
                                                   LEFT JOIN " . TABLE_PREFIX . "customfieldvalues AS customfieldvalues ON (customfields.customfieldid = customfieldvalues.customfieldid)
                                                   WHERE customfields.customfieldid = '" . $_customFieldID . "' AND customfieldvalues.isserialized = '1'
                                                   ORDER BY customfieldvalues.typeid DESC", $_limitCount);
                    while ($_SWIFT->Database->NextRecord()) {
                        if (!in_array($_SWIFT->Database->Record['typeid'], $_resultIDList)) {
                            $_fieldValuesArray = unserialize($_SWIFT->Database->Record['fieldvalue']);
                            if ($_operator == SWIFT_Rules::OP_EQUAL && in_array($_searchQuery, $_fieldValuesArray)) {
                                $_resultIDList[] = $_SWIFT->Database->Record['typeid'];
                            } elseif ($_operator == SWIFT_Rules::OP_NOTEQUAL && !in_array($_searchQuery, $_fieldValuesArray)) {
                                $_resultIDList[] = $_SWIFT->Database->Record['typeid'];
                            }
                        }
                    }

                    break;

                case SWIFT_CustomField::TYPE_PASSWORD:
                    $_encryptedString = SWIFT_CustomFieldManager::Encrypt($_SWIFT->Database->Escape($_searchQuery));
                    $_SWIFT->Database->QueryLimit("SELECT customfieldvalues.typeid, customfieldvalues.fieldvalue FROM " . TABLE_PREFIX . "customfields AS customfields
                                                   LEFT JOIN " . TABLE_PREFIX . "customfieldvalues AS customfieldvalues ON (customfields.customfieldid = customfieldvalues.customfieldid)
                                                   WHERE customfields.customfieldid = '" . $_customFieldID . "' AND customfieldvalues.isencrypted = '1' AND customfieldvalues.fieldvalue = '" . $_encryptedString . "'
                                                   ORDER BY customfieldvalues.typeid DESC", $_limitCount);

                    break;

                /*
                 * BUG FIX - Simaranjit Singh
                 *
                 * SWIFT-2029 Advanced Search is not working for Linked Select custom fields
                 *
                 */
                /**
                 * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
                 *
                 * SWIFT-2230 Filter results does not show all the ticket IDs if search limit is set as default.
                 *
                 * Comments: Select linked list values should not be limitize.
                 */
                case SWIFT_CustomField::TYPE_SELECTLINKED:

                    $_SWIFT->Database->Query("SELECT customfieldvalues.typeid,customfieldvalues.isencrypted, customfieldvalues.fieldvalue FROM " . TABLE_PREFIX . "customfields AS customfields
                                                   LEFT JOIN " . TABLE_PREFIX . "customfieldvalues AS customfieldvalues ON (customfields.customfieldid = customfieldvalues.customfieldid)
                                                   WHERE customfields.customfieldid = '" . $_customFieldID . "'    ORDER BY customfieldvalues.typeid DESC");

                    while ($_SWIFT->Database->NextRecord()) {
                        if ($_SWIFT->Database->Record['isencrypted'] == '1') {
                            $_serializedData = SWIFT_CustomFieldManager::Decrypt($_SWIFT->Database->Record['fieldvalue']);
                        } else {
                            $_serializedData = $_SWIFT->Database->Record['fieldvalue'];
                        }

                        $_unserializedData = mb_unserialize($_serializedData);
                        if ($_operator == SWIFT_Rules::OP_EQUAL && (isset($_unserializedData[0]) && $_unserializedData[0] == $_searchQuery) || (isset($_unserializedData[1][$_unserializedData[0]]) && $_unserializedData[1][$_unserializedData[0]] == $_searchQuery)) {
                            $_resultIDList[] = $_SWIFT->Database->Record['typeid'];
                        } elseif ($_operator == SWIFT_Rules::OP_NOTEQUAL && !((isset($_unserializedData[0]) && $_unserializedData[0] == $_searchQuery) || (isset($_unserializedData[1][$_unserializedData[0]]) && $_unserializedData[1][$_unserializedData[0]] == $_searchQuery))) {
                            $_resultIDList[] = $_SWIFT->Database->Record['typeid'];
                        }
                    }

                    break;

                default:
                    $_SWIFT->Database->QueryLimit("SELECT customfieldvalues.typeid FROM " . TABLE_PREFIX . "customfields AS customfields
                                                   LEFT JOIN " . TABLE_PREFIX . "customfieldvalues AS customfieldvalues ON (customfields.customfieldid = customfieldvalues.customfieldid)
                                                   WHERE customfields.customfieldid = '" . $_customFieldID . "' AND (" . SWIFT_Rules::BuildSQL('customfieldvalues.fieldvalue', (int)($_operator), $_searchQuery) . ")
                                                   ORDER BY customfieldvalues.typeid DESC", $_limitCount);

                    while ($_SWIFT->Database->NextRecord()) {
                        if (!in_array($_SWIFT->Database->Record['typeid'], $_resultIDList)) {
                            $_resultIDList[] = $_SWIFT->Database->Record['typeid'];
                        }
                    }
            }
        }

        if (!$_isUserType) {

            return $_resultIDList;
        } elseif (count($_resultIDList) > 0) {
            /*
             * BUG FIX - Ashish Kataria
             *
             * SWIFT-2749 Search on the Basis of Custom field for Users is showing Wrong result
             * SWIFT-2014 Custom field search is not working for organisations
             * SWIFT-3259 Custom field advanced search is not working correctly in case of 'User' type custom field
             *
             * Comments: Added case for Group Type User and User Organization
             */
            $_ticketIDList = $_userIDList = array();
            if ($_customFieldGroupContainer['grouptype'] == SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION) {
                // Now get all the users under the searched organizations
                $_SWIFT->Database->QueryLimit("SELECT userid FROM " . TABLE_PREFIX . "users
                                              WHERE userorganizationid IN (" . BuildIN($_resultIDList) . ")", $_limitCount);
                while ($_SWIFT->Database->NextRecord()) {
                    $_userIDList[] = $_SWIFT->Database->Record['userid'];
                }
            } elseif ($_customFieldGroupContainer['grouptype'] == SWIFT_CustomFieldGroup::GROUP_USER) {
                $_userIDList = $_resultIDList;
                unset($_resultIDList);
            }

            if (count($_userIDList) != 0) {
                // Now get all tickets by the given user id
                $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets
                                              WHERE userid IN (" . BuildIN($_userIDList) . ")", $_limitCount);
                while ($_SWIFT->Database->NextRecord()) {
                    if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList)) {
                        $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
                    }
                }

                // Don't forget to search ticket posts!
                $_SWIFT->Database->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "ticketposts
                                              WHERE userid IN (" . BuildIN($_userIDList) . ")", $_limitCount);
                while ($_SWIFT->Database->NextRecord()) {
                    if (!in_array($_SWIFT->Database->Record['ticketid'], $_ticketIDList)) {
                        $_ticketIDList[] = $_SWIFT->Database->Record['ticketid'];
                    }
                }
            }

            return $_ticketIDList;
        }

        return $_resultIDList;
    }
}
