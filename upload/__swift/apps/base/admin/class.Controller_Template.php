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

namespace Base\Admin;

use Controller_admin;
use SWIFT;
use SWIFT_CacheManager;
use SWIFT_Exception;
use SWIFT_Session;
use Base\Models\Template\SWIFT_Template;
use Base\Models\Template\SWIFT_TemplateCategory;
use Base\Models\Template\SWIFT_TemplateHistory;

/**
 * The Template Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property \Base\Library\Diff\SWIFT_DiffRenderer $DiffRenderer
 * @property View_Template $View
 * @author Varun Shoor
 */
class Controller_Template extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 0;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Cache:CacheManager');

        $this->Language->Load('templates');
    }

    /**
     * Displays the Manage Templates HTML
     *
     * @author Varun Shoor
     * @param int $_templateGroupID (OPTIONAL) The Template Group ID
     * @param int $_templateCategoryID (OPTIONAL) The Expanded Template Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_templateGroupID = 0, $_templateCategoryID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($_POST['templategroupid'])) {
            $_templateGroupID = (int)($_POST['templategroupid']);
        }

        if (isset($_REQUEST['templatecategoryid'])) {
            $_templateCategoryID = (int)($_REQUEST['templatecategoryid']);
        }

        $this->UserInterface->Header($this->Language->Get('templates') . ' > ' . $this->Language->Get('managetemplates'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanviewtemplates') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderManage($_templateGroupID, $_templateCategoryID);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Restore Templates for a given category
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @param int $_templateCategoryID The Template Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RestoreCategory($_templateGroupID, $_templateCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');
        if (!isset($_templateGroupCache[$_templateGroupID])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TemplateCategoryObject = new SWIFT_TemplateCategory($_templateCategoryID);
        if (!$_SWIFT_TemplateCategoryObject instanceof SWIFT_TemplateCategory || !$_SWIFT_TemplateCategoryObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanrestoretemplates') == '0') {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Manage($_templateGroupID);

            return false;
        }

        SWIFT::Info($this->Language->Get('titletgrouprestorecat'), sprintf($this->Language->Get('msgtgrouprestorecat'), htmlspecialchars($_SWIFT_TemplateCategoryObject->GetLabel()), htmlspecialchars($_templateGroupCache[$_templateGroupID]['title']), htmlspecialchars($_templateGroupCache[$_templateGroupID]['companyname'])));

        $_SWIFT_TemplateCategoryObject->Restore($_SWIFT->Staff->GetStaffID());

        $this->Load->Manage($_templateGroupID, $_templateCategoryID);

        return true;
    }

    /**
     * Insert a new Template
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID
     * @param int $_templateCategoryID The Template Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Insert($_templateGroupID = 0, $_templateCategoryID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');
        if (!isset($_templateGroupCache[$_templateGroupID])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TemplateCategoryObject = new SWIFT_TemplateCategory($_templateCategoryID);
        if (!$_SWIFT_TemplateCategoryObject instanceof SWIFT_TemplateCategory || !$_SWIFT_TemplateCategoryObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }


        $this->UserInterface->Header($this->Language->Get('templates') . ' > ' . $this->Language->Get('inserttemplate'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tmpcaninserttemplate') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderInsert($_templateGroupID, $_SWIFT_TemplateCategoryObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Insert a new Template
     *
     * @author Varun Shoor
     * @param bool $_saveAndReload Whether to Save and Reload (Redirect to Edit Template)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertSubmit($_saveAndReload = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_saveAndReload = (int)($_saveAndReload);
        $_templateGroupID = (int)($_POST['templategroupid']);
        $_templateCategoryID = (int)($_POST['templatecategoryid']);

        $_templateGroupCache = $this->Cache->Get('templategroupcache');
        if (!isset($_templateGroupCache[$_templateGroupID])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_TemplateCategoryObject = new SWIFT_TemplateCategory($_templateCategoryID);
        if (!$_SWIFT_TemplateCategoryObject instanceof SWIFT_TemplateCategory || !$_SWIFT_TemplateCategoryObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            $this->Load->Insert($_POST['templategroupid'], $_POST['templatecategoryid']);

            return false;
        }

        // END CSRF HASH CHECK

        if (trim($_POST['name']) == '' || trim($_POST['templatecontents']) == '' || trim($_POST['templategroupid']) == '' || trim($_POST['templatecategoryid']) == '') {
            $this->UserInterface->CheckFields('name', 'templatecontents');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->Insert($_POST['templategroupid'], $_POST['templatecategoryid']);

            return false;
        } elseif (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            $this->Load->Insert($_POST['templategroupid'], $_POST['templatecategoryid']);

            return false;
        } elseif ($_SWIFT->Staff->GetPermission('admin_tmpcaninserttemplate') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Insert($_POST['templategroupid'], $_POST['templatecategoryid']);

            return false;
        } elseif (!preg_match("/^[[a-z]|[A-Z]|[0-9]|\_]$/", $_POST['name'])) {
            $this->UserInterface->Error($this->Language->Get('titleinserttemplatechar'), $this->Language->Get('msginserttemplatechar'));

            SWIFT::ErrorField('name');

            $this->Load->Insert($_POST['templategroupid'], $_POST['templatecategoryid']);

            return false;
        }

        $_template = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "templates WHERE name = '" . $this->Database->Escape($_POST['name']) . "' AND tgroupid = '" . $_templateGroupID . "'");
        if (isset($_template['templateid'])) {
            $this->UserInterface->Error($this->Language->Get('titleinserttemplatedupe'), $this->Language->Get('msginserttemplatedupe'));

            SWIFT::ErrorField('name');

            $this->Load->Insert($_POST['templategroupid'], $_POST['templatecategoryid']);

            return false;
        }

        $_templateID = SWIFT_Template::Create($_templateGroupID, $_templateCategoryID, $_POST['name'], $_POST['templatecontents'], $_POST['templatecontents'], true);
        if (!$_templateID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        SWIFT::Info(sprintf($this->Language->Get('titleinserttemplate'), htmlspecialchars($_POST['name'])), sprintf($this->Language->Get('msginserttemplate'), htmlspecialchars($_POST['name']), htmlspecialchars($_templateGroupCache[$_templateGroupID]['title'])));

        if ($_saveAndReload) {
            $this->Load->Edit($_templateID);
        } else {
            $this->Load->Manage($_templateGroupID, $_templateCategoryID);
        }

        SWIFT_CacheManager::EmptyCacheDirectory();

        return true;
    }

    /**
     * Edit the given Template
     *
     * @author Varun Shoor
     * @param int $_templateID The Template ID
     * @param int $_templateHistoryID The Template History ID to Load
     * @param mixed $_isSearch Whether this is a search call
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_templateID, $_templateHistoryID = 0, $_isSearch = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TemplateObject = new SWIFT_Template($_templateID, true);
        if (!$_SWIFT_TemplateObject instanceof SWIFT_Template || !$_SWIFT_TemplateObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!is_numeric($_templateHistoryID)) {
            $_templateHistoryID = 0;
        }

        if (!is_numeric($_isSearch)) {
            $_isSearch = false;
        }

        $this->UserInterface->Header($this->Language->Get('templates') . ' > ' . $this->Language->Get('managetemplates'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanviewtemplates') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderTemplate($_SWIFT_TemplateObject, $_templateHistoryID, '', $_isSearch);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * The Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_templateID The Template ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_templateID, $_saveAndReload = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_saveAndReload = (int)($_saveAndReload);

        $_SWIFT_TemplateObject = new SWIFT_Template($_templateID, true);
        if (!$_SWIFT_TemplateObject instanceof SWIFT_Template || !$_SWIFT_TemplateObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            $this->Load->Manage($_SWIFT_TemplateObject->GetProperty('tgroupid'), $_SWIFT_TemplateObject->GetProperty('tcategoryid'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanupdatetemplate') == '0') {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Manage($_SWIFT_TemplateObject->GetProperty('tgroupid'), $_SWIFT_TemplateObject->GetProperty('tcategoryid'));

            return false;
        }

        if (trim($_POST['templatecontents']) == '') {
            $this->UserInterface->CheckFields('templatecontents');

            SWIFT::Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->Edit($_templateID);

            return false;
        }

        $_updateHistory = false;
        if ($_POST['saveasnewversion'] == '1') {
            $_updateHistory = true;
        }

        $_changeLogNotes = '';
        if ($_POST['changelognotes'] != '') {
            $_changeLogNotes = $_POST['changelognotes'];
        }

        $_SWIFT_TemplateObject->Update($_POST['templatecontents'], $_updateHistory, $_SWIFT->Staff->GetStaffID(), $_changeLogNotes, false);

        SWIFT::Info(sprintf($this->Language->Get('titletemplateupdate'), htmlspecialchars($_SWIFT_TemplateObject->GetProperty('name'))), sprintf($this->Language->Get('msgtemplateupdate'), htmlspecialchars($_SWIFT_TemplateObject->GetProperty('name'))));

        if ($_saveAndReload) {
            $this->Load->Edit($_SWIFT_TemplateObject->GetTemplateID());
        } else {
            $this->Load->Manage($_SWIFT_TemplateObject->GetProperty('tgroupid'), $_SWIFT_TemplateObject->GetProperty('tcategoryid'));
        }

        SWIFT_CacheManager::EmptyCacheDirectory();

        return true;
    }

    /**
     * Restore the Template
     *
     * @author Varun Shoor
     * @param int $_templateID The Template ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Restore($_templateID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TemplateObject = new SWIFT_Template($_templateID);
        if (!$_SWIFT_TemplateObject instanceof SWIFT_Template || !$_SWIFT_TemplateObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanrestoretemplates') == '0') {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Manage($_SWIFT_TemplateObject->GetProperty('tgroupid'), $_SWIFT_TemplateObject->GetProperty('tcategoryid'));

            return false;
        }

        $_SWIFT_TemplateObject->Restore(true, $_SWIFT->Staff->GetStaffID(), '');

        SWIFT::Info(sprintf($this->Language->Get('titletemplaterestore'), htmlspecialchars($_SWIFT_TemplateObject->GetProperty('name'))), sprintf($this->Language->Get('msgtemplaterestore'), htmlspecialchars($_SWIFT_TemplateObject->GetProperty('name'))));

        $this->Load->Edit($_SWIFT_TemplateObject->GetTemplateID());

        SWIFT_CacheManager::EmptyCacheDirectory();

        return true;
    }

    /**
     * Delete the Custom Template
     *
     * @author Varun Shoor
     * @param int $_templateID The Template ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_templateID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TemplateObject = new SWIFT_Template($_templateID);
        if (!$_SWIFT_TemplateObject instanceof SWIFT_Template || !$_SWIFT_TemplateObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanupdatetemplate') == '0' || $_SWIFT_TemplateObject->GetProperty('iscustom') != '1') {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Manage($_SWIFT_TemplateObject->GetProperty('tgroupid'), $_SWIFT_TemplateObject->GetProperty('tcategoryid'));

            return false;
        }

        $_templateGroupID = (int)($_SWIFT_TemplateObject->GetProperty('tgroupid'));
        $_templateCategoryID = (int)($_SWIFT_TemplateObject->GetProperty('tcategoryid'));

        SWIFT_Template::DeleteList(array($_SWIFT_TemplateObject->GetTemplateID()));

        SWIFT::Info($this->Language->Get('titletemplatedel'), sprintf($this->Language->Get('msgtemplatedel'), htmlspecialchars($_SWIFT_TemplateObject->GetProperty('name'))));

        $this->Load->Manage($_templateGroupID, $_templateCategoryID);

        SWIFT_CacheManager::EmptyCacheDirectory();

        return true;
    }

    /**
     * Preview the Template
     *
     * @author Varun Shoor
     * @param int $_templateID The Template ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Preview($_templateID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TemplateObject = new SWIFT_Template($_templateID, true);
        if (!$_SWIFT_TemplateObject instanceof SWIFT_Template || !$_SWIFT_TemplateObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanviewtemplates') == '0') {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Manage($_SWIFT_TemplateObject->GetProperty('tgroupid'), $_SWIFT_TemplateObject->GetProperty('tcategoryid'));

            return false;
        }

        echo preg_replace('#<{\$(.*)}>#sU', '&lt;{$\1}&gt;', $_SWIFT_TemplateObject->GetProperty('contents'));

        return true;
    }

    /**
     * Run a Diff on template
     *
     * @author Varun Shoor
     * @param int $_templateID The Template ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Diff($_templateID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TemplateObject = new SWIFT_Template($_templateID, true);
        if (!$_SWIFT_TemplateObject instanceof SWIFT_Template || !$_SWIFT_TemplateObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset($_POST['comparetemplatehistoryid1']) || !isset($_POST['comparetemplatehistoryid2'])) {
            $this->Load->Edit($_SWIFT_TemplateObject->GetTemplateID());

            return false;
        }

        $_compareTemplateHistoryID1 = (int)($_POST['comparetemplatehistoryid1']);
        $_compareTemplateHistoryID2 = (int)($_POST['comparetemplatehistoryid2']);


        if ($_SWIFT->Staff->GetPermission('admin_tmpcanviewtemplates') == '0') {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Manage($_SWIFT_TemplateObject->GetProperty('tgroupid'), $_SWIFT_TemplateObject->GetProperty('tcategoryid'));

            return false;
        }

        $this->Load->Library('Diff:DiffRenderer', [], true, false, 'base');

        if (empty($_compareTemplateHistoryID1)) {
            $_newText = $_SWIFT_TemplateObject->GetProperty('contents');
        } else {
            $_SWIFT_TemplateHistoryObject = new SWIFT_TemplateHistory($_compareTemplateHistoryID1);
            if (!$_SWIFT_TemplateHistoryObject instanceof SWIFT_TemplateHistory || !$_SWIFT_TemplateHistoryObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_newText = $_SWIFT_TemplateHistoryObject->GetProperty('contents');
        }

        if (empty($_compareTemplateHistoryID2)) {
            $_oldText = $_SWIFT_TemplateObject->GetProperty('contents');
        } else {
            $_SWIFT_TemplateHistoryObject = new SWIFT_TemplateHistory($_compareTemplateHistoryID2);
            if (!$_SWIFT_TemplateHistoryObject instanceof SWIFT_TemplateHistory || !$_SWIFT_TemplateHistoryObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_oldText = $_SWIFT_TemplateHistoryObject->GetProperty('contents');
        }

        $_diffInlineHTML = $this->DiffRenderer->InlineHTML($_oldText, $_newText);

        $this->View->RenderTemplate($_SWIFT_TemplateObject, null, $_diffInlineHTML);

        return true;
    }

    /**
     * Export the Diff in Unified Format
     *
     * @author Varun Shoor
     * @param int $_templateID The Template ID
     * @param int $_compareTemplateHistoryID1 The Comparison Template History ID (1)
     * @param int $_compareTemplateHistoryID2 The Comparison Template History ID (2)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ExportDiff($_templateID, $_compareTemplateHistoryID1, $_compareTemplateHistoryID2)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TemplateObject = new SWIFT_Template($_templateID, true);
        if (!$_SWIFT_TemplateObject instanceof SWIFT_Template || !$_SWIFT_TemplateObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('admin_tmpcanviewtemplates') == '0') {
            SWIFT::Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            $this->Load->Manage($_SWIFT_TemplateObject->GetProperty('tgroupid'), $_SWIFT_TemplateObject->GetProperty('tcategoryid'));

            return false;
        }

        $this->Load->Library('Diff:DiffRenderer', [], true, false, 'base');

        if (empty($_compareTemplateHistoryID1)) {
            $_newText = $_SWIFT_TemplateObject->GetProperty('contents');
        } else {
            $_SWIFT_TemplateHistoryObject = new SWIFT_TemplateHistory($_compareTemplateHistoryID1);
            if (!$_SWIFT_TemplateHistoryObject instanceof SWIFT_TemplateHistory || !$_SWIFT_TemplateHistoryObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_newText = $_SWIFT_TemplateHistoryObject->GetProperty('contents');
        }

        if (empty($_compareTemplateHistoryID2)) {
            $_oldText = $_SWIFT_TemplateObject->GetProperty('contents');
        } else {
            $_SWIFT_TemplateHistoryObject = new SWIFT_TemplateHistory($_compareTemplateHistoryID2);
            if (!$_SWIFT_TemplateHistoryObject instanceof SWIFT_TemplateHistory || !$_SWIFT_TemplateHistoryObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_oldText = $_SWIFT_TemplateHistoryObject->GetProperty('contents');
        }

        $this->DiffRenderer->Export($_SWIFT_TemplateObject->GetProperty('name'), $_oldText, $_newText);

        return true;
    }
}

?>
