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

namespace Base\Library\Import\Kayako3;

use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: QueueSignature
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_QueueSignature extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'QueueSignature');

        if (!$this->TableExists(TABLE_PREFIX . 'queuesignatures')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('EmailQueue:EmailQueue', APP_PARSER);
        SWIFT_Loader::LoadModel('EmailQueue:EmailQueueMailbox', APP_PARSER);
        SWIFT_Loader::LoadModel('EmailQueue:EmailQueuePipe', APP_PARSER);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "queuesignatures");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "queuesignatures ORDER BY queuesignatureid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;
            $_newEmailQueueID = $this->ImportManager->GetImportRegistry()->GetKey('emailqueue', $this->DatabaseImport->Record['emailqueueid']);
            if ($_newEmailQueueID == false) {
                $this->GetImportManager()->AddToLog('Queue Signature import failed for "' . htmlspecialchars($this->DatabaseImport->Record['queuesignatureid']) . '" due to non existant email queue id (incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing Queue Signature for Email Queue: ' . htmlspecialchars($_newEmailQueueID), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'queuesignatures',
                array('dateline' => $this->DatabaseImport->Record['dateline'], 'emailqueueid' => $_newEmailQueueID,
                    'contents' => $this->DatabaseImport->Record['contents']), 'INSERT');
            $_queueSignatureID = $this->Database->InsertID();
        }

        SWIFT_EmailQueue::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "queuesignatures");
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
