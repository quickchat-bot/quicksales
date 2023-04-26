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

namespace Tickets\Client;

use Controller_client;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Date;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Models\Rating\SWIFT_Rating;
use Base\Models\Rating\SWIFT_RatingResult;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Models\Merge\SWIFT_TicketMergeLog;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Base\Models\User\SWIFT_User;

/**
 * The Survey Controller
 *
 * @author Varun Shoor
 */
class Controller_Survey extends Controller_client
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('CustomField:CustomFieldRendererClient', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        $this->Language->Load('tickets');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * Retrieve the Ticket Object
     *
     * @author Varun Shoor
     * @param int|string $_ticketID The Ticket ID
     * @param string $_ticketHash The Ticket Hash
     * @return SWIFT_Ticket|bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetTicketObject($_ticketID, $_ticketHash)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID))
        {
            return false;
        }

        $_SWIFT_TicketObject = false;

        $_finalTicketID = false;
        if (is_numeric($_ticketID))
        {
            $_finalTicketID = $_ticketID;
        } else {
            $_finalTicketID = SWIFT_Ticket::GetTicketIDFromMask($_ticketID);
        }

        if (!empty($_finalTicketID))
        {
            try
            {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_finalTicketID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }
        }

        if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded() && $_SWIFT_TicketObject->GetProperty('tickethash') == $_ticketHash && trim($_ticketHash) != '')
        {
            return $_SWIFT_TicketObject;
        }

        // By now we couldnt get the ticket object, we have to lookup the merge logs
        $_mergeTicketID = false;
        if (is_numeric($_ticketID)) {
            $_mergeTicketID = SWIFT_TicketMergeLog::GetTicketIDFromMergedTicketID($_ticketID);
        } else {
            $_mergeTicketID = SWIFT_TicketMergeLog::GetTicketIDFromMergedTicketMaskID($_ticketID);
        }

        if (!empty($_mergeTicketID)) {
            try
            {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_mergeTicketID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded() && $_SWIFT_TicketObject->CanAccess($_SWIFT->User))
            {
                return $_SWIFT_TicketObject;
            }
        }


        return false;
    }

    /**
     * Load the Survey Form
     *
     * @author Varun Shoor
     * @param int|string $_ticketID (OPTIONAL) The Ticket ID
     * @param string $_ticketHash (OPTIONAL) The Ticket Hash
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index($_ticketID = '', $_ticketHash = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID) || empty($_ticketHash)) {
            return false;
        }

        $_SWIFT_TicketObject = $this->_GetTicketObject($_ticketID, $_ticketHash);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded())
        {
            $this->UserInterface->Error(true, $this->Language->Get('nopermission'));

            $this->Load->Controller('Default', 'Base')->Load->Index();

            return false;
        }

        $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded())
        {
            $_ticketEmailAddress = $_SWIFT_TicketObject->GetProperty('email');
            if ($_SWIFT_TicketObject->GetProperty('replyto') != '' && $_SWIFT_TicketObject->GetProperty('replyto') != $_ticketEmailAddress && IsEmailValid($_SWIFT_TicketObject->GetProperty('replyto')))
            {
                $_ticketEmailAddress = $_SWIFT_TicketObject->GetProperty('replyto');
            }

            $_userID = SWIFT_Ticket::GetOrCreateUserID($_SWIFT_TicketObject->GetProperty('fullname'), $_ticketEmailAddress, $_SWIFT->TemplateGroup->GetProperty('regusergroupid'));
            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));

            if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded())
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                $this->UserInterface->Error(true, $this->Language->Get('invaliduseracc'));

                $this->Load->Controller('Default', 'Base')->Load->Index();

                return false;
                // @codeCoverageIgnoreEnd
            }

            $_SWIFT_TicketObject->UpdateUser($_userID);
        }

        $_departmentCache = $this->Cache->Get('departmentcache');

        // Process Ratings
        $_ticketRatingContainer = SWIFT_Rating::Retrieve(array(SWIFT_Rating::TYPE_TICKET), false, SWIFT_PUBLIC, $_SWIFT_TicketObject->GetProperty('departmentid'), SWIFT::Get('usergroupid'));

        $_ticketRatingIDList = array_keys($_ticketRatingContainer);

        $_ticketRatingResultContainer = SWIFT_RatingResult::Retrieve($_ticketRatingIDList, array($_SWIFT_TicketObject->GetTicketID()));

        foreach ($_ticketRatingResultContainer as $_ratingID => $_ticketRatingResultContainer_Sub)
        {
            foreach ($_ticketRatingResultContainer_Sub as $_ticketRatingResult)
            {
                $_ticketRatingContainer[$_ratingID]['result'] = $_ticketRatingResult['ratingresult'];

                if ($_ticketRatingContainer[$_ratingID]['iseditable'] == '0')
                {
                    $_ticketRatingContainer[$_ratingID]['isdisabled'] = true;
                }
            }
        }

        $this->Template->Assign('_ticketRatingCount', count($_ticketRatingContainer));
        $this->Template->Assign('_ticketRatingContainer', $_ticketRatingContainer);

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-5060 Unicode characters like emojis not working in the subject
         */
        // Process Properties
        $_ticketContainer = $_SWIFT_TicketObject->GetDataStore();
        $_ticketContainer['displayticketid'] = $_SWIFT_TicketObject->GetTicketDisplayID();
        $_ticketContainer['subject'] = $this->Input->SanitizeForXSS($this->Emoji->decode($_SWIFT_TicketObject->GetProperty('subject')));
        $_ticketContainer['dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TicketObject->GetProperty('lastactivity'));
        $_ticketContainer['lastactivity'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TicketObject->GetProperty('lastactivity'));

        $_ticketDepartmentFullTitle = $this->Language->Get('na');

        // Department
        if (isset($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]))
        {
            if ($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['departmenttype'] == SWIFT_Department::DEPARTMENT_PUBLIC)
            {
                $_ticketDepartmentParentDepartmentID = $_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['parentdepartmentid'];
                if ($_ticketDepartmentParentDepartmentID != '0' && isset($_departmentCache[$_ticketDepartmentParentDepartmentID]))
                {
                    $_ticketDepartmentFullTitle = text_to_html_entities($_departmentCache[$_ticketDepartmentParentDepartmentID]['title']) . ' &raquo; ' . text_to_html_entities($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['title']);
                } else {
                    $_ticketDepartmentFullTitle = text_to_html_entities($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['title']);
                }
                $_ticketContainer['department'] = StripName(text_to_html_entities($_departmentCache[$_SWIFT_TicketObject->GetProperty('departmentid')]['title']), 16);
            } else {
                $_ticketContainer['department'] = $this->Language->Get('private');
            }
        }

        $this->Template->Assign('_ticketDepartmentFullTitle', $_ticketDepartmentFullTitle);

        $this->Template->Assign('_ticketContainer', $_ticketContainer);

        $this->Template->Assign('_pageTitle', htmlspecialchars($this->Language->Get('ticketsurveytickettitle') . $_SWIFT_TicketObject->GetTicketDisplayID()));
        $this->UserInterface->Header('survey');

        $this->Template->Render('ticketsurvey');

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Survey Submission
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param string $_ticketHash The Ticket Hash
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SurveySubmit($_ticketID, $_ticketHash)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TicketObject = $this->_GetTicketObject($_ticketID, $_ticketHash);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded())
        {
            $this->UserInterface->Error(true, $this->Language->Get('nopermission'));

            $this->Load->Controller('Default', 'Base')->Load->Index();

            return false;
        }

        $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();
        if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded())
        {
            $this->UserInterface->Error(true, $this->Language->Get('invaliduseracc'));

            $this->Load->Controller('Default', 'Base')->Load->Index();

            return false;
        }

        // Process Ratings
        $_ticketRatingContainer = SWIFT_Rating::Retrieve(array(SWIFT_Rating::TYPE_TICKET), false, SWIFT_PUBLIC, $_SWIFT_TicketObject->GetProperty('departmentid'), SWIFT::Get('usergroupid'));

        $_ticketRatingIDList = array_keys($_ticketRatingContainer);

        $_ticketRatingResultContainer = SWIFT_RatingResult::Retrieve($_ticketRatingIDList, array($_SWIFT_TicketObject->GetTicketID()));

        foreach ($_ticketRatingResultContainer as $_ratingID => $_ticketRatingResultContainer_Sub)
        {
            foreach ($_ticketRatingResultContainer_Sub as $_ticketRatingResult)
            {
                $_ticketRatingContainer[$_ratingID]['result'] = $_ticketRatingResult['ratingresult'];

                if ($_ticketRatingContainer[$_ratingID]['iseditable'] == '0')
                {
                    $_ticketRatingContainer[$_ratingID]['isdisabled'] = true;
                }
            }
        }

        foreach ($_ticketRatingContainer as $_ratingID => $_ticketRating)
        {
            if (isset($_ticketRating['isdisabled']) && $_ticketRating['isdisabled'] == true)
            {
                continue;
            }

            if (!isset($_POST['rating']) || !isset($_POST['rating'][$_ratingID])) {
                continue;
            }


            $_SWIFT_RatingObject = new SWIFT_Rating((int) ($_ratingID));
            if (!$_SWIFT_RatingObject instanceof SWIFT_Rating || !$_SWIFT_RatingObject->GetIsClassLoaded())
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            SWIFT_RatingResult::CreateOrUpdateIfExists($_SWIFT_RatingObject, $_SWIFT_TicketObject->GetTicketID(), $_POST['rating'][$_ratingID], SWIFT_RatingResult::CREATOR_USER, $_SWIFT_UserObject->GetUserID());

            // Create Audit Log
            SWIFT_TicketAuditLog::AddToLog($_SWIFT_TicketObject, null,
	            SWIFT_TicketAuditLog::ACTION_RATING,
	            sprintf($this->Language->Get('al_rating'), $_POST['rating'][$_ratingID], $_SWIFT_UserObject->GetFullName()),
	            SWIFT_TicketAuditLog::VALUE_NONE,
	            0, '', 0, '',
	            ['al_rating', $_POST['rating'][$_ratingID], $_SWIFT_UserObject->GetFullName()]);
        }

        $_SWIFT_TicketObject->MarkHasRatings();

        if (isset($_POST['replycontents']) && !empty($_POST['replycontents']))
        {
            SWIFT_TicketPost::CreateClientSurvey($_SWIFT_TicketObject, $_SWIFT_UserObject, SWIFT_Ticket::CREATIONMODE_SUPPORTCENTER, $_POST['replycontents'], '', SWIFT_TicketPost::CREATOR_USER, false);

        // Still trigger the notification even if no comments are provided..
        } else {
            $_SWIFT_TicketObject->NotificationManager->SetEvent('newclientsurvey');
        }

        $this->UserInterface->Info(true, $this->Language->Get('thankyousurvey'));

        $this->Load->Controller('Default', 'Base')->Load->Index();

        return true;
    }
}
