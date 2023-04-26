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
use Base\Models\User\SWIFT_UserOrganization;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;

/**
 * The User Interface Tab Management Class
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_UserInterfaceToolbar $UserInterfaceToolbar
 * @author Varun Shoor
 */
class SWIFT_UserInterfaceTab extends SWIFT_Model
{
    private $_tabTitle;
    private $_tabIcon;
    private $_tabCounter = 0;
    private $_tabID;
    private $_tabURL;
    private $_tabIndex = 0;
    private $_isSelected = false;
    private $_isDisabled = false;
    private $_outputContainer = '';
    private $_SWIFT_UserInterfaceObject = false;
    private $_overflowHeight = false;
    private $_customHTML = '';
    private $_appendHTML = '';
    private $_prependHTML = '';
    private $_hiddenFieldsContainer = array();
    private $_rowContainers = array();
    private $_activeContainer = false;
    private $_defaultPadding = 0;
    private $_columnWidth = '50%';
    private $_javascriptEventFunctionQueue = array();

    public $Toolbar = false;

    private $_rowClass;

    // Core Constants
    const CLASS_ROW1 = 'tablerow1';
    const CLASS_ROW2 = 'tablerow1';
    const TAB_PREFIX = 'tab_';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_tabTitle The Tab Title
     * @param string $_tabIcon The Icon Image Filename
     * @param int $_tabIndex The Zero based Tab Index
     * @param SWIFT_UserInterface $_SWIFT_UserInterfaceObject The SWIFT User Interface Object
     * @param string $_tabID The Tab ID
     * @param bool $_isSelected Whether the tab should be selected
     * @param bool $_isDisabled Whether the Tab is Disabled
     * @param int $_defaultPadding The Default Padding
     * @param string $_tabURL (OPTIONAL) The AJAX Tab URL
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_UserInterface $_SWIFT_UserInterfaceObject, $_tabTitle, $_tabIcon, $_tabIndex, $_tabID = null,
                                $_isSelected = false, $_isDisabled = false, $_defaultPadding = 4, $_tabURL = '')
    {
        parent::__construct();

        if (empty($_tabTitle)) {
            $_tabTitle = $this->Language->Get('nolocale');
        }

        if (!$this->SetTitle($_tabTitle) || !$this->SetInterface($_SWIFT_UserInterfaceObject)) {
            $this->SetIsClassLoaded(false);
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $this->SetIcon($_tabIcon);
        $this->SetID($_tabID);
        $this->SetIsSelected($_isSelected);
        $this->SetTabIndex($_tabIndex);
        $this->SetIsDisabled($_isDisabled);
        $this->SetDefaultPadding($_defaultPadding);
        $this->SetTabURL($_tabURL);
    }

    /**
     * Load the Toolbar object
     *
     * @author Varun Shoor
     * @param string $_toolbarID The Toolbar DOM ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function LoadToolbar($_toolbarID = '')
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Load->Library('UserInterface:UserInterfaceToolbar', array($this->GetInterface(), $_toolbarID),true, false, 'base');

        $this->Toolbar = $this->UserInterfaceToolbar;

        return true;
    }

    /**
     * Set the Tab Title
     *
     * @author Varun Shoor
     * @param string $_tabTitle The Tab Title
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetTitle($_tabTitle)
    {
        if (empty($_tabTitle)) {
            return false;
        }

        $this->_tabTitle = $this->Input->SanitizeForXSS($_tabTitle);

        return true;
    }

    /**
     * Retrieve the Tab Title
     *
     * @author Varun Shoor
     * @return mixed "_tabTitle" (STRING) on Success, "false" otherwise
     */
    public function GetTitle()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_tabTitle;
    }

    /**
     * Set the Tab Index
     *
     * @author Varun Shoor
     * @param int $_tabIndex The Tab Index
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetTabIndex($_tabIndex)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_tabIndex = $_tabIndex;

        return true;
    }

    /**
     * Retrieve the Tab Index
     *
     * @author Varun Shoor
     * @return mixed "_tabIndex" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTabIndex()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_tabIndex;
    }

    /**
     * Set the Column Width
     *
     * @author Varun Shoor
     * @param string $_columnWidth The Column Width
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetColumnWidth($_columnWidth)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_columnWidth = $_columnWidth;

        return true;
    }

    /**
     * Retrieve the Column Width
     *
     * @author Varun Shoor
     * @return mixed "_columnWidth" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetColumnWidth()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_columnWidth;
    }

    /**
     * Set the Default Padding
     *
     * @author Varun Shoor
     * @param int $_defaultPadding The Default Padding
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetDefaultPadding($_defaultPadding = 4)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_defaultPadding = $_defaultPadding;

        return true;
    }

    /**
     * Set the Tab URL
     *
     * @author Varun Shoor
     * @param string $_tabURL The Tab URL
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetTabURL($_tabURL)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_tabURL = $_tabURL;

        return true;
    }

    /**
     * Get the Tab URL
     *
     * @author Varun Shoor
     * @return mixed "_tabURL" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTabURL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_tabURL;
    }

    /**
     * Set Is Disabled Flag
     *
     * @author Varun Shoor
     * @param bool $_isDisabled Whether the Tab is Disabled
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetIsDisabled($_isDisabled)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_isDisabled = $_isDisabled;

        return true;
    }

    /**
     * Get Whether the Tab Is Disabled
     *
     * @author Varun Shoor
     * @return mixed "_isDisabled" (BOOL) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetIsDisabled()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_isDisabled;
    }

    /**
     * Set a custom HTML for this tab contents
     *
     * @author Varun Shoor
     * @param string $_customHTML The Custom HTML
     * @return bool "true" on Success, "false" otherwise
     */
    public function HTML($_customHTML)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->_customHTML = $_customHTML;

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
     * Set a custom Prepended HTML for this tab contents
     *
     * @author Varun Shoor
     * @param string $_prependHTML The Prepended HTML
     * @return bool "true" on Success, "false" otherwise
     */
    public function PrependHTML($_prependHTML)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->_prependHTML .= $_prependHTML;

        return true;
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
     * Set the Tab Icon
     *
     * @author Varun Shoor
     * @param string $_tabIcon The Icon Image Filename
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetIcon($_tabIcon)
    {
        if (empty($_tabIcon)) {
            return false;
        }

        $this->_tabIcon = $_tabIcon;

        return true;
    }

    /**
     * Retrieve the Tab Icon
     *
     * @author Varun Shoor
     * @return mixed "_tabIcon" (STRING) on Success, "false" otherwise
     */
    public function GetIcon()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_tabIcon;
    }

    /**
     * Set the Tab ID
     *
     * @author Varun Shoor
     * @param string $_tabID The Tab ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetID($_tabID)
    {
        if (empty($_tabID)) {
            $_tabID = self::TAB_PREFIX . $this->GetInterface()->GetTabCount();
        }

        if (!$this->GetInterface()->_defaultTabID) {
            $this->GetInterface()->_defaultTabID = $_tabID;
        }

        $this->_tabID = $_tabID;

        return true;
    }

    /**
     * Retrieve the currently set tab id
     *
     * @author Varun Shoor
     * @return mixed "_tabID" (STRING) on Success, "false" otherwise
     */
    public function GetID()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_tabID;
    }

    /**
     * Set whether the tab is selected
     *
     * @author Varun Shoor
     * @param bool $_isSelected Whether the tab should be selected
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetIsSelected($_isSelected)
    {
        $_isSelected = (int)($_isSelected);

        $this->_isSelected = $_isSelected;

        return true;
    }

    /**
     * Retrieve whether the tab should be selected
     *
     * @author Varun Shoor
     * @return bool "_isSelected" (BOOL) on Success, "false" otherwise
     */
    public function GetIsSelected()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_isSelected;
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
    public function Error($_title, $_message, $_divID = '', $_showBorder = true)
    {
        if (!$this->GetIsClassLoaded() || empty($_title) || empty($_message)) {
            return false;
        }

        $this->PrependHTML($this->GetInterface()->GetError($_title, $_message, $_divID, $_showBorder));

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
    public function Alert($_title, $_message, $_divID = '', $_showBorder = true)
    {
        if (!$this->GetIsClassLoaded() || empty($_title) || empty($_message)) {
            return false;
        }

        $this->PrependHTML($this->GetInterface()->GetAlert($_title, $_message, $_divID, $_showBorder));

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
    public function Info($_title, $_message, $_divID = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_title) || empty($_message)) {
            return false;
        }

        $this->PrependHTML($this->GetInterface()->GetInfo($_title, $_message, $_divID));

        return true;
    }

    /**
     * Retrieve the Display HTML
     *
     * @author Varun Shoor
     * @param bool $_extendedDisplay (OPTIONAL) Whether to include the toolbar in output
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDisplayHTML($_extendedDisplay = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_outputData = '';

        if ($_extendedDisplay == true) {
            if ($this->Toolbar instanceof SWIFT_UserInterfaceToolbar && $this->Toolbar->GetIsClassLoaded()) {
                $_outputData .= $this->Toolbar->Render();
            }

            $_outputData .= '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
        }

        $_outputData .= $this->_outputContainer;
        $_outputData .= $this->_appendHTML;

        if ($_extendedDisplay == true) {
            $_outputData .= '</table>';

            if (_is_array($this->_rowContainers) && !$this->_activeContainer) {
                foreach ($this->_rowContainers as $_key => $_val) {
                    $_outputData .= $_val;
                }
            }
        }

        return $_outputData;
    }

    /**
     * Renders the Tab and Returns the Result
     *
     * @author Varun Shoor
     * @return mixed "Processed Tab HTML" (STRING) on Success, "false" otherwise
     */
    public function Render()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if ($this->GetTabURL() != '') {
            return '';
        }

        $_outputData = '<div id="' . $this->GetInterface()->_formName . '_tab_' . $this->GetID() . '" class="ui-tabs">';
