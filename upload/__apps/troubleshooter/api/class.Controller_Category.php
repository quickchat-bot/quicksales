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

namespace Troubleshooter\Api;

use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_RESTServer;
use Base\Models\Staff\SWIFT_Staff;
use SWIFT_XML;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;

/**
 * The Troubleshooter Category API Controller
 *
 * @author Simaranjit Singh
 */
class Controller_Category extends \Controller_api implements \SWIFT_REST_Interface
{
    /** @var SWIFT_RESTServer $RESTServer */
    public $RESTServer;

    /** @var SWIFT_XML $XML */
    public $XML;

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
        $this->Language->Load('staff_troubleshooter');
    }

    /**
     * Retrieve & Dispatch the Categories
     *
     * @author Simaranjit Singh
     * @param int|false $_troubleshooterCategoryID (OPTIONAL) The Troubleshooter Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessTroubleshooterCategories($_troubleshooterCategoryID = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_categoryContainer = array();
        if (!empty($_troubleshooterCategoryID)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "troubleshootercategories WHERE troubleshootercategoryid  = '" . $_troubleshooterCategoryID . "'");
        } else {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "troubleshootercategories ORDER BY displayorder ASC");
        }

        while ($this->Database->NextRecord()) {
            $_categoryContainer[$this->Database->Record['troubleshootercategoryid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('troubleshootercategories');
        foreach ($_categoryContainer as $_categoryID => $_category) {
            $_SWIFT_TroubleshooterCategoryObject = new SWIFT_TroubleshooterCategory(new SWIFT_DataID((int) $_categoryID));

            $this->XML->AddParentTag('troubleshootercategory');
            $this->XML->AddTag('id', $_categoryID);
            $this->XML->AddTag('staffid', $_category['staffid']);
            $this->XML->AddTag('staffname', $_category['staffname']);
            $this->XML->AddTag('title', $_category['title']);
            $this->XML->AddTag('description', $_category['description']);
            $this->XML->AddTag('categorytype', $_category['categorytype']);
            $this->XML->AddTag('displayorder', $_category['displayorder']);
            $this->XML->AddTag('views', $_category['views']);
            $this->XML->AddTag('uservisibilitycustom', $_category['uservisibilitycustom']);

            //For user group
            $this->XML->AddParentTag('usergroupidlist');
            $_userGroupIDList = $_SWIFT_TroubleshooterCategoryObject->GetLinkedUserGroupIDList();
            foreach ($_userGroupIDList as $_userGroupID) {
                $this->XML->AddTag('usergroupid', $_userGroupID);
            }
            $this->XML->EndParentTag('usergroupidlist');

            $this->XML->AddTag('staffvisibilitycustom', $_category['staffvisibilitycustom']);

            //For staff group
            $this->XML->AddParentTag('staffgroupidlist');
            $_staffGroupIDList = $_SWIFT_TroubleshooterCategoryObject->GetLinkedStaffGroupIDList();
            foreach ($_staffGroupIDList as $_staffGroupID) {
                $this->XML->AddTag('staffgroupid', $_staffGroupID);
            }
            $this->XML->EndParentTag('staffgroupidlist');

            $this->XML->EndParentTag('troubleshootercategory');
        }
        $this->XML->EndParentTag('troubleshootercategories');

        return true;
    }

    /**
     * Get a list of Troubleshooter Categories
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

        $this->ProcessTroubleshooterCategories(false);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve a Category
     *
     * @author Simaranjit Singh
     * @param int $_troubleshooterCategoryID The Troubleshooter Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_troubleshooterCategoryID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessTroubleshooterCategories($_troubleshooterCategoryID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Create a Category
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
        $_userGroupCache = (array) $this->Cache->Get('usergroupcache');
        $_staffGroupCache = (array) $this->Cache->Get('staffgroupcache');

        if (!isset($_POST['title']) || trim($_POST['title']) == '' || empty($_POST['title'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Title field is empty');

            return false;
        } else if (!isset($_POST['categorytype']) || trim($_POST['categorytype']) == '' || empty($_POST['categorytype'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Category type is empty');

            return false;
        }

        $_staffID = '';
        if (isset($_POST['staffid']) && !empty($_POST['staffid'])) {
            $_staffID = (int) ($_POST['staffid']);
        }

        $_SWIFT_StaffObject_Creator = new SWIFT_Staff(new SWIFT_DataID($_POST['staffid']));

        $_displayOrder = 0;
        if (isset($_POST['displayorder']) && !empty($_POST['displayorder'])) {
            $_displayOrder = (int) ($_POST['displayorder']);
        }

        $_description = '';
        if (isset($_POST['description']) && trim($_POST['description']) != '') {
            $_description = $_POST['description'];
        }

        $_userVisibilityCustom = false;
        if (isset($_POST['uservisibilitycustom']) && !empty($_POST['uservisibilitycustom'])) {
            $_userVisibilityCustom = (int) ($_POST['uservisibilitycustom']);
        }

        $_userGroupIDList = array();
        if (isset($_POST['usergroupidlist']) && !empty($_POST['usergroupidlist'])) {
            $_userGroupIDList = explode(',', $_POST['usergroupidlist']);
            //I need to make sure that user is not enring any invalid or non existing group, sorry naughty guys :)
            $_userGroupIDList = array_intersect(array_keys($_userGroupCache), $_userGroupIDList);
        }

        $_staffVisibilityCustom = false;
        if (isset($_POST['staffvisibilitycustom']) && !empty($_POST['staffvisibilitycustom'])) {
            $_staffVisibilityCustom = (int) ($_POST['staffvisibilitycustom']);
        }
        $_staffGroupIDList = array();
        if (isset($_POST['staffgroupidlist']) && !empty($_POST['staffgroupidlist'])) {
            $_staffGroupIDList = explode(',', $_POST['staffgroupidlist']);
            //I need to make sure that user is not enring any invalid or non existing group, sorry naughty guys :)
            $_staffGroupIDList = array_intersect(array_keys($_staffGroupCache), $_staffGroupIDList);
        }

        $_troubleshooterCategoryID = SWIFT_TroubleshooterCategory::Create($_POST['title'], $_description, $_POST['categorytype'], $_displayOrder, (bool) $_userVisibilityCustom, $_userGroupIDList, (bool) $_staffVisibilityCustom, $_staffGroupIDList, $_SWIFT_StaffObject_Creator);

        if (!$_troubleshooterCategoryID) {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Category Creation Failed');
            return false;
            // @codeCoverageIgnoreEnd
        }

        $this->ProcessTroubleshooterCategories($_troubleshooterCategoryID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Update a Category
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_troubleshooterCategoryID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TroubleshooterCategoryObject = false;

        try {
            $_SWIFT_TroubleshooterCategoryObject = new SWIFT_TroubleshooterCategory(new SWIFT_DataID($_troubleshooterCategoryID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Troubleshooter Category ID');
            return false;
        }

        //I need staff cache to get infromation of staff who created Category
        $_userGroupCache = (array) $this->Cache->Get('usergroupcache');
        $_staffGroupCache = (array) $this->Cache->Get('staffgroupcache');

        $_title = $_SWIFT_TroubleshooterCategoryObject->GetProperty('title');
        if (isset($_POST['title']) && trim($_POST['title']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'subject is empty');

            return false;
        } else if (isset($_POST['title'])) {
            $_title = $_POST['title'];
        }

        $_categoryType = $_SWIFT_TroubleshooterCategoryObject->GetProperty('categorytype');
        if (isset($_POST['categorytype']) && trim($_POST['categorytype']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'categorytype is empty');

            return false;
        } else if (isset($_POST['categorytype'])) {
            $_categoryType = $_POST['categorytype'];
        }

        $_staffID = $_SWIFT_TroubleshooterCategoryObject->GetProperty('staffid');

        $_SWIFT_StaffObject_Creator = false;
        try {
            $_SWIFT_StaffObject_Creator = new SWIFT_Staff(new SWIFT_DataID($_staffID));
        }  catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            //Nothing here
        }

        $_displayOrder = $_SWIFT_TroubleshooterCategoryObject->GetProperty('displayorder');
        if (isset($_POST['displayorder']) && !empty($_POST['displayorder'])) {
            $_displayOrder = (int) ($_POST['displayorder']);
        }

        $_description = '';

        try {
            $_description = $_SWIFT_TroubleshooterCategoryObject->GetProperty('description');
        }  catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            //Do nothing
        }
        if (isset($_POST['description']) && trim($_POST['description']) != '') {
            $_description = $_POST['description'];
        }

        $_userVisibilityCustom = $_SWIFT_TroubleshooterCategoryObject->GetProperty('uservisibilitycustom');
        if (isset($_POST['uservisibilitycustom']) && !empty($_POST['uservisibilitycustom'])) {
            $_userVisibilityCustom = (int) ($_POST['uservisibilitycustom']);
        }

        $_userGroupIDList = $_SWIFT_TroubleshooterCategoryObject->GetLinkedUserGroupIDList();
        if (isset($_POST['usergroupidlist']) && !empty($_POST['usergroupidlist'])) {
            $_userGroupIDList = explode(',', $_POST['usergroupidlist']);
            //I need to make sure that user is not enring any invalid or non existing group, sorry naughty guys :)
            $_userGroupIDList = array_intersect(array_keys($_userGroupCache), $_userGroupIDList);
        }

        $_staffVisibilityCustom = $_SWIFT_TroubleshooterCategoryObject->GetProperty('staffvisibilitycustom');
        if (isset($_POST['staffvisibilitycustom']) && !empty($_POST['staffvisibilitycustom'])) {
            $_staffVisibilityCustom = (int) ($_POST['staffvisibilitycustom']);
        }

        $_staffGroupIDList = $_SWIFT_TroubleshooterCategoryObject->GetLinkedStaffGroupIDList();
        if (isset($_POST['staffgroupidlist']) && !empty($_POST['staffgroupidlist'])) {
            $_staffGroupIDList = explode(',', $_POST['staffgroupidlist']);
            //I need to make sure that user is not enring any invalid or non existing group, sorry naughty guys :)
            $_staffGroupIDList = array_intersect(array_keys($_staffGroupCache), $_staffGroupIDList);
        }

        $_SWIFT_TroubleshooterCategoryObject->Update($_title, $_description, $_categoryType, $_displayOrder, $_userVisibilityCustom, $_userGroupIDList, $_staffVisibilityCustom, $_staffGroupIDList, $_SWIFT_StaffObject_Creator);

        $this->ProcessTroubleshooterCategories($_troubleshooterCategoryID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     *     Delete a category
     *
     * @author Simaranjit Singh
     * @param string $_troubleshooterCategoryID troubleshooter category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_troubleshooterCategoryID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TroubleshooterCategoryObject = false;

        try {
            $_SWIFT_TroubleshooterCategoryObject = new SWIFT_TroubleshooterCategory(new SWIFT_DataID((int) $_troubleshooterCategoryID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Troubleshooter Category ID');
        }

        SWIFT_TroubleshooterCategory::DeleteList(array($_troubleshooterCategoryID));

        return true;
    }

}

?>
