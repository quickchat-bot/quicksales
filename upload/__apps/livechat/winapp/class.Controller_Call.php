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
use LiveChat\Models\Call\SWIFT_Call;
use LiveChat\Models\Chat\SWIFT_Chat;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_FileManager;
use SWIFT_FileManager_Exception;
use SWIFT_XML;

/**
 * The Call Functions Controller
 *
 * @author Varun Shoor
 * @property SWIFT_XML $XML
 */
class Controller_Call extends Controller_winapp
{
    // Core Constants
    const STATE_CALLING = 1;
    const STATE_INCOMING = 2;
    const STATE_CONNECTED = 3;
    const STATE_DISCONNECTED = 4;
    const STATE_REJECTED = 5;
    const STATE_TRANSFERRED = 6;

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
     * The Call Event Update
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Event()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_CallObject = $_SWIFT_ChatObject = false;
        $_chatObjectID = $_userID = $_departmentID = 0;
        $_userFullName = $_userEmail = '';

        // Do we have a chat object?
        if (isset($_POST['chatobjectid']) && !empty($_POST['chatobjectid'])) {
            $_SWIFT_ChatObject = new SWIFT_Chat($_POST['chatobjectid']);

            $_chatObjectID = $_SWIFT_ChatObject->GetChatObjectID();
            $_userID = (int)($_SWIFT_ChatObject->GetProperty('userid'));
            $_userFullName = (int)($_SWIFT_ChatObject->GetProperty('userfullname'));
            $_userEmail = (int)($_SWIFT_ChatObject->GetProperty('useremail'));
            $_departmentID = (int)($_SWIFT_ChatObject->GetProperty('departmentid'));
        }

        // Do we have a GUID for this call?
        if (isset($_POST['callguid']) && !empty($_POST['callguid'])) {
            $_SWIFT_CallObject = SWIFT_Call::RetrieveOnGUID($_POST['callguid']);
        }

        // No luck? do we have chat object?
        if (!$_SWIFT_CallObject instanceof SWIFT_Call && $_SWIFT_ChatObject instanceof SWIFT_Chat && $_SWIFT_ChatObject->GetIsClassLoaded()) {
            $_SWIFT_CallObject = SWIFT_Call::RetrieveOnChat($_SWIFT_ChatObject);
        }

        // Is this call creation event? then we can create it!
        if ((!$_SWIFT_CallObject instanceof SWIFT_Call || !$_SWIFT_CallObject->GetIsClassLoaded()) &&
            ($_POST['state'] == self::STATE_CALLING || $_POST['state'] == self::STATE_INCOMING || $_POST['state'] == self::STATE_CONNECTED) &&
            (isset($_POST['remotenumber']) && !empty($_POST['remotenumber']))) {
            $_callStatus = SWIFT_Call::STATUS_PENDING;
            $_callType = SWIFT_Call::TYPE_OUTBOUND;

            if ($_POST['state'] == self::STATE_CONNECTED) {
                $_callStatus = SWIFT_Call::STATUS_ACCEPTED;
            } else if ($_POST['state'] == self::STATE_INCOMING) {
                $_callType = SWIFT_Call::TYPE_INBOUND;
            }

            $_callID = SWIFT_Call::Create($_POST['remotenumber'], $_POST['callguid'], $_userID, $_userFullName, $_userEmail, $_SWIFT->Staff->GetStaffID(),
                $_SWIFT->Staff->GetProperty('fullname'), $_chatObjectID, $_departmentID, false, $_callStatus, $_callType);
            $_SWIFT_CallObject = new SWIFT_Call(new SWIFT_DataID($_callID));
        }

        // We still dont have call object? bail
        if (!$_SWIFT_CallObject instanceof SWIFT_Call || !$_SWIFT_CallObject->GetIsClassLoaded()) {
            $this->XML->AddParentTag('kayako_livechat');
            $this->XML->AddTag('error', 'No Call Object Found');
            $this->XML->EndParentTag('kayako_livechat');
            $this->XML->EchoXMLWinapp();

            return false;
        }

