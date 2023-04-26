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

namespace LiveChat\Library\Chat;

use LiveChat\Library\Chat\SWIFT_Chat_Exception;
use LiveChat\Library\Chat\SWIFT_ChatEvent;
use SWIFT;
use LiveChat\Models\Chat\SWIFT_Chat;
use LiveChat\Models\Chat\SWIFT_ChatQueue;
use LiveChat\Models\Chat\SWIFT_ChatVariable;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Session;
use LiveChat\Models\Visitor\SWIFT_Visitor;

/**
 * The Live Chat Event Management Class. Used to handle dispatching and retrieval of chat related events and notifications.
 *
 * @author Varun Shoor
 */
class SWIFT_ChatEventClient extends SWIFT_ChatEvent
{
    public $XML;

    /**
     * Prepare the Packet to Dispatch to Winapp
     *
     * @author Varun Shoor
     * @param string $_sessionID The Visitor Session ID
     * @param string $_chatSessionID The Chat Session ID
     * @param int $_currentChatStatus The Current Chat Status Dispatched by Client
     * @param int $_isFirstTime Whether or not this is the first execution request for the loop
     * @param bool $_isUserTyping Check to see whether user is typing
     * @param string $_filterDepartmentID (OPTIONAL)
     * @param int $_transfer transfer status which was sent to client during last call
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function PrepareClientPacket($_sessionID, $_chatSessionID, $_currentChatStatus, $_isFirstTime, $_isUserTyping, $_filterDepartmentID = '0', $_transfer = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        $_staffCache = $this->Cache->Get('staffcache');
        $_departmentCache = $this->Cache->Get('departmentcache');

        $_redirectLeaveMessage = $_roundRobinExecuted = $_doRoundRobin = false;

        $_logChatObjectID = '0';

        $this->XML->AddParentTag('chatchunks');
        $_SWIFT_ChatObject = SWIFT_Chat::GetChatObjectFromSession($_chatSessionID);
        if (!$_SWIFT_ChatObject) {
            $this->XML->AddParentTag('chunk', array('guid' => '0'));
            $this->XML->AddTag('type', 'message');
            $this->XML->AddTag('message', $this->Language->Get('chatexpired'));
            $this->XML->EndParentTag('chunk');
        } else {
            $_logChatObjectID = $_SWIFT_ChatObject->GetChatObjectID();

            // Is first time?
            if ($_isFirstTime) {
                $_pendingQueue = SWIFT_Chat::GetTotalChatsAhead(array($_sessionID, $_chatSessionID), $_SWIFT_ChatObject->GetProperty('dateline'), $_SWIFT_ChatObject->GetProperty('departmentid'));

                if ($_pendingQueue > 0 && $this->Settings->Get('livesupport_displayclientchatqueue') == '1') {
                    $_peopleAheadQueueMessage = sprintf($this->Language->Get('peopleaheadinqueue'), $_pendingQueue);

                    $this->DispatchSystemMessageChunk($_SWIFT_ChatObject, $_peopleAheadQueueMessage);
                }

                $this->DispatchSystemMessageChunk($_SWIFT_ChatObject, "<strong>" . $this->Language->Get('fieldchatsubject') . "</strong> " . nl2br(htmlspecialchars($_SWIFT_ChatObject->GetProperty('subject'))));
                $this->DispatchSystemMessageChunk($_SWIFT_ChatObject, $this->Language->Get('pleasewaitoperator'));
            }

            // Update both the visitor and chat session id on shutdown, visitorsessionid needs to be updated to make sure chat runs fine even if visitor closes all other browser windows
            SWIFT_Session::UpdateHeartbeatAndStatus(array($_chatSessionID, $_sessionID), -1);

            /**
             * ###############################################
             * START QUEUE ROUTING CODE
             * ###############################################
             */
            $_timeRetry = $_SWIFT_ChatObject->GetProperty('roundrobintimeline') + $this->Settings->Get('livesupport_roundrobintimetry');

            // We dont do round robin processing if its a proactive chat...
            if (DATENOW >= $_timeRetry && $_SWIFT_ChatObject->GetProperty('isproactive') != 1) {
                $_doRoundRobin = true;
            }


