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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Tickets\Models\Bayesian\SWIFT_BayesianCategory;

/**
 * The Bayesian Category Controller
 *
 * @author Varun Shoor
 *
 * @property Controller_BayesianCategory $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_BayesianCategory $View
 */
class Controller_BayesianCategory extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 20;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('tickets');
    }

    /**
     * Delete the Bayesian Categories from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_bayesianCategoryIDList The Bayesian Category ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_bayesianCategoryIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_tcandeletebayescategories') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_bayesianCategoryIDList)) {
            $_SWIFT->Database->Query("SELECT category FROM ". TABLE_PREFIX ."bayescategories WHERE bayescategoryid IN (". BuildIN($_bayesianCategoryIDList) .")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletebayescategory'), htmlspecialchars($_SWIFT->Database->Record['category'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_BayesianCategory::DeleteList($_bayesianCategoryIDList);
        }

        return true;
    }

    /**
     * Delete the Given Bayesian Category ID
     *
     * @author Varun Shoor
     * @param int $_bayesianCategoryID The Bayesian Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_bayesianCategoryID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_bayesianCategoryID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Bayesian Category Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('bayesian') . ' > ' . $this->Language->Get('categories'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanviewbayescategories') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Varun Shoor
     * @param int $_mode The User Interface Mode
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RunChecks($_mode)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (trim($_POST['category']) == '' || trim($_POST['categoryweight']) == '')
        {
            $this->UserInterface->CheckFields('category', 'categoryweight');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_tcaninsertbayescategory') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_tcanupdatebayescategory') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Render the UI Confirmation
     *
     * @author Varun Shoor
     * @param mixed $_mode The User Interface Mode
     * @param int $_wordCount The Word Count for the Category being Rendered
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode, $_wordCount)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalText = $this->Language->Get('bayescattitle') . ': ' . htmlspecialchars($_POST['category']) . '<br />';
        $_finalText .= $this->Language->Get('categoryweight') . ': ' . (float)($_POST['categoryweight']) . '<br />';
        $_finalText .= $this->Language->Get('wordcount') . ': ' . number_format($_wordCount, 0).'<br />';

        if ($_mode == SWIFT_UserInterface::MODE_INSERT)
        {
            SWIFT::Info(sprintf($this->Language->Get('titlebayesinsert'), htmlspecialchars($_POST['category'])), sprintf($this->Language->Get('msgbayesinsert'), htmlspecialchars($_POST['category'])) . '<br />' . $_finalText);
        } else if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            SWIFT::Info(sprintf($this->Language->Get('titlebayesupdate'), htmlspecialchars($_POST['category'])), sprintf($this->Language->Get('msgbayesupdate'), htmlspecialchars($_POST['category'])) . '<br />' . $_finalText);
        }

        return true;
    }

    /**
     * Insert a new Bayesian Category
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Insert()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('bayesian') . ' > ' . $this->Language->Get('insertcategory'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcaninsertbayescategory') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Insert Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertSubmit()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT))
        {
            $_bayesianCategoryID = SWIFT_BayesianCategory::Create($_POST['category'], $_POST['categoryweight']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertbayescategory'), htmlspecialchars($_POST['category'])), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_bayesianCategoryID)
            {
                // @codeCoverageIgnoreStart
                // this code will never be reached
                return false;
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT, 0);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Bayesian Category ID
     *
     * @author Varun Shoor
     * @param int $_bayesianCategoryID The Bayesian Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_bayesianCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_bayesianCategoryID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_BayesianCategoryObject = new SWIFT_BayesianCategory($_bayesianCategoryID);
        if (!$_SWIFT_BayesianCategoryObject instanceof SWIFT_BayesianCategory || !$_SWIFT_BayesianCategoryObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be reached
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->UserInterface->Header($this->Language->Get('bayesian') . ' > ' . $this->Language->Get('editcategory'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanupdatebayescategory') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_BayesianCategoryObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_bayesianCategoryID The Bayesian Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_bayesianCategoryID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_bayesianCategoryID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_BayesianCategoryObject = new SWIFT_BayesianCategory($_bayesianCategoryID);
        if (!$_SWIFT_BayesianCategoryObject instanceof SWIFT_BayesianCategory || !$_SWIFT_BayesianCategoryObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be reached
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT))
        {
            $_updateResult = $_SWIFT_BayesianCategoryObject->Update($_POST['category'], $_POST['categoryweight']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatebayescategory'), htmlspecialchars($_POST['category'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // this code will never be reached
                return false;
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_BayesianCategoryObject->GetProperty('wordcount'));

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_bayesianCategoryID);

        return false;
    }
}
