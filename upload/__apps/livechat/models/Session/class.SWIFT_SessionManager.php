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

namespace LiveChat\Models\Session;

use SWIFT;
use SWIFT_Model;
use SWIFT_Session;
use LiveChat\Models\Visitor\SWIFT_Visitor;

/**
 * The Session Manager File. Handles creation, updation and deletion of all kinds of visitor, chat and staff sessions related to live chat
 *
 * @author Varun Shoor
 */
class SWIFT_SessionManager extends SWIFT_Model
{
    private $_sessionID;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_sessionID The Session ID
     */
    public function __construct($_sessionID)
    {
        parent::__construct();

        if (!$this->SetSessionID($_sessionID)) {
            $this->SetIsClassLoaded(false);
        }
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool()) || !$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'sessions', $this->GetUpdatePool(), 'UPDATE', "sessionid = '" . $this->Database->Escape($this->GetSessionID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieve the Session ID
     *
     * @author Varun Shoor
     * @return string|bool "sessionid" on Success, "false" otherwise
     */
    public function GetSessionID()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_sessionID;
    }

    /**
     * Sets the Session ID
     *
     * @author Varun Shoor
     * @param string $_sessionID The Session ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetSessionID($_sessionID)
    {
        $this->_sessionID = $_sessionID;

        return true;
    }

    /**
     * Updates the Session Status
     *
     * @author Varun Shoor
     * @param int $_status The Session Status
     * @param mixed $_proactiveResult The Proactive Result
     * @return bool "true" on Success, "false" otherwise
     */
    public function UpdateSessionStatus($_status, $_proactiveResult)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UpdatePool('status', ($_status));
        $this->UpdatePool('proactiveresult', (int)($_proactiveResult));

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Updates the last activity for the session
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function UpdateSessionLastActivity()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UpdatePool('lastactivity', DATENOW);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Sets the Color for the visitor
     *
     * @author Varun Shoor
     * @param string $_hexCode The Color HEX Code
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetColor($_hexCode)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UpdatePool('gridcolor', $_hexCode);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Sets the Visitor Group ID for the session
     *
     * @author Varun Shoor
     * @param int $_visitorGroupID The Visitor Group ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetVisitorGroup($_visitorGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_visitorGroupCache = $_SWIFT->Cache->Get('visitorgroupcache');

        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!isset($_visitorGroupCache[$_visitorGroupID])) {
            return false;
        }

        $this->UpdatePool('visitorgroupid', $_visitorGroupID);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Sets the Visitor Department ID for the session
     *
     * @author Varun Shoor
     * @param int $_departmentID The Visitor Department ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetDepartment($_departmentID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');

        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!isset($_departmentCache[$_departmentID]) || $_departmentCache[$_departmentID]['departmentapp'] != APP_LIVECHAT) {
            return false;
        }

        $this->UpdatePool('departmentid', $_departmentID);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Processes the session request for an incoming visitor. Is used to check for banned status and recreate the session if it got flushed out.
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function ProcessVisitorUpdateSession()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (!SWIFT_Session::Start($_SWIFT->Interface, $this->GetSessionID())) {
            $_isBanned = SWIFT_Visitor::IsBanned(true);
            if (!$_isBanned) {
                $_sessionID = SWIFT_Session::Insert($_SWIFT->Interface->GetInterface(), 0, $this->GetSessionID());

                return $_sessionID;
            }
        }

        return true;
    }
}