        // By now we have a call object, process the event..
        if ($_POST['state'] == self::STATE_CONNECTED) {
            $_SWIFT_CallObject->UpdateStatus(SWIFT_Call::STATUS_ACCEPTED);

            if ($_SWIFT_ChatObject instanceof SWIFT_Chat && $_SWIFT_CallObject->GetIsClassLoaded()) {
                $_SWIFT_ChatObject->UpdateCallStatus(SWIFT_Call::STATUS_ACCEPTED);
            }
        } else if ($_POST['state'] == self::STATE_DISCONNECTED && $_SWIFT_CallObject->GetIsClassLoaded()) {

            $_SWIFT_CallObject->UpdateStatus(SWIFT_Call::STATUS_ENDED);

            if ($_SWIFT_ChatObject instanceof SWIFT_Chat) {
                $_SWIFT_ChatObject->UpdateCallStatus(SWIFT_Call::STATUS_ENDED);
            }
        } else if ($_POST['state'] == self::STATE_REJECTED && $_SWIFT_CallObject->GetIsClassLoaded()) {
            $_SWIFT_CallObject->UpdateStatus(SWIFT_Call::STATUS_REJECTED);

            if ($_SWIFT_ChatObject instanceof SWIFT_Chat) {
                $_SWIFT_ChatObject->UpdateCallStatus(SWIFT_Call::STATUS_REJECTED);
            }
        }

        // Check for GUID, if it doesnt exist.. set it
        if ($_SWIFT_CallObject->GetProperty('callguid') == '' && $_POST['callguid'] != '') {
            $_SWIFT_CallObject->UpdateGUID($_POST['callguid']);
        }

        $_SWIFT_CallObject->UpdateActivity();

        $this->XML->AddParentTag('kayako_livechat');
        $this->XML->AddTag('status', '1');
        $this->XML->EndParentTag('kayako_livechat');
        $this->XML->EchoXMLWinapp();

