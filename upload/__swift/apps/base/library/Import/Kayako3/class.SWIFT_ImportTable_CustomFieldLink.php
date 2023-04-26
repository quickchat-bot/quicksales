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

namespace Base\Library\Import\QuickSupport3;

use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: CustomFieldLink
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_CustomFieldLink extends SWIFT_ImportTable
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_ImportManager $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'CustomFieldLink');

        if (!$this->TableExists(TABLE_PREFIX . 'customfieldlinks')) {
            $this->SetByPass(true);
        }
    }

    /**
     * Import the data based on offset in the table
     *
     * @author Varun Shoor
     * @return int The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetOffset() == 0) {
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "customfieldlinks");
        }

        $_count = 0;

        $_customFieldLinkContainer = $_oldUserIDList = $_oldTicketIDList = $_oldTicketTimeTrackIDList = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "customfieldlinks ORDER BY customfieldlinkid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_customFieldGroupID = $this->GetImportManager()->GetImportRegistry()->GetKey('customfieldgroup', $this->DatabaseImport->Record['customfieldgroupid']);
            if ($_customFieldGroupID == false) {
                $this->GetImportManager()->AddToLog('Ignoring Custom Field Link due to non existant custom field group (' . (int)($this->DatabaseImport->Record['customfieldgroupid']) . ')', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_groupType = false;

            // User Registration
            if ($this->DatabaseImport->Record['linktype'] == '1') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_USER;

                $_oldUserIDList[] = $this->DatabaseImport->Record['typeid'];

                // User Groups
            } elseif ($this->DatabaseImport->Record['linktype'] == '2') {
                $this->GetImportManager()->AddToLog('Ignoring Custom Field Link as user group type is no longer supported: ' . htmlspecialchars($this->DatabaseImport->Record['typeid']), SWIFT_ImportManager::LOG_WARNING);

                continue;
//                $_groupType = SWIFT_CustomFieldGroup::GROUP_USERORGANIZATION;

                // Staff Ticket Creation
            } elseif ($this->DatabaseImport->Record['linktype'] == '3') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_STAFFTICKET;

                $_oldTicketIDList[] = $this->DatabaseImport->Record['typeid'];

                // User Ticket Creation
            } elseif ($this->DatabaseImport->Record['linktype'] == '4') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_USERTICKET;

                $_oldTicketIDList[] = $this->DatabaseImport->Record['typeid'];

                // Staff & User Ticket Creation
            } elseif ($this->DatabaseImport->Record['linktype'] == '9') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET;

                $_oldTicketIDList[] = $this->DatabaseImport->Record['typeid'];

                // Ticket Time Tracking
            } elseif ($this->DatabaseImport->Record['linktype'] == '5') {
                $_groupType = SWIFT_CustomFieldGroup::GROUP_TIMETRACK;

                $_oldTicketTimeTrackIDList[] = $this->DatabaseImport->Record['typeid'];

            } else {
                $this->GetImportManager()->AddToLog('Ignoring Custom Field Link due to unsupported group type: ' . htmlspecialchars($this->DatabaseImport->Record['typeid']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_customFieldLinkContainer[$this->DatabaseImport->Record['customfieldlinkid']] = $this->DatabaseImport->Record;
            $_customFieldLinkContainer[$this->DatabaseImport->Record['customfieldlinkid']]['grouptype'] = $_groupType;
            $_customFieldLinkContainer[$this->DatabaseImport->Record['customfieldlinkid']]['customfieldgroupid'] = $_customFieldGroupID;
        }

        $_newUserIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('user', $_oldUserIDList);
        $_newTicketTimeTrackIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('timetrack', $_oldTicketTimeTrackIDList);

        foreach ($_customFieldLinkContainer as $_customFieldLink) {
            $_linkTypeID = false;

            if ($_customFieldLink['grouptype'] == SWIFT_CustomFieldGroup::GROUP_USER && isset($_newUserIDList[$_customFieldLink['typeid']])) {
                $_linkTypeID = $_newUserIDList[$_customFieldLink['typeid']];
            } elseif ($_customFieldLink['grouptype'] == SWIFT_CustomFieldGroup::GROUP_STAFFTICKET || $_customFieldLink['grouptype'] == SWIFT_CustomFieldGroup::GROUP_USERTICKET || $_customFieldLink['grouptype'] == SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET) {
                $_linkTypeID = $_customFieldLink['typeid'];
            } elseif ($_customFieldLink['grouptype'] == SWIFT_CustomFieldGroup::GROUP_TIMETRACK && isset($_newTicketTimeTrackIDList[$_customFieldLink['typeid']])) {
                $_linkTypeID = $_newTicketTimeTrackIDList[$_customFieldLink['typeid']];
            }

            if (empty($_linkTypeID)) {
                $this->GetImportManager()->AddToLog('Ignoring Custom Field Link due to Invalid Link Type ID: ' . htmlspecialchars($this->DatabaseImport->Record['typeid']) . ' (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing Custom Field Link ' . htmlspecialchars($_customFieldLink['customfieldgroupid']) . ' (Custom Field Group) <> ' . htmlspecialchars($_customFieldLink['typeid']) . ' (Link Type ID)', SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'customfieldlinks',
                array('grouptype' => (int)($_customFieldLink['grouptype']), 'linktypeid' => $_linkTypeID, 'customfieldgroupid' => (int)($_customFieldLink['customfieldgroupid'])), 'INSERT');
        }

        return $_count;
    }

    /**
     * Retrieve the total number of records in a table
     *
     * @author Varun Shoor
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTotal()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "customfieldlinks");
        if (isset($_countContainer['totalitems'])) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Retrieve the number of items to process in a pass
     *
     * @author Varun Shoor
     * @return int The Number of Items
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetItemsPerPass()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return 1000;
    }
}

?>
