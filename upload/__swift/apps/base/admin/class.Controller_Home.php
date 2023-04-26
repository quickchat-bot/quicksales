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
use SimpleXMLElement;
use SWIFT;
use SWIFT_App;
use SWIFT_ErrorLog;
use SWIFT_Exception;
use Base\Models\GeoIP\SWIFT_GeoIP;
use SWIFT_Hook;
use Base\Models\Language\SWIFT_LanguagePhrase;
use SWIFT_Loader;
use Parser\Models\Log\SWIFT_ParserLog;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\Staff\SWIFT_StaffLoginLog;
use Base\Models\Template\SWIFT_Template;

/**
 * The Dashboard Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Home $View
 * @author Varun Shoor
 */
class Controller_Home extends Controller_admin
{
    private $_tabContainer = array();
    private $_failureContainer = array();
    private $_errorContainer = array();
    private $_geoIPErrorContainer = array();
    private $_templateList = array();
    private $_languagePhraseList = array();
    private $_parserLogContainer = array();

    // Core Constants
    const TAB_NAME = 0;
    const TAB_ICON = 1;
    const TAB_TITLE = 2;
    const TAB_CONTENTS = 3;
    const TAB_COUNTER = 4;

    const TAB_WELCOME = 'welcome';
    const TAB_RECENTACTIVITY = 'recentactivity';
    const TAB_ALERTS = 'alerts';
    const TAB_LOGINFAILURES = 'loginfailures';
    const TAB_ERRORLOG = 'errorlog';
    const TAB_PARSERLOG = 'parserlog';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Template->Assign('_isDashboard', true);

        $this->Language->Load('dashboard');
        $this->Language->Load('admin_geoip');

        $this->Load->Model('Staff:StaffLoginLog', [], true,false,'base');
        $this->Load->Model('Staff:StaffActivityLog', [], true,false,'base');

        $this->_failureContainer = SWIFT_StaffLoginLog::GetDashboardContainer();
        $this->_errorContainer = SWIFT_ErrorLog::GetDashboardContainer();
        $this->_geoIPErrorContainer = SWIFT_GeoIP::GetDashboardContainer();
        $this->_templateList = SWIFT_Template::GetUpgradeRevertList();
        $this->_languagePhraseList = SWIFT_LanguagePhrase::GetUpgradeRevertList();

