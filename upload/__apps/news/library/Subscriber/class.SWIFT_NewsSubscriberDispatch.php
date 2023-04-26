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

namespace News\Library\Subscriber;

use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;
use SWIFT_Exception;
use SWIFT_LanguageEngine;
use SWIFT_Library;
use SWIFT_Mail;
use SWIFT_StringHTMLToText;
use SWIFT_TemplateEngine;

/**
 * The Subscriber Dispatch Handling Class
 *
 * @property SWIFT_StringHTMLToText $StringHTMLToText
 * @property SWIFT_Mail $Mail
 * @author Varun Shoor
 */
class SWIFT_NewsSubscriberDispatch extends SWIFT_Library
{
    protected $NewsItem = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_NewsItem $_SWIFT_NewsItemObject The SWIFT_NewsItem Object Pointer
     * @throws SWIFT_Exception If Creation Fails
     */
    public function __construct(SWIFT_NewsItem $_SWIFT_NewsItemObject)
    {
        parent::__construct();

        if (!$_SWIFT_NewsItemObject instanceof SWIFT_NewsItem || !$_SWIFT_NewsItemObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $this->NewsItem = $_SWIFT_NewsItemObject;
    }

    /**
     * Send a Subscriber Dispatch
     *
     * @author Varun Shoor
     * @param string $_emailSubject The Email Subject
     * @param string $_fromName The From Name
     * @param string $_fromEmail The From Email Address
     * @param bool $_userVisibilityCustom
     * @param array $_userGroupIDList
     * @param bool $_staffVisibilityCustom
     * @param array|bool $_staffGroupIDList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Send($_emailSubject, $_fromName, $_fromEmail, $_userVisibilityCustom, $_userGroupIDList, $_staffVisibilityCustom, $_staffGroupIDList)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Subscriber_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!is_array($_userGroupIDList))
        {
            $_userGroupIDList = array();
        }

        if (!is_array($_staffGroupIDList))
        {
            $_staffGroupIDList = array();
        }

        $_emailList           = array();
        $_userEmailList       = array();
        $_subscriberContainer = array();

        /*
         * BUG FIX - Ravi Sharma
         *
         * SWIFT-2635 There should be an option of Unsubscribe in the NEWS for the Users when they receive the News by mail.
         *
         * Comments: Treating user and staff emails separately, to inject un-subscribe link.
         */
        // Subscribers

        if ($this->NewsItem->GetProperty('newstype') == SWIFT_NewsItem::TYPE_GLOBAL || $this->NewsItem->GetProperty('newstype') == SWIFT_NewsItem::TYPE_PUBLIC) {
            if ($_userVisibilityCustom == true) {

                /** BUG FIX : Saloni Dhall <saloni.dhall@opencart.com.vn>
                 *
                 * SWIFT-3239 : News is not dispatched to 'Subscriber' list if concerned news article is restricted on basis of user group
                 *
                 * Comments : There is no linking of subscribers with usergroups, while fetching it always exclude and get results on the basis of userGroupIDList, adjusted it in query so that it always consider subscribers.
                 */
                $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newssubscribers WHERE usergroupid IN (" . BuildIN($_userGroupIDList) . ") AND isvalidated = '1'");
            } else {
                $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newssubscribers WHERE isvalidated = '1'");
            }

            while ($this->Database->NextRecord()) {
                if (!in_array($this->Database->Record['email'], $_userEmailList)) {
                    $_userEmailList[]       = $this->Database->Record['email'];
                    $_subscriberContainer[] = $this->Database->Record;
                }
            }
        }

        // Staff
        if (($this->NewsItem->GetProperty('newstype') == SWIFT_NewsItem::TYPE_GLOBAL || $this->NewsItem->GetProperty('newstype') == SWIFT_NewsItem::TYPE_PRIVATE) &&
                $this->Settings->Get('nw_sendstaffemail') == '1')
        {
            if ($_staffVisibilityCustom == true)
            {
                $this->Database->Query("SELECT email FROM " . TABLE_PREFIX . "staff
                    WHERE staffgroupid IN (" . BuildIN($_staffGroupIDList) . ") AND isenabled = '1'");
            } else {
                $this->Database->Query("SELECT email FROM " . TABLE_PREFIX . "staff
                    WHERE isenabled = '1'");
            }

            while ($this->Database->NextRecord())
            {
                if (!in_array($this->Database->Record['email'], $_emailList))
                {
                    $_emailList[] = $this->Database->Record['email'];
                }
            }

        }

        $this->Load->Library('Mail:Mail');
        $this->Load->Library('String:StringHTMLToText');
        $_textContents = $this->StringHTMLToText->Convert($this->NewsItem->GetProperty('contents'));
        $_htmlContents = $this->NewsItem->GetProperty('contents');

        // Load the phrases from the database..
        $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

        // No user email?
        if (!_is_array($_emailList) and !_is_array($_userEmailList)) {
            return false;
        }

        // Users who can UnSubscribe
        foreach ($_subscriberContainer as $_key => $_subscriber) {

            $this->Template->Assign('_subscriberEmail', urlencode($_subscriber['email']));
            $this->Template->Assign('_subscriberID', $_subscriber['newssubscriberid']);
            $this->Template->Assign('_subscriberHash', substr(md5(SWIFT::Get('installationhash') . $_subscriber['email']), 0, 20));
            $this->Template->Assign('_showUnsubscribe', true);

            $this->Template->Assign('_contentsText', $_textContents);
            $this->Template->Assign('_contentsHTML', $_htmlContents);

            $_textEmailContents = $this->Template->Get('email_text', SWIFT_TemplateEngine::TYPE_DB);
            $_htmlEmailContents = $this->Template->Get('email_html', SWIFT_TemplateEngine::TYPE_DB);

            $_SWIFT_MailObject = $this->Mail->GetInstance();

            $_SWIFT_MailObject->SetFromField($_fromEmail, $_fromName);

            $_SWIFT_MailObject->SetSubjectField($_emailSubject);

            $_SWIFT_MailObject->SetDataText($_textEmailContents);
            $_SWIFT_MailObject->SetDataHTML($_htmlEmailContents, true);

            $_SWIFT_MailObject->SetToField($_subscriber['email']);
            $_SWIFT_MailObject->SendMail(true);
        }

        // Staff who cannot UnSubscribe
        $this->Template->Assign('_showUnsubscribe', false);
        $this->Template->Assign('_contentsText', $_textContents);
        $this->Template->Assign('_contentsHTML', $_htmlContents);

        $_textEmailContents = $this->Template->Get('email_text', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = $this->Template->Get('email_html', SWIFT_TemplateEngine::TYPE_DB);

        $this->Mail->SetFromField($_fromEmail, $_fromName);

        $this->Mail->SetSubjectField($_emailSubject);

        $this->Mail->SetDataText($_textEmailContents);
        $this->Mail->SetDataHTML($_htmlEmailContents, true);

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2100 Disabling the option 'Mail Send-Queue' results Fatal error: Call to a member function getFieldBody() on a non-object , while inserting new News article
         *
         */
        // Check to see if mail queue is enabled
        if ($this->Settings->Get('cpu_enablemailqueue') != 1) {
            foreach ($_emailList as $_emailAddress) {
                $this->Mail->OverrideToField($_emailAddress);
                $this->Mail->SendMail();
            }
        } else {
            foreach ($_emailList as $_emailAddress) {
                $this->Mail->AddToField($_emailAddress);
            }

            $this->Mail->SendMail(true);
        }

        return true;
    }
}
