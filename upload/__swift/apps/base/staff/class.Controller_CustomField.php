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

namespace Base\Staff;

use Base\Admin\Controller_Staff;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use SWIFT_Exception;

/**
 * The Custom Field Controller
 *
 * @author Varun Shoor
 * @property SWIFT_CustomFieldManager $CustomFieldManager
 */
class Controller_CustomField extends Controller_staff
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');
    }

    /**
     * Dispatch the File
     *
     * @author Varun Shoor
     * @param int $_customFieldID The Custom Field ID
     * @param string $_uniqueHash The Unique Hash
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Dispatch($_customFieldID, $_uniqueHash)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->CustomFieldManager->DispatchFile($_customFieldID, $_uniqueHash);

        return true;
    }
}

?>
