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

namespace News\Staff;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use News\Models\Category\SWIFT_NewsCategoryLink;
use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;
use Base\Models\Comment\SWIFT_Comment;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The News Item View
 *
 * @author Varun Shoor
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_Comments $Controller
 */
class View_NewsItem extends SWIFT_View
{
    /**
     * Render the News Item Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_NewsItem $_SWIFT_NewsItemObject The SWIFT_NewsItem Object Pointer (Only for EDIT Mode)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_NewsItem $_SWIFT_NewsItemObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_newsSubject = '';
        $_newsContents = '';
        $_newsStart = '';
        $_newsStartTime = 0;
        $_newsExpiry = '';
        $_newsExpiryTime = 0;
        $_allowComments = true;
        $_sendEmail = true;
        $_customEmailSubject = '';

        $_userVisibilityCustom = false;
        $_userGroupIDList = false;

        $_staffVisibilityCustom = false;
        $_staffGroupIDList = false;

        $_newsCategoryIDList = array();

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_NewsItemObject !== null)
        {
            $this->UserInterface->Start(get_short_class($this),'/News/NewsItem/EditSubmit/' . $_SWIFT_NewsItemObject->GetNewsItemID(),
                    SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this),'/News/NewsItem/InsertSubmit/0', SWIFT_UserInterface::MODE_INSERT, false);
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT_NewsItemObject !== null)
        {
            if ($_SWIFT_NewsItemObject->GetProperty('newsstatus') == SWIFT_NewsItem::STATUS_DRAFT)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('publish'), 'fa-check-circle', '/News/NewsItem/EditSubmit/' . $_SWIFT_NewsItemObject->GetNewsItemID() . '/1', SWIFT_UserInterfaceToolbar::LINK_FORM);
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'icon_savereload.gif');
            } else {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
            }

            $this->UserInterface->Toolbar->AddButton('');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/News/NewsItem/Delete/' .
                    $_SWIFT_NewsItemObject->GetNewsItemID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('newsitem'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_newsSubject = $_SWIFT_NewsItemObject->GetProperty('subject');
            $_newsContents = $_SWIFT_NewsItemObject->GetProperty('contents');
            $_customEmailSubject = $_SWIFT_NewsItemObject->GetProperty('emailsubject');
            $_sendEmail = false;
            $_newsExpiry = $_newsExpiryTime = (int) ($_SWIFT_NewsItemObject->GetProperty('expiry'));
            if (!empty($_newsExpiry)) {
                $_newsExpiry = date(SWIFT_Date::GetCalendarDateFormat(), $_newsExpiry);
            } else {
                $_newsExpiry = '';
            }
            $_newsStart = $_newsStartTime = (int) ($_SWIFT_NewsItemObject->GetProperty('start'));
            if (!empty($_newsStart)) {
                $_newsStart = date(SWIFT_Date::GetCalendarDateFormat(), $_newsStart);
            } else {
                $_newsStart = '';
            }
            $_allowComments = (int) ($_SWIFT_NewsItemObject->GetProperty('allowcomments'));
            $_userVisibilityCustom = (int) ($_SWIFT_NewsItemObject->GetProperty('uservisibilitycustom'));
            $_staffVisibilityCustom = (int) ($_SWIFT_NewsItemObject->GetProperty('staffvisibilitycustom'));
            $_staffGroupIDList = $_SWIFT_NewsItemObject->GetLinkedStaffGroupIDList();
            $_userGroupIDList = $_SWIFT_NewsItemObject->GetLinkedUserGroupIDList();

            $_newsCategoryIDList = SWIFT_NewsCategoryLink::RetrieveOnNewsItem(array($_SWIFT_NewsItemObject->GetNewsItemID()));

        } else {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('publish'), 'fa-check-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('saveasdraft'), 'fa-repeat', '/News/NewsItem/InsertSubmit/1', SWIFT_UserInterfaceToolbar::LINK_FORM);

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('newsitem'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_news.gif', 'generalnewsinsert', true);
        $_GeneralTabObject->SetColumnWidth('100');

        $_GeneralTabObject->Text('subject', $this->Language->Get('newstitle'), $this->Language->Get('desc_newstitle'), $_newsSubject, 'text', 90);
        $_GeneralTabObject->YesNo('sendemail', $this->Language->Get('sendemail'), $this->Language->Get('desc_sendemail'), $_sendEmail);

        $_GeneralTabObject->HTMLEditor('newscontents', $_newsContents);

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

        $_OptionsTabObject = $this->UserInterface->AddTab($this->Language->Get('taboptions'), 'icon_settings2.gif', 'nwoptions');

        $_OptionsTabObject->Title($this->Language->Get('generalsettings'), 'icon_doublearrows.gif');

        $_checkBoxContainer = array();
        $_categoryVisibilityContainer = array();

        if ($_POST['newstype'] == SWIFT_NewsItem::TYPE_GLOBAL)
        {
            $_categoryVisibilityContainer[] = SWIFT_PUBLIC;
            $_categoryVisibilityContainer[] = SWIFT_PRIVATE;
        } else if ($_POST['newstype'] == SWIFT_NewsItem::TYPE_PUBLIC) {
            $_categoryVisibilityContainer[] = SWIFT_PUBLIC;
        } else if ($_POST['newstype'] == SWIFT_NewsItem::TYPE_PRIVATE) {
            $_categoryVisibilityContainer[] = SWIFT_PRIVATE;
        }

        $_index = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newscategories WHERE visibilitytype IN (" . BuildIN($_categoryVisibilityContainer) . ") ORDER BY categorytitle ASC");
        while ($this->Database->NextRecord())
        {
            $_checkBoxContainer[$_index]['title'] = $this->Database->Record['categorytitle'];
            $_checkBoxContainer[$_index]['value'] = $this->Database->Record['newscategoryid'];
            $_checkBoxContainer[$_index]['icon'] = SWIFT::Get('themepathimages') . 'icon_folderyellow3.gif';

            if (in_array($this->Database->Record['newscategoryid'], $_newsCategoryIDList))
            {
                $_checkBoxContainer[$_index]['checked'] = true;
            }

            $_index++;
        }

        $_OptionsTabObject->CheckBoxContainerList('newscategoryidlist', $this->Language->Get('newscategories'), $this->Language->Get('desc_newscategories'), $_checkBoxContainer);

        $_OptionsTabObject->Date('start', $this->Language->Get('newsstart'), $this->Language->Get('desc_newsstart'), $_newsStart, $_newsStartTime, true, false);
        $_OptionsTabObject->Date('expiry', $this->Language->Get('newsexpiry'), $this->Language->Get('desc_newsexpiry'), $_newsExpiry, $_newsExpiryTime, true, false);

        $_OptionsTabObject->YesNo('allowcomments', $this->Language->Get('allowcomments'), $this->Language->Get('desc_allowcomments'), $_allowComments);

        $_OptionsTabObject->Title($this->Language->Get('emaildispatchsettings'), 'icon_doublearrows.gif');
        $_OptionsTabObject->Text('customemailsubject', $this->Language->Get('customemailsubject'), $this->Language->Get('desc_customemailsubject'), $_customEmailSubject, 'text', 40);
        $_OptionsTabObject->Text('fromname', $this->Language->Get('fromname'), $this->Language->Get('desc_fromname'), $_SWIFT->Staff->GetProperty('fullname'));
        $_OptionsTabObject->Text('fromemail', $this->Language->Get('fromemail'), $this->Language->Get('desc_fromemail'), $_SWIFT->Settings->Get('general_returnemail'));

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

        if ($_POST['newstype'] == SWIFT_NewsItem::TYPE_GLOBAL || $_POST['newstype'] == SWIFT_NewsItem::TYPE_PUBLIC)
        {
            $_PermissionTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissionsuser'), 'icon_settings2.gif', 'permissionsuser');

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

        if ($_POST['newstype'] == SWIFT_NewsItem::TYPE_GLOBAL || $_POST['newstype'] == SWIFT_NewsItem::TYPE_PRIVATE)
        {
            $_PermissionTabObject = $this->UserInterface->AddTab($this->Language->Get('tabpermissionsstaff'), 'icon_settings2.gif', 'permissionsstaff');

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

                $_PermissionTabObject->YesNo('staffgroupidlist[' . ($this->Database->Record['staffgroupid']) . ']', htmlspecialchars($this->Database->Record['title']), '', $_isSelected);

                $_index++;
            }
        }

        /*
         * ###############################################
         * END PERMISSIONS TAB
         * ###############################################
         */

