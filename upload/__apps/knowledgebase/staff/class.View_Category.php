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

namespace Knowledgebase\Staff;

use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Knowledgebase\Library\Category\SWIFT_KnowledgebaseCategoryManager;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use SWIFT_Exception;
use SWIFT_View;

/**
 * The Knowledgebase Category View
 *
 * @author Varun Shoor
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_UserInterfaceGrid $UserInterfaceGrid
 */
class View_Category extends SWIFT_View
{
    /**
     * Render the Knowledgebase Category Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_KnowledgebaseCategory $_SWIFT_KnowledgebaseCategoryObject The SWIFT_KnowledgebaseCategory Object Pointer (Only for EDIT Mode)
     * @param bool $_selectedKnowledgebaseCategoryIDArg
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Knowledgebase\Models\Category\SWIFT_Category_Exception
     */
    public function Render($_mode, SWIFT_KnowledgebaseCategory $_SWIFT_KnowledgebaseCategoryObject = null, $_selectedKnowledgebaseCategoryIDArg = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_KnowledgebaseCategoryObject !== null)
        {
            $this->UserInterface->Start(get_short_class($this),'/Knowledgebase/Category/EditSubmit/' . $_SWIFT_KnowledgebaseCategoryObject->GetKnowledgebaseCategoryID(), SWIFT_UserInterface::MODE_EDIT, true);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/Knowledgebase/Category/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, true);
        }

        $_categoryTitle = '';
        $_categoryType = true;
        $_selectedKnowledgebaseCategoryID = 0;
        $_displayOrder = SWIFT_KnowledgebaseCategory::GetLastDisplayOrder();
        $_userVisibilityCustom = $_staffVisibilityCustom = false;
        $_userGroupIDList = $_staffGroupIDList = array();
        $_isPublished = $_allowComments = $_allowRating = true;

        $_articleSortOrder = SWIFT_KnowledgebaseCategory::SORT_INHERIT;

        $_activeKnowledgebaseCategoryID = false;

        if (!empty($_selectedKnowledgebaseCategoryIDArg))
        {
            $_selectedKnowledgebaseCategoryID = ($_selectedKnowledgebaseCategoryIDArg);
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_KnowledgebaseCategoryObject !== null)
        {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Knowledgebase/Category/Delete/' . $_SWIFT_KnowledgebaseCategoryObject->GetKnowledgebaseCategoryID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('kbcategory'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_categoryTitle = $_SWIFT_KnowledgebaseCategoryObject->GetProperty('title');
            $_categoryType = ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype'));

            $_selectedKnowledgebaseCategoryID = ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('parentkbcategoryid'));
            $_displayOrder = ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('displayorder'));
            $_userVisibilityCustom = ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('uservisibilitycustom'));
            $_staffVisibilityCustom = ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('staffvisibilitycustom'));
            $_isPublished = ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('ispublished'));
            $_allowComments = ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('allowcomments'));
            $_allowRating = ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('allowrating'));
            $_articleSortOrder = ($_SWIFT_KnowledgebaseCategoryObject->GetProperty('articlesortorder'));
            $_activeKnowledgebaseCategoryID = $_SWIFT_KnowledgebaseCategoryObject->GetKnowledgebaseCategoryID();

            $_userGroupIDList = $_SWIFT_KnowledgebaseCategoryObject->GetLinkedUserGroupIDList();
            $_staffGroupIDList = $_SWIFT_KnowledgebaseCategoryObject->GetLinkedStaffGroupIDList();
        } else {
            if ((isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) || $this->UserInterface->IsAjax() == false)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
            }

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('kbcategory'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('categorytitle'), $this->Language->Get('desc_categorytitle'), $_categoryTitle);

        $_GeneralTabObject->Select('parentkbcategoryid', $this->Language->Get('parentcategory'), $this->Language->Get('desc_parentcategory'), SWIFT_KnowledgebaseCategoryManager::GetCategoryOptions(array($_selectedKnowledgebaseCategoryID), $_activeKnowledgebaseCategoryID));

        $_radioContainer = array();
        $_index = 0;
        $_categoryTypeDesc = '';
        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_selectedKnowledgebaseCategoryIDArg != 0) || ($_mode == SWIFT_UserInterface::MODE_EDIT && ($_selectedKnowledgebaseCategoryID != '0' || $_SWIFT_KnowledgebaseCategoryObject->GetProperty('categorytype') == SWIFT_KnowledgebaseCategory::TYPE_INHERIT)))
        {
            $_categoryTypeDesc .= $this->Language->Get('desc_categorytypeinherit');
            $_radioContainer[$_index]['title'] = $this->Language->Get('inherit');
            $_radioContainer[$_index]['value'] = SWIFT_KnowledgebaseCategory::TYPE_INHERIT;

            if ($_mode == SWIFT_UserInterface::MODE_INSERT || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_categoryType == SWIFT_KnowledgebaseCategory::TYPE_INHERIT))
            {
                $_radioContainer[$_index]['checked'] = true;
            }
            $_index++;
        }

        $_radioContainer[$_index]['title'] = $this->Language->Get('global');
        $_radioContainer[$_index]['value'] = SWIFT_KnowledgebaseCategory::TYPE_GLOBAL;
        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_selectedKnowledgebaseCategoryIDArg == 0) || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_categoryType == SWIFT_KnowledgebaseCategory::TYPE_GLOBAL))
        {
            $_radioContainer[$_index]['checked'] = true;
        }
        $_index++;

        $_radioContainer[$_index]['title'] = $this->Language->Get('public');
        $_radioContainer[$_index]['value'] = SWIFT_KnowledgebaseCategory::TYPE_PUBLIC;
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_categoryType == SWIFT_KnowledgebaseCategory::TYPE_PUBLIC)
        {
            $_radioContainer[$_index]['checked'] = true;
        }
        $_index++;

        $_radioContainer[$_index]['title'] = $this->Language->Get('private');
        $_radioContainer[$_index]['value'] = SWIFT_KnowledgebaseCategory::TYPE_PRIVATE;
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_categoryType == SWIFT_KnowledgebaseCategory::TYPE_PRIVATE)
        {
            $_radioContainer[$_index]['checked'] = true;
        }
        $_categoryTypeDesc .= $this->Language->Get('desc_categorytype');
        $_GeneralTabObject->Radio('categorytype', $this->Language->Get('categorytype'), $_categoryTypeDesc, $_radioContainer, true, 'HandleKBCategoryType();');

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        /*
         * ###############################################
         * BEGIN OPTIONS TAB
         * ###############################################
         */

        $_OptionsTabObject = $this->UserInterface->AddTab($this->Language->Get('taboptions'), 'icon_settings2.gif', 'options', false);

        $_OptionsTabObject->Hidden('ispublished', '1');

