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

namespace Tickets\Staff;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_StaffBase;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use Tickets\Models\Macro\SWIFT_MacroCategory;
use Tickets\Models\Macro\SWIFT_MacroReply;
use Base\Models\SearchStore\SWIFT_SearchStore;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Tickets\Library\View\SWIFT_TicketViewRenderer;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;

/**
 * The Macro Category Controller
 *
 * @property Controller_MacroCategory $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_MacroCategory $View
 * @author Varun Shoor
 */
class Controller_MacroCategory extends Controller_StaffBase
{
    // Core Constants
    const MENU_ID = 2;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_STAFF);

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');
    }

    /**
     * Delete the Macro Categories from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_macroCategoryIDList The Macro Category ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_macroCategoryIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_tcandeletemacro') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_macroCategoryIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "macrocategories WHERE macrocategoryid IN (" . BuildIN($_macroCategoryIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletemacrocategory'), htmlspecialchars($_SWIFT->Database->Record['title'])),
                        SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_MacroCategory::DeleteList($_macroCategoryIDList);
        }

        return true;
    }

    /**
     * Delete the Given Macro Category ID
     *
     * @author Varun Shoor
     * @param int $_macroCategoryID The Macro Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_macroCategoryID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_macroCategoryID), true);

        $this->Load->Manage(false, false);

        return true;
    }

    /**
     * Delete the Macro Replies from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_macroReplyIDList The Macro Reply ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteReplyList($_macroReplyIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        SWIFT::Set('displayreplytab', true);

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_tcandeletemacro') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_macroReplyIDList)) {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "macroreplies WHERE macroreplyid IN (" . BuildIN($_macroReplyIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletemacroreply'), htmlspecialchars($_SWIFT->Database->Record['subject'])),
                        SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            SWIFT_MacroReply::DeleteList($_macroReplyIDList);
        }

        return true;
    }

    /**
     * Delete the Given Macro Reply ID
     *
     * @author Varun Shoor
     * @param int $_macroReplyID The Macro Reply ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DeleteReply($_macroReplyID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteReplyList(array($_macroReplyID), true);

        $this->Load->Manage(false, true);

        return true;
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

        $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), SWIFT_TicketViewRenderer::RenderTree('none', -1, -1, -1));

        return true;
    }

    /**
     * Displays the Macro Categories
     *
     * @author Varun Shoor
     * @param bool $_isReplyTabSelected Whether the reply tab is selected by default
     * @return bool "true" on Success, "false" otherwise
     */
    public function Index($_isReplyTabSelected = false)
    {
        return $this->Load->Manage(false, $_isReplyTabSelected);
    }

    /**
     * Displays the Macro Categories
     *
     * @author Varun Shoor
     * @param bool|int $_searchStoreID (OPTIONAL) The Search Store ID
     * @param mixed $_isReplyTabSelected (OPTIONAL) Whether the reply tab is selected by default
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_searchStoreID = false, $_isReplyTabSelected = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (is_numeric($_isReplyTabSelected) || is_bool($_isReplyTabSelected))
        {
            $_isReplyTabSelected = (int) ($_isReplyTabSelected);
        } else {
            $_isReplyTabSelected = false;
        }

        if (!is_numeric($_searchStoreID))
        {
            $_searchStoreID = false;
        }

        if (isset($_POST['itemid']) || SWIFT::Get('displayreplytab') == true)
        {
            $_isReplyTabSelected = true;
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('macros'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_tcanviewmacro') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderTabs($_isReplyTabSelected, $_searchStoreID);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Varun Shoor
     * @param int $_mode The User Interface Mode
     * @param SWIFT_MacroCategory $_SWIFT_MacroCategoryObject (OPTIONAL) The Macro Category Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RunChecks($_mode, SWIFT_MacroCategory $_SWIFT_MacroCategoryObject = null)
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

        if (trim($_POST['title']) === '' || trim($_POST['parentcategoryid']) === '' || trim($_POST['categorytype']) === '')
        {
            $this->UserInterface->CheckFields('title', 'parentcategoryid', 'categorytype');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('staff_tcaninsertmacro') == '0') ||
                ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('staff_tcanupdatemacro') == '0')) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        if ($_SWIFT_MacroCategoryObject instanceof SWIFT_MacroCategory && $_SWIFT_MacroCategoryObject->GetIsClassLoaded())
        {
            $_subMacroCategoryIDList = SWIFT_MacroCategory::RetrieveSubCategoryIDList(array($_SWIFT_MacroCategoryObject->GetMacroCategoryID()));

            if (($_POST['parentcategoryid'] != '0' && in_array($_POST['parentcategoryid'], $_subMacroCategoryIDList)) || $_POST['parentcategoryid'] == $_SWIFT_MacroCategoryObject->GetMacroCategoryID())
            {
                $this->UserInterface->Error($this->Language->Get('titleinvalidparentcat'), $this->Language->Get('msginvalidparentcat'));

                return false;
            }
        }

        return true;
    }

    /**
     * Insert a new Macro Category
     *
     * @author Varun Shoor
     * @param bool|int $_selectedMacroCategoryID (OPTIONAL) The Selected Macro Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Insert($_selectedMacroCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_LoadDisplayData();

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('insertmacro'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_tcaninsertmacro') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT, null, $_selectedMacroCategoryID);
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

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        if ($_POST['parentcategoryid'] == '0')
        {
            $_parentCategoryTitle = $this->Language->Get('parentcategoryitem');
        } else {
            $_SWIFT_MacroCategoryObject = new SWIFT_MacroCategory(new SWIFT_DataID($_POST['parentcategoryid']));
            if (!$_SWIFT_MacroCategoryObject instanceof SWIFT_MacroCategory || !$_SWIFT_MacroCategoryObject->GetIsClassLoaded())
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $_parentCategoryTitle = $_SWIFT_MacroCategoryObject->GetProperty('title');
        }

        $_finalText = '<b>' . $this->Language->Get('macrocategorytitle') . ':</b> ' . htmlspecialchars($_POST['title']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('parentcategory') . ':</b> ' . htmlspecialchars($_parentCategoryTitle) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('categorytype') . ':</b> ' . IIF($_POST['categorytype'] == '1', $this->Language->Get('public'), $this->Language->Get('private')) . '<br />';

        SWIFT::Info(sprintf($this->Language->Get('titlemacrocategory' . $_type), htmlspecialchars($_POST['title'])),
                sprintf($this->Language->Get('msgmacrocategory' . $_type), htmlspecialchars($_POST['title'])) . '<br />' . $_finalText);

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
            $_macroCategoryID = SWIFT_MacroCategory::Create($_POST['title'], IIF($_POST['categorytype']=='1', SWIFT_MacroCategory::TYPE_PUBLIC, SWIFT_MacroCategory::TYPE_PRIVATE),
                    (int) ($_POST['parentcategoryid']), $_POST['restrictstaffgroupid'], $_SWIFT->Staff);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertmacrocategory'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_INSERT,
                    SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_macroCategoryID)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage(false, false);

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Edit the Macro Category ID
     *
     * @author Varun Shoor
     * @param int $_macroCategoryID The Macro Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_macroCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_macroCategoryID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_MacroCategoryObject = new SWIFT_MacroCategory(new SWIFT_DataID($_macroCategoryID));
        if (!$_SWIFT_MacroCategoryObject instanceof SWIFT_MacroCategory || !$_SWIFT_MacroCategoryObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('editmacro'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_tcanupdatemacro') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_MacroCategoryObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     * @param int $_macroCategoryID The Macro Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_macroCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_macroCategoryID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_MacroCategoryObject = new SWIFT_MacroCategory(new SWIFT_DataID($_macroCategoryID));
        if (!$_SWIFT_MacroCategoryObject instanceof SWIFT_MacroCategory || !$_SWIFT_MacroCategoryObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_MacroCategoryObject))
        {
            $_updateResult = $_SWIFT_MacroCategoryObject->Update($_POST['title'], (int) ($_POST['categorytype']), (int) ($_POST['parentcategoryid']), $_POST['restrictstaffgroupid'], $_SWIFT->Staff);

            SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatemacrocategory'), htmlspecialchars($_POST['title'])), SWIFT_StaffActivityLog::ACTION_UPDATE,
                    SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_STAFF);

            if (!$_updateResult)
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage(false, false);

            return true;
        }

        $this->Load->Edit($_macroCategoryID);

        return false;
    }

    /**
     * Quick Filter Options
     *
     * @author Varun Shoor
     * @param string $_filterType The Filter Type
     * @param string $_filterValue The Filter Value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function QuickReplyFilter($_filterType, $_filterValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_searchStoreID = -1;

        $_macroReplyIDList = array();

        $_gridSortContainer = SWIFT_UserInterfaceGrid::GetGridSortField('macrogrid', 'macroreplies.subject', 'asc');

        switch ($_filterType)
        {
            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-657 'Filter Replies' of public category under "Macros" show the replies which belong to private category
             *
             * Comments: Was missing an enclosing bracket around check for categorytype
             */
            case 'category': {
                if ($_filterValue == SWIFT_MacroCategory::CATEGORY_ROOT) {
                    $this->Database->QueryLimit("SELECT macroreplies.macroreplyid FROM swmacroreplies AS macroreplies WHERE macroreplies.macrocategoryid = '" . SWIFT_MacroCategory::CATEGORY_ROOT . "' ORDER BY macroreplies.subject " . $_gridSortContainer[1], 100);
                } else {
                    $this->Database->QueryLimit("SELECT macroreplies.macroreplyid FROM " . TABLE_PREFIX . "macroreplies AS macroreplies
                    LEFT JOIN " . TABLE_PREFIX . "macrocategories AS macrocategories ON (macroreplies.macrocategoryid = macrocategories.macrocategoryid)
                    WHERE macroreplies.macrocategoryid = '" . (int) ($_filterValue) . "'
                    AND (macrocategories.categorytype = '" . SWIFT_MacroCategory::TYPE_PUBLIC . "' OR (macrocategories.categorytype = '" . SWIFT_MacroCategory::TYPE_PRIVATE . "' AND macrocategories.staffid = '" . $_SWIFT->Staff->GetStaffID() . "'))
                    ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    while ($this->Database->NextRecord())
                    {
                        $_macroReplyIDList[] = $this->Database->Record['macroreplyid'];
                    }
                }

            }
            break;

            default:
                break;
        }

        if (_is_array($_macroReplyIDList))
        {
            $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_MACROREPLY, $_macroReplyIDList, $_SWIFT->Staff->GetStaffID());
        } else {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Manage($_searchStoreID, true);

        return true;
    }
}
