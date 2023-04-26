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

namespace Base\Library\KQL;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * The KQL Parser Result Class
 *
 * @author Varun Shoor
 */
class SWIFT_KQLParserResult extends SWIFT_Library
{
    protected $_resultType = false;
    protected $_kqlStatementList = array();
    protected $_extraStatementList = array();
    protected $_groupByFields = array();
    protected $_groupByXFields = array();
    protected $_multiGroupByFields = array();
    protected $_distinctValueContainer = array();

    // Core Constants
    const RESULTTYPE_TABULAR = 1;
    const RESULTTYPE_GROUPEDTABULAR = 2;
    const RESULTTYPE_SUMMARY = 3;
    const RESULTTYPE_MATRIX = 4;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param mixed $_resultType
     * @param array $_kqlStatementList
     * @param array $_extraStatementList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function __construct($_resultType, $_kqlStatementList, $_extraStatementList)
    {
        parent::__construct();

        if (!self::IsValidResultType($_resultType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_resultType = $_resultType;
        $this->_kqlStatementList = $_kqlStatementList;
        $this->_extraStatementList = $_extraStatementList;
    }

    /**
     * Check to see if its a valid result type
     *
     * @author Varun Shoor
     * @param mixed $_resultType
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidResultType($_resultType)
    {
        return ($_resultType == self::RESULTTYPE_TABULAR || $_resultType == self::RESULTTYPE_GROUPEDTABULAR || $_resultType == self::RESULTTYPE_SUMMARY || $_resultType == self::RESULTTYPE_MATRIX);
    }

    /**
     * Retrieve the result type
     *
     * @author Varun Shoor
     * @return mixed The Result Type
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetResultType()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_resultType;
    }

    /**
     * Return the SQL Statement List
     *
     * @author Varun Shoor
     * @return array SQL Statement List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_kqlStatementList;
    }

    /**
     * Return Extra SQL Statement List
     *
     * @author Andriy Lesyuk
     * @return array SQL Statement List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetExtraSQL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_extraStatementList;
    }

    /**
     * Set the Group By (Y) Fields
     *
     * @author Varun Shoor
     * @param array $_fieldData
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetGroupByFields($_fieldData)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_groupByFields = $_fieldData;

        return true;
    }

    /**
     * Retrieve the Group By (Y) Fields
     *
     * @author Varun Shoor
     * @return array The Field Data
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetGroupByFields()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_groupByFields;
    }

    /**
     * Set the Group By (X) Fields
     *
     * @author Varun Shoor
     * @param array $_fieldData
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetGroupByXFields($_fieldData)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_groupByXFields = $_fieldData;

        return true;
    }

    /**
     * Retrieve the Group By (X) Fields
     *
     * @author Varun Shoor
     * @return array The Field Data
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetGroupByXFields()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_groupByXFields;
    }

    /**
     * Set the MultiGroup By Fields
     *
     * @author Varun Shoor
     * @param array $_fieldData
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetMultiGroupByFields($_fieldData)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_multiGroupByFields = $_fieldData;

        return true;
    }

    /**
     * Retrieve the MultiGroup By Fields
     *
     * @author Varun Shoor
     * @return array The Field Data
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetMultiGroupByFields()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_multiGroupByFields;
    }

    /**
     * Set the distinct values in the system
     *
     * @author Varun Shoor
     * @param array $_distinctValueContainer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetDistinctValues($_distinctValueContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_distinctValueContainer = $_distinctValueContainer;

        return true;
    }

    /**
     * Retrieve the distinct values
     *
     * @author Varun Shoor
     * @return array The Distinct Values
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDistinctValues()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_distinctValueContainer;
    }

    /**
     * Loads the Tabular Result Type
     *
     * @author Varun Shoor
     * @param array $_kqlStatementList
     * @param array $_extraStatementList
     * @return SWIFT_KQLParserResult
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function LoadTabular($_kqlStatementList, $_extraStatementList)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT_KQLParserResultObject = new SWIFT_KQLParserResult(SWIFT_KQLParserResult::RESULTTYPE_TABULAR, $_kqlStatementList, $_extraStatementList);

        return $_SWIFT_KQLParserResultObject;
    }

    /**
     * Loads the Grouped Tabular Result Type
     *
     * @author Varun Shoor
     * @param array $_kqlStatementList
     * @param array $_extraStatementList
     * @param array $_multiGroupByFields
     * @return SWIFT_KQLParserResult
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function LoadGroupedTabular($_kqlStatementList, $_extraStatementList, $_multiGroupByFields)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_multiGroupByFields)) {
            throw new SWIFT_Exception('Invalid Multi Group By Field Data');
        }

        $_SWIFT_KQLParserResultObject = new SWIFT_KQLParserResult(SWIFT_KQLParserResult::RESULTTYPE_GROUPEDTABULAR, $_kqlStatementList, $_extraStatementList);

        $_SWIFT_KQLParserResultObject->SetMultiGroupByFields($_multiGroupByFields);

        return $_SWIFT_KQLParserResultObject;
    }

    /**
     * Loads the Summary Result Type
     *
     * @author Varun Shoor
     * @param array $_kqlStatementList
     * @param array $_extraStatementList
     * @param array $_groupByFields
     * @return SWIFT_KQLParserResult
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function LoadSummary($_kqlStatementList, $_extraStatementList, $_groupByFields)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_groupByFields)) {
            throw new SWIFT_Exception('Invalid Group By Field Data');
        }

        $_SWIFT_KQLParserResultObject = new SWIFT_KQLParserResult(SWIFT_KQLParserResult::RESULTTYPE_SUMMARY, $_kqlStatementList, $_extraStatementList);

        $_SWIFT_KQLParserResultObject->SetGroupByFields($_groupByFields);

        return $_SWIFT_KQLParserResultObject;
    }

    /**
     * Loads the Matrix Result Type
     *
     * @author Varun Shoor
     * @param array $_kqlStatementList
     * @param array $_extraStatementList
     * @param array $_groupByFields
     * @param array $_groupByXFields
     * @param array $_distinctValueContainer
     * @return SWIFT_KQLParserResult
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function LoadMatrix($_kqlStatementList, $_extraStatementList, $_groupByFields, $_groupByXFields, $_distinctValueContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_groupByFields)) {
            throw new SWIFT_Exception('Invalid Group By (Y) Field Data');
        } elseif (!_is_array($_groupByXFields)) {
            throw new SWIFT_Exception('Invalid Group By (X) Field Data');
        }

        $_SWIFT_KQLParserResultObject = new SWIFT_KQLParserResult(SWIFT_KQLParserResult::RESULTTYPE_MATRIX, $_kqlStatementList, $_extraStatementList);

        $_SWIFT_KQLParserResultObject->SetGroupByFields($_groupByFields);
        $_SWIFT_KQLParserResultObject->SetGroupByXFields($_groupByXFields);
        $_SWIFT_KQLParserResultObject->SetDistinctValues($_distinctValueContainer);

        return $_SWIFT_KQLParserResultObject;
    }
}

?>