            if ($this->Settings->Get('ls_routingmode') == 'roundrobin') {
                // Is user in CHAT_INCOMING mode and all roundrobin retries have failed?
                if ($_SWIFT_ChatObject->GetChatStatus() == SWIFT_Chat::CHAT_INCOMING && $_SWIFT_ChatObject->GetProperty('roundrobinhits') >= $this->Settings->Get('livesupport_roundrobinretries') && $_doRoundRobin == true) {
                    // Seems like no staff answered this persons query, poor soul.. must be pissed at the company!.. nevertheless lets just redirect him to leave a message form now he can vent his anger on the company ;)

                    $_SWIFT_ChatObject->UpdateChatStatus(SWIFT_Chat::CHAT_NOANSWER);

                    $_redirectLeaveMessage = true;
                } else if ($_SWIFT_ChatObject->GetChatStatus() == SWIFT_Chat::CHAT_INCOMING && $_doRoundRobin == true) {
                    // We need to try to switch over the staff, maybe the current staff is busy drinking his coffee?
                    SWIFT_ChatVariable::Create($_SWIFT_ChatObject->GetChatObjectID(), SWIFT_ChatVariable::TYPE_ROUNDROBINIGNORE, $_SWIFT_ChatObject->GetProperty('staffid'));

                    $_newStaffID = $_SWIFT_ChatObject->GetRoundRobinStaff($_SWIFT_ChatObject->GetProperty('departmentid'), SWIFT_ChatVariable::RetrieveVariableValues($_SWIFT_ChatObject->GetChatObjectID(), SWIFT_ChatVariable::TYPE_ROUNDROBINIGNORE), false, SWIFT_ChatVariable::RetrieveVariableValues($_SWIFT_ChatObject->GetChatObjectID(), SWIFT_ChatVariable::TYPE_SKILL), (int)($_SWIFT_ChatObject->GetProperty('isphone')));

                    /*
                    * BUG FIX - Mahesh Salaria
                    *
                    * SWIFT-1635: Round Robin is not working properly if Staff neither Accepts nor Decline incoming chat.
                    *
                    * Comments: We need to check if no one accepted or rejected chat first and in this case we have to bypass ignore list.
                    */

                    if (!$_newStaffID && $_redirectLeaveMessage == false) {
                        SWIFT_ChatVariable::DeleteOnType(array($_SWIFT_ChatObject->GetChatObjectID()), array(SWIFT_ChatVariable::TYPE_ROUNDROBINIGNORE));
                        $_newStaffID = $_SWIFT_ChatObject->GetRoundRobinStaff($_SWIFT_ChatObject->GetProperty('departmentid'), false, false, SWIFT_ChatVariable::RetrieveVariableValues($_SWIFT_ChatObject->GetChatObjectID(), SWIFT_ChatVariable::TYPE_SKILL), (int)($_SWIFT_ChatObject->GetProperty('isphone')));
                    }

                    if ($_newStaffID) {
                        // We need to update the staff, seems like there are other staff online too
                        $_newStaffName = $_staffCache[$_newStaffID]['fullname'];

                        $_SWIFT_ChatObject->UpdateRoundRobinTimeline($_newStaffID, $_newStaffName);
                    } else {
                        $_SWIFT_ChatObject->UpdateRoundRobinTimeline();
                    }

                    $_roundRobinExecuted = true;
                }
            } else if ($this->Settings->Get('ls_routingmode') == 'openqueue') {
                $_timeSinceChat = DATENOW - $_SWIFT_ChatObject->GetProperty('dateline');

                // No one picked the chat in open queue mode and we have hit the timeout?
                if ($_SWIFT_ChatObject->GetChatStatus() == SWIFT_Chat::CHAT_INCOMING && $_timeSinceChat >= $this->Settings->Get('ls_openqueuetimeout')) {
                    $_SWIFT_ChatObject->UpdateChatStatus(SWIFT_Chat::CHAT_NOANSWER);

                    $_redirectLeaveMessage = true;

                    // Keep on updating the timeline otherwise
                } else if ($_SWIFT_ChatObject->GetChatStatus() == SWIFT_Chat::CHAT_INCOMING && $_doRoundRobin == true) {
                    $_SWIFT_ChatObject->UpdateRoundRobinTimeline();
                }
            }


            /**
             * ###############################################
             * END QUEUE ROUTING CODE
             * ###############################################
             */

            /**
             * ###############################################
             * BEGIN CHECK FOR USER IS TYPING
             * ###############################################
             */

            if ($_isUserTyping) {
                $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->AddActionToQueue(SWIFT_ChatQueue::SUBMIT_CLIENT, SWIFT_ChatQueue::CHATACTION_TYPING, array($_SWIFT_ChatObject->GetProperty('userfullname'), 1));
            } else {
                $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->AddActionToQueue(SWIFT_ChatQueue::SUBMIT_CLIENT, SWIFT_ChatQueue::CHATACTION_TYPING, array($_SWIFT_ChatObject->GetProperty('userfullname'), 0));
            }

