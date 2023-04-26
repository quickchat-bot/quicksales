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
 * @copyright    Copyright (c) 2001-2012, Kayako
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
use LiveChat\Models\Chat\SWIFT_ChatChild;
use LiveChat\Models\Chat\SWIFT_ChatQueue;
use LiveChat\Library\Chat\SWIFT_ChatTransferManager;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Interface;
use SWIFT_Session;
use LiveChat\Models\Visitor\SWIFT_Visitor;
use LiveChat\Models\Ban\SWIFT_VisitorBan;

/**
 * The Live Chat Event Management Class. Used to handle dispatching and retrieval of chat related events and notifications.
 *
 * @author Varun Shoor
 */
class SWIFT_ChatEventWinapp extends SWIFT_ChatEvent
{
    public $XML;
    public $ChatTransferManager;

    /**
     * Prepares the Packet to Dispatch to Winapp
     *
     * @author Varun Shoor
     * @param array $_winappGUIDList The Winapp GUID Confirmation Dispatch Container Array
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function PrepareWinappPacket($_winappGUIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->XML->BuildXML();

        $_staffCache = $this->Cache->Get('staffcache');

        // The goal of this file is to dispatch all active chat objects that are pending or in chat. The winapp dispatches the chat objects which it believes belong to the staff user in this POST variable and which are currently active.. so if a chat has ended, it still gets loaded..
        $_chatObjectIDListArray = array();
        if (!empty($_POST['chatobjectidlist'])) {
            $_postChatObjectIDList = $_POST['chatobjectidlist'];
            if (strstr($_postChatObjectIDList, ',')) {
                $_chatObjectIDListArray = explode(',', $_postChatObjectIDList);
            } else {
                $_chatObjectIDListArray = array((int)($_postChatObjectIDList));
            }
        }

        // Get all the incoming and active chats
        $_lookupChatObjectIDList = $_loadChatObjectIDList = array();
        $_lookupChatObjectIDList = $_chatObjectIDListArray;
        $this->Database->Query("SELECT chatobjectid, staffid, chattype FROM " . TABLE_PREFIX . "chatobjects WHERE (chatstatus = '" . SWIFT_Chat::CHAT_INCOMING . "' OR chatstatus = '" . SWIFT_Chat::CHAT_INCHAT . "')");
        while ($this->Database->NextRecord()) {
            $_lookupChatObjectIDList[] = $this->Database->Record['chatobjectid'];

            if ($this->Database->Record['staffid'] == $_SWIFT->Staff->GetStaffID() || $this->Database->Record['chattype'] == SWIFT_Chat::CHATTYPE_STAFF) {
                $_loadChatObjectIDList[] = $this->Database->Record['chatobjectid'];
            }
        }

        // Load any other chat objects specifically requested by winapp
        if (_is_array($_chatObjectIDListArray)) {
            foreach ($_chatObjectIDListArray as $_key => $_val) {
                $_val = trim($_val);

                if (is_numeric($_val) && !in_array($_val, $_loadChatObjectIDList)) {
                    $_loadChatObjectIDList[] = (int)($_val);
                }
            }
        }

        $_chatChildIDList = $_staffChatChildIDList = array();
        if (count($_loadChatObjectIDList)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatchilds WHERE chatobjectid IN (" . BuildIN($_loadChatObjectIDList) . ")");
            while ($this->Database->NextRecord()) {
                $_chatChildIDList[$this->Database->Record['chatobjectid']][] = $this->Database->Record;
                if ($this->Database->Record['staffid'] == $_SWIFT->Staff->GetStaffID()) {
                    $_staffChatChildIDList[] = $this->Database->Record['chatchildid'];
                }

                // Is the chatchild same as staff that is currently logged in? If yes, then we lookup the chatobjectid and add it to be loaded list
                if ($this->Database->Record['staffid'] == $_SWIFT->Staff->GetStaffID()) {
                    if (!in_array($this->Database->Record['chatobjectid'], $_loadChatObjectIDList)) {
                        $_loadChatObjectIDList[] = $this->Database->Record['chatobjectid'];
                    }
                }
            }
        }

        $_chatObjects = $_finalChatObjectIDList = $_updateChatObjectIDList = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatobjects WHERE chatobjectid IN (" . BuildIN($_loadChatObjectIDList) . ")");

        while ($this->Database->NextRecord()) {
            $_chatObjects[$this->Database->Record['chatobjectid']] = $this->Database->Record;
            $_finalChatObjectIDList[] = $this->Database->Record['chatobjectid'];

            if ($this->Database->Record['chattype'] == SWIFT_Chat::CHATTYPE_STAFF) {
                $_updateChatObjectIDList[] = $this->Database->Record['chatobjectid'];
            }
        }

        // Get Sessions
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "sessions WHERE typeid IN (" . BuildIN($_finalChatObjectIDList) . ") AND sessiontype = '" . SWIFT_Interface::INTERFACE_CHAT . "'");
        while ($this->Database->NextRecord()) {
            $_chatObjects[$this->Database->Record['typeid']]['visitorsession'] = $this->Database->Record;
        }

        // Are there any chat events?
        if (count($_updateChatObjectIDList)) {
            SWIFT_Session::UpdateHeartbeatOnTypeID($_updateChatObjectIDList, SWIFT_Interface::INTERFACE_CHAT);
        }

        $_messageQueueIDList = $_messageQueues = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "messagequeue WHERE chatchildid IN (" . BuildIN($_staffChatChildIDList) . ") ORDER BY dateline ASC");
        while ($this->Database->NextRecord()) {
            $_messageQueueIDList[] = $this->Database->Record['messagequeueid'];
            $_messageQueues[$this->Database->Record['chatobjectid']][] = $this->Database->Record;
        }

        $this->XML->AddParentTag('events');

        // Process all chat objects
        $_updateChatObjectIDList = $_clearGUIDList = array();
        foreach ($_chatObjects as $_key => $_val) {
            $_typingDispatched = $_showNoResponseWarning = false;
            $_timeScale = DATENOW - 40;

            if ($_val['chatstatus'] == SWIFT_Chat::CHAT_INCOMING && !isset($_val['visitorsession'])) {
                continue;
            }

            /**
             * BUG FIX - Simaranjit Singh
             *
             * SWIFT-3545: Script in the 'Full Name' and 'Your Question' fields on live chat client window gets executed in KD side
             *
             * Comments: None
             */
            $_val['userfullname'] = $_SWIFT->Input->SanitizeForXSS($_val['userfullname']);
            $_val['subject'] = $_SWIFT->Input->SanitizeForXSS($_val['subject']);

            $_timeDifference = 0;
            if (isset($_val['visitorsession'])) {
                $_timeDifference = DATENOW - $_val['visitorsession']['lastactivity'];
            } else {
                $_val['visitorsessionid'] = '';
            }

            if ($_timeDifference > 300) {
                continue;
            }