//        $_outputData = '<div name="tab" title="'. $this->GetTitle() .'" tabicon="'. $this->GetIcon() .'" tabid="'. $this->GetID() .'"'. IIF($this->GetIsSelected(), ' selected="1"') . '>'.SWIFT_CRLF;
        // Toolbar
        if ($this->Toolbar instanceof SWIFT_UserInterfaceToolbar && $this->Toolbar->GetIsClassLoaded()) {
            $_outputData .= $this->Toolbar->Render();
        } elseif ($this->GetInterface()->Toolbar instanceof SWIFT_UserInterfaceToolbar && $this->GetInterface()->Toolbar->GetIsClassLoaded()) {
            $_outputData .= $this->GetInterface()->Toolbar->Render();
        }

        // Set the Custom HTML Contents if Available...
        if (!empty($this->_customHTML)) {
            $_outputData .= $this->_customHTML;
        } else {
            $_outputData .= $this->_prependHTML;

            if (empty($this->_defaultPadding)) {
                $_defaultSpacing = 0;
            } else {
                $_defaultSpacing = 1;
            }

            $_outputData .= '<table width="100%" border="0" cellspacing="' . $_defaultSpacing . '" cellpadding="' . $this->_defaultPadding . '">' . SWIFT_CRLF;

            $_outputData .= $this->GetDisplayHTML();
            $_outputData .= '</table>';

            if (_is_array($this->_rowContainers) && !$this->_activeContainer) {
                foreach ($this->_rowContainers as $_key => $_val) {
                    $_outputData .= $_val;
                }
            }
        }

        $_outputData .= implode(SWIFT_CRLF, $this->_hiddenFieldsContainer) . '</div>';

        return $_outputData;
    }

    /**
     * Starts a Container
     *
     * @author Varun Shoor
     * @param string $_containerID The Container ID
     * @param bool $_showContainer Whether to show the container or not
     * @param string $_containerPrefixHTML The Prefix HTML
     * @param bool $_showSpacing (OPTIONAL) Whether the table should contain spacing and padding
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function StartContainer($_containerID, $_showContainer = true, $_containerPrefixHTML = '', $_showSpacing = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // An unclosed container? Dont start this one...
        if ($this->_activeContainer) {
            return false;
        }

        $_cellSpacing = '1';
        $_cellPadding = '4';
        if ($_showSpacing == false) {
            $_cellSpacing = '0';
            $_cellPadding = '0';
        }

        $_containerID = Clean($_containerID);

        $this->_rowContainers[$_containerID] = '<div id="' . $_containerID . '"' . IIF(!$_showContainer, ' style="DISPLAY: none;"') . '>' . $_containerPrefixHTML . '<table width="100%" border="0" cellspacing="' . $_cellSpacing . '" cellpadding="' . $_cellPadding . '">';

        $this->_activeContainer = $_containerID;

        return true;
    }


    /**
     * Ends an already started container
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EndContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$this->_activeContainer || !isset($this->_rowContainers[$this->_activeContainer])) {
            return false;
        }

        $this->_rowContainers[$this->_activeContainer] .= '</table></div>';

        $this->_activeContainer = false;

        return true;
    }

    /**
     * Retrieve the alternating row class
     *
     * @author Varun Shoor
     * @return mixed "_rowClass" (STRING) on Success, "false" otherwise
     */
    public function GetClass()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if ($this->_rowClass == self::CLASS_ROW1) {
            $this->_rowClass = self::CLASS_ROW2;
        } else {
            $this->_rowClass = self::CLASS_ROW1;
        }

        return $this->_rowClass;
    }

    /**
     * Set the Overflow Height
     *
     * @author Varun Shoor
     * @param int $_overflowHeight The Overflow Height
     * @return bool "true" on Success, "false" otherwise
     */
    public function Overflow($_overflowHeight)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->_overflowHeight = $_overflowHeight;

        return true;
    }

    /**
     * Stores the output data in a container variable
     *
     * @author Varun Shoor
     * @param string $_outputData The Data to Output
     * @return bool "true" on Success, "false" otherwise
     */
    protected function Output($_outputData)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (isset($this->_activeContainer) && isset($this->_rowContainers[$this->_activeContainer])) {
            $this->_rowContainers[$this->_activeContainer] .= $_outputData . SWIFT_CRLF;
        } else {
            $this->_outputContainer .= $_outputData . SWIFT_CRLF;
        }

        return true;
    }

    /**
     * Retrieve the data in output container
     *
     * @author Varun Shoor
     * @return mixed "_outputContainer" (STRING) on Success, "false" otherwise
     */
    public function GetOutput()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_outputContainer;
    }

    /**
     * Add a Hidden Field
     *
     * @author Varun Shoor
     * @param string $_key The Hidden Field Key
     * @param string $_value The Hidden Field Value
     * @return bool "true" on Success, "false" otherwise
     */
    public function Hidden($_key, $_value)
    {
        if (!$this->GetIsClassLoaded() || empty($_key)) {
            return false;
        }

        $this->_hiddenFieldsContainer[] = '<input type="hidden" name="' . $_key . '" value="' . htmlspecialchars($_value) . '" id="' . $this->GetInterface()->_formName . '_' . $_key . '" />';

        return true;
    }

    /**
     * Print a Row with a list of coluimns
     *
     * @author Varun Shoor
     * @param array $_columnContainer The Column Container Array
     * @param string $_columnClass The Default CSS Class
     * @param string $_onMouseOver The On Mouse Over JavaScript
     * @param string $_onMouseOut The On Mouse Out JavaScript
     * @param string $_onClick The On Click JavaScript
     * @param string $_id The Unique Identifier for this Row
     * @return mixed "_outputContainer" (STRING) on Success, "false" otherwise
     */
    public function Row($_columnContainer, $_columnClass = '', $_id = '', $_onMouseOver = '', $_onMouseOut = '', $_onClick = '')
    {
        if (!$this->GetIsClassLoaded() || !_is_array($_columnContainer)) {
            return false;
        }

        if (empty($_columnClass)) {
            $_columnClass = $this->GetClass();
        }

        $_trClass = '';
        if ($_columnClass == self::CLASS_ROW1 || $_columnClass == self::CLASS_ROW2) {
            $_trClass = $_columnClass . '_tr';
        }

        $_outputContainer = '<tr class="' . $_trClass . '"' . IIF(!empty($_onMouseOver), ' onmouseover="' . $_onMouseOver . '"') . IIF(!empty($_onMouseOut), ' onmouseout="' . $_onMouseOut . '"') . IIF(!empty($_onClick), ' onclick="' . $_onClick . '"') . IIF(!empty($_id), ' id="' . $_id . '"') . '>';

        foreach ($_columnContainer as $_key => $_val) {
            if (!isset($_val['align']) || empty($_val['align'])) {
                $_val['align'] = 'center';
            }

            if (!isset($_val['valign']) || empty($_val['valign'])) {
                $_val['valign'] = 'middle';
            }

            if (!isset($_val['class']) || empty($_val['class'])) {
                $_val['class'] = $_columnClass;
            }

            if (!isset($_val['colspan'])) {
                $_val['colspan'] = '';
            }

            if (!isset($_val['width'])) {
                $_val['width'] = '';
            }

            if (!isset($_val['onmouseover'])) {
                $_val['onmouseover'] = '';
            }

            if (!isset($_val['onmouseout'])) {
                $_val['onmouseout'] = '';
            }

            if (!isset($_val['onclick'])) {
                $_val['onclick'] = '';
            }

            if (!isset($_val['value'])) {
                $_val['value'] = '';
            }

            $_outputContainer .= '<td class="' . $_val['class'] . '" align="' . $_val['align'] . '" valign="' . $_val['valign'] . '"' . IIF(!empty($_val['colspan']), ' colspan="' . $_val['colspan'] . '"') . IIF(!empty($_val['width']), ' width="' . $_val['width'] . '"') . IIF(!empty($_val['onmouseover']), ' onmouseover="' . $_val['onmouseover'] . '"') . IIF(!empty($_val['onmouseout']), ' onmouseout="' . $_val['onmouseout'] . '"') . IIF(!empty($_val['onclick']), ' onclick="' . $_val['onclick'] . '"') . '>' . $_val['value'] . '</td>';
        }

        $_outputContainer .= '</tr>';

        $this->Output($_outputContainer);

        return $_outputContainer;
    }

    /**
     * Add a Row as HTML
     *
     * @author Varun Shoor
     * @param string $_outputContents The Output Contents
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RowHTML($_outputContents)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Output($_outputContents);

        return true;
    }

    /**
     * Print a Two Column Row with Title and Value
     *
     * @author Varun Shoor
     * @param string $_title The Row Title
     * @param string $_value The Row Value
     * @param string $_id The Unique ID
     * @param string $_columnClass The CSS Class
     * @param mixed $_customWidth (OPTIONAL) Custom Width
     * @return bool "true" on Success, "false" otherwise
     */
    public function DefaultRow($_title, $_value, $_id = '', $_columnClass = '', $_customWidth = false)
    {
        if (!$this->GetIsClassLoaded() || empty($_title) || empty($_value)) {
            return false;
        }

        $_columnContainer[0]['align'] = 'left';
        $_columnContainer[0]['valign'] = 'top';
        $_columnContainer[0]['nowrap'] = true;
        $_columnContainer[0]['value'] = $_title;
        $_columnContainer[0]['width'] = IIF(!empty($_customWidth), $_customWidth, $this->GetColumnWidth());
        $_columnContainer[1]['align'] = 'left';
        $_columnContainer[1]['valign'] = 'top';
        $_columnContainer[1]['value'] = $_value;

        return $this->Row($_columnContainer, $_columnClass, $_id);
    }

    /**
     * Print a Default Two Column Row with Title, Description and Value
     *
     * @author Varun Shoor
     * @param string $_title The Row Title
     * @param string $_description The Row Description
     * @param string $_value The Row Value
     * @param string $_id The Unique ID
     * @param string $_columnClass The CSS Class
     * @param string $_width The Column Width
     * @return bool "true" on Success, "false" otherwise
     */
    public function DefaultDescriptionRow($_title, $_description, $_value, $_id = '', $_columnClass = '', $_width = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_title)) {
            return false;
        }

        $_firstColumnData = '<span class="' . 'tabletitle' . '">' . $_title . '</span>' . IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');
        $_secondColumnData = '<span class="tabledescription">' . $_value . '</span>';

        $_columnContainer[0]['align'] = 'left';
        $_columnContainer[0]['valign'] = 'top';
        $_columnContainer[0]['nowrap'] = true;
        $_columnContainer[0]['value'] = $_firstColumnData;
        $_columnContainer[0]['width'] = IIF(!empty($_width), $_width, $this->GetColumnWidth());
        $_columnContainer[1]['align'] = 'left';
        $_columnContainer[1]['valign'] = 'top';
        $_columnContainer[1]['value'] = $_secondColumnData;

        return $this->Row($_columnContainer, $_columnClass, $_id);
    }

    /**
     * Print Four Column Rows
     *
     * @author Varun Shoor
     * @param array $_rowContainer
     * @param bool $_returnHTML Whether to return the HTML rather than outputting it
     * @return bool "true" on Success, "false" otherwise
     */
    public function FourColumnRows($_rowContainer, &$_finalAppendHTML, $_returnHTML = false, $_escapeEntities = true)
    {
        if (!$this->GetIsClassLoaded() || !_is_array($_rowContainer)) {
            return false;
        }

        $_outputHTML = '';

        $_index = 0;

        foreach ($_rowContainer as $_key => $_val) {
            if (is_array($_val)) {
                if ($_key != 'item' && !is_numeric($_key)) {
                    $_finalAppendHTML .= '<tr><td colspan="4" align="left" class="tabletitlerowtitle"><img src="' . SWIFT::Get('themepath') . 'images/icon_doublearrows.gif" align="absmiddle" border="0" /> ' . htmlspecialchars(strtoupper($_key)) . '</td></tr>';
                }

                $this->FourColumnRows($_val, $_finalAppendHTML, true, $_escapeEntities);
            } else {
                if ($_index == 0) {
                    $_outputHTML .= '<tr>';
                }

                $_outputHTML .= '<td><span class="tabletitle">' . htmlspecialchars($_key) . '</span></td><td><span class="tabledescription">' . IIF($_escapeEntities, htmlspecialchars($_val), $_val) . '</span></td>';

                $_index++;

                if ($_index == 2) {
                    $_index = 0;

                    $_outputHTML .= '</tr>';
                }
            }
        }

        if ($_index == 1) {
            $_outputHTML .= '<td>&nbsp;</td><td>&nbsp;</td></tr>';
        }

        if ($_returnHTML) {
            $_finalAppendHTML .= $_outputHTML;

            return true;
        }

        $this->Output($_outputHTML . $_finalAppendHTML);

        $_finalAppendHTML = '';

        return true;
    }

    /**
     * Render a Description Bar
     *
     * @author Varun Shoor
     * @param string $_value The Description String
     * @param string $_icon (OPTIONAL) The Title Bar Icon
     * @param string $_customClass (OPTIONAL) The Custom Class
     * @param int $_columnSpan (OPTIONAL) The Column Span for the Title Bar. Default: 2
     * @param bool $_noEntities (OPTIONAL) Whether to convert to entities
     * @param string $_extraHTML
     * @return bool "true" on Success, "false" otherwise
     */
    public function Description($_value, $_icon = '', $_customClass = '', $_columnSpan = 2, $_noEntities = false, $_extraHTML = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_value)) {
            return false;
        }

        if (empty($_customClass)) {
            $_rowClass = $this->GetClass();
        } else {
            $_rowClass = $_customClass;
        }

        $_columnContainer[0]['align'] = 'left';
        $_columnContainer[0]['valign'] = 'middle';
        $_columnContainer[0]['colspan'] = $_columnSpan;
        $_columnContainer[0]['value'] = IIF(!empty($_icon), '<img src="' . SWIFT::Get('themepath') . 'images/' . $_icon .
                '" align="absmiddle" border="0" /> ') . IIF($_noEntities, $_value, htmlspecialchars($_value));

        $_columnContainer[0]['value'] .= $_extraHTML;

        return $this->Row($_columnContainer, $_rowClass);
    }

    /**
     * Render a Title Bar
     *
     * @author Varun Shoor
     * @param string $_title The Title bar Title String
     * @param string $_icon The Title Bar Icon
     * @param int $_columnSpan The Column Span for the Title Bar. Default: 2
     * @return bool "true" on Success, "false" otherwise
     */
    public function Title($_title, $_icon = '', $_columnSpan = 2)
    {
        if (!$this->GetIsClassLoaded() || empty($_title)) {
            return false;
        }

        $_columnContainer[0]['align'] = 'left';
        $_columnContainer[0]['valign'] = 'middle';
        $_columnContainer[0]['colspan'] = $_columnSpan;
        $_columnContainer[0]['value'] = IIF(!empty($_icon), '<img src="' . SWIFT::Get('themepath') . 'images/' . $_icon . '" align="absmiddle" border="0" /> ') . $_title;

        $this->_rowClass = self::CLASS_ROW2;

        return $this->Row($_columnContainer, 'tabletitlerowtitle');
    }

    /**
     * Prints a Row with One Radio button besides title
     *
     * @author Varun Shoor
     * @param string $_name The List Group Name
     * @param string $_title The Field Title
     * @param string $_value The Field Value
     * @param bool $_isChecked (OPTIONAL) Whether the radio button should be checked
     * @param string $_extendedHTML (OPTIONAL) The Extended HTML to display
     * @return bool "true" on Success, "false" otherwise
     */
    public function RadioList($_name, $_title, $_value, $_isChecked = false, $_extendedHTML = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title)) {
            return false;
        }

        // Override the value if its set in $_POST
        if (isset($_POST[$_name]) && $_POST[$_name] == 1) {
            $_isChecked = true;
        } elseif (isset($_POST[$_name]) && $_POST[$_name] == '0') {
            $_isChecked = false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        $_info = '<span class="tabletitle"><label for="' . $_name . $_value . '">' . '<input type="radio" name="' . $_name . '" class="swiftradio" id="' . $_name . $_value . '" value="' . $_value . '"' . IIF($_isChecked, " checked") . ' /> ' . htmlspecialchars($_title) . '</label></span>' . $_extendedHTML;

        $_columnContainer = array();
        $_columnContainer[0]['align'] = 'left';
        $_columnContainer[0]['valign'] = 'top';
        $_columnContainer[0]['colspan'] = '2';
        $_columnContainer[0]['nowrap'] = true;
        $_columnContainer[0]['value'] = $_info;

        return $this->Row($_columnContainer, $_columnClass, '');
    }

    /**
     * Prints a Row with Two Radio buttons, One Yes and Other No
     *
     * @author Varun Shoor
     * @param string $_name The Yes/No Group Name
     * @param string $_title The Field Title
     * @param string $_description The Field Description
     * @param bool $_isYes Whether Yes Radio button should be checked
     * @param string $_onClick The On Click JavaScript Code
     * @param string $_id The Unique Row ID
     * @param bool $_isDisabled Whether this field is to be disabled
     * @return bool "true" on Success, "false" otherwise
     */
    public function YesNo($_name, $_title, $_description = '', $_isYes = false, $_onClick = '', $_id = '', $_isDisabled = false)
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title)) {
            return false;
        }

        // Override the value if its set in $_POST
        if (isset($_POST[$_name]) && $_POST[$_name] == 1) {
            $_isYes = true;
        } elseif (isset($_POST[$_name]) && $_POST[$_name] == '0') {
            $_isYes = false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        $_outputData = '<label for="y' . $_name . '">' . '<input type="radio" name="' . $_name . '" class="swiftradio" onClick="' . $_onClick . '" id="y' . $_name . '" value="1"' . IIF($_isYes, " checked") . IIF($_isDisabled, ' disabled="disabled"') . ' /> ' . $this->Language->Get('yes') . '</label>' . SWIFT_CRLF;
        $_outputData .= '<label for="n' . $_name . '">' . '<input type="radio" name="' . $_name . '" onClick="' . $_onClick . '" id="n' . $_name . '" value="0"' . IIF(!$_isYes, " checked") . IIF($_isDisabled, ' disabled="disabled"') . ' /> ' . $this->Language->Get('no') . '</label>' . SWIFT_CRLF;

        $_info = '<span class="tabletitle">' . $_title . '</span>';

        $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

        return $this->DefaultRow($_info, $_outputData, $_id, $_columnClass);
    }

    /**
     * Prints a Row with Public & Private Radio Buttons
     *
     * @author Varun Shoor
     * @param string $_name The Yes/No Group Name
     * @param string $_title The Field Title
     * @param string $_description The Field Description
     * @param mixed $_isPublic Whether Is Public Radio button should be checked
     * @return bool "true" on Success, "false" otherwise
     */
    public function PublicPrivate($_name, $_title, $_description, $_isPublic = false, $_onChange = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title)) {
            return false;
        }

        if ($_isPublic === 'public') {
            $_isPublic = true;
        } elseif ($_isPublic === 'private') {
            $_isPublic = false;
        } elseif ($_isPublic === '1') {
            $_isPublic = true;
        } elseif ($_isPublic === '0') {
            $_isPublic = false;
        }


        // Override the value if its set in $_POST
        if (isset($_POST[$_name]) && $_POST[$_name] == 1) {
            $_isPublic = true;
        } elseif (isset($_POST[$_name]) && $_POST[$_name] == '0') {
            $_isPublic = false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        $_outputData = '<label for="public' . $_name . '">' . '<input type="radio" name="' . $_name . '" class="swiftradio"' . IIF(!empty($_onChange), ' onchange="javascript: ' . $_onChange . '"') . ' id="public' . $_name . '" value="1"' . IIF($_isPublic, " checked") . ' /> ' . $this->Language->Get('public') . '</label>' . SWIFT_CRLF;
        $_outputData .= '<label for="private' . $_name . '">' . '<input type="radio" name="' . $_name . '" id="private' . $_name . '"' . IIF(!empty($_onChange), ' onchange="javascript: ' . $_onChange . '"') . ' value="0"' . IIF(!$_isPublic, " checked") . ' /> ' . $this->Language->Get('private') . '</label>' . SWIFT_CRLF;

        $_info = '<span class="tabletitle">' . $_title . '</span>';

        $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

        return $this->DefaultRow($_info, $_outputData, '', $_columnClass);
    }

    /**
     * Prints a Color Selection Row
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_description (OPTIONAL) The Field Description
     * @param string $_defaultColor (OPTIONAL) THe Default HEX Field Color
     * @return bool "true" on Success, "false" otherwise
     */
    public function Color($_name, $_title, $_description, $_defaultColor = '#FFFFFF')
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title)) {
            return false;
        }

        // Override the value if its set in $_POST
        if (isset($_POST[$_name]) && trim($_POST[$_name]) != '') {
            $_defaultColor = $_POST[$_name];
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        $_outputData = '<table width="100%"  border="0" cellspacing="4" cellpadding="0"><tr><td width="50">' . SWIFT_CRLF;

        $_outputData .= '<input type="text" class="swifttext" name="' . $_name . '" id="colorfield_' . $_name . '" size="10" value="' . htmlspecialchars($_defaultColor) . '" />' . SWIFT_CRLF;
        $_outputData .= '</td><td align="left"><table width="50"  border="0" cellpadding="1" cellspacing="0" bgcolor="#333333"><tr><td height="16"><table width="100%"  border="0" cellpadding="0" cellspacing="0"><tr><td id="color_' . $_name . '" bgcolor="' . htmlspecialchars($_defaultColor) . '"><img src="' . SWIFT::Get('themepath') . 'images/space.gif" height="16" /></td></tr></table></td></tr></table></td></tr></table><script type="text/javascript">QueueFunction(function() { $("#colorfield_' . $_name . '").ColorPicker({ onSubmit: function(hsb, hex, rgb, el) { $(el).val("#" + hex); $(el).ColorPickerHide(); }, onChange: function(hsb, hex, rgb) { $("#colorfield_' . $_name . '").val("#" + hex); ChangeColorTable(\'' . $_name . '\', "#" + hex); }, onBeforeShow: function () { $(this).ColorPickerSetColor(this.value); } }).bind(\'keyup\', function(){ $(this).ColorPickerSetColor(this.value); }); });</script>';


        $_info = '<span class="tabletitle">' . $_title . '</span>';

        $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

        return $this->DefaultRow($_info, $_outputData, '', $_columnClass);
    }

    /**
     * Prints a row with single checkbox button
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_description (OPTIONAL) The Field Description
     * @param string $_checkBoxDescription (OPTIONAL) The Description that Appears beside the Checkbox
     * @param string $_defaultValue (OPTIONAL) The Default Value for the Checkbox
     * @param bool $_isChecked (OPTIONAL) Whether the Checkbox is Checked by Default
     * @return bool "true" on Success, "false" otherwise
     */
    public function CheckBox($_name, $_title, $_description = '', $_checkBoxDescription = '', $_defaultValue = '1', $_isChecked = false)
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title)) {
            return false;
        }

        // Override the value if its set in $_POST
        if (isset($_POST[$_name]) && $_POST[$_name] == $_defaultValue) {
            $_isChecked = true;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        $_title = '<label for="' . $_name . '">' . $_title . '</label>';
        $_outputData = '<label for="' . $_name . '">' . '<input type="checkbox" name="' . $_name . '" class="swiftcheckbox" id="' . $_name . '" value="' . $_defaultValue . '"' . IIF($_isChecked, ' checked') . ' />' . $_checkBoxDescription . '</label>' . SWIFT_CRLF;

        $_info = '<span class="tabletitle">' . $_title . '</span>';

        $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

        return $this->DefaultRow($_info, $_outputData, '', $_columnClass);
    }

    /**
     * Prints a List of Checkboxes
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_description (OPTIONAL) The Field Description
     * @param array $_checkBoxContainer The Checkbox Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public function CheckBoxList($_name, $_title, $_description = '', $_checkBoxContainer = array())
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title) || !_is_array($_checkBoxContainer)) {
            return false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        $_outputData = '';

        foreach ($_checkBoxContainer as $_key => $_val) {
            $_outputData .= '<label for="' . $_name . '[' . $_key . ']"><input type="checkbox" id="' . $_name . '[' . $_key . ']" name="' . $_name . '[' . $_key . ']" value="' . $_val['value'] . '"';

            // Override the value if its set in $_POST
            if ((isset($_POST[$_name]) && in_array($_val['value'], $_POST[$_name])) || (!isset($_POST[$_name]) && isset($_val['checked']) && $_val['checked'] == true)) {
                $_outputData .= ' checked';
                $_isChecked = true;
            }

            $_outputData .= ' /> <span class="smalltext">' . $_val['title'] . '</label></span><br /><br />' . SWIFT_CRLF;
        }

        $_info = '<span class="tabletitle">' . $_title . '</span>';

        $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

        return $this->DefaultRow($_info, $_outputData, '', $_columnClass);
    }

    /**
     * Prints a List of Checkboxes in a Container DIV
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_description (OPTIONAL) The Field Description
     * @param array $_checkBoxContainer (OPTIONAL) The Checkbox Container Array
     * @param bool $_extendedWidth (OPTIONAL) The Extended Width
     * @param bool $_allowHTML (OPTIONAL) Whether to allow HTML Tags
     * @param string $_extraHTML
     * @return bool "true" on Success, "false" otherwise
     */
    public function CheckBoxContainerList($_name, $_title, $_description = '', $_checkBoxContainer = array(), $_extendedWidth = false, $_allowHTML = false, $_extraHTML = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title) || !_is_array($_checkBoxContainer)) {
            return false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        $_outputData = '';

        foreach ($_checkBoxContainer as $_key => $_val) {
            $_iconExt = '';
            if (isset($_val['icon']) && !empty($_val['icon'])) {
                $_iconExt = '<img src="' . $_val['icon'] . '" border="0" /> ';
            }
            $_outputData .= '<div><label for="' . $_name . '[' . $_key . ']"><input type="checkbox" id="' . $_name . '[' . $_key . ']" name="' . $_name . '[' . $_key . ']" value="' . $_val['value'] . '"';

            // Override the value if its set in $_POST
            if ((!isset($_POST[$_name]) && isset($_val['checked']) && $_val['checked'] == true) || @in_array($_val['value'], $_POST[$_name])) {
                $_outputData .= ' checked';
                $_isChecked = true;
            }

            $_outputData .= ' /><span onclick="javascript: ToggleSubCheckbox(this);"> ' . $_iconExt . IIF($_allowHTML, $_val['title'], str_replace(' ', '&nbsp;', htmlspecialchars($_val['title']))) . '</span></label></div>' . SWIFT_CRLF;

        }

        $_info = '<span class="tabletitle">' . $_title . '</span>';
        $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

        $_outputData = '<div class="checkboxcontainer" style="' . IIF(!empty($_extendedWidth), 'width: ' . $_extendedWidth . 'px !important;') . '">' . $_outputData . '</div>' . $_extraHTML;

        return $this->DefaultRow($_info, $_outputData, '', $_columnClass);
    }

    /**
     * Print a URL + Upload Row
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_description (OPTIONAL) The Field Description
     * @param string $_defaultValue (OPTIONAL) The Default Field Value
     * @param bool $_isDisabled (OPTIONAL) Whether the fields are disabled by default
     * @return bool "true" on Success, "false" otherwise
     */
    public function URLAndUpload($_name, $_title, $_description = '', $_defaultValue = '', $_isDisabled = false)
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title)) {
            return false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        if (isset($_POST[$_name])) {
            $_defaultValue = $_POST[$_name];
        }

        $_disabledSuffix = '';
        if ($_isDisabled) {
            $_disabledSuffix = ' disabled="disabled"';
        }

        $_fieldClass = 'swifttext';

        $_outputData = '<div style="padding: 3px;">' . $this->Language->Get('fuenterurl') . ' <input type="text" class="' . $_fieldClass . '" name="url_' . $_name . '" id="url_' . $_name . '" value="' . htmlentities($_defaultValue, ENT_COMPAT, $this->Language->Get('charset')) . '" size="30"' . $_disabledSuffix . ' /></div>' . SWIFT_CRLF;
        $_outputData .= '<div style="padding: 3px;">' . $this->Language->Get('fuorupload') . ' <input type="file" class="' . $_fieldClass . '" name="file_' . $_name . '" id="file_' . $_name . '" size="30"' . $_disabledSuffix . ' /></div>' . SWIFT_CRLF;

        $_info = '<span class="tabletitle">' . $_title . '</span>';

        $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

        return $this->DefaultRow($_info, $_outputData, '', $_columnClass);
    }

    /**
     * Print a Text Row
     *
     * @author Varun Shoor
     * @param string|false $_name The Field Name
     * @param string|false $_title The Field Title
     * @param string|false $_description (OPTIONAL) The Field Description
     * @param string $_defaultValue (OPTIONAL) The Default Field Value
     * @param string $_type (OPTIONAL) The Field Type (text/password/file)
     * @param int $_fieldSize (OPTIONAL) The Field Size
     * @param int $_maxLength (OPTIONAL) The Maximum Length of Characters Allowed
     * @param string $_id (OPTIONAL) The Unique Field ID
     * @param string $_customClass (OPTIONAL) The Custom Class for this field
     * @param string $_fieldSuffix (OPTIONAL) The Field Suffix
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Text($_name, $_title, $_description = '', $_defaultValue = '', $_type = 'text', $_fieldSize = 30, $_maxLength = 0, $_id = '', $_customClass = '', $_fieldSuffix = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title)) {
            return false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        if (isset($_POST[$_name])) {
            $_defaultValue = $_POST[$_name];
        }

        if (empty($_customClass)) {
            $_fieldClass = 'swifttext';
        } else {
            $_fieldClass = $_customClass;
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-5067 SMTP server password does not accept %12 in the password
         */
        if ($_type != 'password') {
            $_defaultValue = $this->Input->SanitizeForXSS($_defaultValue);
        }

        $_outputData = '<input type="' . $_type . '" class="' . ($_type == 'password' && empty($_customClass) ? 'swiftpassword' : $_fieldClass) . IIF($_type == 'file', ' swifttextfile') . '" name="' . $_name . '" id="' . $_name . '" value="' . htmlentities($_defaultValue, ENT_COMPAT, $this->Language->Get('charset')) . '" size="' . $_fieldSize . '"' . IIF(!empty($_maxLength), ' maxlength="' . $_maxLength . '"') . ' />' . $_fieldSuffix . SWIFT_CRLF;

        $_info = '<span class="tabletitle">' . $_title . '</span>';

        $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

        return $this->DefaultRow($_info, $_outputData, $_id, $_columnClass);
    }

    /**
     * Print a Text Row (with Auto Complete Functionality)
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_autoCompleteURL The Auto Complete URL
     * @param string $_description (OPTIONAL) The Field Description
     * @param string $_defaultValue (OPTIONAL) The Default Field Value
     * @param string $_defaultAutoCompleteValue (OPTIONAL) The Default AutoComplete Hidden Field Value
     * @param string $_autoCompleteIcon (OPTIONAL) The Icon to Display Beside AutoComplete Items
     * @param int $_fieldSize (OPTIONAL) The Field Size
     * @param int $_maxLength (OPTIONAL) The Maximum Length of Characters Allowed
     * @param string $_extendedHTML (OPTIONAL) The Extended HTML to Appear towards right of the field
     * @return bool "true" on Success, "false" otherwise
     */
    public function TextAutoComplete($_name, $_title, $_autoCompleteURL, $_description = '', $_defaultValue = '', $_defaultAutoCompleteValue = '', $_autoCompleteIcon = '', $_fieldSize = 30, $_maxLength = 0, $_extendedHTML = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title)) {
            return false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        if (isset($_POST[$_name])) {
            $_defaultValue = $_POST[$_name];
        }

        if (isset($_POST['autocomplete_' . $_name])) {
            $_defaultAutoCompleteValue = $_POST['autocomplete_' . $_name];
        }

        $_outputData = '<input type="text" class="swifttextautocompletelookup" name="' . $_name . '" id="' . $_name . '" value="' . htmlentities($_defaultValue, ENT_COMPAT, $this->Language->Get('charset')) . '" size="' . $_fieldSize . '"' . IIF(!empty($_maxLength), ' maxlength="' . $_maxLength . '"') . ' />' . '<input type="hidden" id="autocomplete_' . $_name . '" name="autocomplete_' . $_name . '" value="' . htmlentities($_defaultAutoCompleteValue, ENT_COMPAT, $this->Language->Get('charset')) . '" /> ' . $_extendedHTML . '<script type="text/javascript">QueueFunction(function() { UIAutoCompleteControl(\'' . addslashes($_name) . '\', \'' . $_autoCompleteURL . '\', \'' . $_autoCompleteIcon . '\') });</script>' . SWIFT_CRLF;

        $_info = '<span class="tabletitle">' . $_title . '</span>';

        $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

        return $this->DefaultRow($_info, $_outputData, '', $_columnClass);
    }

    /**
     * Print a Text Row Auto Complete
     *
     * @author Varun Shoor
     * @param string|false $_name The Field Name
     * @param string|false $_title The Field Title
     * @param string|false $_description (OPTIONAL) The Field Description
     * @param string|false $_jsonAutoCompleteURL (OPTIONAL) The Auto Complete URL with JSON Data
     * @param array $_defaultValueContainer (OPTIONAL) The Default Field Value Container
     * @param string|false $_icon (OPTIONAL) The Icon for this Input
     * @param string|false $_class (OPTIONAL) The Custom Class for this Input
     * @param bool $_displayDefaultLabel (OPTIONAL) Whether to display default label
     * @param int $_colSpan (OPTIONAL) The col span value
     * @param bool $_customWidth (OPTIONAL) The Custom Width Value
     * @param bool $_ignorePost (OPTIONAL)
     * @param bool $_hasCheckboxes (OPTIONAL)
     * @param string $_suffixedHTML (OPTIONAL)
     * @param array $_customAttrContainer (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function TextMultipleAutoComplete($_name, $_title, $_description = '', $_jsonAutoCompleteURL = '', $_defaultValueContainer = array(), $_icon = '', $_class = '',
                                             $_displayDefaultLabel = false, $_colSpan = 2, $_customWidth = false, $_ignorePost = false, $_hasCheckboxes = false, $_suffixedHTML = '', $_customAttrContainer = array())
    {
        if (!$this->GetIsClassLoaded() || empty($_name)) {
            return false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        } elseif ($_class) {
            $_columnClass = $_class;
        }

        if (isset($_POST['containertaginput_' . $_name]) && is_array($_POST['containertaginput_' . $_name]) && !$_ignorePost) {
            $_defaultValueContainer = $_POST['containertaginput_' . $_name];
        }

        $_defaultValue = '';
        if (isset($_POST['taginput_' . $_name])) {
            $_defaultValue = $this->Input->Post('taginput_' . $_name);
        } elseif ($_displayDefaultLabel) {
            $_defaultValue = $this->Language->Get('starttypingtags');
        }

        if (!$_icon) {
            $_finalIcon = 'fa-tags';
        } else {
            $_finalIcon = $_icon;
        }
        /**
         * Feature : Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT: 4759 Add a toggle button to deselect/select all CC recipeints on ticket replies
         *
         * Comments: Adding additional checkbox to Select/DeSelect All
         */
        $_outputData = '<div class="swifttextautocompletediv" style="BACKGROUND: #FFFFFF;"><i class="fa ' . $_finalIcon . '" aria-hidden="true"></i><ul class="swifttextautocomplete" jsonurl="' . htmlspecialchars($_jsonAutoCompleteURL) . '" id="tagcontainer_' . addslashes($_name) . '">';
        $_outputData .= '<label ' . IIF((count($_defaultValueContainer) > 0 && strpos($_name, 'replycc') === 0), '', 'style="display:none;"') . ' class="toggle-check toggle-all">All <input type="checkbox" checked="checked" class="parent-checkbox"></label>';

        if (_is_array($_defaultValueContainer)) {
            foreach ($_defaultValueContainer as $_key => $_val) {
                /*
                 * BUG FIX - Ravi Sharma
                 *
                 * SWIFT-1930 Email field strips off apostrophe (') from email address when a user registers from Client Support Center
                 *
                 * Comments: None
                 */
                if (IsEmailValid($_val)) {
                    $_val = CleanEmail($_val);
                } else {
                    $_val = CleanTag($_val, SWIFT_UserOrganization::ALLOWED_CHARACTERS);
                }

                $_checkboxHTML = '';
                if ($_hasCheckboxes == true) {
                    $_checkHash = md5('taginputcheck_' . $_name . $_val);
                    $_checkboxHTML = '<input type="hidden" name="' . $_checkHash . '" value="1" /><input type="checkbox" class="child-checkbox" name="taginputcheck_' . $_name . '[]" value="' . $_val . '" checked="checked" /> ';
                }

                $_outputData .= '<li class="swifttextautocompleteinputcontainer swifttextautocompleteitem" tagid="' . $_val . '">' . $_checkboxHTML . $_val . str_replace('%tagid', $_key, str_replace('%tagvalue', $_val, $_suffixedHTML)) . '<div class="swifttextautocompleteitemclose"><i class="fa fa-times-circle" aria-hidden="true"></i></div><input type="hidden" name="containertaginput_' . $_name . '[]" value="' . $_val . '" /></li>';
            }
        }

        // Add custom attributes
        $_customAttrs = '';
        foreach ($_customAttrContainer as $_attrName => $_attrvalue) {
            $_customAttrs .= $_attrName . ' = "' . $_attrvalue . '"';
        }

        $_outputData .= '<li class="swifttextautocompleteinputcontainer"><input type="text" class="swifttextautocompleteinput" name="taginput_' . $_name . '" id="taginput_' . $_name . '" value="' . $_defaultValue . '" autocomplete="off" size="30" ' . $_customAttrs . ' /></li>';

        $_outputData .= '</ul></div>';
        $_outputData .= '<script type="text/javascript">if (window.$UIObject) { window.$UIObject.Queue(function(){ UITagControl(\'' . addslashes($_name) . '\', \'' . addslashes($_suffixedHTML) . '\') }); }</script>';

        if (empty($_title)) {
            $_columnContainer = array();
            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[0]['valign'] = 'top';
            $_columnContainer[0]['value'] = $_outputData;

            if (!empty($_colSpan)) {
                $_columnContainer[0]['colspan'] = $_colSpan;
            }

            return $this->Row($_columnContainer, $_columnClass, '');
        } else {
            $_info = '<span class="tabletitle">' . $_title . '</span>';
            $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');
            return $this->DefaultRow($_info, $_outputData, '', $_columnClass, $_customWidth);
        }
    }

    /**
     * Extended version of SWIFT_UserInterfaceTab::TextMultiplaAutoComplete()
     * $_defaultValueContainer is now an associative array with the following structure
     * $_defaultValueContainer = array(
     *    array('key'        =>    autocomplete_key,
     *        'value    '    =>    autocomplete_value,
     *        'checked'    =>    checked or not
     *    ));
     *
     * @author Abhinav Kumar <abhinav.kumar@kayako.com>
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_description (OPTIONAL) The Field Description
     * @param string $_jsonAutoCompleteURL (OPTIONAL) The Auto Complete URL with JSON Data
     * @param array $_defaultValueContainer (OPTIONAL) The Default Field Value Container
     * @param string $_icon (OPTIONAL) The Icon for this Input
     * @param string $_class (OPTIONAL) The Custom Class for this Input
     * @param bool $_displayDefaultLabel (OPTIONAL) Whether to display default label
     * @param int $_colSpan (OPTIONAL) The col span value
     * @param bool $_customWidth (OPTIONAL) The Custom Width Value
     * @param bool $_ignorePost (OPTIONAL)
     * @param bool $_hasCheckboxes (OPTIONAL)
     * @param string $_suffixedHTML (OPTIONAL)
     * @param array $_customAttrContainer (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     */
    public function TextMultipleAutoCompleteExtended($_name, $_title, $_description = '', $_jsonAutoCompleteURL = '', $_defaultValueContainer = array(), $_icon = '', $_class = '',
                                                     $_displayDefaultLabel = false, $_colSpan = 2, $_customWidth = false, $_ignorePost = false, $_hasCheckboxes = false, $_suffixedHTML = '', $_customAttrContainer = array())
    {
        if (!$this->GetIsClassLoaded() || empty($_name)) {
            return false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        } elseif ($_class) {
            $_columnClass = $_class;
        }

        if (isset($_POST['containertaginput_' . $_name]) && is_array($_POST['containertaginput_' . $_name]) && !$_ignorePost) {
            $_defaultValueContainer = $this->Input->Post('containertaginput_' . $_name);
        }

        $_defaultValue = '';
        if (isset($_POST['taginput_' . $_name])) {
            $_defaultValue = $_POST['taginput_' . $_name];
        } elseif ($_displayDefaultLabel) {
            $_defaultValue = $this->Language->Get('starttypingtags');
        }

        if (!$_icon) {
            $_finalIcon = 'fa-tags';
        } else {
            $_finalIcon = $_icon;
        }

        $_outputData = '<label class="toggle-forward toggle-all" ' . IIF(count($_defaultValueContainer) == 0, 'style="display:none;"', '') . '>All <input class="parent-checkbox" id="toggle-forward" type="checkbox"></label>';
        $_outputData .= '<div class="swifttextautocompletediv" style="BACKGROUND: #FFFFFF;"><i class="fa ' . $_finalIcon . '" aria-hidden="true"></i><ul class="swifttextautocomplete" jsonurl="' . htmlspecialchars($_jsonAutoCompleteURL) . '" id="tagcontainer_' . addslashes($_name) . '">';

        if (_is_array($_defaultValueContainer)) {
            //foreach ($_defaultValueContainer as $_key => $_val)
            foreach ($_defaultValueContainer as $_defaultValueArray) {
                if (!_is_array($_defaultValueArray)
                    || !array_key_exists('key', $_defaultValueArray)
                    || !array_key_exists('value', $_defaultValueArray)) {
                    continue;

                }
                $_key = CleanTag($_defaultValueArray['key'], TRUE);

                /*
                 * BUG FIX - Ravi Sharma
                 *
                 * SWIFT-1930 Email field strips off apostrophe (') from email address when a user registers from Client Support Center
                 *
                 * Comments: None
                 */
                if (IsEmailValid($_defaultValueArray['value'])) {
                    $_val = CleanEmail($_defaultValueArray['value']);
                } else {
                    $_val = CleanTag($_defaultValueArray['value'], true);
                }

                if (array_key_exists('checked', $_defaultValueArray) && $_defaultValueArray['checked'] == 'checked') {
                    $_checked = 'checked = "checked"';
                } else {
                    $_checked = '';
                }

                $_checkboxHTML = '';
                if ($_hasCheckboxes == true) {
                    $_checkHash = md5('taginputcheck_' . $_name . $_val);
                    $_checkboxHTML = '<input type="hidden" name="' . $_checkHash . '" value="1" /><input class="child-checkbox" type="checkbox" name="taginputcheck_' . $_name . '[]" value="' . $_val . '" ' . $_checked . ' /> ';
                }

                $_outputData .= '<li class="swifttextautocompleteinputcontainer swifttextautocompleteitem" tagid="' . $_val . '">' . $_checkboxHTML . $_val . str_replace('%tagid', $_key, str_replace('%tagvalue', $_val, $_suffixedHTML)) . '<div class="swifttextautocompleteitemclose"><i class="fa fa-times-circle" aria-hidden="true"></i></div><input type="hidden" name="containertaginput_' . $_name . '[]" value="' . $_val . '" /></li>';
            }
        }

        // Add custom attributes
        $_customAttrs = '';
        foreach ($_customAttrContainer as $_attrName => $_attrvalue) {
            $_customAttrs .= $_attrName . ' = "' . $_attrvalue . '"';
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4767 Auto-sugesstion is not working for CC option under the Forward tab.
         *
         * Comments: Removing redundant list.
         */
        $_outputData .= '<li class="swifttextautocompleteinputcontainer"><input type="text" class="swifttextautocompleteinput" name="taginput_' . $_name . '" id="taginput_' . $_name . '" value="' . $_defaultValue . '" autocomplete="off" size="30" ' . $_customAttrs . ' /></li>';
        $_outputData .= '</ul></div>';
        $_outputData .= '<script type="text/javascript">if (window.$UIObject) { window.$UIObject.Queue(function(){ UITagControl(\'' . addslashes($_name) . '\', \'' . addslashes($_suffixedHTML) . '\') }); }</script>';

        if (empty($_title)) {
            $_columnContainer = array();
            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[0]['valign'] = 'top';
            $_columnContainer[0]['value'] = $_outputData;

            if (!empty($_colSpan)) {
                $_columnContainer[0]['colspan'] = $_colSpan;
            }

            return $this->Row($_columnContainer, $_columnClass, '');
        } else {
            $_info = '<span class="tabletitle">' . $_title . '</span>';
            $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');
            return $this->DefaultRow($_info, $_outputData, '', $_columnClass, $_customWidth);
        }
    }

    /**
     * Print a Number Row
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_description (OPTIONAL) The Field Description
     * @param string $_defaultValue (OPTIONAL) The Default Field Value
     * @return bool "true" on Success, "false" otherwise
     */
    public function Number($_name, $_title, $_description = '', $_defaultValue = '')
    {
        return $this->Text($_name, $_title, $_description, $_defaultValue, 'text', 10, 0, '', 'swifttextnumeric');
    }

    /**
     * Print a File Upload Row
     *
     * @author Varun Shoor
     * @param string|false $_name The Field Name
     * @param string|false $_title The Field Title
     * @param string|false $_description (OPTIONAL) The Field Description
     * @param int $_fieldSize (OPTIONAL) The Field Size
     * @param string $_fieldSuffix (OPTIONAL) The Field Suffix
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function File($_name, $_title, $_description = '', $_fieldSize = 25, $_fieldSuffix = '')
    {
        return $this->Text($_name, $_title, $_description, '', 'file', $_fieldSize, 0, '', '', $_fieldSuffix);
    }

    /**
     * Print a Password Row
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_description (OPTIONAL) The Field Description
     * @param string $_defaultValue (OPTIONAL) The Default Field Value
     * @param bool $_allowReveal (OPTIONAL)
     * @param string $_extraHTML (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Password($_name, $_title, $_description = '', $_defaultValue = '', $_allowReveal = false, $_extraHTML = '')
    {
        $_revealHTML = '';

        if ($_allowReveal == true) {
            $_revealHTML .= '<div style="display: inline;" class="tabletitle" id="prevealcontainer_' . $_name . '"></div> <a href="javascript: void(0);" onclick="javascript: RevealPasswordField(\'' . $_name . '\');" id="preveal_' . $_name . '">' . $this->Language->Get('pfieldreveal') . '</a>';
        }

        $_revealHTML .= $_extraHTML;

        return $this->Text($_name, $_title, $_description, $_defaultValue, 'password', 20, 0, '', '', $_revealHTML);
    }

    /**
     * Prints a calendar select row, only call this function for fields which will require just date
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_description (OPTIONAL) The Field Description
     * @param string $_defaultValue (OPTIONAL) The Default Field Value
     * @param int $_timeStamp (OPTIONAL) The Timestamp Value
     * @param bool $_displayTime (OPTIONAL) Display the Time Selector
     * @param bool $_disableClearing (OPTIONAL) If enabled, the clearing image wont be displayed
     * @param string $_onChange (OPTIONAL) The JS OnChange event for all fields
     * @param string $_extraHTML
     * @param bool $_disablePastDates
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Date($_name, $_title, $_description = '', $_defaultValue = '', $_timeStamp = 0, $_displayTime = false, $_disableClearing = false, $_onChange = '', $_extraHTML = '', $_disablePastDates = false)
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title)) {
            return false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        if (isset($_POST[$_name])) {
            $_defaultValue = $this->Input->Post($_name);
        }

        if (empty($_defaultValue)) {
            $_defaultValue = '';
        }

        $_onChangeJS = '';
        if (!empty($_onChange)) {
            $_onChangeJS = ' onchange="javascript: ' . $_onChange . '"';
        }

        $_timeSelectorHTML = '';
        if ($_displayTime == true) {
            $_timeSelectorHTML = '<td align="left" valign="top">';

            // US 12 Hour
            if ($this->Settings->Get('dt_caltype') == 'us') {

                $_timeSelectorHTML .= '<select name="' . $_name . '_hour" id="' . $_name . '_hour"' . $_onChangeJS . ' class="swiftselect">';
                for ($index = 1; $index <= 12; $index++) {

                    $_displayValue = $index;
                    if ($index < 10) {
                        $_displayValue = '0' . $index;
                    }

                    $_timeSelectorHTML .= '<option value="' . $index . '"' . IIF(date('h', $_timeStamp) == $index, ' selected') . '>' . $_displayValue . '</option>';
                }
                $_timeSelectorHTML .= '</select>';

                $_timeSelectorHTML .= '<select name="' . $_name . '_minute" id="' . $_name . '_minute"' . $_onChangeJS . ' class="swiftselect">';
                for ($index = 0; $index <= 59; $index++) {
                    $_displayValue = $index;
                    if ($index < 10) {
                        $_displayValue = '0' . $index;
                    }

                    $_timeSelectorHTML .= '<option value="' . $index . '"' . IIF(date('i', $_timeStamp) == $index, ' selected') . '>' . $_displayValue . '</option>';
                }
                $_timeSelectorHTML .= '</select>';

                $_timeSelectorHTML .= '<select name="' . $_name . '_meridian" id="' . $_name . '_meridian"' . $_onChangeJS . ' class="swiftselect">';
                $_timeSelectorHTML .= '<option value="am"' . IIF(date('a', $_timeStamp) == 'am', ' selected') . '>' . $this->Language->Get('am') . '</option>';
                $_timeSelectorHTML .= '<option value="pm"' . IIF(date('a', $_timeStamp) == 'pm', ' selected') . '>' . $this->Language->Get('pm') . '</option>';
                $_timeSelectorHTML .= '</select>';

                // European 24 Hour
            } elseif ($this->Settings->Get('dt_caltype') == 'eu') {

                $_timeSelectorHTML .= '<select name="' . $_name . '_hour" id="' . $_name . '_hour"' . $_onChangeJS . ' class="swiftselect">';
                for ($index = 0; $index <= 23; $index++) {
                    $_displayValue = $index;
                    if ($index < 10) {
                        $_displayValue = '0' . $index;
                    }

                    $_timeSelectorHTML .= '<option value="' . $index . '"' . IIF(date('H', $_timeStamp) == $index, ' selected') . '>' . $_displayValue . '</option>';
                }
                $_timeSelectorHTML .= '</select>';

                $_timeSelectorHTML .= '<select name="' . $_name . '_minute" id="' . $_name . '_minute"' . $_onChangeJS . ' class="swiftselect">';
                for ($index = 0; $index <= 59; $index++) {
                    $_displayValue = $index;
                    if ($index < 10) {
                        $_displayValue = '0' . $index;
                    }

                    $_timeSelectorHTML .= '<option value="' . $index . '"' . IIF(date('i', $_timeStamp) == $index, ' selected') . '>' . $_displayValue . '</option>';
                }
                $_timeSelectorHTML .= '</select>';

            }
            $_timeSelectorHTML .= '</td>';
        }

        if ($_disableClearing == false) {
            $_timeSelectorHTML .= '<td align="left" valign="middle"><div><a href="javascript: void(0);" onclick="javascript: ClearDateField(\'' . $_name . '\');"><i style="font-size:21px !important;" class="fa fa-calendar-times-o" aria-hidden="true"></i></a></div></td>';
        }

        $_outputData = '<table border="0" cellpadding="0" cellspacing="0"><tr><td><input type="text" name="' . $_name . '" id="' . $_name . '"' . $_onChangeJS . ' size="12" value="' . $_defaultValue . '" class="swifttext"/></td><td width="2"><img src="' . SWIFT::Get('themepath') . 'images/space.gif" width="2" border="0"/></td>' . $_timeSelectorHTML . '</tr></table><script type="text/javascript">if (window.$UIObject) { window.$UIObject.Queue(function(){ '.($_disablePastDates?'datePickerDefaults.minDate=0;':'').'$("#' . $_name . '").datepicker(datePickerDefaults); }); }</script>';

        // TODO: Check where dotime is being used

        $_info = '<span class="tabletitle">' . $_title . '</span>';
        $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

        $_outputData .= $_extraHTML;

        return $this->DefaultRow($_info, $_outputData, '', $_columnClass);
    }

    /**
     * Prints a Notes Row
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_defaultValue (OPTIONAL) The Default Field Value
     * @param int $_noteColor (OPTIONAL) The Optional Custom Note Color
     * @return bool "true" on Success, "false" otherwise
     */
    public function Notes($_name, $_title, $_defaultValue = '', $_noteColor = 1)
    {
        if (!$this->GetIsClassLoaded() || empty($_name)) {
            return false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        if (isset($_POST[$_name])) {
            $_defaultValue = $_POST[$_name];
        }

        $this->Title($_title . '&nbsp;&nbsp;&nbsp;<a href="javascript: void(0);" onclick="javascript: UISwitchNote(\'' . $_name . '\', \'1\');"><img src="' . SWIFT::Get('themepath') . 'images/icon_note1.gif" align="absmiddle" border="0" /></a> <a href="javascript: void(0);" onclick="javascript: UISwitchNote(\'' . $_name . '\', \'2\');"><img src="' . SWIFT::Get('themepath') . 'images/icon_note2.gif" align="absmiddle" border="0" /></a> <a href="javascript: void(0);" onclick="javascript: UISwitchNote(\'' . $_name . '\', \'3\');"><img src="' . SWIFT::Get('themepath') . 'images/icon_note3.gif" align="absmiddle" border="0" /></a> <a href="javascript: void(0);" onclick="javascript: UISwitchNote(\'' . $_name . '\', \'4\');"><img src="' . SWIFT::Get('themepath') . 'images/icon_note4.gif" align="absmiddle" border="0" /></a> <a href="javascript: void(0);" onclick="javascript: UISwitchNote(\'' . $_name . '\', \'5\');"><img src="' . SWIFT::Get('themepath') . 'images/icon_note5.gif" align="absmiddle" border="0" /></a>', 'icon_doublearrows.gif');

        $_outputData = '<textarea id="' . $_name . '" style="WIDTH: 99%;" class="swifttextareanotes' . $_noteColor . '" name="' . $_name . '" cols="' . '30' . '" rows="' . '6' . '">' . htmlspecialchars($_defaultValue) . '</textarea><input type="hidden" id="notecolor_' . $_name . '" name="notecolor_' . $_name . '" value="' . $_noteColor . '" />' . SWIFT_CRLF;

        $_columnContainer = array();
        $_columnContainer[0]['align'] = 'left';
        $_columnContainer[0]['valign'] = 'top';
        $_columnContainer[0]['value'] = $_outputData;
//        $_columnContainer[0]['width'] = '100%';
        $_columnContainer[0]['colspan'] = '2';

        return $this->Row($_columnContainer, $_columnClass);
    }

    /**
     * Prints a HTML Editor Row
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_defaultValue (OPTIONAL) The Default Field Value
     * @return bool "true" on Success, "false" otherwise
     */
    public function HTMLEditor($_name, $_defaultValue = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_name)) {
            return false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        if (isset($_POST[$_name])) {
            $_defaultValue = $_POST[$_name];
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-5066 Need to use the same image upload option on KB's / News as the new staff reply editor
         */
        $_SWIFT = SWIFT::GetInstance();

        echo '<script type="text/javascript" src="' . $_SWIFT->Settings->Get('general_producturl') . '__swift/apps/base/javascript/__global/thirdparty/TinyMCE/tinymce.min.js"/><script>tinyMCE.baseURL = "' . $_SWIFT->Settings->Get('general_producturl') . '__swift/apps/base/javascript/__global/thirdparty/TinyMCE/";</script>';

        $this->EventQueue('show', GetTinyMceCode('textarea#' . $_name, $_name, $_name . '_htmlcontents'));

        $_outputData = '<textarea id="' . $_name . '" style="WIDTH: 98%;" class="swifttextarea" name="' . $_name . '" cols="30" rows="25">' . htmlspecialchars($_defaultValue) . '</textarea><textarea id="' . $_name . '_htmlcontents" name="' . $_name . '_htmlcontents" style="display: none;">' . htmlspecialchars($_defaultValue) . '</textarea>' . SWIFT_CRLF;

        $_columnContainer = array();
        $_columnContainer[0]['align'] = 'left';
        $_columnContainer[0]['valign'] = 'top';
        $_columnContainer[0]['value'] = $_outputData;
        $_columnContainer[0]['colspan'] = '2';

        return $this->Row($_columnContainer, $_columnClass);
    }

    /**
     * Prints a Text Area Row
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_description (OPTIONAL) The Field Description
     * @param string $_defaultValue (OPTIONAL) The Default Field Value
     * @param int $_columnWidth (OPTIONAL) The Text Area Column Width
     * @param int $_rowCount (OPTIONAL) The Text Area Row Count
     * @param bool $_isDisabled (OPTIONAL) Whether the Text Area is Disabled
     * @param string $_id (OPTIONAL) The Unique Text Area Identifier
     * @param string $_extendedHTML (OPTIONAL) The Extended HTML Data
     * @param string $_className (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     */
    public function TextArea($_name, $_title, $_description = '', $_defaultValue = '', $_columnWidth = 30, $_rowCount = 3, $_isDisabled = false, $_id = '', $_extendedHTML = '', $_className = 'swifttextarea')
    {
        if (!$this->GetIsClassLoaded() || empty($_name)) {
            return false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        } elseif ($_className == 'swifttextareaconsole') {
            $_columnClass = 'tablerowbase_tr';
        }

        if (isset($_POST[$_name]) && !empty($_POST[$_name])) {
            $_defaultValue = $_POST[$_name];
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-486 The ticket replies editor scrolls the unnecessary text while editing the text, by placing the cursor.
         *
         * Comments: None
         */
        $_textAreaDynamicHeight = (int)($_rowCount * (137 / 7));
        $_outputData = '<textarea id="' . $_name . '" style="height: ' . $_textAreaDynamicHeight . 'px; width: 200px; min-width: 99%; max-width: 99%;" class="' . $_className . '" name="' . $_name . '" cols="' . $_columnWidth . '" rows="' . $_rowCount . '"' . IIF($_isDisabled, " disabled") . '>' . htmlspecialchars($_defaultValue) . '</textarea>' . SWIFT_CRLF . $_extendedHTML;

        if (empty($_title)) {
            $_columnContainer = array();
            $_columnContainer[0]['align'] = 'left';
            $_columnContainer[0]['valign'] = 'top';
            $_columnContainer[0]['value'] = $_outputData;
//            $_columnContainer[0]['width'] = '100%';
            $_columnContainer[0]['colspan'] = '2';

            return $this->Row($_columnContainer, $_columnClass, $_id);
        } else {
            $_info = '<span class="tabletitle">' . $_title . '</span>';
            $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

            return $this->DefaultRow($_info, $_outputData, $_id, $_columnClass);
        }
    }

    /**
     * Prints a List of Radio Buttons
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_description (OPTIONAL) The Field Description
     * @param array $_radioContainer The Radio Container Array
     * @param bool $_splitIntoRows (OPTIONAL) Whether to Split the Radio Buttons into Separate Lines
     * @param string $_onClickHandler (OPTIONAL) JS On Click Handler
     * @param string $_extraHTML
     * @return bool "true" on Success, "false" otherwise
     */
    public function Radio($_name, $_title, $_description = '', $_radioContainer = array(), $_splitIntoRows = true, $_onClickHandler = '', $_extraHTML = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title) || !_is_array($_radioContainer)) {
            return false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        $_outputData = '';
        foreach ($_radioContainer as $_key => $_val) {
            $_outputData .= '<label for="' . $_name . '[' . $_key . ']"><input type="radio" onclick="' . $_onClickHandler . '" id="' . $_name . '[' . $_key . ']" name="' . $_name . '" value="' . $_val['value'] . '"';

            if ((isset($_POST[$_name]) && $_val['value'] == $_POST[$_name]) || (!isset($_POST[$_name]) && isset($_val['checked']) && $_val['checked'] == true)) {
                $_outputData .= ' checked';
            }

            $_outputData .= ' /> <span class="smalltext">' . $_val['title'] . '</label></span>' . IIF($_splitIntoRows, '<BR /><BR />', '&nbsp;&nbsp;') . SWIFT_CRLF;
        }

        $_info = '<span class="tabletitle">' . $_title . '</span>';
        $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

        $_outputData .= $_extraHTML;

        return $this->DefaultRow($_info, $_outputData, '', $_columnClass);
    }

    /**
     * Print a Select Row that is Linked
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_description (OPTIONAL) The Field Description
     * @param array $_optionsContainer The Options Container Array
     * @param string $_extendedData (OPTIONAL) The Extended HTML Data
     * @param mixed $_customWidth (OPTIONAL) Specify Custom Width for First Column
     * @param string $_extraHTML
     * @return bool "true" on Success, "false" otherwise
     */
    public function SelectLinked($_name, $_title, $_description = '', $_optionsContainer = array(), $_extendedData = '', $_customWidth = false, $_extraHTML = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title) || !_is_array($_optionsContainer)) {
            return false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        $_optionSelected = false;

        $_parentOptionContainer = array();
        foreach ($_optionsContainer as $_option) {
            if (!empty($_option['parent'])) {
                $_parentOptionContainer[$_option['parent']][] = $_option;
            }
        }

        // First render the parent options
        $_selectedParentOption = false;
        $_outputData = '<select name="' . $_name . '[0]" id="select' . $_name . '" class="swiftselect" onChange="javascript: LinkedSelectChanged(this, \'' . $_name . '\');">';
        foreach ($_optionsContainer as $_val) {
            if (!empty($_val['parent'])) {
                continue;
            }

            $_iconData = '';
            if (isset($_val['icon']) && !empty($_val['icon'])) {
                $_iconData = ' style="padding-left: 18px; background: url(' . $_val['icon'] . ') no-repeat; HEIGHT: 15px; PADDING-TOP: 3px;"';
            }

            $_outputData .= '<option' . $_iconData . ' value="' . $_val['value'] . '"';
            if (((isset($_POST[$_name][0]) && $_POST[$_name][0] == $_val['value']) || (!isset($_POST[$_name][0]) && isset($_val['selected']) && $_val['selected'] == true)) && $_optionSelected == false) {
                $_outputData .= ' selected';
                $_optionSelected = true;
                $_selectedParentOption = $_val['value'];
            }

            $_outputData .= '>' . str_replace(" ", "&nbsp;", htmlspecialchars($this->Input->SanitizeForXSS($_val['title']))) . '</option>' . SWIFT_CRLF;
        }

        $_outputData .= '</select>' . $_extendedData . SWIFT_CRLF;

        // Now for each parent option, generate a select box
        foreach ($_parentOptionContainer as $_parentValue => $_subOptionsContainer) {
            $_displayStyle = 'none';
            $_disabledSuffix = ' disabled="disabled"';
            if ($_selectedParentOption == $_parentValue || count($_parentOptionContainer) == 1) {
                $_displayStyle = 'block';
                $_disabledSuffix = '';
            }


            $_subOptionSelected = false;

            $_outputData .= '<div id="selectsuboptioncontainer_' . $_parentValue . '" class="linkedselectcontainer linkedselectcontainer_' . $_name . '" style="display: ' . $_displayStyle . ';"><select name="' . $_name . '[1][' . $_parentValue . ']" id="select' . $_name . '_sub_' . $_parentValue . '" class="swiftselect"' . $_disabledSuffix . '>';
            foreach ($_subOptionsContainer as $_val) {
                $_iconData = '';
                if (isset($_val['icon']) && !empty($_val['icon'])) {
                    $_iconData = ' style="padding-left: 18px; background: url(' . $_val['icon'] . ') no-repeat; HEIGHT: 15px; PADDING-TOP: 3px;"';
                }

                $_outputData .= '<option' . $_iconData . ' value="' . $_val['value'] . '"';
                if (((isset($_POST[$_name][1][$_parentValue]) && $_POST[$_name] == $_val['value']) ||
                        (!isset($_POST[$_name][1][$_parentValue]) && isset($_val['selected']) && $_val['selected'] == true)) && $_subOptionSelected == false) {
                    $_outputData .= ' selected';
                    $_subOptionSelected = true;
                }

                $_outputData .= '>' . str_replace(" ", "&nbsp;", htmlspecialchars($this->Input->SanitizeForXSS($_val['title']))) . '</option>' . SWIFT_CRLF;
            }

            $_outputData .= '</select></div>' . SWIFT_CRLF;
        }

        $_info = '<span class="tabletitle">' . $_title . '</span>';

        $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

        $_outputData .= $_extraHTML;

        return $this->DefaultRow($_info, $_outputData, 'tr_' . $_name, $_columnClass, $_customWidth);
    }

    /**
     * Print a Select Row
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_description (OPTIONAL) The Field Description
     * @param array $_optionsContainer The Options Container Array
     * @param string $_onChange (OPTIONAL) The JavaScript to Execute On Change of Select Item
     * @param string $_divContainerID (OPTIONAL) If the Select Data should be placed in a container div
     * @param string $_extendedData (OPTIONAL) The Extended HTML Data
     * @param mixed $_customWidth (OPTIONAL) Specify Custom Width for First Column
     * @param string $_selectStyle (OPTIONAL) Specify a custom select style
     * @param string $_extraHTML
     * @return bool "true" on Success, "false" otherwise
     */
    public function Select($_name, $_title, $_description = '', $_optionsContainer = array(), $_onChange = '', $_divContainerID = '',
                           $_extendedData = '', $_customWidth = false, $_selectStyle = '', $_extraHTML = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title) || !is_array($_optionsContainer)) {
            return false;
        }

        $_optionSelected = false;

        $_forceStyle = '';
        if ($_selectStyle) {
            $_forceStyle = ' style="' . $_selectStyle . '"';
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
            $_forceStyle = ' style="margin-left: 8px"';
        }

        $_outputData = '<select name="' . $_name . '" id="select' . $_name . '" class="swiftselect"' . IIF(!empty($_onChange), ' onChange="' . $_onChange . '"') . $_forceStyle . '>';
        foreach ($_optionsContainer as $_key => $_val) {
            if (isset($_val['icon']) && !empty($_val['icon'])) {
                $_iconData = ' style="padding-left: 18px; background: url(' . $_val['icon'] . ') no-repeat; HEIGHT: 15px; PADDING-TOP: 3px;"';
            } else {
                $_iconData = '';
            }

            $_outputData .= '<option' . $_iconData . ' value="' . $_val['value'] . '"';
            if (((isset($_POST[$_name]) && $_POST[$_name] == $_val['value']) || (!isset($_POST[$_name]) && isset($_val['selected']) && $_val['selected'] == true)) && $_optionSelected == false) {
                $_outputData .= ' selected';
                $_optionSelected = true;
            }

            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
             *
             * SWIFT-4430 Disabled SLA plan can be implemented over a ticket manually from 'Edit' tab.
             */
            if ((isset($_val['disabled']) && $_val['disabled'] == true)) {
                $_outputData .= ' disabled="disabled"';
            }

            $_outputData .= '>' . text_to_html_entities($this->Input->SanitizeForXSS($_val['title'])) . '</option>' . SWIFT_CRLF;
        }

        $_outputData .= '</select>' . $_extendedData . SWIFT_CRLF;

        $_info = '<span class="tabletitle">' . $_title . '</span>';

        $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

        $value = IIF(!empty($_divContainerID),
                '<div id="' . $_divContainerID . '">') . $_outputData . IIF(!empty($_divContainerID), '</div>');

        $value .= $_extraHTML;

        return $this->DefaultRow($_info, $value, 'tr_' . $_name, $_columnClass, $_customWidth);
    }

    /**
     * Prints a row with select multiple box allowing selection of multiple options
     *
     * @author Varun Shoor
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_description (OPTIONAL) The Field Description
     * @param array $_optionsContainer The Options Container Array
     * @param int $_fieldSize (OPTIONAL) The Field Size
     * @param string $_width (OPTIONAL) The Field Width
     * @param string $_onChange (OPTIONAL) The JavaScript to Execute On Change of Select Item
     * @param string $_divContainerID (OPTIONAL) If the Select Data should be placed in a container div
     * @param string $_fieldID
     * @param string $_extraHTML
     * @return bool "true" on Success, "false" otherwise
     */
    public function SelectMultiple($_name, $_title, $_description = '', $_optionsContainer = array(), $_fieldSize = 5, $_width = '', $_onChange = '', $_divContainerID = '', $_fieldID = '', $_extraHTML = '')
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title)) {
            return false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        $_outputData = '<select name="' . $_name . '[]" class="swiftselect" size="' . $_fieldSize . '"' . IIF(!empty($_width), ' style="WIDTH: ' . $_width . ';"') . ' multiple>';
        foreach ($_optionsContainer as $_key => $_val) {
            if (isset($_val['icon']) && !empty($_val['icon'])) {
                $_iconData = ' style="padding-left: 18px; background: url(' . $_val['icon'] . ') no-repeat; HEIGHT: 15px; PADDING-TOP: 3px;"';
            } else {
                $_iconData = '';
            }

            $_outputData .= '<option' . $_iconData . ' value="' . $_val['value'] . '"';
            if ((isset($_POST[$_name]) && in_array($_val['value'], $_POST[$_name])) || (!isset($_POST[$_name]) && isset($_val['selected']) && $_val['selected'] == true)) {
                $_outputData .= ' selected';
            }

            $_outputData .= '>' . str_replace(' ', '&nbsp;', htmlspecialchars($this->Input->SanitizeForXSS($_val['title']))) . '</option>' . SWIFT_CRLF;
        }

        $_outputData .= '</select>' . SWIFT_CRLF;

        $_info = '<span class="tabletitle">' . $_title . '</span>';

        $_info .= IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

        $str = IIF(!empty($_divContainerID),
                '<div id="' . $_divContainerID . '">') . $_outputData . IIF(!empty($_divContainerID), '</div>');

        $str .= $_extraHTML;

        return $this->DefaultRow($_info, $str, $_fieldID, $_columnClass);
    }

    /**
     * Prints a row with date in select box allowing selection of day, month and year
     *
     * @author Ruchi Kothari
     * @param string $_name The Field Name
     * @param string $_title The Field Title
     * @param string $_description (OPTIONAL) The Field Description
     * @param int $_yearStart Starting year in year select box
     * @param int $_yearEnd End year in year select box
     * @param int $_defaultDateLine (OPTIONAL) Default dateline to be displayed
     * @param string $_divContainerID (OPTIONAL) If the Select Data should be placed in a container div
     * @param mixed $_customWidth (OPTIONAL) Specify Custom Width for First Column
     * @return bool "true" on Success, "false" otherwise
     */
    public function DateSelect($_name, $_title, $_description, $_yearStart, $_yearEnd, $_defaultDateLine = null, $_divContainerID = '', $_customWidth = false)
    {
        if (!$this->GetIsClassLoaded() || empty($_name) || empty($_title)) {
            return false;
        }

        $_columnClass = '';
        if (in_array($_name, SWIFT::GetErrorFieldContainer())) {
            $_columnClass = 'errorrow';
        }

        $_defaultDay = '';
        $_defaultMonth = '';
        $_defaultYear = '';
        if (!is_null($_defaultDateLine)) {
            $_defaultDay = date('j', $_defaultDateLine);
            $_defaultMonth = date('n', $_defaultDateLine);
            $_defaultYear = date('Y', $_defaultDateLine);
        }

        $_outputData = '';
        $_outputData = '<select name="' . $_name . '_year" class="swiftselect">';
        $_outputData .= '<option value="0"></option>';
        for ($_index = $_yearStart; $_index <= $_yearEnd; $_index++) {
            $_outputData .= '<option value="' . $_index . '"';

            if ($_index == $_defaultYear) {
                $_outputData .= ' selected';
            }

            $_outputData .= '>' . $_index . '</option>';
        }

        $_outputData .= '</select>';

        $_outputData .= '<select name="' . $_name . '_month" class="swiftselect">';
        $_outputData .= '<option value="0"></option>';

        for ($_index = 1; $_index <= 12; $_index++) {
            $_outputData .= '<option value="' . $_index . '"';

            if ($_index == $_defaultMonth) {
                $_outputData .= ' selected="selected"';
            }

            /*
             * BUG FIX - Amarjeet Kaur
             *
             * SWIFT-3697: DateSelect method - Months with 31 days appear two times in drop down at last day of respective month
             */
            $_outputData .= '>' . date('M', mktime(0, 0, 0, $_index, 1, (int)date('Y'))) . '</option>';
        }

        $_outputData .= '</select>';

        $_outputData .= '<select name="' . $_name . '_day" class="swiftselect">';
        $_outputData .= '<option value="0"></option>';
        for ($_index = 1; $_index <= 31; $_index++) {
            $_outputData .= '<option value="' . $_index . '"';

            if ($_index == $_defaultDay) {
                $_outputData .= ' selected';
            }

            $_outputData .= '>' . $_index . '</option>';
        }

        $_outputData .= '</select>';

        $_info = '<span class="tabletitle">' . $_title . '</span>' . IIF(!empty($_description), '<span class="tabledescription">' . $_description . '</span>');

        return $this->DefaultRow($_info, IIF(!empty($_divContainerID), '<div id="' . $_divContainerID . '">') . $_outputData . IIF(!empty($_divContainerID), '</div>'), 'tr_' . $_name, $_columnClass, $_customWidth);
    }

    /**
     *
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function SelectChoice()
    {

    }

    /**
     * Add a Javascript code to event queue
     *
     * @author Varun Shoor
     * @param string $_eventType The Event Type
     * @param string $_javaScriptCode The JS Code
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function EventQueue($_eventType, $_javaScriptCode)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_eventType) || empty($_javaScriptCode)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_javascriptEventFunctionQueue[$_eventType][] = $_javaScriptCode;

        return true;
    }

    /**
     * Render the Javascript code
     *
     * @author Varun Shoor
     * @return string The JS Code
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderJavaScript()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_javaScriptCode = 'if (typeof _tabFunctionQueue != "undefined") {';

        foreach ($this->_javascriptEventFunctionQueue as $_eventType => $_javaScriptCodeContainer) {
            $_javaScriptCode .= '_tabFunctionQueue[\'' . $this->UserInterface->_formName . '\'][\'' . $_eventType . '\'][' . $this->_tabIndex . '] = function () {' . implode(SWIFT_CRLF, $_javaScriptCodeContainer) . '};' . SWIFT_CRLF;
        }

        $_javaScriptCode .= '}';

        return $_javaScriptCode;
    }

    /**
     * Return the tab counter
     *
     * @author Varun Shoor
     * @return int The Tab Counter
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTabCounter()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_tabCounter;
    }

    /**
     * Set the tab counter
     *
     * @author Varun Shoor
     * @param int $_tabCounter The Tab Counter
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetTabCounter($_tabCounter)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_tabCounter = $_tabCounter;

        return true;
    }
}

?>
