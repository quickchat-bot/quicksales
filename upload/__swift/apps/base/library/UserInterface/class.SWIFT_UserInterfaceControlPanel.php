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

namespace Base\Library\UserInterface;

use Base\Library\UserInterface\SWIFT_UserInterface;
use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_LanguageEngine;
use SWIFT_Loader;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use Tickets\Models\Filter\SWIFT_TicketFilter;

/**
 * The Control Panel User Interface Management Class
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceToolbar $UserInterfaceToolbar
 * @author Varun Shoor
 */
class SWIFT_UserInterfaceControlPanel extends SWIFT_UserInterface
{
    public $_isDialog = false;
    private $_outputContainer = '';
    private $_targetDiv = '';
    private $_targetFunction = '';
    private $_formList = array();
    private $_onlineStaffContainer = array();
    private $_hiddenFieldContainer = array();
    private $_tabContainer = array();
    private $_mode = false;
    public $_formName = '';
    public $_defaultTabID = false;
    private $_appendHTML = '';
    protected $_dialogBottomLeftPanel = '';

    protected $_notificationContainer = array();

    protected $_overrideButtonText = '';
    private $_documentTitle = '';

    private $_navigationContainer = array();

    private $_saveButton = true;

    public $Toolbar = false;

    private $_tabCount = 0;
    private $_tabIndex = 0;