            /**
             * ###############################################
             * END CHECK FOR USER IS TYPING
             * ###############################################
             */

            /**
             * ###############################################
             * START MESSAGE FETCHING CODE
             * ###############################################
             */
            $_messageGUIDList = $_messageQueueIDList = $_staffIgnoreIDList = array();
            $_messageQueueList = $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->GetMessageQueue(0);

            /**
             * Incoming > In Chat
             */
            if ($_currentChatStatus == SWIFT_Chat::CHAT_INCOMING && $_SWIFT_ChatObject->GetChatStatus() == SWIFT_Chat::CHAT_INCHAT) {
                SWIFT_Visitor::Flush();

                if ($this->Settings->Get('ls_depname') == 1 && isset($_departmentCache[$_SWIFT_ChatObject->GetProperty('departmentid')]['title'])) {
                    $this->DispatchSystemMessageChunk($_SWIFT_ChatObject, sprintf($this->Language->Get('joinedchatdep'), $_SWIFT_ChatObject->GetProperty('departmenttitle')));
                } else {
                    $_finalStaffName = $_SWIFT_ChatObject->GetProperty('staffname');
                    if (isset($_staffCache[$_SWIFT_ChatObject->GetProperty('staffid')]) && !empty($_staffCache[$_SWIFT_ChatObject->GetProperty('staffid')]['designation'])) {
                        $_finalStaffName .= ' (' . htmlspecialchars($_staffCache[$_SWIFT_ChatObject->GetProperty('staffid')]['designation']) . ')';
                    }
                    $this->DispatchSystemMessageChunk($_SWIFT_ChatObject, sprintf($this->Language->Get('joinedchat'), $_finalStaffName, $_SWIFT_ChatObject->GetProperty('departmenttitle')));
                }

                // To display the avatar..
                $this->XML->AddParentTag('chunk', array('guid' => '0'));
                $this->XML->AddTag('type', 'staffaccept');
                $this->XML->AddTag('staffid', (int)($_SWIFT_ChatObject->GetProperty('staffid')));
                $this->XML->EndParentTag('chunk');

                // Begin Hook: visitor_chat_accepted
                unset($_hookCode);
                ($_hookCode = SWIFT_Hook::Execute('visitor_chat_eventaccepted')) ? eval($_hookCode) : false;
                // End Hook
            }

            /*
             * BUG FIX - Simaranjit Singh
             *
             * SWIFT-175:  The image of staff on the Live chat is not updated if the chat is transfered to other staff member.
             *
             * Comments: We need to match current status of chat in database with the status sent to user last time.
             */
            if ($_transfer != SWIFT_Chat::TRANSFER_PENDING && $_SWIFT_ChatObject->GetChatStatus() == SWIFT_Chat::CHAT_INCHAT && $_SWIFT_ChatObject->GetProperty('transferstatus') == SWIFT_Chat::TRANSFER_PENDING) {
                $this->XML->AddParentTag('chunk', array('guid' => '0'));
                $this->XML->AddTag('type', 'transfer');
                $this->XML->AddTag('status', SWIFT_Chat::TRANSFER_PENDING);
                $this->XML->EndParentTag('chunk');
            }

            //If staff accepted the transferred chat
            if ($_transfer != SWIFT_Chat::TRANSFER_ACCEPTED && $_SWIFT_ChatObject->GetChatStatus() == SWIFT_Chat::CHAT_INCHAT && $_SWIFT_ChatObject->GetProperty('transferstatus') == SWIFT_Chat::TRANSFER_ACCEPTED) {
                $this->XML->AddParentTag('chunk', array('guid' => '0'));
                $this->XML->AddTag('type', 'transfer');
                $this->XML->AddTag('status', SWIFT_Chat::TRANSFER_ACCEPTED);
                $this->XML->AddTag('staffid', (int)($_SWIFT_ChatObject->GetProperty('transfertoid')));
                $this->XML->EndParentTag('chunk');
            }

            //If staff rejected the transferred chat
            if ($_transfer != SWIFT_Chat::TRANSFER_REJECTED && $_SWIFT_ChatObject->GetChatStatus() == SWIFT_Chat::CHAT_INCHAT && $_SWIFT_ChatObject->GetProperty('transferstatus') == SWIFT_Chat::TRANSFER_REJECTED) {
                $this->XML->AddParentTag('chunk', array('guid' => '0'));
                $this->XML->AddTag('type', 'transfer');
                $this->XML->AddTag('status', SWIFT_Chat::TRANSFER_REJECTED);
                $this->XML->EndParentTag('chunk');
            }

