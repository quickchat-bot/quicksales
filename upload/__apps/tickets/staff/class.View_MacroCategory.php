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

namespace Tickets\Staff;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Tickets\Models\Macro\SWIFT_MacroCategory;
use Tickets\Library\Macro\SWIFT_MacroManager;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Macro Category View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_MacroCategory extends SWIFT_View
{
    /**
     * Render the Macro Category Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_MacroCategory $_SWIFT_MacroCategoryObject The SWIFT_MacroCategory Object Pointer (Only for EDIT
     *     Mode)
     * @param int|bool $_selectedMacroCategoryIDArg (OPTIONAL) The Selected Macro Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_MacroCategory $_SWIFT_MacroCategoryObject = null, $_selectedMacroCategoryIDArg = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/MacroCategory/EditSubmit/' . $_SWIFT_MacroCategoryObject->GetMacroCategoryID(), SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Tickets/MacroCategory/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, true);
        }

        $_macroCategoryTitle = '';
        $_macroCategoryType = true;
        $_selectedMacroCategoryID = 0;
        $_restrictStaffGroupID = 0;
        $_activeMacroCategoryID = false;

        if (!empty($_selectedMacroCategoryIDArg))
        {
            $_selectedMacroCategoryID = (int) ($_selectedMacroCategoryIDArg);
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            if (isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Tickets/MacroCategory/Delete/' . $_SWIFT_MacroCategoryObject->GetMacroCategoryID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmacro'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_macroCategoryTitle = $_SWIFT_MacroCategoryObject->GetProperty('title');
            $_macroCategoryType = (int) ($_SWIFT_MacroCategoryObject->GetProperty('categorytype'));

            $_restrictStaffGroupID = (int) ($_SWIFT_MacroCategoryObject->GetProperty('restrictstaffgroupid'));
            $_selectedMacroCategoryID = (int) ($_SWIFT_MacroCategoryObject->GetProperty('parentcategoryid'));

            $_activeMacroCategoryID = $_SWIFT_MacroCategoryObject->GetMacroCategoryID();
        } else {
            if (isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmacro'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('macrocategorytitle'), $this->Language->Get('desc_macrocategorytitle'), $_macroCategoryTitle);

        $_GeneralTabObject->Select('parentcategoryid', $this->Language->Get('parentcategory'), $this->Language->Get('desc_parentcategory'), SWIFT_MacroManager::GetMacroCategoryOptions($_selectedMacroCategoryID, $_activeMacroCategoryID));

        $_GeneralTabObject->PublicPrivate('categorytype', $this->Language->Get('categorytype'), $this->Language->Get('desc_categorytype'), (int) ($_macroCategoryType));

        $_optionsContainer = array();
        $_index = 1;
        $_optionsContainer[0]['title'] = $this->Language->Get('reststaffgroupall');
        $_optionsContainer[0]['value'] = 0;
        if ($_restrictStaffGroupID == 0)
        {
            $_optionsContainer[0]['selected'] = true;
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroup ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['staffgroupid'];

            if ($this->Database->Record['staffgroupid'] == $_restrictStaffGroupID)
            {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_GeneralTabObject->Select('restrictstaffgroupid', $this->Language->Get('restrictstaffgroup'), $this->Language->Get('desc_restrictstaffgroup'), $_optionsContainer);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Macro Tabs
     *
     * @author Varun Shoor
     * @param bool $_isReplyTabSelected (OPTIONAL) Whether the replies tab is selected by default
     * @param int|bool $_searchStoreID (OPTIONAL) The optional search store id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderTabs($_isReplyTabSelected = false, $_searchStoreID = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        if (!isset($_POST['_searchQuery']) && !isset($_POST['_sortBy']))
        {
            $this->UserInterface->Start();

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insertcategory'), 'fa-folder-o', "InsertMacroCategoryWindow(0);", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('insertmacro'), 'fa-commenting-o', 'InsertMacroReplyWindow(0);', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmacro'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            /*
             * ###############################################
             * BEGIN CATEGORIES TAB
             * ###############################################
             */
            $_CategoriesTabObject = $this->UserInterface->AddTab($this->Language->Get('tabcategories'), 'icon_folderyellow3.gif', 'categories', IIF($_isReplyTabSelected == false, true, false));

            $_CategoriesTabObject->RowHTML('<tr><td align="left" valign="top">' . SWIFT_MacroManager::GetMacroCategoryTree(0) . '</td></tr>');

            /*
             * ###############################################
             * BEGIN MACROS TAB
             * ###############################################
             */
            $_MacrosTabObject = $this->UserInterface->AddTab($this->Language->Get('tabmacros'), 'icon_form.gif', 'replies', IIF($_isReplyTabSelected == true, true, false), false, 0);
            $_MacrosTabObject->LoadToolbar();
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('macrogrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT macroreplies.*, macrocategories.title AS categorytitle FROM ' . TABLE_PREFIX . 'macroreplies AS macroreplies
                LEFT JOIN ' . TABLE_PREFIX . 'macroreplydata AS macroreplydata ON (macroreplies.macroreplyid = macroreplydata.macroreplyid)
                LEFT JOIN ' . TABLE_PREFIX . 'macrocategories AS macrocategories ON (macroreplies.macrocategoryid = macrocategories.macrocategoryid)
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('macroreplies.subject') . ' OR ' . $this->UserInterfaceGrid->BuildSQLSearch('macroreplydata.contents') . ')
                AND (macrocategories.categorytype = \'' . SWIFT_MacroCategory::TYPE_PUBLIC . '\' OR macroreplies.macrocategoryid = \'0\' OR  (macrocategories.categorytype = \'' . SWIFT_MacroCategory::TYPE_PRIVATE . '\' AND macrocategories.staffid = \'' . $_SWIFT->Staff->GetStaffID() . '\'))',

                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'macroreplies AS macroreplies
                LEFT JOIN ' . TABLE_PREFIX . 'macroreplydata AS macroreplydata ON (macroreplies.macroreplyid = macroreplydata.macroreplyid)
                LEFT JOIN ' . TABLE_PREFIX . 'macrocategories AS macrocategories ON (macroreplies.macrocategoryid = macrocategories.macrocategoryid)
                WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('macroreplies.subject') . ' OR ' . $this->UserInterfaceGrid->BuildSQLSearch('macroreplydata.contents') . ')
                AND (macrocategories.categorytype = \'' . SWIFT_MacroCategory::TYPE_PUBLIC . '\' OR macroreplies.macrocategoryid = \'0\' OR  (macrocategories.categorytype = \'' . SWIFT_MacroCategory::TYPE_PRIVATE . '\' AND macrocategories.staffid = \'' . $_SWIFT->Staff->GetStaffID() . '\'))');
        }

        $this->UserInterfaceGrid->SetSearchStoreOptions($_searchStoreID, 'SELECT macroreplies.*, macrocategories.title AS categorytitle FROM ' . TABLE_PREFIX . 'macroreplies AS macroreplies
            LEFT JOIN ' . TABLE_PREFIX . 'macroreplydata AS macroreplydata ON (macroreplies.macroreplyid = macroreplydata.macroreplyid)
            LEFT JOIN ' . TABLE_PREFIX . 'macrocategories AS macrocategories ON (macroreplies.macrocategoryid = macrocategories.macrocategoryid)
            WHERE macroreplies.macroreplyid IN (%s)', SWIFT_SearchStore::TYPE_MACROREPLY);

        $this->UserInterfaceGrid->SetQuery('SELECT macroreplies.*, macrocategories.title AS categorytitle FROM ' . TABLE_PREFIX . 'macroreplies AS macroreplies
            LEFT JOIN ' . TABLE_PREFIX . 'macroreplydata AS macroreplydata ON (macroreplies.macroreplyid = macroreplydata.macroreplyid)
            LEFT JOIN ' . TABLE_PREFIX . 'macrocategories AS macrocategories ON (macroreplies.macrocategoryid = macrocategories.macrocategoryid)
            WHERE macrocategories.categorytype = \'' . SWIFT_MacroCategory::TYPE_PUBLIC . '\' OR macroreplies.macrocategoryid = \'0\' OR (macrocategories.categorytype = \'' . SWIFT_MacroCategory::TYPE_PRIVATE . '\' AND macrocategories.staffid = \'' . $_SWIFT->Staff->GetStaffID() . '\')',

            'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'macroreplies AS macroreplies
            LEFT JOIN ' . TABLE_PREFIX . 'macroreplydata AS macroreplydata ON (macroreplies.macroreplyid = macroreplydata.macroreplyid)
            LEFT JOIN ' . TABLE_PREFIX . 'macrocategories AS macrocategories ON (macroreplies.macrocategoryid = macrocategories.macrocategoryid)
            WHERE macrocategories.categorytype = \'' . SWIFT_MacroCategory::TYPE_PUBLIC . '\' OR macroreplies.macrocategoryid = \'0\' OR  (macrocategories.categorytype = \'' . SWIFT_MacroCategory::TYPE_PRIVATE . '\' AND macrocategories.staffid = \'' . $_SWIFT->Staff->GetStaffID() . '\')');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('macroreplyid', 'macroreplyid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('macroreplies.subject', $this->Language->Get('macrotitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('macroreplies.totalhits', $this->Language->Get('replytotalhits'), SWIFT_UserInterfaceGridField::TYPE_DB, 60, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('macroreplies.lastusage', $this->Language->Get('replylastused'), SWIFT_UserInterfaceGridField::TYPE_DB, 100, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('macrocategories.title', $this->Language->Get('macrocategorytitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 230, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash', array('Tickets\Staff\Controller_MacroCategory', 'DeleteReplyList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->Render();

        $_javascriptAppendHTML = '<script type="text/javascript">QueueFunction(function(){ $(\'#qireply_macromenucontainer, #qiforward_macromenucontainer, #qinewticket_macromenucontainer\').remove(); });</script>';
        $this->UserInterface->AppendHTML($_javascriptAppendHTML);

        if (!isset($_POST['_searchQuery']) && !isset($_POST['_sortBy']) && isset($_MacrosTabObject))
        {
            $_MacrosTabObject->RowHTML('<tr><td align="left" valign="top">' . $this->UserInterfaceGrid->GetRenderData() . '</td></tr>');

            $this->UserInterface->End();
        } else {
            $this->UserInterfaceGrid->Display();
        }

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array The Processed Field Container Array
     * @throws SWIFT_Exception
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_icon = 'fa-commenting';

        $_fieldContainer['icon'] = '<i class="fa '. $_icon .'" aria-hidden="true"></i>';

        $_fieldContainer['macroreplies.subject'] = '<a href="' . "javascript: UICreateWindow('" . SWIFT::Get('basename') . "/Tickets/MacroReply/Edit/" . (int) ($_fieldContainer['macroreplyid']) . "', 'editmacroreply', '" . $_SWIFT->Language->Get('edit') . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 680, 630, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['subject']) . '</a>';

        $_categoryTitle = $_SWIFT->Language->Get('parentcategoryitem');
        if (isset($_fieldContainer['categorytitle']) && !empty($_fieldContainer['categorytitle']))
        {
            $_categoryTitle = $_fieldContainer['categorytitle'];
        }

        $_fieldContainer['macrocategories.title'] = htmlspecialchars($_categoryTitle);
        $_fieldContainer['macroreplies.totalhits'] = (int) ($_fieldContainer['totalhits']);

        $_lastUsage = $_SWIFT->Language->Get('na');
        if (!empty($_fieldContainer['lastusage']))
        {
            $_lastUsage = SWIFT_Date::ColorTime(DATENOW-$_fieldContainer['lastusage']);
        }
        $_fieldContainer['macroreplies.lastusage'] = $_lastUsage;

        return $_fieldContainer;
    }
}
