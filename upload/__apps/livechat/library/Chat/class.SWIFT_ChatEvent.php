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

namespace LiveChat\Library\Chat;

use LiveChat\Models\Chat\SWIFT_Chat;
use LiveChat\Models\Chat\SWIFT_ChatQueue;
use SWIFT_Library;

/**
 * The Live Chat Event Management Class. Used to handle dispatching and retrieval of chat related events and notifications.
 *
 * @author Varun Shoor
 */
class SWIFT_ChatEvent extends SWIFT_Library
{
    public $XML;
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');
    }

    /**
     * Dispatches a System Message packet
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject The SWIFT_Chat Object Pointer
     * @param string $_messageContents The Message Contents
     * @return bool "true" on Success, "false" otherwise
     */
    protected function DispatchSystemMessageChunk(SWIFT_Chat $_SWIFT_ChatObject, $_messageContents)
    {
        if (!$_SWIFT_ChatObject->GetIsClassLoaded()) {
            return false;
        }

        $this->XML->AddParentTag('chunk', array('guid' => '0'));
        $this->XML->AddTag('type', 'message');
        $this->XML->AddTag('message', $_messageContents);
        $this->XML->EndParentTag('chunk');

        $_SWIFT_ChatObject->AppendChatData(SWIFT_ChatQueue::MESSAGE_SYSTEM, SWIFT_ChatQueue::SUBMIT_SYSTEM, '', $_messageContents, false, SWIFT_ChatQueue::CHATACTION_SYSTEM);

        return true;
    }
}
