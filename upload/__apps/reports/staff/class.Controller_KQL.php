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

use Base\Admin\Controller_Staff;
use Base\Library\KQL\SWIFT_KQLAutoComplete;

/**
 * The KQL Controller
 *
 * @property SWIFT_KQLAutoComplete $KQLAutoComplete
 * @author Varun Shoor
 */
class Controller_KQL extends Controller_staff
{
    /**
     * Fetch the KQL JSON
     *
     * @author Varun Shoor
     * @param string $_baseTableName
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function FetchKQLJSON($_baseTableName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * BUG FIX - Anjali Sharma
         *
         * SWIFT-3745: Undefined index error for _caretStartingPosition
         */
        $this->Load->Library('KQL:KQLAutoComplete', array(isset($_POST['_textareaContents']) ? $_POST['_textareaContents'] : '', '',
                                                          $_baseTableName, array($_baseTableName), isset($_POST['_caretStartingPosition']) ? $_POST['_caretStartingPosition'] : '',
                                                          isset($_POST['_caretEndingPosition']) ? $_POST['_caretEndingPosition'] : ''    , isset($_POST['_textareaSelection']) ? $_POST['_textareaSelection'] : ''), true, false, 'base');

        ob_start();
        $_results = $this->KQLAutoComplete->RetrieveOptionsJSON();
        ob_end_clean();

        echo $_results;

        return true;
    }
}
?>