            /**
             * Incoming > No Answer
             */
            if ($_currentChatStatus == SWIFT_Chat::CHAT_INCOMING && $_SWIFT_ChatObject->GetChatStatus() == SWIFT_Chat::CHAT_NOANSWER) {
                $this->XML->AddParentTag('chunk', array('guid' => '0'));
                $this->XML->AddTag('type', 'redirect');
                $this->XML->AddTag('url', SWIFT::Get('swiftpath') . 'visitor/index.php?/LiveChat/Chat/Message/_sessionID=' . $_sessionID . '/_leaveMessage=1/_departmentID=' . $_SWIFT_ChatObject->GetProperty('departmentid') . '/_filterDepartmentID=' . urlencode($_filterDepartmentID));
                $this->XML->EndParentTag('chunk');


                // Begin Hook: visitor_chat_eventnoanswer
                unset($_hookCode);
                ($_hookCode = SWIFT_Hook::Execute('visitor_chat_eventnoanswer')) ? eval($_hookCode) : false;
                // End Hook
            }

            // Begin Hook: visitor_chat_event
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('visitor_chat_event')) ? eval($_hookCode) : false;
            // End Hook

            if (_is_array($_messageQueueList)) {
                foreach ($_messageQueueList as $_key => $_val) {
                    $_messageQueueIDList[] = $_val['messagequeueid'];
                    $_messageArray = mb_unserialize($_val['contents']);

                    if (_is_array($_messageArray)) {
                        if ($_messageArray['type'] == SWIFT_ChatQueue::CHATACTION_MESSAGE) {
                            if (!in_array($_val['staffid'], $_staffIgnoreIDList)) {
                                $_staffIgnoreIDList[] = $_val['staffid'];
                            }

                            if ($this->Settings->Get('ls_depname') == 1 && !empty($_departmentCache[$_SWIFT_ChatObject->GetProperty('departmentid')]['title'])) {
                                $_chatName = $_departmentCache[$_SWIFT_ChatObject->GetProperty('departmentid')]['title'];
                            } else {
                                $_chatName = $_val['name'];
                            }

                            $this->XML->AddParentTag('chunk', array('guid' => $_val['guid']));
                            $this->XML->AddTag('type', 'staffmessage');
                            $this->XML->AddTag('staffname', $_chatName);
                            $this->XML->AddTag('message', str_replace("\n", '<BR />', preg_replace('#(\r\n|\r|\n)#s', "\n", htmlspecialchars(IIF($_messageArray['base64'] == true, $_messageArray['contents'], base64_encode($_messageArray['contents']))))));
                            $this->XML->EndParentTag('chunk');

                        } else if ($_messageArray['type'] == SWIFT_ChatQueue::CHATACTION_URL) {
                            /*
                             * BUG FIX - Varun Shoor
                             *
                             * SWIFT-1725 PUSH URL feature not sending link correctly
                             *
                             * Comments: Gurpreet said that the dialog box in KD does not allow the client to push the URL
                             */
                            // If the URL isn't prepended with http:// or https://, we need to add it.  -- RML

                            $_messageArray['contents'] = self::FixURL($_messageArray['contents']);

                            $this->XML->AddParentTag('chunk', array('guid' => $_val['guid']));
                            $this->XML->AddTag('type', 'pushurl');
                            $this->XML->AddTag('url', urldecode($_messageArray['contents']));
                            $this->XML->EndParentTag('chunk');

                        } else if ($_messageArray['type'] == SWIFT_ChatQueue::CHATACTION_IMAGE) {

                            /*
                             * BUG FIX - Varun Shoor
                             *
                             * SWIFT-1024 While sending an image in chat conversation, the local path of the image is also shown to end-users
                             *
                             * Comments: None
                             */

                            $_imagePath = urldecode($_messageArray['contents']);
                            $_reversedPosition = strrpos($_imagePath, "\\");
                            if ($_reversedPosition > 0) {
                                $_reversedPosition++;
                            }

                            $_imageName = substr($_imagePath, $_reversedPosition);

                            $this->XML->AddParentTag('chunk', array('guid' => $_val['guid']));
                            $this->XML->AddTag('type', 'pushimage');
                            $this->XML->AddTag('url', $_imageName);
                            $this->XML->EndParentTag('chunk');

                        } else if ($_messageArray['type'] == SWIFT_ChatQueue::CHATACTION_UPLOADEDIMAGE) {
                            $this->XML->AddParentTag('chunk', array('guid' => $_val['guid']));
                            $this->XML->AddTag('type', 'uploadedimage');

                            // Original Image
                            $this->XML->AddTag('original', $_messageArray['contents'][0]);

                            // Thumbnail Image
                            $this->XML->AddTag('thumbnail', $_messageArray['contents'][1]);
                            $this->XML->EndParentTag('chunk');

                        } else if ($_messageArray['type'] == SWIFT_ChatQueue::CHATACTION_FILE) {
                            $this->XML->AddParentTag('chunk', array('guid' => $_val['guid']));
                            $this->XML->AddTag('type', 'pushfile');
                            $this->XML->AddTag('filename', $_messageArray['contents'][0]);
                            $this->XML->AddTag('fileid', $_messageArray['contents'][1]);
                            $this->XML->AddTag('filehash', $_messageArray['contents'][2]);
                            $this->XML->EndParentTag('chunk');

                        } else if ($_messageArray['type'] == SWIFT_ChatQueue::CHATACTION_CODE) {
                            $this->XML->AddParentTag('chunk', array('guid' => $_val['guid']));
                            $this->XML->AddTag('type', 'pushcode');
                            $this->XML->AddTag('code', HighlightCode($_messageArray['contents'][0]));
                            $this->XML->EndParentTag('chunk');

                        } else if ($_messageArray['type'] == SWIFT_ChatQueue::CHATACTION_CHATLEAVE) {
                            // Staff left the chat from conference
                            $this->XML->AddParentTag('chunk', array('guid' => $_val['guid']));
                            $this->XML->AddTag('type', 'message');
                            $this->XML->AddTag('message', sprintf($this->Language->Get('chatleave'), $_messageArray['contents']));
                            $this->XML->EndParentTag('chunk');

                        } else if ($_messageArray['type'] == SWIFT_ChatQueue::CHATACTION_CHATENTER) {
                            // Staff entered the chat for conference
                            $this->XML->AddParentTag('chunk', array('guid' => $_val['guid']));
                            $this->XML->AddTag('type', 'message');
                            $this->XML->AddTag('message', sprintf($this->Language->Get('chatenter'), $_messageArray['contents']));
                            $this->XML->EndParentTag('chunk');

                        } else if ($_messageArray['type'] == SWIFT_ChatQueue::CHATACTION_TYPING) {
                            if ($this->Settings->Get('ls_depname') == 1 && isset($_departmentCache[$_SWIFT_ChatObject->GetProperty('departmentid')])) {
                                $_typingName = $_departmentCache[$_SWIFT_ChatObject->GetProperty('departmentid')]['title'];
                            } else {
                                $_typingName = $_messageArray['contents'][0];
                            }

                            $_typingStaffID = $_val['staffid'];
                            $_messageGUIDList[] = $_val['guid'];
                        }
                    }
                }
            }

            if (count($_messageGUIDList) > 0) {
                $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->DeleteGUID($_messageGUIDList);
            }