            if (in_array($_val['chatobjectid'], $_chatObjectIDListArray) && $_val['chattype'] == SWIFT_Chat::CHATTYPE_STAFF) {
                $_updateChatObjectIDList[] = $_val['chatobjectid'];
            }

            // Has it been more than 120 seconds since last update from client?
            if (isset($_val['visitorsession']['lastactivity']) && $_val['visitorsession']['lastactivity'] < $_timeScale && $_val['visitorsession']['sessionhits'] != '' && $_val['visitorsession']['sessionhits'] <= 2) {
                // Yes sir
                SWIFT_Session::UpdateHeartbeatAndHitsOnTypeID(array($_val['chatobjectid']), SWIFT_Interface::INTERFACE_CHAT);
                SWIFT_Session::UpdateHeartbeatAndHitsOnSessionID(array($_val['visitorsessionid']));

                $_showNoResponseWarning = true;

                // More than 120 seconds and we have hit our threshold.. I guess we need to timeout this chat..
            } else if (isset($_val['visitorsession']['lastactivity']) && $_val['visitorsession']['lastactivity'] < $_timeScale && $_val['visitorsession']['sessionhits'] != '' && $_val['visitorsession']['sessionhits'] >= 3) {
                $_val['chatstatus'] = SWIFT_Chat::CHAT_TIMEOUT;
                $_SWIFT_ChatObject_Timeout = new SWIFT_Chat($_val['chatobjectid']);
                if ($_SWIFT_ChatObject_Timeout instanceof SWIFT_Chat && $_SWIFT_ChatObject_Timeout->GetIsClassLoaded()) {
                    $_SWIFT_ChatObject_Timeout->UpdateChatStatus(SWIFT_Chat::CHAT_TIMEOUT);
                }

                /**
                 * BUG FIX - Parminder Singh
                 *
                 * SWIFT-1329: Chat logs incorrectly display status as Timed Out
                 *
                 * Comments: Reset sessionhits, if it is already set
                 */
            } else if (!empty($_val['visitorsession']['sessionhits'])) {
                SWIFT_Session::ResetHitsOnSessionID($_val['chatsessionid']);
            }

            $this->XML->AddParentTag('chat', array('chatobjectid' => (int)($_val['chatobjectid']), 'visitorsessionid' => $_val['visitorsessionid'], 'dateline' => $_val['dateline'], 'lastpostactivity' => $_val['lastpostactivity'], 'userpostactivity' => $_val['userpostactivity'], 'staffpostactivity' => $_val['staffpostactivity'], 'userid' => (int)($_val['userid']), 'creatorstaffid' => (int)($_val['creatorstaffid']), 'userfullname' => $_val['userfullname'], 'useremail' => $_val['useremail'], 'staffid' => $_val['staffid'], 'staffname' => $_val['staffname'], 'chatstatus' => $_val['chatstatus'], 'departmentid' => $_val['departmentid'], 'departmenttitle' => $_val['departmenttitle'], 'chattype' => $_val['chattype'], 'isproactive' => $_val['isproactive'], 'transferfromid' => $_val['transferfromid'], 'transfertoid' => $_val['transfertoid'], 'transfertimeline' => $_val['transfertimeline'], 'transferstatus' => $_val['transferstatus'], 'subject' => $_val['subject'], 'phonenumber' => $_val['phonenumber']));

            if ($_showNoResponseWarning) {
                $this->XML->AddTag('warning', '', array('guid' => SWIFT_ChatQueue::GenerateGUID(), 'type' => 'noresponse', 'message' => htmlspecialchars($this->Language->Get('userwarningnoresponse'))));
            }

            // Load up the roster
            // Add the client roster entry
            if ($_val['chattype'] == SWIFT_Chat::CHATTYPE_CLIENT) {
                $this->XML->AddTag('roster', '', array('guid' => SWIFT_ChatQueue::GenerateGUID(), 'from' => 'user', 'visitorsessionid' => $_val['visitorsessionid'], 'userid' => $_val['userid'], 'fullname' => $_val['userfullname']));
            }

            if (isset($_chatChildIDList[$_val['chatobjectid']]) && _is_array($_chatChildIDList[$_val['chatobjectid']])) {
                foreach ($_chatChildIDList[$_val['chatobjectid']] as $_chatChildKey => $_chatChildVal) {
                    $this->XML->AddTag('roster', '', array('guid' => SWIFT_ChatQueue::GenerateGUID(), 'from' => 'staff', 'staffid' => $_chatChildVal['staffid'], 'fullname' => $_staffCache[$_chatChildVal['staffid']]['fullname'], 'observing' => (int)($_chatChildVal['isobserver'])));
                }
            }

