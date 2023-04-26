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

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: AttachmentChunk
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_AttachmentChunk extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'AttachmentChunk');

        if (!$this->TableExists(TABLE_PREFIX . 'attachmentchunks')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
        SWIFT_Loader::LoadModel('Ticket:TicketPost', APP_TICKETS);
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

        $_count = 0;

        $_oldAttachmentIDList = $_attachmentChunksContainer = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "attachmentchunks ORDER BY attachmentid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_attachmentChunksContainer[$this->DatabaseImport->Record['chunkid']] = $this->DatabaseImport->Record;

            $_oldAttachmentIDList[] = $this->DatabaseImport->Record['attachmentid'];
        }

        $_newAttachmentIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('attachment', $_oldAttachmentIDList);

        foreach ($_attachmentChunksContainer as $_attachmentChunkID => $_attachmentChunk) {
            $_newAttachmentID = 0;
            if (isset($_newAttachmentIDList[$_attachmentChunk['attachmentid']])) {
                $_newAttachmentID = $_newAttachmentIDList[$_attachmentChunk['attachmentid']];
            }

            if (empty($_newAttachmentID)) {
                $this->GetImportManager()->AddToLog('Ignoring Ticket Attachment Chunk as it is linked to a non existant attachment: ' . htmlspecialchars($_attachmentChunk['chunkid']) . '. (Probable Explanation: Incomplete Old Deletion)', SWIFT_ImportManager::LOG_SUCCESS);

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing Attachment Chunk ID: ' . htmlspecialchars($_attachmentChunk['chunkid']), SWIFT_ImportManager::LOG_SUCCESS);

            $_newAttachmentChunks = str_split($_attachmentChunk['contents'], 200);

            foreach ($_newAttachmentChunks as $_chunkContents) {
                $_notBase64 = false;
                $_finalChunkContents = '';
                if (strtolower(DB_TYPE) == 'mysql' || strtolower(DB_TYPE) == 'mysqli') {
                    $_finalChunkContents = $_chunkContents;
                    $_notBase64 = true;
                } else {
                    $_finalChunkContents = base64_encode($_chunkContents);
                }

                $this->Database->AutoExecute(TABLE_PREFIX . 'attachmentchunks',
                    array('attachmentid' => $_newAttachmentID, 'contents' => $_finalChunkContents, 'notbase64' => (int)($_notBase64)
                    ), 'INSERT');
            }
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "attachmentchunks");
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

        return 15;
    }
}

?>