//            if (count($_messageQueueIDList) > 0)
//            {
//                $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->DeleteMessageQueue($_messageQueueIDList);
//            }

            /**
             * ###############################################
             * END MESSAGE FETCHING CODE
             * ###############################################
             */

            /**
             * In Chat > Ended
             */
            if ($_currentChatStatus == SWIFT_Chat::CHAT_INCHAT && ($_SWIFT_ChatObject->GetChatStatus() == SWIFT_Chat::CHAT_ENDED || $_SWIFT_ChatObject->GetChatStatus() == SWIFT_Chat::CHAT_TIMEOUT)) {
                if ($this->Settings->Get('ls_depname') == 1 && isset($_departmentCache[$_SWIFT_ChatObject->GetProperty('departmentid')]['title'])) {
                    $this->DispatchSystemMessageChunk($_SWIFT_ChatObject, sprintf($this->Language->Get('chatleave'), $_SWIFT_ChatObject->GetProperty('departmenttitle')) . " <BR /><a href='" . SWIFT::Get('swiftpath') . "visitor/index.php?/LiveChat/Chat/Message/_sessionID=" . $_sessionID . "/_leaveMessage=1/_departmentID=" . $_SWIFT_ChatObject->GetProperty('departmentid') . '/_filterDepartmentID=' . urlencode($_filterDepartmentID) . "' class='chatlink'>" . $this->Language->Get('chatendleavemsg') . "</a>");
                } else {
                    $this->DispatchSystemMessageChunk($_SWIFT_ChatObject, sprintf($this->Language->Get('chatleave'), $_SWIFT_ChatObject->GetProperty('staffname'), $_SWIFT_ChatObject->GetProperty('departmenttitle')) . " <BR /><a href='" . SWIFT::Get('swiftpath') . "visitor/index.php?/LiveChat/Chat/Message/_sessionID=" . $_sessionID . "/_leaveMessage=1/_departmentID=" . $_SWIFT_ChatObject->GetProperty('departmentid') . '/_filterDepartmentID=' . urlencode($_filterDepartmentID) . "' class='chatlink'>" . $this->Language->Get('chatendleavemsg') . "</a>");
                }

                // Begin Hook: visitor_chat_eventended
                unset($_hookCode);
                ($_hookCode = SWIFT_Hook::Execute('visitor_chat_eventended')) ? eval($_hookCode) : false;
                // End Hook
            }

            // ======= User is Typing Check =======
            if (!empty($_typingName)) {
                // If we just received a message from the same staff then dont display user is typing, its a tricky little way to make it a bit more realtime but hey it works..
                if (!isset($_typingStaffID) || !in_array($_typingStaffID, $_staffIgnoreIDList)) {
                    $_isTyping = true;
                    $this->XML->AddParentTag('chunk', array('guid' => '0'));
                    $this->XML->AddTag('type', 'usertyping');
                    $this->XML->AddTag('name', $_typingName);
                    $this->XML->EndParentTag('chunk');
                }
            }
            // ======= End User is Typing Check =======

            if ($_SWIFT_ChatObject->GetChatStatus() != '') {
                $this->XML->AddParentTag('chunk', array('guid' => '0'));
                $this->XML->AddTag('type', 'chatstatus');
                $this->XML->AddTag('status', $_SWIFT_ChatObject->GetChatStatus());
                $this->XML->EndParentTag('chunk');
            }

            if ($_redirectLeaveMessage == true) {
                $this->XML->AddParentTag('chunk', array('guid' => '0'));
                $this->XML->AddTag('type', 'redirect');
                $this->XML->AddTag('url', SWIFT::Get('swiftpath') . 'visitor/index.php?/LiveChat/Chat/Message/_sessionID=' . $_sessionID . '/_leaveMessage=1/_departmentID=' . $_SWIFT_ChatObject->GetProperty('departmentid') . '/_filterDepartmentID=' . urlencode($_filterDepartmentID));
                $this->XML->EndParentTag('chunk');

            } else if ($_roundRobinExecuted == true) {
                $_pendingQueue = SWIFT_Chat::GetTotalChatsAhead(array($_sessionID, $_chatSessionID), $_SWIFT_ChatObject->GetProperty('dateline'), $_SWIFT_ChatObject->GetProperty('departmentid'));


                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-1630 'Tell Visitor How Many Chats In Queue' setting does not work if we set it to No
                 *
                 * Comments: None
                 */
                if ($_pendingQueue > 0 && $this->Settings->Get('livesupport_displayclientchatqueue') == '1') {
                    $_peopleAheadQueueMessage = sprintf($this->Language->Get('peopleaheadinqueue'), $_pendingQueue);

                    $this->DispatchSystemMessageChunk($_SWIFT_ChatObject, $_peopleAheadQueueMessage);
                }

                $this->DispatchSystemMessageChunk($_SWIFT_ChatObject, $this->Language->Get('alloperatorsbusy'));
            }
        }

        $this->XML->EndParentTag('chatchunks');

        /**
         * ---------------------------------------------
         * BEGIN DEBUGGING CODE (UNCOMMENT TO ACTIVATE)
         * ---------------------------------------------
         */
        /*
        $_xmlCode = $this->XML->ReturnXML();
        file_put_contents('./__swift/cache/chatpacketlog.' . $_logChatObjectID . '.txt', $_xmlCode, FILE_APPEND);
         */

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Fix the URL
     *
     * @author Varun Shoor
     * @param string $_url
     * @return string
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function FixURL($_url)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_newURL = mb_strtolower($_url);

        if (substr($_newURL, 0, strlen('http://')) != 'http://' && substr($_newURL, 0, strlen('https://')) != 'https://' && substr($_newURL, 0, strlen('ftp://')) != 'ftp://' && substr($_newURL, 0, strlen('mailto://')) != 'mailto://') {
            return 'http://' . $_url;
        }

        return $_url;
    }
}
