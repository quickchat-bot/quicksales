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

use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_Interface;
use SWIFT_View;

/**
 * The Diagnostics View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property Controller_Diagnostics $Controller
 * @author Varun Shoor
 */
class View_Diagnostics extends SWIFT_View
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Renders the PHP Info
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderPHPInfo()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/Diagnostics/PHPInfo', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('diagnostics'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN PHPINFO TAB
         * ###############################################
         */
        $_PHPInfoTabObject = $this->UserInterface->AddTab($this->Language->Get('phpinfo'), 'icon_form.gif', 'phpinfo', true);

        $_PHPInfoTabObject->Title($this->Language->Get('configurationvariables'), 'icon_settings2.gif');
        $_configurationVariableContainer = $this->Controller->DiagnosticsPHPInfo->ParseConfig();

        foreach ($_configurationVariableContainer as $_key => $_val) {
            $_PHPInfoTabObject->DefaultRow($_key, $_val);
        }

        $_extensionVariableContainer = $this->Controller->DiagnosticsPHPInfo->ParseExtensions();
        foreach ($_extensionVariableContainer as $_key => $_val) {
            $_PHPInfoTabObject->Title(sprintf($this->Language->Get('phpextension'), $_key), 'icon_settings2.gif');

            if (!_is_array($_val)) {
                continue;
            }

            foreach ($_val as $_subKey => $_subVal) {
                $_finalValue = '';

                if (_is_array($_subVal)) {
                    foreach ($_subVal as $_splitKey => $_splitVal) {
                        $_finalValue .= $_splitVal . '<BR />' . SWIFT_CRLF;
                    }
                } else {
                    $_finalValue = $_subVal;
                }

                $_PHPInfoTabObject->DefaultDescriptionRow($_subKey, '', $_finalValue);
            }
        }

        /*
         * ###############################################
         * END PHPINFO TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Renders the Cache Dialog
     *
     * @author Varun Shoor
     * @param string $_cacheTitle The Cache Title
     * @param string $_cacheData The Cache Data
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderCacheDialog($_cacheTitle, $_cacheData)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/Diagnostics/CacheInformation', SWIFT_UserInterface::MODE_EDIT, true);
        $this->UserInterface->SetDialogOptions(false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('diagnostics'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        $_GeneralTabObject->Textarea('cache', '', '', $_cacheData, 30, 22);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Renders the License Information Dialog
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderLicenseDialog()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_domainList = array();

        $_notAvailable = $this->Language->Get('na');

        $_licenseExpiry = $_licenseFullName = $_licenseUniqueID = $_licenseProduct = $_licenseProductPackage = $_licenseStaff = $_licensePackage = $_licenseOrganization = $_notAvailable;

        if (SWIFT::Get('licenseexpiry') !== false && SWIFT::Get('licenseexpiry') > 100) {
            $_licenseExpiry = SWIFT_Date::Get(SWIFT_Date::TYPE_DATE, SWIFT::Get('licenseexpiry'));
        } else {
            $_licenseExpiry = $this->Language->Get('licexpirenever');
        }

        if (SWIFT::Get('licensefullname') !== false) {
            $_licenseFullName = text_to_html_entities(SWIFT::Get('licensefullname'));
        }

        if (SWIFT::Get('licenseuniqueid') !== false) {
            $_licenseUniqueID = htmlspecialchars(SWIFT::Get('licenseuniqueid'));
        }

        if (SWIFT::Get('licenseproduct') !== false) {
            $_licenseProduct = htmlspecialchars(strtoupper(SWIFT::Get('licenseproduct')));
        }

        if (SWIFT::Get('licensestaff') !== false) {
            if (SWIFT::Get('licensestaff') == '0') {
                $_licenseStaff = $this->Language->Get('licunlimited');
            } else {
                $_licenseStaff = htmlspecialchars(SWIFT::Get('licensestaff'));
            }
        }

        if (SWIFT::Get('licensedomains') !== false) {
            $_domainList = SWIFT::Get('licensedomains');
        }

        if (SWIFT::Get('licensepackage') !== false) {
            $_licenseProductPackage = htmlspecialchars(strtoupper(SWIFT::Get('licensepackage')));
        }

        if (SWIFT::Get('licenseorganization') !== false) {
            $_licenseOrganization = htmlspecialchars(SWIFT::Get('licenseorganization'));
        }

        if (SWIFT::Get('licenseuniqueid') !== false) {
            $_licenseUniqueID = htmlspecialchars(SWIFT::Get('licenseuniqueid'));
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/Diagnostics/LicenseInformation', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('diagnostics'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('licenseinfo'), 'icon_lock.gif', 'general', true);

        $_GeneralTabObject->DefaultRow($this->Language->Get('licexpiry'), $_licenseExpiry);
        $_GeneralTabObject->DefaultRow($this->Language->Get('licfullname'), $_licenseFullName);
        $_GeneralTabObject->DefaultRow($this->Language->Get('licorganization'), $_licenseOrganization);
        $_GeneralTabObject->DefaultRow($this->Language->Get('licstaffcount'), $_licenseStaff);
        $_GeneralTabObject->DefaultRow($this->Language->Get('licuniqueid'), $_licenseUniqueID);
        $_GeneralTabObject->DefaultRow($this->Language->Get('licproduct'), $_licenseProduct);
        $_GeneralTabObject->DefaultRow($this->Language->Get('licproductpackage'), $_licenseProductPackage);

        $_GeneralTabObject->Title($this->Language->Get('licdomains'));

        $_GeneralTabObject->Description(implode('<br />', $_domainList), '', '', 2, true);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Renders the Bug Dialog
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderBugDialog()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        $this->UserInterface->Start(get_short_class($this), '/Base/Diagnostics/SendBug', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('send'), 'fa-check-circle');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('diagnostics'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('bugdetails'), 'icon_bugs.gif', 'general', true);

        $_GeneralTabObject->Text('subject', $this->Language->Get('bugsubject'), $this->Language->Get('desc_bugsubject'));
        $_GeneralTabObject->Text('fromname', $this->Language->Get('bugfromname'), $this->Language->Get('desc_bugfromname'), $_SWIFT->Staff->GetProperty('fullname'));
        $_GeneralTabObject->Text('fromemail', $this->Language->Get('bugfromemail'), $this->Language->Get('desc_bugfromemail'), $_SWIFT->Staff->GetProperty('email'));

        // KACT 5/pages/343
        $_descriptionText = SWIFT_CRLF . SWIFT_CRLF . $this->Language->Get('debuginfo') . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('dswifturl'), SWIFT::Get('swiftpath')) . SWIFT_CRLF; // SWIFT URL
        $_descriptionText .= sprintf($this->Language->Get('dswiftproduct'), SWIFT_PRODUCT) . SWIFT_CRLF; // Product
        $_descriptionText .= sprintf($this->Language->Get('dswiftversion'), SWIFT_VERSION) . SWIFT_CRLF; // Version
        $_descriptionText .= sprintf($this->Language->Get('dswiftbuilddate'), SWIFT::Get('builddate')) . SWIFT_CRLF; // Build Date
        $_descriptionText .= sprintf($this->Language->Get('dswiftbuildtype'), SWIFT::Get('buildtype')) . SWIFT_CRLF; // Build Type
        $_descriptionText .= sprintf($this->Language->Get('dphpversion'), phpversion()) . SWIFT_CRLF; // PHP Version

        $_maxFileSize = ini_get('upload_max_filesize');
        $_descriptionText .= sprintf($this->Language->Get('dphpuploadsize'), IIF(empty($_maxFileSize), 'NOT SPECIFIED', $_maxFileSize)) . SWIFT_CRLF;

        // KACT 5/pages/343
        $_safeMode = ini_get('safe_mode');
        $_safeMode = IIF(empty($_safeMode), $this->Language->Get('no'), $this->Language->Get('yes'));
        $_descriptionText .= sprintf($this->Language->Get('dphpsafemode'), $_safeMode) . SWIFT_CRLF;

        $_databaseData = $this->Database->GetADODBObject()->ServerInfo();
        $_descriptionText .= sprintf($this->Language->Get('ddbtype'), ucfirst(DB_TYPE)) . SWIFT_CRLF; // Database Version
        $_descriptionText .= sprintf($this->Language->Get('ddbversion'), $_databaseData['version']) . SWIFT_CRLF; // MySQL Version
        $_descriptionText .= sprintf($this->Language->Get('duseragent'), $_SERVER['HTTP_USER_AGENT']) . SWIFT_CRLF; // MySQL Version
        $_descriptionText .= sprintf($this->Language->Get('dserversoftware'), $_SERVER['SERVER_SOFTWARE']) . SWIFT_CRLF; // Server Software
        $_descriptionText .= sprintf($this->Language->Get('dos'), @PHP_OS) . SWIFT_CRLF; // OS

        $_GeneralTabObject->Title($this->Language->Get('description'), 'icon_doublearrows.gif');

        // KACT 5/p[ages/343
        // Added Feb 11 2008 JCH
        // for admin bug report dialog, new fields added feb 11 2008 by jch

        $_loadedExtensionsContainer = get_loaded_extensions();
        natcasesort($_loadedExtensionsContainer);

        $_textSeperator = SWIFT_CRLF . ' - ';
        $_extensionList = $_textSeperator . implode($_textSeperator, $_loadedExtensionsContainer);

        $_descriptionText .= sprintf($this->Language->Get('php_installed_extensions'), $_extensionList) . SWIFT_CRLF;

        $_descriptionText .= sprintf($this->Language->Get('php_uname'), php_uname()) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_memory_limit'), ini_get('memory_limit')) . SWIFT_CRLF;

        $_descriptionText .= sprintf($this->Language->Get('swift_language_base_charset'), $this->Language->Get('base_charset')) . SWIFT_CRLF; // todo
        $_descriptionText .= sprintf($this->Language->Get('swift_language_html_charset'), $this->Language->Get('html_charset')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('swift_language_text_charset'), $this->Language->Get('text_charset')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('swift_language_html_encoding'), $this->Language->Get('html_encoding')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('swift_language_text_encoding'), $this->Language->Get('text_encoding')) . SWIFT_CRLF;

        // yes, this one takes two args.  yes, it's an abberation.  deal with it.
        $_descriptionText .= sprintf($this->Language->Get('disk_free_space'), strval(disk_free_space('.')), strval(disk_total_space('.'))) . SWIFT_CRLF;

        $_descriptionText .= sprintf($this->Language->Get('php_ini_max_execution_time'), ini_get('max_execution_time')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_display_errors'), ini_get('display_errors')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_quiet_eval'), ini_get('quiet_eval')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_error_reporting'), error_reporting()) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_output_buffering'), ini_get('output_buffering')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('zend_version'), zend_version()) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_register_globals'), ini_get('register_globals')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_magic_quotes_gpc'), get_magic_quotes_gpc()) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_magic_quotes_runtime'), get_magic_quotes_runtime()) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_post_max_size'), ini_get('post_max_size')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_upload_max_size'), ini_get('upload_max_filesize')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_allow_calltime_pass_reference'), ini_get('allow_call_time_pass_reference')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_allow_url_fopen'), ini_get('allow_url_fopen')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_always_populate_raw_post_data'), ini_get('always_populate_raw_post_data')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_default_charset'), ini_get('default_charset')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_default_mimetype'), ini_get('default_mimetype')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_display_startup_errors'), ini_get('display_startup_errors')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_expose_php'), ini_get('expose_php')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_ignore_repeated_errors'), ini_get('ignore_repeated_errors')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_max_input_time'), ini_get('max_input_time')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_output_handler'), ini_get('output_handler')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_report_zend_debug'), ini_get('report_zend_debug')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_safe_mode'), ini_get('safe_mode')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_bug_compat_42'), ini_get('session.bug_compat_42')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_bug_compat_warn'), ini_get('session.bug_compat_warn')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_track_errors'), ini_get('track_errors')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_upload_max_filesize'), ini_get('upload_max_filesize')) . SWIFT_CRLF;
        $_descriptionText .= sprintf($this->Language->Get('php_ini_zend_1_compatibility_mode'), ini_get('zend.ze1_compatibility_mode')) . SWIFT_CRLF;

        if (function_exists('getrusage')) {
            $_rUsageData = getrusage(1);
            $_rUsage = '';
            foreach ($_rUsageData as $_rUsageKey => $_rUsageVal) {
                $_rUsage .= $_textSeperator . $_rUsageKey . ': ' . $_rUsageVal;
            }
        } else {
            $_rUsage = 'No rusage implementation (common on Windows)';
        }

        $_descriptionText .= sprintf($this->Language->Get('php_rusage'), $_rUsage) . SWIFT_CRLF;

        // jch add complete
        $_GeneralTabObject->Textarea('contents', '', '', $_descriptionText, 100, 15);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Cache Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderCacheGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('cachegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM (SELECT vkey as display_title, r.* FROM ' . TABLE_PREFIX . 'registry r) t0 WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('vkey') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'registry WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('vkey') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM (SELECT vkey as display_title, r.* FROM ' . TABLE_PREFIX . 'registry r) t0', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'registry');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('vkey', 'vkey', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('display_title', $this->Language->Get('cachetitle'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        /*
         * BUG FIX - Simaranjit Singh
         *
         * SWIFT-2968 "Admin CP->Options->Diagnostics->Cache info->Sort by size" does not return results.
         *
         */
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('datasize', $this->Language->Get('size'), SWIFT_UserInterfaceGridField::TYPE_DB, 200, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('dateline', $this->Language->Get('lastupdate'), SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'CacheGridRender'));

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Cache Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array
     */
    public static function CacheGridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_icon = 'icon_file.gif';

        $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/' . $_icon . '" align="absmiddle" border="0" />';

        $_fieldContainer['datasize'] = FormattedSize($_fieldContainer['datasize']);

        $_fieldContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATE, $_fieldContainer['dateline']);

        $_fieldContainer['display_title'] = '<a href="' . SWIFT::Get('basename') . '/Base/Diagnostics/ViewCache/' . Clean($_fieldContainer['vkey']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" . SWIFT::Get('basename') . '/Base/Diagnostics/ViewCache/' . Clean($_fieldContainer['vkey']) . "', 'editcache', '" . sprintf($_SWIFT->Language->Get('wineditcache'), htmlspecialchars($_fieldContainer['vkey'])) . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 730, 650, true, this);" . '" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['vkey']) . '</a>';

        return $_fieldContainer;
    }

    /**
     * Render the Active Sessions Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderActiveSessionsGrid()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('activesessionsgrid'), true, false, 'base');

        // replace sessiontype IDs with the descriptions
        $_sessionTypes = [
            SWIFT_Interface::INTERFACE_API,
            SWIFT_Interface::INTERFACE_STAFF,
            SWIFT_Interface::INTERFACE_ADMIN,
            SWIFT_Interface::INTERFACE_CLIENT,
            SWIFT_Interface::INTERFACE_WINAPP,
            SWIFT_Interface::INTERFACE_CONSOLE,
            SWIFT_Interface::INTERFACE_SETUP,
            SWIFT_Interface::INTERFACE_VISITOR,
            SWIFT_Interface::INTERFACE_CALLBACK,
            SWIFT_Interface::INTERFACE_CRON,
            SWIFT_Interface::INTERFACE_CHAT,
            SWIFT_Interface::INTERFACE_PDA,
            SWIFT_Interface::INTERFACE_RSS,
            SWIFT_Interface::INTERFACE_SYNCWORKS,
            SWIFT_Interface::INTERFACE_INSTAALERT,
            SWIFT_Interface::INTERFACE_MOBILE,
            SWIFT_Interface::INTERFACE_ARCHIVE,
            SWIFT_Interface::INTERFACE_INTRANET,
            SWIFT_Interface::INTERFACE_GEOIP,
            SWIFT_Interface::INTERFACE_STAFFAPI,
            SWIFT_Interface::INTERFACE_TESTS,
        ];

        $_select = 'select sessionid, useragent, ipaddress, lastactivity, case ';
        foreach ($_sessionTypes as $type) {
            $_sessionKey = 'sess' . $type;
            $_select .= ' WHEN sessiontype = ' . $type . ' THEN "' . $_SWIFT->Language->Get($_sessionKey) . '" ';
        }
        $_select .= ' else "Other" END AS sessiontype FROM ' . TABLE_PREFIX . 'sessions ';

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery($_select . ' WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('ipaddress') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('useragent') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'sessions WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('ipaddress') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('useragent') . ')');
        }

        //Reverse last activity sorting to view last activity elapsed time correctly in UI
        $this->UserInterfaceGrid->SetReversedSortField('lastactivity');

        $this->UserInterfaceGrid->SetQuery($_select, 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'sessions');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('sessionid', 'sessionid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16, SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('useragent', $this->Language->Get('useragent'), SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('ipaddress', $this->Language->Get('ipaddress'), SWIFT_UserInterfaceGridField::TYPE_DB, 150, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('lastactivity', $this->Language->Get('lastactivity'), SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('sessiontype', $this->Language->Get('sessiontype'), SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'ActiveSessionsGridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('killsessions'), 'icon_block.gif', array('Base\Admin\Controller_Diagnostics', 'KillSessions'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Active Sessions Grid Rendering Function
     *
     * @author Varun Shoor
     * @param array $_fieldContainer The Field Record Value Container
     * @return array
     */
    public static function ActiveSessionsGridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_threshold = DATENOW - 600;
        if ($_fieldContainer['lastactivity'] < $_threshold) {
            $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_bulboff.gif" border="0" align="absmiddle" />';
        } else {
            $_fieldContainer['icon'] = '<img src="' . SWIFT::Get('themepath') . 'images/icon_bulbon.gif" border="0" align="absmiddle" />';
        }

        $_fieldContainer['ipaddress'] = htmlspecialchars($_fieldContainer['ipaddress']);
        $_fieldContainer['lastactivity'] = SWIFT_Date::ColorTime((DATENOW - $_fieldContainer['lastactivity']));

        $_fieldContainer['useragent'] = htmlspecialchars($_fieldContainer['useragent']);

        return $_fieldContainer;
    }

    /**
     * Render the Rebuild Cache
     *
     * @author Varun Shoor
     * @param array $_cacheContainer The Cache Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function RenderRebuildCache($_cacheContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/Diagnostics/RebuildCache', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('diagnostics'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN DIAGNOSTICS TAB
         * ###############################################
         */
        $_RebuildCacheTabObject = $this->UserInterface->AddTab($this->Language->Get('tabrebuildcache'), 'icon_form.gif', 'general', true);

        $_columnContainer = array();
        $_columnContainer[0]['align'] = 'left';
        $_columnContainer[0]['nowrap'] = true;
        $_columnContainer[0]['value'] = $this->Language->Get('cachename');
        $_columnContainer[2]['align'] = 'left';
        $_columnContainer[2]['nowrap'] = true;
        $_columnContainer[2]['value'] = $this->Language->Get('result');
        $_columnContainer[2]['width'] = '200';
        $_RebuildCacheTabObject->Row($_columnContainer, 'gridtabletitlerow');


        foreach ($_cacheContainer as $_cacheResult) {
            if (!$_cacheResult[1]) {
                $_statusImage = 'fa-minus-circle';
                $_statusText = $this->Language->Get('failure');
            } else {
                $_statusImage = 'fa-check-circle';
                $_statusText = $this->Language->Get('success');
            }

            $_columnContainer = array();
            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[0]['nowrap'] = true;
            $_columnContainer[0]['value'] = htmlspecialchars($_cacheResult[0]);

            $_columnContainer[1]['align'] = 'left';
            $_columnContainer[1]['nowrap'] = true;
            $_columnContainer[1]['value'] = '<i class="fa ' . $_statusImage . '" aria-hidden="true"></i>&nbsp;' . $_statusText;

            $_RebuildCacheTabObject->Row($_columnContainer, IIF(!$_cacheResult[1], 'errorrow', false));
        }


        /*
         * ###############################################
         * END DIAGNOSTICS TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }
}

?>
