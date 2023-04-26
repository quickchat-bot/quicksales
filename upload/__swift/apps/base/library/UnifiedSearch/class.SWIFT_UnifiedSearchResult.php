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

namespace Base\Library\UnifiedSearch;

use SWIFT_Exception;
use SWIFT_Library;

/**
 * The Unified Search Result Library
 *
 * @author Varun Shoor
 */
class SWIFT_UnifiedSearchResult extends SWIFT_Library
{
    protected $_title = '';
    protected $_titleIcon = '';
    protected $_results = array(); // array(title, link, icon, rowclass)

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_title
     * @param string $_titleIcon
     * @param array $_results
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Object Creation Fails
     */
    public function __construct($_title, $_titleIcon, $_results)
    {
        parent::__construct();

        if (!$this->SetTitle($_title) || !$this->SetTitleIcon($_titleIcon) || !$this->SetResults($_results)) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }
    }

    /**
     * Set the Title
     *
     * @author Varun Shoor
     * @param string $_title
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetTitle($_title)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_title)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_title = $_title;

        return true;
    }

    /**
     * Retrieve the title
     *
     * @author Varun Shoor
     * @return string The Search Category Title
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTitle()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_title;
    }

    /**
     * Set the Title Icon
     *
     * @author Varun Shoor
     * @param string $_titleIcon
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetTitleIcon($_titleIcon)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_titleIcon = $_titleIcon;

        return true;
    }

    /**
     * Retrieve the title icon
     *
     * @author Varun Shoor
     * @return string The Title Icon
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTitleIcon()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_titleIcon;
    }

    /**
     * Set the Results
     *
     * @author Varun Shoor
     * @param array $_results
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetResults($_results)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_results = $_results;

        return true;
    }

    /**
     * Retrieve the results
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetResults()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_results;
    }

    /**
     * Retrieve the Result Count
     *
     * @author Varun Shoor
     * @return int The Result Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetResultCount()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return count($this->_results);
    }

}

?>
