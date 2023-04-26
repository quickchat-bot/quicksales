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

namespace Base\Library\UserInterface;

use Base\Library\UserInterface\SWIFT_UserInterface;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Toolbar Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_UserInterfaceToolbar extends SWIFT_Model
{
    private $_outputContainer = '';
    private $_toolbarID = '';
    static private $_buttonIndex = 0;

    private $_SWIFT_UserInterfaceObject = false;

    // Core Constants
    const LINK_CONFIRM = 1;
    const LINK_DEFAULT = 2;
    const LINK_SUBMIT = 3;
    const LINK_JAVASCRIPT = 4;
    const LINK_VIEWPORT = 5;
    const LINK_SUBMITCONFIRM = 6;
    const LINK_FORM = 7;
    const LINK_SUBMITTARGET = 8;
    const LINK_SPACER = 9;
    const LINK_SUBMITCONFIRMCUSTOM = 10;
    const LINK_NEWWINDOW = 11;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_UserInterface $_SWIFT_UserInterfaceObject The SWIFT User Interface Object
     * @param string $_toolbarID The Toolbar DOM ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_UserInterface $_SWIFT_UserInterfaceObject, $_toolbarID = '')
    {
        parent::__construct();

        if (!$this->SetInterface($_SWIFT_UserInterfaceObject)) {
            $this->SetIsClassLoaded(false);
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $this->SetID($_toolbarID);
    }

    /**
     * Set the User Interface
     *
     * @author Varun Shoor
     * @param SWIFT_UserInterface $_SWIFT_UserInterfaceObject The User Interface Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetInterface(SWIFT_UserInterface $_SWIFT_UserInterfaceObject)
    {
        if (!$_SWIFT_UserInterfaceObject->GetIsClassLoaded()) {
            return false;
        }

        $this->_SWIFT_UserInterfaceObject = $_SWIFT_UserInterfaceObject;

        return true;
    }

    /**
     * Retrieve the Currently Set User Interface
     *
     * @author Varun Shoor
     * @return mixed "_SWIFT_UserInterfaceObject" (OBJECT) on Success, "false" otherwise
     */
    public function GetInterface()
    {
        return $this->_SWIFT_UserInterfaceObject;
    }

    /**
     * Set The Toolbar ID
     *
     * @author Varun Shoor
     * @param string $_toolbarID The Toolbar DOM ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetID($_toolbarID)
    {
        if (empty($_toolbarID)) {
            return false;
        }

        $this->_toolbarID = $_toolbarID;

        return true;
    }

    /**
     * Add a Toolbar Buton
     *
     * @author Varun Shoor
     *
     * @param string $_buttonTitle The Button Title
     * @param string $_buttonIcon The Button Icon
     * @param string $_buttonLink The Button Link
     * @param int $_buttonLinkType The Button Link Type
     * @param string $_buttonID The Button DOM ID
     * @param bool $_disableMultipleClicking
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function AddButton($_buttonTitle, $_buttonIcon = '', $_buttonLink = '', $_buttonLinkType = self::LINK_SUBMIT, $_buttonID = '', $_anchorLink = '', $_disableMultipleClicking = true)
    {
        if (empty($_buttonLinkType)) {
            $_buttonLinkType = self::LINK_SUBMIT;
        }

        if (empty($_buttonTitle) && empty($_buttonIcon) && empty($_buttonLink)) {
            $this->_outputContainer .= '<li><span class="toolbarspacer">&nbsp;&nbsp;&nbsp;</span></li>';

            return false;
        }

        if (empty($_buttonTitle)) {
            $_buttonTitle = $this->Language->Get('nolocale');
        }

        $_extendedJavaScript = $_linkID = '';
        switch ($_buttonLinkType) {
            case self::LINK_DEFAULT:
                break;

            case self::LINK_CONFIRM:
                $_extendedJavaScript = 'doConfirm(\'' . addslashes($this->Language->Get('actionconfirm')) . '\', \'' . SWIFT::Get('basename') . $_buttonLink . '\');';
                break;

            case self::LINK_SUBMITCONFIRM:
                $_extendedJavaScript = 'TabLoading(\'' . $this->GetInterface()->_formName . '\', \'' . '%TABID' . '\'); doConfirmForm(\'' . addslashes($this->Language->Get('actionconfirm')) . '\', \'' . $this->GetInterface()->_formName . SWIFT_UserInterface::FORM_SUFFIX . '\');';
                //$_linkID = $this->GetInterface()->_formName . SWIFT_UserInterface::FORM_SUFFIX . '_submit';
                break;

            case self::LINK_SUBMITCONFIRMCUSTOM:
                $_extendedJavaScript = 'TabLoading(\'' . $this->GetInterface()->_formName . '\', \'' . '%TABID' . '\'); doConfirmForm(\'' . $_buttonLink . '\', \'' . $this->GetInterface()->_formName . SWIFT_UserInterface::FORM_SUFFIX . '\');';
                //$_linkID = $this->GetInterface()->_formName . SWIFT_UserInterface::FORM_SUFFIX . '_submit';
                break;

            case self::LINK_VIEWPORT:
                $_extendedJavaScript = 'loadViewportData(\'' . SWIFT::Get('basename') . $_buttonLink . '\');';
                break;

            case self::LINK_SUBMIT:
                $_extendedJavaScript = 'TabLoading(\'' . $this->GetInterface()->_formName . '\', \'' . '%TABID' . '\'); $(\'#' . $this->GetInterface()->_formName . SWIFT_UserInterface::FORM_SUFFIX . '\').submit();';
                $_linkID = $this->GetInterface()->_formName . SWIFT_UserInterface::FORM_SUFFIX . '_submit_' . '%d';
                break;

            case self::LINK_SUBMITTARGET:
                $_extendedJavaScript = '$(\'#' . $this->GetInterface()->_formName . SWIFT_UserInterface::FORM_SUFFIX . '\').submit();';
                $_linkID = $this->GetInterface()->_formName . SWIFT_UserInterface::FORM_SUFFIX . '_submit_' . '%d';
                break;

            case self::LINK_FORM:
                $_extendedJavaScript = 'TabLoading(\'' . $this->GetInterface()->_formName . '\', \'' . '%TABID' . '\'); $(\'#' . $this->GetInterface()->_formName . SWIFT_UserInterface::FORM_SUFFIX . '\').attr(\'action\', \'' . SWIFT::Get('basename') . addslashes($_buttonLink) . '\'); $(\'#' . $this->GetInterface()->_formName . SWIFT_UserInterface::FORM_SUFFIX . '\').submit();';
                $_linkID = $this->GetInterface()->_formName . SWIFT_UserInterface::FORM_SUFFIX . '_submitform_' . '%d';
                break;

            case self::LINK_JAVASCRIPT:
                $_extendedJavaScript = $_buttonLink;
                break;

            default:
                break;
        }

        // if (!empty($_buttonIcon))
        // {
        //     if (substr($_buttonIcon, 0, strlen('http://')) != 'http://' && substr($_buttonIcon, 0, strlen('https://')) != 'https://')
        //     {
        //         $_buttonIcon = SWIFT::Get('themepath') .'images/' . $_buttonIcon;
        //     }
        // }

        if ($_buttonLinkType == self::LINK_NEWWINDOW) {
            $this->_outputContainer .= '<li' . IIF(!empty($_buttonID), ' id="' . $_buttonID . '"') . '><a href="' . $_buttonLink . '" target="_blank">' . IIF(!empty($_buttonIcon), '<i class="fa ' . $_buttonIcon . '" aria-hidden="true"></i> ') . $_buttonTitle . '</a></li>';
        } else {
            $this->_outputContainer .= '<li' . IIF(!empty($_buttonID), ' id="' . $_buttonID . '"') . '><a href="' . IIF(!empty($_anchorLink), '#' . $_anchorLink, 'javascript:void(0);') . '"' . IIF(!empty($_linkID), ' id="' . $_linkID . '"') . ' onclick="javascript:this.blur(); ' . $_extendedJavaScript;
            $this->_outputContainer .= $_disableMultipleClicking ? ' PreventDoubleClicking(this); ' : '';
            $this->_outputContainer .= '">' . IIF(!empty($_buttonIcon), '<i class="fa ' . $_buttonIcon . '" aria-hidden="true"></i> ') . $_buttonTitle . '</a></li>';
        }

        return true;
    }

    /**
     * Render the Toolbar
     *
     * @author Varun Shoor
     * @param bool $_noTable Whether to dispatch the <table> tag or not
     * @return mixed "_outputContainer" (STRING) on Success, "false" otherwise
     */
    public function Render($_noTable = false)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_outputData = '';

        if (empty($this->_outputContainer)) {
            return '';
        }

        if (!$_noTable) {
            $_outputData .= '<table width="100%" border="0" cellspacing="0" cellpadding="0"' . IIF(!empty($this->_toolbarID), ' id="' . $this->_toolbarID . '"', 'id="tabtoolbartable"') . '>';
        }
        $_outputData .= '<tr id="tabtoolbar" class="settabletitlerowmain5"><td align="left" class="settabletitlerowmain5"' . IIF($_noTable, ' colspan="2"') . '>';
        $_outputData .= '<div class="tabtoolbarsub"><ul>' . str_replace('%TABID', $this->GetInterface()->_defaultTabID, str_replace('%d', self::$_buttonIndex, $this->_outputContainer)) . '</ul></div>';
        $_outputData .= '</td></tr>';

        if (!$_noTable) {
            $_outputData .= '</table>';
        }

        self::$_buttonIndex++;


        return $_outputData;
    }
}

?>
