<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser\Library\Rule;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\User\SWIFT_User;
use Parser\Library\MailParser\SWIFT_MailParser;
use Parser\Library\MailParser\SWIFT_MailParserEmail;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use Parser\Models\Rule\SWIFT_ParserRule;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_LanguageEngine;
use SWIFT_Library;
use SWIFT_Loader;
use SWIFT_Mail;
use SWIFT_TemplateEngine;
use Tickets\Models\SLA\SWIFT_SLA;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Parser Rule Manager
 *
 * @property SWIFT_UserInterface $UserInterface
 * @property SWIFT_Mail $Mail
 * @author Varun Shoor
 */
class SWIFT_ParserRuleManager extends SWIFT_Library
{
    /**
     * @var SWIFT_MailParserEmail
     */
    protected $MailParserEmail;

    /**
     * @var SWIFT_EmailQueue
     */
    protected $EmailQueue;

    /**
     * @var SWIFT_MailParser
     */
    protected $MailParser;

    /** @var bool */
    private $_stopProcessing = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param SWIFT_MailParserEmail $_SWIFT_MailParserEmailObject
     * @param SWIFT_EmailQueue      $_SWIFT_EmailQueueObject
     * @param SWIFT_MailParser      $_SWIFT_MailParserObject
     *
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function __construct(SWIFT_MailParserEmail $_SWIFT_MailParserEmailObject, SWIFT_EmailQueue $_SWIFT_EmailQueueObject, SWIFT_MailParser $_SWIFT_MailParserObject)
    {
        parent::__construct();

        if (!$_SWIFT_MailParserEmailObject instanceof SWIFT_MailParserEmail || !$_SWIFT_MailParserEmailObject->GetIsClassLoaded() ||
            !$_SWIFT_EmailQueueObject instanceof SWIFT_EmailQueue || !$_SWIFT_EmailQueueObject->GetIsClassLoaded() ||
            !$_SWIFT_MailParserObject instanceof SWIFT_MailParser || !$_SWIFT_MailParserObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->MailParserEmail = $_SWIFT_MailParserEmailObject;
        $this->EmailQueue = $_SWIFT_EmailQueueObject;
        $this->MailParser = $_SWIFT_MailParserObject;
    }

    /**
     * Retrieve the parsed properties container
     *
     * @author Varun Shoor
     *
     * @param SWIFT_Ticket $_SWIFT_TicketObject (OPTIONAL) The SWIFT_Ticket Object Pointer
     *
     * @return array The Properties Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetProperties(SWIFT_Ticket $_SWIFT_TicketObject = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_propertiesContainer = array();

        $_propertiesContainer[SWIFT_ParserRule::PARSER_SENDERNAME] = $this->MailParserEmail->GetFromName();
        $_propertiesContainer[SWIFT_ParserRule::PARSER_SENDEREMAIL] = $this->MailParserEmail->GetFromEmail();
        $_propertiesContainer[SWIFT_ParserRule::PARSER_DESTINATIONNAME] = $this->MailParserEmail->GetToName();
        $_propertiesContainer[SWIFT_ParserRule::PARSER_DESTINATIONEMAIL] = $this->MailParserEmail->GetToEmailList();
        $_propertiesContainer[SWIFT_ParserRule::PARSER_REPLYTONAME] = $this->MailParserEmail->GetReplyToName();
        $_propertiesContainer[SWIFT_ParserRule::PARSER_REPLYTOEMAIL] = $this->MailParserEmail->GetReplyToEmail();
        $_propertiesContainer[SWIFT_ParserRule::PARSER_SUBJECT] = $this->MailParserEmail->GetSubject();
        $_propertiesContainer[SWIFT_ParserRule::PARSER_RECIPIENTS] = $this->MailParserEmail->GetRecipients();
        $_propertiesContainer[SWIFT_ParserRule::PARSER_BODY] = $this->MailParserEmail->GetFinalContents();
        $_propertiesContainer[SWIFT_ParserRule::PARSER_BODYSIZE] = $this->MailParserEmail->GetFinalContentSize();
        $_propertiesContainer[SWIFT_ParserRule::PARSER_TEXTBODY] = $this->MailParserEmail->GetText();
        $_propertiesContainer[SWIFT_ParserRule::PARSER_HTMLBODY] = $this->MailParserEmail->GetHTML();
        $_propertiesContainer[SWIFT_ParserRule::PARSER_TEXTBODYSIZE] = $this->MailParserEmail->GetTextSize();
        $_propertiesContainer[SWIFT_ParserRule::PARSER_HTMLBODYSIZE] = $this->MailParserEmail->GetHTMLSize();

        // Attachments
        $_attachmentNameList = $_attachmentSizeList = array();
        $_totalAttachmentSize = 0;
        foreach ($this->MailParserEmail->GetAttachments() as $_attachmentContainer) {
            $_attachmentNameList[] = $_attachmentContainer['filename'];
            $_attachmentSizeList[] = $_attachmentContainer['size'];
            $_totalAttachmentSize += $_attachmentContainer['size'];
        }

        $_propertiesContainer[SWIFT_ParserRule::PARSER_ATTACHMENTNAME] = $_attachmentNameList;
        $_propertiesContainer[SWIFT_ParserRule::PARSER_ATTACHMENTSIZE] = $_attachmentSizeList;
        $_propertiesContainer[SWIFT_ParserRule::PARSER_TOTALATTACHMENTSIZE] = $_totalAttachmentSize;

        if ($this->MailParserEmail->GetProperty('isreply') == true) {
            $_propertiesContainer[SWIFT_ParserRule::PARSER_ISREPLY] = true;
        } else {
            $_propertiesContainer[SWIFT_ParserRule::PARSER_ISREPLY] = false;
        }

        if ($this->MailParserEmail->GetProperty('isthirdpartyreply') == true) {
            $_propertiesContainer[SWIFT_ParserRule::PARSER_ISTHIRDPARTY] = true;
        } else {
            $_propertiesContainer[SWIFT_ParserRule::PARSER_ISTHIRDPARTY] = false;
        }


        if ($this->MailParserEmail->GetProperty('isstaffreply') == true) {
            $_propertiesContainer[SWIFT_ParserRule::PARSER_ISSTAFFREPLY] = true;
        } else {
            $_propertiesContainer[SWIFT_ParserRule::PARSER_ISSTAFFREPLY] = false;
        }

        $_propertiesContainer[SWIFT_ParserRule::PARSER_BAYESCATEGORY] = $this->MailParserEmail->GetBayesianCategory();
        $_propertiesContainer[SWIFT_ParserRule::PARSER_TICKETEMAILQUEUE] = $this->EmailQueue->GetEmailQueueID();

        if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded()) {
            $_propertiesContainer[SWIFT_ParserRule::PARSER_TICKETSTATUS] = $_SWIFT_TicketObject->GetProperty('ticketstatusid');
            $_propertiesContainer[SWIFT_ParserRule::PARSER_TICKETTYPE] = $_SWIFT_TicketObject->GetProperty('tickettypeid');
            $_propertiesContainer[SWIFT_ParserRule::PARSER_TICKETPRIORITY] = $_SWIFT_TicketObject->GetProperty('priorityid');
            $_propertiesContainer[SWIFT_ParserRule::PARSER_TICKETDEPARTMENT] = $_SWIFT_TicketObject->GetProperty('departmentid');
            $_propertiesContainer[SWIFT_ParserRule::PARSER_TICKETOWNER] = $_SWIFT_TicketObject->GetProperty('ownerstaffid');
            $_propertiesContainer[SWIFT_ParserRule::PARSER_TICKETFLAGTYPE] = $_SWIFT_TicketObject->GetProperty('flagtype');

            $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();
            if ($_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                $_propertiesContainer[SWIFT_ParserRule::PARSER_TICKETUSERGROUP] = $_SWIFT_UserObject->GetProperty('usergroupid');
            }
        }

        return $_propertiesContainer;
    }

    /**
     * Execute the Pre Parse Rules
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ExecutePreParse()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ruleProperties = $this->GetProperties();

        list($_ruleActionContainer, $_stopProcessing) = SWIFT_ParserRule::ExecuteAllRules(SWIFT_ParserRule::TYPE_PREPARSE, $_ruleProperties);

        $this->_stopProcessing = $_stopProcessing;

        if (_is_array($_ruleActionContainer)) {
            // @codeCoverageIgnoreStart
            foreach ($_ruleActionContainer as $_action) {
                if ($_action['name'] == SWIFT_ParserRule::PARSERACTION_REPLY && trim($_action['typedata']) != '') {
                    $this->Load->Library('Mail:Mail');
                    // Load the phrases from the database..
                    $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
                    $this->Language->Queue('users', SWIFT_LanguageEngine::TYPE_DB);
                    $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

                    $this->Template->Assign('_contentsText', $_action['typedata']);
                    $this->Template->Assign('_contentsHTML', nl2br($_action['typedata']));

                    $_textEmailContents = $this->Template->Get('email_text', SWIFT_TemplateEngine::TYPE_DB);
                    $_htmlEmailContents = $this->Template->Get('email_html', SWIFT_TemplateEngine::TYPE_DB);

                    $this->Mail->SetFromField($this->EmailQueue->GetProperty('email'), SWIFT::Get('companyname'));
                    $this->Mail->SetToField($this->MailParserEmail->GetFromEmail());
                    $this->Mail->SetSubjectField($this->Language->Get('actionrepprefix') . $this->MailParserEmail->GetSubject());

                    $this->Mail->SetDataText($_textEmailContents);
                    $this->Mail->SetDataHTML($_htmlEmailContents);

                    $this->Mail->SendMail(false, $this->EmailQueue->GetEmailQueueID());
                } else if ($_action['name'] == SWIFT_ParserRule::PARSERACTION_FORWARD && trim($_action['typechar']) != '') {
                    $this->Load->Library('Mail:Mail');
                    // Load the phrases from the database..
                    $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
                    $this->Language->Queue('users', SWIFT_LanguageEngine::TYPE_DB);
                    $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

                    $_textEmailContents = $this->MailParserEmail->GetText();
                    $_htmlEmailContents = $this->MailParserEmail->GetHTML();

                    $this->Mail->SetFromField($this->MailParserEmail->GetFromEmail(), $this->MailParserEmail->GetFromname());
                    $this->Mail->SetToField($_action['typechar']);
                    $this->Mail->SetSubjectField($this->MailParserEmail->GetSubject());

                    /**
                     * BUG FIX - Nidhi Gupta <nidhi.gupta@kayako.com>
                     *
                     * SWIFT-2899 If the HTML email settings is set to HTML then when forwarding the email, outgoing doesn't fall back to Plain text if HTML is missing in the email
                     *
                     */
                    if (empty($_htmlEmailContents)) {
                        $this->Mail->SetDataText($_textEmailContents);
                    } else {
                        $this->Mail->SetDataHTML($_htmlEmailContents);
                    }

                    /*
                     * BUG FIX - Saloni Dhall
                     *
                     * SWIFT-2341 Attachment is removed while forwarding an email using pre-parser rule.
                     *
                     */
                    foreach ($this->MailParserEmail->GetAttachments() as $_attachmentContainer) {
                        if ($_attachmentContainer['data'] != '' && $_attachmentContainer['filename'] != '') {
                            $this->Mail->Attach($_attachmentContainer['data'], $_attachmentContainer['contenttype'], $_attachmentContainer['filename']);
                        }
                    }

                    $this->Mail->SendMail(false, $this->EmailQueue->GetEmailQueueID());

                } else if ($_action['name'] == SWIFT_ParserRule::PARSERACTION_IGNORE) {
                    /*
                     * BUG FIX - Ravi Sharma
                     *
                     * SWIFT-2193 Option in Parser Logs to display which Parser Rule blocked the Email
                     *
                     * Comments: None
                     */
                    $_parserRuleCache = $this->Cache->Get('parserrulecache');
                    $this->MailParserEmail->SetProperty('parserruletitle', $_parserRuleCache[$_action['parserruleid']]['title']);
                    $this->MailParserEmail->SetProperty('ignoreemail', true);
                } else if ($_action['name'] == SWIFT_ParserRule::PARSERACTION_NOAUTORESPONDER) {
                    $this->MailParserEmail->SetProperty('noautoresponder', true);
                } else if ($_action['name'] == SWIFT_ParserRule::PARSERACTION_NOALERTRULES) {
                    /**
                     * @todo Add the option to ignore alerts
                     */

                    $this->MailParserEmail->SetProperty('noalerts', true);
                } else if ($_action['name'] == SWIFT_ParserRule::PARSERACTION_NOTICKET) {
                    $this->MailParserEmail->SetProperty('noticketreply', true);
                }
            }
            // @codeCoverageIgnoreEnd
        }

        return true;
    }

    /**
     * Execute the Post Parse Rules
     *
     * @author Varun Shoor
     *
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ExecutePostParse(SWIFT_Ticket $_SWIFT_TicketObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        /**
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-4484 'Stop processing other rules' setting gets violated in Email Parser Rules
         *
         * Comment - Checking whether we need to stop the processing the PostParse rule execution
         */
        if ($this->_stopProcessing == true) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $_ruleProperties = $this->GetProperties($_SWIFT_TicketObject);
        list($_ruleActionContainer) = SWIFT_ParserRule::ExecuteAllRules(SWIFT_ParserRule::TYPE_POSTPARSE, $_ruleProperties);

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_staffCache = $this->Cache->Get('staffcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');
        $_slaPlanCache = $this->Cache->Get('slaplancache');

        if (_is_array($_ruleActionContainer)) {
            // @codeCoverageIgnoreStart
            foreach ($_ruleActionContainer as $_action) {
                if ($_action['name'] == SWIFT_ParserRule::PARSERACTION_DEPARTMENT && !empty($_action['typeid']) && isset($_departmentCache[$_action['typeid']])) {
                    $_SWIFT_TicketObject->SetDepartment($_action['typeid']);
                } elseif ($_action['name'] == SWIFT_ParserRule::PARSERACTION_OWNER && !empty($_action['typeid']) && isset($_staffCache[$_action['typeid']])) {
                    $_SWIFT_TicketObject->SetOwner($_action['typeid']);
                } elseif ($_action['name'] == SWIFT_ParserRule::PARSERACTION_TICKETTYPE && !empty($_action['typeid']) && isset($_ticketTypeCache[$_action['typeid']])) {
                    $_SWIFT_TicketObject->SetType($_action['typeid']);
                } elseif ($_action['name'] == SWIFT_ParserRule::PARSERACTION_STATUS && !empty($_action['typeid']) && isset($_ticketStatusCache[$_action['typeid']])) {
                    $_SWIFT_TicketObject->SetStatus($_action['typeid']);
                } elseif ($_action['name'] == SWIFT_ParserRule::PARSERACTION_PRIORITY && !empty($_action['typeid']) && isset($_ticketPriorityCache[$_action['typeid']])) {
                    $_SWIFT_TicketObject->SetPriority($_action['typeid']);
                } elseif ($_action['name'] == SWIFT_ParserRule::PARSERACTION_SLAPLAN && !empty($_action['typeid']) && isset($_slaPlanCache[$_action['typeid']])) {
                    SWIFT_Loader::LoadModel('SLA:SLA', APP_TICKETS);
                    $_SWIFT_TicketObject->SetSLA(new SWIFT_SLA(new SWIFT_DataID($_action['typeid'])));
                } elseif ($_action['name'] == SWIFT_ParserRule::PARSERACTION_FLAGTICKET && !empty($_action['typeid'])) {
                    $_SWIFT_TicketObject->SetFlag($_action['typeid']);
                } elseif ($_action['name'] == SWIFT_ParserRule::PARSERACTION_MOVETOTRASH && !empty($_action['typeid'])) {
                    $_SWIFT_TicketObject->Trash();
                } elseif ($_action['name'] == SWIFT_ParserRule::PARSERACTION_ADDNOTE && trim($_action['typedata']) != '') {
                    $_SWIFT_TicketObject->CreateNote(null, $_action['typedata'], 1, 'ticket');
                } elseif ($_action['name'] == SWIFT_ParserRule::PARSERACTION_ADDTAGS && trim($_action['typedata']) != '') {
                    $_tagContainer = json_decode($_action['typedata']);
                    if (!_is_array($_tagContainer)) {
                        continue;
                    }

                    SWIFT_Tag::AddTags(SWIFT_TagLink::TYPE_TICKET, $_SWIFT_TicketObject->GetTicketID(),
                        $_tagContainer, false);
                } elseif ($_action['name'] == SWIFT_ParserRule::PARSERACTION_REMOVETAGS && trim($_action['typedata']) != '') {
                    $_tagContainer = json_decode($_action['typedata']);
                    if (!_is_array($_tagContainer)) {
                        continue;
                    }

                    SWIFT_Tag::RemoveTags(SWIFT_TagLink::TYPE_TICKET, array($_SWIFT_TicketObject->GetTicketID()),
                        $_tagContainer, false);
                } elseif ($_action['name'] == SWIFT_ParserRule::PARSERACTION_PRIVATE && !empty($_action['typeid'])) {
                    $this->MailParserEmail->SetProperty('isprivate', true);
                }
            }
            // @codeCoverageIgnoreEnd
        }

        return true;
    }
}

?>