            // Begin Hook: desktop_chat_event
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('desktop_chat_event')) ? eval($_hookCode) : false;
            // End Hook

            $_messageListIDContainer = $_ignoreUsers = array();
            if (isset($_messageQueues[$_val['chatobjectid']]) && _is_array($_messageQueues[$_val['chatobjectid']])) {
                foreach ($_messageQueues[$_val['chatobjectid']] as $_msgKey => $_msgVal) {
                    $_messageListIDContainer[] = $_msgVal['messagequeueid'];
                    $_msgArray = mb_unserialize($_msgVal['contents']);
                    if (is_array($_msgArray)) {

                        $_timeStamp = DATENOW;
                        if (isset($_msgArray['timestamp'])) {
                            $_timeStamp = $_msgArray['timestamp'];
                        }

                        if ($_msgArray['type'] == SWIFT_ChatQueue::CHATACTION_MESSAGE) {
                            if (!in_array($_val['staffid'], $_ignoreUsers)) {
                                $_ignoreUsers[] = $_val['staffid'];
                            }

                            unset($_message);
                            if ($_msgArray['base64'] == true) {
                                $_message = $_msgArray['contents'];
                            } else {
                                $_message = base64_encode($_msgArray['contents']);
                            }

                            $this->XML->AddTag('message', $_message, array('guid' => $_msgVal['guid'],
                                'from' => IIF($_msgVal['submittype'] == SWIFT_ChatQueue::SUBMIT_STAFF, 'staff', 'user'),
                                'fullname' => preg_replace('/&amp;#(x[a-f0-9]+|[0-9]+);/i', '&#$1;', htmlspecialchars($_msgVal['name'])),
                                'visitorsessionid' => IIF(empty($_msgVal['staffid']), $_val['visitorsessionid']),
                                'userid' => IIF(empty($_msgVal['staffid']), $_val['userid']), 'type' => 'text', 'base64' => '1', 'timestamp' => $_timeStamp));

                        } else if ($_msgArray['type'] == SWIFT_ChatQueue::CHATACTION_IMAGE) {
                            $this->XML->AddTag('message', base64_encode($_msgArray['contents']), array('guid' => $_msgVal['guid'],
                                'from' => IIF($_msgVal['submittype'] == SWIFT_ChatQueue::SUBMIT_STAFF, 'staff', 'user'),
                                'fullname' => preg_replace('/&amp;#(x[a-f0-9]+|[0-9]+);/i', '&#$1;', htmlspecialchars($_msgVal['name'])),
                                'visitorsessionid' => IIF(empty($_msgVal['staffid']), $_val['visitorsessionid']),
                                'userid' => IIF(empty($_msgVal['staffid']), $_val['userid']), 'type' => 'image', 'base64' => '1'));
                        } else if ($_msgArray['type'] == SWIFT_ChatQueue::CHATACTION_UPLOADEDIMAGE) {
                            $this->XML->AddTag('message', base64_encode($_msgArray['contents'][0]), array('guid' => $_msgVal['guid'],
                                'from' => IIF($_msgVal['submittype'] == SWIFT_ChatQueue::SUBMIT_STAFF, 'staff', 'user'),
                                'fullname' => preg_replace('/&amp;#(x[a-f0-9]+|[0-9]+);/i', '&#$1;', htmlspecialchars($_msgVal['name'])),
                                'visitorsessionid' => IIF(empty($_msgVal['staffid']), $_val['visitorsessionid']),
                                'userid' => IIF(empty($_msgVal['staffid']), $_val['userid']), 'type' => 'image', 'base64' => '1'));
                        } else if ($_msgArray['type'] == SWIFT_ChatQueue::CHATACTION_URL) {
                            $this->XML->AddTag('message', base64_encode($_msgArray['contents']), array('guid' => $_msgVal['guid'],
                                'from' => IIF($_msgVal['submittype'] == SWIFT_ChatQueue::SUBMIT_STAFF, 'staff', 'user'),
                                'fullname' => preg_replace('/&amp;#(x[a-f0-9]+|[0-9]+);/i', '&#$1;', htmlspecialchars($_msgVal['name'])),
                                'visitorsessionid' => IIF(empty($_msgVal['staffid']), $_val['visitorsessionid']),
                                'userid' => IIF(empty($_msgVal['staffid']), $_val['userid']), 'type' => 'url', 'base64' => '1'));
                        } else if ($_msgArray['type'] == SWIFT_ChatQueue::CHATACTION_CODE) {
                            $this->XML->AddTag('message', base64_encode($_msgArray['contents'][0]), array('guid' => $_msgVal['guid'],
                                'from' => IIF($_msgVal['submittype'] == SWIFT_ChatQueue::SUBMIT_STAFF, 'staff', 'user'),
                                'fullname' => preg_replace('/&amp;#(x[a-f0-9]+|[0-9]+);/i', '&#$1;', htmlspecialchars($_msgVal['name'])),
                                'visitorsessionid' => IIF(empty($_msgVal['staffid']), $_val['visitorsessionid']),
                                'userid' => IIF(empty($_msgVal['staffid']), $_val['userid']), 'userdata' => $_msgArray['contents'][1],
                                'type' => 'code', 'base64' => '1'));
                        } else if ($_msgArray['type'] == SWIFT_ChatQueue::CHATACTION_CHATLEAVE) {
                            $this->XML->AddTag('pounce', '', array('guid' => $_msgVal['guid'],
                                'from' => IIF($_msgVal['submittype'] == SWIFT_ChatQueue::SUBMIT_STAFF, 'staff', 'user'),
                                'fullname' => preg_replace('/&amp;#(x[a-f0-9]+|[0-9]+);/i', '&#$1;', htmlspecialchars($_msgVal['name'])),
                                'visitorsessionid' => IIF(empty($_msgVal['staffid']), $_val['visitorsessionid']),
                                'userid' => IIF(empty($_msgVal['staffid']), $_val['userid']), 'type' => 'leave'));
                        } else if ($_msgArray['type'] == SWIFT_ChatQueue::CHATACTION_CHATENTER) {
                            $this->XML->AddTag('pounce', '', array('guid' => $_msgVal['guid'],
                                'from' => IIF($_msgVal['submittype'] == SWIFT_ChatQueue::SUBMIT_STAFF, 'staff', 'user'),
                                'fullname' => preg_replace('/&amp;#(x[a-f0-9]+|[0-9]+);/i', '&#$1;', htmlspecialchars($_msgVal['name'])),
                                'visitorsessionid' => IIF(empty($_msgVal['staffid']), $_val['visitorsessionid']),
                                'userid' => IIF(empty($_msgVal['staffid']), $_val['userid']), 'type' => 'enter'));
                        } else if ($_msgArray['type'] == SWIFT_ChatQueue::CHATACTION_TYPING) {
                            $_clearGUIDList[] = $_msgVal['guid'];

                            $this->XML->AddTag('typing', '', array('guid' => $_msgVal['guid'],
                                'from' => IIF($_msgVal['submittype'] == SWIFT_ChatQueue::SUBMIT_STAFF, 'staff', 'user'),
                                'fullname' => preg_replace('/&amp;#(x[a-f0-9]+|[0-9]+);/i', '&#$1;', htmlspecialchars($_msgVal['name'])),
                                'visitorsessionid' => IIF(empty($_msgVal['staffid']), $_val['visitorsessionid']),
                                'userid' => IIF(empty($_msgVal['staffid']), $_val['userid']), 'type' => $_msgArray['contents'][1]));
                        }
                    }
                }
            }

            $this->XML->EndParentTag('chat');
        }

        /**
         * Process GUID List for confirmation
         */
        if (_is_array($_winappGUIDList)) {
            foreach ($_winappGUIDList as $_key => $_val) {
                $_messageText = '';
                if (isset($_val['msg']) && !empty($_val['msg'])) {
                    $_messageText = $_val['msg'];
                }
                if ($_val['status'] == 1) {
                    $this->XML->AddTag('confirmation', $_messageText, array('guid' => $_val['guid'], 'status' => '1'));
                } else {
                    $this->XML->AddTag('confirmation', $_messageText, array('guid' => $_val['guid'], 'status' => '0'));
                }
            }
        }

        $this->XML->EndParentTag('events');

        // Update the last activity for all the chats that are active for this staff member
        if (count($_updateChatObjectIDList)) {
            SWIFT_Session::UpdateHeartbeatOnTypeID($_updateChatObjectIDList, SWIFT_Interface::INTERFACE_CHAT);
        }

        SWIFT_ChatQueue::DeleteGUID($_clearGUIDList);

        $this->XML->EchoXMLWinapp();

        return true;
    }

    /**
     * Return attributes as array
     *
     * @author Varun Shoor
     * @param array $_SimpleXMLObject
     * @return array
     */
    public static function SimpleXMLAttributes($_SimpleXMLObject)
    {
        $_returnContainer = array();

        foreach ($_SimpleXMLObject as $_key => $_val) {
            $_finalKey = (string)$_key;

            $_returnContainer[$_finalKey] = (string)$_val;
        }

        return $_returnContainer;
    }

    /**
     * Processes the Incoming Winapp Events
     *
     * @author Varun Shoor
     * @param string $_xmlText The XML Text Data
     * @return mixed Processed GUID List Array on Success, "false" otherwise
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function ProcessIncomingWinappEvents($_xmlText)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_XMLObject = @simplexml_load_string($_xmlText);
        if ($_XMLObject === false) {
            echo 'Invalid XML Received';
            echo $_xmlText;

            return false;
        }

        $_xmlContainerArray = $this->XML->XMLToTree($_xmlText);

        /**
         * BEGIN: Confirmation Messages Processing
         */
        $_guidList = array();
        if (isset($_XMLObject->confirmation)) {
            foreach ($_XMLObject->confirmation as $_ConfirmationObject) {
                $_attributeContainer = self::SimpleXMLAttributes($_ConfirmationObject->attributes());
                $_guid = $_attributeContainer['guid'];
                $_status = (int)($_attributeContainer['status']);

                if (empty($_guid)) {
                    throw new SWIFT_Exception('Invalid GUID Received');
                }

                if ($_status == 1) {
                    $_guidList[] = $_guid;
                } else {
                    throw new SWIFT_Exception('Invalid Status for GUID: ' . $_guid);
                }
            }
        }

        if (count($_guidList)) {
            SWIFT_ChatQueue::DeleteGUID($_guidList);
        }

        /**
         * BEGIN: Chat Events Processing
         */
        $_processedGUIDList = array();

        if (isset($_XMLObject->chat)) {
            foreach ($_XMLObject->chat as $_ChatObject) {
                $_attributeContainer = self::SimpleXMLAttributes($_ChatObject->attributes());
                $_chatObjectID = $_attributeContainer['chatobjectid'];
                if (empty($_chatObjectID)) {
                    throw new SWIFT_Exception('Invalid Chat Object ID');
                    continue;
                }

                if (isset($_ChatObject->chataction)) {
                    foreach ($_ChatObject->chataction as $_ChatActionObject) {
                        $_attributeContainer = self::SimpleXMLAttributes($_ChatActionObject->attributes());

                        $_eventValue = (string)$_ChatActionObject;

                        $_resultGUID = $this->ProcessWinappEventChatActions($_chatObjectID, $_attributeContainer, $_eventValue);
                        if ($_resultGUID) {
                            $_processedGUIDList[] = $_resultGUID;
                        }
                    }
                }

                if (isset($_ChatObject->message)) {
                    foreach ($_ChatObject->message as $_ChatMessageObject) {
                        $_attributeContainer = self::SimpleXMLAttributes($_ChatMessageObject->attributes());

                        $_eventMessage = (string)$_ChatMessageObject;

                        $_resultGUID = $this->ProcessWinappEventMessages($_chatObjectID, $_attributeContainer, $_eventMessage);
                        if ($_resultGUID) {
                            $_processedGUIDList[] = $_resultGUID;
                        }
                    }
                }

                if (isset($_ChatObject->typing)) {
                    foreach ($_ChatObject->typing as $_ChatTypingObject) {
                        $_attributeContainer = self::SimpleXMLAttributes($_ChatTypingObject->attributes());
                        $_resultGUID = $this->ProcessWinappEventTypingNotifications($_chatObjectID, $_attributeContainer);
                        if ($_resultGUID) {
                            $_processedGUIDList[] = $_resultGUID;
                        }
                    }
                }

                if (isset($_ChatObject->email)) {
                    foreach ($_ChatObject->email as $_ChatEmailObject) {
                        $_attributeContainer = self::SimpleXMLAttributes($_ChatEmailObject->attributes());

                        $_emailChatNotes = (string)$_ChatEmailObject;

                        $_resultGUID = $this->ProcessWinappEventEmailChat($_chatObjectID, $_attributeContainer, $_emailChatNotes);
                        if ($_resultGUID) {
                            $_processedGUIDList[] = $_resultGUID;
                        }
                    }
                }

                $_transferList = array();
                if (isset($_val['children']['transfer'])) {
                    $_transferList = $_val['children']['transfer'];
                }

                if (isset($_ChatObject->transfer)) {
                    foreach ($_ChatObject->transfer as $_ChatTransferObject) {
                        $_attributeContainer = self::SimpleXMLAttributes($_ChatTransferObject->attributes());
                        $_transferNodeContents = (string)$_ChatTransferObject;

                        $_resultGUID = $this->ProcessWinappEventTransferNotifications($_chatObjectID, $_attributeContainer, $_transferNodeContents);
                        if ($_resultGUID) {
                            $_processedGUIDList[] = $_resultGUID;
                        }
                    }
                }

                // Begin Hook: desktop_incoming_chat
                unset($_hookCode);
                ($_hookCode = SWIFT_Hook::Execute('desktop_incoming_chat')) ? eval($_hookCode) : false;
                // End Hook

            }
        }

        /**
         * BEGIN: Visitor Action Processing
         */
        if (isset($_XMLObject->visitor)) {
            foreach ($_XMLObject->visitor as $_VisitorObject) {
                $_attributeContainer = self::SimpleXMLAttributes($_VisitorObject->attributes());

                $_visitorSessionID = $_attributeContainer['sessionid'];
                if (empty($_visitorSessionID)) {
                    continue;
                }

                if (isset($_VisitorObject->ban)) {
                    foreach ($_VisitorObject->ban as $_VisitorBanObject) {
                        $_attributeContainer = self::SimpleXMLAttributes($_VisitorBanObject->attributes());

                        $_resultGUID = $this->ProcessWinappVisitorBan($_visitorSessionID, $_attributeContainer);
                        if ($_resultGUID) {
                            $_processedGUIDList[] = $_resultGUID;
                        }
                    }
                }

                if (isset($_VisitorObject->engage)) {
                    foreach ($_VisitorObject->engage as $_VisitorEngageObject) {
                        $_attributeContainer = self::SimpleXMLAttributes($_VisitorEngageObject->attributes());
                        $_resultGUID = $this->ProcessWinappVisitorEngage($_visitorSessionID, $_attributeContainer);
                        if ($_resultGUID) {
                            $_processedGUIDList[] = $_resultGUID;
                        }
                    }
                }

                if (isset($_VisitorObject->note)) {
                    foreach ($_VisitorObject->note as $_VisitorNoteObject) {
                        $_noteValue = (string)$_VisitorNoteObject;
                        $_attributeContainer = self::SimpleXMLAttributes($_VisitorNoteObject->attributes());

                        $_resultGUID = $this->ProcessWinappVisitorNote($_visitorSessionID, $_attributeContainer, $_noteValue);
                        if ($_resultGUID) {
                            $_processedGUIDList[] = $_resultGUID;
                        }
                    }
                }

                // Begin Hook: desktop_incoming_visitor
                unset($_hookCode);
                ($_hookCode = SWIFT_Hook::Execute('desktop_incoming_visitor')) ? eval($_hookCode) : false;
                // End Hook
            }
        }

        // Begin Hook: desktop_incoming
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('desktop_incoming')) ? eval($_hookCode) : false;
        // End Hook

        return $_processedGUIDList;
    }

    /**
     * Processes the Winapp Visitor Actions and Bans the Specified Visitor
     *
     * @author Varun Shoor
     * @param string $_visitorSessionID The Visitor Session ID
     * @param array $_attributes The Attributes Container
     * @return array
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    private function ProcessWinappVisitorBan($_visitorSessionID, $_attributes)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_SWIFT->Staff->GetPermission('winapp_lrcaninsertban') == '0') {
            return array('guid' => $_attributes['guid'], 'status' => '0', 'msg' => $_SWIFT->Language->Get('nopermissiontext'));
        }

        $_SWIFT_VisitorObject = new SWIFT_Visitor($_visitorSessionID);
        if (!$_SWIFT_VisitorObject instanceof SWIFT_Visitor || !$_SWIFT_VisitorObject->GetIsClassLoaded()) {
            return array('guid' => $_attributes['guid'], 'status' => '0');
        }

        $_banType = SWIFT_Visitor::BAN_IP;

        if ($_attributes['type'] == 'ip') {
            $_banType = SWIFT_Visitor::BAN_IP;
        } else if ($_attributes['type'] == 'classa') {
            $_banType = SWIFT_Visitor::BAN_CLASSA;
        } else if ($_attributes['type'] == 'classb') {
            $_banType = SWIFT_Visitor::BAN_CLASSB;
        } else if ($_attributes['type'] == 'classc') {
            $_banType = SWIFT_Visitor::BAN_CLASSC;
        }

        $_visitorSession = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "sessions WHERE sessionid = '" . $this->Database->Escape($_visitorSessionID) . "'");
        if (!$_visitorSession || !isset($_visitorSession['sessionid']) || empty($_visitorSession['sessionid'])) {
            return array('guid' => $_attributes['guid'], 'status' => '0');
        }

        if (SWIFT_VisitorBan::BanExists($_visitorSession['ipaddress'])) {
            $_SWIFT_VisitorObject->DeleteVisitorFootprints();

            return array('guid' => $_attributes['guid'], 'status' => '1');
        }

        if ($_SWIFT_VisitorObject->Ban($_banType, $_SWIFT->Staff->GetStaffID())) {
            return array('guid' => $_attributes['guid'], 'status' => '1');
        }

        return array('guid' => $_attributes['guid'], 'status' => '0');
    }

    /**
     * Processes the Winapp Visitor Actions and Engages the Specified Visitor
     *
     * @author Varun Shoor
     * @param string $_visitorSessionID The Visitor Session ID
     * @param array $_attributes The Attributes Container
     * @return array
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    private function ProcessWinappVisitorEngage($_visitorSessionID, $_attributes)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_VisitorObject = new SWIFT_Visitor($_visitorSessionID);
        if (!$_SWIFT_VisitorObject instanceof SWIFT_Visitor || !$_SWIFT_VisitorObject->GetIsClassLoaded()) {
            return array('guid' => $_attributes['guid'], 'status' => '0');
        }

        $_proactiveType = SWIFT_Visitor::PROACTIVE_ENGAGE;

        if ($_attributes['type'] == 'engage') {
            $_proactiveType = SWIFT_Visitor::PROACTIVE_ENGAGE;
        } else if ($_attributes['type'] == 'inline') {
            $_proactiveType = SWIFT_Visitor::PROACTIVE_INLINE;
        }

        if ($_SWIFT_VisitorObject->Engage($_proactiveType, $_SWIFT->Staff->GetStaffID())) {
            return array('guid' => $_attributes['guid'], 'status' => '1');
        }

        return array('guid' => $_attributes['guid'], 'status' => '0');
    }

    /**
     * Processes the Winapp Visitor Actions and add a Visitor Note
     *
     * @author Varun Shoor
     * @param string $_visitorSessionID The Visitor Session ID
     * @param array $_attributes The Attributes Container
     * @param array $_value The Value Holder
     * @return array
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    private function ProcessWinappVisitorNote($_visitorSessionID, $_attributes, $_value)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return array('guid' => $_attributes['guid'], 'status' => '0');
        }

        $_SWIFT_VisitorObject = new SWIFT_Visitor($_visitorSessionID);
        if (!$_SWIFT_VisitorObject instanceof SWIFT_Visitor || !$_SWIFT_VisitorObject->GetIsClassLoaded()) {
            return array('guid' => $_attributes['guid'], 'status' => '0');
        }

        if ($_SWIFT_VisitorObject->AddNote($_value, $_SWIFT->Staff->GetStaffID())) {
            return array('guid' => $_attributes['guid'], 'status' => '1');
        }

        return array('guid' => $_attributes['guid'], 'status' => '0');
    }

    /**
     * Processes the Winapp Chat Actions like Enter, Leave, Refusal of chats
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @param array $_attributes The Attributes Container
     * @param string $_value The Value Holder
     * @return array
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    private function ProcessWinappEventChatActions($_chatObjectID, $_attributes, $_value)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_ChatObject = false;
        try {
            $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
        }
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded() ||
            !$_SWIFT_ChatObject->_SWIFT_ChatQueueObject instanceof SWIFT_ChatQueue ||
            !$_SWIFT_ChatObject->_SWIFT_ChatQueueObject->GetIsClassLoaded()) {
            return array('guid' => $_attributes['guid'], 'status' => '0');
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_isConference = false;

        /**
         * ACTION: Staff enters the chat
         */
        if ($_attributes['type'] == SWIFT_ChatQueue::CHATACTION_CHATENTER || $_attributes['type'] == SWIFT_ChatQueue::CHATACTION_OBSERVE || $_attributes['type'] == SWIFT_ChatQueue::CHATACTION_CHATJOIN) {
            $_isObserver = $_ignoreCustomerAddition = $_onlyForObservers = false;

            if ($_attributes['type'] == SWIFT_ChatQueue::CHATACTION_OBSERVE) {
                $_isObserver = true;
                $_ignoreCustomerAddition = true;
                $_onlyForObservers = true;
            }

            $_updateStaffID = $_updateStaffName = false;

            if ($_SWIFT_ChatObject->GetChatObjectID() != '') {
                // How many chat childs do we have?
                $_chatChildCount = $_SWIFT_ChatObject->GetChatChildCount();

                $_eventDispatched = false;

                // None?
                if ($_chatChildCount == 0) {
                    // Set the staff id, staffname etc because we are just accepting a chat
                    $_updateStaffID = $_SWIFT->Staff->GetStaffID();
                    $_updateStaffName = $_SWIFT->Staff->GetProperty('fullname');

                    // Most probably a conference... because there is someone else in the chat!
                } else {
                    $extendedquery = '';
                    // Enter the CHATENTER action as we are in a conference

                    if (($_chatChildCount >= 1 && $_SWIFT_ChatObject->GetProperty('chattype') == SWIFT_Chat::CHATTYPE_CLIENT) ||
                        ($_chatChildCount >= 2 && $_SWIFT_ChatObject->GetProperty('chattype') == SWIFT_Chat::CHATTYPE_STAFF) || $_attributes['type'] == SWIFT_ChatQueue::CHATACTION_CHATJOIN) {
                        $_isConference = true;
                    }

                    if ($_isConference) {
                        if (!isset($_attributes['join']) && $_attributes['type'] != SWIFT_ChatQueue::CHATACTION_CHATJOIN && $_SWIFT_ChatObject->GetProperty('transferstatus') != SWIFT_Chat::TRANSFER_PENDING && $_attributes['type'] != SWIFT_ChatQueue::CHATACTION_OBSERVE) {
                            $_attributes['join'] = '0';
                        } else if (!isset($_attributes['join']) && $_attributes['type'] == SWIFT_ChatQueue::CHATACTION_CHATJOIN) {
                            $_attributes['join'] = '1';
                        }

                        if (isset($_attributes['join']) && $_attributes['join'] == '0') {
                            $_staffNameList = array();
                            $this->Database->Query("SELECT staffid, isobserver FROM " . TABLE_PREFIX . "chatchilds
                                                   WHERE chatobjectid = '" . ($_SWIFT_ChatObject->GetChatObjectID()) . "'");
                            while ($this->Database->NextRecord()) {
                                if ($this->Database->Record['staffid'] != '0' && $this->Database->Record['isobserver'] == '0' && isset($_staffCache[$this->Database->Record['staffid']])) {
                                    $_staffNameList[] = $_staffCache[$this->Database->Record['staffid']]['fullname'];
                                }
                            }

                            if (count($_staffNameList)) {
                                return array('guid' => $_attributes['guid'], 'status' => '0', 'msg' => sprintf($this->Language->Get('confirmconference'), htmlentities(implode(', ', $_staffNameList), ENT_COMPAT)));
                            }
                        }

                        $_eventDispatched = true;
                        $_finalStaffName = $_SWIFT->Staff->GetProperty('fullname');
                        if ($_SWIFT->Staff->GetProperty('designation') != '') {
                            $_finalStaffName .= ' (' . htmlspecialchars($_SWIFT->Staff->GetProperty('designation')) . ')';
                        }

                        $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->AddActionToQueue(SWIFT_ChatQueue::SUBMIT_STAFF,
                            SWIFT_ChatQueue::CHATACTION_CHATENTER, $_finalStaffName, $_ignoreCustomerAddition,
                            $_onlyForObservers);
                    }
                }

                if (!$_eventDispatched && $_SWIFT_ChatObject->GetProperty('chattype') == SWIFT_Chat::CHATTYPE_STAFF) {
                    $_eventDispatched = true;
                    $_finalStaffName = $_SWIFT->Staff->GetProperty('fullname');
                    if ($_SWIFT->Staff->GetProperty('designation') != '') {
                        $_finalStaffName .= ' (' . htmlspecialchars($_SWIFT->Staff->GetProperty('designation')) . ')';
                    }

                    $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->AddActionToQueue(SWIFT_ChatQueue::SUBMIT_STAFF,
                        SWIFT_ChatQueue::CHATACTION_CHATENTER, $_finalStaffName, $_ignoreCustomerAddition,
                        $_onlyForObservers);
                }

                // Chat Acceptance Routines
                if ($_SWIFT_ChatObject->GetProperty('chattype') == SWIFT_Chat::CHATTYPE_STAFF &&
                    $_SWIFT_ChatObject->GetProperty('userid') == $_SWIFT->Staff->GetStaffID()) {
                    // Dont accept the chat if this user initiated it, wait for other user
                } else if ($_SWIFT_ChatObject->GetProperty('chattype') == SWIFT_Chat::CHATTYPE_STAFF && $_SWIFT_ChatObject->GetProperty('staffid') == $_SWIFT->Staff->GetStaffID()) {
                    $_SWIFT_ChatObject->UpdateChatStatus(SWIFT_Chat::CHAT_INCHAT, $_SWIFT_ChatObject->GetProperty('staffid'), $_SWIFT_ChatObject->GetProperty('staffname'));

                } else if (!empty($_updateStaffID) && !empty($_updateStaffName) && $_SWIFT_ChatObject->GetProperty('chattype') != SWIFT_Chat::CHATTYPE_STAFF) {
                    //updateChatHitStatus($_chatobject["chatobjectid"], STAFF_ACCEPT, $_updstaffid);
                    $_SWIFT_ChatObject->UpdateChatStatus(SWIFT_Chat::CHAT_INCHAT, $_updateStaffID, $_updateStaffName);
                }

                // Accept the Transfer Request
                if ($_SWIFT_ChatObject->GetProperty('transfertoid') == $_SWIFT->Staff->GetStaffID() && $_SWIFT_ChatObject->GetProperty('transferstatus') == SWIFT_Chat::TRANSFER_PENDING) {
                    $_SWIFT_ChatObject->UpdateTransfer($_SWIFT_ChatObject->GetProperty('transferfromid'), $_SWIFT_ChatObject->GetProperty('transfertoid'), SWIFT_Chat::TRANSFER_ACCEPTED, DATENOW);
                    /*
                     * BUG FIX - Ravi Sharma
                     *
                     * SWIFT-2885 Chat shows ‘Pending’ in Kayako Desktop to all staff members, if staff declines a transfer chat
                     *
                     * Comments: In case of open queue, transfertoid should not check with staffid.
                     */
                } else if ($_SWIFT_ChatObject->GetProperty('transferstatus') == SWIFT_Chat::TRANSFER_PENDING && $this->Settings->Get('ls_routingmode') == 'openqueue') {
                    $_SWIFT_ChatObject->UpdateTransfer($_SWIFT_ChatObject->GetProperty('transferfromid'), $_SWIFT->Staff->GetStaffID(), SWIFT_Chat::TRANSFER_ACCEPTED, DATENOW);
                }

                // Insert the chat child id
                $_chatChildID = SWIFT_ChatChild::Insert($_SWIFT_ChatObject, $_SWIFT->Staff->GetStaffID(), false, $_isObserver);

                // Begin Hook: desktop_chat_evententer
                unset($_hookCode);
                ($_hookCode = SWIFT_Hook::Execute('desktop_chat_evententer')) ? eval($_hookCode) : false;
                // End Hook

                $_chatConversationContainer = $_SWIFT_ChatObject->GetConversationArray();
                if (_is_array($_chatConversationContainer)) {
                    foreach ($_chatConversationContainer as $_message) {
                        if ($_message['msgtype'] == 'system') {
                            continue;
                        }

                        /*
                         * BUG FIX - Ravi Sharma
                         *
                         * SWIFT-3097 Chat logs doesn't apprear in correct sequence.
                         */
                        $_msgArray = array('type' => SWIFT_ChatQueue::CHATACTION_MESSAGE, 'contents' => $_message['messageoriginal'], 'base64' => $_message['base64'], 'timestamp' => $_message['dateline']);
                        $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->Insert($_chatChildID, '0', $_message['name'], serialize($_msgArray),
                            $_message['type'], SWIFT_ChatQueue::GenerateGUID(), $_message['submittype']);
                    }
                }

                return array('guid' => $_attributes['guid'], 'status' => '1');
            }


            /**
             * ACTION: Staff leaves the chat
             */
        } else if ($_attributes['type'] == SWIFT_ChatQueue::CHATACTION_CHATLEAVE) {
            if ($_SWIFT_ChatObject->GetChatObjectID() != '') {
                $_SWIFT_ChatObject->EndChat(SWIFT_Chat::CHATEND_STAFF);

                // Begin Hook: desktop_chatevent_leave
                unset($_hookCode);
                ($_hookCode = SWIFT_Hook::Execute('desktop_chatevent_leave')) ? eval($_hookCode) : false;
                // End Hook

                return array('guid' => $_attributes['guid'], 'status' => '1');
            }


            /**
             * ACTION: Staff refuses the chat
             */
        } else if ($_attributes['type'] == SWIFT_ChatQueue::CHATACTION_CHATREFUSE) {
            if ($_SWIFT_ChatObject->GetChatObjectID()) {
                $_roundRobinStaffID = $_SWIFT_ChatObject->GetRoundRobinStaff($_SWIFT_ChatObject->GetProperty('departmentid'), array($_SWIFT->Staff->GetStaffID()));

                //updateChatHitStatus($_chatobject['chatobjectid'], STAFF_REFUSECHAT, $roundrobinstaffid);

                if (!$_roundRobinStaffID || $_roundRobinStaffID == $_SWIFT->Staff->GetStaffID()) {
                    // Change the status because no other staff is online
                    $_SWIFT_ChatObject->UpdateChatStatus(SWIFT_Chat::CHAT_NOANSWER);
                } else {
                    // If the round robin mode is active then the chat is routed to the next available staff
                    if ($this->Settings->Get('ls_routingmode') == 'roundrobin') {
                        $_SWIFT_ChatObject->UpdateChatStatus(SWIFT_Chat::CHAT_INCOMING, $_roundRobinStaffID, $_staffCache[$_roundRobinStaffID]['fullname']);
                        // In case of open queue, the staff is set to 0
                    } else if ($this->Settings->Get('ls_routingmode') == 'openqueue') {
                        $_SWIFT_ChatObject->UpdateChatStatus(SWIFT_Chat::CHAT_INCOMING, 0, '', true);
                    }
                }

                $_SWIFT_ChatObject->UpdateRoundRobinTimeline();

                // Begin Hook: desktop_chatevent_refuse
                unset($_hookCode);
                ($_hookCode = SWIFT_Hook::Execute('desktop_chatevent_refuse')) ? eval($_hookCode) : false;
                // End Hook

                return array('guid' => $_attributes['guid'], 'status' => '1');
            }
        }

        return array('guid' => $_attributes['guid'], 'status' => '0');
    }

    /**
     * Processes the incoming Winapp Message Packages
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @param array $_attributes The Attributes Container
     * @param string $_value The Value Holder
     * @return array
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    private function ProcessWinappEventMessages($_chatObjectID, $_attributes, $_value)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded() || !$_SWIFT_ChatObject->_SWIFT_ChatQueueObject instanceof SWIFT_ChatQueue || !$_SWIFT_ChatObject->_SWIFT_ChatQueueObject->GetIsClassLoaded()) {
            return array('guid' => $_attributes['guid'], 'status' => '0');
        }

        /**
         * Message Type: text/message
         */
        if ($_attributes['type'] == SWIFT_ChatQueue::CHATACTION_MESSAGE || $_attributes['type'] == SWIFT_ChatQueue::CHATACTION_TEXT) {
            $_timeStamp = false;
            if (isset($_attributes['timestamp'])) {
                $_timeStamp = (int)($_attributes['timestamp']);
            }
            $_queueResult = $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->AddMessageToQueue(SWIFT_ChatQueue::MESSAGE_STAFF, SWIFT_ChatQueue::SUBMIT_STAFF, IIF($_attributes['base64'] == '1', $_value, base64_encode($_value)), true, $_timeStamp);

            if ($_queueResult) {
                return array('guid' => $_attributes['guid'], 'status' => '1');
            }

            /**
             * Message Type: image
             */
        } else if ($_attributes['type'] == SWIFT_ChatQueue::CHATACTION_IMAGE) {
            $_queueResult = $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->AddActionToQueue(SWIFT_ChatQueue::SUBMIT_STAFF, SWIFT_ChatQueue::CHATACTION_IMAGE, IIF($_attributes['base64'] == '1', base64_decode($_value), $_value));

            if ($_queueResult) {
                return array('guid' => $_attributes['guid'], 'status' => '1');
            }

            /**
             * Message Type: url
             */
        } else if ($_attributes['type'] == SWIFT_ChatQueue::CHATACTION_URL) {
            $_queueResult = $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->AddActionToQueue(SWIFT_ChatQueue::SUBMIT_STAFF, SWIFT_ChatQueue::CHATACTION_URL, IIF($_attributes['base64'] == '1', base64_decode($_value), $_value));

            if ($_queueResult) {
                return array('guid' => $_attributes['guid'], 'status' => '1');
            }

            /**
             * Message Type: code
             */
        } else if ($_attributes['type'] == SWIFT_ChatQueue::CHATACTION_CODE) {
            $_queueResult = $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->AddActionToQueue(SWIFT_ChatQueue::SUBMIT_STAFF,
                SWIFT_ChatQueue::CHATACTION_CODE, array(IIF($_attributes['base64'] == '1', base64_decode($_value), $_value),
                    $_attributes['userdata']));

            if ($_queueResult) {
                return array('guid' => $_attributes['guid'], 'status' => '1');
            }

        }

        return array('guid' => $_attributes['guid'], 'status' => '0');
    }

    /**
     * Processes the "User is Typing" notifications received from winapp
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @param array $_attributes The Attributes Container
     * @return array
     * @throws SWIFT_Chat_Exception If the Class is not Loaded
     */
    private function ProcessWinappEventTypingNotifications($_chatObjectID, $_attributes)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded() || !$_SWIFT_ChatObject->_SWIFT_ChatQueueObject instanceof SWIFT_ChatQueue || !$_SWIFT_ChatObject->_SWIFT_ChatQueueObject->GetIsClassLoaded()) {
            return array('guid' => $_attributes['guid'], 'status' => '0');
        }

        $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->AddActionToQueue(SWIFT_ChatQueue::SUBMIT_STAFF, SWIFT_ChatQueue::CHATACTION_TYPING, array($_SWIFT->Staff->GetProperty('fullname'), $_attributes['type']));

        return array('guid' => $_attributes['guid'], 'status' => '1');
    }

    /**
     * Processes the Email Chat function
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @param array $_attributes The Attributes Container
     * @param string $_emailNotes The Email Notes
     * @return array
     * @throws SWIFT_Chat_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    private function ProcessWinappEventEmailChat($_chatObjectID, $_attributes, $_emailNotes)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded() || !$_SWIFT_ChatObject->_SWIFT_ChatQueueObject instanceof SWIFT_ChatQueue || !$_SWIFT_ChatObject->_SWIFT_ChatQueueObject->GetIsClassLoaded()) {
            return array('guid' => $_attributes['guid'], 'status' => '0');
        }

        $_emailAddresses = base64_decode($_attributes['addresses']);

        if (!empty($_attributes['subject'])) {
            $_emailSubject = base64_decode($_attributes['subject']);
        } else {
            $_emailSubject = '';
        }

        $_emailList = array();
        if (stristr($_emailAddresses, ';')) {
            $_emailArray = explode(';', $_emailAddresses);
            foreach ($_emailArray as $_key => $_val) {
                $_val = trim($_val);

                if (IsEmailValid($_val)) {
                    $_emailList[] = $_val;
                }
            }
        } else if (IsEmailValid($_emailAddresses)) {
            $_emailList[] = $_emailAddresses;
        }

        // No valid email found?
        if (!count($_emailList)) {
            return array('guid' => $_attributes['guid'], 'status' => '0');
        }

        $_SWIFT_ChatObject->Email($_emailList, $_emailSubject, $_emailNotes);

        return array('guid' => $_attributes['guid'], 'status' => '1');
    }

    /**
     * Processes the "transfer" notifications received from winapp
     *
     * @author Varun Shoor
     * @param int $_chatObjectID The Chat Object ID
     * @param array $_attributes The Attributes Container
     * @param string $_nodeContents The Node Contents
     * @return array
     * @throws SWIFT_Chat_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    private function ProcessWinappEventTransferNotifications($_chatObjectID, $_attributes, $_nodeContents)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded() || !$_SWIFT_ChatObject->_SWIFT_ChatQueueObject instanceof SWIFT_ChatQueue || !$_SWIFT_ChatObject->_SWIFT_ChatQueueObject->GetIsClassLoaded()) {
            return array('guid' => $_attributes['guid'], 'status' => '0');
        }

        // Load the transfer manager
        $this->Load->Library('Chat:ChatTransferManager', array($_SWIFT_ChatObject), true, false, APP_LIVECHAT);
        $_rejectTransferToStaffID = false;

        // Transfer to >> Staff
        if ($_attributes['type'] == SWIFT_ChatTransferManager::TYPE_STAFF && isset($_attributes['data']) && !empty($_attributes['data'])) {
            $_transferResult = $this->ChatTransferManager->TransferToStaff($_SWIFT->Staff->GetStaffID(), $_attributes['data']);

            // Transfer to >> Staff Group
        } else if ($_attributes['type'] == SWIFT_ChatTransferManager::TYPE_STAFFGROUP && isset($_attributes['data']) && !empty($_attributes['data'])) {
            $_transferResult = $this->ChatTransferManager->TransferToStaffGroup($_SWIFT->Staff->GetStaffID(), $_attributes['data']);

            // Transfer to >> Department
        } else if ($_attributes['type'] == SWIFT_ChatTransferManager::TYPE_DEPARTMENT && isset($_attributes['data']) && !empty($_attributes['data'])) {
            $_transferResult = $this->ChatTransferManager->TransferToDepartment($_SWIFT->Staff->GetStaffID(), $_attributes['data']);

            // Transfer to >> Skill
        } else if ($_attributes['type'] == SWIFT_ChatTransferManager::TYPE_SKILL && isset($_attributes['data']) && !empty($_attributes['data'])) {
            $_transferResult = $this->ChatTransferManager->TransferToSkill($_SWIFT->Staff->GetStaffID(), $_attributes['data']);

            // Transfer Rejected?
        } else if ($_attributes['type'] == SWIFT_ChatTransferManager::TYPE_REJECT) {
            $_rejectTransferToStaffID = $_SWIFT_ChatObject->GetProperty('transfertoid');
            $_transferResult = $this->ChatTransferManager->RejectTransfer($_SWIFT->Staff->GetStaffID(), $_nodeContents);

        } else {
            $_transferResult = false;
        }

        if (!$_transferResult || (($_attributes['type'] == SWIFT_ChatTransferManager::TYPE_STAFF || $_attributes['type'] == SWIFT_ChatTransferManager::TYPE_STAFFGROUP || $_attributes['type'] == SWIFT_ChatTransferManager::TYPE_DEPARTMENT || $_attributes['type'] == SWIFT_ChatTransferManager::TYPE_SKILL) && !isset($_staffCache[$_transferResult]))) {
            return array('guid' => $_attributes['guid'], 'status' => '1');
        }

        if ($_attributes['type'] == SWIFT_ChatTransferManager::TYPE_STAFF || $_attributes['type'] == SWIFT_ChatTransferManager::TYPE_STAFFGROUP || $_attributes['type'] == SWIFT_ChatTransferManager::TYPE_DEPARTMENT || $_attributes['type'] == SWIFT_ChatTransferManager::TYPE_SKILL) {
            // By now we have the staff id to which the chat was transfered..
            $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->AddActionToQueue(SWIFT_ChatQueue::SUBMIT_STAFF, SWIFT_ChatQueue::CHATACTION_SYSTEMMESSAGE, sprintf($this->Language->Get('transferattempt'), text_to_html_entities($_staffCache[$_transferResult]['fullname'])), true);
        } else if ($_attributes['type'] == SWIFT_ChatTransferManager::TYPE_REJECT && isset($_staffCache[$_rejectTransferToStaffID])) {
            if (empty($_nodeContents)) {
                $_rejectionReason = $this->Language->Get('na');
            } else {
                $_rejectionReason = $_nodeContents;
            }

            $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->AddActionToQueue(SWIFT_ChatQueue::SUBMIT_STAFF, SWIFT_ChatQueue::CHATACTION_SYSTEMMESSAGE, sprintf($this->Language->Get('transferrejected'), text_to_html_entities($_staffCache[$_rejectTransferToStaffID]['fullname']), htmlspecialchars($_rejectionReason)), true);
        }

        return array('guid' => $_attributes['guid'], 'status' => '1');
    }
}