        $this->UserInterface->Hidden('newstype', $_POST['newstype']);

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Insert News Dialog
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderInsertNewsDialog()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }


        $this->UserInterface->Start('insertnewsdialog', '/News/NewsItem/Insert', SWIFT_UserInterface::MODE_INSERT, true);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('next'), 'fa-chevron-circle-right ');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('insertnews'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $this->UserInterface->SetDialogOptions(false);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_news.gif', 'generalnewsdialog', true);

        $_radioContainer = array();
        $_index = 0;
        if ($_SWIFT->Staff->GetPermission('staff_newscanpublicinsert') != '0')
        {
            $_radioContainer[$_index]['title'] = $this->Language->Get('global');
            $_radioContainer[$_index]['value'] = SWIFT_NewsItem::TYPE_GLOBAL;
            $_radioContainer[$_index]['checked'] = true;
            $_index++;

            $_radioContainer[$_index]['title'] = $this->Language->Get('public');
            $_radioContainer[$_index]['value'] = SWIFT_NewsItem::TYPE_PUBLIC;
            $_index++;
        }

        $_radioContainer[$_index]['title'] = $this->Language->Get('private');
        $_radioContainer[$_index]['value'] = SWIFT_NewsItem::TYPE_PRIVATE;
        if ($_SWIFT->Staff->GetPermission('staff_newscanpublicinsert') == '0')
        {
            $_radioContainer[$_index]['checked'] = true;
        }

        $_GeneralTabObject->Radio('newstype', $this->Language->Get('newstype'), $this->Language->Get('desc_newstype'), $_radioContainer);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the News Items Grid
     *
     * @author Varun Shoor
     * @param int $_searchStoreID (OPTIONAL) The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function RenderGrid($_searchStoreID = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('newsitemgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery(
            "SELECT newsitems.* FROM " . TABLE_PREFIX . "newsitems AS newsitems
                LEFT JOIN " . TABLE_PREFIX . "newsitemdata AS newsitemdata ON (newsitems.newsitemid = newsitemdata.newsitemid)
                WHERE ((" . $this->UserInterfaceGrid->BuildSQLSearch('newsitems.subject') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('newsitemdata.contents') . ") OR
                    (" . $this->UserInterfaceGrid->BuildSQLSearch('newsitems.author') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('newsitems.email') . "))",

            "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "newsitems AS newsitems
                LEFT JOIN " . TABLE_PREFIX . "newsitemdata AS newsitemdata ON (newsitems.newsitemid = newsitemdata.newsitemid)
                WHERE ((" . $this->UserInterfaceGrid->BuildSQLSearch('newsitems.subject') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('newsitemdata.contents') . ") OR
                    (" . $this->UserInterfaceGrid->BuildSQLSearch('newsitems.author') . ") OR (" . $this->UserInterfaceGrid->BuildSQLSearch('newsitems.email') . "))");
        }

        $this->UserInterfaceGrid->SetSearchStoreOptions($_searchStoreID,
                "SELECT newsitems.* FROM " . TABLE_PREFIX . "newsitems AS newsitems
                    WHERE newsitems.newsitemid IN (%s)",
                SWIFT_SearchStore::TYPE_NEWS, '/News/NewsItem/Manage/-1');

        $this->UserInterfaceGrid->SetQuery('SELECT newsitems.* FROM ' . TABLE_PREFIX . 'newsitems AS newsitems',
                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'newsitems');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('newsitemid', 'newsitemid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('newsitems.subject', $this->Language->Get('newstitle'),SWIFT_UserInterfaceGridField::TYPE_DB, 0,
                SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('newsitems.author', $this->Language->Get('author'),SWIFT_UserInterfaceGridField::TYPE_DB, 160,
                SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('newsitems.newstype', $this->Language->Get('newstype'),SWIFT_UserInterfaceGridField::TYPE_DB, 100,
                SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('newsitems.dateline', $this->Language->Get('creationdate'),SWIFT_UserInterfaceGridField::TYPE_DB, 180,
                SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_DESC), true);


        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('\News\Staff\Controller_NewsItem', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLink("UICreateWindow('" . SWIFT::Get('basename') . "/News/NewsItem/InsertDialog', 'insertnwitem', '" . $this->Language->Get('wininsertnewsitem') . "', '" . $this->Language->Get('loadingwindow') . "', 600, 430, true, this);");

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array The Processed Field Container Array
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffCache = (array) $_SWIFT->Cache->Get('staffcache');

        $_subjectSuffix = '';
        $_icon = 'fa-newspaper-o';
        if ($_fieldContainer['newsstatus'] == SWIFT_NewsItem::STATUS_DRAFT)
        {
            $_icon = 'fa-newspaper-o newsdraft';
            $_subjectSuffix .= ' ' . $_SWIFT->Language->Get('newsdraft');
        }

        if ($_fieldContainer['start'] > DATENOW && $_fieldContainer['start'] != '0')
        {
            $_icon = 'fa-newspaper-o newsnotstarted';
            $_subjectSuffix .= ' ' . $_SWIFT->Language->Get('newsnotstarted');
        }

        if ($_fieldContainer['start'] < DATENOW && $_fieldContainer['start'] != '0')
        {
            $_icon = 'fa-newspaper-o newsstarted';
            $_subjectSuffix .= ' ' . $_SWIFT->Language->Get('newsstarted');
        }

        if ($_fieldContainer['expiry'] < DATENOW && $_fieldContainer['expiry'] != '0')
        {
            $_icon = 'fa-newspaper-o newsexpired';
            $_subjectSuffix .= ' ' . $_SWIFT->Language->Get('newsexpired');
        }

        $_fieldContainer['icon'] = '<i class="fa '. $_icon .'" aria-hidden="true"></i>';

        $_fieldContainer['newsitems.subject'] = '<a href="' . SWIFT::Get('basename') . '/News/NewsItem/Edit/' . ($_fieldContainer['newsitemid']) . '" viewport="1" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['subject']) . '</a>' . $_subjectSuffix;

        $_fieldContainer['newsitems.dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);

        if (isset($_staffCache[$_fieldContainer['staffid']]['fullname']))
        {
            $_fieldContainer['newsitems.author'] = text_to_html_entities($_staffCache[$_fieldContainer['staffid']]['fullname']);
        } else {
            $_fieldContainer['newsitems.author'] = htmlspecialchars($_fieldContainer['author']);
        }

        $_fieldContainer['newsitems.newstype'] = SWIFT_NewsItem::GetNewsTypeLabel($_fieldContainer['newstype']);

        return $_fieldContainer;
    }

    /**
     * Render the View All Page
     *
     * @author Varun Shoor
     * @param int $_newsCategoryID (OPTIONAL) The filter by news category id
     * @param int $_offset
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderViewAll($_newsCategoryID = 0, $_offset = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->UserInterface->Start('viewallnews', '/News/NewsItem/ViewAll', SWIFT_UserInterface::MODE_INSERT, false);

        $_totalNewsItems = SWIFT_NewsItem::RetrieveCount(array(SWIFT_NewsItem::TYPE_GLOBAL, SWIFT_NewsItem::TYPE_PRIVATE), 0, $_SWIFT->Staff->GetProperty('staffgroupid'), $_newsCategoryID);

        $_newsOffset = ($_offset);

        $_showOlderPosts = $_showNewerPosts = false;

        $_olderOffset = $_newerOffset = 0;

        if ($_newsOffset > 0)
        {
            $_showNewerPosts = true;

            $_newerOffset = $_newsOffset - $this->Settings->Get('nw_staffpageno');
        }

        $_newsActiveCount = $_totalNewsItems - ($_newsOffset + $this->Settings->Get('nw_staffpageno'));

        if ($_newsActiveCount > 0)
        {
            $_showOlderPosts = true;

            $_olderOffset = $_newsOffset + $this->Settings->Get('nw_staffpageno');
        }

        if ($_showOlderPosts == true)
        {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('olderposts'), 'fa-chevron-circle-left', '/News/NewsItem/ViewAll/' . ($_newsCategoryID) . '/' . ($_olderOffset), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        }

        if ($_showNewerPosts == true)
        {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('newerposts'), 'fa-chevron-circle-right', '/News/NewsItem/ViewAll/' . ($_newsCategoryID) . '/' . ($_newerOffset), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('newsitem'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN VIEW ALL TAB
         * ###############################################
         */
        $_ViewAllTabObject = $this->UserInterface->AddTab($this->Language->Get('tabviewall'), 'icon_news.gif', 'viewall', true);

        $_renderHTML = '<div class="tabdatacontainer">';
        $_newsContainer = SWIFT_NewsItem::Retrieve($this->Settings->Get('nw_staffpageno'), $_offset, array(SWIFT_NewsItem::TYPE_GLOBAL, SWIFT_NewsItem::TYPE_PRIVATE), 0, $_SWIFT->Staff->GetProperty('staffgroupid'), $_newsCategoryID);
        if (!_is_array($_newsContainer))
        {
            $_renderHTML .= '<div class="dashboardmsg">' . $this->Language->Get('noinfoinview') . '</div>';
        } else {
            $_renderHTML .= '<table cellpadding="0" cellspacing="0" border="0">';

            foreach ($_newsContainer as $_newsItemID => $_newsItem)
            {

                $_posted = ($_newsItem['author']) ? $this->Language->Get('nwpostedby') . ' ' . htmlspecialchars($_newsItem['author']) : $this->Language->Get('nwposted');

                $_renderHTML .= '<tr>';
                $_renderHTML .= '<td width="100%" valign="top">
                            <div class="newsavatar"><img src="' . SWIFT::Get('basename') . '/Base/StaffProfile/DisplayAvatar/' . $_newsItem['staffid'] . '/' . $_newsItem['emailhash'] . '/40'. '" align="absmiddle" border="0" /></div>
                            <div class="newstitle"><a class="newstitlelink" href="' . SWIFT::Get('basename') . '/News/NewsItem/ViewItem/' . $_newsItemID . '/' . ($_newsCategoryID) . '/' . ($_offset) . '" viewport="1">' . $_newsItem['subject'] . '</a>
                            <div class="newsinfo">' . $_posted . ' '. $this->Language->Get('on') . ' ' . htmlspecialchars($_newsItem['date']) . '</div>';

                $_renderHTML .= '</tr>';

                $_renderHTML .= '<tr><td colspan="2" class="newscontents">' . $_newsItem['contents'] . '<br /><a class="newsreadmorelink" href="' . SWIFT::Get('basename') . '/News/NewsItem/ViewItem/' . $_newsItemID . '/' . ($_newsCategoryID) . '/' . ($_offset) . '" viewport="1" title="' . htmlspecialchars($_newsItem['subject']) . '">' . $this->Language->Get('nwreadmore') . '</a></td></tr>';

                $_renderHTML .= '<tr><td colspan="2"><hr class="newshr" /><br /></td></tr>';
            }

            $_renderHTML .= '</table>';
        }
        $_renderHTML .= '</div>';


        $_ViewAllTabObject->RowHTML('<tr><td>' . $_renderHTML . '</td></tr>');

        /*
         * ###############################################
         * END VIEW ALL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the View Item Page
     *
     * @author Varun Shoor
     * @param SWIFT_NewsItem $_SWIFT_NewsItemObject
     * @param int $_newsCategoryID (OPTIONAL) The filter by news category id
     * @param int $_offset
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderViewItem(SWIFT_NewsItem $_SWIFT_NewsItemObject, $_newsCategoryID = 0, $_offset = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->UserInterface->Start('viewallnews', '/News/Comments/Submit/' . $_SWIFT_NewsItemObject->GetNewsItemID(), SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left', '/News/NewsItem/ViewAll/' . ($_newsCategoryID) . '/' . ($_offset), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);

        if ($_SWIFT->Staff->GetPermission('staff_nwcanmanageitems') != '0' && $_SWIFT->Staff->GetPermission('staff_nwcanupdateitem') != '0')
        {
            $this->UserInterface->Toolbar->AddButton('');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('edit'), 'fa-pencil-square-o', '/News/NewsItem/Edit/' . ($_SWIFT_NewsItemObject->GetNewsItemID()) . '/' . ($_newsCategoryID) . '/' . ($_offset), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('newsitem'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN VIEW ITEM TAB
         * ###############################################
         */
        $_ViewItemTabObject = $this->UserInterface->AddTab($this->Language->Get('tabnews'), 'icon_news.gif', 'viewitem', true);

        $_newsItem = $_SWIFT_NewsItemObject->RetrieveStore();
        $_newsItemID = $_SWIFT_NewsItemObject->GetNewsItemID();

        $_posted = ($_newsItem['author']) ? $this->Language->Get('nwpostedby') . ' ' . htmlspecialchars($_newsItem['author']) : $this->Language->Get('nwposted');

        $_renderHTML = '<div class="tabdatacontainer">';
            $_renderHTML .= '<table cellpadding="0" cellspacing="0" border="0">';

                $_renderHTML .= '<tr>';
                $_renderHTML .= '<td width="100%" valign="top">
                            <div class="newsavatar"><img src="' . SWIFT::Get('basename') . '/Base/StaffProfile/DisplayAvatar/' . $_newsItem['staffid'] . '/' . $_newsItem['emailhash'] . '/40'. '" align="absmiddle" border="0" /></div>
                            <div class="newstitle"><a class="newstitlelink" href="' . SWIFT::Get('basename') . '/News/NewsItem/ViewItem/' . $_newsItemID . '" viewport="1">' . $_newsItem['subject'] . '</a>
                            <div class="newsinfo">' . $_posted . ' '. $this->Language->Get('on') . ' ' . htmlspecialchars($_newsItem['date']) . '</div>';

                $_renderHTML .= '</tr>';

                $_renderHTML .= '<tr><td colspan="2" class="newscontents">' . $_newsItem['contents'] . '<br /></td></tr>';

                $_renderHTML .= '<tr><td colspan="2"><hr class="newshr" /></td></tr>';

            $_renderHTML .= '</table>';

        if ($_newsItem['allowcomments'] == '1')
        {
            $_renderHTML .= $this->Controller->CommentManager->LoadStaffCP('News', SWIFT_Comment::TYPE_NEWS, $_newsItem['newsitemid']);
        }

        $_renderHTML .= '</div>';


        $_ViewItemTabObject->RowHTML('<tr><td>' . $_renderHTML . '</td></tr>');

        /*
         * ###############################################
         * END VIEW ITEM TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Information Box
     *
     * @author Varun Shoor
     * @param SWIFT_NewsItem $_SWIFT_NewsItemObject The SWIFT_NewsItem Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderInfoBox(SWIFT_NewsItem $_SWIFT_NewsItemObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_staffCache = (array) $this->Cache->Get('staffcache');

        $_informationHTML = '';

        $_authorName = $_SWIFT_NewsItemObject->GetProperty('author');
        if (isset($_staffCache[$_SWIFT_NewsItemObject->GetProperty('staffid')]))
        {
            $_authorName = $_staffCache[$_SWIFT_NewsItemObject->GetProperty('staffid')]['fullname'];
        }

        if (!empty($_authorName))
        {
            $_informationHTML .= '<div class="navinfoitemtext">' .
                    '<div class="navinfoitemtitle">' . $this->Language->Get('infobauthor') . '</div><div class="navinfoitemcontent">' . htmlspecialchars(StripName($_authorName, 20)) . '</div></div>';
        }

        $_informationHTML .= '<div class="navinfoitemtext">' .
                '<div class="navinfoitemtitle">' . $this->Language->Get('infobcreationdate') . '</div><div class="navinfoitemcontent">' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_NewsItemObject->GetProperty('dateline')) . '</div></div>';

        if ($_SWIFT_NewsItemObject->GetProperty('edited') == '1')
        {
            $_editedStaffName = $this->Language->Get('na');
            if (isset($_staffCache[$_SWIFT_NewsItemObject->GetProperty('editedstaffid')]))
            {
                $_editedStaffName = $_staffCache[$_SWIFT_NewsItemObject->GetProperty('editedstaffid')]['fullname'];
            }

            $_informationHTML .= '<div class="navinfoitemtext">' .
                    '<div class="navinfoitemtitle">' . $this->Language->Get('infobeditedby') . '</div><div class="navinfoitemcontent">' . htmlspecialchars(StripName($_editedStaffName, 20)) . '</div></div>';

            $_informationHTML .= '<div class="navinfoitemtext">' .
                    '<div class="navinfoitemtitle">' . $this->Language->Get('infobeditedon') . '</div><div class="navinfoitemcontent">' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_NewsItemObject->GetProperty('editeddateline')) . '</div></div>';
        }


        if ($_SWIFT_NewsItemObject->GetProperty('issynced') == '1')
        {
            $_informationHTML .= '<div class="navinfoitemtext">' .
                    '<div class="navinfoitemtitle">' . $this->Language->Get('infobsync') . '</div><div class="navinfoitemcontent">' . $this->Language->Get('yes') . '</div></div>';

            $_informationHTML .= '<div class="navinfoitemtext">' .
                    '<div class="navinfoitemtitle">' . $this->Language->Get('infobsyncdate') . '</div><div class="navinfoitemcontent">' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_NewsItemObject->GetProperty('syncdateline')) . '</div></div>';
        }

        $this->UserInterface->AddNavigationBox($this->Language->Get('informationbox'), $_informationHTML);

        return true;
    }
}
?>
