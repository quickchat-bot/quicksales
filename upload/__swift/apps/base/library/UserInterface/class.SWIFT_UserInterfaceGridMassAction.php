<?php
//=======================================
//###################################
// QuickSupport Singapore Pte. Ltd. - SWIFT Framework
//
// Source Copyright 2001QuickSupport Singapore Pte. Ltd.h Ltd.
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//                          www.opencart.com.vn
//###################################
//=======================================

namespace Base\Library\UserInterface;

use SWIFT;
use SWIFT_Base;
use SWIFT_Exception;

/**
 * The Mass Action Handler Class
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class SWIFT_UserInterfaceGridMassAction extends SWIFT_Base
{
    private $_massActionTitle = '';
    private $_massActionName = '';
    private $_massActionIcon = false;
    private $_massActionConfirmMessage = false;
    private $_massActionCallback = false;
    private $_massActionDialogCallback = false;
    private $_massActionDialogTitle = '';
    private $_massActionDialogWidth = 0;
    private $_massActionDialogHeight = 0;

    private $_massActionIsDialog = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_massActionTitle The Mass Action Button Title
     * @param string $_massActionIcon The Mass Action Button Icon
     * @param array $_massActionCallback The Mass Action Callback
     * @param string $_massActionConfirmMessage (OPTIONAL) The Message Confirmation
     * @param array $_massActionDialog (OPTIONAL) The Mass Action Dialog
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct($_massActionTitle, $_massActionIcon, $_massActionCallback, $_massActionConfirmMessage = '',
                                $_massActionDialog = array())
    {
        parent::__construct();

        if ($this->SetTitle($_massActionTitle) && $this->SetName(md5($_massActionTitle)) && $this->SetCallback($_massActionCallback) &&
            $this->SetDialog($_massActionDialog)) {
            $this->SetIcon($_massActionIcon);
            $this->SetConfirmMessage($_massActionConfirmMessage);

            $this->SetIsClassLoaded(true);
        }
    }

    /**
     * Set the Mass Action Dialog
     *
     * @author Varun Shoor
     * @param array $_massActionDialog The Dialog Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetDialog($_massActionDialog)
    {
        if (_is_array($_massActionDialog) && count($_massActionDialog) != 4) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!_is_array($_massActionDialog)) {
            return true;
        }

        $this->_massActionDialogTitle = $_massActionDialog[0];
        $this->_massActionDialogWidth = $_massActionDialog[1];
        $this->_massActionDialogHeight = $_massActionDialog[2];
        $this->_massActionDialogCallback = $_massActionDialog[3];

        $this->_massActionIsDialog = true;

        return true;
    }

    /**
     * Retrieve the dialog title
     *
     * @author Varun Shoor
     * @return string The Dialog Title
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDialogTitle()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_massActionDialogTitle;
    }

    /**
     * Retrieve the Dialog Width
     *
     * @author Varun Shoor
     * @return int The Dialog Width
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDialogWidth()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_massActionDialogWidth;
    }

    /**
     * Retrieve the Dialog Height
     *
     * @author Varun Shoor
     * @return int The Dialog Height
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDialogHeight()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_massActionDialogHeight;
    }

    /**
     * Retrieve whether the dialog is active
     *
     * @author Varun Shoor
     * @return bool The Dialog Property
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetIsDialog()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_massActionIsDialog;
    }

    /**
     * Set the Mass Action Dialog Callback
     *
     * @author Varun Shoor
     * @param array $_massActionDialogCallback The Callback Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetDialogCallback($_massActionDialogCallback)
    {
        if (!_is_array($_massActionDialogCallback)) {
            return true;
        }

        $this->_massActionDialogCallback = $_massActionDialogCallback;

        return true;
    }

    /**
     * Get the Mass Action Dialog Callback
     *
     * @author Varun Shoor
     * @return mixed "_massActionDialogCallback" (ARRAY) on Success, "false" otherwise
     */
    public function GetDialogCallback()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_massActionDialogCallback;
    }

    /**
     * Set the Mass Action Callback
     *
     * @author Varun Shoor
     * @param array $_massActionCallback The Callback Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetCallback($_massActionCallback)
    {
        if (!_is_array($_massActionCallback)) {
            return false;
        }

        $this->_massActionCallback = $_massActionCallback;

        return true;
    }

    /**
     * Get the Mass Action Callback
     *
     * @author Varun Shoor
     * @return mixed "_massActionCallback" (ARRAY) on Success, "false" otherwise
     */
    public function GetCallback()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_massActionCallback;
    }

    /**
     * Set the Mass Action Button Title
     *
     * @author Varun Shoor
     * @param string $_massActionTitle The Mass Action Button Title
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetTitle($_massActionTitle)
    {
        if (empty($_massActionTitle) || trim($_massActionTitle) == '') {
            return false;
        }

        $this->_massActionTitle = $_massActionTitle;

        return true;
    }

    /**
     * Retrieve the Mass Action Button Title
     *
     * @author Varun Shoor
     * @return mixed "_massActionTitle" (STRING) on Success, "false" otherwise
     */
    public function GetTitle()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_massActionTitle;
    }

    /**
     * Set the Mass Action Button Name
     *
     * @author Varun Shoor
     * @param string $_massActionName The Mass Action Button Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetName($_massActionName)
    {
        if (empty($_massActionName) || trim($_massActionName) == '') {
            return false;
        }

        $this->_massActionName = $_massActionName;

        return true;
    }

    /**
     * Retrieve the Mass Action Button Name
     *
     * @author Varun Shoor
     * @return mixed "_massActionName" (STRING) on Success, "false" otherwise
     */
    public function GetName()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_massActionName;
    }

    /**
     * Set Icon
     *
     * @author Varun Shoor
     * @param string $_massActionIcon The Mass Action Icon
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetIcon($_massActionIcon)
    {
        if (empty($_massActionIcon) || trim($_massActionIcon) == '') {
            return false;
        }

        $this->_massActionIcon = $_massActionIcon;

        return true;
    }

    /**
     * Retrieve the Mass Action Icon
     *
     * @author Varun Shoor
     * @return mixed "_massActionIcon" (STRING) on Success, "false" otherwise
     */
    public function GetIcon()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_massActionIcon;
    }

    /**
     * Set the Mass Action Confirm Message (Optional)
     *
     * @author Varun Shoor
     * @param string $_massActionConfirmMessage The Mass Action Confirm Message
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetConfirmMessage($_massActionConfirmMessage)
    {
        if (empty($_massActionConfirmMessage) || trim($_massActionConfirmMessage) == '') {
            return false;
        }

        $this->_massActionConfirmMessage = $_massActionConfirmMessage;

        return true;
    }

    /**
     * Retrieve the Mass Action Confirm Message
     *
     * @author Varun Shoor
     * @return string "_massActionConfirmMessage" (STRING) on Success, "" otherwise
     */
    public function GetConfirmMessage()
    {
        if (!$this->GetIsClassLoaded()) {
            return '';
        }

        return $this->_massActionConfirmMessage;
    }

    /**
     * Check and Execute the Callback if we are supposed to
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function CheckAndExecute()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (isset($_POST['_massAction']) && !empty($_POST['_massAction']) && $this->GetName() == $_POST['_massAction'] && isset($_POST['itemid']) && _is_array($_POST['itemid'])) {
            $_callBackResult = call_user_func($this->GetCallback(), $_POST['itemid']);
        }

        if (isset($_POST['_massActionDialog']) && !empty($_POST['_massActionDialog']) && $this->GetName() == $_POST['_massActionDialog']) {
            $_SWIFT->UserInterface->Hidden('_massAction', $this->GetName());
            $_SWIFT->UserInterface->Hidden('_massActionPanel', $_POST['_massActionPanel']);
            $_SWIFT->UserInterface->Hidden('_offset', $_POST['_offset']);

            $_itemIDList = array();
            if (isset($_POST['itemid'])) {
                $_itemIDList = $_POST['itemid'];
                foreach ($_POST['itemid'] as $_val) {
                    $_SWIFT->UserInterface->HiddenArray('itemid', $_val);
                }

            }

            $_callBackResult = call_user_func($this->GetDialogCallback(), $_itemIDList);

        }

        return true;
    }
}

?>
