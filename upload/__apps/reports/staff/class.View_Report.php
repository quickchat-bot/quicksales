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

use Base\Library\KQL\SWIFT_KQLSchema;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Models\SearchStore\SWIFT_SearchStore;

/**
 * The Reports View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Report extends SWIFT_View
{
    static protected $_schemaContainer = array();

    /**
     * Render the Report Dialog
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Base\Library\Staff\SWIFT_Staff_Exception
     */
    public function RenderDialog()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this),'/Reports/Report/Insert', SWIFT_UserInterface::MODE_INSERT, true);

        $_reportTitle = '';
        $_visibilityType = SWIFT_Report::VISIBLE_PUBLIC;

        if (isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1)
        {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('repnext'), 'fa-chevron-circle-right ');
        }

        $_bottomLeftPanel = '<div id="rperm"><i class="fa fa-lock" aria-hidden="true"></i> <span id="rperm_text">' . SWIFT_Report::GetVisibilityLabel($_visibilityType) . '</span> <img src="' . SWIFT::Get('themepathimages') . 'menudrop_grey.svg" border="0" /></div>';


        $this->UserInterface->SetDialogBottomLeftPanel($_bottomLeftPanel);

        $this->UserInterface->OverrideButtonText('<input type="button" name="submitbutton" id="%formid%_submit" class="rebuttonblue" onclick="javascript: $(\'#%formid%\').submit();" value="' . $this->Language->Get('repnextbutton') . '" onfocus="blur();" />');

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('properties'), 'icon_report_add.png', 'general', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('reporttitle'), $this->Language->Get('desc_reporttitle'), $_reportTitle);

        $_optionsContainer = array();
        $_index = 0;
        $_baseTableList = SWIFT_Report::GetBaseTableList();
        foreach ($_baseTableList as $_tableName => $_tableLabel) {
            $_optionsContainer[$_index]['title'] = $_tableLabel;
            $_optionsContainer[$_index]['value'] = $_tableName;

            if ($_index == 0) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_GeneralTabObject->Select('basetable', $this->Language->Get('basetable'), $this->Language->Get('desc_basetable'), $_optionsContainer);


        $_optionsContainer = array();
        $_index = 0;
        $this->Database->Query("SELECT reportcategories.* FROM " . TABLE_PREFIX . "reportcategories AS reportcategories
                LEFT JOIN " . TABLE_PREFIX . "staff AS staff ON (reportcategories.staffid = staff.staffid)
                LEFT JOIN " . TABLE_PREFIX . "staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)
                WHERE (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_PUBLIC . "'
                        OR (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_PRIVATE . "' AND reportcategories.staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                        OR (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_TEAM . "' AND staffgroup.staffgroupid = '" . (int) ($_SWIFT->Staff->GetProperty('staffgroupid')) . "')
                        )
                ORDER BY reportcategories.title ASC");
        while ($this->Database->NextRecord()) {
            $_titleSuffix = '';
            if ($this->Database->Record['visibilitytype'] == SWIFT_ReportCategory::VISIBLE_PRIVATE) {
                $_titleSuffix = ' (' . $this->Language->Get('visible_private') . ')';
            }

            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'] . $_titleSuffix;
            $_optionsContainer[$_index]['value'] = $this->Database->Record['reportcategoryid'];

            if ($_index == 0) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        /*
         * BUG FIX - Ashish Kataria
         *
         * SWIFT-2638 Reports linked under Private category are available for other staff members
         *
         * Comments: When user select private category don't show the visibility option and make report private by default
         */
        $_onChangeJs = "if($('option:selected',this).text().indexOf('".$this->Language->Get('visible_private')."') > 0) {
                            $('#rperm').css('display','none');
                            $('#View_Report_visibilitytype').val('" . SWIFT_Report::VISIBLE_PRIVATE . "');
                        } else {
                            $('#rperm').css('display','');
                            $('#View_Report_visibilitytype').val($('#rperm_text').text().toLowerCase());
                        }";

        $_GeneralTabObject->Select('reportcategoryid', $this->Language->Get('reportcategory'), $this->Language->Get('desc_reportcategory'), $_optionsContainer, $_onChangeJs);

        $_GeneralTabObject->Hidden('visibilitytype', $_visibilityType);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->AppendHTML('<ul class="swiftpopup" id="popup_rperm">
        <li onclick="$(\'#View_Report_visibilitytype\').val(\'' . SWIFT_Report::VISIBLE_PUBLIC . '\'); $(\'#rperm_text\').html(\'' . SWIFT_Report::GetVisibilityLabel(SWIFT_Report::VISIBLE_PUBLIC) . '\'); SWIFT_PopupDestroyAll(\'rperm\');"><a href="javascript: void(0);">' . $this->Language->Get('visible_public') . '</a></li>
        <li class="separator"></li>
        <li onclick="$(\'#View_Report_visibilitytype\').val(\'' . SWIFT_Report::VISIBLE_PRIVATE . '\'); $(\'#rperm_text\').html(\'' . SWIFT_Report::GetVisibilityLabel(SWIFT_Report::VISIBLE_PRIVATE) . '\'); SWIFT_PopupDestroyAll(\'rperm\');"><a href="javascript: void(0);">' . $this->Language->Get('visible_private') . '</a></li>
        </ul><script type="text/javascript">QueueFunction(function(){ $("#rperm").SWIFT_Popup({align: "left", isdialog: true, width: 100}); });</script>');

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Report Properties Dialog
     *
     * @author Varun Shoor
     * @param SWIFT_Report $_SWIFT_ReportObject
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderPropertiesDialog(SWIFT_Report $_SWIFT_ReportObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this),'/Reports/Report/PropertiesSubmit/' . $_SWIFT_ReportObject->GetReportID(), SWIFT_UserInterface::MODE_EDIT, true);

        $_reportTitle = $_SWIFT_ReportObject->GetProperty('title');
        $_visibilityType = $_SWIFT_ReportObject->GetProperty('visibilitytype');
        $_baseTableName = $_SWIFT_ReportObject->GetProperty('basetablename');
        $_reportCategoryID = $_SWIFT_ReportObject->GetProperty('reportcategoryid');

        if (isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1)
        {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
        }

        $_bottomLeftPanel = '<div id="rperm"><i class="fa fa-lock" aria-hidden="true"></i> <span id="rperm_text">' . SWIFT_Report::GetVisibilityLabel($_visibilityType) . '</span> <img src="' . SWIFT::Get('themepathimages') . 'menudrop_grey.svg" border="0" /></div>';

        $this->UserInterface->SetDialogBottomLeftPanel($_bottomLeftPanel);

        $this->UserInterface->OverrideButtonText('<input type="button" name="submitbutton" id="%formid%_submit" class="rebuttonblue" onclick="javascript: $(\'#%formid%\').submit();" value="' . $this->Language->Get('update') . '" onfocus="blur();" />');

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('properties'), 'fa-pencil-square-o', 'reportproperties', true);

        $_GeneralTabObject->Text('title', $this->Language->Get('reporttitle'), $this->Language->Get('desc_reporttitle'), $_reportTitle);

        $_optionsContainer = array();
        $_index = 0;
        $_baseTableList = SWIFT_Report::GetBaseTableList();
        foreach ($_baseTableList as $_tableName => $_tableLabel) {
            $_optionsContainer[$_index]['title'] = $_tableLabel;
            $_optionsContainer[$_index]['value'] = $_tableName;

            if ($_tableName == $_baseTableName) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_GeneralTabObject->Select('basetable', $this->Language->Get('basetable'), $this->Language->Get('desc_basetable'), $_optionsContainer);


        $_optionsContainer = array();
        $_index = 0;
        $this->Database->Query("SELECT reportcategories.* FROM " . TABLE_PREFIX . "reportcategories AS reportcategories
                LEFT JOIN " . TABLE_PREFIX . "staff AS staff ON (reportcategories.staffid = staff.staffid)
                LEFT JOIN " . TABLE_PREFIX . "staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)
                WHERE (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_PUBLIC . "'
                        OR (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_PRIVATE . "' AND reportcategories.staffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                        OR (reportcategories.visibilitytype = '" . SWIFT_ReportCategory::VISIBLE_TEAM . "' AND staffgroup.staffgroupid = '" . (int) ($_SWIFT->Staff->GetProperty('staffgroupid')) . "')
                        )
                ORDER BY reportcategories.title ASC");
        while ($this->Database->NextRecord()) {
            $_titleSuffix = '';
            if ($this->Database->Record['visibilitytype'] == SWIFT_ReportCategory::VISIBLE_PRIVATE) {
                $_titleSuffix = ' (' . $this->Language->Get('visible_private') . ')';
            }

            $_optionsContainer[$_index]['title'] = $this->Database->Record['title'] . $_titleSuffix;
            $_optionsContainer[$_index]['value'] = $this->Database->Record['reportcategoryid'];

            if ($_reportCategoryID == $this->Database->Record['reportcategoryid']) {
                $_optionsContainer[$_index]['selected'] = true;
            }

            $_index++;
        }

        $_GeneralTabObject->Select('reportcategoryid', $this->Language->Get('reportcategory'), $this->Language->Get('desc_reportcategory'), $_optionsContainer);

        $_GeneralTabObject->Hidden('visibilitytype', $_visibilityType);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->AppendHTML('<ul class="swiftpopup" id="popup_rperm">
        <li onclick="$(\'#View_Report_visibilitytype\').val(\'' . SWIFT_Report::VISIBLE_PUBLIC . '\'); $(\'#rperm_text\').html(\'' . SWIFT_Report::GetVisibilityLabel(SWIFT_Report::VISIBLE_PUBLIC) . '\'); SWIFT_PopupDestroyAll(\'rperm\');"><a href="javascript: void(0);">' . $this->Language->Get('visible_public') . '</a></li>
        <li class="separator"></li>
        <li onclick="$(\'#View_Report_visibilitytype\').val(\'' . SWIFT_Report::VISIBLE_PRIVATE . '\'); $(\'#rperm_text\').html(\'' . SWIFT_Report::GetVisibilityLabel(SWIFT_Report::VISIBLE_PRIVATE) . '\'); SWIFT_PopupDestroyAll(\'rperm\');"><a href="javascript: void(0);">' . $this->Language->Get('visible_private') . '</a></li>
        </ul><script type="text/javascript">QueueFunction(function(){ $("#rperm").SWIFT_Popup({align: "left", isdialog: true, width: 100}); });</script>');

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Report Form
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_Report $_SWIFT_ReportObject The SWIFT_Report Object Pointer (Only for EDIT Mode)
     * @param bool $_isSchedulesTabSelected (OPTIONAL) Is Schedules Tab Selected (Only for EDIT Mode)
     * @param bool $_isReportScheduled
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Render($_mode, SWIFT_Report $_SWIFT_ReportObject = null, $_isSchedulesTabSelected = false, $_isReportScheduled = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_staffCache = $this->Cache->Get('staffcache');

        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $this->UserInterface->Start(get_short_class($this) . '2', '/Reports/Report/EditSubmit/' . $_SWIFT_ReportObject->GetReportID(), SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            $this->UserInterface->Start(get_short_class($this) . '2', '/Reports/Report/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, true);
        }

        $_reportTitle = '';
        $_appendHTML = '';
        $_kql = '';
        $_enableCharts = true;
        $_baseTableName = 'users';
        $_visibilityType = SWIFT_Report::VISIBLE_PUBLIC;
        $_reportCategoryID = 0;
        if ($_mode == SWIFT_UserInterface::MODE_EDIT)
        {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('generate'), 'fa-pie-chart', '/Reports/Report/GenerateSubmit/' . $_SWIFT_ReportObject->GetReportID(), SWIFT_UserInterfaceToolbar::LINK_FORM);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('export') . ' <img src="'. SWIFT::Get('themepath') . 'images/menudropgray.gif" align="absmiddle" border="0" />', 'fa-download', 'UIDropDown(\'exportmenu\', event, \'exportOptions\', \'tabtoolbartable\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, 'exportOptions', '', false);
            $this->UserInterface->Toolbar->AddButton('');

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('schedule'), 'fa-calendar-o', "UICreateWindow('" . SWIFT::Get('basename') . "/Reports/Schedule/Dialog/" . $_SWIFT_ReportObject->GetReportID() . "', 'schedulereport', '" .
                $this->Language->Get('reportemailingproperties') . "', '" . $this->Language->Get('loadingwindow') . "', 700, 408, true, this);", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            $this->UserInterface->Toolbar->AddButton('');

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('savereport'), 'fa-check-circle');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('properties'), 'fa-pencil-square-o', "UICreateWindow('" . SWIFT::Get('basename') . "/Reports/Report/PropertiesDialog/" . $_SWIFT_ReportObject->GetReportID() . "', 'editreport', '" .
                $this->Language->Get('editproperties') . "', '" . $this->Language->Get('loadingwindow') . "', 400, 260, true, this);", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);

            $this->UserInterface->Toolbar->AddButton('');
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Reports/Report/Delete/' . $_SWIFT_ReportObject->GetReportID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM,'', '', false);

            $_appendHTML = $this->GetExportOptionsMenu($_SWIFT_ReportObject->GetReportID());

            $_reportTitle = $_SWIFT_ReportObject->GetProperty('title') . (($_isReportScheduled)? '<div class="notecounterred" style="">'. $this->Language->Get('scheduled') .'</div>' : '');
            $_baseTableName = $_SWIFT_ReportObject->GetProperty('basetablename');
            $_reportCategoryID = $_SWIFT_ReportObject->GetProperty('reportcategoryid');
            $_visibilityType = $_SWIFT_ReportObject->GetProperty('visibilitytype');
            $_kql = $_SWIFT_ReportObject->GetProperty('kql');
            $_enableCharts = $_SWIFT_ReportObject->GetProperty('chartsenabled');
        } else {
            if (isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1)
            {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('generate'), 'fa-pie-chart');
            }

            $_reportTitle = $_POST['title'];
            $_baseTableName = $_POST['basetable'];
            $_reportCategoryID = $_POST['reportcategoryid'];
            $_visibilityType = $_POST['visibilitytype'];

            $_kql = 'SELECT ';
            $_enableCharts = true;
        }

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject = $this->UserInterface->AddTab($_reportTitle, 'icon_report.png', 'general', !$_isSchedulesTabSelected);

        $_descriptionContents = "

                <span class='title'>Report Writer</span><br />
                <p class='text'>The Report Writer is the text field you see above. It makes creating your own reports using KQL really simple by checking what you type on-the-fly and automatically providing suggestions about what you may need to type next.</p>
                <p class='text'>To get started, simply place your cursor inside of the text editor and start with the first suggestion. You can use your mouse or arrow keys to scroll through and accept a suggestion. It's as easy as that!</p>

                <span class='title'>Kayako Query Language (KQL)</span><br />
                <p class='text'>KQL is the syntax used to define a report. KQL is used to specify the information to include in your report and how the report should be displayed and formatted.</p>
                <p class='text'>The following KQL statement:</p>
                <p><span class='code'>SELECT 'Tickets.Ticket Mask ID', 'Tickets.Subject', 'Users.Fullname' FROM 'Tickets', 'Users' WHERE 'Tickets.Department' = 'Sales' AND 'Tickets.Status' = 'Open'</span><p>
                <p class='text'>would produce a table of all the Open tickets in the Sales department. In this example, we are <strong>SELECT</strong>ing specific pieces of information <strong>FROM</strong> two sources, <strong>WHERE</strong> various conditions are met. For more information on the Kayako Query Language, see <a href='https://classic.kayako.com/article/1413-introduction-to-kayako-query-language-kql' target='_blank' rel='noopener noreferrer'><u>the KQL guide</u></a>.</p>

                <span class='title'>Report Types</span><br />
                <p class='text'>Reports is capable of producing various report layouts, such as <strong>tabular</strong>, <strong>summary</strong> and <strong>matrix</strong> reports. The way a report is displayed depends on how you structure your KQL statement. For more information on report types and the syntax required for specific report layouts, see the <a href='https://classic.kayako.com/article/1411-report-types-in-kayako-classic' target='_blank' rel='noopener noreferrer'><u>guide to report types</u></a>.</p>

        ";

        $_extendedOptions = '<div align="right">
            <input type="hidden" name="chartsenabled" value="0" />
            <label for="chartsenabled">
            ' . $this->Language->Get('enablecharts') . ' &nbsp;
            <input type="checkbox" value="1" id="chartsenabled" class="swiftcheckbox" name="chartsenabled"' . IIF($_enableCharts == true, ' checked') . ' />
            </label> &nbsp;&nbsp;&nbsp;
            </div>';

        $_extendedHTML = '<div class="reportdesccontainer">
            <div class="reportdescpointer"></div>
            <div class="reportdesccontentsborder">
            <div class="reportdesccontents">
                ' . $_descriptionContents . '
            </div>
            </div>
        </div>';

        $_GeneralTabObject->TextArea('kql', '', '', $_kql, '30', '4', false, '', $_extendedOptions . $_extendedHTML, 'swifttextareaconsole');

        $_GeneralTabObject->Hidden('reportcategoryid', $_reportCategoryID);
        $_GeneralTabObject->Hidden('basetable', $_baseTableName);
        $_GeneralTabObject->Hidden('visibilitytype', $_visibilityType);
        $_GeneralTabObject->Hidden('title', $_reportTitle);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */





        /*
         * ###############################################
         * BEGIN HISTORY TAB
         * ###############################################
         */

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_historyContainer = SWIFT_ReportHistory::RetrieveOnReport($_SWIFT_ReportObject);

            if (count($_historyContainer)) {
                $_HistoryTabObject = $this->UserInterface->AddTab($this->Language->Get('tabhistory'), 'icon_spacer.gif', 'history', false);
                $_HistoryTabObject->SetTabCounter(SWIFT_ReportHistory::GetTotalCount($_SWIFT_ReportObject));
                $_HistoryTabObject->LoadToolbar();



                $_historyHTML = '';

                foreach ($_historyContainer as $_reportHistoryID => $_reportHistory) {
                    $_emailAddressHash = 'none';
                    $_staffName = $_reportHistory['creatorstaffname'];
                    if (isset($_staffCache[$_reportHistory['creatorstaffid']])) {
                        $_emailAddressHash = md5($_staffCache[$_reportHistory['creatorstaffid']]['email']);
                        $_staffName = $_staffCache[$_reportHistory['creatorstaffid']]['fullname'];
                    }

                    $_dateText = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_reportHistory['dateline']) . ' (' . SWIFT_Date::ColorTime(DATENOW-$_reportHistory['dateline'], false, true) . ')';

                    $_historyHTML .= "<div class='reporthistorycontainer'>
                        <div class='reporthistorycontentsborder'>
                        <div class='reporthistorycontents'>
                            <div class='avatarcontainer'><div class='avatar'><img src='" . SWIFT::Get('basename') . "/Base/StaffProfile/DisplayAvatar/" . $_reportHistory['creatorstaffid'] . "/" . $_emailAddressHash . "/50/0' border='0' /></div></div>
                            <div class='basetitle'><div class='title'>" . htmlspecialchars($_staffName) . "</div><div class='date'>" . $_dateText . "</div><div class='code'>" . htmlspecialchars($_reportHistory['kql']) . "</div></div>
                            <div style='clear: both;'></div>
                        </div>
                        </div>
                    </div>";
                }

                if (!count($_historyContainer)) {
                    $_historyHTML = $this->Language->Get('noinfoinview');
                }

                $_columnContainer = array();
                $_columnContainer[0]['align'] = 'left';
                $_columnContainer[0]['valign'] = 'top';
                $_columnContainer[0]['value'] = $_historyHTML;
                $_columnContainer[0]['colspan'] = '2';

                $_HistoryTabObject->Row($_columnContainer, 'tablerowbase_tr');
            }
        }

        /*
         * ###############################################
         * END HISTORY TAB
         * ###############################################
         */

        /*
         * ###############################################
         * BEGIN SCHEDULES TAB
         * ###############################################
         */

        if (($_mode == SWIFT_UserInterface::MODE_EDIT) && ($_SWIFT->Staff->GetPermission('staff_rcanviewschedules') != '0')) {
            $_schedulesContainer = SWIFT_ReportSchedule::RetrieveOnReportAndStaff($_SWIFT_ReportObject, $_SWIFT->Staff);

            if (count($_schedulesContainer)) {
                $_SchedulesTabObject = $this->UserInterface->AddTab($this->Language->Get('tabschedules'), 'icon_spacer.gif', 'schedules', $_isSchedulesTabSelected, false, 0, SWIFT::Get('basename') . '/Reports/Schedule/Manage/' . $_SWIFT_ReportObject->GetReportID());
                $_SchedulesTabObject->SetTabCounter(count($_schedulesContainer));
                $_SchedulesTabObject->LoadToolbar();
            }
        }

        /*
         * ###############################################
         * END SCHEDULES TAB
         * ###############################################
         */

        $this->UserInterface->AppendHTML($_appendHTML . '<script type="text/javascript">QueueFunction(function(){ $("#kql").kql({"kqlPath":"' . SWIFT::Get('basename') . '/Reports/KQL/FetchKQLJSON/' . $_baseTableName . '"}); });</script>');

        $this->UserInterface->End();

        return true;
    }


    /**
     * Render the Report
     *
     * @author Varun Shoor
     * @param SWIFT_Report $_SWIFT_ReportObject The SWIFT_Report Object Pointer
     * @param SWIFT_ReportRender $_SWIFT_ReportRenderObject
     * @return bool "true" on Success, "false" otherwise
     */
    public function Generate(SWIFT_Report $_SWIFT_ReportObject, SWIFT_ReportRender $_SWIFT_ReportRenderObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this) . '2', '/Reports/Report/EditSubmit/' . $_SWIFT_ReportObject->GetReportID(), SWIFT_UserInterface::MODE_EDIT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left', '/Reports/Report/Edit/' . $_SWIFT_ReportObject->GetReportID(), SWIFT_UserInterfaceToolbar::LINK_VIEWPORT);
        $this->UserInterface->Toolbar->AddButton('');

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('print'), 'fa-print', 'PrintReport(\'' . (int) ($_SWIFT_ReportObject->GetReportID()) . '\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
//        $this->UserInterface->Toolbar->AddButton($this->Language->Get('mail'), 'icon_mail.gif');

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('export') . ' <img src="'. SWIFT::Get('themepath') . 'images/menudropgray.gif" align="absmiddle" border="0" />', 'fa-sign-out', 'UIDropDown(\'exportmenu\', event, \'exportOptions\', \'tabtoolbartable\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, 'exportOptions', '', false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('schedule'), 'fa-calendar', "UICreateWindow('" . SWIFT::Get('basename') . "/Reports/Schedule/Dialog/" . $_SWIFT_ReportObject->GetReportID() . "', 'schedulereport', '" .
            $this->Language->Get('reportemailingproperties') . "', '" . $this->Language->Get('loadingwindow') . "', 700, 385, true, this);", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);

        $_exportOptionsMenu = $this->GetExportOptionsMenu($_SWIFT_ReportObject->GetReportID());

        $this->UserInterface->AppendHTML($_exportOptionsMenu);

        $_reportTitle = $_SWIFT_ReportObject->GetProperty('title');
        $_baseTableName = $_SWIFT_ReportObject->GetProperty('basetablename');
        $_reportCategoryID = $_SWIFT_ReportObject->GetProperty('reportcategoryid');
        $_visibilityType = $_SWIFT_ReportObject->GetProperty('visibilitytype');
        $_kql = $_SWIFT_ReportObject->GetProperty('kql');

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */

        $_GeneralTabObject = $this->UserInterface->AddTab($_reportTitle, 'icon_report.png', 'general', true);

        $_reportCharts = $_SWIFT_ReportRenderObject->GetChartsOutput();
        $_fusionChartsJS = '<script type="text/javascript" src="' . SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THIRDPARTYDIRECTORY . '/FusionCharts/Charts/FusionCharts.js"></script>';
        $_fusionChartsJS .= '<script type="text/javascript" src="' . SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THIRDPARTYDIRECTORY . '/FusionCharts/Charts/FusionCharts.HC.js"></script>';
        $_fusionChartsJS .= '<script type="text/javascript" src="' . SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THIRDPARTYDIRECTORY . '/FusionCharts/Charts/FusionCharts.HC.Charts.js"></script>';

        $_columnContainer = array();
        $_columnContainer[0]['value'] = '<div id="reportviewportcontainer">' . $_fusionChartsJS . $_reportCharts . $_SWIFT_ReportRenderObject->GetOutput() . '</div>';
        $_columnContainer[0]['align'] = 'left';

        $_GeneralTabObject->Row($_columnContainer, 'tablerowbase_tr');

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Reports Grid
     *
     * @author Varun Shoor
     * @param int|false $_searchStoreID (OPTIONAL) The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Base\Library\Staff\SWIFT_Staff_Exception
     */
    public function RenderGrid($_searchStoreID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        self::$_schemaContainer = SWIFT_KQLSchema::GetCombinedSchema();

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('reportgrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH)
        {
            $this->UserInterfaceGrid->SetSearchQuery(
            "SELECT reports.*, (select if(count(*) > 0, '".$this->Language->Get('yes')."', '".$this->Language->Get('no')."') from " . TABLE_PREFIX . "reportschedules rs where rs.reportid = reports.reportid) as scheduled FROM " . TABLE_PREFIX . "reports AS reports
                LEFT JOIN " . TABLE_PREFIX . "staff AS staff ON (reports.creatorstaffid = staff.staffid)
                WHERE (" . $this->UserInterfaceGrid->BuildSQLSearch('reports.title') . ")
                    AND (reports.visibilitytype = '" . SWIFT_Report::VISIBLE_PUBLIC . "'
                        OR (reports.visibilitytype = '" . SWIFT_Report::VISIBLE_PRIVATE . "' AND reports.creatorstaffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                        )",

            "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "reports AS reports
                LEFT JOIN " . TABLE_PREFIX . "staff AS staff ON (reports.creatorstaffid = staff.staffid)
                WHERE (" . $this->UserInterfaceGrid->BuildSQLSearch('reports.title') . ")
                    AND (reports.visibilitytype = '" . SWIFT_Report::VISIBLE_PUBLIC . "'
                        OR (reports.visibilitytype = '" . SWIFT_Report::VISIBLE_PRIVATE . "' AND reports.creatorstaffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                        )");
        }

        $this->UserInterfaceGrid->SetSearchStoreOptions($_searchStoreID,
                "SELECT reports.*, (select if(count(*) > 0, '".$this->Language->Get('yes')."', '".$this->Language->Get('no')."') from " . TABLE_PREFIX . "reportschedules rs where rs.reportid = reports.reportid) as scheduled FROM " . TABLE_PREFIX . "reports AS reports
                    WHERE reports.reportid IN (%s)",
                SWIFT_SearchStore::TYPE_REPORTS, '/Reports/Report/Manage/-1');

        $this->UserInterfaceGrid->SetQuery("SELECT reports.*, (select if(count(*) > 0, '".$this->Language->Get('yes')."', '".$this->Language->Get('no')."') from " . TABLE_PREFIX . "reportschedules rs where rs.reportid = reports.reportid) as scheduled FROM " . TABLE_PREFIX . "reports AS reports
                LEFT JOIN " . TABLE_PREFIX . "staff AS staff ON (reports.creatorstaffid = staff.staffid)
                WHERE (reports.visibilitytype = '" . SWIFT_Report::VISIBLE_PUBLIC . "'
                        OR (reports.visibilitytype = '" . SWIFT_Report::VISIBLE_PRIVATE . "' AND reports.creatorstaffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                        )",
            "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "reports AS reports
                LEFT JOIN " . TABLE_PREFIX . "staff AS staff ON (reports.creatorstaffid = staff.staffid)
                WHERE (reports.visibilitytype = '" . SWIFT_Report::VISIBLE_PUBLIC . "'
                        OR (reports.visibilitytype = '" . SWIFT_Report::VISIBLE_PRIVATE . "' AND reports.creatorstaffid = '" . (int) ($_SWIFT->Staff->GetStaffID()) . "')
                        )");

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('reportid', 'reportid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('reports.title', $this->Language->Get('reporttitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('scheduled', $this->Language->Get('scheduled'), SWIFT_UserInterfaceGridField::TYPE_DB, 100, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('reports.basetablenametext', $this->Language->Get('basetable'), SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('reports.visibilitytype', $this->Language->Get('visibilitytype'), SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('reports.executedateline', $this->Language->Get('lastused'), SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
                array('Controller_Report', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLink("UICreateWindow('" . SWIFT::Get('basename') . "/Reports/Report/InsertDialog', 'insertreport', '" . $this->Language->Get('winnewreport') . "', '" . $this->Language->Get('loadingwindow') . "', 400, 260, true, this);");

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

        $_icon = 'icon_report.png';
        if ($_fieldContainer['visibilitytype'] == SWIFT_Report::VISIBLE_PRIVATE)
        {
            $_icon = 'icon_report_user.png';
        }

        $_fieldContainer['reports.title'] = '<a href="' . SWIFT::Get('basename') . '/Reports/Report/Edit/' . (int) ($_fieldContainer['reportid']) . '" viewport="1" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['title']) . '</a>';

        $_fieldContainer['reports.visibilitytype'] = SWIFT_Report::GetVisibilityLabel($_fieldContainer['visibilitytype']);

        $_executeDateline = $_SWIFT->Language->Get('reportnever');
        if (isset($_fieldContainer['executedateline']) && !empty($_fieldContainer['executedateline'])) {
            $_executeDateline = SWIFT_Date::ColorTime(DATENOW-$_fieldContainer['executedateline']);
        }
        $_fieldContainer['reports.executedateline'] = $_executeDateline;

        $_baseTableName = $_fieldContainer['basetablename'];
        $_baseTableTitle = $_fieldContainer['basetablename'];
        if (isset(self::$_schemaContainer[$_baseTableName][SWIFT_KQLSchema::SCHEMA_TABLELABEL])) {
            $_baseTableTitle = SWIFT_KQLSchema::GetLabel(self::$_schemaContainer[$_baseTableName][SWIFT_KQLSchema::SCHEMA_TABLELABEL]);
        } else if (isset($_fieldContainer['basetablenametext']) && !empty($_fieldContainer['basetablenametext'])) {
            $_baseTableTitle = $_fieldContainer['basetablenametext'];
        }

        $_fieldContainer['reports.basetablenametext'] = htmlspecialchars($_baseTableTitle);

        if ($_baseTableName == 'calls') {
            $_icon = 'fa-phone';
        } else if ($_baseTableName == 'chatobjects' || $_baseTableName == 'chathits') {
            $_icon = 'fa-comments';
        } else if ($_baseTableName == 'escalationpaths') {
            $_icon = 'fa-arrow-circle-o-up';
        } else if ($_baseTableName == 'ticketauditlogs') {
            $_icon = 'fa-users';
        } else if ($_baseTableName == 'tickettimetracks') {
            $_icon = 'fa-clock-o';
        } else if ($_baseTableName == 'ratingresults') {
            $_icon = 'fa-star-half-o';
        } else if ($_baseTableName == 'tickets' || $_baseTableName == 'ticketposts') {
            $_icon = 'fa-ticket';
        }

        $_fieldContainer['icon'] = '<i class="fa ' . $_icon . '" aria-hidden="true"></i>';

        return $_fieldContainer;
    }

    /**
     * Render the Information Box
     *
     * @author Varun Shoor
     * @param string $_categoryTitle
     * @param string $_baseTableTitle
     * @param string $_visibilityType
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderInfoBox($_categoryTitle, $_baseTableTitle, $_visibilityType) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_informationHTML = '';

        $_informationHTML .= '<div class="ticketinfoitem">' .
                '<div class="ticketinfoitemtitle">' . $this->Language->Get('rinfocategory') . '</div><div class="ticketinfoitemcontent">' . htmlspecialchars($_categoryTitle) . '</div></div>';

        $_informationHTML .= '<div class="ticketinfoitem">' .
                '<div class="ticketinfoitemtitle">' . $this->Language->Get('rinfoprimarytable') . '</div><div class="ticketinfoitemcontent">' . htmlspecialchars($_baseTableTitle) . '</div></div>';

        $_informationHTML .= '<div class="ticketinfoitem">' .
                '<div class="ticketinfoitemtitle">' . $this->Language->Get('rinfopermission') . '</div><div class="ticketinfoitemcontent">' . htmlspecialchars($_visibilityType) . '</div></div>';


        $this->UserInterface->AddNavigationBox($this->Language->Get('informationbox'), $_informationHTML);

        return true;
    }

    /**
     * Get the Export Options Menu
     *
     * @author Andriy Lesyuk
     * @return string The Export Options Menu Data
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetExportOptionsMenu($_reportID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return '';
        }

        $_exportFormatContainer = SWIFT_ReportExport::GetExportFormatContainer();

        $_exportOptionsHTML = '<ul class="swiftdropdown" id="exportmenu">';

        foreach ($_exportFormatContainer as $_exportFormat => $_exportFormatInfo) {
            $_exportOptionsHTML .= '<li class="swiftdropdownitemparent" onclick="javascript: navigateWindow(\''. SWIFT::Get('basename') .'/Reports/Report/Export/'. $_reportID .'/'. $_exportFormat .'\');">';
            $_exportOptionsHTML .= '<div class="swiftdropdownitem">';
            $_exportOptionsHTML .= '<div class="swiftdropdownitemimage">';
            $_exportOptionsHTML .= '<img src="' . SWIFT::Get('themepath') . 'images/' . $_exportFormatInfo[1] . '" align="absmiddle" border="0" />';
            $_exportOptionsHTML .= '</div>';
            $_exportOptionsHTML .= '<div class="swiftdropdownitemtext" onclick="javascript: void(0);">' . $this->Language->Get('exportas') . ' ' . $this->Language->Get($_exportFormatInfo[0]) . '</div>';
            $_exportOptionsHTML .= '</div>';
            $_exportOptionsHTML .= '</li>';
        }

        $_exportOptionsHTML .= '</ul>';

        return $_exportOptionsHTML;
    }

}
?>