        if (SWIFT_App::IsInstalled(APP_PARSER)) {
            SWIFT_Loader::LoadModel('Log:ParserLog', APP_PARSER);
            $this->_parserLogContainer = SWIFT_ParserLog::GetDashboardContainer();
        }
    }

    /**
     * The Main Dashboard Renderer
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Index()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Begin Hook: admin_dashboard_init
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('admin_dashboard_init')) ? eval($_hookCode) : false;
        // End Hook

//        $this->_BuildWelcomeTab();
        $this->_BuildAlertsTab();
        $this->_BuildRecentActivityTab();
        $this->_BuildLoginFailuresTab();
        $this->_BuildErrorLogTab();
        $this->_BuildParserLogTab();

        // Begin Hook: admin_dashboard_end
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('admin_dashboard_end')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->Header($this->Language->Get('dashboard'), 1, 0);


        // Begin Hook: admin_dashboard_output_start
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('admin_dashboard_output_start')) ? eval($_hookCode) : false;
        // End Hook

        $this->View->RenderDashboard();

        // Begin Hook: admin_dashboard_output_end
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('admin_dashboard_output_end')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Build the Welcome Tab
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _BuildWelcomeTab()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tabContents = '';

        $this->_AddTab(self::TAB_WELCOME, 'icon_dashboardwelcome.gif', $this->Language->Get('tabwelcome'), $_tabContents);

        return true;
    }

    /**
     * Build the Recent Activity Tab
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _BuildRecentActivityTab()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_activityContainer = SWIFT_StaffActivityLog::GetDashboardContainer();

        $_tabContents = $this->View->RenderRecentActivityTab($_activityContainer);

        $this->_AddTab(self::TAB_RECENTACTIVITY, 'icon_dashboardrecentactivity.gif', $this->Language->Get('tabrecentactivity'), $_tabContents, $_activityContainer[0]);

        return true;
    }

    /**
     * Build the Alerts Tab
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _BuildAlertsTab()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tabContents = '';
        $_alertIndex = 0;
        $_sevenDays = 7 * 86400;

        /*
         * ###############################################
         * BEGIN LICENSE CHECK
         * ###############################################
         */

        if (SWIFT::Get('licenseistrial') == 1 && (SWIFT::Get('licenseexpiry') - DATENOW) <= $_sevenDays) {
            $_tabContents .= $this->View->RenderError($this->Language->Get('titletrialexpirywarning'), sprintf($this->Language->Get('msgtrialexpirywarning'), date('d M Y h:i:s A', SWIFT::Get('licenseexpiry'))), '', false);
            $_alertIndex++;
        } elseif (SWIFT::Get('licenserenewal') !== false && SWIFT::Get('licenseexpiry') == 0) {
            if ((SWIFT::Get('licenserenewal') - DATENOW) <= 0) {
                $_tabContents .= $this->View->RenderError($this->Language->Get('titlelicenseexpired'), sprintf($this->Language->Get('msglicenseexpired'), date('d M Y h:i:s A', SWIFT::Get('licenserenewal'))), '', false);
                $_alertIndex++;
            } elseif ((SWIFT::Get('licenserenewal') - DATENOW) <= $_sevenDays) {
                $_tabContents .= $this->View->RenderError($this->Language->Get('titlelicenseexpirywarning'), sprintf($this->Language->Get('msglicenseexpirywarning'), date('d M Y h:i:s A', SWIFT::Get('licenserenewal'))), '', false);
                $_alertIndex++;
            }
        }

        /*
         * ###############################################
         * BEGIN TEMPLATE UPGRADE REVERT DETECTION
         * ###############################################
         */

        if (count($this->_templateList)) {
            $_finalTemplateText = '';
            $_templateIndex = 1;
            foreach ($this->_templateList as $_templateName) {
                $_finalTemplateText .= $_templateIndex . '. ' . htmlspecialchars($_templateName) . '<br />';
                $_templateIndex++;
            }
            $_tabContents .= $this->View->RenderAlert($this->Language->Get('titletemplaterevert'), sprintf($this->Language->Get('msgtemplaterevert'), count($this->_templateList)) . '<br />' . $_finalTemplateText);

            $_alertIndex++;
        }

        /*
         * ###############################################
         * END TEMPLATE UPGRADE REVERT DETECTION
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN LANGUAGE UPGRADE REVERT DETECTION
         * ###############################################
         */

        if (count($this->_languagePhraseList)) {
            $_finalLanguageText = '';
            $_languageIndex = 1;
            foreach ($this->_languagePhraseList as $_languageTitle => $_phraseList) {
                foreach ($_phraseList as $_phraseName) {
                    $_finalLanguageText .= $_languageIndex . '. ' . htmlspecialchars($_languageTitle) . ' &gt; ' . htmlspecialchars($_phraseName) . '<br />';
                    $_languageIndex++;
                }
            }
            $_tabContents .= $this->View->RenderAlert($this->Language->Get('titlelanguagerevert'), sprintf($this->Language->Get('msglanguagerevert'), $_languageIndex - 1) . '<br />' . $_finalLanguageText);

            $_alertIndex++;
        }

        /*
         * ###############################################
         * END LANGUAGE UPGRADE REVERT DETECTION
         * ###############################################
         */

        /*
         * ###############################################
         * BEGIN GEOIP DETECTION
         * ###############################################
         */

        if (count($this->_geoIPErrorContainer)) {
            foreach ($this->_geoIPErrorContainer as $_key => $_val) {
                $_tabContents .= $this->View->RenderAlert($_val['title'], $_val['contents']);

                $_alertIndex++;
            }
        }

        /*
         * ###############################################
         * END GEOIP DETECTION
         * ###############################################
         */

        /*
         * ###############################################
         * BEGIN LOGIN FAILURE DETECTION
         * ###############################################
         */

        if ($this->_failureContainer[0] > 0) {
            if ($this->_failureContainer[0] == 1) {
                $_tabContents .= $this->View->RenderAlert($this->Language->Get('titleloginfailuredetectedone'), $this->Language->Get('msgloginfailuredetectedone'));
            } else {
                $_tabContents .= $this->View->RenderAlert(sprintf($this->Language->Get('titleloginfailuredetected'), $this->_failureContainer[0]), sprintf($this->Language->Get('msgloginfailuredetected'), $this->_failureContainer[0]));
            }

            $_alertIndex++;
        }

        /*
         * ###############################################
         * END LOGIN FAILURE DETECTION
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN ERROR DETECTION
         * ###############################################
         */

        if ($this->_errorContainer[0] > 0) {
            $_tabContents .= $this->View->RenderAlert(sprintf($this->Language->Get('titleerrorsdetected'), $this->_errorContainer[0]), sprintf($this->Language->Get('msgerrorsdetected'), $this->_errorContainer[0]));

            $_alertIndex++;
        }

        /*
         * ###############################################
         * END ERROR DETECTION
         * ###############################################
         */


        /*
         * ###############################################
         * BEGIN PARSER DETECTION
         * ###############################################
         */

        if (isset($this->_parserLogContainer[0]) && $this->_parserLogContainer[0] > 0) {
            $_tabContents .= $this->View->RenderAlert(sprintf($this->Language->Get('titleparserlogfailuredetected'), $this->_parserLogContainer[0]), sprintf($this->Language->Get('msgparserlogfailuredetected'), $this->_parserLogContainer[0]));

            $_alertIndex++;
        }

        /*
         * ###############################################
         * END PARSER DETECTION
         * ###############################################
         */
        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-5138 Check and alert if `Email size limit` is more then the PHP memory_limit
         */
        $bytes = return_bytes(ini_get('memory_limit'));
        if ($bytes !== -1 && $bytes < $this->Settings->Get('pr_sizelimit')) {
            $_tabContents .= $this->View->RenderAlert($this->Language->Get('titlephpini'), sprintf($this->Language->Get('msgemailsizelimit'), ini_get('memory_limit')));
            $_alertIndex++;
        }

        if ($_alertIndex > 0) {
            $this->_AddTab(self::TAB_ALERTS, 'icon_dashboardalerts.gif', $this->Language->Get('tabalerts'), $_tabContents, $_alertIndex);
        }

        return true;
    }

    /**
     * Build the Login Failures Tab
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _BuildLoginFailuresTab()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tabContents = $this->View->RenderLoginFailureTab($this->_failureContainer);

        $this->_AddTab(self::TAB_LOGINFAILURES, 'icon_dashboardloginfailures.gif', $this->Language->Get('tabloginfailures'), $_tabContents, $this->_failureContainer[0]);

        return true;
    }

    /**
     * Build the Error Log Tab
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _BuildErrorLogTab()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tabContents = $this->View->RenderErrorLogTab($this->_errorContainer);

        $this->_AddTab(self::TAB_ERRORLOG, 'icon_dashboarderrorlog.gif', $this->Language->Get('taberrorlog'), $_tabContents, $this->_errorContainer[0]);

        return true;
    }

    /**
     * Build the Parser Log Tab
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _BuildParserLogTab()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!SWIFT_App::IsInstalled(APP_PARSER)) {
            return false;
        }

        $_tabContents = $this->View->RenderParserLogTab($this->_parserLogContainer);

        $this->_AddTab(self::TAB_PARSERLOG, 'icon_dashboardparserlog.gif', $this->Language->Get('tabparserlog'), $_tabContents, $this->_parserLogContainer[0]);

        return true;
    }

    /**
     * Adds a tab to the tab container
     *
     * @author Varun Shoor
     * @param string $_tabName The Tab Name
     * @param string $_tabIcon The Tab Icon
     * @param string $_tabTitle The Tab Title
     * @param string $_tabContents The Tab Contents
     * @param int $_tabCounter The Tab Counter
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _AddTab($_tabName, $_tabIcon, $_tabTitle, $_tabContents, $_tabCounter = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_tabContainer[] = array(self::TAB_NAME => $_tabName, self::TAB_ICON => $_tabIcon, self::TAB_TITLE => $_tabTitle, self::TAB_CONTENTS => $_tabContents,
            self::TAB_COUNTER => number_format($_tabCounter, 0));

        return true;
    }

    /**
     * Retrieve the Tab Container
     *
     * @author Varun Shoor
     * @return mixed "_tabContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _GetTabContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_tabContainer;
    }
}
