<?php

/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    QuickSupport Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-2009, QuickSupport Singapore Pte. Ltd.
 * @license    http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 * @filesource
 * ###################################
 * =======================================
 */

namespace Knowledgebase\Api;

use Controller_api;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;
use SWIFT_XML;

/**
 * The Knowledgebase Category API Controller
 *
 * @author Simaranjit Singh
 *
 * @property SWIFT_XML $XML
 * @property SWIFT_RESTServer $RESTServer
 */
class Controller_Category extends Controller_api implements SWIFT_REST_Interface
{
    /**
     * Constructor
     *
     * @author Simaranjit Singh
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');
        $this->Load->Library('Category:KnowledgebaseCategoryManager', false, false);
    }

    /**
     * @author Utsav Handa <utsav.handa@opencart.com.vn>
     *
     * @param string $_sortField
     *
     * @return bool
     */
    public static function IsValidSortField($_sortField)
    {
        return in_array(strtolower($_sortField),
            array('staffid', 'title', 'dateline', 'totalarticles', 'categorytype', 'articlesortorder'));
    }

    /**
     * Retrieve & Dispatch the Categories
     *
     * @author Simaranjit Singh
     * @author Saloni Dhall <saloni.dhall@opencart.com.vn>
     *
     * @param int|bool  $_knowledgebaseCategoryID (OPTIONAL)
     * @param int       $_rowsPerPage             (OPTIONAL)
     * @param int       $_rowOffset               (OPTIONAL)
     * @param string    $_sortField               (OPTIONAL)
     * @param string     $_sortOrder               (OPTIONAL)
     *
     * @return bool
     *
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessKbCategories($_knowledgebaseCategoryID = false, $_rowsPerPage = -1, $_rowOffset = 0, $_sortField = null, $_sortOrder = self::SORT_ASC)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_categoryContainer = array();

        $_sortField = Clean($_sortField);

        $_sqlExtended  = ' ORDER BY ';
        $_sqlExtended .= IIF(self::IsValidSortField($_sortField), $this->Database->Escape($_sortField) . ' ', 'displayorder ');
        $_sqlExtended .= IIF(self::IsValidSortOrder($_sortOrder), $_sortOrder, self::SORT_ASC);

        if (!empty($_knowledgebaseCategoryID)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . SWIFT_KnowledgebaseCategory::TABLE_NAME . "
                                    WHERE kbcategoryid = '" . ($_knowledgebaseCategoryID) . "'");
        }

        /**
         * BUG FIX - Mansi Wason
         *
         * SWIFT-3324: Pagination Support for API requests
         *
         * Comments: None
         */
        else {
            $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . SWIFT_KnowledgebaseCategory::TABLE_NAME . $_sqlExtended, $_rowsPerPage, $_rowOffset);
        }

        while ($this->Database->NextRecord()) {
            $_categoryContainer[$this->Database->Record['kbcategoryid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('kbcategories');
        foreach ($_categoryContainer as $_knowledgebaseCategoryID => $_category) {
            $_SWIFT_KnowledgebaseCategoryObject = new SWIFT_KnowledgebaseCategory(new SWIFT_DataID($_knowledgebaseCategoryID));

            $this->XML->AddParentTag('kbcategory');
            $this->XML->AddTag('id', $_knowledgebaseCategoryID);
            $this->XML->AddTag('parentkbcategoryid', $_category['parentkbcategoryid']);
            $this->XML->AddTag('staffid', $_category['staffid']);
            $this->XML->AddTag('title', $_category['title']);
            $this->XML->AddTag('totalarticles', $_category['totalarticles']);
            $this->XML->AddTag('categorytype', $_category['categorytype']);
            $this->XML->AddTag('displayorder', $_category['displayorder']);
            $this->XML->AddTag('allowcomments', $_category['allowcomments']);
            $this->XML->AddTag('uservisibilitycustom', $_category['uservisibilitycustom']);

            //For user group
            $this->XML->AddParentTag('usergroupidlist');
            $_userGroupIDList = $_SWIFT_KnowledgebaseCategoryObject->GetLinkedUserGroupIDList();

            foreach ($_userGroupIDList as $_userGroupID) {
                $this->XML->AddTag('usergroupid', $_userGroupID);
            }

            $this->XML->EndParentTag('usergroupidlist');

            $this->XML->AddTag('staffvisibilitycustom', $_category['staffvisibilitycustom']);

            //For staff group
            $this->XML->AddParentTag('staffgroupidlist');
            $_staffGroupIDList = $_SWIFT_KnowledgebaseCategoryObject->GetLinkedStaffGroupIDList();
            foreach ($_staffGroupIDList as $_staffGroupID) {
                $this->XML->AddTag('staffgroupid', $_staffGroupID);
            }
            $this->XML->EndParentTag('staffgroupidlist');

            $this->XML->AddTag('allowrating', $_category['allowrating']);
            $this->XML->AddTag('ispublished', $_category['ispublished']);
            $this->XML->EndParentTag('kbcategory');
        }
        $this->XML->EndParentTag('kbcategories');

        return true;
    }

    /**
     * Get a list of Knowledgebase Categories
     *
     * Example Output:
     *
     * <kbcategories>
     * <category>
     * <id><![CDATA[63]]></id>
     * <parentkbcategoryid><![CDATA[1]]></parentkbcategoryid>
     * <staffid><![CDATA[0]]></staffid>
     * <title><![CDATA[By API]]></title>
     * <totalarticles><![CDATA[0]]></totalarticles>
     * <categorytype><![CDATA[1]]></categorytype>
     * <displayorder><![CDATA[4]]></displayorder>
     * <allowcomments><![CDATA[1]]></allowcomments>
     * <uservisibilitycustom><![CDATA[1]]></uservisibilitycustom>
     * <usergroupidlist>
     * </usergroupidlist>
     * <staffvisibilitycustom><![CDATA[1]]></staffvisibilitycustom>
     * <staffgroupidlist>
     * </staffgroupidlist>
     * <allowrating><![CDATA[1]]></allowrating>
     * <ispublished><![CDATA[1]]></ispublished>
     * </category>
     * </kbcategories>
     *
     * @author Simaranjit Singh
     *
     * @param int $_rowsPerPage (OPTIONAL)
     * @param int $_rowOffset   (OPTIONAL)
     * @param string $_sortField (OPTIONAL) The Sort Field
     * @param mixed $_sortOrder (OPTIONAL) The Sort Order
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList($_rowsPerPage = -1, $_rowOffset = 0, $_sortField = null, $_sortOrder = self::SORT_ASC)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessKbCategories(false, ($_rowsPerPage), ($_rowOffset), $_sortField, $_sortOrder);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve a Category
     *
     * Example Output:
     *
     * <kbcategories>
     * <category>
     *          <id>2</id>
     *         <parentkbcategoryid>0</parentkbcategoryid>
     *         <staffid>1</staffid>
     *         <title>apiCategory</title>
     *         <totalarticles>0</totalarticles>
     *         <categorytype>1</categorytype>
     *         <displayorder>1</displayorder>
     *         <allowcomments>1</allowcomments>
     *         <allowrating>1</allowrating>
     *         <ispublished>1</ispublished>
     * </category>
     * </kbcategories>
     *
     * @author Simaranjit Singh
     * @param int $_knowledgebaseCategoryID The Knowledgebase Article ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_knowledgebaseCategoryID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessKbCategories(($_knowledgebaseCategoryID));

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Create a Category
     *
     * Required Fields:
     * parentcategoryid
     * title
     * categorytype
     * displayorder
     * articlesortorder
     * allowcomments
     * allowrating
     * ispublished
     *
     * Example Output:
     *
     * <kbcategories>
     * <category>
     *          <id>2</id>
     *         <parentkbcategoryid>0</parentkbcategoryid>
     *         <staffid>1</staffid>
     *         <title>apiCategory</title>
     *         <totalarticles>0</totalarticles>
     *         <categorytype>1</categorytype>
     *         <displayorder>1</displayorder>
     *         <allowcomments>1</allowcomments>
     *         <allowrating>1</allowrating>
     *         <ispublished>1</ispublished>
     * </category>
     * </kbcategories>
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        //I need staff cache to get infromation of staff who created Category
        $_userGroupCache = $this->Cache->Get('usergroupcache');
        $_staffGroupCache = $this->Cache->Get('staffgroupcache');

        if (!isset($_POST['title']) || trim($_POST['title']) == '' || empty($_POST['title'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Title field is empty');

            return false;
        }

        if (!isset($_POST['categorytype']) || trim($_POST['categorytype']) == '' || empty($_POST['categorytype'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Category type is empty');

            return false;
        }

        $_parentCategoryID = '';
        if (isset($_POST['parentcategoryid']) && !empty($_POST['parentcategoryid'])) {
            $_parentCategoryID = ($_POST['parentcategoryid']);
        }

        $_displayOrder = '';
        if (isset($_POST['displayorder']) && !empty($_POST['displayorder'])) {
            $_displayOrder = ($_POST['displayorder']);
        }

        $_articleSortOrder = '2'; //Sort by Title
        if (isset($_POST['articlesortorder']) && !empty($_POST['articlesortorder'])) {
            $_articleSortOrder = ($_POST['articlesortorder']);
        }

        $_allowComments = '';
        if (isset($_POST['allowcomments']) && !empty($_POST['allowcomments'])) {
            $_allowComments = ($_POST['allowcomments']);
        }

        $_allowRating = '';
        if (isset($_POST['allowrating']) && !empty($_POST['allowrating'])) {
            $_allowRating = ($_POST['allowrating']);
        }

        $_isPublished = '';
        if (isset($_POST['ispublished']) && !empty($_POST['ispublished'])) {
            $_isPublished = ($_POST['ispublished']);
        }

        $_userVisibilityCustom = false;
        if (isset($_POST['uservisibilitycustom']) && !empty($_POST['uservisibilitycustom'])) {
            $_userVisibilityCustom = ($_POST['uservisibilitycustom']);
        }

        $_userGroupIDList = array();
        if (isset($_POST['usergroupidlist']) && !empty($_POST['usergroupidlist'])) {
            $_userGroupIDList = explode(',', $_POST['usergroupidlist']);
            //I need to make sure that user is not enring any invalid or non existing group, sorry naughty guys :)
            $_userGroupIDList = array_intersect(array_keys($_userGroupCache), $_userGroupIDList);
        }

        $_staffVisibilityCustom = false;
        if (isset($_POST['staffvisibilitycustom']) && !empty($_POST['staffvisibilitycustom'])) {
            $_staffVisibilityCustom = ($_POST['staffvisibilitycustom']);
        }
        $_staffGroupIDList = array();
        if (isset($_POST['staffgroupidlist']) && !empty($_POST['staffgroupidlist'])) {
            $_staffGroupIDList = explode(',', $_POST['staffgroupidlist']);
            //I need to make sure that user is not enring any invalid or non existing group, sorry naughty guys :)
            $_staffGroupIDList = array_intersect(array_keys($_staffGroupCache), $_staffGroupIDList);
        }

        $_staffID = '';
        if (isset($_POST['staffid']) && !empty($_POST['staffid'])) {
            $_staffID = ($_POST['staffid']);
        }

        $_SWIFT_KnowledgebaseCategoryObject = SWIFT_KnowledgebaseCategory::Create($_parentCategoryID, $_POST['title'], $_POST['categorytype'], $_displayOrder, $_articleSortOrder, $_allowComments, $_allowRating, $_isPublished, $_userVisibilityCustom, $_userGroupIDList, $_staffVisibilityCustom, $_staffGroupIDList, $_staffID);

        if (!$_SWIFT_KnowledgebaseCategoryObject) {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Category Creation Failed');
            return false;
            // @codeCoverageIgnoreEnd
        }

        $this->ProcessKbCategories($_SWIFT_KnowledgebaseCategoryObject);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Update the Category
     *
     * Required Fields:
     * title
     * categorytype
     * displayorder
     * articlesortorder
     * allowcomments
     * allowrating
     * ispublished
     *
     * Example Output:
     *
     * <kbcategories>
     * <category>
     *          <id>2</id>
     *         <parentkbcategoryid>0</parentkbcategoryid>
     *         <staffid>1</staffid>
     *         <title>apiCategory</title>
     *         <totalarticles>0</totalarticles>
     *         <categorytype>1</categorytype>
     *         <displayorder>1</displayorder>
     *         <allowcomments>1</allowcomments>
     *         <allowrating>1</allowrating>
     *         <ispublished>1</ispublished>
     * </category>
     * </kbcategories>
     *
     * @author Simaranjit Singh
     * @param int $_knowledgebaseCategoryID The Knowledgebase Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_knowledgebaseCategoryID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_KnowledgebaseCategoryObject = false;
        try {
            $_SWIFT_KnowledgebaseCategoryObject = new SWIFT_KnowledgebaseCategory(new SWIFT_DataID($_knowledgebaseCategoryID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid category id');

            return false;
        }

        //I need staff cache to get infromation of staff who created category
        $_userGroupCache = $this->Cache->Get('usergroupcache');
        $_staffGroupCache = $this->Cache->Get('staffgroupcache');

        $_title = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('title');
        if (isset($_POST['title']) && trim($_POST['title']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'subject is empty');

            return false;
        }

        if (isset($_POST['title'])) {
            $_title = $_POST['title'];
        }

        $_parentCategoryID = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('parentkbcategoryid');
        if (isset($_POST['parentkbcategoryid']) && !empty($_POST['parentkbcategoryid'])) {
            $_parentCategoryID = ($_POST['parentkbcategoryid']);
        }

        $_categoryType = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype');
        if (isset($_POST['categorytype']) && !empty($_POST['categorytype'])) {
            $_categoryType = ($_POST['categorytype']);
        }

        $_displayOrder = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('displayorder');
        if (isset($_POST['displayorder']) && !empty($_POST['displayorder'])) {
            $_displayOrder = ($_POST['displayorder']);
        }

        $_articleSortOrder = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('articlesortorder');
        if (isset($_POST['articlesortorder']) && !empty($_POST['articlesortorder'])) {
            $_articleSortOrder = ($_POST['articlesortorder']);
        }

        $_allowComments = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('allowcomments');
        if (isset($_POST['allowcomments']) && !empty($_POST['allowcomments'])) {
            $_allowComments = ($_POST['allowcomments']);
        }

        $_allowRating = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('allowrating');
        if (isset($_POST['allowrating']) && !empty($_POST['allowrating'])) {
            $_allowRating = ($_POST['allowrating']);
        }

        $_isPublished = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('ispublished');
        if (isset($_POST['ispublished']) && !empty($_POST['ispublished'])) {
            $_isPublished = ($_POST['ispublished']);
        }

        $_userVisibilityCustom = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('uservisibilitycustom');
        if (isset($_POST['uservisibilitycustom']) && !empty($_POST['uservisibilitycustom'])) {
            $_userVisibilityCustom = ($_POST['uservisibilitycustom']);
        }

        $_userGroupIDList = $_SWIFT_KnowledgebaseCategoryObject->GetLinkedUserGroupIDList();
        if (isset($_POST['usergroupidlist']) && !empty($_POST['usergroupidlist'])) {
            $_userGroupIDList = explode(',', $_POST['usergroupidlist']);
            //I need to make sure that user is not enring any invalid or non existing group, sorry naughty guys :)
            $_userGroupIDList = array_intersect(array_keys($_userGroupCache), $_userGroupIDList);
        }

        $_staffVisibilityCustom = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('staffvisibilitycustom');
        if (isset($_POST['staffvisibilitycustom']) && !empty($_POST['staffvisibilitycustom'])) {
            $_staffVisibilityCustom = ($_POST['staffvisibilitycustom']);
        }

        $_staffGroupIDList = $_SWIFT_KnowledgebaseCategoryObject->GetLinkedStaffGroupIDList();
        if (isset($_POST['staffgroupidlist']) && !empty($_POST['staffgroupidlist'])) {
            $_staffGroupIDList = explode(',', $_POST['staffgroupidlist']);
            //I need to make sure that user is not enring any invalid or non existing group, sorry naughty guys :)
            $_staffGroupIDList = array_intersect(array_keys($_staffGroupCache), $_staffGroupIDList);
        }

        $_SWIFT_KnowledgebaseCategoryObject->Update($_parentCategoryID, $_title, $_categoryType, $_displayOrder, $_articleSortOrder, $_allowComments, $_allowRating, $_isPublished, $_userVisibilityCustom, $_userGroupIDList, $_staffVisibilityCustom, $_staffGroupIDList, false);

        $this->ProcessKbCategories($_SWIFT_KnowledgebaseCategoryObject->GetKnowledgebaseCategoryID());

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Delete the Category
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Simaranjit Singh
     * @param int $_knowledgebaseCategoryID The Knowledgebase Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_knowledgebaseCategoryID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_KnowledgebaseCategoryObject = false;
        try {
            $_SWIFT_KnowledgebaseCategoryObject = new SWIFT_KnowledgebaseCategory(new SWIFT_DataID($_knowledgebaseCategoryID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid category id');

            return false;
        }

        SWIFT_KnowledgebaseCategory::DeleteList(array($_knowledgebaseCategoryID));

        return true;
    }

}
