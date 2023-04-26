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

namespace LiveChat\Winapp;

use Controller_winapp;
use LiveChat\Models\Chat\SWIFT_Chat;
use LiveChat\Models\Chat\SWIFT_ChatChild;
use LiveChat\Models\Chat\SWIFT_ChatQueue;
use SWIFT;
use SWIFT_Exception;
use SWIFT_FileManager;
use SWIFT_ImageResize;
use SWIFT_XML;

/**
 * The Chat Misc Functions Controller
 *
 * @property SWIFT_XML $XML
 * @author Varun Shoor
 */
class Controller_Chat extends Controller_winapp
{
    // Core Constants
    const FILE_SCREENSHOT = 'screenshot';
    const FILE_IMAGE = 'image';
    const FILE_OTHER = 'other';

    // Staff <> Staff chat constants
    const REQUEST_CHAT = 'chat';
    const REQUEST_INVITE = 'invite';

    public $XML;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('livesupport');

        $this->Load->Library('Chat:ChatEventWinapp', [], true, false, APP_LIVECHAT);
    }

    /**
     * Upload a file for this chat
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Upload()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($_POST['sessionid']) || empty($_POST['sessionid']) || !isset($_POST['chatsessionid']) || empty($_POST['chatsessionid'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ChatObject = SWIFT_Chat::GetChatObjectFromSession($_POST['chatsessionid']);
        if (!$_ChatObject instanceof SWIFT_Chat || !$_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // No files uploaded? Throw an error!
        if (!_is_array($_FILES)) {
            return false;
        }

        // Now that we have the chat session, we need to upload the files..
        if (isset($_FILES['imagecontainer']) && _is_array($_FILES['imagecontainer'])) {
            $this->UploadImage($_ChatObject);
        }

        if (isset($_FILES['filecontainer']) && _is_array($_FILES['filecontainer'])) {
            $this->UploadFile($_ChatObject);
        }

        echo '1';

        return true;
    }

    /**
     * Upload an Image
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject The SWIFT_Chat Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function UploadImage(SWIFT_Chat $_SWIFT_ChatObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if (!isset($_FILES['imagecontainer']) || !_is_array($_FILES['imagecontainer']) || !_is_array($_FILES['imagecontainer']['name'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // File expires in 1 day
        $_fileExpiry = time() + 86400;
        $_fileManagerObjectCache = array();

        $_fileCount = count($_FILES['imagecontainer']['name']);

        for ($_ii = 0; $_ii < $_fileCount; $_ii++) {
            $_fileName = $_FILES['imagecontainer']['name'][$_ii];
            $_temporaryName = $_FILES['imagecontainer']['tmp_name'][$_ii];
            $_fileSize = $_FILES['imagecontainer']['size'][$_ii];

            // First verify the extensions
            $_pathInfoContainer = pathinfo($_fileName);
            if (isset($_pathInfoContainer['extension'])) {
                $_pathInfoContainer['extension'] = mb_strtolower($_pathInfoContainer['extension']);
            }

            if (empty($_pathInfoContainer) || !_is_array($_pathInfoContainer) || !isset($_pathInfoContainer['extension']) || ($_pathInfoContainer['extension'] != 'png' && $_pathInfoContainer['extension'] != 'gif' && $_pathInfoContainer['extension'] != 'jpg' && $_pathInfoContainer['extension'] != 'jpeg')) {
                continue;
            }

            // Once we have validated the file extension, we attempt to store it
            $_fileID = SWIFT_FileManager::Create($_temporaryName, $_fileName, $_fileExpiry);
            if (!$_fileID) {
                throw new SWIFT_Exception(SWIFT_CREATEFAILED);
            }

            // Load the file object
            $_SWIFT_FileManagerObject = new SWIFT_FileManager($_fileID);
            if (!$_SWIFT_FileManagerObject instanceof SWIFT_FileManager || !$_SWIFT_FileManagerObject->GetIsClassLoaded()) {
                continue;
            }

            // Add to object cache
            $_fileManagerObjectCache[$_fileName][0] = $_SWIFT_FileManagerObject;

            // Now that the temporary file has been created, we need to resize the image and create a thumbnail
            $_SWIFT_ImageResizeObject = new SWIFT_ImageResize($_SWIFT_FileManagerObject->GetPath());
            $_SWIFT_ImageResizeObject->SetSize(100, 100);
            $_SWIFT_ImageResizeObject->Resize();

            $_temporaryThumbnailPath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_CACHEDIRECTORY . '/' . substr(BuildHash(), 0, 15) . '.' . $_pathInfoContainer['extension'];
            $_resizeResult = $_SWIFT_ImageResizeObject->Save($_temporaryThumbnailPath);
            if (!$_resizeResult || !file_exists($_temporaryThumbnailPath)) {
                throw new SWIFT_Exception(SWIFT_CREATEFAILED);
            }

            // Create a temporary thumbnail file object
            $_fileID_Thumbnail = SWIFT_FileManager::Create($_temporaryThumbnailPath, $_fileName, $_fileExpiry);
            if (!$_fileID_Thumbnail) {
                throw new SWIFT_Exception(SWIFT_CREATEFAILED);
            }

            // Load the thumbnail object
            $_SWIFT_FileManagerObject_Thumbnail = new SWIFT_FileManager($_fileID_Thumbnail);
            if (!$_SWIFT_FileManagerObject_Thumbnail instanceof SWIFT_FileManager || !$_SWIFT_FileManagerObject_Thumbnail->GetIsClassLoaded()) {
                continue;
            }

            // Add to Cache
            $_fileManagerObjectCache[$_fileName][1] = $_SWIFT_FileManagerObject_Thumbnail;

            // By now we have stored both the original image file AND the resized image, its time to add the action to queue
            $_urlPrefix = SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/';
            $_queueResult = $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->AddActionToQueue(SWIFT_ChatQueue::SUBMIT_STAFF, SWIFT_ChatQueue::CHATACTION_UPLOADEDIMAGE, array($_urlPrefix . $_SWIFT_FileManagerObject->GetProperty('filename'), $_urlPrefix . $_SWIFT_FileManagerObject_Thumbnail->GetProperty('filename')));
        }

        return true;
    }

    /**
     * Upload a file
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject The SWIFT_Chat Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function UploadFile(SWIFT_Chat $_SWIFT_ChatObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if (!isset($_FILES['filecontainer']) || !_is_array($_FILES['filecontainer']) || !_is_array($_FILES['filecontainer']['name'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // File expires in 1 day
        $_fileExpiry = time() + 86400;
        $_fileManagerObjectCache = array();

        $_fileCount = count($_FILES['filecontainer']['name']);

        for ($_ii = 0; $_ii < $_fileCount; $_ii++) {
            $_fileName = $_FILES['filecontainer']['name'][$_ii];
            $_temporaryName = $_FILES['filecontainer']['tmp_name'][$_ii];
            $_fileSize = $_FILES['filecontainer']['size'][$_ii];

            // First verify the extensions
            $_pathInfoContainer = pathinfo($_fileName);
            if (isset($_pathInfoContainer['extension'])) {
                $_pathInfoContainer['extension'] = mb_strtolower($_pathInfoContainer['extension']);
            }

            if (empty($_pathInfoContainer) || !_is_array($_pathInfoContainer) || !isset($_pathInfoContainer['extension']) || empty($_pathInfoContainer['extension'])) {
                continue;
            }

            // Once we have validated the file extension, we attempt to store it BUT without ANY EXTENSION (for security reasons)
            $_fileID = SWIFT_FileManager::Create($_temporaryName, $_fileName, $_fileExpiry, true);
            if (!$_fileID) {
                throw new SWIFT_Exception(SWIFT_CREATEFAILED);
            }

            // Load the file object
            $_SWIFT_FileManagerObject = new SWIFT_FileManager($_fileID);
            if (!$_SWIFT_FileManagerObject instanceof SWIFT_FileManager || !$_SWIFT_FileManagerObject->GetIsClassLoaded()) {
                continue;
            }

            // Add to object cache
            $_fileManagerObjectCache[$_fileName][0] = $_SWIFT_FileManagerObject;

            // By now we have stored the file, its time to add the action to queue
            $_urlPrefix = SWIFT::Get('swiftpath') . SWIFT_FILESDIRECTORY . '/';
            $_queueResult = $_SWIFT_ChatObject->_SWIFT_ChatQueueObject->AddActionToQueue(SWIFT_ChatQueue::SUBMIT_STAFF, SWIFT_ChatQueue::CHATACTION_FILE, array($_fileName, $_fileID, $_SWIFT_FileManagerObject->GetProperty('filehash')));
        }

        return true;
    }

    /**
     * Start a Staff <> Staff chat
     *
     * @author Varun Shoor
     * @param int $_targetStaffID The Target Staff ID
     * @param mixed $_requestType The Request Type
     * @param int $_chatObjectID (OPTIONAL) In case of a conference request
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function StartStaff($_targetStaffID, $_requestType, $_chatObjectID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffCache = $_SWIFT->Cache->Get('staffcache');

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_targetStaffID) || ($_requestType != self::REQUEST_CHAT && $_requestType != self::REQUEST_INVITE) || !isset($_staffCache[$_targetStaffID])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->XML->BuildXML();

        // First check to see if the staff is online
        if (!SWIFT_Chat::IsStaffOnline($_targetStaffID)) {

            $this->XML->AddParentTag('kayako_livechat');
            $this->XML->AddTag('error', 'staffchatnotonline');
            $this->XML->EndParentTag('kayako_livechat');
            $this->XML->EchoXMLWinapp();

            return false;
        }

        $_SWIFT_ChatObject = false;

        // Is this an invite?
        if ($_requestType == self::REQUEST_INVITE && !empty($_chatObjectID)) {
            $_SWIFT_ChatObject = false;
            try {
                $_SWIFT_ChatObject = new SWIFT_Chat($_chatObjectID);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                $this->XML->AddParentTag('kayako_livechat');
                $this->XML->AddTag('error', sprintf($this->Language->Get('staffchatnotonline'), ($_chatObjectID)));
                $this->XML->EndParentTag('kayako_livechat');
                $this->XML->EchoXMLWinapp();

                return false;
            }

            SWIFT_ChatChild::Insert($_SWIFT_ChatObject, $_targetStaffID, true);
        } elseif ($_requestType == self::REQUEST_CHAT) {
            // Begin a Staff <> Staff chat
            $_SWIFT_ChatObject = SWIFT_Chat::Insert('', false, $_SWIFT->Staff->GetProperty('fullname'), $_SWIFT->Staff->GetProperty('email'), '',
                $_targetStaffID, $_staffCache[$_targetStaffID]['fullname'], false, '', SWIFT_Chat::CHATTYPE_STAFF, SWIFT::Get('IP'), false,
                false, $_SWIFT->Staff->GetStaffID());
        }

        // Begin a Staff <> Staff chat
        if ($_SWIFT_ChatObject instanceof SWIFT_Chat && $_SWIFT_ChatObject->GetIsClassLoaded()) {
            $this->XML->AddParentTag('kayako_livechat');
            $this->XML->AddParentTag('staffchat');
            $this->XML->AddTag('chatobjectid', $_SWIFT_ChatObject->GetChatObjectID());
            $this->XML->AddTag('chatsessionid', $_SWIFT_ChatObject->GetProperty('chatsessionid'));
            $this->XML->AddTag('staffname', $_staffCache[$_targetStaffID]['fullname']);
            $this->XML->AddTag('staffemail', $_staffCache[$_targetStaffID]['email']);
            $this->XML->EndParentTag('staffchat');
            $this->XML->EndParentTag('kayako_livechat');
            $this->XML->EchoXMLWinapp();

            return true;
        } else {
            $this->XML->AddParentTag('kayako_livechat');
            $this->XML->AddTag('error', 'staffnochatobjectcreate');
            $this->XML->EndParentTag('kayako_livechat');
            $this->XML->EchoXMLWinapp();

            return false;
        }
    }

}
