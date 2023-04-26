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

/**
 * Import Table: CommentData
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_CommentData extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'CommentData');

        if (!$this->TableExists(TABLE_PREFIX . 'commentdata')) {
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "commentdata");
        }

        $_count = 0;

        $_oldCommentIDList = $_commentDataContainer = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "commentdata ORDER BY commentid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_commentDataContainer[$this->DatabaseImport->Record['commentid']] = $this->DatabaseImport->Record;

            $_oldCommentIDList[] = $this->DatabaseImport->Record['commentid'];
        }

        $_newCommentIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('comment', $_oldCommentIDList);

        foreach ($_commentDataContainer as $_commentData) {
            $_count++;

            if (!isset($_newCommentIDList[$_commentData['commentid']])) {
                $this->GetImportManager()->AddToLog('Failed to Import Comment Data due to non existent Comment: ' . htmlspecialchars($_commentData['commentid']) . ' (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_commentID = (int)($_newCommentIDList[$_commentData['commentid']]);

            $this->Database->AutoExecute(TABLE_PREFIX . 'commentdata',
                array('commentid' => $_commentID, 'contents' => $_commentData['contents']), 'INSERT');
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "commentdata");
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
