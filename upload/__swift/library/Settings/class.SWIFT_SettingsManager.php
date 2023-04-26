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

use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;

/**
 * The Settings Manager Class (Import/Update)
 *
 * @author Varun Shoor
 */
class SWIFT_SettingsManager extends SWIFT_Library
{
    // Core Constants
    const FILTER_NAME = 1;
    const FILTER_ID = 2;

    /** @var SWIFT_XML */
    public $XML;

    private $isSaas;

    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');

        $this->isSaas = preg_match('/.+saas.+/', strtolower(SWIFT::Get('licensepackage')));
    }

    /**
     * Render Settings
     *
     * @author Varun Shoor
     * @param \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $_SWIFT_UserInterfaceObject The SWIFT_UserInterface Object Pointer
     * @param int $_filterMode The Filter Mode (NAME/ID)
     * @param array $_filterSettingGroupIDList Filter by Setting Group ID
     * @param array $_filterSettingTitleList Filter by Setting Title
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Render(SWIFT_UserInterface $_SWIFT_UserInterfaceObject, $_filterMode = self::FILTER_NAME, $_filterSettingGroupIDList = array(), $_filterSettingTitleList = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded() || !$_SWIFT_UserInterfaceObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        require_once ('./'. SWIFT_BASEDIRECTORY .'/'. SWIFT_INCLUDESDIRECTORY .'/functions.settings.php');

        $_rebuildCache = false;

        $_settingGroupName = '';

        if (_is_array($_filterSettingGroupIDList))
        {
            $_settingGroupsContainer = array();

            if ($_filterMode == self::FILTER_NAME)
            {
                $this->Database->Query("SELECT * FROM ". TABLE_PREFIX ."settingsgroups WHERE name IN (". BuildIN($_filterSettingGroupIDList) .")");
            } else {
                $this->Database->Query("SELECT * FROM ". TABLE_PREFIX ."settingsgroups WHERE sgroupid IN (". BuildIN($_filterSettingGroupIDList) .")");
            }
            while ($this->Database->NextRecord())
            {
                $_settingGroupsContainer[$this->Database->Record['sgroupid']] = $this->Database->Record;
            }

            $_displaySetting = true;

            if (_is_array($_settingGroupsContainer))
            {
                foreach ($_settingGroupsContainer as $_key => $_val)
                {
                    if (_is_array($_filterSettingTitleList))
                    {
                        $_displaySetting = false;
                    }

                    $_settingGroupName = $_val['name'];

                    $_finalSettingsGroupName = $this->Language->Get($_val['name']);
                    if (empty($_finalSettingsGroupName))
                    {
                        $_finalSettingsGroupName = $_settingGroupName;
                    }

                    $_extendedName = ': ' . htmlspecialchars($_finalSettingsGroupName);

                    $_SettingsTabObject = $_SWIFT_UserInterfaceObject->AddTab($this->Language->Get('settings') . $_extendedName, 'icon_settings2.gif', 'settings', true);
                    $_SettingsTabObject->Hidden('step', '1');

                    $_SWIFT_UserInterfaceObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
                    $_SWIFT_UserInterfaceObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('settings'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

                    $this->Database->Query("SELECT * FROM ". TABLE_PREFIX ."settingsfields WHERE sgroupid = '". (int) ($_val['sgroupid']) ."' ORDER BY displayorder ASC");
                    while ($this->Database->NextRecord())
                    {
                        if ((!SWIFT::Get('demo') || SWIFT::Get('demo') == false) && isset($_POST[$this->Database->Record['name']]) && $_SWIFT->Staff->GetPermission('admin_canupdatesettings') != '0')
                        {
                            $_doUpdateField = true;

                            if ($this->Database->Record['settingtype'] == 'url')
                            {
                                $_POST[$this->Database->Record['name']] = AddTrailingSlash($_POST[$this->Database->Record['name']]);

                                // URL is not in proper syntax?
                                if (!preg_match("@^(?:https?)://(?:[-.\w]+(?:\.[\w]{2,6})?|[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3})(?::[\d]{1,5})?(?:[/\\\\][-=\w_?&%+$.!*'()/\\\\]+)?@i", $_POST[$this->Database->Record['name']]))
                                {
                                    SWIFT::ErrorField($this->Database->Record['name']);

                                    $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('titleurlinvaliddata'), $this->Language->Get('desc_titleurlinvaliddata'));

                                    $_doUpdateField = false;
                                }
                            } else if ($this->Database->Record['name'] == 'general_returnemail') {
                                if (!IsEmailValid($_POST[$this->Database->Record['name']])) {
                                    SWIFT::ErrorField($this->Database->Record['name']);

                                    $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

                                    $_doUpdateField = false;
                                }

                            /*
                             * BUG FIX - Varun Shoor
                             *
                             * SWIFT-1529 Session Expired Due to Inactivity should not be set to empty
                             *
                             * Comments: None
                             */
                            } else if ($this->Database->Record['name'] == 'security_sessioninactivity') {
                                if (empty($_POST[$this->Database->Record['name']])) {
                                    SWIFT::ErrorField($this->Database->Record['name']);

                                    $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

                                    $_doUpdateField = false;
                                }


                                /*
                                 * BUG FIX - Verem Dugeri
                                 *
                                 * KAYAKO-2969 System accepts updating session expiry to invalid text value, and Admin will be blocked from Login to system as timeout is considered as zero
                                 *
                                 * Comments: None
                                 */

                                if (!is_numeric($_POST[$this->Database->Record['name']]) || $_POST[$this->Database->Record['name']] <= 0) {
                                    SWIFT::ErrorField($this->Database->Record['name']);

                                    $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('titleinvalidsessionexpiry'), $this->Language->Get('messageinvalidsessionexpiry'));

                                    $_doUpdateField = false;
                                }
                            } else if ($this->Database->Record['name'] === 'security_visitorinactivity') {
                                if (empty($_POST[$this->Database->Record['name']]) ) {
                                    SWIFT::ErrorField($this->Database->Record['name']);
                                    $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

                                    $_doUpdateField = false;
                                }

                                if (!is_numeric($_POST[$this->Database->Record['name']]) || $_POST[$this->Database->Record['name']] <= 0) {
                                    SWIFT::ErrorField($this->Database->Record['name']);
                                    $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('titleinvalidsessionexpiry'), $this->Language->Get('messageinvalidsessionexpiry'));

                                    $_doUpdateField = false;
                                }
                            } else if ($this->Database->Record['name'] === 'user_delcleardays') {
                                if (empty($_POST[$this->Database->Record['name']])) {
                                    SWIFT::ErrorField($this->Database->Record['name']);
                                    $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));
                                    $_doUpdateField = false;
                                } else if (!preg_match('/^(?=.*[1-9])[0-9]+$/', $_POST[$this->Database->Record['name']])) {
                                    SWIFT::ErrorField($this->Database->Record['name']);
                                    $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('user_delcleardays_invalid'), $this->Language->Get('d_user_delcleardays_invalid'));
                                    $_doUpdateField = false;
                                }
                            } else if (in_array($this->Database->Record['name'], ['security_scloginattempts', 'security_loginattempts'])) {
                                if (empty($_POST[$this->Database->Record['name']])) {
                                    SWIFT::ErrorField($this->Database->Record['name']);
                                    $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));
                                    $_doUpdateField = false;
                                } else if (!preg_match('/^(?=.*[1-9])[0-9]+$/', $_POST[$this->Database->Record['name']])) {
                                    SWIFT::ErrorField($this->Database->Record['name']);
                                    $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('security_loginattempts_invalid'), $this->Language->Get('d_security_loginattempts_invalid'));
                                    $_doUpdateField = false;
                                }
                            } else if ($this->Database->Record['name'] === 'livesupport_clientpagerefresh') {
                                if (empty($_POST[$this->Database->Record['name']])) {
                                    SWIFT::ErrorField($this->Database->Record['name']);
                                    $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));
                                    $_doUpdateField = false;
                                } else if ($this->isSaas && (!is_numeric($_POST[$this->Database->Record['name']]) || $_POST[$this->Database->Record['name']] < 60)) {
                                    SWIFT::ErrorField($this->Database->Record['name']);
                                    $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('livesupport_pagerefresh_invalid'), $this->Language->Get('d_livesupport_pagerefresh_invalid'));
                                    $_doUpdateField = false;
                                }
                            } else if ($this->Database->Record['name'] === 'livesupport_clientchatrefresh') {
                                if (empty($_POST[$this->Database->Record['name']])) {
                                    SWIFT::ErrorField($this->Database->Record['name']);
                                    $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));
                                    $_doUpdateField = false;
                                } else if ($this->isSaas && (!is_numeric($_POST[$this->Database->Record['name']]) || $_POST[$this->Database->Record['name']] < 10)) {
                                    SWIFT::ErrorField($this->Database->Record['name']);
                                    $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('livesupport_chatrefresh_invalid'), $this->Language->Get('d_livesupport_chatrefresh_invalid'));
                                    $_doUpdateField = false;
                                }
                            } else if (in_array($this->Database->Record['name'],[
                                'dt_dateformat',
                                'dt_timeformat',
                                'dt_datetimeformat',
                                'livechat_timestampformat',
                            ])) {
                                if (empty($_POST[$this->Database->Record['name']])) {
                                    SWIFT::ErrorField($this->Database->Record['name']);
                                    $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));
                                    $_doUpdateField = false;
                                } else {
                                    // test the format using locale-independant functions
                                    $_fmt = $_POST[$this->Database->Record['name']];
                                    $date = strftime($_fmt);
                                    $_fmt = strtr($_fmt, [
                                        '%P' => '%p',
                                    ]);
                                    if (false === strptime($date, $_fmt)) {
                                        SWIFT::ErrorField($this->Database->Record['name']);
                                        $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('titlefieldinvalid'), $this->Language->Get('msgfieldinvalid'));
                                        $_doUpdateField = false;
                                    }
                                }
                            }

                            $_settingValue = $_POST[$this->Database->Record['name']];

                            if ($_doUpdateField)
                            {
                                $this->UpdateField($this->Database->Record['name'], $_settingValue);
                            }

                            $_rebuildCache = true;
                        }

                        if ($this->Database->Record['settingtype'] == 'title' && _is_array($_filterSettingTitleList) && in_array($this->Database->Record['name'], $_filterSettingTitleList))
                        {
                            $_displaySetting = true;
                        } else if ($this->Database->Record['settingtype'] == 'title' && _is_array($_filterSettingTitleList) && !in_array($this->Database->Record['name'], $_filterSettingTitleList)) {
                            $_displaySetting = false;
                        }

                        if ($_displaySetting)
                        {
                            $this->RenderField($_SettingsTabObject, $this->Database->Record);
                        }
                    }
                }
            }
        }

        if ($_rebuildCache)
        {
            SWIFT_Settings::RebuildCache();
        }

        if (SWIFT::Get('isdemo') && SWIFT::Get('isdemo') == true)
        {
            $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));
        }

        if (isset($_POST['step']) && $_POST['step'] == '1' && $_SWIFT->Staff->GetPermission('admin_canupdatesettings') == '0')
        {
            $_SWIFT_UserInterfaceObject->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

        } else if (isset($_POST['step']) && $_POST['step'] == '1') {
            if (empty(SWIFT::GetErrorFieldContainer())) {
                $_SWIFT_UserInterfaceObject->DisplayInfo(sprintf($this->Language->Get('titleupdatedswiftsettings'), $this->Language->Get($_settingGroupName)), sprintf($this->Language->Get('msgupdatedswiftsettings'), $this->Language->Get($_settingGroupName)));
            } else {
                $errors = SWIFT::GetErrorFieldContainer();
                $failedSettings = '<ul><li>';
                $failedSettings .= join('</li><li>', array_unique(array_map(function($val){
                    return $this->Language->Get($val);
                }, $errors)));
                $failedSettings .= '</li></ul>';

                $_SWIFT_UserInterfaceObject->DisplayAlert($this->Language->Get('updatedsettingspartially'), $failedSettings);
            }
        }

        return true;
    }

    /**
     * Updates the Setting
     *
     * @author Varun Shoor
     * @param string $_settingKey The Setting Key
     * @param mixed $_settingValue (ARRAY/STRING) The Setting Value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function UpdateField($_settingKey, $_settingValue)
    {
        if (!$this->GetIsClassLoaded() || empty($_settingKey))
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (is_array($_settingValue))
        {
            $_serializedContainer = serialize($_settingValue);
            $this->Settings->UpdateKey('settings', $_settingKey, 'SERIALIZED:' . $_serializedContainer, true);

            $this->Settings->UpdateLocalCache('settings', $_settingKey, $_settingValue);
        } else {
            $this->Settings->UpdateKey('settings', $_settingKey, $_settingValue, true);
        }

        return true;
    }

    /**
     * Renders the Setting Field
     *
     * @author Varun Shoor
     * @param SWIFT_UserInterfaceTab $_SWIFT_UserInterfaceTabObject The SWIFT_UserInterfaceTab Object Pointer
     * @param array $_settingRecord The Setting Database Record Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Class is not Loaded
     */
    protected function RenderField(SWIFT_UserInterfaceTab $_SWIFT_UserInterfaceTabObject, $_settingRecord)
    {
        $_SWIFT = SWIFT::GetInstance(); // do not remove. eval code uses it

        if (!$this->GetIsClassLoaded() || !_is_array($_settingRecord))
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_settingRecord['name'] === 't_ccaptcha' ||
        $_settingRecord['name'] === 'user_enablecaptcha' ||
        $_settingRecord['name'] === 'livesupport_captcha' ||
        $_settingRecord['name'] === 'security_commentscaptcha') {
            if ($this->isSaas) { // hide field if is SaaS
                return false;
            }
        }

        $label = $this->Language->Get($_settingRecord['name']);
        $description = $this->Language->Get('d_' . $_settingRecord['name']);
        $domain = SWIFT::Get('licensedomains');

        if ($_settingRecord['name'] === 'general_producturl') {
            $_settingRecord['settingtype'] = 'custom';
            $_settingRecord['customvalue'] = $this->Settings->Get($_settingRecord['name']);

            if (!empty($domain)) {
                // cloud installation
                $description = $this->Language->Get('d_customurl');
            } else {
                // on premises installation
                $description = sprintf($this->Language->Get('d_customurlwarning'),
                    SWIFT_Help::RetrieveHelpLink('customurl'));
            }
        }

        if ($_settingRecord['settingtype'] == 'text' || $_settingRecord['settingtype'] == 'password' || $_settingRecord['settingtype'] == 'url')
        {
            $_fieldValue = $this->Settings->Get($_settingRecord['name']);

            if ($_settingRecord['settingtype'] == 'url')
            {
                $_fieldType = 'text';
            } else {
                $_fieldType = $_settingRecord['settingtype'];
            }

            $_SWIFT_UserInterfaceTabObject->Text($_settingRecord['name'], $label,
                $description, $_fieldValue, $_fieldType);

        } else if ($_settingRecord['settingtype'] == 'yesno') {
            if ($this->Settings->Get($_settingRecord['name']) == '1')
            {
                $_fieldValue = true;
            } else {
                $_fieldValue = false;
            }

            $_SWIFT_UserInterfaceTabObject->YesNo($_settingRecord['name'], $label,
                $description, $_fieldValue);

        } else if ($_settingRecord['settingtype'] == 'number') {
            $_fieldValue = $this->Settings->Get($_settingRecord['name']);

            $_SWIFT_UserInterfaceTabObject->Number($_settingRecord['name'], $label,
                $description, $_fieldValue);

        } else if ($_settingRecord['settingtype'] == 'title') {
            $_SWIFT_UserInterfaceTabObject->Title($label, 'icon_doublearrows.gif');

        } else if ($_settingRecord['settingtype'] == 'color') {
            $_fieldValue = $this->Settings->Get($_settingRecord['name']);

            $_SWIFT_UserInterfaceTabObject->Color($_settingRecord['name'], $label,
                $description, $_fieldValue);

        } else if ($_settingRecord['settingtype'] == 'custom') {
            ob_start();
            eval('?>'.$_settingRecord['customvalue']);
            $_contents = ob_get_contents();
            ob_end_clean();

            $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($label, $description, $_contents);

        } else if ($_settingRecord['settingtype'] == 'hidden') {
            $_fieldValue = $this->Settings->Get($_settingRecord['name']);

            $_SWIFT_UserInterfaceTabObject->Hidden($_settingRecord['name'], $_fieldValue);
        }

        return true;
    }

    /**
     * Imports the settings from a given file
     *
     * @author Varun Shoor
     * @param string $_fileName The Settings XML File
     * @param string $_companyName The Default Company Name
     * @param string $_productURL The Default Product URL
     * @param string $_returnEmail The Default Return Email
     * @param bool $_isUpgrade Whether the instance is an upgrade, if true, it wont insert the default values and will only insert new settings.
     * @return array "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid App Data is Provided
     */
    public function Import($_fileName, $_companyName = '', $_productURL = '', $_returnEmail = '', $_isUpgrade = true)
    {
        $_appList = SWIFT_App::ListApps();
        if (!_is_array($_appList))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Make sure we are starting cleanly

        // Parse the settings XML File.
        if (!file_exists($_fileName))
        {
            throw new SWIFT_Exception('Settings file does not exist! ' . $_fileName);
        }

        $_statusListContainer = array();

        $_fileData = file_get_contents($_fileName);
        $_xmlSettingsContainer = $this->XML->XMLToTree($_fileData);

        $_finalSettingsContainer = &$_xmlSettingsContainer["swiftsettings"][0]["children"];
        // ======= !! DELETE EXISTING DATA FIRST !! =======
        $_groupNameDeleteList = $_settingNameDeleteList = array();
        for ($ii=0; $ii<count($_finalSettingsContainer["group"]); $ii++)
        {
            $_settingGroupContainer = &$_finalSettingsContainer["group"][$ii]["attrs"];

            if (in_array($_settingGroupContainer["app"], $_appList))
            {
                $_groupNameDeleteList[] = $_settingGroupContainer["name"];

                for ($kk=0; $kk<count($_finalSettingsContainer["group"][$ii]["children"]["setting"]); $kk++)
                {
                    $_settingContainer = $_finalSettingsContainer["group"][$ii]["children"]["setting"][$kk]["attrs"];
                    $_settingNameDeleteList[] = $_settingContainer["name"];
                }
            }
        }

        if (count($_groupNameDeleteList))
        {
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "settingsgroups WHERE name IN (" . BuildIN($_groupNameDeleteList) . ")");
        }
        if (count($_settingNameDeleteList))
        {
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "settingsfields WHERE name IN (" . BuildIN($_settingNameDeleteList) . ")");
        }

        // ======= PROCESS THE SETTINGS =======
        // Ok we have the settings as an array now, iterate through it, insert groups & then templates
        $_groupDisplayOrder = 0;

        // If its upgrade we need to fetch the max display order
        if ($_isUpgrade)
        {
            $_displayOrderContainer = $this->Database->QueryFetch("SELECT MAX(displayorder) AS displayorder FROM " . TABLE_PREFIX . "settingsgroups");
            if (isset($_displayOrderContainer['displayorder']) && is_numeric($_displayOrderContainer['displayorder']))
            {
                $_groupDisplayOrder = (int) ($_displayOrderContainer['displayorder']) + 1;
            }
        }

        $_settingsList = $_inSettingList = array();
        for ($ii=0; $ii<count($_finalSettingsContainer["group"]); $ii++)
        {
            // We iterate through groups first

            // This is our attribute holder, contains the group name etc
            $_groupContainer = &$_finalSettingsContainer["group"][$ii]["attrs"];

            if (in_array($_groupContainer["app"], $_appList))
            {
                // Only proceed if the setting group is in the allowed app list
                // Insert the setting group first
                $_queryResult = $this->Database->AutoExecute(TABLE_PREFIX.'settingsgroups', array('name' => $_groupContainer["name"], 'app' => $_groupContainer["app"],
                    'displayorder' => $_groupDisplayOrder, 'ishidden' => IIF($_groupContainer["ishidden"], $_groupContainer["ishidden"], "0")), 'INSERT');
                $_settingGroupID = $this->Database->Insert_ID();

                $_groupDisplayOrder++;

                $_statusListContainer[] = array('statusText' => sprintf($this->Language->Get('scinsertsettingsgroup'), $_groupContainer['name']), 'result' => $_queryResult,
                    'reasonFailure' => $this->Database->FetchLastError());

                // Dont add other settings if this fails
                if (empty($_settingGroupID) || !$_queryResult)
                {
                    continue;
                }

                /*
                 * BEGIN SETTING PROCESSING FOR UPGRADE CHECKS
                 */
                // Now we iterate through individual settings for this group
                for ($kk=0; $kk<count($_finalSettingsContainer["group"][$ii]["children"]["setting"]); $kk++)
                {
                    $_settingContainer = $_finalSettingsContainer["group"][$ii]["children"]["setting"][$kk]["attrs"];
                    $_inSettingList[] = $_settingContainer["name"];
                    $_settingsList[$_settingContainer["name"]] = 1;
                }

                $this->Database->Query("SELECT settingid, vkey FROM ". TABLE_PREFIX ."settings WHERE vkey IN (". BuildIN($_inSettingList) .")");
                while ($this->Database->NextRecord())
                {
                    $_settingsList[$this->Database->Record["vkey"]] = 0;
                }
                /**
                 * END SETTING PROCESSING
                 */

                $_settingDisplayOrder = 0;
                // Now we iterate through individual settings for this group
                for ($kk=0; $kk<count($_finalSettingsContainer["group"][$ii]["children"]["setting"]); $kk++)
                {
                    $_settingContainer = $_finalSettingsContainer["group"][$ii]["children"]["setting"][$kk]["attrs"];

                    $_defaultValue = $_customValue = '';
                    if (isset($_finalSettingsContainer["group"][$ii]["children"]["setting"][$kk]["children"]))
                    {
                        if (isset($_finalSettingsContainer["group"][$ii]["children"]["setting"][$kk]["children"]["defaultvalue"]) && isset($_finalSettingsContainer["group"][$ii]["children"]["setting"][$kk]["children"]["defaultvalue"][0]["values"]))
                        {
                            $_defaultValue = $_finalSettingsContainer["group"][$ii]["children"]["setting"][$kk]["children"]["defaultvalue"][0]["values"][0];
                        }

                        if (isset($_finalSettingsContainer["group"][$ii]["children"]["setting"][$kk]["children"]["customcode"]) && isset($_finalSettingsContainer["group"][$ii]["children"]["setting"][$kk]["children"]["customcode"][0]["values"]))
                        {
                            $_customValue = $_finalSettingsContainer["group"][$ii]["children"]["setting"][$kk]["children"]["customcode"][0]["values"][0];
                        }
                    }

                    if (!empty($_settingContainer["app"]))
                    {
                        $_settingApp = $_settingContainer["app"];
                        if (!in_array($_settingApp, $_appList))
                        {
                            // Move over, this setting has a custom app set and that isnt in our existing app list.
                            continue;
                        }
                    } else {
                        $_settingApp = $_groupContainer["app"];
                    }

                    $_isCustom = '0';
                    if (isset($_settingContainer['iscustom']))
                    {
                        $_isCustom = $_settingContainer['iscustom'];
                    }

                    $_settingType = 'text';
                    if (isset($_settingContainer['type']))
                    {
                        $_settingType = $_settingContainer['type'];
                    }

                    $this->Database->AutoExecute(TABLE_PREFIX . 'settingsfields', array('sgroupid' => $_settingGroupID, 'name' => $_settingContainer["name"], 'customvalue' => ReturnNone($_customValue), 'iscustom' => $_isCustom, 'settingtype' => $_settingType, 'app' => $_settingApp, 'displayorder' => $_settingDisplayOrder), 'INSERT');
                    $_settingDisplayOrder++;

                    if (($_defaultValue != '' && !$_isUpgrade) || ($_isUpgrade == true && $_settingsList[$_settingContainer["name"]] == 1 && $_defaultValue != ""))
                    {
                        $this->Database->AutoExecute(TABLE_PREFIX . 'settings', array('section' => 'settings', 'vkey' => $_settingContainer['name'], 'data' => $_defaultValue), 'INSERT');
                    }
                }

                $_statusListContainer[] = array('statusText' => sprintf($this->Language->Get('scinsertsettings'), $_groupContainer['name']), 'result' => true, 'reasonFailure' => '');
            }
        }

        if ($_isUpgrade) {
            SWIFT_Settings::RebuildCache();
        }

        return $_statusListContainer;
    }

    /**
     * Import all files
     *
     * @author Varun Shoor
     * @param bool $_isUpgrade
     * @param string $_companyName
     * @param string $_productURL
     * @param string $_returnEmail
     * @return array Result Array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ImportAll($_isUpgrade, $_companyName, $_productURL, $_returnEmail)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_statusListContainer = array();

        $_installedAppList = SWIFT_App::GetInstalledApps();

        foreach ($_installedAppList as $_appName) {
            $_SWIFT_AppObject = false;
            try {
                $_SWIFT_AppObject = SWIFT_App::Get($_appName);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                continue;
            }

            if (!$_SWIFT_AppObject instanceof SWIFT_App || !$_SWIFT_AppObject->GetIsClassLoaded()) {
                continue;
            }

            // See if we have a settings file in there..
            $_settingsFile = $_SWIFT_AppObject->GetDirectory() . '/' . SWIFT_CONFIG_DIRECTORY . '/settings.xml';
            if (!file_exists($_settingsFile)) {
                continue;
            }

            $_statusList = $this->Import($_settingsFile, $_companyName, $_productURL, $_returnEmail, $_isUpgrade);
            $_statusListContainer = array_merge($_statusList, $_statusListContainer);
        }

        if (!$_isUpgrade && !empty($_companyName) && !empty($_productURL) && !empty($_returnEmail))
        {
            // Add the default data that was specified by user earlier ;)
            $this->Database->AutoExecute(TABLE_PREFIX . 'settings', array('section' => 'settings', 'vkey' => 'general_companyname', 'data' => $_companyName), 'INSERT');
            $this->Database->AutoExecute(TABLE_PREFIX . 'settings', array('section' => 'settings', 'vkey' => 'general_producturl', 'data' => $_productURL), 'INSERT');
            $this->Database->AutoExecute(TABLE_PREFIX . 'settings', array('section' => 'settings', 'vkey' => 'general_returnemail', 'data' => $_returnEmail), 'INSERT');
        } else if ($_isUpgrade && !empty($_productURL)) {

            $this->Database->AutoExecute(TABLE_PREFIX . 'settings', array('data' => $_productURL), 'UPDATE', 'vkey = \'general_producturl\'');

            SWIFT_Settings::RebuildCache();
        }

        return $_statusListContainer;
    }

    /**
     * Clear the settings on the provided list of apps
     *
     * @author Varun Shoor
     * @param array $_appNameList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DeleteOnApp($_appNameList)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        } else if (!_is_array($_appNameList)) {
            return false;
        }

        SWIFT_SettingsGroup::DeleteOnApp($_appNameList);

        return true;
    }
}
?>
