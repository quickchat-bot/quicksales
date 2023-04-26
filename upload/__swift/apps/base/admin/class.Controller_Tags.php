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

namespace Base\Admin;

use Controller_admin;
use SWIFT_Exception;

/**
 * The Tags Controller
 *
 * @author Varun Shoor
 */
class Controller_Tags extends Controller_admin
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

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
        }

        if (!isset($_POST['q']) || empty($_POST['q'])) {
            return false;
        }

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "tags WHERE (" . BuildSQLSearch('tagname', $_POST['q']) . ")", 10);
        while ($this->Database->NextRecord()) {
            echo str_replace('|', '', mb_strtolower(CleanTag($this->Database->Record['tagname']))) . '|' .
                mb_strtolower(CleanTag($this->Database->Record['tagname'])) . SWIFT_CRLF;
        }

        return true;
    }
}

?>
