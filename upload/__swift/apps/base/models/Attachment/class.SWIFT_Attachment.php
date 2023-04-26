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

namespace Base\Models\Attachment;

use Base\Library\Attachment\SWIFT_Attachment_Exception;
use SWIFT;
use SWIFT_App;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_FileManager;
use SWIFT_Loader;
use SWIFT_MIME_Exception;
use SWIFT_Model;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * The Attachment Handling Class
 *
 * @property \SWIFT_MIMEList $MIMEList
 * @author Varun Shoor
 */
class SWIFT_Attachment extends SWIFT_Model
{
    const TABLE_NAME        =    'attachments';
    const PRIMARY_KEY        =    'attachmentid';

    const TABLE_STRUCTURE    =    "attachmentid I PRIMARY AUTO NOTNULL,
                                linktype I2 DEFAULT '0' NOTNULL,
                                linktypeid I DEFAULT '0' NOTNULL,
                                downloaditemid I DEFAULT '0' NOTNULL,
                                ticketid I DEFAULT '0' NOTNULL,
                                filename C(255) DEFAULT '' NOTNULL,
                                filesize I DEFAULT '0' NOTNULL,
                                filetype C(150) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                attachmenttype I2 DEFAULT '0' NOTNULL,
                                storefilename C(255) DEFAULT '' NOTNULL,
                                contentid C(255) DEFAULT '' NOTNULL,
                                sha1 C(40) DEFAULT '' NOTNULL";

    const INDEX_1            =    'linktype, linktypeid';
    const INDEX_2            =    'attachmenttype';
    const INDEX_3            =    'downloaditemid';
    const INDEX_4            =    'ticketid, linktype, linktypeid';
    const INDEX_5            =    'linktype, ticketid, linktypeid';
    const INDEX_6            =    'linktype, ticketid, attachmentid';
    const INDEX_7            =    'sha1';

    protected $_dataStore = array();

    // Core Constants
    const TYPE_DATABASE = 1;
    const TYPE_FILE = 2;
    const TYPE_DOWNLOAD = 3;

    const LINKTYPE_TICKETPOST = 1;
    const LINKTYPE_TICKETNOTE = 2;
    const LINKTYPE_USERNOTE = 3;
    const LINKTYPE_CHATNOTE = 4;
    const LINKTYPE_KBARTICLE = 5;
    const LINKTYPE_TROUBLESHOOTERSTEP = 6;

