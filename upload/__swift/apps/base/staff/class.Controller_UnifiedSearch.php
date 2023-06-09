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
use Base\Library\UnifiedSearch\SWIFT_UnifiedSearch;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Interface;

/**
 * The Base Unified Search Controller
 *
 * @property SWIFT_UnifiedSearch $UnifiedSearch
 * @author Varun Shoor
 */
class Controller_UnifiedSearch extends Controller_staff
{
    /**
     * Retrieve the JSON Values
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RetrieveJSON()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_searchQuery = '';
        if (isset($_POST['_textContents'])) {
            $_searchQuery = $_POST['_textContents'];
        }

        $this->Load->Library('UnifiedSearch:UnifiedSearch', [], true, false, 'base');
        $_searchResults = $this->UnifiedSearch->Search($_searchQuery, SWIFT_Interface::INTERFACE_STAFF, $_SWIFT->Staff);
        $_finalSearchResults = array('data' => array());
        foreach ($_searchResults as $_title => $_SWIFT_UserInterfaceSearchResultObject) {
            $_finalSearchResults['data'][$_title] = $_SWIFT_UserInterfaceSearchResultObject->GetResults();
        }

        echo json_encode($_finalSearchResults);

        return true;
    }
}
