<?php

/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    QuickSupport Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-2009, QuickSupport Singapore Pte. Ltd.
 * @license    http://www.kayako.com/license
 * @link        http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

namespace News\Api;

use News\Models\Category\SWIFT_NewsCategory;
use SWIFT;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use Controller_api;
use SWIFT_RESTServer;
use SWIFT_XML;

/**
 * The News Category API Controller
 *
 * @property SWIFT_XML $XML
 * @property SWIFT_RESTServer $RESTServer
 * @author Simaranjit Singh
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
        $this->Load->Library('Render:NewsRenderManager');
    }

    /**
     * Retrieve & Dispatch the Categories
     *
     * Example output:
     *
     * <newscategories>
     *     <category>
     *         <id><![CDATA[1]]></id>
     *         <title><![CDATA[name]]></title>
     *         <newsitemcount><![CDATA[3]]></newsitemcount>
     *         <visibilitytype><![CDATA[public]]></visibilitytype>
     *     </category>
     * </newscategories>
     *
     * @author Simaranjit Singh
     * @param int $_newsCategoryID (OPTIONAL) The News Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessNewsCategories($_newsCategoryID = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_categoryContainer = array();

        if (!empty($_newsCategoryID)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newscategories WHERE newscategoryid = '" .  ($_newsCategoryID) . "'");
        } else {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newscategories");
        }

        while ($this->Database->NextRecord()) {
            $_categoryContainer[$this->Database->Record['newscategoryid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('newscategories');

        foreach ($_categoryContainer as $_newsCategoryID => $_category) {
            $this->XML->AddParentTag('newscategory');
            $this->XML->AddTag('id', $_newsCategoryID);
            $this->XML->AddTag('title', $_category['categorytitle']);
            $this->XML->AddTag('newsitemcount', $_category['newsitemcount']);
            $this->XML->AddTag('visibilitytype', $_category['visibilitytype']);
            $this->XML->EndParentTag('newscategory');
        }

        $this->XML->EndParentTag('newscategories');

        return true;
    }

    /**
     * Get a list of News Categories
     *
     * Example output:
     *
     * <newscategories>
     *     <category>
     *         <id><![CDATA[1]]></id>
     *         <title><![CDATA[name]]></title>
     *         <newsitemcount><![CDATA[3]]></newsitemcount>
     *         <visibilitytype><![CDATA[public]]></visibilitytype>
     *     </category>
     * </newscategories>
     *
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessNewsCategories(0);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve a Category
     *
     * Example output:
     *
     * <newscategories>
     *     <category>
     *         <id><![CDATA[1]]></id>
     *         <title><![CDATA[name]]></title>
     *         <newsitemcount><![CDATA[3]]></newsitemcount>
     *         <visibilitytype><![CDATA[public]]></visibilitytype>
     *     </category>
     * </newscategories>
     *
     * @author Simaranjit Singh
     * @param int $_newsCategoryID The News Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_newsCategoryID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->ProcessNewsCategories($_newsCategoryID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Create a Category
     *
     * Required Fields:
     * title
     * visibilitytype
     *
     * Example output:
     *
     * <newscategories>
     *     <category>
     *         <id><![CDATA[1]]></id>
     *         <title><![CDATA[name]]></title>
     *         <newsitemcount><![CDATA[3]]></newsitemcount>
     *         <visibilitytype><![CDATA[public]]></visibilitytype>
     *     </category>
     * </newscategories>
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!isset($_POST['title']) || trim($_POST['title']) == '' || empty($_POST['title'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Title field is missing/empty');

            return false;
        }

        if (!isset($_POST['visibilitytype']) || trim($_POST['visibilitytype']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'visibilitytype is missing/empty');

            return false;
        }

        $_newsCategoryID = SWIFT_NewsCategory::Create($_POST['title'], IIF($_POST['visibilitytype'] == 'public', SWIFT_PUBLIC, SWIFT_PRIVATE));

        if (!$_newsCategoryID) {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Category Creation Failed');

            return false;
            // @codeCoverageIgnoreEnd
        }

        $this->ProcessNewsCategories($_newsCategoryID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Update a Category
     *
     * Required Fields:
     * newscategoryid
     * title
     * visibilitytype
     *
     * Example output:
     *
     * <newscategories>
     *     <category>
     *         <id><![CDATA[1]]></id>
     *         <title><![CDATA[name]]></title>
     *         <newsitemcount><![CDATA[3]]></newsitemcount>
     *         <visibilitytype><![CDATA[public]]></visibilitytype>
     *     </category>
     * </newscategories>
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_newsCategoryID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_NewsCategoryObject = false;

        try {
            $_SWIFT_NewsCategoryObject = new SWIFT_NewsCategory($_newsCategoryID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid category ID');

            return false;
        }

        $_title = $_SWIFT_NewsCategoryObject->GetProperty('categorytitle');

        if (isset($_POST['title']) && trim($_POST['title']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'title is empty');

            return false;

        } else if (isset($_POST['title'])) {
            $_title = $_POST['title'];
        }

        $_visibilityType = $_SWIFT_NewsCategoryObject->GetProperty('visibilitytype');

        if (isset($_POST['visibilitytype']) && trim($_POST['visibilitytype']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'visibilitytype is empty');

            return false;

        } else if (isset($_POST['visibilitytype'])) {
            $_visibilityType = $_POST['visibilitytype'];
        }

        $_updateResult = $_SWIFT_NewsCategoryObject->Update($_title, IIF($_visibilityType == 'public', SWIFT_PUBLIC, SWIFT_PRIVATE));

        if (!$_updateResult) {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Category Update Failed');

            return false;
            // @codeCoverageIgnoreEnd
        }

        $this->ProcessNewsCategories($_newsCategoryID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     *     Delete a category
     *
     * @author Simaranjit Singh
     * @param string $_newsCategoryID news category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_newsCategoryID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_NewsCategoryObject = false;

        try {
            $_SWIFT_NewsCategoryObject = new SWIFT_NewsCategory((int)$_newsCategoryID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid category ID');
            return false;
        }

        SWIFT_NewsCategory::DeleteList(array($_newsCategoryID));

        return true;
    }

}