//        $_OptionsTabObject->YesNo('ispublished', $this->Language->Get('ispublished'), $this->Language->Get('desc_ispublished'), $_isPublished);

        $_OptionsTabObject->Number('displayorder', $this->Language->Get('displayorder'), $this->Language->Get('desc_displayorder'), ($_displayOrder));

        $_optionsContainer = array();
        $_index = 0;

        $_optionsContainer[0]['title'] = $this->Language->Get('as_inherit');
        $_optionsContainer[0]['value'] = SWIFT_KnowledgebaseCategory::SORT_INHERIT;
        if ($_articleSortOrder == SWIFT_KnowledgebaseCategory::SORT_INHERIT)
        {
            $_optionsContainer[0]['selected'] = true;
        }

        $_optionsContainer[2]['title'] = $this->Language->Get('as_title');
        $_optionsContainer[2]['value'] = SWIFT_KnowledgebaseCategory::SORT_TITLE;
        if ($_articleSortOrder == SWIFT_KnowledgebaseCategory::SORT_TITLE)
        {
            $_optionsContainer[2]['selected'] = true;
        }

        $_optionsContainer[3]['title'] = $this->Language->Get('as_creationdate');
        $_optionsContainer[3]['value'] = SWIFT_KnowledgebaseCategory::SORT_CREATIONDATE;
        if ($_articleSortOrder == SWIFT_KnowledgebaseCategory::SORT_CREATIONDATE)
        {
            $_optionsContainer[3]['selected'] = true;
        }

        $_optionsContainer[4]['title'] = $this->Language->Get('as_rating');
        $_optionsContainer[4]['value'] = SWIFT_KnowledgebaseCategory::SORT_RATING;
        if ($_articleSortOrder == SWIFT_KnowledgebaseCategory::SORT_RATING)
        {
            $_optionsContainer[4]['selected'] = true;
        }

