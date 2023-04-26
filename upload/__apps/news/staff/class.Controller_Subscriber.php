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

namespace News\Staff;

use Base\Admin\Controller_Staff;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use News\Library\Render\SWIFT_NewsRenderManager;
use News\Models\Category\SWIFT_NewsCategory;
use News\Models\NewsItem\SWIFT_NewsItem;
use News\Models\Subscriber\SWIFT_NewsSubscriber;
use SWIFT;
use Base\Models\Comment\SWIFT_Comment;
use SWIFT_Exception;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The Subscriber Controller
 *
 * @author Varun Shoor
 *
 * @method Library($_libraryName)
 * @property Controller_Subscriber $Load
 * @property SWIFT_NewsRenderManager $NewsRenderManager
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Subscriber $View
 */
class Controller_Subscriber extends \Controller_StaffBase
{
    // Core Constants
    const MENU_ID = 7;
    const NAVIGATION_ID = 1;

    protected $sendEmails = true;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_STAFF);

        $this->Load->Library('Render:NewsRenderManager');

        $this->Language->Load('staff_news');
        $this->Language->Load('staff_newssubscribers');
    }

    /**
     * Loads the Display Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _LoadDisplayData()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), $this->NewsRenderManager->RenderTree());

        return true;
    }

    /**
     * Delete the News Subscribers from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_newsSubscriberIDList The News Subscriber ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_newsSubscriberIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_nwcandeletesubscriber') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_newsSubscriberIDList)) {
            $_SWIFT->Database->Query("SELECT email FROM " . TABLE_PREFIX . "newssubscribers WHERE newssubscriberid IN (" . BuildIN($_newsSubscriberIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletenewssubscriber'),
                        htmlspecialchars($_SWIFT->Database->Record['email'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                        SWIFT_StaffActivityLog::SECTION_NEWS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_NewsSubscriber::DeleteList($_newsSubscriberIDList);
        }

        return true;
    }

    /**
     * Delete the Given News Subscriber ID
     *
     * @author Varun Shoor
     * @param int $_newsSubscriberID The News Subscriber ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_newsSubscriberID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_newsSubscriberID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the News Subscriber Grid
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

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('news') . ' > ' . $this->Language->Get('subscribers'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_nwcanviewsubscribers') == '0')
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
     * @param int $_newsSubscriberID The News Subscriber ID ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode, $_newsSubscriberID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }


        /*
        * BUG FIX - Anjali Sharma
        *
        * SWIFT-3608: Inserting a news subscriber with a space at the end, causes an error "Invalid Data, Unable to proceed".
        */
        $_newsSubscriberEmail = trim($_POST['email']);

        if ($_newsSubscriberEmail == '' || !IsEmailValid($_newsSubscriberEmail))
        {
            $this->UserInterface->CheckFields('email');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('staff_nwcaninsertsubscriber') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('staff_nwcanupdatesubscriber') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        $_newsSubscriberContainer = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "newssubscribers
            WHERE email = '" . $this->Database->Escape(mb_strtolower($_newsSubscriberEmail)) . "'");
        if (isset($_newsSubscriberContainer['newssubscriberid']) && $_newsSubscriberContainer['newssubscriberid'] != $_newsSubscriberID)
        {
            $this->UserInterface->Error(sprintf($this->Language->Get('titleemailmismatch'),
                    htmlspecialchars($_newsSubscriberContainer['email'])), sprintf($this->Language->Get('msgemailmismatch'),
                            htmlspecialchars($_newsSubscriberContainer['email'])));

            return false;
        }

        return true;
    }

    /**
     * Insert a new Subscriber
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

        $this->UserInterface->Header($this->Language->Get('news') . ' > ' . $this->Language->Get('insertsubscriber'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_nwcaninsertsubscriber') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Render the Confirmation for InsertSubmit/EditSubmit
     *
     * @author Varun Shoor
     * @param mixed $_mode The User Interface Mode
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_type = 'insert';
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $_type = 'update';
        }

        SWIFT::Info(sprintf($this->Language->Get('titlesubscriber' . $_type), htmlspecialchars($_POST['email'])),
                sprintf($this->Language->Get('msgsubscriber' . $_type), htmlspecialchars($_POST['email'])));

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
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT))
        {
            $_newsSubscriberID = SWIFT_NewsSubscriber::Create($_POST['email'], true, 0, 0, $this->sendEmails);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertsubscriber'), htmlspecialchars($_POST['email'])),
                    SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_NEWS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_newsSubscriberID)
            {
                // @codeCoverageIgnoreStart
                // This code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the News Subscriber
     *
     * @author Varun Shoor
     * @param int $_newsSubscriberID The News Subscriber ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_newsSubscriberID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_newsSubscriberID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_NewsSubscriberObject = new SWIFT_NewsSubscriber($_newsSubscriberID);
        if (!$_SWIFT_NewsSubscriberObject instanceof SWIFT_NewsSubscriber || !$_SWIFT_NewsSubscriberObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->UserInterface->Header($this->Language->Get('news') . ' > ' . $this->Language->Get('editsubscriber'), self::MENU_ID,
                self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_nwcanupdatesubscriber') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_NewsSubscriberObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_newsSubscriberID The News Subscriber ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_newsSubscriberID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_newsSubscriberID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_NewsSubscriberObject = new SWIFT_NewsSubscriber($_newsSubscriberID);
        if (!$_SWIFT_NewsSubscriberObject instanceof SWIFT_NewsSubscriber || !$_SWIFT_NewsSubscriberObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_newsSubscriberID))
        {
            $_updateResult = $_SWIFT_NewsSubscriberObject->Update($_POST['email']);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatesubscriber'), htmlspecialchars($_POST['email'])),
                    SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_NEWS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // This code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_newsSubscriberID);

        return false;
    }
}
