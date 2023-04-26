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

namespace Base\Library\Template;

use Base\Models\Department\SWIFT_Department;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Template\SWIFT_Template;
use Base\Models\Template\SWIFT_TemplateCategory;
use Base\Models\Template\SWIFT_TemplateGroup;
use Base\Models\User\SWIFT_UserGroup;
use SWIFT;
use SWIFT_App;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_FileManager;
use SWIFT_Library;
use SWIFT_TemplateEngine;

/**
 * The Template Import/Export/ImportUpdate Manager
 *
 * @property \SWIFT_XML $XML
 * @author Varun Shoor
 */
class SWIFT_TemplateManager extends SWIFT_Library
{
    // Core Constants
    const EXPORT_ALL = 1;
    const EXPORT_MODIFICATIONS = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');
    }

    /**
     * Update the Header Image with the given file
     *
     * @author Varun Shoor
     * @param mixed $_headerImageType The Header Image Type
     * @param string $_originalFileName Original File Name
     * @param string $_filePath The Path to new Header Image File
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateHeaderImage($_headerImageType, $_originalFileName, $_filePath)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!SWIFT_TemplateEngine::IsValidHeaderImageType($_headerImageType) || !file_exists($_filePath)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_settingsHeaderImage = $this->Settings->GetKey('headerimage', $_headerImageType);

        // First nuke the existing records
        if ($_settingsHeaderImage && file_exists('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $_settingsHeaderImage)) {
            $this->Settings->DeleteKey('headerimage', $_headerImageType);
            SWIFT_FileManager::DeleteOnFileNameList(array($_settingsHeaderImage));
        }

        // Now update the header image
        $_fileID = SWIFT_FileManager::Create($_filePath, $_originalFileName, false);
        $_SWIFT_FileManagerObject = new SWIFT_FileManager($_fileID);
        if (!$_SWIFT_FileManagerObject instanceof SWIFT_FileManager || !$_SWIFT_FileManagerObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $this->Settings->UpdateKey('headerimage', $_headerImageType, $_SWIFT_FileManagerObject->GetProperty('filename'));

        return true;
    }

    /**
     * Import Templates from XML and Create a New Group based on data in the file
     *
     * @author Varun Shoor
     * @param string $_filePath The File Name to Import From
     * @param bool $_doVersionCheck Whether to carry out version check or not
     * @return mixed array(array(statusText, result, reasonFailure), ...) on Success, "false" in case of generic failure, "-1" in case of Version Failure
     */
    public function ImportCreateGroup($_filePath, $_doVersionCheck = false)
    {
        if (!file_exists($_filePath) || !is_readable($_filePath)) {
            return false;
        }

        // Parse the templates XML File.
        $_templateXMLContainer = $this->XML->XMLToTree(file_get_contents($_filePath));
        if (!isset($_templateXMLContainer["swifttemplate"][0]["children"])) {
            return false;
        }

        $_finalTemplates = &$_templateXMLContainer['swifttemplate'][0]['children'];

        $_title = $_author = $_version = '';
        if (isset($_finalTemplates['title']) && isset($_finalTemplates['title'][0]['values'])) {
            $_title = $_finalTemplates['title'][0]['values'][0];
        }

        if (isset($_finalTemplates['author']) && isset($_finalTemplates['author'][0]['values'])) {
            $_author = $_finalTemplates['author'][0]['values'][0];
        }

        if (isset($_finalTemplates['version']) && isset($_finalTemplates['version'][0]['values'])) {
            $_version = $_finalTemplates['version'][0]['values'][0];
        }

        if (!empty($_version) && $_doVersionCheck == true) {
            // Check for Template Version
            if (version_compare($_version, SWIFT_VERSION) == -1) {
                return -1;
            }
        }

        if (empty($_title)) {
            return false;
        }

        // Get default group options
        $_templateGroupCheck = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "templategroups WHERE title = '" . $this->Database->Escape($_title) . "'");
        if (isset($_templateGroupCheck['title']) && !empty($_templateGroupCheck['title'])) {
            $_title .= '_' . substr(BuildHash(), 0, 5);
        }

        // Get default group options
        $_templateGroupMaster = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "templategroups WHERE ismaster = '1'");
        if (!isset($_templateGroupMaster['tgroupid'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_languageMaster = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "languages WHERE ismaster = '1'");
        if (!isset($_languageMaster['languageid'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }


        // Get guest group
        $_userGroupGuest = $this->Database->QueryFetch("SELECT usergroupid FROM " . TABLE_PREFIX . "usergroups WHERE grouptype = '" . SWIFT_UserGroup::TYPE_GUEST . "' ORDER BY usergroupid ASC", 5);
        if (!isset($_userGroupGuest['usergroupid'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_guestUserGroupID = $_userGroupGuest['usergroupid'];

        $_userGroupRegistered = $this->Database->QueryFetch("SELECT usergroupid FROM " . TABLE_PREFIX . "usergroups WHERE grouptype = '" . SWIFT_UserGroup::TYPE_REGISTERED . "' ORDER BY usergroupid ASC", 5);
        if (!isset($_userGroupRegistered['usergroupid'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_registeredUserGroupID = $_userGroupRegistered['usergroupid'];

        $_departmentID = $_ticketStatusID = $_ticketPriorityID = $_ticketTypeID = 0;
        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_departmentContainer = $this->Database->QueryFetch("SELECT departmentid FROM " . TABLE_PREFIX . "departments WHERE departmentapp = '" . APP_TICKETS . "' AND departmenttype = '" . SWIFT_Department::DEPARTMENT_PUBLIC . "' AND parentdepartmentid = '0' ORDER BY departmentid ASC", 5);
            if (!isset($_departmentContainer['departmentid'])) {
                return false;
            }

            $_departmentID = $_departmentContainer['departmentid'];

            $_ticketTypeContainer = $this->Database->QueryFetch("SELECT tickettypeid FROM " . TABLE_PREFIX . "tickettypes WHERE departmentid = '0'
                AND type = '" . SWIFT_PUBLIC . "' ORDER BY tickettypeid ASC", 5);
            if (!isset($_ticketTypeContainer['tickettypeid'])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_ticketTypeID = $_ticketTypeContainer['tickettypeid'];

            $_ticketStatusContainer = $this->Database->QueryFetch("SELECT ticketstatusid FROM " . TABLE_PREFIX . "ticketstatus WHERE departmentid = '0' AND type = '" . SWIFT_PUBLIC . "' ORDER BY ticketstatusid ASC", 5);
            if (!isset($_ticketStatusContainer['ticketstatusid'])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_ticketStatusID = $_ticketStatusContainer['ticketstatusid'];

            $_ticketPriorityContainer = $this->Database->QueryFetch("SELECT priorityid FROM " . TABLE_PREFIX . "ticketpriorities WHERE type = '" . SWIFT_PUBLIC . "' ORDER BY priorityid ASC", 5);
            if (!isset($_ticketPriorityContainer['priorityid'])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_ticketPriorityID = $_ticketPriorityContainer['priorityid'];
        }

        $_departmentID_LiveChat = 0;

        if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            $_departmentContainer = $this->Database->QueryFetch("SELECT departmentid FROM " . TABLE_PREFIX .
                "departments WHERE departmentapp = '" . APP_LIVECHAT . "' AND departmenttype = '" .
                SWIFT_Department::DEPARTMENT_PUBLIC . "' AND parentdepartmentid = '0' ORDER BY departmentid ASC", 5);
            if (!isset($_departmentContainer['departmentid'])) {
                return false;
            }

            $_departmentID_LiveChat = $_departmentContainer['departmentid'];
        }

        // First create a group with default options
        $_SWIFT_TemplateGroupObject = SWIFT_TemplateGroup::Create($_title, '', $_templateGroupMaster['companyname'], false, '', '',
            $_languageMaster['languageid'], false, false, $_guestUserGroupID, $_registeredUserGroupID, false, 0, $_departmentID,
            $_ticketStatusID, $_ticketPriorityID, $_ticketTypeID, true, true, $_departmentID_LiveChat, true, false);

        if (!$_SWIFT_TemplateGroupObject instanceof SWIFT_TemplateGroup || !$_SWIFT_TemplateGroupObject->GetIsClassLoaded()) {
            return -2;
        }

        // Now when the template group is created with default templates, we import the ones from file.
        $_importResult = $this->Import($_filePath, $_SWIFT_TemplateGroupObject->GetTemplateGroupID(), $_doVersionCheck);

        // All done, return the new tgroupid
        SWIFT_TemplateGroup::RebuildCache();

        return $_SWIFT_TemplateGroupObject;
    }

    /**
     * Import Templates from XML
     *
     * @author Varun Shoor
     * @param string $_fileName The File Name to Import From
     * @param int $_templateGroupID The Template Group ID to Import to
     * @param bool $_doVersionCheck Whether to carry out version check or not
     * @return mixed array(array(statusText, result, reasonFailure), ...) on Success, "false" in case of generic failure, "-1" in case of Version Failure
     */
    public function Import($_fileName, $_templateGroupID, $_doVersionCheck = false)
    {
        if (!file_exists($_fileName) || empty($_templateGroupID)) {
            return false;
        }

        // Parse the templates XML File.
        $_templateXMLContainer = $this->XML->XMLToTree(file_get_contents($_fileName));
        $_finalTemplates = &$_templateXMLContainer["swifttemplate"][0]["children"];

        $_title = $_author = $_version = '';
        if (isset($_finalTemplates["title"]) && isset($_finalTemplates["title"][0]["values"])) {
            $_title = $_finalTemplates["title"][0]["values"][0];
        }

        if (isset($_finalTemplates["author"]) && isset($_finalTemplates["author"][0]["values"])) {
            $_author = $_finalTemplates["author"][0]["values"][0];
        }

        if (isset($_finalTemplates["version"]) && isset($_finalTemplates["version"][0]["values"])) {
            $_version = $_finalTemplates["version"][0]["values"][0];
        }

        if (!empty($_version) && $_doVersionCheck == true) {
            // Check for Template Version
            if (version_compare($_version, SWIFT_VERSION) == -1) {
                return -1;
            }
        }

        if (empty($_title)) {
            return false;
        }

        $_appList = SWIFT_App::ListApps();
        if (!_is_array($_appList)) {
            return false;
        }

        $_statusListContainer = array();

        if (isset($_finalTemplates["category"])) {
            for ($ii = 0; $ii < count($_finalTemplates["category"]); $ii++) {
                // This is our attribute holder, contains the category name etc
                $_templateCategoryContainer = &$_finalTemplates["category"][$ii]["attrs"];

                if (isset($_templateCategoryContainer["module"])) {
                    $_appName = $_templateCategoryContainer["module"];
                } else {
                    $_appName = $_templateCategoryContainer["app"];
                }

                if (in_array($_appName, $_appList)) {
                    // Only proceed if the category app is in the allowed app list

                    // Insert category for the given template group
                    if (!isset($_templateCategoryContainer["description"])) {
                        $_templateCategoryContainer["description"] = '';
                    }

                    if (!isset($_templateCategoryContainer["icon"])) {
                        $_templateCategoryContainer["icon"] = '';
                    }

                    $_templateCategoryID = SWIFT_TemplateCategory::Create($_templateGroupID,
                        $_templateCategoryContainer["name"], $_appName, $_templateCategoryContainer["description"],
                        $_templateCategoryContainer["icon"], true);

                    $_statusListContainer[] = [
                        'statusText' => sprintf($this->Language->Get('sctemplatecategory'),
                            $_templateCategoryContainer['name']),
                        'result' => $_templateCategoryID,
                        'reasonFailure' => $this->Database->FetchLastError()
                    ];

                    // Now we iterate through individual templates for this category
                    for ($kk = 0; $kk < count($_finalTemplates["category"][$ii]["children"]["template"]); $kk++) {
                        if (!$_templateCategoryID) {
                            continue;
                        }

                        $_templateContainer = $_finalTemplates["category"][$ii]["children"]["template"][$kk]["attrs"];
                        $_templateContents = $_finalTemplates["category"][$ii]["children"]["template"][$kk]["values"][0];

                        $_templateID = SWIFT_Template::Create($_templateGroupID, $_templateCategoryID,
                            $_templateContainer['name'], $_templateContents);

                        $_statusListContainer[] = [
                            'statusText' => sprintf($this->Language->Get('sctemplate'), $_templateContainer['name']),
                            'result' => $_templateID,
                            'reasonFailure' => $this->Database->FetchLastError()
                        ];
                    }
                }
            }
        }

        return $_statusListContainer;
    }

    /**
     * Makes up the template file name using product name & version
     *
     * @author Varun Shoor
     * @param string $_templateGroupTitle The Template Group Title
     * @return mixed "The File Name" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GenerateFileName($_templateGroupTitle)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return strtolower(SWIFT_PRODUCT) . '.' . str_replace('.', '-', SWIFT_VERSION) . IIF(!empty($_groupTitle), '.' . strtolower(Clean($_templateGroupTitle))) . '.templates.xml';
    }

    /**
     * Export the Template as XML
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @param mixed $_exportOptions The Export Options
     * @param string $_fileName The File Name to Export As
     * @param bool $_exportHistory The Export History
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Export($_templateGroupID, $_exportOptions, $_fileName = '', $_exportHistory = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');
        if (!isset($_templateGroupCache[$_templateGroupID])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_templateGroup = $_templateGroupCache[$_templateGroupID];

        if (empty($_fileName)) {
            $_fileName = self::GenerateFileName($_templateGroupCache[$_templateGroupID]['title']);
        }

        $_templateCategoryContainer = $_templateCategoryIDList = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templatecategories WHERE tgroupid = '" . $_templateGroupID . "'");
        while ($this->Database->NextRecord()) {
            $_templateCategoryContainer[$this->Database->Record['tcategoryid']] = $this->Database->Record;
            $_templateCategoryIDList[] = $this->Database->Record['tcategoryid'];
        }

        if ($_exportOptions == self::EXPORT_ALL) {
            $_modifiedSQL = '';
        } else {
            $_modifiedSQL = " AND templates.modified = '" . SWIFT_Template::TYPE_MODIFIED . "'";
        }

        $_templateIDList = $_templateContainer = $_templateHistoryContainer = array();

        // ======= Fetch all the templates under the category =======
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templates AS templates LEFT JOIN " . TABLE_PREFIX . "templatedata AS templatedata ON (templates.templateid = templatedata.templateid) WHERE templates.tcategoryid IN (" . BuildIN($_templateCategoryIDList) . ")" . $_modifiedSQL);
        while ($this->Database->NextRecord()) {
            $_templateContainer[$this->Database->Record['tcategoryid']][] = $this->Database->Record;
            $_templateIDList[] = $this->Database->Record['templateid'];
        }

        // Fetch history if needed
        if ($_exportHistory && count($_templateIDList)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templatehistory WHERE templateid IN (" . BuildIN($_templateIDList) . ")");
            while ($this->Database->NextRecord()) {
                $_templateHistoryContainer[$this->Database->Record['templateid']][] = $this->Database->Record;
            }
        }

        // ======= Process all data into XML now =======
        $this->XML->AddComment(sprintf($this->Language->Get('generationdate'), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, DATENOW)));
        $this->XML->AddParentTag('swifttemplate');
        $this->XML->AddTag('title', $_templateGroup['title']);
        $this->XML->AddTag('author', $_templateGroup['companyname']);
        $this->XML->AddTag('version', SWIFT_VERSION);

        foreach ($_templateCategoryContainer as $_key => $_val) {
            if (isset($_templateContainer[$_val['tcategoryid']]) && _is_array($_templateContainer[$_val['tcategoryid']])) {
                $this->XML->AddParentTag('category', array('name' => $_val['name'], 'app' => $_val['app'], 'icon' => $_val['icon']));

                // Now iterate through templates for this category
                foreach ($_templateContainer[$_val['tcategoryid']] as $_templateKey => $_templateVal) {
                    $this->XML->AddComment('BEGIN TEMPLATE: ' . $_templateVal['name']);
                    $this->XML->AddTag('template', $_templateVal['contents'], array('name' => $_templateVal['name'], 'date' => $_templateVal['dateline'], 'version' => $_templateVal['templateversion']));
                    if (isset($_templateHistoryContainer[$_templateVal['templateid']]) && _is_array($_templateHistoryContainer[$_templateVal['templateid']])) {
                        foreach ($_templateHistoryContainer[$_templateVal['templateid']] as $_historyKey => $_historyVal) {
                            $this->XML->AddTag('history', $_historyVal['contents'], array('name' => $_templateVal['name'], 'date' => $_historyVal['dateline'], 'version' => $_historyVal['templateversion'], 'hash' => $_historyVal['contentshash']));
                        }
                    }

                    $this->XML->AddComment('END TEMPLATE: ' . $_templateVal['name']);
                }

                $this->XML->EndTag('category');
            }
        }

        $this->XML->EndTag('swifttemplate');

        $_xmlData = $this->XML->ReturnXML();


        if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) {
            // IE Bug in download name workaround
            ini_set('zlib.output_compression', 'Off');
        }

        header('Content-Type: application/force-download');

        header("Content-Disposition: attachment; filename=\"" . $_fileName . "\"");

        header('Content-Transfer-Encoding: binary');
//        header('Content-Length: ' . mb_strlen($_xmlData));

        echo preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_xmlData);

        return true;
    }

    /**
     * Merge the Templates
     *
     * @author Varun Shoor
     * @param string $_filePath The File Path to Get Templates From
     * @param bool $_doVersionCheck Whether to do version checking on data
     * @param int $_templateGroupRestrictID (OPTIONAL) Template Group ID to Merge Changes with
     * @param bool $_addToHistory (OPTIONAL) Whether to add changes to the history
     * @return mixed array(_templateCategoryNameList, _templateNameList) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Merge($_filePath, $_doVersionCheck = false, $_templateGroupRestrictID = 0, $_addToHistory = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!file_exists($_filePath) || !is_readable($_filePath)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Parse the templates XML File.
        $_templateXMLContainer = $this->XML->XMLToTree(file_get_contents($_filePath));
        if (!$_templateXMLContainer || !isset($_templateXMLContainer['swifttemplate'][0]['children'])) {
            return false;
        }

        $_finalTemplates = &$_templateXMLContainer['swifttemplate'][0]['children'];

        $_title = $_author = $_version = '';
        if (isset($_finalTemplates['title']) && isset($_finalTemplates['title'][0]['values'])) {
            $_title = $_finalTemplates['title'][0]['values'][0];
        }

        if (isset($_finalTemplates['author']) && isset($_finalTemplates['author'][0]['values'])) {
            $_author = $_finalTemplates['author'][0]['values'][0];
        }

        if (isset($_finalTemplates['version']) && isset($_finalTemplates['version'][0]['values'])) {
            $_version = $_finalTemplates['version'][0]['values'][0];
        }

        if (!empty($_version) && $_doVersionCheck == true) {
            // Check for Template Version
            if (version_compare($_version, SWIFT_VERSION) == -1) {
                return -1;
            }
        }

        if (empty($_title)) {
            return false;
        }

        $_appList = SWIFT_App::ListApps();
        if (!_is_array($_appList)) {
            return false;
        }

        $_templateCategoryNameList = $_templateNameList = $_result = array();

        $_templateGroupIDList = array();

        // ======= Fetch All Template Groups =======
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templategroups ORDER BY tgroupid ASC", 5);
        while ($this->Database->NextRecord(5)) {
            $_templateGroupIDList[] = $this->Database->Record5['tgroupid'];
        }

        // Does user wants us to restrict the update to a specific template group?
        $_isUpgrade = false;
        if ($_templateGroupRestrictID) {
            $_templateGroupIDList = array($_templateGroupRestrictID);

            $_modifiedStatus = SWIFT_Template::TYPE_MODIFIED;
        } else {
            $_isUpgrade = true;

            $_modifiedStatus = SWIFT_Template::TYPE_UPGRADE;
        }

        $_staffID = 0;
        if (isset($_SWIFT->Staff) && $_SWIFT->Staff instanceof SWIFT_Staff && $_SWIFT->Staff->GetIsClassLoaded()) {
            $_staffID = $_SWIFT->Staff->GetStaffID();
        }

        if (!isset($_finalTemplates['category']) || !count($_finalTemplates['category'])) {
            return false;
        }

        $_installedApps = SWIFT_App::GetInstalledApps();

        for ($_ii = 0; $_ii < count($_finalTemplates['category']); $_ii++) {
            if (!isset($_finalTemplates['category'][$_ii]['attrs'])) {
                continue;
            }

            // This is our attribute holder, contains the category name etc
            $_templateCategoryContainer = &$_finalTemplates['category'][$_ii]['attrs'];

            /*
             * BUG FIX - Simaranjit Singh
             *
             * SWIFT-3081 Modified templates exported from build < 4.5 do not get imported and merged with templates in build >=4.5 templates.
             *
             * Comments: Backward compatibility
             */
            if (isset($_templateCategoryContainer['module'])) {
                $_templateCategoryContainer['app'] = $_templateCategoryContainer['module'];
            }

            if (!isset($_templateCategoryContainer['name'], $_templateCategoryContainer['app'])) {
                continue;
            }

            $_templateCategoryIDList = $_templateCategoryGroupMap = $_templateHashList = array();

            $_proceed = false;

            // Only add this category if its app is registered
            if (in_array($_templateCategoryContainer['app'], $_installedApps)) {
                $_proceed = true;
            }

            if ($_proceed) {
                // ======= Iterate through all the groups and look for categories =======
                foreach ($_templateGroupIDList as $_key => $_val) {
                    $_val = (int)($_val);
                    if (empty($_val)) {
                        continue;
                    }

                    // Does this category exist for this template group?
                    $_templateCategory_Group = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "templatecategories
                        WHERE name = '" . addslashes($_templateCategoryContainer['name']) . "' AND tgroupid = '" . $_val . "'");

                    if ($_templateCategory_Group && isset($_templateCategory_Group['tcategoryid']) && $_templateCategory_Group['tgroupid'] == $_val) {
                        // Exists
                        $_templateCategoryIDList[] = $_templateCategory_Group['tcategoryid'];
                        $_templateCategoryGroupMap[$_templateCategory_Group['tcategoryid']] = $_templateCategory_Group['tgroupid'];
                    } else {
                        // Insert a new template category for this group as it doesnt exist
                        $_newTemplateCategoryDescription = $_newTemplateCategoryIcon = '';
                        if (isset($_templateCategoryContainer['description'])) {
                            $_newTemplateCategoryDescription = $_templateCategoryContainer['description'];
                        }

                        if (isset($_templateCategoryContainer['icon'])) {
                            $_newTemplateCategoryIcon = $_templateCategoryContainer['icon'];
                        }

                        $_newTemplateCategoryID = SWIFT_TemplateCategory::Create($_val, $_templateCategoryContainer['name'],
                            $_templateCategoryContainer['app'], $_newTemplateCategoryDescription,
                            $_newTemplateCategoryIcon, true);

                        $_templateCategoryNameList[] = $_templateCategoryContainer['name'];
                        $_templateCategoryIDList[] = $_newTemplateCategoryID;

                        $_templateCategoryGroupMap[$_newTemplateCategoryID] = $_val;
                    }
                }

                // ======= Build Content Hash for all Templates under this Category for ALL Groups =======
                $this->Database->Query("SELECT contentshash, templateid, name FROM " . TABLE_PREFIX . "templates WHERE tcategoryid IN (" . BuildIN($_templateCategoryIDList) . ")", 5);
                while ($this->Database->NextRecord(5)) {
                    if (!isset($_templateHashList[$this->Database->Record5['name']])) {
                        $_index = 0;
                    } else {
                        $_index = count($_templateHashList[$this->Database->Record5['name']]);
                    }

                    $_templateHashList[$this->Database->Record5['name']][$_index]['contentshash'] = $this->Database->Record5['contentshash'];
                    $_templateHashList[$this->Database->Record5['name']][$_index]['templateid'] = $this->Database->Record5['templateid'];
                }

                if (!isset($_finalTemplates['category'][$_ii]['children']['template'])) {
                    continue;
                }

                // Now we iterate through individual templates for this category
                for ($_kk = 0; $_kk < count($_finalTemplates['category'][$_ii]['children']['template']); $_kk++) {
                    if (!isset($_finalTemplates['category'][$_ii]['children']['template'][$_kk]['attrs'], $_finalTemplates['category'][$_ii]['children']['template'][$_kk]['values'][0])) {
                        continue;
                    }

                    $_templateContainer = $_finalTemplates['category'][$_ii]['children']['template'][$_kk]['attrs'];
                    $_templateContents = $_finalTemplates['category'][$_ii]['children']['template'][$_kk]['values'][0];

                    /**
                     * BUG FIX : Nidhi Gupta <nidhi.gupta@kayako.com>
                     *
                     * SWIFT-4762 While upgrading product restore notification should come only for modified template, phrases.
                     *
                     * Comments : Code to remove Carriage return character ^M
                     */
                    $_templateContents = str_replace("\r\n", SWIFT_CRLF, $_templateContents);

                    if (!isset($_templateContainer['name'])) {
                        continue;
                    }

                    $_templateContentsHash = md5(trim($_templateContents));

                    $_templateCount = $this->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "templates WHERE tcategoryid IN (" . BuildIN($_templateCategoryIDList) . ") AND name = '" . addslashes($_templateContainer['name']) . "'");
                    if ($_templateCount['totalitems'] > 0) {
                        // Records are already there for this template, see if this template needs any updating
                        $this->Database->Query("SELECT templates.*, templatedata.* FROM " . TABLE_PREFIX . "templates AS templates
                            LEFT JOIN " . TABLE_PREFIX . "templatedata AS templatedata ON (templates.templateid = templatedata.templateid)
                            WHERE templates.tcategoryid IN (" . BuildIN($_templateCategoryIDList) . ") AND templates.name = '" . addslashes($_templateContainer['name']) . "'", 5);
                        while ($this->Database->NextRecord(5)) {
                            $_templateRecord = $this->Database->Record5;

                            // Are we merging in the changes to a specific template group?
                            $_hashCheck = '';
                            if ($_templateGroupRestrictID) {
                                $_hashCheck = $_templateRecord['contentshash'];
                            } else {
                                $_hashCheck = $_templateRecord['contentsdefaulthash'];
                            }

                            if ($_isUpgrade) {
                                $_hashCheck = $_templateRecord['contentsdefaulthash'];
                            }

                            // Does the hash differ with the one from our latest set file?
                            if (md5(trim($_templateContents)) != $_hashCheck) {
                                $_SWIFT_TemplateObject = new SWIFT_Template($_templateRecord['templateid']);
                                if ($_SWIFT_TemplateObject instanceof SWIFT_Template && $_SWIFT_TemplateObject->GetIsClassLoaded()) {
                                    $_SWIFT_TemplateObject->Update($_templateContents, $_addToHistory, $_staffID, '', $_isUpgrade);
                                    $_templateNameList[] = $_templateContainer['name'];
                                }
                            }
                        }
                    } else {
                        // No such record! Seems like a new template, insert it for all the categories for all groups
                        foreach ($_templateCategoryIDList as $_key => $_val) {
                            if (!isset($_templateCategoryGroupMap[$_val])) {
                                continue;
                            }

                            $_templateID = SWIFT_Template::Create($_templateCategoryGroupMap[$_val], $_val, $_templateContainer['name'], $_templateContents, $_templateContents, IIF($_isUpgrade, false, true));

                            $_templateNameList[] = $_templateContainer['name'];
                        }
                    }
                }
            }
        }

        $_result['categorylist'] = $_templateCategoryNameList;
        $_result['templatelist'] = $_templateNameList;

        return $_result;
    }

    /**
     * Import all files
     *
     * @author Varun Shoor
     * @param bool $_isUpgrade
     * @return array Result Array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ImportAll($_isUpgrade)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_statusListContainer = array();

        $_installedAppList = SWIFT_App::GetInstalledApps();

        foreach ($_installedAppList as $_appName) {
            $_SWIFT_AppObject = false;
            try {
                $_SWIFT_AppObject = SWIFT_App::Get($_appName);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                continue;
            }

            if (!$_SWIFT_AppObject instanceof SWIFT_App || !$_SWIFT_AppObject->GetIsClassLoaded()) {
                continue;
            }

            // See if we have a templates file in there..
            $_templatesFile = $_SWIFT_AppObject->GetDirectory() . '/config/templates.xml';
            if (!file_exists($_templatesFile)) {
                continue;
            }

            $_statusList = array();
            if ($_isUpgrade) {
                $_statusList = $this->Merge($_templatesFile);
            } else {
                $_templateGroupContainer = $this->Database->QueryFetch("SELECT tgroupid AS tgroupid FROM " . TABLE_PREFIX . "templategroups ORDER BY tgroupid ASC");
                $_statusList = $this->Import($_templatesFile, $_templateGroupContainer['tgroupid'], false);
            }
            $_statusListContainer = array_merge($_statusList, $_statusListContainer);
        }

        return $_statusListContainer;
    }

    /**
     * Clear the templates on the provided list of apps
     *
     * @author Varun Shoor
     * @param array $_appNameList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DeleteOnApp($_appNameList)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        } elseif (!_is_array($_appNameList)) {
            return false;
        }

        SWIFT_TemplateCategory::DeleteOnApp($_appNameList);

        return true;
    }
}

?>
