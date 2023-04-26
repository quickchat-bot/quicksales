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

namespace Base\Console;

use Base\Models\Attachment\SWIFT_Attachment;
use Controller_console;
use SWIFT_Exception;

/**
 * The Attachments Controller
 *
 * @author Varun Shoor
 */
class Controller_Attachments extends Controller_console
{
    // Core Constants
    const MOVE_LIMIT = 1000000;

    /**
     * Renames all attachments
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Rename()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_attachmentContainer = array();

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE attachmenttype = '" . SWIFT_Attachment::TYPE_FILE . "' ORDER BY attachmentid ASC", self::MOVE_LIMIT);
        while ($this->Database->NextRecord()) {
            $_attachmentContainer[$this->Database->Record['attachmentid']] = $this->Database->Record;
        }

        foreach ($_attachmentContainer as $_attachmentID => $_attachment) {
            if (!file_exists('./__swift/files/' . $_attachment['storefilename'])) {
                $this->Console->WriteLine($this->Console->Red('Ignoring Attachment ID: ' . $_attachmentID . ' and file: ' . $_attachment['storefilename'] . ' as the file does not exist'));

                continue;
            }

            $_newFilename = SWIFT_Attachment::DEFAULT_PREFIX . BuildHash();

            rename('./__swift/files/' . $_attachment['storefilename'], './__swift/files/' . $_newFilename);

            $this->Database->AutoExecute(TABLE_PREFIX . 'attachments', array('storefilename' => $_newFilename), 'UPDATE', "attachmentid = '" . (int)($_attachmentID) . "'");

            $this->Console->WriteLine($this->Console->Green('Renaming Attachment ID: ' . $_attachmentID . ' and file: ' . $_attachment['storefilename'] . ' to: ' . $_newFilename));
        }

        $this->Console->WriteLine($this->Console->Yellow('Renaming process completed'));

        return true;
    }
}

?>
