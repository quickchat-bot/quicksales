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

use Base\Models\User\SWIFT_UserGroup;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: NewsSubscriber
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_NewsSubscriber extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'NewsSubscriber');

        if (!$this->TableExists(TABLE_PREFIX . 'newssubscribers')) {
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "newssubscribers");
        }

        // Retrieve Master Template Group ID
        $_masterTemplateGroupID = false;
        $_masterTemplateGroupContainer = $this->Database->QueryFetch("SELECT tgroupid FROM " . TABLE_PREFIX . "templategroups WHERE ismaster = '1'");
        if (isset($_masterTemplateGroupContainer['tgroupid']) && !empty($_masterTemplateGroupContainer['tgroupid'])) {
            $_masterTemplateGroupID = (int)($_masterTemplateGroupContainer['tgroupid']);
        } else {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Retrieve Default User Group ID (GUEST)
        $_masterGuestUserGroupID = false;
        $_guestUserGroupContainer = $this->Database->QueryFetch("SELECT usergroupid FROM " . TABLE_PREFIX . "usergroups
            WHERE grouptype = '" . SWIFT_UserGroup::TYPE_GUEST . "' ORDER BY usergroupid ASC");
        if (isset($_guestUserGroupContainer['usergroupid']) && !empty($_guestUserGroupContainer['usergroupid'])) {
            $_masterGuestUserGroupID = (int)($_guestUserGroupContainer['usergroupid']);
        } else {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "newssubscribers ORDER BY newssubscriberid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_templateGroupID = $this->ImportManager->GetImportRegistry()->GetKey('templategroup', $this->DatabaseImport->Record['tgroupid']);
            if ($_templateGroupID == false) {
                $_templateGroupID = $_masterTemplateGroupID;
            }

            if ($this->DatabaseImport->Record['validated'] != '1') {
                $this->GetImportManager()->AddToLog('Ignoring Unvalidated Subscriber: ' . htmlspecialchars($this->DatabaseImport->Record['email']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing Subscriber: ' . htmlspecialchars($this->DatabaseImport->Record['email']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'newssubscribers',
                array('tgroupid' => $_templateGroupID, 'userid' => '0', 'email' => $this->DatabaseImport->Record['email'],
                    'dateline' => $this->DatabaseImport->Record['dateline'], 'isvalidated' => '1', 'usergroupid' => $_masterGuestUserGroupID), 'INSERT');
            $_newsSubscriberID = $this->Database->InsertID();
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "newssubscribers");
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

        return 500;
    }
}

?>
