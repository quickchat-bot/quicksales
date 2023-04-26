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

namespace LiveChat\Admin;

use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use SWIFT_FileManager;

/**
 * The Tag Generator Controller
 *
 * @author Varun Shoor
 *
 * @property View_TagGenerator $View
 */
class Controller_TagGenerator extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 27;

    const TAG_HTMLBUTTON = 'htmlbutton';
    const TAG_SITEBADGE = 'sitebadge';
    const TAG_TEXTLINK = 'textlink';
    const TAG_MONITORING = 'monitoring';

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('admin_taggenerator');
    }

    /**
     * Render the Tag Generator Tab
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Index()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!isset($_POST['tagtype']) || empty($_POST['tagtype']) || !self::IsValidTagType($_POST['tagtype'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header($this->Language->Get('taggenerator') . ' > ' . $this->Language->Get('livechat'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canmanagetaggenerator') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render($_POST['tagtype']);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Retrieve the Tag Label
     *
     * @author Varun Shoor
     * @param string $_tagType The Tag Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _GetTagLabel($_tagType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        switch ($_tagType) {
            case self::TAG_HTMLBUTTON:
                return $this->Language->Get('tag_htmlbutton');
                break;

            case self::TAG_SITEBADGE:
                return $this->Language->Get('tag_sitebadge');
                break;

            case self::TAG_TEXTLINK:
                return $this->Language->Get('tag_textlink');
                break;

            case self::TAG_MONITORING:
                return $this->Language->Get('tag_monitoring');
                break;

            default:
                break;
        }

        return false;
    }

    /**
     * Check to see if its a valid tag type
     *
     * @author Varun Shoor
     * @param mixed $_tagType The Tag Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidTagType($_tagType)
    {
        if ($_tagType == self::TAG_HTMLBUTTON || $_tagType == self::TAG_SITEBADGE || $_tagType == self::TAG_TEXTLINK || $_tagType == self::TAG_MONITORING) {
            return true;
        }

        return false;
    }

    /**
     * Generate the tag HTML
     *
     * @author Varun Shoor
     * @param mixed $_tagType The Tag Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Generate($_tagType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_tagType) || !self::IsValidTagType($_tagType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Field Sanitization
        if (isset($_POST['skipuserdetails']) && !empty($_POST['skipuserdetails']) && (!isset($_POST['filterbydepartment']) || (isset($_POST['filterbydepartment']) && !_is_array($_POST['filterbydepartment'])))) {
            SWIFT::ErrorField('filterbydepartment');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $_POST['tagtype'] = $_tagType;

            $this->Load->Index();

            return true;
        } else if ($_tagType == self::TAG_TEXTLINK && (!isset($_POST['textcontents']) || empty($_POST['textcontents']))) {
            SWIFT::ErrorField('textcontents');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $_POST['tagtype'] = $_tagType;

            $this->Load->Index();

            return true;
        }

        // Any uploaded file? Check extensions...
        foreach (array('customonline', 'customoffline', 'customaway', 'custombackshortly') as $_key => $_val) {
            $_uploadedFieldName = 'file_' . $_val;

            if (isset($_FILES[$_uploadedFieldName]) && isset($_FILES[$_uploadedFieldName]['tmp_name']) && is_uploaded_file($_FILES[$_uploadedFieldName]['tmp_name'])) {
                $_pathInfoContainer = pathinfo($_FILES[$_uploadedFieldName]['name']);
                if (!isset($_pathInfoContainer['extension']) || empty($_pathInfoContainer['extension']) || ($_pathInfoContainer['extension'] != 'gif' && $_pathInfoContainer['extension'] != 'jpeg' && $_pathInfoContainer['extension'] != 'jpg' && $_pathInfoContainer['extension'] != 'png')) {
                    SWIFT::ErrorField($_val);

                    $this->UserInterface->Error($this->Language->Get('titleinvalidfileext'), $this->Language->Get('msginvalidfileext'));

                    $_POST['tagtype'] = $_tagType;

                    $this->Load->Index();

                    return false;
                }
            }
        }

        $this->Cache->Queue('skillscache');
        $this->Cache->Queue('templategroupcache');
        $this->Cache->LoadQueue();
        $_chatSkillCache = $this->Cache->Get('skillscache');
        $_templateGroupCache = $this->Cache->Get('templategroupcache');
        $_staffCache = $this->Cache->Get('staffcache');
        $_departmentCache = $this->Cache->Get('departmentcache');

        $_tagFunction = '';

        switch ($_tagType) {
            case self::TAG_HTMLBUTTON:
                $_tagFunction = 'HTMLButton';
                break;

            case self::TAG_SITEBADGE:
                $_tagFunction = 'SiteBadge';
                break;

            case self::TAG_TEXTLINK:
                $_tagFunction = 'TextLink';
                break;

            case self::TAG_MONITORING:
                $_tagFunction = 'Monitoring';
                break;

            default:
                break;
        }

        if (empty($_tagFunction)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        /*
         * ###############################################
         * BEGIN TAG CODE GENERATION
         * ###############################################
         */

        $_variableText = '';
        $_variableContainer = array();

        $_uniqueID = substr(BuildHash(), 0, 10);

        $_variableContainer[] = 'prompttype=' . $_POST['chatprompttype'];
        $_variableContainer[] = 'uniqueid=' . $_uniqueID;

        // Baseline data
        $_variableContainer[] = 'version=' . SWIFT_VERSION;
        $_variableContainer[] = 'product=' . SWIFT_PRODUCT;

        // Process the Secure Links Setting
        $_tagSwiftPath = mb_strtolower(SWIFT::Get('swiftpath'));
        if ($_POST['usesecurelinks'] == '1' && substr($_tagSwiftPath, 0, strlen('http://')) == 'http://') {
            $_tagSwiftPath = 'https://' . substr($_tagSwiftPath, strlen('http://'));
        }

        // Process the Filter By Departments
        $_filterDepartmentID = '';
        if (isset($_POST['filterbydepartment']) && _is_array($_POST['filterbydepartment'])) {
            $_filterDepartmentIDList = array();
            foreach ($_POST['filterbydepartment'] as $_key => $_val) {
                if (isset($_departmentCache[$_val])) {
                    $_filterDepartmentIDList[] = (int)($_val);

                    if (!empty($_departmentCache[$_val]['parentdepartmentid'])) {
                        $_filterDepartmentIDList[] = (int)($_departmentCache[$_val]['parentdepartmentid']);
                    }
                }
            }

            $_variableContainer[] = 'filterdepartmentid=' . implode(',', $_filterDepartmentIDList);
            $_filterDepartmentID = implode(',', $_filterDepartmentIDList);
        }

        // Process the Filter by Chat Skill
        if (isset($_POST['routetochatskill']) && _is_array($_POST['routetochatskill'])) {
            $_routeChatSkillIDList = array();
            foreach ($_POST['routetochatskill'] as $_key => $_val) {
                if (isset($_chatSkillCache[$_val])) {
                    $_routeChatSkillIDList[] = (int)($_val);
                }
            }

            $_variableContainer[] = 'routechatskillid=' . implode(',', $_routeChatSkillIDList);
        }

        // Process Template Group
        $_templateGroupURLPrefix = '';
        if (isset($_POST['templategroupid']) && !empty($_POST['templategroupid']) && isset($_templateGroupCache[$_POST['templategroupid']])) {
            $_templateGroupURLPrefix = '/' . $_templateGroupCache[$_POST['templategroupid']]['title'];
        }

        // Process Skip User Details
        if (isset($_POST['skipuserdetails']) && !empty($_POST['skipuserdetails'])) {
            $_variableContainer[] = 'skipuserdetails=' . (int)($_POST['skipuserdetails']);
        }

        // Process Variables & Alerts
        if (isset($_POST['tagextend']) && _is_array($_POST['tagextend'])) {
            $_variableIndex = $_alertIndex = 0;
            foreach ($_POST['tagextend'] as $_key => $_val) {
                if ($_val[0] == 'variable') {
                    $_variableContainer[] = 'variable[' . $_variableIndex . '][0]=' . urlencode($_val[1]);
                    $_variableContainer[] = 'variable[' . $_variableIndex . '][1]=' . urlencode($_val[2]);

                    $_variableIndex++;
                } else if ($_val[0] == 'alert') {
                    $_variableContainer[] = 'alert[' . $_alertIndex . '][0]=' . urlencode($_val[1]);
                    $_variableContainer[] = 'alert[' . $_alertIndex . '][1]=' . urlencode($_val[2]);

                    $_alertIndex++;
                }
            }
        }

        // Process Site Badge Properties
        foreach (array('sitebadgecolor', 'badgelanguage', 'badgetext') as $_key => $_val) {
            if (isset($_POST[$_val]) && !empty($_POST[$_val])) {
                $_variableContainer[] = $_val . '=' . $_POST[$_val];
            }
        }

        foreach (array('onlinecolor', 'offlinecolor', 'awaycolor', 'backshortlycolor') as $_key => $_val) {
            if (isset($_POST[$_val]) && !empty($_POST[$_val])) {
                $_variableContainer[] = $_val . '=' . $_POST[$_val];

                // Hex color? Calculate the hover and border colors..
                if (substr($_POST[$_val], 0, 1) == '#') {
                    $_variableContainer[] = $_val . 'hover=' . ColorBrightness($_POST[$_val], 0.7);
                    $_variableContainer[] = $_val . 'border=' . ColorBrightness($_POST[$_val], -0.7);
                } else {
                    $_variableContainer[] = $_val . 'hover=' . $_POST[$_val];
                    $_variableContainer[] = $_val . 'border=' . $_POST[$_val];
                }
            }
        }

        // Process Custom Icons
        $_variableContainer[] = 'customonline=' . urlencode($this->_GetIcon('customonline'));
        $_variableContainer[] = 'customoffline=' . urlencode($this->_GetIcon('customoffline'));
        $_variableContainer[] = 'customaway=' . urlencode($this->_GetIcon('customaway'));
        $_variableContainer[] = 'custombackshortly=' . urlencode($this->_GetIcon('custombackshortly'));

        $_combinedVariableText = implode('&', $_variableContainer);
        $_variableText = $_combinedVariableText . SWIFT_CRLF . sha1($_combinedVariableText . SWIFT::Get('InstallationHash'));

        $_tagCode = '<!-- ' . mb_strtoupper($this->Language->Get('tagcodeheader')) . ' -->';

        if ($_tagType == self::TAG_HTMLBUTTON) {
            $_divStyleExtended = '';
            $_tagCode .= '<div' . $_divStyleExtended . '><div id="proactivechatcontainer' . $_uniqueID . '"></div><table border="0" cellspacing="2" cellpadding="2"><tr><td align="center" id="swifttagcontainer' . $_uniqueID . '"><div style="display: inline;" id="swifttagdatacontainer' . $_uniqueID . '"></div>';
        } else if ($_tagType == self::TAG_SITEBADGE) {
            $_tagCode .= '<div id="swifttagcontainer' . $_uniqueID . '"><div id="proactivechatcontainer' . $_uniqueID . '"></div><div style="display: inline;" id="swifttagdatacontainer' . $_uniqueID . '"></div>';
        } else if ($_tagType == self::TAG_MONITORING) {
            $_tagCode .= '<div id="proactivechatcontainer' . $_uniqueID . '"></div><div id="swifttagcontainer' . $_uniqueID . '" style="display: none;"><div id="swifttagdatacontainer' . $_uniqueID . '"></div></div>';
        }

        // Build the Live Chat Links
        if (isset($_POST['skipuserdetails']) && $_POST['skipuserdetails'] == '1') {
            $_liveChatLink = SWIFT::Get('swiftpath') . 'visitor/index.php?' . $_templateGroupURLPrefix . '/LiveChat/Chat/StartInline/_sessionID=/_promptType=' . $_POST['chatprompttype'] . '/_proactive=0/_filterDepartmentID=' . urlencode($_filterDepartmentID) . '/_randomNumber=' . BuildHash() . '/_fullName=/_email=/';
        } else {
            $_liveChatLink = SWIFT::Get('swiftpath') . 'visitor/index.php?' . $_templateGroupURLPrefix . '/LiveChat/Chat/Request/_sessionID=/_promptType=' . $_POST['chatprompttype'] . '/_proactive=0/_filterDepartmentID=' . urlencode($_filterDepartmentID) . '/_randomNumber=' . BuildHash() . '/_fullName=/_email=/';
        }

        // Replace the Script with an Image
        $_showJS = false;
        if (isset($_POST['nojavascript']) && $_POST['nojavascript'] == '1' && $_tagType == self::TAG_HTMLBUTTON) {
            $_tagCode .= '<a target="_blank" href="' . htmlspecialchars($_liveChatLink) . '" class="livechatlink"><img src="' . SWIFT::Get('swiftpath') . 'visitor/index.php?' . $_templateGroupURLPrefix . '/LiveChat/HTML/NoJSImage/' . base64_encode($_variableText) . '" align="absmiddle" border="0" /></a>';

        } else if ($_tagType == self::TAG_TEXTLINK) {
            $_tagCode .= '<a href="javascript: void(0);" onclick="javascript: window.open(\'' . htmlspecialchars($_liveChatLink) . '\', \'livechatwin\', \'toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=0,resizable=1,width=' . (int)($this->Settings->Get('livesupport_chatwidth')) . ',height=' . (int)($this->Settings->Get('livesupport_chatheight')) . '\');" class="livechatlink">' . htmlspecialchars($_POST['textcontents']) . '</a>';
        } else {
            $_showJS = true;
        }

        /**
         * Bug Fix : Nidhi Gupta <nidhi.gupta@kayako.com>
         *
         * SWIFT-4900 : UTM parameters for links to kayako.com
         */
        $_requestURL = $_SERVER['SERVER_NAME'];

        if ($_tagType == self::TAG_HTMLBUTTON) {
            $_tagCode .= '</td> </tr><tr><td align="center"><div style="MARGIN-TOP: 2px; WIDTH: 100%; TEXT-ALIGN: center;"><span style="FONT-SIZE: 9px; FONT-FAMILY: Tahoma, Arial, Helvetica, sans-serif;"><a href="https://www.opencart.com.vn/helpdesk?utm_source=' . $_requestURL . '&utm_medium=chat&utm_content=powered-by-kayako-help-desk-software&utm_campaign=product_links" style="TEXT-DECORATION: none; COLOR: #000000" target="_blank" rel="noopener noreferrer">' . $this->Language->Get('tagpoweredlivechat') . '</a><span style="COLOR: #000000"> ' . $this->Language->Get('tagpoweredby') . ' </span>QuickSupport</span></div></td></tr>';

            $_tagCode .= '</table></div>';
        } else if ($_tagType == self::TAG_SITEBADGE) {
            $_tagCode .= '</div>';
        }

        if ($_showJS) {
            $_tagCode .= ' <script type="text/javascript">var swiftscriptelem' . $_uniqueID . '=document.createElement("script");swiftscriptelem' . $_uniqueID . '.type="text/javascript";var swiftrandom = Math.floor(Math.random()*1001); var swiftuniqueid = "' . $_uniqueID . '"; var swifttagurl' . $_uniqueID . '="' . $_tagSwiftPath . 'visitor/index.php?' . $_templateGroupURLPrefix . '/LiveChat/HTML/' . $_tagFunction . '/' . IIF($this->Settings->Get('ls_forcerandomnumber') == '1', '" + swiftrandom + ":') . base64_encode($_variableText) . '";setTimeout("swiftscriptelem' . $_uniqueID . '.src=swifttagurl' . $_uniqueID . ';document.getElementById(\'swifttagcontainer' . $_uniqueID . '\').appendChild(swiftscriptelem' . $_uniqueID . ');",1);</script>';
        }

        $_tagCode .= '<!-- ' . mb_strtoupper($this->Language->Get('tagcodefooter')) . ' -->';

        /*
         * ###############################################
         * END TAG CODE GENERATION
         * ###############################################
         */

        SWIFT::Info($this->Language->Get('title_tagcode'), $this->Language->Get('desc_tagcode'));
        $this->UserInterface->Header($this->Language->Get('taggenerator') . ' > ' . $this->Language->Get('livechat'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_canmanagetaggenerator') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGenerateTag($_tagType, $_tagCode);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Retrieve the icon, if a new one is uploaded.. pass it through file manager and return the relevant new URL to it
     *
     * @author Varun Shoor
     * @param string $_fieldName The Field Name
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetIcon($_fieldName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // We always give priority to the uploaded file..
        $_uploadedFieldName = 'file_' . $_fieldName;
        if (isset($_FILES[$_uploadedFieldName]) && isset($_FILES[$_uploadedFieldName]['tmp_name']) && is_uploaded_file($_FILES[$_uploadedFieldName]['tmp_name'])) {
            $_fileID = SWIFT_FileManager::Create($_FILES[$_uploadedFieldName]['tmp_name'], $_FILES[$_uploadedFieldName]['name']);
            if (!empty($_fileID)) {
                $_SWIFT_FileManagerObject = new SWIFT_FileManager($_fileID);
                if ($_SWIFT_FileManagerObject->GetIsClassLoaded()) {
                    return $_SWIFT_FileManagerObject->GetURL();
                }
            }
        }

        if (!isset($_POST['url_' . $_fieldName])) {
            return '';
        }

        return $_POST['url_' . $_fieldName];
    }
}
