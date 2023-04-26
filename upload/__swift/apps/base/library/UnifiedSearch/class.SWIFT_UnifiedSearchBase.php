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

namespace Base\Library\UnifiedSearch;

use Base\Models\Staff\SWIFT_Staff;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_Library;

/**
 * The Abstract Base Class for Public Unified Search Libraries
 *
 * @author Varun Shoor
 */
abstract class SWIFT_UnifiedSearchBase extends SWIFT_Library
{
    protected $_query = '';
    protected $_interfaceType = false;
    protected $_SWIFT_StaffObject = false;
    protected $_maxResults = 5;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_query The Search Query
     * @param mixed $_interfaceType The Interface Type
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param int $_maxResults
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Object Creation Fails
     */
    public function __construct($_query, $_interfaceType, SWIFT_Staff $_SWIFT_StaffObject, $_maxResults)
    {
        parent::__construct();

        if (!$this->SetQuery($_query) || !$this->SetInterface($_interfaceType) || !$this->SetStaff($_SWIFT_StaffObject) || !$this->SetMaxResults($_maxResults)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->Load->Library('String:StringHighlighter');
        $this->Load->Library('String:StringHTMLToText');
    }

    /**
     * Run the search
     *
     * @author Varun Shoor
     * @return array|false False if there is no results, otherwise return an array with the results
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Search()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return false;
    }

    /**
     * Set the Search Query
     *
     * @author Varun Shoor
     * @param string $_query
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetQuery($_query)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_query)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_query = $_query;

        return true;
    }

    /**
     * Retrieve the Search Query
     *
     * @author Varun Shoor
     * @return string Search Query
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetQuery()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_query;
    }

    /**
     * Set the Interface
     *
     * @author Varun Shoor
     * @param mixed $_interfaceType
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetInterface($_interfaceType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!SWIFT_Interface::IsValidInterfaceType($_interfaceType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_interfaceType = $_interfaceType;

        return true;
    }

    /**
     * Retrieve the Interface
     *
     * @author Varun Shoor
     * @return mixed Interface Type
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetInterface()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_interfaceType;
    }

    /**
     * Set the Staff Object
     *
     * @author Varun Shoor
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetStaff(SWIFT_Staff $_SWIFT_StaffObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_SWIFT_StaffObject = $_SWIFT_StaffObject;

        return true;
    }

    /**
     * Retrieve the Staff Object
     *
     * @author Varun Shoor
     * @return SWIFT_Staff The SWIFT_Staff Object Pointer
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetStaff()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_SWIFT_StaffObject;
    }

    /**
     * Set the Max Results
     *
     * @author Varun Shoor
     * @param int $_maxResults
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetMaxResults($_maxResults)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_maxResults)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_maxResults = $_maxResults;

        return true;
    }

    /**
     * Retrieve the Max Results
     *
     * @author Varun Shoor
     * @return int The Max Results
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetMaxResults()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_maxResults;
    }

    /**
     * Check the haystack to see if it has the needle (split into words)
     *
     * @author Varun Shoor
     * @param string $_haystack
     * @param string $_needle
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function HasQuery($_haystack, $_needle)
    {
        $_matches = $_strictMatches = array();
        if (preg_match_all('/["|\'](.*)["|\']/iU', $_needle, $_matches)) {
            foreach ($_matches[1] as $_strictMatch) {
                if (trim($_strictMatch) == '') {
                    continue;
                }

                $_strictMatches[] = $_strictMatch;
            }

            $_needle = preg_replace('/["|\'](.*)["|\']/iU', '', $_needle);
        }

        // Split the query into words using spaces
        $_wordsContainer = explode(' ', $_needle);
        if (!count($_wordsContainer)) {
            $_wordsContainer = array($_needle);
        }

        $_wordsContainer = array_merge($_strictMatches, $_wordsContainer);

        foreach ($_wordsContainer as $_word) {
            if (trim($_word) == '') {
                continue;
            }

            if (stristr($_haystack, $_word)) {
                return true;
            }
        }

        return false;
    }
}

?>
