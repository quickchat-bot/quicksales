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

namespace Base\Library\UserInterface;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\Widget\SWIFT_Widget;
use SWIFT;
use SWIFT_Exception;

/**
 * The Client User Interface Management Class
 *
 * @method void DisplayError($title, $message, $_param1 = '')
 * @author Varun Shoor
 */
class SWIFT_UserInterfaceClient extends SWIFT_UserInterface
{
    protected $_defaultWidget = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Initialize the Client Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function InitializeClient()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Template->Assign('_widgetContainer', SWIFT_Widget::GetWidgetListForUser());

        return true;
    }

    /**
     * Set the default widget
     *
     * @author Varun Shoor
     * @param string $_defaultWidget The Default Widget Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetWidget($_defaultWidget)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_defaultWidget)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_defaultWidget = $_defaultWidget;

        return true;
    }

    /**
     * Retrieve the default widget
     *
     * @author Varun Shoor
     * @return mixed "_defaultWidget" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetWidget()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_defaultWidget;
    }

    /**
     * Load the Header
     *
     * @author Varun Shoor
     * @param string $_defaultWidget The Default Widget Name
     * @param null $_param1
     * @param null $_param2
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Header($_defaultWidget = '', $_param1 = null, $_param2 = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!empty($_defaultWidget)) {
            $this->SetWidget($_defaultWidget);
        }

        $this->InitializeClient();
        $this->ProcessDialogs();

        $this->Template->Render('header');

        return true;
    }

    /**
     * Load the Footer
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Footer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        echo $this->GetFooterScript();

        $this->Template->Render('footer');

        return true;
    }

    /**
     * Get the Footer Script
     *
     * @author Varun Shoor
     * @return string The Footer Script
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFooterScript()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_scriptHTML = '';
        $_tinyMCEerror = false;

        // Process and dispatch error fields Javascript
        $_scriptHTML = '<script type="text/javascript"> $(function(){ $(\'.dialogerror, .dialoginfo, .dialogalert\').fadeIn(\'slow\');';
        $_scriptHTML .= '$("form").on("submit", function(e){$(this).find("input:submit").attr("disabled", "disabled");});';
        /*
        * BUG FIX - Ukbe Akdogan
        * KAYAKOC-19722 Next/Submit ticket button stops working after BACK button in Firefox
        * Comments: DFCache should be disabled in order to have pages reloaded when user navigates back.
        *           Setting unload event handler (even an empty function) prevents use of DFCache
        */
        $_scriptHTML .= '$(window).on("unload",function(){});';
        foreach (SWIFT::GetErrorFieldContainer() as $_key => $_val) {
            $_scriptHTML .= '$(\'input[name="' . Clean($_val) . '"]\').addClass(\'swifttexterror\');';
            $_scriptHTML .= '$(\'textarea[name="' . Clean($_val) . '"]\').addClass(\'swifttexterror\');';
            $_scriptHTML .= '$(\'select[name="' . Clean($_val) . '"]\').addClass(\'swifttexterror\');';

            /*
             * BUG FIX - Ankit Saini
             *
             * SWIFT-5238 Highlight empty or required fields on Support Center
             *
             * Comments: To highlight the tinymce iframe, when tiny mce is enabled.
             */
            if ($this->Settings->Get('t_tinymceeditor') != '0' && $_val == "ticketmessage") {
                $_tinyMCEerror = true;
            }
        }

        $_scriptHTML .= '}); function showEditorValidationError(){' . IIF($_tinyMCEerror, '$(\'#ticketmessage_ifr\').addClass(\'swifttexterror\');') . '}</script>';

        return $_scriptHTML;
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

        $_errorContainer = $_infoContainer = $_alertContainer = array();

        if (_is_array(SWIFT::GetInfoContainer())) {
            $_infoContainer = SWIFT::GetInfoContainer();
        }

        if (_is_array(SWIFT::GetErrorContainer())) {
            $_errorContainer = SWIFT::GetErrorContainer();
        }

        if (_is_array(SWIFT::GetAlertContainer())) {
            $_alertContainer = SWIFT::GetAlertContainer();
        }

        SWIFT::ResetAllContainers();

        $this->Template->Assign('_errorContainer', $_errorContainer);
        $this->Template->Assign('_infoContainer', $_infoContainer);
        $this->Template->Assign('_alertContainer', $_alertContainer);

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

        return '<div class="dialogerror"><div class="dialogerrorsub"><div class="dialogerrorcontent">' . $_message . '</div></div></div>';
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

        return '<div id="' . $_divID . '" style="padding: 4px; background: #f8f4eb;' . IIF($_showBorder, ' border-bottom: 1px SOLID #efe8da; PADDING-RIGHT: 7px; border-left: 1px solid #e9e1d1;') . '"><div class="dialogalert"><div class="hd"><div class="e">' . $_title . '</div><div class="d"><div class="c"></div></div></div><div class="bd"><div class="c"><div class="s">' . $_message . '</div></div></div><div class="ft"><div class="c"></div></div></div></div>';
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

        return '<div id="' . $_divID . '" style="padding: 4px; background: #f8f4eb; border-bottom: 1px SOLID #efe8da; PADDING-RIGHT: 7px;"><div class="dialogok"><div class="hd"><div class="e">' . $_title . '</div><div class="d"><div class="c"></div></div></div><div class="bd"><div class="c"><div class="s">' . $_message . '</div></div></div><div class="ft"><div class="c"></div></div></div></div>';
    }
}

?>