    private $_noSubmit = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
        header('Content-type: text/html; charset=' . $this->Language->Get('charset'));
    }

    /**
     * Processes the Dialogs
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function ProcessDialogs()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (_is_array(SWIFT::GetInfoContainer())) {
            foreach (SWIFT::GetInfoContainer() as $_key => $_val) {
                if (isset($_val['title']) && isset($_val['message'])) {
                    $this->DisplayInfo($_val['title'], $_val['message']);
                }
            }
        }

        if (_is_array(SWIFT::GetErrorContainer())) {
            foreach (SWIFT::GetErrorContainer() as $_key => $_val) {
                if (isset($_val['title']) && isset($_val['message'])) {
                    $this->DisplayError($_val['title'], $_val['message']);
                }
            }
        }

        if (_is_array(SWIFT::GetAlertContainer())) {
            foreach (SWIFT::GetAlertContainer() as $_key => $_val) {
                if (isset($_val['title']) && isset($_val['message'])) {
                    $this->DisplayAlert($_val['title'], $_val['message']);
                }
            }
        }

        SWIFT::ResetAllContainers();

        return true;
    }

    /**
     * Display Error
     *
     * @author Varun Shoor
     * @param string $_title The Title
     * @param string $_message The Message
     * @param string $_divID The Unique ID for this Div
     * @param bool $_showBorder Whether to Show Border for this Div
     * @return bool "true" on Success, "false" otherwise
     */
    public function DisplayError($_title, $_message, $_divID = '', $_showBorder = true)
    {
        if (!$this->GetIsClassLoaded() || empty($_title) || empty($_message)) {
            return false;
        }

        echo $this->GetError($_title, $_message, $_divID, $_showBorder);

        return true;
    }

    /**
     * Display Alert
     *
     * @author Varun Shoor
     * @param string $_title The Title
     * @param string $_message The Message
     * @param string $_divID The Unique ID for this Div
     * @param bool $_showBorder Whether to Show Border for this Div
     * @return bool "true" on Success, "false" otherwise
     */
    public function DisplayAlert($_title, $_message, $_divID = '', $_showBorder = true)
    {
        if (!$this->GetIsClassLoaded() || empty($_title) || empty($_message)) {
            return false;
        }

        echo $this->GetAlert($_title, $_message, $_divID, $_showBorder);

        return true;
    }

    /**
     * Display Confirmation
     *
     * @author Varun Shoor
     * @param string $_title The Title
     * @param string $_message The Message
     * @param string $_divID The Unique ID for this Div
     * @return bool "true" on Success, "false" otherwise
     */
    public function DisplayInfo($_title, $_message, $_divID = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_title) || empty($_message)) {
            return false;
        }

        echo $this->GetInfo($_title, $_message, $_divID);

        return true;
    }

    /**
     * Set a custom Appended HTML for this tab contents
     *
     * @author Varun Shoor
     * @param string $_appendHTML The Appended HTML
     * @return bool "true" on Success, "false" otherwise
     */
    public function AppendHTML($_appendHTML)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->_appendHTML = $_appendHTML;

        return true;
    }

    /**
     * Add a Navigation Box
     *
     * @author Varun Shoor
     * @param string $_navigationTitle The Navigation Title
     * @param string $_contentsHTML The Box Contents
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AddNavigationBox($_navigationTitle, $_contentsHTML)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_renderHTML = '<div class="renavsection" id="itemoptionsnav"><div class="navsub"><div class="navtitle">' . $_navigationTitle . '</div>';
        $_renderHTML .= $_contentsHTML;
        $_renderHTML .= '</div></div>';

        $this->_navigationContainer[] = $_renderHTML;

        return true;
    }

    /**
     * Render the Header
     *
     * @author Varun Shoor
     * @param string $_documentTitle The Document Title
     * @param int $_selectedMenu The Selected Top Menu
     * @param int $_selectedNavigation The Selected Navigation Item
     * @param string $_customNavigationHTML The Custom Navigation HTML to Dispatch
     * @return bool "true" on Success, "false" otherwise
     */
    public function Header($_documentTitle = '', $_selectedMenu = 1, $_selectedNavigation = 1, $_customNavigationHTML = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        $this->_documentTitle = $_documentTitle;

        $this->Template->Assign('_area', $this->Interface->GetName());
        $this->Template->Assign('_defaultTitle', sprintf($this->Language->Get('poweredby'), SWIFT_PRODUCT));
        $this->Template->Assign('_defaultFooter', sprintf($this->Language->Get('poweredby'), SWIFT_PRODUCT));

        $this->Template->Assign('_userName', addslashes(htmlspecialchars($_SWIFT->Staff->GetProperty('username'))));


        $_resetColorIndexAt = 4;
        $_colorIndex = 1;

        $_globalAdminBar = SWIFT::Get('globaladminbar');
        $_globalAdminBarItems = SWIFT::Get('globaladminbaritems');
        $_globalMenu = SWIFT::Get('globalmenu');
        $_globalMenuLinks = SWIFT::Get('globalmenulinks');

        $_finalMainMenu = SWIFT::Get($this->Interface->GetName() . 'menu');
        if (!$_finalMainMenu) {
            $_finalMainMenu = array();
        }

        if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_STAFF && $_globalMenu['staff']) {
            $_finalMainMenu = $_finalMainMenu + $_globalMenu['staff'];
        } elseif ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_ADMIN && $_globalMenu['admin']) {

            $_finalMainMenu = $_finalMainMenu + $_globalMenu['admin'];
        } elseif ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_INTRANET && $_globalMenu['intranet']) {

            $_finalMainMenu = $_finalMainMenu + $_globalMenu['intranet'];
        }


        $_finalMenuLinks = SWIFT::Get($this->Interface->GetName() . 'links');
        if (!$_finalMenuLinks) {
            $_finalMenuLinks = array();
        }

        if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_STAFF && $_globalMenuLinks['staff']) {
            $_finalMenuLinks = $_finalMenuLinks + $_globalMenuLinks['staff'];
        } elseif ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_ADMIN && $_globalMenuLinks['admin']) {
            $_finalMenuLinks = $_finalMenuLinks + $_globalMenuLinks['admin'];
        } elseif ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_INTRANET && $_globalMenuLinks['intranet']) {
            $_finalMenuLinks = $_finalMenuLinks + $_globalMenuLinks['intranet'];
        }

        foreach ($_finalMainMenu as $_key => $_val) {
            if (!SWIFT_App::IsInstalled($_val[2]) || (isset($_val[3]) && $_SWIFT->Staff->GetPermission($_val[3]) == '0')) {
                unset($_finalMainMenu[$_key]);

                if (isset($_finalMenuLinks[$_key])) {
                    unset($_finalMenuLinks[$_key]);
                }

                continue;
            }

            $_finalMainMenu[$_key][4] = $_colorIndex;

            $_colorIndex++;

            if ($_colorIndex > $_resetColorIndexAt) {
                $_colorIndex = 1;
            }
        }

        $this->Template->Assign('_menuCount', count($_finalMainMenu));
        $this->Template->Assign('_menuColumnSpan', count($_finalMainMenu) + 2);
        $this->Template->Assign('_menu', $_finalMainMenu);

        $_menuIDDispatch = $_selectedMenu;
        if (empty($_menuIDDispatch)) {
            $_menuIDDispatch = $_SWIFT->Controller->GetMenuID();
        }

        $_finalSubKey = '0';
        $_comparisonURL = strtolower('/' . $_SWIFT->Router->GetApp()->GetName() . '/' . $_SWIFT->Router->GetController() . '/' . $_SWIFT->Router->GetAction());

        foreach ($_finalMenuLinks as $_key => $_val) {
            $_menuIndex = 1;
            $_menuCount = count($_val);

            foreach ($_val as $_subKey => $_subVal) {
                if ($_menuIndex == 1) {
                    $_finalMenuLinks[$_key][$_subKey][12] = false;
                    $_finalMenuLinks[$_key][$_subKey][13] = true;
                } elseif ($_menuIndex == $_menuCount) {
                    $_finalMenuLinks[$_key][$_subKey][12] = true;
                    $_finalMenuLinks[$_key][$_subKey][13] = false;
                } else {
                    $_finalMenuLinks[$_key][$_subKey][12] = true;
                    $_finalMenuLinks[$_key][$_subKey][13] = true;
                }

                // Javascript Replacement
                if (substr($_subVal[1], 0, 1) == ':') {
                    $_finalMenuLinks[$_key][$_subKey][14] = substr($_subVal[1], 1);
                    $_finalMenuLinks[$_key][$_subKey][25] = '1';
                } else {
                    $_finalMenuLinks[$_key][$_subKey][25] = '0';
                }

                // Permission Check
                if (isset($_subVal[2]) && !empty($_subVal[2]) && $_SWIFT->Staff->GetPermission($_subVal[2]) == '0') {
                    unset($_finalMenuLinks[$_key][$_subKey]);
                }

                if ($_key == $_selectedMenu && StripTrailingSlash(strtolower($_subVal[1])) == $_comparisonURL) {
                    $_finalSubKey = $_subKey;
                    $this->Template->Assign('_menuJavaScript', 'menulinks[' . $_selectedMenu . '][' . $_subKey . '] = menulinks[' . $_selectedMenu . '][' . $_subKey . '].replace(/topnavmenuitem/g, "topnavselmenuitem");');
//                    $_menuHiddenFieldValue = $_selectedMenu .'_'. $_subKey;
                }

                $_menuIndex++;
            }
        }

        $_menuHiddenFieldValue = '';
        if (isset($_finalMainMenu[$_menuIDDispatch])) {
            $_menuHiddenFieldValue = $_menuIDDispatch . '_' . $_finalMainMenu[$_menuIDDispatch][4] . '_' . $_finalSubKey;
        }

        $_selectedMenuClass = '';
        if (isset($_finalMainMenu[$_selectedMenu][4])) {
            $_selectedMenuClass = $_finalMainMenu[$_selectedMenu][4];
        }

        $this->Template->Assign('_menuHiddenFieldValue', $_menuHiddenFieldValue);

        $this->Template->Assign('_menuLinks', $_finalMenuLinks);
        $this->Template->Assign('_selectedMenu', $_selectedMenu);
        $this->Template->Assign('_selectedMenuClass', $_selectedMenuClass);

        $this->ProcessOnlineStaff();

        if ($this->IsAjax()) {
            echo '<script type="text/javascript">menuhiddenfieldval = "' . $_menuHiddenFieldValue . '";';
            echo '_incomingRequestHistoryChunk = "' . SWIFT::Get('_incomingRequestHistoryChunk') . '";';
            echo '</script>';
        } else {
            if (isset($_COOKIE['documentheight']) && $_COOKIE['documentheight']) {
                $_finalHeight = (int)($_COOKIE['documentheight']) - 125;
            } else {
                $_finalHeight = 400;
            }

            $this->Template->Assign('_finalHeightDifference', 125);
            $this->Template->Assign('_finalHeight', $_finalHeight);

            if (SWIFT_App::IsInstalled(APP_TICKETS) && $this->Interface->GetInterface() == SWIFT_Interface::INTERFACE_STAFF) {
                SWIFT_Loader::LoadModel('Filter:TicketFilter', APP_TICKETS);
                $_finalTicketFilterCache = array();
                $_finalTicketFilterCache = SWIFT_TicketFilter::RetrieveMenu();

                $this->Template->Assign('_ticketFilterContainer', $_finalTicketFilterCache);
            }

            /**
             * BUG FIX - Nidhi Gupta <nidhi.gupta@kayako.com>
             * Feature  - Nidhi Gupta <nidhi.gupta@kayako.com>
             * Improvement  - Nidhi Gupta <nidhi.gupta@kayako.com>
             *
             * SWIFT-4925 undefined index __executesegment and __staffemail at staffcp on ticket loading from history tab
             * SWIFT-4948 Introduce Google Tag Manager and config.php parameter in disabled state
             * SWIFT-4939 Disable Segment by default
             *
             */
            $_executeSegment = false;
            $_executeGTM = false;

            if (defined('EXECUTEGTM')) {
                $_executeGTM = EXECUTEGTM;
            }

            /*if (defined('EXECUTESEGMENT')) {
                $_executeSegment = EXECUTESEGMENT;
            }*/
            $_email = htmlspecialchars($_SWIFT->Staff->GetProperty('email'));

            $_SWIFT->Template->Assign('_executeSegment', $_executeSegment);
            $_SWIFT->Template->Assign('_executeGTM', $_executeGTM);
            $_SWIFT->Template->Assign('_staffEmail', $_email);

            $this->Template->Render('recpheader');

            $this->Navigation($_selectedNavigation);
        }

        if (count($this->_navigationContainer)) {
            $_customNavigationHTML .= implode(SWIFT_CRLF, $this->_navigationContainer);
        } else {
            $_customNavigationHTML .= '<div></div>';
        }

        if (!empty($_customNavigationHTML)) {
            echo '<div style="display: none;" id="customnavhtmlcontainer">' . $_customNavigationHTML . '</div>';
        }

        $this->ProcessDialogs();

        return true;
    }

    /**
     * Get Error
     *
     * @author Varun Shoor
     * @param string $_title The Title
     * @param string $_message The Message
     * @param string $_divID The Unique ID for this Div
     * @param bool $_showBorder Whether to Show Border for this Div
     * @return string|bool
     */
    public function GetError($_title, $_message, $_divID = '', $_showBorder = true)
    {
        if (!$this->GetIsClassLoaded() || empty($_title) || empty($_message)) {
            return false;
        }

        return '<div id="' . $_divID . '" class="dialogcontainer"><div class="dialogerror"></div><div class="dialogerrorcontainer"><div class="dialogtitle">' . $_title . '</div><div class="dialogtext">' . $_message . '</div></div></div>';

//        return '<div id="'. $_divID .'" style="padding: 4px; background: #f8f4eb;'. IIF($_showBorder, ' border-bottom: 1px SOLID #efe8da; PADDING-RIGHT: 7px; border-left: 1px solid #e9e1d1;') . '"><div class="dialogerror"><div class="hd"><div class="e">'. $_title .'</div><div class="d"><div class="c"></div></div></div><div class="bd"><div class="c"><div class="s">'. $_message .'</div></div></div><div class="ft"><div class="c"></div></div></div></div>';
    }

    /**
     * Get Alert
     *
     * @author Varun Shoor
     * @param string $_title The Title
     * @param string $_message The Message
     * @param string $_divID The Unique ID for this Div
     * @param bool $_showBorder Whether to Show Border for this Div
     * @return string|bool
     */
    public function GetAlert($_title, $_message, $_divID = '', $_showBorder = true)
    {
        if (!$this->GetIsClassLoaded() || empty($_title) || empty($_message)) {
            return false;
        }

        return '<div id="' . $_divID . '" class="dialogcontainer"><div class="dialogalert"></div><div class="dialogalertcontainer"><div class="dialogtitle">' . $_title . '</div><div class="dialogtext">' . $_message . '</div></div></div>';

        //return '<div id="'. $_divID .'" style="padding: 4px; background: #f8f4eb;'. IIF($_showBorder, ' border-bottom: 1px SOLID #efe8da; PADDING-RIGHT: 7px; border-left: 1px solid #e9e1d1;') . '"><div class="dialogalert"><div class="hd"><div class="e">'. $_title .'</div><div class="d"><div class="c"></div></div></div><div class="bd"><div class="c"><div class="s">'. $_message .'</div></div></div><div class="ft"><div class="c"></div></div></div></div>';
    }

    /**
     * Get Confirmation
     *
     * @author Varun Shoor
     * @param string $_title The Title
     * @param string $_message The Message
     * @param string $_divID The Unique ID for this Div
     * @return string|bool
     */
    public function GetInfo($_title, $_message, $_divID = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_title) || empty($_message)) {
            return false;
        }

        return '<div id="' . $_divID . '" class="dialogcontainer"><div class="dialogok"></div><div class="dialogokcontainer"><div class="dialogtitle">' . $_title . '</div><div class="dialogtext">' . $_message . '</div></div></div>';
    }

    /**
     * Render the Navigation
     *
     * @author Varun Shoor
     * @param int $_selectedNavigation The Selected Navigation Item
     * @return bool "true" on Success, "false" otherwise
     */
    public function Navigation($_selectedNavigation)
    {
        if ($this->Interface->GetInterface() == SWIFT_Interface::INTERFACE_ADMIN) {
            $_globalAdminBar = SWIFT::Get('globaladminbar');
            $_globalAdminBarItems = SWIFT::Get('globaladminbaritems');
            $_adminNavigationBar = SWIFT::Get('adminbar');
            $_adminBarItems = SWIFT::Get('adminbaritems');

            /**
             * BUNBTX KAYAKOC-3469: Error message is displayed for Admin while
             * navigating to 'Move Attachments' page
             *
             * @author Werner Garcia <werner.garcia@crossover.com>
             */

            if (defined('ENFORCEATTACHMENTS_INFILES') && ENFORCEATTACHMENTS_INFILES === true) {
                // Remove Move Attachments menu option
                foreach ($_adminBarItems as $i => $adminBarItem) {
                    foreach ($adminBarItem as $j => $items) {
                        if ($items[1] === '/Base/MoveAttachments/Index') {
                            unset($_adminBarItems[$i][$j]);
                            break 2;
                        }
                    }
                }
            }

            if ($_globalAdminBar) {
                $_adminNavigationBar = $_globalAdminBar + $_adminNavigationBar;
            }

            if ($_globalAdminBarItems) {
                $_adminBarItems = $_globalAdminBarItems + $_adminBarItems;
            }

            foreach ($_adminNavigationBar as $_key => $_val) {
                if (!SWIFT_App::IsInstalled($_val[2])) {
                    unset($_adminNavigationBar[$_key]);

                    if (isset($_adminBarItems[$_key])) {
                        unset($_adminBarItems[$_key]);
                    }

                    continue;
                }

                if ($_selectedNavigation == $_key) {
                    $_adminNavigationBar[$_key][4] = true;
                }

                if (isset($_adminBarItems[$_key])) {
                    $_adminNavigationBar[$_key][5] = $_adminBarItems[$_key];
                }
            }

            $this->Template->Assign('_adminNavigationBar', $_adminNavigationBar);
            $this->Template->Assign('_adminNavigationBarItems', $_adminBarItems);
        }

        $this->Template->Assign('_selectedNavigation', $_selectedNavigation);

        if ($this->IsAjax()) {
            return true;
        }

        $this->Template->Render('re' . $this->Interface->GetName() . 'navbar');

        return true;
    }

    /**
     * Render the CP Footer
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Footer()
    {
        $_SWIFT = SWIFT::GetInstance();
        $_finalNotificationContainer = array_merge($this->_notificationContainer, SWIFT::GetNotificationContainer());

        echo '<script type="text/javascript">';
        echo '$(document).ready(function(){if($(\'.subjectspancontainer\').length){var subjectcontainerwidth = ($(\'.subjectspancontainer\')[0].clientWidth - 100);$(\'.subjectspan > a:first-child\').css({"max-width" : subjectcontainerwidth});}});';
        echo 'if (window.$UIObject) { window.$UIObject.Queue(function() { ';
        if ($this->_isDialog == false) {
            $_headerURL = htmlspecialchars($_SWIFT->Router->GetCurrentURL());
            echo 'SetHeaderURL("' . $_headerURL . '");';

            /*
             * BUG FIX - Varun Shoor
             * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
             *
             * SWIFT-1806 Using 'enter' in "Your Question" field while initiating a chat, help desk throws an error when viewing chat history
             * SWIFT-4917 Cross site scripting flaw in QuickSupport Case.
             */
            $_documentTitle = preg_replace("#(\r\n|\r|\n)#s", ' ', addslashes($this->Input->SanitizeForXSS($this->_documentTitle)));
            echo 'SetHeaderTitle("' . $_documentTitle . '");';
        }

        // Process the notifications

        foreach ($_finalNotificationContainer as $_notificationType => $_notificationList) {
            $_notificationFunction = 'SWIFT_Notification.Info';
            if ($_notificationType == 'alert') {
                $_notificationFunction = 'SWIFT_Notification.Alert';
            } elseif ($_notificationType == 'error') {
                $_notificationFunction = 'SWIFT_Notification.Error';
            } elseif ($_notificationType == 'users') {
                $_notificationFunction = 'SWIFT_Notification.Users';
            }

            foreach ($_notificationList as $_notificationText) {
                echo $_notificationFunction . '("' . preg_replace("#(\r\n|\r|\n)#s", '', htmlentities(StripName($_notificationText, 105), ENT_QUOTES, $_SWIFT->Language->Get('charset'))) . '"); ';
            }
        }


        if (count($this->_formList)) {
            $_ajaxCode = '';
            foreach ($this->_formList as $_key => $_val) {
                $_ajaxCode .= 'bindFormSubmit("' . $_val . SWIFT_UserInterface::FORM_SUFFIX . '");';
            }


            echo $_ajaxCode;

        }
        echo '}); }</script>';

        echo '<script>function UITipTagsValidationError(){
            var tag_inputs = ["taginput_tags", "taginput_replytags", "taginput_reltags"];
            $(tag_inputs).each(function(key, value){
                if($("#"+value).is(\':visible\')){
                    $("#"+value).parent().prev().qtip(
                        {
                            show: "",
                            hide: {
                                event: \'click mouseleave\'
                            },
                            position: {
                                my: "top center",
                                at: "bottom center",
                            },
                            style: {
                                classes: "qtip-tag-errors"
                            },
                            content: { text: "' . $this->Language->get('unsupportedtagchars') . '"}
                        }).qtip("show");}});}</script>';
        if ($this->IsAjax()) {
            return true;
        }


        $this->Template->Render('admincpfooter');

        return true;
    }

    /**
     * Processes the Online Staff and stores the data in a container
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function ProcessOnlineStaff()
    {
        $_SWIFT = SWIFT::GetInstance();

        // Now process the online users
        $_onlineStaffList = $_staffIDList = $_staffSessionContainer = array();
        $_index = 0;
        $_activityThreshold = DATENOW - 180;

        $_staffCacheContainer = $_SWIFT->Cache->Get('staffcache');

        $this->Database->Query("SELECT sessiontype, typeid, lastactivity FROM " . TABLE_PREFIX . "sessions WHERE sessiontype IN ('" . SWIFT_Interface::INTERFACE_STAFF . "', '" . SWIFT_Interface::INTERFACE_ADMIN . "', '" . SWIFT_Interface::INTERFACE_WINAPP . "', '" . SWIFT_Interface::INTERFACE_STAFFAPI . "', '" . SWIFT_Interface::INTERFACE_INTRANET . "') AND lastactivity >= '" . $_activityThreshold . "' AND typeid != 0 ORDER BY lastactivity DESC");
        while ($this->Database->NextRecord()) {
            if (!isset($_staffSessionContainer[$this->Database->Record['sessiontype']][$this->Database->Record['typeid']])) {
                $_staffSessionContainer[$this->Database->Record['sessiontype']][$this->Database->Record['typeid']] = 0;
            }

            $_staffSessionContainer[$this->Database->Record['sessiontype']][$this->Database->Record['typeid']]++;
            $_onlineStaffList[$this->Database->Record['typeid']]['fullname'] = $_staffCacheContainer[$this->Database->Record['typeid']]['fullname'];
            $_onlineStaffList[$this->Database->Record['typeid']]['staffid'] = $this->Database->Record['typeid'];
            $_onlineStaffList[$this->Database->Record['typeid']]['onlinecount'] = $_staffSessionContainer[$this->Database->Record['sessiontype']][$this->Database->Record['typeid']];
            $_onlineStaffList[$this->Database->Record['typeid']]['type'] = $this->Database->Record['sessiontype'];

            $_index++;
            $_staffIDList[] = $this->Database->Record['typeid'];
        }

        $this->_onlineStaffContainer = $_onlineStaffList;

        return true;
    }

    /**
     * Retrieve the Online Staff Container
     *
     * @author Varun Shoor
     * @return mixed "_onlineStaffContainer" (ARRAY) on Success, "false" otherwise
     */
    public function GetOnlineStaffContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_onlineStaffContainer;
    }

    /**
     * Processes the Control Panel Menu into a includable or displayed javascript
     *
     * @author Varun Shoor
     * @return string
     */
    public static function RenderControlPanelMenu()
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_SWIFT->Settings->Get('g_jscache') == 1) {
            $_enableJSCache = false;
        } else {
            $_enableJSCache = true;
        }

        if ($_SWIFT->Settings->Get('g_cpmenu') == 'hover') {
            $_SWIFT->Template->Assign('_controlPanelMenu', 'hover');
        } else {
            $_SWIFT->Template->Assign('_controlPanelMenu', 'click');
        }

        $_commentsHash = $_returnData = '';

        if ($_enableJSCache == false) {
            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-1276 Cache of JavaScript are the same for all laguages in Admin CP and Staff CP
             *
             * Comments: None
             */

            $_languageCode = 'en-default';
            if (isset($_SWIFT->Language) && $_SWIFT->Language instanceof SWIFT_LanguageEngine && $_SWIFT->Language->GetIsClassLoaded()) {
                $_languageCode = $_SWIFT->Language->GetLanguageCode();
            }

            $_filePartOne = $_SWIFT->Interface->GetName() . 'cpmenu';
            $_filePartTwo = '_' . $_languageCode . '_' . $_SWIFT->Settings->Get('g_cpmenu');
            $_filePartThree = '_' . $_SWIFT->Staff->GetStaffID();

            $_dataHash = $_filePartOne . $_commentsHash . $_filePartTwo . $_filePartThree;

            $_filePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_CACHEDIRECTORY . '/' . $_dataHash . '.js';

            if (!file_exists($_filePath)) {
                // Delete the previous cached file for this javascript.
                if ($_directoryHandle = opendir('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_CACHEDIRECTORY)) {
                    while (false !== ($_fileName = readdir($_directoryHandle))) {
                        if (preg_match("@^{$_filePartOne}[\\w]*{$_filePartTwo}\\.js$@", $_fileName)) {
                            @unlink('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_CACHEDIRECTORY . '/' . $_fileName);
                        }
                    }
                    closedir($_directoryHandle);
                }

                // Write the new one.
                $_javascriptCacheData = $_SWIFT->Template->Get('cpmenu');

                $_fp = fopen($_filePath, 'w+');
                fwrite($_fp, $_javascriptCacheData);
                fclose($_fp);
                @chmod($_filePath, 0666);
            }

            $_returnData = '<script type="text/javascript" src="' . SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_CACHEDIRECTORY . '/' . $_dataHash . '.js"></script>';
        } else {
            $_returnData = '<script type="text/javascript">';
            $_returnData .= $_SWIFT->Template->Get('cpmenu');
            $_returnData .= '</script>';
        }

        return $_returnData;
    }

    /**
     * Render the Admin Navigation Bar (Hook for Template Engine)
     *
     * @author Varun Shoor
     * @return string
     */
    public static function RenderAdminNavigationBar()
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_SWIFT->Settings->Get('g_jscache') == 1) {
            $_enableJSCache = false;
        } else {
            $_enableJSCache = true;
        }

        $_returnData = '';

        if ($_enableJSCache == true) {
            $_dataHash = 'cpadminnavbar';
            $_filePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_CACHEDIRECTORY . '/' . $_dataHash . '.js';
            if (!file_exists($_filePath)) {
                $_javaScriptCacheData = self::StringToJavascript($_SWIFT->Template->Get('adminnavbardata'));
                $_fp = fopen($_filePath, 'w+');
                fwrite($_fp, $_javaScriptCacheData);
                fclose($_fp);
                @chmod($_filePath, 0666);
            }

            $_returnData = '<script type="text/javascript" src="' . SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_CACHEDIRECTORY . '/' . $_dataHash . '.js"></script>';
        } else {
            $_returnData = '<script type="text/javascript">' . SWIFT_CRLF;
            $_returnData .= self::StringToJavascript($_SWIFT->Template->Get('adminnavbardata'));
            $_returnData .= '</script>';
        }

        return $_returnData;
    }

    /**
     * Renders the Online Staff Data
     *
     * @author Varun Shoor
     * @return string|bool
     */
    public static function RenderOnlineStaff()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT->UserInterface instanceof SWIFT_UserInterface || !$_SWIFT->UserInterface->GetIsClassLoaded()) {
            return false;
        }

        $_finalText = '';

        foreach ($_SWIFT->UserInterface->GetOnlineStaffContainer() as $_key => $_val) {
            if ($_val['type'] == SWIFT_Interface::INTERFACE_ADMIN) {
                $_icon = 'icon_onlinemoov';
                $_text = $_SWIFT->Language->Get('admincp');
            } elseif ($_val['type'] == SWIFT_Interface::INTERFACE_WINAPP) {
                $_icon = 'icon_online';
                $_text = $_SWIFT->Language->Get('winapp');
            } elseif ($_val['type'] == SWIFT_Interface::INTERFACE_STAFFAPI) {
                $_icon = 'icon_onlinepda';
                $_text = $_SWIFT->Language->Get('staffapi');
            } else {
                $_icon = 'icon_onlineyellow';
                $_text = $_SWIFT->Language->Get('staffcp');
            }

            $_finalText .= '<div class="itemrowunlinked" id="itemoption' . $_key . '" onClick="javascript: void(0);" title="' . $_text . '"><i class="fa fa-circle online_icon ' . $_icon . '" aria-hidden="true"></i>' . text_to_html_entities($_val['fullname']) . IIF($_val['onlinecount'] > 1, ' (' . $_val['onlinecount'] . ')') . '</div>';
        }


        return $_finalText;
    }

    /**
     * Converts string to parsable Javascript
     *
     * @author Varun Shoor
     * @param string $_string The String to Process
     * @return string The Processed String
     */
    public static function StringToJavascript($_string)
    {
        $_string = preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_string); // Convert to universal CRLF
        return "document.write('" . str_replace(SWIFT_CRLF, "\\n", str_replace("'", "\\'", $_string)) . "');" . SWIFT_CRLF;
    }

    /**
     * Load the Toolbar object
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function LoadToolbar()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceToolbar', [$this], true, false, 'base');

        $this->Toolbar = $this->UserInterfaceToolbar;

        return true;
    }

    /**
     * Set the Dialog Options
     *
     * @author Varun Shoor
     * @param bool $_saveButton Whether to Display the Save Button
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetDialogOptions($_saveButton = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_saveButton = $_saveButton;

        return true;
    }

    /**
     * Start the Form & Interface
     *
     * @author Varun Shoor
     * @param string $_formName The Form Name
     * @param string $_action The Form Action
     * @param bool $_isUpload Whether the form is a file upload form
     * @param bool|string $_noSubmit Whether to Disable Form Submission
     * @param string $_target The Form Target Mode
     * @param string $_targetDiv The Output Div
     * @param string $_targetFunction The Target JS Function
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Start($_formName = '', $_action = '', $_mode = false, $_isDialog = false, $_isUpload = false, $_noSubmit = false, $_target = '', $_targetDiv = '', $_targetFunction = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_mode)) {
            $_mode = self::MODE_INSERT;
        }

        $this->LoadToolbar();
        $this->_isDialog = $_isDialog;

        $this->_targetDiv = $_targetDiv;
        $this->_targetFunction = $_targetFunction;
        $this->_mode = $_mode;
        $this->_noSubmit = $_noSubmit;
        $this->_interfaceStarted = true;

        if ($_formName) {
            $_formName = Clean($_formName);

            $_finalAction = $_action;
            if (substr($_action, 0, 1) == '/') {
                $_finalAction = SWIFT::Get('basename') . $_action;
            }

            $this->_outputContainer = '<form name="' . $_formName . self::FORM_SUFFIX . '" id="' . $_formName . self::FORM_SUFFIX . '" action="' . $_finalAction . '" method="post"' . IIF($_isUpload, ' enctype="multipart/form-data"') . IIF($_noSubmit && !$_target, ' onsubmit="javascript: return false;"') . IIF($_target, 'target="' . $_target . '"') . '>' . SWIFT_CRLF;
            $this->_outputContainer .= '<script type="text/javascript">if (typeof _tabFunctionQueue != \'undefined\') { _tabFunctionQueue[\'' . Clean($_formName) . '\'] = new Array();';
            $this->_outputContainer .= '_tabFunctionQueue[\'' . Clean($_formName) . '\'][\'show\'] = _tabFunctionQueue[\'' . Clean($_formName) . '\'][\'load\'] = _tabFunctionQueue[\'' . Clean($_formName) . '\'][\'enable\'] = _tabFunctionQueue[\'' . Clean($_formName) . '\'][\'disable\'] = new Array(); }</script>';

            if (!$_noSubmit) {
                $this->_formList[] = $_formName;
            }

            $this->_formName = $_formName;

            if ($_isUpload) {
                $this->Hidden('isajax', 1);
            }

            $this->Hidden('csrfhash', $_SWIFT->Session->GetProperty('csrfhash'));
        }

        return true;
    }

    /**
     * End the UI Processing and Dispatch Output to the Browser
     *
     * @author Varun Shoor
     * @return string
     */
    public function End()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_tabHTML = $_tabPrefixHTML = $_tabSuffixHTML = $_tabHeaderHTML = $_tabJavaScript = '';
        if (_is_array($this->_tabContainer)) {
            $_disabledIndexList = array();

            $_tabIndex = 0;
            $_selectedTabIndex = 0;

            foreach ($this->_tabContainer as $_key => $_val) {
                $_tabIcon = $_val->GetIcon();

                // Check if the icon is a custom icon if not then append the SWIFT themepath
                //Toolbar headers icos removed in the UI Upgrade.
                if (substr($_tabIcon, 0, 4) != 'http') {
                    $_tabIcon = SWIFT::Get('themepath') . 'images/' . $_tabIcon;

                }

                $_tabURL = $_val->GetTabURL();
                $_tabCounter = $_val->GetTabCounter();

                $_finalTabURL = '#' . $this->_formName . '_tab_' . $_val->GetID();
                if (!empty($_tabURL)) {
                    $_finalTabURL = $_tabURL;
                }

                $_tabHeaderHTML .= '<li>' . IIF(!empty($_tabCounter), '<div class="notecounterred">' . $_tabCounter . '</div>') . '<a href="' . $_finalTabURL . '">' . IIF(!empty($_tabIcon), '<img id="' . $this->_formName . '_tabimg_' . $_val->GetID() . '" src="' . $_tabIcon . '" align="absmiddle" border="0" /> ') . $_val->GetTitle() . '</a></li>';
                $_tabHTML .= $_val->Render();
                $_tabJavaScript .= $_val->RenderJavaScript();

                if ($_val->GetIsDisabled()) {
                    $_disabledIndexList[] = $_val->GetTabIndex();
                }

                if ($_val->GetIsSelected()) {
                    $_selectedTabIndex = $_tabIndex;
                }

                $_tabIndex++;
            }

            $this->_outputContainer .= '<div id="' . $this->_formName . 'tabs" class="ui-tabs ui-tabs-hide"><ul class="ui-tabs-nav">' . $_tabHeaderHTML . '</ul>' . $_tabHTML . '</div>';

            $this->_outputContainer .= SWIFT_CRLF . '<script type="text/javascript">' . $_tabJavaScript . '</script>' . SWIFT_CRLF;

            $_extendedArgumentsContainer = array();

            if (count($_disabledIndexList)) {
                $_extendedArgumentsContainer[] = 'disabled: [' . implode(', ', $_disabledIndexList) . ']';
            }

            $_extendedArgumentsContainer[] = 'select: function () { UIHideAllDropDowns(); }';
            $_extendedArgumentsContainer[] = 'selected: ' . $_selectedTabIndex;
            $_extendedArgumentsContainer[] = 'show: function() { UIProcessTabFunctionQueue(\'' . $this->_formName . '\', \'show\') }';
            $_extendedArgumentsContainer[] = 'load: function() { UIProcessTabFunctionQueue(\'' . $this->_formName . '\', \'load\') }';
            $_extendedArgumentsContainer[] = 'enable: function() { UIProcessTabFunctionQueue(\'' . $this->_formName . '\', \'enable\') }';
            $_extendedArgumentsContainer[] = 'disable: function() { UIProcessTabFunctionQueue(\'' . $this->_formName . '\', \'disable\') }';
            $_extendedArgumentsContainer[] = 'cache: true';

            $this->_outputContainer .= '<script type="text/javascript">if (window.$UIObject) { window.$UIObject.Queue(function() { $("#' . $this->_formName . 'tabs").tabs({ ' . implode(', ', $_extendedArgumentsContainer) . ' }); }); }</script>';
        }

        $this->_outputContainer .= '<script type="text/javascript">';
        $this->_outputContainer .= 'if (window.$UIObject) { window.$UIObject.Queue(function(){';

        if ($this->_mode == self::MODE_EDIT || ($this->_isDialog && (!isset($_POST['_isDialog']) || $_POST['_isDialog'] != 1))) {

            foreach ($this->_formList as $_key => $_val) {
                $this->_outputContainer .= 'bindFormSubmit("' . $_val . self::FORM_SUFFIX . '", "' . $this->_targetDiv . '"' . IIF(!empty($this->_targetFunction), ',' . $this->_targetFunction) . ');';
            }

            $this->_formList = array(); // Reset the form list
        } elseif ($this->_noSubmit) {
        }
        $this->_outputContainer .= '}); }</script>';

        if (!empty($this->_formName)) {
            if ($this->_isDialog && (!isset($_POST['_isDialog']) || $_POST['_isDialog'] != 1)) {
                $_buttonText = IIF($this->_saveButton, '<input type="button" name="submitbutton" id="' . $this->_formName . SWIFT_UserInterface::FORM_SUFFIX . '_submit" class="rebuttonblue" onclick="javascript: $(\'body\').css(\'overflow\',\'scroll\');$(\'#' . $this->_formName . self::FORM_SUFFIX . '\').submit(); if (typeof tinyMCE != \'undefined\')
 {
 tinyMCE.triggerSave(); }PreventDoubleClicking(this);" value="' . $this->Language->Get('save') . '" onfocus="blur();" /> ') . '<input type="button" name="cancel" class="rebuttonred" value="' . $this->Language->Get('cancel') . '" onclick="javascript: UIDestroyAllDialogs();" onfocus="blur();" />';
                if (!empty($this->_overrideButtonText)) {
                    $_buttonText = str_replace('%formid%', $this->_formName . SWIFT_UserInterface::FORM_SUFFIX, $this->_overrideButtonText);
                }

                $this->_outputContainer .= '<input type="hidden" name="_isDialog" value="1" />';
                $this->_outputContainer .= '<div class="bottom-container-panel"></div><div style="position: absolute; bottom: 12px; left: 10px;">' . $this->_dialogBottomLeftPanel . '</div><div style="position: absolute; bottom: 12px; right: 10px; text-align: right;">' . $_buttonText . '</div>';
            } elseif (isset($_POST['_isDialog']) && $_POST['_isDialog'] == 1) {
                $this->_outputContainer .= '<input type="hidden" name="_isDialog" value="1" />';
            }

            if (_is_array($this->_hiddenFieldContainer)) {
                foreach ($this->_hiddenFieldContainer as $_key => $_val) {
                    if (_is_array($_val)) {
                        foreach ($_val as $_subVal) {
                            $this->_outputContainer .= '<input type="hidden" name="' . addslashes($_key) . '[]" value="' . htmlspecialchars($_subVal) . '" />';
                        }
                    } else {
                        $this->_outputContainer .= '<input type="hidden" id="' . addslashes($_key) . '" name="' . addslashes($_key) . '" value="' . htmlspecialchars($_val) . '" />';
                    }
                }
            }

            $this->_outputContainer .= '</form>';
        }

        $this->_outputContainer .= $this->_appendHTML;

        echo $this->_outputContainer;
    }

    /**
     * Get the Current Tab Count
     *
     * @author Varun Shoor
     * @return mixed "_tabCount" (INT) on Success, "false" otherwise
     */
    public function GetTabCount()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_tabCount;
    }

    /**
     * Incrememnts the Tab Count
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function IncrementTabCount()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->_tabCount++;

        return true;
    }

    /**
     * Add a new Tab to the User Interface
     *
     * @author Varun Shoor
     * @param string $_tabTitle The Tab Title
     * @param string $_tabIcon The Icon Image Filename
     * @param string $_tabID The Tab ID
     * @param bool $_isSelected Whether the tab should be selected
     * @param bool $_isDisabled Whether the Tab is Disabled
     * @param int $_defaultPadding The Default Padding
     * @param string $_tabURL (OPTIONAL) The AJAX Tab URL
     * @return SWIFT_UserInterfaceTab
     */
    public function AddTab($_tabTitle, $_tabIcon, $_tabID = null, $_isSelected = false, $_isDisabled = false, $_defaultPadding = 4, $_tabURL = '')
    {
        $_tabIndex = $this->_tabIndex;

        $_SWIFT_UserInterfaceTabObject = new SWIFT_UserInterfaceTab($this, $_tabTitle, $_tabIcon, $_tabIndex, $_tabID, $_isSelected, $_isDisabled,
            $_defaultPadding, $_tabURL);

        $this->_tabIndex++;

        $this->IncrementTabCount();

        $this->_tabContainer[] = $_SWIFT_UserInterfaceTabObject;

        return $_SWIFT_UserInterfaceTabObject;
    }

    /**
     * Add a Hidden Field
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string|int $_value The Field Value
     * @return bool "true" on Success, "false" otherwise
     */
    public function Hidden($_name, $_value)
    {
        if (!$this->GetIsClassLoaded() || empty($_name)) {
            return false;
        }

        $this->_hiddenFieldContainer[$_name] = $_value;

        return true;
    }

    /**
     * Add a Hidden Field
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_value The Field Value
     * @return bool "true" on Success, "false" otherwise
     */
    public function HiddenArray($_name, $_value)
    {
        if (!$this->GetIsClassLoaded() || empty($_name)) {
            return false;
        }

        $this->_hiddenFieldContainer[$_name][] = $_value;

        return true;
    }

    /**
     * Retrieve the Mode Name
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @return mixed "Mode Name" (STRING) on Success, "false" otherwise
     */
    public function GetModeName($_mode)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        switch ($_mode) {
            case self::MODE_INSERT:
                return 'Insert';
                break;

            case self::MODE_EDIT:
                return 'Edit';
                break;

            default:
                break;
        }

        return false;
    }

    /**
     * Set the Dialog Bottom Left Panel
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetDialogBottomLeftPanel($_dialogBottomLeftPanel)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_dialogBottomLeftPanel = $_dialogBottomLeftPanel;

        return true;
    }

    /**
     * Override the button text
     *
     * @author Varun Shoor
     * @param string $_buttonText
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function OverrideButtonText($_buttonText)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_overrideButtonText = $_buttonText;

        return true;
    }

    /**
     * Render the Mass Action Panel HTML
     *
     * @author Varun Shoor
     * @param string $_formName The Form Name
     * @param array $_tabContainer The Tab Container
     * @return string
     */
    public static function RenderMassActionPanelTabs($_formName, $_tabContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_tabIndex = 0;
        $_selectedTabIndex = 0;
        $_renderHTML = $_tabHeaderHTML = $_tabHTML = '';

        $_disabledIndexList = array();

        foreach ($_tabContainer as $_key => $_val) {
            $_tabIcon = $_val->GetIcon();

            $_tabHeaderHTML .= '<li><a href="#' . $_formName . '_tab_' . $_val->GetID() . '">' . IIF(!empty($_tabIcon),
                    '<img id="' . $_formName . '_tabimg_' . $_val->GetID() . '" src="' . SWIFT::Get('themepath') . 'images/' .
                    $_tabIcon . '" align="absmiddle" border="0" /> ') . $_val->GetTitle() . '</a></li>';
            $_tabHTML .= $_val->Render();

            if ($_val->GetIsDisabled()) {
                $_disabledIndexList[] = $_val->GetTabIndex();
            }

            if ($_val->GetIsSelected()) {
                $_selectedTabIndex = $_tabIndex;
            }

            $_tabIndex++;
        }

        $_renderHTML .= '<div id="' . $_formName . 'tabs" class="ui-tabs ui-tabs-hide"><ul class="ui-tabs-nav">' . $_tabHeaderHTML . '</ul>' . $_tabHTML . '</div>';

        $_extendedArgumentsContainer = array();

        if (count($_disabledIndexList)) {
            $_extendedArgumentsContainer[] = 'disabled: [' . implode(', ', $_disabledIndexList) . ']';
        }

        $_extendedArgumentsContainer[] = 'select: function () { UIHideAllDropDowns(); }';
        $_extendedArgumentsContainer[] = 'selected: ' . $_selectedTabIndex;

        $_renderHTML .= '<script type="text/javascript">if (window.$UIObject) { window.$UIObject.Queue(function() { $("#' .
            $_formName . 'tabs").tabs({ ' . implode(', ', $_extendedArgumentsContainer) . ' }); }); }</script>';

        return '<div>' . $_renderHTML . '</div>';
    }

    /**
     * Notify the Users
     *
     * @author Varun Shoor
     * @param mixed $_notificationType
     * @param string $_notificationText
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Notify($_notificationType, $_notificationText)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!SWIFT::IsValidNotificationType($_notificationType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset($this->_notificationContainer[$_notificationType])) {
            $this->_notificationContainer[$_notificationType] = array();
        }

        $this->_notificationContainer[$_notificationType][] = $_notificationText;

        return true;
    }
}

?>
