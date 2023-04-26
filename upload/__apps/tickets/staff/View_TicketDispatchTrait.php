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

namespace Tickets\Staff;

use SWIFT;
use SWIFT_App;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Library\Attachment\SWIFT_AttachmentRenderer;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Library\CustomField\SWIFT_CustomFieldRenderer;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use SWIFT_Hook;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Draft\SWIFT_TicketDraft;
use Tickets\Models\Lock\SWIFT_TicketPostLock;
use Tickets\Models\Recipient\SWIFT_TicketRecipient;
use Tickets\Library\View\SWIFT_TicketViewRenderer;
use Base\Models\User\SWIFT_User;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;

trait View_TicketDispatchTrait {
    /**
     * Render the Reply/Forward Tabs
     *
     * @author Varun Shoor
     * @param mixed $_tabType The Tab Type
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param SWIFT_User $_SWIFT_UserObject
     * @param SWIFT_UserInterfaceTab $_TabObject
     * @param array $_ticketWatchContainer (OPTIONAL) The Ticket Watch Container
     * @param int|bool $_departmentID (OPTIONAL) The Department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderDispatchTab($_tabType, SWIFT_UserInterfaceTab $_TabObject, SWIFT_Ticket $_SWIFT_TicketObject = null, $_SWIFT_UserObject = null,
        $_ticketWatchContainer = array(), $_departmentID = false) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketDraftContents = $_destinationDescription = '';

        if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded() &&
            $_SWIFT_TicketObject->GetProperty('hasdraft') == '1') {
            $_SWIFT_TicketDraftObject = SWIFT_TicketDraft::Retrieve($_SWIFT_TicketObject, $_SWIFT->Staff);
            if ($_SWIFT_TicketDraftObject instanceof SWIFT_TicketDraft && $_SWIFT_TicketDraftObject->GetIsClassLoaded()) {

                $_ticketDraftContents = $_SWIFT->Emoji->decode($_SWIFT_TicketDraftObject->GetProperty('contents'));
            }
        } else if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded()) {
            $_ticketDraftContents = SWIFT_TicketPostLock::RetrieveOnTicketAndStaff($_SWIFT_TicketObject, $_SWIFT->Staff);
        }

        $_SWIFT_TicketViewObject = SWIFT_TicketViewRenderer::GetDefaultTicketViewObject();

        // This is necessary to show the right queue list when opening a ticket which wasnt created through a email queue
        if (!$_departmentID && $_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded()) {
            $_departmentID = $_SWIFT_TicketObject->GetProperty('departmentid');
        }

        $_tabPrefix = 'reply';
        if ($_tabType == self::TAB_FORWARD) {
            $_tabPrefix = 'forward';
        } else if ($_tabType == self::TAB_NEWTICKET_EMAIL || $_tabType == self::TAB_NEWTICKET_USER) {
            $_tabPrefix = 'newticket';
        }

        $_ticketStatusCache = (array) $this->Cache->Get('statuscache');
        $_ticketPriorityCache = (array) $this->Cache->Get('prioritycache');
        $_templateGroupCache = (array) $this->Cache->Get('templategroupcache');
        $_departmentCache = (array) $this->Cache->Get('departmentcache');

        $_departmentTitle = $this->Language->Get('na');

        if (isset($_departmentCache[$_departmentID])) {
            $_departmentTitle = $_departmentCache[$_departmentID]['title'];
        }

        $_ticketEmailQueueID = 0;
        $_recipientContainer = $_extendedDataContainer = array();
        $_ticketID = 0;
        if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded()) {
            if ($_SWIFT_TicketObject->GetProperty('replyto') != '' && $_SWIFT_TicketObject->GetProperty('creator') == SWIFT_Ticket::CREATOR_STAFF) {
                $_destinationDescription = htmlspecialchars($_SWIFT_TicketObject->GetProperty('replyto'));
            } else {
                if ($_SWIFT_TicketObject->GetProperty('fullname') != '') {
                    $_destinationDescription = text_to_html_entities($_SWIFT_TicketObject->GetProperty('fullname') . ' (' . $_SWIFT_TicketObject->GetProperty('email') . ')');
                } else {
                    $_destinationDescription = htmlspecialchars($_SWIFT_TicketObject->GetProperty('email'));
                }
            }

            $_ticketID = (int) ($_SWIFT_TicketObject->GetTicketID());
            $_recipientContainer = SWIFT_TicketRecipient::RetrieveOnTicket($_SWIFT_TicketObject);
            $_ticketEmailQueueID = (int) ($_SWIFT_TicketObject->GetProperty('emailqueueid'));

            if ($_SWIFT_TicketViewObject->GetProperty('setasowner') == '1') {
                $_extendedDataContainer['_exOwnerStaffID'] = $_SWIFT->Staff->GetStaffID();
            }

            $_defaultTicketStatusOnReply = $_SWIFT_TicketViewObject->GetProperty('defaultstatusonreply');

            if ($_defaultTicketStatusOnReply != '0' && isset($_ticketStatusCache[$_defaultTicketStatusOnReply]) &&
                ($_ticketStatusCache[$_defaultTicketStatusOnReply]['departmentid'] == '0' || $_ticketStatusCache[$_defaultTicketStatusOnReply]['departmentid'] == $_SWIFT_TicketObject->GetProperty('departmentid'))) {
                $_extendedDataContainer['_exTicketStatusID'] = $_defaultTicketStatusOnReply;
            }

        } else {
            $_extendedDataContainer['_exDepartmentID'] = $_departmentID;
            $_extendedDataContainer['_exOwnerStaffID'] = $_SWIFT->Staff->GetStaffID();

            // Calculate Default Values
            foreach ($_templateGroupCache as $_templateGroupID => $_templateGroupContainer) {
                if ($_templateGroupContainer['isdefault'] == '1') {
                    $_extendedDataContainer['_exTicketStatusID'] = $_templateGroupContainer['ticketstatusid'];
                    $_extendedDataContainer['_exTicketPriorityID'] = $_templateGroupContainer['priorityid'];
                    $_extendedDataContainer['_exTicketTypeID'] = $_templateGroupContainer['tickettypeid'];
                }
            }
        }

        $_ticketStatusContainer = false;
        /// Get Default Status from Ticket
        if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded() && isset($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')])) {
            $_ticketStatusContainer = $_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')];

            // Retrieve Default Status from Master Group
        } else if (isset($_extendedDataContainer['_exTicketStatusID']) && isset($_ticketStatusCache[$_extendedDataContainer['_exTicketStatusID']])) {
            $_ticketStatusContainer = $_ticketStatusCache[$_extendedDataContainer['_exTicketStatusID']];
        }

        $_ticketPriorityContainer = false;

        // Get Default Priority from Ticket
        if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded() && isset($_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')])) {
            $_ticketPriorityContainer = $_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')];

            // Get Default Priority from Master Template Group
        } else if (isset($_extendedDataContainer['_exTicketPriorityID']) && isset($_ticketPriorityCache[$_extendedDataContainer['_exTicketPriorityID']])) {
            $_ticketPriorityContainer = $_ticketPriorityCache[$_extendedDataContainer['_exTicketPriorityID']];
        }

        $_titleBackgroundColor = '#626262';
        if ($_ticketStatusContainer) {
            $_titleBackgroundColor = $_ticketStatusContainer['statusbgcolor'];
        }

        // Override the color if the default status on reply is different than the active ticket status
        if (isset($_extendedDataContainer['_exTicketStatusID']) &&
            (($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_extendedDataContainer['_exTicketStatusID'] != $_SWIFT_TicketObject->GetProperty('ticketstatusid') && isset($_ticketStatusCache[$_extendedDataContainer['_exTicketStatusID']])) ||
                (!$_SWIFT_TicketObject instanceof SWIFT_Ticket && isset($_ticketStatusCache[$_extendedDataContainer['_exTicketStatusID']])))) {
            $_titleBackgroundColor = $_ticketStatusCache[$_extendedDataContainer['_exTicketStatusID']]['statusbgcolor'];
        }

        $_priorityBackgroundColor = false;
        if ($_ticketPriorityContainer && isset($_ticketPriorityContainer['bgcolorcode']) && !empty($_ticketPriorityContainer['bgcolorcode'])) {
            $_priorityBackgroundColor = $_ticketPriorityContainer['bgcolorcode'];
        }


        $_ticketCCEmailContainer = $_ticketBCCEmailContainer = $_ticketThirdPartyEmailContainer = $_ticketForwardContainer = array();

        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_THIRDPARTY])) {
            $_ticketThirdPartyEmailContainer = $_recipientContainer[SWIFT_TicketRecipient::TYPE_THIRDPARTY];
        }

        if (count($_ticketThirdPartyEmailContainer) == 1) {
            $_ticketForwardContainer = $_ticketThirdPartyEmailContainer;
        }

        /**
         * BUG Fix : Abhinav Kumar <abhinav.kumar@kayako.com>
         *
         * SWIFT-2375: While forwarding a ticket to a third party, 'CC' users should not be selected by default.
         *
         * Comments: use SWIFT_UserInterfaceTab::TextMultipleAutoCompleteExtended() for the forward tab
         */
        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC])) {
            if ($_tabType == self::TAB_FORWARD) {
                foreach ($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC] as $_key => $_val) {
                    $_ticketCCEmail            = array();
                    $_ticketCCEmail['key']        = $_key;
                    $_ticketCCEmail['value']        = $_val;
                    $_ticketCCEmail['checked']    = '';

                    $_ticketCCEmailContainer[] = $_ticketCCEmail;
                }
            } else {
                $_ticketCCEmailContainer = $_recipientContainer[SWIFT_TicketRecipient::TYPE_CC];
            }
        }

        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC])) {
            $_ticketBCCEmailContainer = $_recipientContainer[SWIFT_TicketRecipient::TYPE_BCC];
        }

        $_emailQueueCache = (array) $this->Cache->Get('queuecache');

        $_TabObject->SetColumnWidth('130');

        $_optionsContainer = array();
        $_index = 0;
        $_queueFrom = false;

        /*
         * BUG FIX - Ashish Kataria
         *
         * SWIFT-1988 From address during post reply is set to email queue of lowest queue ID on department where no email queue specified
         *
         * Comments: Set $_selected to true when we get selected option
         */
        $_selected = false;

        // Attempt to retrieve email queue based on department
        $emailCacheList = isset($_emailQueueCache['list'])?(array)$_emailQueueCache['list']:[];
        if (!empty($_departmentID) && _is_array($emailCacheList)) {
            /**
             * @var int $_emailQueueID
             * @var array $_emailQueueContainer
             */
            foreach ($emailCacheList as $_emailQueueID => $_emailQueueContainer) {
                if ($_emailQueueContainer['departmentid'] == $_departmentID && $_emailQueueContainer['isenabled'] == '1') {
                    /*
                     * BUG FIX - Ashish Kataria <ashish.kataria@kayako.com>, Nidhi Gupta <nidhi.gupta@kayako.com>
                     *
                     * SWIFT-2520 Ticket Reply FROM does not use mail queue FROM NAME
                     *
                     * Comments: Default value set to Staff full name, check for customfromname if found then replace the fromNameSuffix
                     */
                    $_fromNameSuffix = !empty($_emailQueueContainer['customfromname']) ? $_emailQueueContainer['customfromname'] : $_SWIFT->Staff->GetProperty('fullname');
                    $_optionsContainer[$_index]['title'] = $_fromNameSuffix . ' (' . $_emailQueueContainer['email'] . ')';
                    $_optionsContainer[$_index]['value'] = $_emailQueueID;
                    if ($_ticketEmailQueueID == $_emailQueueID) {
                        $_optionsContainer[$_index]['selected'] = true;
                        $_selected = true;
                    }

                    $_index++;
                    $_queueFrom = true;
                    $_selected = true;
                }
            }
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1667 Email queue address linked with Parent department should also be availbale for sub-department
         *
         * Comments: If we are unable to find an email for this department and we have a parent department, we try to match up against that
         */
        $_parentDepartmentID = 0;
        if (!empty($_departmentCache[$_departmentID]['parentdepartmentid'])) {
            $_parentDepartmentID = $_departmentCache[$_departmentID]['parentdepartmentid'];
        }

        if (!$_queueFrom && !empty($_parentDepartmentID)) {
            foreach ($emailCacheList as $_emailQueueID => $_emailQueueContainer) {
                if ($_emailQueueContainer['departmentid'] == $_parentDepartmentID && $_emailQueueContainer['isenabled'] == '1') {
                    /*
                     * BUG FIX - Ashish Kataria <ashish.kataria@kayako.com>, Nidhi Gupta <nidhi.gupta@kayako.com>
                     *
                     * SWIFT-2520 Ticket Reply FROM does not use mail queue FROM NAME
                     *
                     * Comments: Default value set to Staff full name, check for customfromname if found then replace the fromNameSuffix
                     */
                    $_fromNameSuffix = !empty($_emailQueueContainer['customfromname']) ? $_emailQueueContainer['customfromname'] : $_SWIFT->Staff->GetProperty('fullname');
                    $_optionsContainer[$_index]['title'] = $_fromNameSuffix . ' (' . $_emailQueueContainer['email'] . ')';
                    $_optionsContainer[$_index]['value'] = $_emailQueueID;
                    $_optionsContainer[$_index]['selected'] = true;

                    $_index++;

                    $_queueFrom = true;
                }
            }
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1317 From address should also check the ticket template group id with 'Queue Template Group' name under email queue.
         *
         * Comments: Try to compare the email queue based on template group if we couldnt match on any of the scenarios above
         */
        $_ticketTemplateGroupID = 0;
        if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded()) {
            $_ticketTemplateGroupID = (int) ($_SWIFT_TicketObject->GetProperty('tgroupid'));
        }
        /*
         * BUG FIX - Saloni Dhall
         *
         * SWIFT-1988 From address during post reply is set to email queue of lowest queue ID on department .
         */
        if (!$_queueFrom && !empty($_ticketTemplateGroupID)) {
            foreach ($emailCacheList as $_emailQueueID => $_emailQueueContainer) {
                if ($_ticketTemplateGroupID == $_emailQueueContainer['tgroupid'] && $_emailQueueContainer['isenabled'] == '1' && $_emailQueueContainer['departmentid'] == $_departmentID) {
                    /*
                     * BUG FIX - Ashish Kataria <ashish.kataria@kayako.com>, Nidhi Gupta <nidhi.gupta@kayako.com>
                     *
                     * SWIFT-2520 Ticket Reply FROM does not use mail queue FROM NAME
                     *
                     * Comments: Default value set to Staff full name, check for customfromname if found then replace the fromNameSuffix
                     */
                    $_fromNameSuffix = !empty($_emailQueueContainer['customfromname']) ? $_emailQueueContainer['customfromname'] : $_SWIFT->Staff->GetProperty('fullname');
                    $_optionsContainer[$_index]['title'] = $_fromNameSuffix . ' (' . $_emailQueueContainer['email'] . ')';
                    $_optionsContainer[$_index]['value'] = $_emailQueueID;
                    $_optionsContainer[$_index]['selected'] = true;

                    $_index++;

                    $_queueFrom = true;
                }
            }
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1301 Improvement in From Email Address while replying to a ticket from Staff CP
         *
         * Comments: If we couldnt find a queue linked with this ticket then we try to match one up using the domain name
         */
        if (!$_queueFrom && !empty($_ticketEmailQueueID) && isset($emailCacheList[$_ticketEmailQueueID]['email'])) {
            $_ticketEmailQueueAddress = mb_strtolower($emailCacheList[$_ticketEmailQueueID]['email']);

            foreach ($emailCacheList as $_emailQueueID => $_emailQueueContainer) {
                $_emailQueueAddress = mb_strtolower($_emailQueueContainer['email']);

                if (mb_strstr($_ticketEmailQueueAddress, '@') == mb_strstr($_emailQueueAddress, '@') && $_emailQueueContainer['isenabled'] == '1' && $_emailQueueContainer['departmentid'] == $_departmentID) {
                    /*
                     * BUG FIX - Ashish Kataria <ashish.kataria@kayako.com>, Nidhi Gupta <nidhi.gupta@kayako.com>
                     *
                     * SWIFT-2520 Ticket Reply FROM does not use mail queue FROM NAME
                     *
                     * Comments: Default value set to Staff full name, check for customfromname if found then replace the fromNameSuffix
                     */
                    $_fromNameSuffix                        = !empty($_emailQueueContainer['customfromname']) ? $_emailQueueContainer['customfromname'] : $_SWIFT->Staff->GetProperty('fullname');
                    $_optionsContainer[$_index]['title']    = $_fromNameSuffix . ' (' . $_emailQueueAddress . ')';
                    $_optionsContainer[$_index]['value']    = $_emailQueueID;
                    $_optionsContainer[$_index]['selected'] = true;

                    $_index++;

                    $_queueFrom = true;
                }
            }
        }

        // Use Default Return Email
        $_optionsContainer[$_index]['title'] = $_SWIFT->Staff->GetProperty('fullname') . ' (' . $this->Settings->Get('general_returnemail') . ')';
        $_optionsContainer[$_index]['value'] = '0';

        if (!$_selected) {
            $_optionsContainer[$_index]['selected'] = true;
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcanaddfromaddress') == '1') {
            $_index++;
            $_optionsContainer[$_index]['title'] = $_SWIFT->Staff->GetProperty('fullname') . ' (' . $_SWIFT->Staff->GetProperty('email') . ')';
            $_optionsContainer[$_index]['value'] = '-1';
        }

        $_TabObject->Select($_tabPrefix . 'from', $this->Language->Get('dispatchfrom'), '', $_optionsContainer);

        if ($_tabType == self::TAB_REPLY) {
            $_TabObject->DefaultDescriptionRow($this->Language->Get('dispatchto'), '', '<span class="tabledescriptionbig">' . $_destinationDescription . '</span>');
        }

        if ($_tabType == self::TAB_NEWTICKET_EMAIL || $_tabType == self::TAB_NEWTICKET_USER) {
            $_TabObject->Text($_tabPrefix . 'subject', $this->Language->Get('dispatchsubject'), '', '', 'text', '60');
        }

        if ($_tabType == self::TAB_FORWARD || $_tabType == self::TAB_NEWTICKET_EMAIL) {
            $_tagSuffixHTML = '';
            if ($_tabType == self::TAB_FORWARD) {
                $_tagSuffixHTML = '<label for="opt' . $_tabPrefix . '_addrecipients%tagid">&nbsp;&nbsp;[ <input type="checkbox" id="opt' . $_tabPrefix . '_addrecipients%tagid" name="opt' . $_tabPrefix . '_addrecipients[]" value="%tagvalue"> ' . $this->Language->Get('tagaddtorecp') . ' ]</label>';

                /*
                 * BUG FIX - Ravi Sharma
                 *
                 * SWIFT-3402: Edit Subject Field option should be available on ticket forwarding page.
                 *
                 * Comments: Adding subject field in forward tab.
                 */
                $_TabObject->Text($_tabPrefix . 'subject', $this->Language->Get('dispatchsubject'), '', $this->Emoji->Decode($_SWIFT_TicketObject->GetProperty('subject')), 'text', '60');
            }
            $_TabObject->TextMultipleAutoComplete($_tabPrefix . 'to', $this->Language->Get('dispatchto'), '', '/Tickets/Ajax/SearchEmail', $_ticketForwardContainer, 'fa-envelope-o',
                false, false, 2, false, false, false, $_tagSuffixHTML, array('containemail' => true));
        } else if ($_tabType == self::TAB_NEWTICKET_USER) {
            $_extendedHTML = '&nbsp;&nbsp;<a href="' . "javascript: UICreateWindow('" . SWIFT::Get('basename') . "/Base/User/QuickInsert/" .
                "', 'userquickinsert', '" . $_SWIFT->Language->Get('winuserquickinsert') . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 850, 610, true, this);" . '" ><i class="fa fa-plus-circle"></i>' . $this->Language->Get('dispatchnewuser') . '</a><div id="quickinsertuserdiv" style="display: none;"></div>';
            $_TabObject->TextAutoComplete('userid', $this->Language->Get('dispatchuser'), '/Base/User/AjaxSearch', '', '', 0, '', '30', 0, $_extendedHTML);
        }

        /**
         * BUG Fix : Abhinav Kumar <abhinav.kumar@kayako.com>
         *
         * SWIFT-2375: While forwarding a ticket to a third party, 'CC' users should not be selected by default.
         *
         * Comments: use SWIFT_UserInterfaceTab::TextMultipleAutoCompleteExtended() for the forward tab
         */
        if ($_tabType == self::TAB_FORWARD) {
            $_TabObject->TextMultipleAutoCompleteExtended($_tabPrefix . 'cc', $this->Language->Get('dispatchcc'), '', '/Tickets/Ajax/SearchEmail', $_ticketCCEmailContainer, 'fa-envelope-o', false, false, 2, false, false, true, '', array('containemail' => true));
        } else {
            $_TabObject->TextMultipleAutoComplete($_tabPrefix . 'cc', $this->Language->Get('dispatchcc'), '', '/Tickets/Ajax/SearchEmail', $_ticketCCEmailContainer, 'fa-envelope-o', false, false, 2, false, false, true, '', array('containemail' => true));
        }

        if (_is_array($_ticketBCCEmailContainer))
        {
            $_TabObject->TextMultipleAutoComplete($_tabPrefix . 'bcc', $this->Language->Get('dispatchbcc'), '', '/Tickets/Ajax/SearchEmail', $_ticketBCCEmailContainer, 'fa-envelope-o', false, false, 2, false, false, true, '', array('containemail' => true));
        }

        // Begin Quick Insert
        $_quickInsertHTML = '<div class="swifttextquickinsertdiv">';
        $_quickInsertHTML .= '<div class="qipadding"><input type="text" class="swifttextautocompleteinput" name="qi' . $_tabPrefix . '_macro" id="qi' . $_tabPrefix . '_macro" value="' . $this->Language->Get('qimacro') . '" autocomplete="off" size="40" /></div>';
        $_quickInsertHTML .= '<span id="qi' . $_tabPrefix . '_macromenu">&nbsp;</span>';
        $_quickInsertHTML .= '</div>';

        if (SWIFT_App::IsInstalled(APP_KNOWLEDGEBASE)) {
            $_quickInsertHTML .= '<div class="swifttextquickinsertdiv">';
            $_quickInsertHTML .= '<div class="qipadding"><input type="text" class="swifttextautocompleteinput" name="qi' . $_tabPrefix . '_knowledgebase" id="qi' . $_tabPrefix . '_knowledgebase" value="' . $this->Language->Get('qiknowledgebase') . '" autocomplete="off" size="40" /></div>';
            $_quickInsertHTML .= '<span id="qi' . $_tabPrefix . '_knowledgebasemenu">&nbsp;</span>';
            $_quickInsertHTML .= '</div>';
        }

        $_TabObject->DefaultDescriptionRow($this->Language->Get('quickinsert'), '', $_quickInsertHTML);

        // Begin Properties
        $_dividerHTML = '<div class="ticketreleasepropertiesdivider"><img src="' . SWIFT::Get('themepathimages') . 'ticketpropertiesdivider.png" align="middle" border="0" /></div>';
        $_renderHTML = '<tr><td colspan="2"><div class="ticketreplycontainer">';
        $_renderHTML .= '<div class="ticketreplyproperties" id="' . $_tabPrefix . 'ticketproperties" style="background-color: ' . htmlspecialchars($_titleBackgroundColor) . ';">';

        // Departments
        if ($_tabType == self::TAB_NEWTICKET_EMAIL || $_tabType == self::TAB_NEWTICKET_USER) {
            $_renderHTML .= '<div class="ticketgeneralpropertiesobject"><div class="newticketpropertiestitle">' . $this->Language->Get('proptitledepartment') . '</div>';
            $_renderHTML .= '<div class="newticketpropertiesselect"><select id="' . $_tabPrefix . '_departmentid" name="' . $_tabPrefix . 'departmentid" class="swiftselect" style="width:160px;" onchange="javascript: UpdateTicketStatusDiv(this, \'' . $_tabPrefix . 'ticketstatusid\', false, false, \'' . $_tabPrefix . 'ticketproperties\'); UpdateTicketTypeDiv(this, \'' . $_tabPrefix . 'tickettypeid\', false, false); UpdateTicketOwnerDiv(this, \'' . $_tabPrefix . 'ownerstaffid\', false, false);">';
            $_renderHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_DEPARTMENT, $_extendedDataContainer);
            $_renderHTML .= '</select></div></div>';
        } else {
            $_renderHTML .= '<div class="ticketreplypropertiesobject"><div class="ticketreplypropertiestitle">' . $this->Language->Get('proptitledepartment') . '</div>';
            $_renderHTML .= '<div class="ticketreplypropertiesselect"><select id="' . $_tabPrefix . '_departmentid" name="' . $_tabPrefix . 'departmentid" class="swiftselect" style="width:160px;" onchange="javascript: UpdateTicketStatusDiv(this, \'' . $_tabPrefix . 'ticketstatusid\', false, false, \'' . $_tabPrefix . 'ticketproperties\'); UpdateTicketTypeDiv(this, \'' . $_tabPrefix . 'tickettypeid\', false, false); UpdateTicketOwnerDiv(this, \'' . $_tabPrefix . 'ownerstaffid\', false, false);">';
            $_renderHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_DEPARTMENT, $_extendedDataContainer);
            $_renderHTML .= '</select></div></div>';
        }
        $_renderHTML .= $_dividerHTML;

        // Owner
        $_ownerSelectHTML = '<div class="ticketreplypropertiesselect"><div id="' . $_tabPrefix . 'ownerstaffid_container"><select id="select' . $_tabPrefix . 'ownerstaffid" name="' . $_tabPrefix . 'ownerstaffid" class="swiftselect" style="width:160px;">';
        $_ownerSelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_OWNER, $_extendedDataContainer);
        $_ownerSelectHTML .= '</select></div></div>';

        $_renderHTML .= '<div class="ticketreplypropertiesobject"><div class="ticketreplypropertiestitle">' . $this->Language->Get('proptitleowner') . '</div>' . $_ownerSelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Type
        $_ticketTypeSelectHTML = '<div class="ticketreplypropertiesselect"><div id="' . $_tabPrefix . 'tickettypeid_container"><select id="select' . $_tabPrefix . 'tickettypeid" name="' . $_tabPrefix . 'tickettypeid" class="swiftselect" style="width:160px;">';
        $_ticketTypeSelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_TYPE, $_extendedDataContainer);
        $_ticketTypeSelectHTML .= '</select></div></div>';

        $_renderHTML .= '<div class="ticketreplypropertiesobject"><div class="ticketreplypropertiestitle">' . $this->Language->Get('proptitletype') . '</div>' . $_ticketTypeSelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Status
        $_ticketStatusSelectHTML = '<div class="ticketreplypropertiesselect"><div id="' . $_tabPrefix . 'ticketstatusid_container"><select id="select' . $_tabPrefix . 'ticketstatusid" onchange="javascript: ResetStatusParentColor(this, \'' . $_tabPrefix . 'ticketproperties\');" name="' . $_tabPrefix . 'ticketstatusid" class="swiftselect" style="width:160px;">';
        $_ticketStatusSelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_STATUS, $_extendedDataContainer);
        $_ticketStatusSelectHTML .= '</select></div></div>';

        $_renderHTML .= '<div class="ticketreplypropertiesobject"><div class="ticketreplypropertiestitle">' . $this->Language->Get('proptitlestatus') . '</div>' . $_ticketStatusSelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        // Priority
        $_ticketPrioritySelectHTML = '<div class="ticketreplypropertiesselect"><select id="' . $_tabPrefix . '_ticketpriorityid" name="' . $_tabPrefix . 'ticketpriorityid" onchange="javascript: ResetPriorityParentColor(this, \'' . $_tabPrefix . 'priorityproperties\');" class="swiftselect" style="width:160px;">';
        $_ticketPrioritySelectHTML .= $this->GetSelectOptions($_SWIFT_TicketObject, self::TYPE_PRIORITY, $_extendedDataContainer);
        $_ticketPrioritySelectHTML .= '</select></div>';

        $_renderHTML .= '<div class="ticketreplypropertiesobject" id="' . $_tabPrefix . 'priorityproperties"' . IIF(!empty($_priorityBackgroundColor), ' style="background-color: ' . htmlspecialchars($_priorityBackgroundColor) . ';"') . '><div class="ticketreplypropertiestitle">' . $this->Language->Get('proptitlepriority') . '</div>' . $_ticketPrioritySelectHTML . '</div>';
        $_renderHTML .= $_dividerHTML;

        $_renderHTML .= '</div>';

        $_renderHTML .= '</div></td></tr>';

        $_TabObject->RowHTML($_renderHTML);

        // Tags
        $_ticketTagContainer = array();
        if ($_SWIFT->Staff->GetPermission('staff_canupdatetags') != '0') {
            if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded()) {
                $_ticketTagContainer = SWIFT_Tag::GetTagList(SWIFT_TagLink::TYPE_TICKET, $_SWIFT_TicketObject->GetTicketID());
            }

            $_TabObject->TextMultipleAutoComplete('' . $_tabPrefix . 'tags', false, false, '/Base/Tags/QuickSearch', $_ticketTagContainer, 'fa-tags', 'gridrow2', true, 2, false, true);
        }

        if ($_tabType == self::TAB_NEWTICKET_EMAIL || $_tabType == self::TAB_NEWTICKET_USER) {
            $this->Controller->CustomFieldRendererStaff->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, SWIFT_UserInterface::MODE_INSERT,
                array(SWIFT_CustomFieldGroup::GROUP_STAFFTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET), $_TabObject,
                0, $_POST['departmentid']);
        }

        $_TabObject->Title($this->Language->Get('dispatchcontents'), 'doublearrows.gif');

        $_textAreaDataSuffix = '<div id="' . $_tabPrefix . 'attachmentlistcontainer">';
        if ($_tabType == self::TAB_FORWARD)
        {
            $_textAreaDataSuffix .= SWIFT_AttachmentRenderer::RenderCheckbox(SWIFT_Attachment::LINKTYPE_TICKETPOST, $_SWIFT_TicketObject->GetTicketPostIDList(true), $_tabPrefix . 'attachmentslist');;
        }
        $_textAreaDataSuffix .= '</div>';

        $_TabObject->TextArea($_tabPrefix . 'contents', '', '', $_ticketDraftContents, '30', '14', false, '', '<div id="ticket' . $_tabPrefix . 'lockcontainer' .  ($_ticketID) . '"></div>' . $_textAreaDataSuffix);

        $_sendMailCheckedStatus = ' checked ';
        if ($_tabType == self::TAB_NEWTICKET_USER) {
            $_sendMailCheckedStatus = ' ';
        }

        $_extendedOptions = '<tr class="tablerow1_tr"><td align="left" valign="top" colspan="2" class="tablerow1">';

        if ($_tabType == self::TAB_NEWTICKET_USER) {
            $_extendedOptions .= '&nbsp;&nbsp;&nbsp;&nbsp;<label for="' . $_tabPrefix . 'isphone"><input type="checkbox" value="1" id="' . $_tabPrefix . 'isphone" class="swiftcheckbox" name="opt' . $_tabPrefix . '_isphone" checked />&nbsp;&nbsp;<img src="' . SWIFT::Get('themepathimages') . 'icon_phonesmall.gif' . '" align="absmiddle" border="0" /> ' . $this->Language->Get('dispatchisphone') . '</label>';
        }

        /**
         * BUG FIX: Parminder Singh
         *
         * SWIFT-1523: "Send mail" checkmark on Forward not used
         *
         * Comments: There is no need of Send Mail check box while forwarding
         */
        if ($_tabType != self::TAB_FORWARD) {
            $_extendedOptions .= '&nbsp;&nbsp;&nbsp;&nbsp;<label for="' . $_tabPrefix . 'sendmail"><input type="checkbox" value="1" id="' . $_tabPrefix . 'sendmail" class="swiftcheckbox" name="opt' . $_tabPrefix . '_sendemail"' . $_sendMailCheckedStatus . '/>&nbsp;&nbsp;<i class="fa fa-envelope" aria-hidden="true"></i>' . $this->Language->Get('dispatchsendmail') . '</label>';
        }

        if ($_tabType != self::TAB_NEWTICKET_USER && $_tabType != self::TAB_NEWTICKET_EMAIL) {
            $_privateChecked = false;
            $_extendedOptions .= '&nbsp;&nbsp;&nbsp;&nbsp;<label for="' . $_tabPrefix . 'private"><input type="checkbox" value="1" id="' . $_tabPrefix . 'private" class="swiftcheckbox" name="opt' . $_tabPrefix . '_private"' . $_privateChecked . '/>&nbsp;&nbsp;<i class="fa fa-lock" aria-hidden="true"></i>' . $this->Language->Get('dispatchprivate') . '</label>';
        }

        if ($_tabType == self::TAB_NEWTICKET_USER) {
            $_sendArCheckedStatus = ' ';

            if ($_tabType == self::TAB_NEWTICKET_USER) {
                $_sendArCheckedStatus = ' checked ';
            }

            $_extendedOptions .= '&nbsp;&nbsp;&nbsp;&nbsp;<label for="' . $_tabPrefix . 'sendar"><input type="checkbox" value="1" id="' . $_tabPrefix . 'sendar" class="swiftcheckbox" name="opt' . $_tabPrefix . '_sendar"' . $_sendArCheckedStatus . '/>&nbsp;&nbsp;<i class="fa fa-envelope" aria-hidden="true"></i> ' . $this->Language->Get('dispatchsendar') . '</label>';
        }

        if ($_tabType == self::TAB_REPLY && $_SWIFT_TicketObject->GetProperty('creator') == SWIFT_Ticket::CREATOR_USER) {
            $_extendedOptions .= '&nbsp;&nbsp;&nbsp;&nbsp;<label for="' . $_tabPrefix . 'createasuser"><input type="checkbox" value="1" id="' . $_tabPrefix . 'createasuser" class="swiftcheckbox" name="opt' . $_tabPrefix . '_createasuser" />&nbsp;&nbsp;<i class="fa fa-user" aria-hidden="true"></i>' . $this->Language->Get('dispatchasuser') . '</label>';
        }


        if ($_SWIFT->Staff->GetPermission('staff_tcaninsertmacro') != '0') {
            $_extendedOptions .= '&nbsp;&nbsp;&nbsp;&nbsp;<label for="' . $_tabPrefix . 'addmacro"><input type="checkbox" value="1" id="' . $_tabPrefix . 'addmacro" class="swiftcheckbox" name="opt' . $_tabPrefix . '_addmacro" />&nbsp;&nbsp;<i class="fa fa-plus-circle" aria-hidden="true"></i> ' . $this->Language->Get('dispatchaddmacro') . '</label>';
        }

        if (SWIFT_App::IsInstalled(APP_KNOWLEDGEBASE)) {
            $_extendedOptions .= '&nbsp;&nbsp;&nbsp;&nbsp;<label for="' . $_tabPrefix . 'addkb"><input type="checkbox" value="1" id="' . $_tabPrefix . 'addkb" class="swiftcheckbox" name="opt' . $_tabPrefix . '_addkb" />&nbsp;&nbsp;<i class="fa fa-plus-circle" aria-hidden="true"></i>' . $this->Language->Get('dispatchaddkb') . '</label>';
        }

        $_watchChecked = false;
        if (isset($_ticketWatchContainer[$_SWIFT->Staff->GetStaffID()])) {
            $_watchChecked = true;
        }
        $_extendedOptions .= '&nbsp;&nbsp;&nbsp;&nbsp;<label for="' . $_tabPrefix . 'watch"><input type="checkbox" value="1" id="' . $_tabPrefix . 'watch" class="swiftcheckbox" name="opt' . $_tabPrefix . '_watch"' . IIF($_watchChecked == true, ' checked') . ' />&nbsp;&nbsp;<i class="fa fa-eye" aria-hidden="true"></i> ' . $this->Language->Get('dispatchwatch') . '</label>';

        $_extendedOptions .= '&nbsp;&nbsp;&nbsp;' . $this->Language->Get('billworked') . ' <input type="text" class="swifttextnumeric2small" name="' . $_tabPrefix . 'billingtimeworked" id="' . $_tabPrefix . 'billingtimeworked" value="" size="3" />';
        $_extendedOptions .= '&nbsp;&nbsp;&nbsp;' . $this->Language->Get('billbillable') . ' <input type="text" class="swifttextnumeric2small" name="' . $_tabPrefix . 'billingtimebillable" id="' . $_tabPrefix . 'billingtimebillable" value="" onfocus="javascript: HandleBillingBillableFocus(this, \'' . $_tabPrefix . '\');" size="3" />';

        // Begin Hook: staff_ticket_workflow
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_workflow')) ? eval($_hookCode) : false;
        // End Hook

        $_extendedOptions .= '</td></tr>';

        $_TabObject->RowHTML($_extendedOptions);

        // Begin Hook: staff_ticket_dispatchtab
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_dispatchtab')) ? eval($_hookCode) : false;
        // End Hook

        // Notes
        $_TabObject->StartContainer($_tabPrefix . 'notes', false);
        $_TabObject->Notes($_tabPrefix . 'ticketnotes', $this->Language->Get('addnotes'), '', 1);

        $_radioContainer = array();
        $_radioContainer[0]['title'] = $this->Language->Get('notes_ticket');
        $_radioContainer[0]['value'] = 'ticket';
        $_radioContainer[0]['checked'] = true;

        if ($_SWIFT->Staff->GetPermission('staff_caninsertusernote') != '0' && $_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
            $_radioContainer[1]['title'] = $this->Language->Get('notes_user');
            $_radioContainer[1]['value'] = 'user';

            $_radioContainer[2]['title'] = $this->Language->Get('notes_userorganization');
            $_radioContainer[2]['value'] = 'userorganization';
        }

        $_TabObject->Radio($_tabPrefix . 'notetype', $this->Language->Get('notetype'), '', $_radioContainer, false);

        $_TabObject->EndContainer();

        // Follow-Up
        $_TabObject->StartContainer($_tabPrefix . 'followup', false);
        $_TabObject->Title($this->Language->Get('followup'), 'doublearrows.gif');
        $_TabObject->RowHTML('<tr><td><div id="' . $_tabPrefix . 'followupcontainer"></div></td></tr>');
        $_TabObject->EndContainer();

        // Options
        $_TabObject->StartContainer($_tabPrefix . 'options', false);
        $_TabObject->Title($this->Language->Get('options'), 'doublearrows.gif');

        if (!_is_array($_ticketBCCEmailContainer))
        {
            $_TabObject->TextMultipleAutoComplete($_tabPrefix . 'bcc', $this->Language->Get('dispatchbcc'), '', '/Tickets/Ajax/SearchEmail', $_ticketBCCEmailContainer, 'fa-envelope-o', false, false, 2, false, false, true, '', array('containemail' => true));
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4614 Incorrect Reply Due time is calculated, if same ticket is replied by multiple staff members in different timezones.
         */
        $_dueDate = '';
        $_ticketDueTime = false;
        if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded() && $_SWIFT_TicketObject->GetProperty('duetime') != '0') {
            $_dueDate = date(SWIFT_Date::GetCalendarDateFormat(), $_SWIFT_TicketObject->GetProperty('duetime'));

            $_ticketDueTime = $_SWIFT_TicketObject->GetProperty('duetime');
        }
        $_TabObject->Date($_tabPrefix . 'due', $this->Language->Get('dialogduetimeline'), '', $_dueDate, $_ticketDueTime, true);

        $_resolutionDueDate = '';
        $_ticketResolutionDueTime = false;
        if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded() && $_SWIFT_TicketObject->GetProperty('resolutionduedateline') != '0') {
            $_resolutionDueDate = date(SWIFT_Date::GetCalendarDateFormat(), $_SWIFT_TicketObject->GetProperty('resolutionduedateline'));

            $_ticketResolutionDueTime = $_SWIFT_TicketObject->GetProperty('resolutionduedateline');
        }
        $_TabObject->Date($_tabPrefix . 'resolutiondue', $this->Language->Get('dialogresolutionduetimeline'), '', $_resolutionDueDate, $_ticketResolutionDueTime, true);

        $_TabObject->EndContainer();

        // Attach Files
        $_SWIFT_UserInterfaceToolbarObject_Attachment = new SWIFT_UserInterfaceToolbar($this->UserInterface);
        $_SWIFT_UserInterfaceToolbarObject_Attachment->AddButton($this->Language->Get('dispatchaddfile'), 'fa-plus-circle',
            "AddTicketFile('" . $_tabPrefix . "');", SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);

        $_TabObject->StartContainer($_tabPrefix . 'attachments', false, $_SWIFT_UserInterfaceToolbarObject_Attachment->Render(false));

        $_attachmentContainerHTML = '<tr class="tablerow1_tr"><td align="left" valign="top class="tablerow1"><div id="' . $_tabPrefix . 'attachmentcontainer">';
        $_attachmentFileHTML = '<div class="ticketattachmentitem"><div class="ticketattachmentitemdelete" onclick="javascript: $(this).parent().remove();">&nbsp;</div><input name="' . $_tabPrefix . 'attachments[]" type="file" size="20" class="swifttextlarge swifttextfile" /></div>';
        for ($index = 0; $index < 3; $index++) {
            $_attachmentContainerHTML .= $_attachmentFileHTML;
        }
        $_attachmentContainerHTML .= '</div></td></tr>';

        $_TabObject->RowHTML($_attachmentContainerHTML);

        $_TabObject->EndContainer();

        // Retrieve the Ticket Status & Priority JSON
        if ($_tabType == self::TAB_NEWTICKET_EMAIL || $_tabType == self::TAB_NEWTICKET_USER) {
            $_ticketDataJSON = $this->GetTicketDataJSON();
            $_ticketDataHTML = '<script language="Javascript" type="text/javascript">';
            $_ticketDataHTML .= '_ticketData = ' . $_ticketDataJSON . ';';
            $_ticketDataHTML .= '</script>';

            $this->UserInterface->AppendHTML($_ticketDataHTML);
        }

        $_scriptHTML = '<script language="Javascript" type="text/javascript">';
        $_scriptHTML .= 'if (window.$UIObject) { window.$UIObject.Queue(function(){';
        $_scriptHTML .= ' $("#' . $_tabPrefix . 'billingtimeworked").mask("99:99");';
        $_scriptHTML .= ' $("#' . $_tabPrefix . 'billingtimebillable").mask("99:99");';
        $_scriptHTML .= ' QuickInsertLoad(\'' . $_tabPrefix . '\');';
        $_scriptHTML .= '}); }';
        $_scriptHTML .= '</script>';

        $_TabObject->AppendHTML($_scriptHTML);

        return true;
    }

    /**
     * Render the Dispatch Dialog
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Base\Library\Staff\SWIFT_Staff_Exception
     * @throws \Tickets\Library\Ticket\SWIFT_Ticket_Exception
     */
    public function RenderDispatchForm($_mode, SWIFT_Ticket $_SWIFT_TicketObject) {
        $_SWIFT = SWIFT::GetInstance();

        $_staffCache = (array) $this->Cache->Get('staffcache');

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Start('ticketdispatch', '/Tickets/Ticket/DispatchSubmit/' . $_SWIFT_TicketObject->GetTicketID() . '/' . SWIFT::Get('ticketurlsuffix'), SWIFT_UserInterface::MODE_EDIT, true, false, false, false);
        $this->UserInterface->SetDialogOptions(false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('assign'), 'fa-check-circle');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);


        $_staffGroupCache = (array) $this->Cache->Get('staffgroupcache');
        $_staffCache = (array) $this->Cache->Get('staffcache');
        $_ticketCountCache = (array) $this->Cache->Get('ticketcountcache');

        $_staffGroupStaffContainer = array(0);

        /**
         * @var int $_staffID
         * @var array $_staffContainer
         */
        foreach ($_staffCache as $_staffID => $_staffContainer) {
            if ($_staffContainer['isenabled'] == '0') {
                continue;
            }

            $_totalUnresolvedItems = 0;
            if (isset($_ticketCountCache['ownerstaff'][$_staffID])) {
                $_totalUnresolvedItems = (int) ($_ticketCountCache['ownerstaff'][$_staffID]['totalunresolveditems']);
            }

            $_staffGroupStaffContainer[$_staffContainer['staffgroupid']][$_totalUnresolvedItems . '.' . $_staffID] = $_staffContainer;
            $_staffGroupStaffContainer[$_staffContainer['staffgroupid']][$_totalUnresolvedItems . '.' . $_staffID]['totalunresolveditems'] = $_totalUnresolvedItems;

        }


        /*
         * ###############################################
         * BEGIN DISPATCH TAB
         * ###############################################
        */

        $_DispatchTabObject = $this->UserInterface->AddTab($this->Language->Get('tabdispatch'), 'icon_dispatch.png', 'dispatch', true);

        $_DispatchTabObject->Overflow(390);
        /**
         * @var int $_staffGroupID
         * @var array $_staffGroupContainer
         */
        foreach ($_staffGroupCache as $_staffGroupID => $_staffGroupContainer) {
            $_tabHTML = '';

            $_tabHTML .= '<table width="100%" border="0" cellspacing="1" cellpadding="4">';
            $_tabHTML .= '<tr class="settabletitlerowmain2"><td class="settabletitlerowmain2" align="left" colspan="2">';
            $_tabHTML .= '<span style="float: left;"><img src="' . SWIFT::Get('themepath') . 'images/icon_doublearrows.gif' . '" align="absmiddle" border="0" /> ' . htmlspecialchars($_staffGroupContainer['title']) . '</span>';
            $_tabHTML .= '</tr>';
            $_tabHTML .= '</table>';

            $_tabHTML .= '<div id="" style="display: block;">';
            $_tabHTML .= '<table width="100%" border="0" cellspacing="1" cellpadding="4">';

            $_DispatchTabObject->RowHTML($_tabHTML);

            if (isset($_staffGroupStaffContainer[$_staffGroupID]) && _is_array($_staffGroupStaffContainer[$_staffGroupID])) {
                $staffs = (array)$_staffGroupStaffContainer[$_staffGroupID];
                ksort($staffs);

                foreach ($staffs as $_staffContainer) {
                    $_assignedDepartmentForStaff = SWIFT_Staff::GetAssignedDepartmentsOnStaffID($_staffContainer['staffid']);
                    if (!in_array($_SWIFT_TicketObject->GetProperty('departmentid'), $_assignedDepartmentForStaff)) {
                        continue;
                    }

                    $_extendedHTML = '<div class="blocknotecountergreen">0</div>';
                    if (!empty($_staffContainer['totalunresolveditems'])) {
                        $_divClass = 'blocknotecountergreen';
                        if ($_staffContainer['totalunresolveditems'] > 0 && $_staffContainer['totalunresolveditems'] < 20) {
                            $_divClass = 'blocknotecounterblue';
                        } else {
                            $_divClass = 'blocknotecounterred';
                        }
                        $_extendedHTML = '<div class="' . $_divClass . '">' . number_format($_staffContainer['totalunresolveditems'], 0) . '</div>';
                    }
                    $_DispatchTabObject->RadioList('dispatchstaffid', $_staffContainer['fullname'], $_staffContainer['staffid'], IIF($_SWIFT_TicketObject->GetProperty('ownerstaffid') == $_staffContainer['staffid'], true, false), '<span style="float: right;">' . $_extendedHTML . '</span>');
                }
            }

            $_DispatchTabObject->RowHTML('</table></div>');
        }


        /*
         * ###############################################
         * END DISPATCH TAB
         * ###############################################
        */

        $this->UserInterface->End();

        return true;
    }
}
