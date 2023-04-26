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

namespace Base\Models\Template;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Library\Template\SWIFT_Template_Exception;
use Base\Library\Template\SWIFT_TemplateDiff;

/**
 * The Template Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Template extends SWIFT_Model
{
    const TABLE_NAME = 'templates';
    const PRIMARY_KEY = 'templateid';

    const TABLE_STRUCTURE = "templateid I PRIMARY AUTO NOTNULL,
                                tgroupid I DEFAULT '0' NOTNULL,
                                tcategoryid I DEFAULT '0' NOTNULL,
                                templateversion C(20) DEFAULT '1.00.00' NOTNULL,
                                name C(60) DEFAULT '' NOTNULL,
                                templatelength I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                modified C(50) DEFAULT 'notmodified' NOTNULL,
                                contentshash C(32) DEFAULT '' NOTNULL,
                                iscustom I2 DEFAULT '0' NOTNULL,
                                contentsdefaulthash C(32) DEFAULT '' NOTNULL";

    const INDEX_1 = 'tgroupid, name';
    const INDEX_2 = 'tcategoryid';


    protected $_dataStore = array();
    private $_templateContents = '';
    private $_templateContentsDefault = '';

    // Core Constants
    const TYPE_NOTMODIFIED = 'notmodified';
    const TYPE_MODIFIED = 'modified';
    const TYPE_UPGRADE = 'upgrade';

    const DEFAULT_VERSION = '1.00.00';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_templateID The Template ID
     * @param bool $_doLoadContents Whether to Load Contents during Init
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Record could not be loaded
     */
    public function __construct($_templateID, $_doLoadContents = false)
    {
        parent::__construct();

        if (!$this->LoadData($_templateID, $_doLoadContents)) {
            throw new SWIFT_Template_Exception('Failed to load Template ID: ' . $_templateID);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool())) {
            return false;
        } else if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'templates', $this->GetUpdatePool(), 'UPDATE', "templateid = '" . (int)($this->GetTemplateID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Template ID
     *
     * @author Varun Shoor
     * @return mixed "templateid" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetTemplateID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['templateid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_templateID The Template ID
     * @param bool $_doLoadContents Whether to Load Contents during Init
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_templateID, $_doLoadContents = false)
    {
        if ($_doLoadContents) {
            $_dataStore = $this->Database->QueryFetch("SELECT templates.*, templatedata.contents AS contents, templatedata.contentsdefault AS contentsdefault FROM " . TABLE_PREFIX . "templates AS templates LEFT JOIN " . TABLE_PREFIX . "templatedata AS templatedata ON (templates.templateid = templatedata.templateid)WHERE templates.templateid = '" . $_templateID . "'");
        } else {
            $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "templates WHERE templateid = '" . $_templateID . "'");
        }

        if (isset($_dataStore['templateid']) && !empty($_dataStore['templateid'])) {
            $this->_dataStore = $_dataStore;

            return true;
        }

        return false;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded() || !isset($this->_dataStore[$_key])) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Template
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @param int $_templateCategoryID The Template Category ID
     * @param string $_templateName The Template Name
     * @param string $_contents The Template Contents
     * @param int|bool $_isCustom Whether Template is Custom or Not
     * @return mixed "_templateID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If Invalid Data is Provided or If the Object could not be created
     * @throws SWIFT_Exception
     */
    public static function Create($_templateGroupID, $_templateCategoryID, $_templateName, $_contents, $_contentsDefault = false, $_isCustom = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_templateGroupID) || empty($_templateCategoryID) || empty($_templateName) || empty($_contents)) {
            throw new SWIFT_Template_Exception(SWIFT_INVALIDDATA);
        }

        if (!$_contentsDefault) {
            $_contentsDefault = $_contents;
        }

        $_contents = trim($_contents);
        $_contentsDefault = trim($_contentsDefault);

        $_templateLength = mb_strlen($_contents);
        $_contentsHash = md5($_contents);
        $_contentsDefaultHash = md5($_contentsDefault);

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1135 'Modifier' indicator is missing from the templates which have been modified in the original template group.
         *
         * Comments: None
         */
        $_modifiedType = self::TYPE_NOTMODIFIED;
        if ($_contentsHash != $_contentsDefaultHash) {
            $_modifiedType = self::TYPE_MODIFIED;
        }

        $_queryResult = $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'templates', array('tgroupid' => $_templateGroupID, 'tcategoryid' => $_templateCategoryID,
            'name' => $_templateName, 'templatelength' => $_templateLength, 'dateline' => DATENOW, 'modified' => $_modifiedType, 'contentshash' => $_contentsHash,
            'contentsdefaulthash' => $_contentsDefaultHash, 'templateversion' => self::DEFAULT_VERSION, 'iscustom' => $_isCustom), 'INSERT');
        if (!$_queryResult) {
            throw new SWIFT_Template_Exception(SWIFT_CREATEFAILED);
        }

        $_templateID = $_SWIFT->Database->Insert_ID();
        if (!$_templateID) {
            throw new SWIFT_Template_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'templatedata', array('templateid' => $_templateID, 'contents' => ReturnNone($_contents),
            'contentsdefault' => ReturnNone($_contentsDefault)), 'INSERT');

        return $_templateID;
    }

    /**
     * Update the Template Record
     *
     * @author Varun Shoor
     * @param string $_contents The New Template Contents
     * @param bool $_updateHistory (OPTIONAL) Whether to Update the History
     * @param int $_staffID (OPTIONAL) The Staff ID of the Staff Making the Change
     * @param string $_changeLogNotes (OPTIONAL) The Change Log Notes for this Update
     * @param bool $_isUpgrade (OPTIONAL) Whether its being executed from Upgrade
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_contents, $_updateHistory = true, $_staffID = 0, $_changeLogNotes = '', $_isUpgrade = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_contents = trim($_contents);

        // Move the data into template history
        if ($_updateHistory && md5($_contents) != md5($this->GetContents())) {
            $_templateHistoryID = SWIFT_TemplateHistory::Create($this, $_contents, $_changeLogNotes, $_staffID);

            $_newVersion = SWIFT_TemplateDiff::GetVersion($this->GetVersion(), $this->GetContents(), $_contents);
        } else {
            $_newVersion = $this->GetVersion();
        }


        if ($_isUpgrade) {
            $this->Database->AutoExecute(TABLE_PREFIX . 'templatedata', array('contentsdefault' => ReturnNone($_contents)), 'UPDATE', "templateid = '" . (int)($this->GetTemplateID()) . "'");
            $this->UpdatePool('contentsdefaulthash', md5($_contents));
        } else {
            $this->Database->AutoExecute(TABLE_PREFIX . 'templatedata', array('contents' => ReturnNone($_contents)), 'UPDATE', "templateid = '" . (int)($this->GetTemplateID()) . "'");
            $this->UpdatePool('contentshash', md5($_contents));
        }

        $this->UpdatePool('dateline', DATENOW);
        $this->UpdatePool('modified', IIF($_isUpgrade, self::TYPE_UPGRADE, self::TYPE_MODIFIED));
        $this->UpdatePool('templatelength', strlen($_contents));
        $this->UpdatePool('templateversion', $_newVersion);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Restore the Template
     *
     * @author Varun Shoor
     * @param bool $_updateHistory (OPTIONAL) Whether to Update the History
     * @param int $_staffID (OPTIONAL) The Staff ID of the Staff Making the Change
     * @param string $_changeLogNotes (OPTIONAL) The Change Log Notes for this Update
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function Restore($_updateHistory = true, $_staffID = 0, $_changeLogNotes = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Update($this->GetDefaultContents(), $_updateHistory, $_staffID, $_changeLogNotes);

        $this->UpdatePool('modified', self::TYPE_NOTMODIFIED);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Restore a list of Templates
     *
     * @author Varun Shoor
     * @param array $_templateIDList The Template ID List
     * @param bool $_updateHistory Whether to Update the History
     * @param int $_staffID (OPTIONAL) The Staff ID of the Staff Making the Change
     * @param string $_changeLogNotes (OPTIONAL) The Change Log Notes for this Update
     * @return mixed "_restoredTemplateContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Template ID is Invalid
     */
    public static function RestoreList($_templateIDList, $_updateHistory = true, $_staffID = 0, $_changeLogNotes = '')
    {
        if (!_is_array($_templateIDList)) {
            return false;
        }

        $_restoredTemplateContainer = array();

        foreach ($_templateIDList as $_key => $_val) {
            $_SWIFT_TemplateObject = new SWIFT_Template($_val, true);
            if (!$_SWIFT_TemplateObject instanceof SWIFT_Template || !$_SWIFT_TemplateObject->GetIsClassLoaded()) {
                throw new SWIFT_Template_Exception(SWIFT_INVALIDDATA);
            }

            $_SWIFT_TemplateObject->Restore($_updateHistory, $_staffID, $_changeLogNotes);

            $_restoredTemplateContainer[$_SWIFT_TemplateObject->GetTemplateID()] = $_SWIFT_TemplateObject->GetDataStore();
        }

        return $_restoredTemplateContainer;
    }

    /**
     * Retrieve the Template Version
     *
     * @author Varun Shoor
     * @return string "templateversion" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetVersion()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$this->GetProperty('templateversion')) {
            return self::DEFAULT_VERSION;
        }

        return $this->GetProperty('templateversion');
    }

    /**
     * Retrieve the Template Contents
     *
     * @author Varun Shoor
     * @return mixed "_templateContents" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetContents()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($this->_templateContents) && !$this->LoadContents()) {
            throw new SWIFT_Template_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_templateContents;
    }

    /**
     * Retrieve the Default Template Contents
     *
     * @author Varun Shoor
     * @return mixed "_templateContentsDefault" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function GetDefaultContents()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($this->_templateContentsDefault) && !$this->LoadContents()) {
            throw new SWIFT_Template_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_templateContentsDefault;
    }

    /**
     * Attempt to load the Template Conetnts
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    protected function LoadContents()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_templateDataContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "templatedata WHERE templateid = '" . (int)($this->GetTemplateID()) . "'");
        if (isset($_templateDataContainer['contents']) && isset($_templateDataContainer['contentsdefault'])) {
            $this->_templateContents = $_templateDataContainer['contents'];
            $this->_templateContentsDefault = $_templateDataContainer['contentsdefault'];

            return true;
        }

        return false;
    }

    /**
     * Delete the Template record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Template_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Template_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTemplateID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Template IDs
     *
     * @author Varun Shoor
     * @param array $_templateIDList The Template ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_templateIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_templateIDList)) {
            return false;
        }

        $_finalTemplateIDList = array();

        $_SWIFT->Database->Query("SELECT templateid FROM " . TABLE_PREFIX . "templates WHERE templateid IN (" . BuildIN($_templateIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalTemplateIDList[] = $_SWIFT->Database->Record['templateid'];
        }

        if (!count($_finalTemplateIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "templates WHERE templateid IN (" . BuildIN($_finalTemplateIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "templatedata WHERE templateid IN (" . BuildIN($_finalTemplateIDList) . ")");

        SWIFT_TemplateHistory::DeleteOnTemplate($_finalTemplateIDList);

        return true;
    }

    /**
     * Delete the Templates based on Template Group IDs
     *
     * @author Varun Shoor
     * @param array $_templateGroupIDList The Template Group ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTemplateGroup($_templateGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_templateGroupIDList)) {
            return false;
        }

        $_templateIDList = array();

        $_SWIFT->Database->Query("SELECT templateid FROM " . TABLE_PREFIX . "templates WHERE tgroupid IN (" . BuildIN($_templateGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_templateIDList[] = $_SWIFT->Database->Record['templateid'];
        }

        if (!count($_templateIDList)) {
            return false;
        }

        self::DeleteList($_templateIDList);

        return true;
    }

    /**
     * Retrieve the list of template ids from the template group
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @return mixed "_templateIDList" (ARRAY) on Success, "false" otherwise
     */
    public static function GetTemplateIDListFromTemplateGroup($_templateGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_templateGroupID)) {
            return false;
        }

        $_templateIDList = array();

        $_SWIFT->Database->Query("SELECT templateid FROM " . TABLE_PREFIX . "templates WHERE tgroupid = '" . $_templateGroupID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_templateIDList[] = $_SWIFT->Database->Record['templateid'];
        }

        if (!count($_templateIDList)) {
            return false;
        }

        return $_templateIDList;
    }

    /**
     * Retrieve the list of template ids from the template category
     *
     * @author Varun Shoor
     * @param int $_templateCategoryID The Template Category ID
     * @return mixed "_templateIDList" (ARRAY) on Success, "false" otherwise
     */
    public static function GetTemplateIDListFromTemplateCategory($_templateCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_templateCategoryID)) {
            return false;
        }

        $_templateIDList = array();

        $_SWIFT->Database->Query("SELECT templateid FROM " . TABLE_PREFIX . "templates WHERE tcategoryid = '" . $_templateCategoryID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_templateIDList[] = $_SWIFT->Database->Record['templateid'];
        }

        if (!count($_templateIDList)) {
            return false;
        }

        return $_templateIDList;
    }

    /**
     * Retrieve the Template Data as an Array for the Specified Template Group
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @return mixed "_templateDataContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function GetTemplateData($_templateGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_templateGroupID)) {
            return false;
        }

        $_templateDataContainer = array();

        $_SWIFT->Database->Query("SELECT templates.*, templatedata.contents AS contents, templatedata.contentsdefault AS contentsdefault FROM " . TABLE_PREFIX . "templates AS templates LEFT JOIN " . TABLE_PREFIX . "templatedata AS templatedata ON (templates.templateid = templatedata.templateid) WHERE templates.tgroupid = '" . $_templateGroupID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_templateDataContainer[$_SWIFT->Database->Record['templateid']] = $_SWIFT->Database->Record;
        }

        if (!count($_templateDataContainer)) {
            return false;
        }

        return $_templateDataContainer;
    }

    /**
     * Get the Relevant Status text and Icon for a modified status
     *
     * @author Varun Shoor
     * @param mixed $_modifiedStatus The Modified Status
     * @return mixed array(_modifiedStatusImage, _modifiedText) on Success, "false" otherwise
     */
    public static function GetModifiedHTML($_modifiedStatus)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_modifiedStatus)) {
            return false;
        }

        $_modifiedStatusImage = $_modifiedText = '';

        if ($_modifiedStatus == self::TYPE_NOTMODIFIED) {
            $_modifiedStatusImage = 'images/icon_templatenotmodified.gif';
            $_modifiedText = $_SWIFT->Language->Get('notmodified');
        } else if ($_modifiedStatus == self::TYPE_UPGRADE) {
            $_modifiedStatusImage = 'images/icon_templateupgrade.gif';
            $_modifiedText = $_SWIFT->Language->Get('upgrade');
        } else {
            $_modifiedStatusImage = 'images/icon_templatemodified.gif';
            $_modifiedText = $_SWIFT->Language->Get('modified');
        }

        return array($_modifiedStatusImage, $_modifiedText);
    }

    /**
     * Retrieve the list of templates requiring revert due to upgrade
     *
     * @author Varun Shoor
     * @return array The Template List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetUpgradeRevertList()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_templateList = array();
        $_SWIFT->Database->Query("SELECT templates.*, templategroups.title AS templategrouptitle FROM " . TABLE_PREFIX . "templates AS templates
            LEFT JOIN " . TABLE_PREFIX . "templategroups AS templategroups ON (templates.tgroupid = templategroups.tgroupid)
            WHERE templates.modified = '" . self::TYPE_UPGRADE . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_suffix = '';
            if (isset($_SWIFT->Database->Record['templategrouptitle']) && !empty($_SWIFT->Database->Record['templategrouptitle'])) {
                $_suffix = ' (' . htmlspecialchars($_SWIFT->Database->Record['templategrouptitle']) . ')';
            }

            $_templateList[] = $_SWIFT->Database->Record['name'] . $_suffix;
        }

        return $_templateList;
    }

    /**
     * Delete a list of templates on the provided list of template categories
     *
     * @author Varun Shoor
     * @param array $_templateCategoryIDList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnTemplateCategory($_templateCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_templateCategoryIDList)) {
            return false;
        }

        $_templateIDList = array();
        $_SWIFT->Database->Query("SELECT templateid FROM " . TABLE_PREFIX . "templates
            WHERE tcategoryid IN (" . BuildIN($_templateCategoryIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_templateIDList[] = $_SWIFT->Database->Record['templateid'];
        }

        self::DeleteList($_templateIDList);

        return true;
    }
}

?>
