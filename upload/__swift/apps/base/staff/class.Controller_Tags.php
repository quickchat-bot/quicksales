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
use SWIFT_Exception;

/**
 * The Tags Controller
 *
 * @author Varun Shoor
 */
class Controller_Tags extends Controller_staff
{
    /**
     * Searches using Auto Complete
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function QuickSearch()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!isset($_POST['q']) || empty($_POST['q'])) {
            return false;
        }

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "tags WHERE (" . BuildSQLSearch('tagname', $_POST['q']) . ")", 10);
        while ($this->Database->NextRecord()) {
            echo str_replace('|', '', mb_strtolower(CleanTag($this->Database->Record['tagname']))) . '|' . mb_strtolower(CleanTag($this->Database->Record['tagname'])) . SWIFT_CRLF;
        }

        return true;
    }
}

?>
