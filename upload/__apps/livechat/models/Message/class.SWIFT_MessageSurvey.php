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

use LiveChat\Models\Message\SWIFT_Message_Exception;
use LiveChat\Models\Message\SWIFT_MessageManager;
use SWIFT;
use SWIFT_Exception;

/**
 * The Survey Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_MessageSurvey extends SWIFT_MessageManager
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

        if ($this->GetProperty('messagetype') != self::MESSAGE_CLIENTSURVEY) {
            throw new SWIFT_Message_Exception(SWIFT_INVALIDDATA);
        }
    }

    /**
     * Create the client survey messages
     *
     * @author Varun Shoor
     * @param string $_fullName The User Full Name
     * @param string $_email The User Email
     * @param string $_subject The User Subject
     * @param int $_departmentID The Department ID
     * @param int $_chatObjectID The Chat Object ID
     * @param float $_messageRating The Chat Rating
     * @param string $_messageContents The Message Contents
     * @return SWIFT_MessageSurvey Object on Success, "false" otherwise
     */
    public static function Create($_fullName, $_email, $_subject, $_departmentID, $_chatObjectID, $_messageRating, $_messageContents = null, $_ = null, $__ = null)
    {
        if (empty($_fullName) || empty($_email) || empty($_departmentID) || empty($_chatObjectID) || empty($_subject) || !IsEmailValid($_email) || empty($_messageContents)) {
            throw new SWIFT_Message_Exception(SWIFT_INVALIDDATA);
        }

        $_messageID = parent::Create($_fullName, $_email, $_subject, $_departmentID, $_messageContents, self::MESSAGE_CLIENTSURVEY, 0, $_chatObjectID, $_messageRating);
        if (!$_messageID) {
            throw new SWIFT_Message_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_MessageObject = new SWIFT_MessageSurvey($_messageID);

        // GeoIP
        $_SWIFT_MessageObject->UpdateGeoIP();

        return (new SWIFT_MessageSurvey($_messageID));
    }

    /**
     * Retrieve the Message Rating Image
     *
     * @author Varun Shoor
     * @param float $_messageRating The Message Rating
     * @return string The Image file name
     */
    public static function RetrieveRatingImage($_messageRating)
    {
        if ($_messageRating == '0') {
            return 'icon_star_0.gif';
        } else if ($_messageRating == '0.5') {
            return 'icon_star_0_5.gif';
        } else if ($_messageRating == '1') {
            return 'icon_star_1.gif';
        } else if ($_messageRating == '1.5') {
            return 'icon_star_1_5.gif';
        } else if ($_messageRating == '2') {
            return 'icon_star_2.gif';
        } else if ($_messageRating == '2.5') {
            return 'icon_star_2_5.gif';
        } else if ($_messageRating == '3') {
            return 'icon_star_3.gif';
        } else if ($_messageRating == '3.5') {
            return 'icon_star_3_5.gif';
        } else if ($_messageRating == '4') {
            return 'icon_star_4.gif';
        } else if ($_messageRating == '4.5') {
            return 'icon_star_4_5.gif';
        } else if ($_messageRating == '5') {
            return 'icon_star_5.gif';
        }

        return 'icon_star_0.gif';
    }

    /**
     * Retrieve the object on the basis of chatobjectid
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @return mixed "_SWIFT_MessageSurveyObject" (OBJECT) on Success, "false" otherwise
     */
    public static function RetrieveOnChat($_chatObjectID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_messageContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "messages WHERE chatobjectid = '" . $_chatObjectID . "'");
        if ($_messageContainer && isset($_messageContainer['messageid']) && !empty($_messageContainer['messageid'])) {
            try {
                $_SWIFT_MessageSurveyObject = new SWIFT_MessageSurvey($_messageContainer['messageid']);
                if ($_SWIFT_MessageSurveyObject instanceof SWIFT_MessageSurvey && $_SWIFT_MessageSurveyObject->GetIsClassLoaded()) {
                    return $_SWIFT_MessageSurveyObject;
                }
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            }
        }

        return false;
    }
}