    const DEFAULT_PREFIX = 'attach_';
    const DEFAULT_FILEPERMISSION = 0666;
    const DEFAULT_CHUNKSIZE = 256000;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param mixed $_attachmentID The Attachment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Record could not be loaded
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_attachmentID)
    {
        parent::__construct();

        if ($_attachmentID instanceof SWIFT_DataID) {
            /** @var SWIFT_DataID $_attachmentID */
            $_attachmentID = $_attachmentID->GetDataID();
        }

        if (!$this->LoadData($_attachmentID)) {
            throw new SWIFT_Attachment_Exception('Failed to load Attachment ID: ' . $_attachmentID);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'attachments', $this->GetUpdatePool(), 'UPDATE', "attachmentid = '" . (int) ($this->GetAttachmentID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Attachment ID
     *
     * @author Varun Shoor
     * @return mixed "attachmentid" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function GetAttachmentID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['attachmentid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_attachmentID The Attachment ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_attachmentID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE attachmentid = '" . $_attachmentID . "'");
        if (isset($_dataStore['attachmentid']) && !empty($_dataStore['attachmentid']))
        {
            $this->_dataStore = $_dataStore;

            return true;
        }

        return false;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if it is a valid Attachment type
     *
     * @author Varun Shoor
     * @param mixed $_attachmentType The Attachment Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_attachmentType)
    {
        if ($_attachmentType == self::TYPE_FILE || $_attachmentType == self::TYPE_DATABASE || $_attachmentType == self::TYPE_DOWNLOAD)
        {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid link type
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidLinkType($_linkType)
    {
        if ($_linkType == self::LINKTYPE_TICKETPOST || $_linkType == self::LINKTYPE_TICKETNOTE || $_linkType == self::LINKTYPE_USERNOTE || $_linkType == self::LINKTYPE_CHATNOTE ||
                $_linkType == self::LINKTYPE_KBARTICLE || $_linkType == self::LINKTYPE_TROUBLESHOOTERSTEP)
        {
            return true;
        }

        return false;
    }

    /**
     * Generate a Random File Name
     *
     * @author Varun Shoor
     * @return string "true" on Success, "false" otherwise
     */
    public static function GenerateRandomFileName()
    {
        return self::DEFAULT_PREFIX . BuildHash();
    }

    /**
     * Return the default attachment type
     *
     * @author Varun Shoor
     * @return mixed "Attachment Type" (CONSTANT) on Success, "false" otherwise
     */
    public static function GetDefaultAttachmentType()
    {
        $_SWIFT = SWIFT::GetInstance();

        // Override the attachment type
        if (defined('ENFORCEATTACHMENTS_INFILES') && ENFORCEATTACHMENTS_INFILES == true) {
            return self::TYPE_FILE;
        }

        switch ($_SWIFT->Settings->Get('cpu_attachtype'))
        {
            case self::TYPE_DATABASE:
                return self::TYPE_DATABASE;
                break;

            case self::TYPE_FILE:
                return self::TYPE_FILE;
                break;

            default:
                break;
        }

        return false;
    }

    /**
     * Create a new Attachment
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param int $_linkTypeID The Link Type ID
     * @param SWIFT_AttachmentStore $_AttachmentStoreObject (OPTIONAL) The Attachment Store Object. Required If Attachment Type is FILE || DATABASE
     * @param int $_ticketID The Ticket ID
     * @return SWIFT_Attachment "_SWIFT_AttachmentObject" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If Invalid Data is Provided or If the Object could not be created
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_linkType, $_linkTypeID, SWIFT_AttachmentStore $_AttachmentStoreObject = null, $_ticketID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_downloadItemID = 0;
        $_storeFileName = '';

        $_attachmentType = self::GetDefaultAttachmentType();
        $_linkTypeID = $_linkTypeID;

        if (!self::IsValidLinkType($_linkType) || empty($_linkTypeID) || !self::IsValidType($_attachmentType) || !$_AttachmentStoreObject instanceof SWIFT_AttachmentStore || !$_AttachmentStoreObject->GetIsClassLoaded())
        {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        $_storeFileName = self::GenerateRandomFileName();
        $_dateline = DATENOW;

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . SWIFT_Attachment::TABLE_NAME, array(
                                                                                         'ticketid'       => $_ticketID,
                                                                                         'linktype'       => (int) ($_linkType),
                                                                                         'linktypeid'     => $_linkTypeID,
                                                                                         'downloaditemid' => $_downloadItemID,
                                                                                         'filename'       => $_AttachmentStoreObject->GetFileName(),
                                                                                         'filesize'       => $_AttachmentStoreObject->GetFileSize(),
                                                                                         'filetype'       => $_AttachmentStoreObject->GetFileType(),
                                                                                         'dateline'       => $_dateline,
                                                                                         'attachmenttype' => (int) ($_attachmentType),
                                                                                         'storefilename'  => $_storeFileName,
                                                                                         'contentid'      => $_AttachmentStoreObject->GetContentID()
                                                                                    ), 'INSERT');
        $_attachmentID = $_SWIFT->Database->Insert_ID();

        if (!$_attachmentID)
        {
            throw new SWIFT_Attachment_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_AttachmentObject = new SWIFT_Attachment($_attachmentID);
        if (!$_SWIFT_AttachmentObject instanceof SWIFT_Attachment || !$_SWIFT_AttachmentObject->GetIsClassLoaded())
        {
            throw new SWIFT_Attachment_Exception(SWIFT_CREATEFAILED);
        }

        if ($_attachmentType == self::TYPE_FILE || $_attachmentType == self::TYPE_DATABASE)
        {
            $_SWIFT_AttachmentObject->ProcessAttachmentStore($_AttachmentStoreObject);
        }

        return $_SWIFT_AttachmentObject;
    }

    /**
     * Clone a Attachment
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object
     * @param SWIFT_TicketPost $_SWIFT_TicketPostObject The SWIFT_TicketPost Object
     * @param SWIFT_Attachment $_SWIFT_AttachmentObject
     * @return mixed "_SWIFT_AttachmentObject" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function CloneOnTicket(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_TicketPost $_SWIFT_TicketPostObject, SWIFT_Attachment $_SWIFT_AttachmentObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() ||
                !$_SWIFT_TicketPostObject instanceof SWIFT_TicketPost || !$_SWIFT_TicketPostObject->GetIsClassLoaded() || !$_SWIFT_AttachmentObject instanceof SWIFT_Attachment ||
                !$_SWIFT_AttachmentObject->GetIsClassLoaded())
        {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        $_fileContents = $_SWIFT_AttachmentObject->Get();

        $_SWIFT_AttachmentStoreObject = new SWIFT_AttachmentStoreString($_SWIFT_AttachmentObject->GetProperty('filename'), $_SWIFT_AttachmentObject->GetProperty('filetype'), $_fileContents, $_SWIFT_AttachmentObject->GetProperty('contentid'));

        $_SWIFT_AttachmentObject = self::Create(self::LINKTYPE_TICKETPOST, $_SWIFT_TicketPostObject->GetTicketPostID(), $_SWIFT_AttachmentStoreObject, $_SWIFT_TicketObject->GetTicketID());

        $_SWIFT_TicketObject->AddToAttachments($_SWIFT_AttachmentObject->GetProperty('filename'), $_SWIFT_AttachmentObject->GetProperty('filetype'), $_fileContents);

        return $_SWIFT_AttachmentObject;
    }

    /**
     * Create a new Attachment
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object
     * @param SWIFT_TicketPost $_SWIFT_TicketPostObject The SWIFT_TicketPost Object
     * @param SWIFT_AttachmentStore $_AttachmentStoreObject (OPTIONAL) The Attachment Store Object. Required If Attachment Type is FILE || DATABASE
     * @return SWIFT_Attachment "_SWIFT_AttachmentObject" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If Invalid Data is Provided or If the Object could not be created
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function CreateOnTicket(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_TicketPost $_SWIFT_TicketPostObject, SWIFT_AttachmentStore $_AttachmentStoreObject = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() ||
                !$_SWIFT_TicketPostObject instanceof SWIFT_TicketPost || !$_SWIFT_TicketPostObject->GetIsClassLoaded())
        {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AttachmentObject = self::Create(self::LINKTYPE_TICKETPOST, $_SWIFT_TicketPostObject->GetTicketPostID(), $_AttachmentStoreObject, $_SWIFT_TicketObject->GetTicketID());

        return $_SWIFT_AttachmentObject;
    }

    /**
     * Process the Attachment Store & Convert it into Chunks
     *
     * @author Varun Shoor
     * @param SWIFT_AttachmentStore $_AttachmentStoreObject The Attachment Store Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    protected function ProcessAttachmentStore(SWIFT_AttachmentStore $_AttachmentStoreObject)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_AttachmentStoreObject instanceof SWIFT_AttachmentStore || !$_AttachmentStoreObject->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        $_className = get_short_class($_AttachmentStoreObject);

        $_finalFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $this->GetProperty('storefilename');

        switch ($_className)
        {
            case 'SWIFT_AttachmentStoreFile':
            {
                if ($this->GetProperty('attachmenttype') == self::TYPE_FILE)
                {
                    copy($_AttachmentStoreObject->GetFilePath(), $_finalFilePath);

                    @chmod($_finalFilePath, self::DEFAULT_FILEPERMISSION);
                } else if ($this->GetProperty('attachmenttype') == self::TYPE_DATABASE) {
                    do {
                        $_chunkData = $_AttachmentStoreObject->GetChunk();
                        if (!$_chunkData)
                        {
                            break;
                        }

                        // Insert the chunk
                        $this->InsertAttachmentChunk($_chunkData);
                        unset($_chunkData);

                    } while(true);
                }

                break;
            }

            case 'SWIFT_AttachmentStoreString':
            {
                if ($this->GetProperty('attachmenttype') == self::TYPE_FILE)
                {
                    $_filePointer = @fopen($_finalFilePath, 'w+');

                    if (!$_filePointer)
                    {
                        throw new SWIFT_Attachment_Exception(SWIFT_CREATEFAILED);
                    }

                    do
                    {
                        $_chunkData = $_AttachmentStoreObject->GetChunk();
                        if (!$_chunkData)
                        {
                            break;
                        }

                        fwrite($_filePointer, $_chunkData);
                    } while (true);

                    fclose($_filePointer);
                    @chmod($_finalFilePath, self::DEFAULT_FILEPERMISSION);

                } else if ($this->GetProperty('attachmenttype') == self::TYPE_DATABASE) {
                    do {
                        $_chunkData = $_AttachmentStoreObject->GetChunk();
                        if (!$_chunkData)
                        {
                            break;
                        }

                        // Insert the chunk
                        $this->InsertAttachmentChunk($_chunkData);
                        unset($_chunkData);

                    } while(true);
                }

                break;
            }

            default:
            {
                throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
            }
        }

        if (file_exists($_finalFilePath)) {
            $this->UpdatePool('sha1', sha1_file($_finalFilePath));
            $this->ProcessUpdatePool();
        }

        return true;
    }

    /**
     * Insert a new Chunk for this Attachment
     *
     * @author Varun Shoor
     * @param string $_chunkData The Chunk Data
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    protected function InsertAttachmentChunk($_chunkData)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_notBase64 = false;
        $_finalChunkContents = '';
        if (strtolower(DB_TYPE) == 'mysql' || strtolower(DB_TYPE) == 'mysqli')
        {
            $_finalChunkContents = $_chunkData;
            $_notBase64 = true;
        } else {
            $_finalChunkContents = base64_encode($_chunkData);
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'attachmentchunks', array('attachmentid' => (int) ($this->GetAttachmentID()), 'contents' => $_finalChunkContents,
            'notbase64' => (int) ($_notBase64)), 'INSERT');

        return true;
    }

    /**
     * Clear the Attachment Chunks
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    protected function ClearChunks()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "attachmentchunks WHERE attachmentid = '" . (int) ($this->GetAttachmentID()) . "'");

        return true;
    }

    /**
     * Move the Attachment from Database to File
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function MoveToFile()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        } else if ($this->GetProperty('attachmenttype') == self::TYPE_FILE || $this->GetProperty('attachmenttype') == self::TYPE_DOWNLOAD) {
            return false;
        }

        $_storeFileName = self::GenerateRandomFileName();

        $_finalFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $_storeFileName;

        $_filePointer = fopen($_finalFilePath, 'wb+');
        if (!$_filePointer)
        {
            return false;
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachmentchunks WHERE attachmentid = '" . (int) ($this->GetAttachmentID()) . "' ORDER BY chunkid ASC");
        while ($this->Database->NextRecord())
        {
            @fflush($_filePointer);

            if ($this->Database->Record['notbase64'] == '1')
            {
                fwrite($_filePointer, $this->Database->Record['contents']);
            } else {
                fwrite($_filePointer, base64_decode($this->Database->Record['contents']));
            }
        }
        @fflush($_filePointer);
        fclose($_filePointer);

        // We now change the type of this attachment to file
        $this->UpdatePool('attachmenttype', self::TYPE_FILE);
        $this->UpdatePool('storefilename', $_storeFileName);

        $this->ProcessUpdatePool();

        // We now remove all the chunks for this attachment
        $this->ClearChunks();

        return true;
    }

    /**
     * Move the Attachment from File to Database
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function MoveToDatabase()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        } else if ($this->GetProperty('attachmenttype') == self::TYPE_DATABASE || $this->GetProperty('attachmenttype') == self::TYPE_DOWNLOAD) {
            return false;
        }

        $_finalFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $this->GetProperty('storefilename');
        if (!file_exists($_finalFilePath))
        {
            return false;
        }

        $this->ClearChunks();

        // Add the chunks
        $_filePointer = @fopen($_finalFilePath, 'rb');
        if ($_filePointer)
        {
            do {
                $_chunkData = fread($_filePointer, self::GetChunkSize());
                if (!$_chunkData || strlen($_chunkData) == 0)
                {
                    break;
                }

                // Insert the chunk
                $this->InsertAttachmentChunk($_chunkData);

                unset($_chunkData);
            } while(true);

            fclose($_filePointer);

            // Delete the file
            @unlink($_finalFilePath);
        }

        // Once the file is deleted we update the attachmenttype field
        $this->UpdatePool('attachmenttype', self::TYPE_DATABASE);
        $this->UpdatePool('storefilename', '');

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Retrieve the Chunk Size
     *
     * @author Varun Shoor
     * @return mixed "_chunkSize" (INT) on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public static function GetChunkSize()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_settingChunkSize = (int) ($_SWIFT->Settings->Get('cpu_attachchunksize'));

        if (empty($_settingChunkSize))
        {
            $_settingChunkSize = self::DEFAULT_CHUNKSIZE;
        }

        return $_settingChunkSize;
    }

    /**
     * Dispatch the Attachment to the Browser
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function Dispatch()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (SWIFT_INTERFACE == 'tests') {
            return false;
        }

        // Get the File Details
        $_fileExtension = substr($this->GetProperty('filename'), strrpos($this->GetProperty('filename'), '.') + 1);
        $_finalFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $this->GetProperty('storefilename');

        $this->Load->Library('MIME:MIMEList');

        if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) {
            // IE Bug in download name workaround
            @ini_set('zlib.output_compression','Off');
        }

        $_mimeContainer = false;
        try
        {
            $_mimeContainer = $this->MIMEList->Get($_fileExtension);
        } catch (SWIFT_MIME_Exception $_SWIFT_MIME_ExceptionObject) {
            // Reserved
        }

        // Get the file extension
        if ($_mimeContainer && isset($_mimeContainer[0]))
        {
            @header('Content-Type: ' . $_mimeContainer[0] . SWIFT_CRLF);
        } else {
            @header('Content-Type: application/force-download');
        }

        @header("Content-Disposition: attachment; filename=\"" . $this->GetProperty('filename') . "\"");

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1669 Support Center Downloads are working for large files say more than 30 MB of size.
         *
         * Comments: None
         */
        // header("Content-Length: " . $this->GetProperty('filesize'));

        @header("Content-Transfer-Encoding: binary");

        // Is the attachment stored as a file?
        if ($this->GetProperty('attachmenttype') == self::TYPE_FILE)
        {
            /**
             * ---------------------------------------------
             * Remote Attachment Logic
             * ---------------------------------------------
             */
            if (SWIFT_FileManager::HasRemoteFile($this->GetProperty('storefilename'))) {
                $_remoteFileInfo = SWIFT_FileManager::GetRemoteFileInfo($this->GetProperty('storefilename'));
                if (!$_remoteFileInfo) {
                    echo 'Unable to locate remote file "' . $this->GetProperty('storefilename') . '". Please contact Kayako support for assistance.';
                    return false;
                }

                // Yes, just read out the file
                $_filePointer = @fopen($_remoteFileInfo['url'], 'rb');
                if (!$_filePointer)
                {
                    echo 'Unable to open remote file "' . $this->GetProperty('storefilename') . '", info: ' . print_r($_remoteFileInfo, true) . '. Please contact Kayako support for assistance.';

                    return false;
                }

                while(!feof($_filePointer)) {
                    echo fread($_filePointer, 8192);
                }

                fclose($_filePointer);

            /**
             * ---------------------------------------------
             * Local Attachment Logic
             * ---------------------------------------------
             */
            } else {
                if (!file_exists($_finalFilePath))
                {
                    return false;
                }

                // Yes, just read out the file
                $_filePointer = @fopen($_finalFilePath, 'rb');
                if (!$_filePointer)
                {
                    return false;
                }

                while(!feof($_filePointer)) {
                    echo fread($_filePointer, 8192);
                }

                fclose($_filePointer);
            }
        } else if ($this->GetProperty('attachmenttype') == self::TYPE_DATABASE) {
            // Attachment type is set to database, read the chunks into a temporary file
            $_temporaryFileName = SWIFT_FileManager::DEFAULT_TEMP_PREFIX . BuildHash();
            $_temporaryFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $_temporaryFileName;
            $_fileID = SWIFT_FileManager::CreateExtended($_temporaryFileName, DATENOW+1800);

            $_filePointer = @fopen($_temporaryFilePath, 'wb+');
            if (!$_filePointer)
            {
                return false;
            }

            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachmentchunks WHERE attachmentid = '" . (int) ($this->GetAttachmentID()) . "' ORDER BY chunkid ASC");
            while ($this->Database->NextRecord())
            {
                @fflush($_filePointer);

                if ($this->Database->Record['notbase64'] == '1')
                {
                    fwrite($_filePointer, $this->Database->Record['contents']);
                } else {
                    fwrite($_filePointer, base64_decode($this->Database->Record['contents']));
                }
            }

            @fflush($_filePointer);
            fclose($_filePointer);

            // Now that we have a temporary file, we need to pump out the combined chunks to the user
            $_filePointer = @fopen($_temporaryFilePath, 'rb');
            if (!$_filePointer)
            {
                return false;
            }

            while(!feof($_filePointer)) {
                echo fread($_filePointer, 8192);
            }

            fclose($_filePointer);

            // Nuke the temporary file as we dont need it anymore
            @unlink($_temporaryFilePath);
        }

        return true;
    }

    /**
     * Retrieve the Attachment Data
     *
     * @author Varun Shoor
     * @return bool|string
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function Get($_ = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Get the file extension
        $_fileExtension = substr($this->GetProperty('filename'), strrpos($this->GetProperty('filename'), '.') + 1);
        $_finalFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $this->GetProperty('storefilename');

        $_returnData = '';

        // Is the attachment stored as a file?
        if ($this->GetProperty('attachmenttype') == self::TYPE_FILE)
        {
            /**
             * ---------------------------------------------
             * Remote Attachment Logic
             * ---------------------------------------------
             */
            if (SWIFT_FileManager::HasRemoteFile($this->GetProperty('storefilename'))) {
                $_remoteFileInfo = SWIFT_FileManager::GetRemoteFileInfo($this->GetProperty('storefilename'));
                if (!$_remoteFileInfo) {
                    echo 'Unable to locate remote file "' . $this->GetProperty('storefilename') . '". Please contact Kayako support for assistance.';

                    return false;
                }

                // Yes, just read out the file
                $_filePointer = @fopen($_remoteFileInfo['url'], 'rb');
                if (!$_filePointer)
                {
                    echo 'Unable to open remote file "' . $this->GetProperty('storefilename') . '", info: ' . print_r($_remoteFileInfo, true) . '. Please contact Kayako support for assistance.';

                    return false;
                }

                while(!feof($_filePointer)) {
                    $_returnData .= fread($_filePointer, 8192);
                }

                fclose($_filePointer);

            /**
             * ---------------------------------------------
             * Local Attachment Logic
             * ---------------------------------------------
             */
            } else {
                if (!file_exists($_finalFilePath))
                {
                    return false;
                }

                // Yes, just read out the file
                $_filePointer = @fopen($_finalFilePath, 'rb');
                if (!$_filePointer)
                {
                    return false;
                }

                while(!feof($_filePointer)) {
                    $_returnData .= fread($_filePointer, 8192);
                }

                fclose($_filePointer);
            }

            return $_returnData;


        } else if ($this->GetProperty('attachmenttype') == self::TYPE_DATABASE) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachmentchunks WHERE attachmentid = '" . (int) ($this->GetAttachmentID()) . "' ORDER BY chunkid ASC");
            while ($this->Database->NextRecord())
            {
                if ($this->Database->Record['notbase64'] == '1')
                {
                    $_returnData .= $this->Database->Record['contents'];
                } else {
                    $_returnData .= base64_decode($this->Database->Record['contents']);
                }
            }

            return $_returnData;
        }

        return false;
    }

    /**
     * Delete the Attachment record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetAttachmentID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Attachments
     *
     * @author Varun Shoor
     * @param array $_attachmentIDList The Attachment ID List
     * @return mixed "_deletedAttachmentFileList" (ARRAY) on Success, "false" otherwise
     */
    public static function DeleteList($_attachmentIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_attachmentIDList))
        {
            return false;
        }

        $_finalAttachmentIDList = $_finalAttachmentContainer = $_deletedAttachmentFileList = $_finalTicketIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE attachmentid IN (" . BuildIN($_attachmentIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalAttachmentIDList[] = $_SWIFT->Database->Record['attachmentid'];
            $_finalAttachmentContainer[$_SWIFT->Database->Record['attachmentid']] = $_SWIFT->Database->Record;

            if ($_SWIFT->Database->Record['linktype'] == self::LINKTYPE_TICKETPOST)
            {
                $_finalTicketIDList[] = $_SWIFT->Database->Record['ticketid'];
            }

            $_deletedAttachmentFileList[] = $_SWIFT->Database->Record['filename'];
        }

        if (!count($_finalAttachmentIDList) || !count($_finalAttachmentContainer))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "attachments WHERE attachmentid IN (" . BuildIN($_finalAttachmentIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "attachmentchunks WHERE attachmentid IN (" . BuildIN($_finalAttachmentIDList) . ")");

        foreach ($_finalAttachmentContainer as $_val)
        {
            if (isset($_val['storefilename']) && !empty($_val['storefilename']))
            {
                $_finalFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $_val['storefilename'];
                $_jsonFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/_' . $_val['storefilename'];

                if (file_exists($_finalFilePath))
                {
                    @unlink($_finalFilePath);
                }

                if (file_exists($_jsonFilePath)) {
                    @unlink($_jsonFilePath);
                }
            }
        }

        if (count($_finalTicketIDList) && SWIFT_App::IsInstalled(APP_TICKETS))
        {
            SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
            SWIFT_Ticket::RecalculateHasAttachmentProperty($_finalTicketIDList);
        }

        return $_deletedAttachmentFileList;
    }

    /**
     * Delete on Ticket ID List
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTicket($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList))
        {
            return false;
        }

        $_attachmentIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE linktype = '" . self::LINKTYPE_TICKETPOST . "' AND ticketid IN (". BuildIN($_ticketIDList) .")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_attachmentIDList[] = $_SWIFT->Database->Record['attachmentid'];
        }

        if (!count($_attachmentIDList))
        {
            return false;
        }

        self::DeleteList($_attachmentIDList);

        return false;
    }

    /**
     * Replace the current ticket id all tickets with the new one
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Old Ticket ID List
     * @param SWIFT_Ticket $_SWIFT_ParentTicketObject The Parent Ticket Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ReplaceTicket($_ticketIDList, SWIFT_Ticket $_SWIFT_ParentTicketObject) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ParentTicketObject instanceof SWIFT_Ticket || !$_SWIFT_ParentTicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!_is_array($_ticketIDList)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'attachments', array('ticketid' => (int) ($_SWIFT_ParentTicketObject->GetTicketID())),
                'UPDATE', "linktype = '" . self::LINKTYPE_TICKETPOST . "' AND ticketid IN (" . BuildIN($_ticketIDList) . ")");

        return true;
    }

    /**
     * Delete on a Ticket Post
     *
     * @author Varun Shoor
     * @param array $_ticketPostIDList The Ticket Post ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTicketPost($_ticketPostIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketPostIDList))
        {
            return false;
        }

        $_attachmentIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE linktype = '" . self::LINKTYPE_TICKETPOST . "' AND linktypeid IN (". BuildIN($_ticketPostIDList) .")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_attachmentIDList[] = $_SWIFT->Database->Record['attachmentid'];
        }

        if (!count($_attachmentIDList))
        {
            return false;
        }

        self::DeleteList($_attachmentIDList);

        return false;
    }

    /**
     * Delete attachments based on Link Type
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param array $_linkTypeIDList The Link TYpe ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnLinkType($_linkType, $_linkTypeIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidLinkType($_linkType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!_is_array($_linkTypeIDList))
        {
            return false;
        }

        $_attachmentIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE linktype = '" . (int) ($_linkType) . "' AND linktypeid IN (". BuildIN($_linkTypeIDList) .")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_attachmentIDList[] = $_SWIFT->Database->Record['attachmentid'];
        }

        if (!count($_attachmentIDList))
        {
            return false;
        }

        self::DeleteList($_attachmentIDList);

        return false;
    }

    /**
     * Delete on a list of Download Item ID's
     *
     * @author Varun Shoor
     * @param array $_downloadItemIDList The Download Item ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnDownload($_downloadItemIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_downloadItemIDList))
        {
            return false;
        }

        $_attachmentIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE downloaditemid IN (". BuildIN($_downloadItemIDList) .")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_attachmentIDList[] = $_SWIFT->Database->Record['attachmentid'];
        }

        if (!count($_attachmentIDList))
        {
            return false;
        }

        self::DeleteList($_attachmentIDList);

        return false;
    }

    /**
     * Get the Attachment Count Based on Attachment Type
     *
     * @author Varun Shoor
     * @param mixed $_attachmentType The Attachment Type
     * @return int
     */
    public static function GetAttachmentCount($_attachmentType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_attachmentType))
        {
            return 0;
        }

        $_totalItemContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "attachments WHERE attachmenttype = '" . (int) ($_attachmentType) . "'");
        if (isset($_totalItemContainer['totalitems']) && !empty($_totalItemContainer['totalitems']))
        {
            return (int) ($_totalItemContainer['totalitems']);
        }

        return 0;
    }

    /**
     * Retrieve the Attachments
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param int $_linkTypeID The Link Type ID
     * @return array The Attachment Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve($_linkType, $_linkTypeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidLinkType($_linkType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_attachmentContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE linktype = '" . (int) ($_linkType) . "' AND linktypeid = '" . $_linkTypeID . "'");
        while ($_SWIFT->Database->NextRecord())
        {
            $_attachmentContainer[$_SWIFT->Database->Record['attachmentid']] = $_SWIFT->Database->Record;
        }

        return $_attachmentContainer;
    }

	/**
	 * Retrieve the Attachments by sha1
	 *
	 * @param string $_sha1
	 * @param int $_ticketId
	 * @return array The Attachment Container
	 * @throws SWIFT_Exception If error in database Query (catched internally)
	 * @author Werner Garcia
	 */
    public static function RetrieveBySha1($_sha1, $_ticketId = null): array
    {
        if (empty($_sha1))
        {
            return [];
        }

        $_SWIFT = SWIFT::GetInstance();
        $_attachmentContainer = [];
        $query = "SELECT * FROM " . TABLE_PREFIX . "attachments WHERE sha1 = ?";
        $params = [$_sha1];

        if (!is_null($_ticketId)) {
			$query .= ' AND ticketid = ?';
			$params[] = $_ticketId;
        }
	    $_SWIFT->Database->Query($query,1, false, $params);

        while ($_SWIFT->Database->NextRecord())
        {
            $_attachmentContainer[$_SWIFT->Database->Record['attachmentid']] = $_SWIFT->Database->Record;
        }

        return $_attachmentContainer;
    }

    /**
     * @author Simaranjit Singh
     * @param int $_dateline
     *
     * @return bool
     */
    public function SetDate($_dateline)
    {
        $this->UpdatePool('dateline', $_dateline);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Retrieve the Attachments as Base64 encoded
     *
     * @author Ravi Sharma <ravi.sharma@kayako.com>
     *
     * @return string|bool The Base 64 encoded string
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function GetBase64Encoded()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $this->GetProperty('storefilename');
        $_returnData    = '';

        // Is the attachment stored as a file?
        if ($this->GetProperty('attachmenttype') == self::TYPE_FILE) {
            if (SWIFT_FileManager::HasRemoteFile($this->GetProperty('storefilename'))) {

                $_remoteFileInfo = SWIFT_FileManager::GetRemoteFileInfo($this->GetProperty('storefilename'));

                if (!$_remoteFileInfo) {
                    echo 'Unable to locate remote file "' . $this->GetProperty('storefilename') . '". Please contact Kayako support for assistance.';

                    return false;
                }

                // Yes, just read out the file
                $_filePointer = @fopen($_remoteFileInfo['url'], 'rb');
                if (!$_filePointer) {
                    echo 'Unable to open remote file "' . $this->GetProperty('storefilename') . '", info: ' . print_r($_remoteFileInfo, true) . '. Please contact Kayako support for assistance.';

                    return false;
                }

                while (!feof($_filePointer)) {
                    $_returnData .= fread($_filePointer, 8192);
                }

                fclose($_filePointer);
            } else {
                if (!file_exists($_finalFilePath)) {
                    return false;
                }

                $_filePointer = @fopen($_finalFilePath, 'rb');
                if (!$_filePointer) {
                    return false;
                }

                while (!feof($_filePointer)) {
                    $_returnData .= fread($_filePointer, 8192);
                }

                fclose($_filePointer);
            }

            return base64_encode($_returnData);
        } else if ($this->GetProperty('attachmenttype') == self::TYPE_DATABASE) {

            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachmentchunks WHERE attachmentid = '" . (int) ($this->GetAttachmentID()) . "' ORDER BY chunkid ASC");

            while ($this->Database->NextRecord()) {
                if ($this->Database->Record['notbase64'] == '1') {
                    $_returnData .= $this->Database->Record['contents'];
                } else {
                    $_returnData .= base64_decode($this->Database->Record['contents']);
                }
            }

            return base64_encode($_returnData);
        }

        return false;
    }
}

