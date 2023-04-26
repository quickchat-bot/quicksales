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

namespace LiveChat\Models\Message;

use Base\Models\User\SWIFT_UserConsent;
use SWIFT;
use SWIFT_App;
use SWIFT_Loader;
use SWIFT_Mail;
use LiveChat\Models\Message\SWIFT_Message_Exception;
use LiveChat\Models\Message\SWIFT_MessageManager;
use SWIFT_Router;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Client Message Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Message extends SWIFT_MessageManager
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_messageID The Message ID
     * @throws SWIFT_Message_Exception If the Record could not be loaded
     */
    public function __construct($_messageID)
    {
        parent::__construct($_messageID);

        if ($this->GetProperty('messagetype') != self::MESSAGE_CLIENT && $this->GetProperty('messagetype') != self::MESSAGE_STAFF) {
            throw new SWIFT_Message_Exception(SWIFT_INVALIDDATA);
        }
    }

    /**
     * Creates the message, handles the routing and inserts the record if necessary
     *
     * @author Varun Shoor
     * @param string $_fullName The User Full Name
     * @param string $_email The User Email
     * @param string $_subject The User Subject
     * @param int $_departmentID The Department ID
     * @param string $_messageContents The Message Contents
     * @param int $_templateGroupID The Template Group ID
     * @return mixed "_SWIFT_MessageObject" (OBJECT) on Success
     * @throws SWIFT_Message_Exception If Invalid Data is Provided
     */
    public static function Create($_fullName, $_email, $_subject, $_departmentID, $_messageContents, $_templateGroupID, $_ = null, $__ = null, $___ = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_fullName) || empty($_email) || empty($_departmentID) || empty($_subject) || empty($_messageContents) || !IsEmailValid($_email)) {
            throw new SWIFT_Message_Exception(SWIFT_INVALIDDATA);
        }

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_templateGroupCache = $_SWIFT->Cache->Get('templategroupcache');

        $_messageRouting = self::GetMessageRoutingRule($_departmentID);

        $_insertMessageRecord = false;
        if (!$_messageRouting || (_is_array($_messageRouting) && $_messageRouting['preservemessage'])) {
            $_insertMessageRecord = true;
        }

        $_SWIFT_MessageObject = true;
        if ($_insertMessageRecord) {
            $_messageID = parent::Create($_fullName, $_email, $_subject, $_departmentID, $_messageContents, self::MESSAGE_CLIENT, 0);

            if (!$_messageID) {
                throw new SWIFT_Message_Exception(SWIFT_CREATEFAILED);
            }

            $_SWIFT_MessageObject = new SWIFT_Message($_messageID);
            if (!$_SWIFT_MessageObject instanceof SWIFT_Message || !$_SWIFT_MessageObject->GetIsClassLoaded()) {
                throw new SWIFT_Message_Exception(SWIFT_CREATEFAILED);
            }
        }

        if (isset($_messageRouting['routetotickets'], $_messageRouting['ticketdepartmentid'], $_departmentCache[$_messageRouting['ticketdepartmentid']], $_templateGroupCache[$_templateGroupID]) && !empty($_messageRouting['routetotickets']) && !empty($_messageRouting['ticketdepartmentid']) && SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_templateGroup = $_templateGroupCache[$_templateGroupID];

            SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);

            $_SWIFT->Language->Load('tickets');
            SWIFT_Ticket::LoadLanguageTable();

            $_userID = SWIFT_Ticket::GetOrCreateUserID($_fullName, $_email, $_templateGroup['regusergroupid']);

            if ($_userConsent = SWIFT_UserConsent::RetrieveConsent($_userID, SWIFT_UserConsent::CONSENT_REGISTRATION)) {
                (new SWIFT_UserConsent($_userConsent[SWIFT_UserConsent::PRIMARY_KEY]))
                    ->update(SWIFT_UserConsent::CHANNEL_WEB, SWIFT_UserConsent::SOURCE_LIVE_CHAT, SWIFT_Router::GetRequestURI());
            } else {
                SWIFT_UserConsent::Create(
                    $_userID,
                    SWIFT_UserConsent::CONSENT_REGISTRATION,
                    SWIFT_UserConsent::CHANNEL_WEB,
                    SWIFT_UserConsent::SOURCE_LIVE_CHAT, SWIFT_Router::GetRequestURI());
            }
            $_ownerStaffID = 0;

            $_SWIFT_TicketObject = SWIFT_Ticket::Create($_subject, $_fullName, $_email, $_messageContents, $_ownerStaffID, $_messageRouting['ticketdepartmentid'],
                $_templateGroup['ticketstatusid'], $_templateGroup['priorityid'], $_templateGroup['tickettypeid'],
                $_userID, 0, SWIFT_Ticket::TYPE_DEFAULT, SWIFT_Ticket::CREATOR_CLIENT, SWIFT_Ticket::CREATIONMODE_SUPPORTCENTER, '', 0, true);

            $_SWIFT_TicketObject->SetTemplateGroup($_SWIFT->TemplateGroup->GetTemplateGroupID());
        }

        if ($_messageRouting['routetoemail'] && isset($_messageRouting['forwardemails']) && !empty($_messageRouting['routetoemail']) && !empty($_messageRouting['forwardemails'])) {
            $_destinationEmailList = $_pendingProcessEmailList = array();
            $_messageRouting['forwardemails'] = trim($_messageRouting['forwardemails']);
            if (stristr($_messageRouting['forwardemails'], ';')) {
                $_pendingProcessEmailList = explode(';', $_messageRouting['forwardemails']);
                if (_is_array($_pendingProcessEmailList)) {
                    foreach ($_pendingProcessEmailList as $_pendingEmail) {
                        if (IsEmailValid(trim($_pendingEmail))) {
                            $_destinationEmailList[] = trim($_pendingEmail);
                        }
                    }
                } else if (IsEmailValid(trim(str_replace(';', '', $_messageRouting['forwardemails'])))) {
                    $_destinationEmailList[] = trim(str_replace(';', '', $_messageRouting['forwardemails']));
                }
            } else if (IsEmailValid($_messageRouting['forwardemails'])) {
                $_destinationEmailList[] = $_messageRouting['forwardemails'];
            }

            if (_is_array($_destinationEmailList)) {
                foreach ($_destinationEmailList as $_destinationEmail) {
                    $_SWIFT_MailObject = new SWIFT_Mail();
                    $_SWIFT_MailObject->SetToField($_destinationEmail);
                    $_SWIFT_MailObject->SetFromField($_email, $_fullName);
                    $_SWIFT_MailObject->SetSubjectField($_subject);
                    $_SWIFT_MailObject->SetDataText($_messageContents);
                    $_SWIFT_MailObject->SendMail(false);
                }
            }
        }

        // GeoIP
        if ($_SWIFT_MessageObject instanceof SWIFT_Message && $_SWIFT_MessageObject->GetIsClassLoaded()) {
            $_SWIFT_MessageObject->UpdateGeoIP();
        }

        return $_SWIFT_MessageObject;
    }

    /**
     * Retrieve the Message Routing Rules for a given department
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID to fetch the rule from
     * @return array|bool
     * @throws SWIFT_Message_Exception If Invalid Data is Provided
     */
    public static function GetMessageRoutingRule($_departmentID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_departmentID)) {
            throw new SWIFT_Message_Exception(SWIFT_INVALIDDATA);
        }

        $_messageRouting = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "messagerouting WHERE departmentid = '" . $_departmentID . "'");
        if (!isset($_messageRouting['messageroutingid']) || empty($_messageRouting['messageroutingid'])) {
            return false;
        }

        return $_messageRouting;
    }
}