        return true;
    }

    /**
     * Call Recording
     *
     * @author Parminder Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upload()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_CallObject = false;

        // Do we have a GUID for this call?
        if (isset($_POST['callguid']) && !empty($_POST['callguid'])) {
            $_SWIFT_CallObject = SWIFT_Call::RetrieveOnGUID($_POST['callguid']);
        }

        if (!$_SWIFT_CallObject instanceof SWIFT_Call || !$_SWIFT_CallObject->GetIsClassLoaded()) {
            $this->XML->AddParentTag('kayako_livechat');
            $this->XML->AddTag('error', 'No Call Object Found');
            $this->XML->EndParentTag('kayako_livechat');
            $this->XML->EchoXMLWinapp();

            return false;
        }

        if (isset($_FILES['recording']) && isset($_FILES['recording']['tmp_name']) && is_uploaded_file($_FILES['recording']['tmp_name'])) {
            // Store the file without ANY EXTENSION (for security reasons)
            $_fileID = SWIFT_FileManager::Create($_FILES['recording']['tmp_name'], $_FILES['recording']['name'], false, true, SWIFT_FileManager::CALL_RECORDING);

            if (!empty($_fileID)) {
                $_SWIFT_CallObject->UpdatePool('fileid', $_fileID);
                $_SWIFT_CallObject->UpdateActivity();

                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-2365 Failed to upload call recording! Reason: compressed;84068ca593e56402747e4b24f66bf17e;569198d22f23cba4af76be4ace68ffb5;112;123;
                 *
                 */
                echo '1';

                return true;
            }
        }

        return true;
    }

    /**
     * Upload Recoring in Chunks
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UploadChunk()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_CallObject = false;

        // Do we have a GUID for this call?
        if (isset($_POST['callguid']) && !empty($_POST['callguid'])) {
            $_SWIFT_CallObject = SWIFT_Call::RetrieveOnGUID($_POST['callguid']);
        }

        if (!$_SWIFT_CallObject instanceof SWIFT_Call || !$_SWIFT_CallObject->GetIsClassLoaded()) {
            $this->XML->AddParentTag('kayako_livechat');
            $this->XML->AddTag('error', 'No Call Object Found');
            $this->XML->EndParentTag('kayako_livechat');
            $this->XML->EchoXMLWinapp();

            return false;
        }

        if (isset($_FILES['chunk']) && isset($_FILES['chunk']['tmp_name']) && is_uploaded_file($_FILES['chunk']['tmp_name'])) {

            $_fileExtention = strtolower(substr($_FILES['chunk']['name'], strrpos($_FILES['chunk']['name'], '.') + 1));

            // We got single orginal file to upload.
            if ($_fileExtention === 'mp3' && $_POST['last'] == '1') {
                // Store the file without ANY EXTENSION (for security reasons)
                $_fileID = SWIFT_FileManager::Create($_FILES['chunk']['tmp_name'], $_FILES['chunk']['name'], false, true, SWIFT_FileManager::CALL_RECORDING);

                if (!empty($_fileID)) {
                    $_SWIFT_CallObject->UpdatePool('fileid', $_fileID);
                    $_SWIFT_CallObject->UpdateActivity();

                    echo '1';

                    return true;
                }
            } else if ($_fileExtention == 'chunk') {
                /**
                 * @todo File upload logic need to move to FileManager
                 */
                $_newFileName = $_FILES['chunk']['name'];

                $_directoryPath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY;
                $_newFilePath = '';
                $_subDirectory = 'temp';

                $_subDirectoryPath = $_directoryPath . '/' . $_subDirectory;
                if (!is_dir($_subDirectoryPath)) {
                    mkdir($_subDirectoryPath, 0777);
                }
                $_newFilePath = $_subDirectoryPath . '/' . $_newFileName;


                // Attempt to move the file over to files directory
                rename($_FILES['chunk']['tmp_name'], $_newFilePath);
                @chmod($_newFilePath, 0666);

                if (!file_exists($_newFilePath)) {
                    throw new SWIFT_FileManager_Exception(SWIFT_INVALIDDATA);

                    return false;
                }

                if ($_POST['last'] == '1') {

                    // Check Chunk index
                    preg_match("/\[(.*?)\]/", $_newFileName, $_maxIndex);

                    // Keep First chunk as a temp file.
                    $_fileToUpload = preg_replace("/\[(.*?)\]/", '1', $_newFileName);
                    $_fileToUploadFirst = $_subDirectoryPath . '/' . $_fileToUpload;

                    // Start from chunk 2 for reading and appending to chunk 1.
                    for ($_index = 2; $_index <= $_maxIndex[1]; $_index++) {
                        $contents = '';
                        $_fileToUploadTemp = preg_replace("/\[(.*?)\]/", '[' . $_index . ']', $_newFileName);
                        $_fileToRead = $_subDirectoryPath . '/' . $_fileToUploadTemp;

                        // Open File to read
                        $_filePointer = fopen($_fileToRead, "rb");
                        $_contents = fread($_filePointer, filesize($_fileToRead));
                        fclose($_filePointer);

                        $_filePointer = fopen($_fileToUploadFirst, 'a');
                        fwrite($_filePointer, $_contents);
                        fclose($_filePointer);

                        // Unlink file after reading and writing operations.
                        unlink($_fileToRead);
                    }

                    $_fileID = SWIFT_FileManager::Create($_fileToUploadFirst, $_fileToUploadFirst, false, true, SWIFT_FileManager::CALL_RECORDING);

                    if (!empty($_fileID)) {
                        $_SWIFT_CallObject->UpdatePool('fileid', $_fileID);
                        $_SWIFT_CallObject->UpdateActivity();

                        echo '1';

                        return true;
                    }
                }
            }
        }
        return false;
    }
}