//        $_OptionsTabObject->Select('articlesortorder', $this->Language->Get('articlesortorder'), $this->Language->Get('desc_articlesortorder'), $_optionsContainer);
        $_OptionsTabObject->Hidden('articlesortorder', SWIFT_KnowledgebaseCategory::SORT_INHERIT);

        $_OptionsTabObject->YesNo('allowcomments', $this->Language->Get('allowcomments'), $this->Language->Get('desc_allowcomments'), $_allowComments);
        $_OptionsTabObject->YesNo('allowrating', $this->Language->Get('allowrating'), $this->Language->Get('desc_allowrating'), $_allowRating);

        /*
         * ###############################################
         * END OPTIONS TAB
         * ###############################################
         */



        /*
         * ###############################################
         * BEGIN PERMISSIONS (USERS) TAB
         * ###############################################
         */

        $_PermissionTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissionsuser'), 'icon_settings2.gif', 'permissionsuser');
        $_PermissionTabObject->Overflow('380');

        $_PermissionTabObject->YesNo('uservisibilitycustom', $this->Language->Get('uservisibilitycustom'),
                $this->Language->Get('desc_uservisibilitycustom'), $_userVisibilityCustom);

        $_PermissionTabObject->Title($this->Language->Get('usergroups'), 'doublearrows.gif');

        $_index = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_isSelected = false;

            if ($_mode == SWIFT_UserInterface::MODE_INSERT && !$_userGroupIDList)
            {
                $_isSelected = true;
            } else if (_is_array($_userGroupIDList)) {
                if (in_array($this->Database->Record['usergroupid'], $_userGroupIDList))
                {
                    $_isSelected = true;
                }
            }

            $_PermissionTabObject->YesNo('usergroupidlist[' . ($this->Database->Record['usergroupid']) . ']', htmlspecialchars($this->Database->Record['title']), '', $_isSelected);

            $_index++;
        }

        /*
         * ###############################################
         * END PERMISSIONS (USERS) TAB
         * ###############################################
         */



        /*
         * ###############################################
         * BEGIN PERMISSIONS (STAFF) TAB
         * ###############################################
         */

        $_PermissionTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissionsstaff'), 'icon_settings2.gif', 'permissionsstaff');
        $_PermissionTabObject->Overflow('380');

        $_PermissionTabObject->YesNo('staffvisibilitycustom', $this->Language->Get('staffvisibilitycustom'), $this->Language->Get('desc_staffvisibilitycustom'), $_staffVisibilityCustom);
        $_PermissionTabObject->Title($this->Language->Get('staffteams'), 'doublearrows.gif');

        $_index = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroup ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            $_isSelected = false;

            if ($_mode == SWIFT_UserInterface::MODE_INSERT && !$_staffGroupIDList)
            {
                $_isSelected = true;
            } else if (_is_array($_staffGroupIDList)) {
                if (in_array($this->Database->Record['staffgroupid'], $_staffGroupIDList))
                {
                    $_isSelected = true;
                }
            }

            if ($this->Database->Record['isadmin']) {
                // prevent administrator groups to be removed so categories can be managed
                $_PermissionTabObject->YesNo('staffgroupidlist[' . $this->Database->Record['staffgroupid'] . ']',
                    htmlspecialchars($this->Database->Record['title']) . ' (*)', '', true,
                    'return false;');
                $_PermissionTabObject->AppendHTML("<script>jQuery(document).ajaxComplete(function(){jQuery('span:contains(\"(*)\")', '#View_Category_tab_permissionsstaff').parents('tr').find('label,span').attr('disabled', 'disabled');});</script>");
            } else {
                $_PermissionTabObject->YesNo('staffgroupidlist[' . $this->Database->Record['staffgroupid'] . ']', htmlspecialchars($this->Database->Record['title']), '', $_isSelected);
            }

            $_index++;
        }

        /*
         * ###############################################
         * END PERMISSIONS TAB
         * ###############################################
         */

        $_outputHTML = '<script type="text/javascript">';
        $_outputHTML .= 'QueueFunction(function(){';
        $_outputHTML .= 'HandleKBCategoryType();';
        $_outputHTML .= '});</script>';
        $this->UserInterface->AppendHTML($_outputHTML);

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Canned Tabs
     *
     * @author Varun Shoor
     * @param bool $_isResponsesTabSelected (OPTIONAL) Whether the responses tab is selected by default
     * @param int $_searchStoreID (OPTIONAL) The optional search store id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderTabs($_isResponsesTabSelected = false, $_searchStoreID = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        $this->UserInterface->Start();

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('insertcategory'), 'fa-folder-o', "InsertKnowledgebaseCategoryWindow(0);", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('insertarticle'), 'fa-file-text-o', "loadViewportData('/Knowledgebase/Article/Insert/0');", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('kbcategory'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN CATEGORIES TAB
         * ###############################################
         */
        $_CategoriesTabObject = $this->UserInterface->AddTab($this->Language->Get('tabcategories'), 'icon_folderyellow3.gif', 'categories', true);

        $_CategoriesTabObject->RowHTML('<tr><td align="left" valign="top">' . SWIFT_KnowledgebaseCategoryManager::GetCategoryTree(0) . '</td></tr>');

        $this->UserInterface->End();

        return true;
    }
}
