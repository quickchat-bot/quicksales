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

/**
 * The File Manager Handling Class
 *
 * @method int GetFileManagerID()
 * @author Varun Shoor
 */
class SWIFT_FileManager extends SWIFT_Model
{
    protected $_dataStore = array();

    // Core Constants
    const DEFAULT_PREFIX = 'file_';
    const DEFAULT_TEMP_PREFIX = 'temp_';
    const CALL_RECORDING = 'callrecording';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param mixed $_fileID The File ID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_fileID)
    {
        parent::__construct();

        if (!$this->LoadData($_fileID)) {
            throw new SWIFT_FileManager_Exception('Failed to load File ID: ' . ($_fileID));
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'files', $this->GetUpdatePool(), 'UPDATE', "fileid = '" . ($this->GetFileID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the File ID
     *
     * @author Varun Shoor
     * @return mixed "fileid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFileID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_FileManager_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore['fileid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_fileID The File ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_fileID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "files WHERE fileid = '" . ($_fileID) . "'");
        if (isset($_dataStore['fileid']) && !empty($_dataStore['fileid']))
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_FileManager_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_FileManager_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_FileManager_Exception(SWIFT_INVALIDDATA . ': ' . $_key);

            return false;
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new File Record
     *
     * @author Varun Shoor
     * @param string $_filePath The File Path
     * @param string $_originalFileName The Original File Name
     * @param int|bool $_expiry (OPTIONAL) The File Expiry
     * @param bool $_ignoreExtension (OPTIONAL) If specified, the file being created will not have any extension
     * @param string|bool $_subDirectory (OPTIONAL) The Sub Directory Name
     * @return mixed "_fileID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_filePath, $_originalFileName, $_expiry = false, $_ignoreExtension = false, $_subDirectory = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!file_exists($_filePath) || !is_readable($_filePath))
        {
            throw new SWIFT_FileManager_Exception(SWIFT_INVALIDDATA);
        }

        $_pathContainer = pathinfo($_filePath);
        if (!isset($_pathContainer['basename']) || empty($_pathContainer['basename']))
        {
            throw new SWIFT_FileManager_Exception(SWIFT_INVALIDDATA);
        }

        $_newFileName = self::GetNewFileName($_originalFileName, $_ignoreExtension);

        $_directoryPath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY;
        $_newFilePath = '';
        if (!empty($_subDirectory)) {
            $_subDirectoryPath = $_directoryPath .  '/' . $_subDirectory;
            if (!is_dir($_subDirectoryPath)) {
                mkdir($_subDirectoryPath, 0777);
            }
            $_newFilePath = $_subDirectoryPath . '/' . $_newFileName;
        } else {
            $_newFilePath = $_directoryPath . '/' . $_newFileName;
        }

        // Attempt to move the file over to files directory
        rename($_filePath, $_newFilePath);
        @chmod($_newFilePath, 0666);

        if (!file_exists($_newFilePath))
        {
            throw new SWIFT_FileManager_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'files', array('filename' => $_newFileName, 'originalfilename' => $_originalFileName, 'dateline' => DATENOW, 'expiry' => ($_expiry), 'filehash' => BuildHash(), 'subdirectory' => $_subDirectory), 'INSERT');
        $_fileID = $_SWIFT->Database->Insert_ID();

        if (!$_fileID)
        {
            throw new SWIFT_FileManager_Exception(SWIFT_CREATEFAILED);
        }

        return $_fileID;
    }

    /**
     * Create a new File Record
     *
     * @author Varun Shoor
     * @param string $_fileName The File Name
     * @param int $_expiry (OPTIONAL) The File Expiry
     * @return mixed "_fileID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function CreateExtended($_fileName, $_expiry = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'files', array('filename' => $_fileName, 'originalfilename' => $_fileName,
            'dateline' => DATENOW, 'expiry' => ($_expiry), 'filehash' => BuildHash()), 'INSERT');
        $_fileID = $_SWIFT->Database->Insert_ID();

        if (!$_fileID)
        {
            throw new SWIFT_FileManager_Exception(SWIFT_CREATEFAILED);
        }

        return $_fileID;
    }

    /**
     * Retrieve a new calculated filename based on original filename
     *
     * @author Varun Shoor
     * @param string $_originalFileName The Original File Name
     * @param bool $_ignoreExtension (OPTIONAL) If specified the file name will not have any extension
     * @return mixed "_newFileName" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected static function GetNewFileName($_originalFileName, $_ignoreExtension = false)
    {
        if ($_ignoreExtension)
        {
            $_newFileName = self::DEFAULT_PREFIX . BuildHash();
        } else {
            $_pathContainer = pathinfo($_originalFileName);

            if (!isset($_pathContainer['extension']))
            {
                throw new SWIFT_FileManager_Exception(SWIFT_INVALIDDATA);
            }

            $_newFileName = self::DEFAULT_PREFIX . substr(BuildHash(), 0, 15) . '.' . mb_strtolower($_pathContainer['extension']);
        }

        return $_newFileName;
    }

    /**
     * Delete the File record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_FileManager_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetFileManagerID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of File ID's
     *
     * @author Varun Shoor
     * @param array $_fileIDList The File ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_fileIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_fileIDList))
        {
            return false;
        }

        $_finalFileIDList = array();
        $_fileNameList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "files WHERE fileid IN (" . BuildIN($_fileIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalFileIDList[] = $_SWIFT->Database->Record['fileid'];
            if (!empty($_SWIFT->Database->Record['subdirectory'])) {
                $_fileNameList[] = $_SWIFT->Database->Record['subdirectory'] . '/' . $_SWIFT->Database->Record['filename'];
            } else {
                $_fileNameList[] = $_SWIFT->Database->Record['filename'];
            }
        }

        if (!count($_finalFileIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "files WHERE fileid IN (" . BuildIN($_finalFileIDList) . ")");

        foreach ($_fileNameList as $_key => $_val)
        {
            $_filePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $_val;
            $_jsonFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/_' . $_val;

            if (file_exists($_filePath))
            {
                @unlink($_filePath);
            }

            if (file_exists($_jsonFilePath)) {
                @unlink($_jsonFilePath);
            }
        }

        return true;
    }

    /**
     * Delete all the Expired Files
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteExpired()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fileIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "files WHERE (expiry != '0' AND expiry < '" . DATENOW . "')");
        while ($_SWIFT->Database->NextRecord())
        {
            $_fileIDList[] = $_SWIFT->Database->Record['fileid'];
        }

        if (!count($_fileIDList))
        {
            return false;
        }

        self::DeleteList($_fileIDList);

        return true;
    }

    /**
     * Delete on a list of File Name
     *
     * @author Varun Shoor
     * @param array $_fileNameList The List of File Names
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteOnFileNameList($_fileNameList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_fileNameList))
        {
            return false;
        }

        $_fileIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "files WHERE filename IN (" . BuildIN($_fileNameList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_fileIDList[] = $_SWIFT->Database->Record['fileid'];
        }

        if (!count($_fileIDList))
        {
            return false;
        }

        self::DeleteList($_fileIDList);

        return true;
    }

    /**
     * Get the URL pointer to this file
     *
     * @author Varun Shoor
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetURL()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetProperty('subdirectory') != '') {
            return SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $this->GetProperty('subdirectory') . '/' . $this->GetProperty('filename');
        }

        return SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $this->GetProperty('filename');
    }

    /**
     * Get the path to this file
     *
     * @author Varun Shoor
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPath()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetProperty('subdirectory') != '') {
            return './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $this->GetProperty('subdirectory') . '/' . $this->GetProperty('filename');
        }

        return './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $this->GetProperty('filename');
    }

    /**
     * Retrieve the File Size
     *
     * @author Saloni Dhall
     * @return int "_fileSize" (INT) on Success
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFileSize()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fileSize = 0;

        if ($this->HasRemote()) {
            $_remoteFileInfo = $this->GetRemoteInfo();

            if (isset($_remoteFileInfo['size']) && ($_remoteFileInfo['size']) > 0) {
                $_fileSize = ($_remoteFileInfo['size']);
            }
        } else {
            $_filePath = $this->GetPath();

            if (file_exists($_filePath)) {
                $_fileSize = (filesize($_filePath));
            }
        }

        return $_fileSize;
    }

    /**
     * Check and see whether this file has a remote instance
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function HasRemote()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_originalFilePath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_FILES_DIRECTORY . '/' . $this->GetProperty('filename');
        $_remoteFileInfoPath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_FILES_DIRECTORY . '/_' . $this->GetProperty('filename');
        if ($this->GetProperty('subdirectory') != '') {
            $_originalFilePath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_FILES_DIRECTORY . '/' . $this->GetProperty('subdirectory') . '/' . $this->GetProperty('filename');
            $_remoteFileInfoPath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_FILES_DIRECTORY . '/' . $this->GetProperty('subdirectory') . '/_' . $this->GetProperty('filename');
        }

        if (file_exists($_remoteFileInfoPath) && !file_exists($_originalFilePath)) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the Remote File Info
     *
     * @author Varun Shoor
     * @return mixed array(url, expiry, size, mtime) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetRemoteInfo()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_remoteFileInfoPath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_FILES_DIRECTORY . '/_' . $this->GetProperty('filename');
        if ($this->GetProperty('subdirectory') != '') {
            $_remoteFileInfoPath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_FILES_DIRECTORY . '/' . $this->GetProperty('subdirectory') . '/_' . $this->GetProperty('filename');
        }

        if (!file_exists($_remoteFileInfoPath)) {
            return false;
        }

        $_remoteFileInfo = @json_decode(@file_get_contents($_remoteFileInfoPath), true);
        if (!_is_array($_remoteFileInfo)) {
            return false;
        }

        // Array Structure: url, expiry, size, mtime
        if (!isset($_remoteFileInfo['url']) || !isset($_remoteFileInfo['expiry']) || !isset($_remoteFileInfo['size']) || !isset($_remoteFileInfo['mtime']) || $_remoteFileInfo['expiry'] < DATENOW) {
            return false;
        }

        return $_remoteFileInfo;
    }

    /**
     * Dispatch the file to the browser
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Dispatch()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /*
         * BUG FIX - Amarjeet Kaur
         *
         * SWIFT-3494: Dispatch the file to the browser does not handle subdirectory content
         *
         * Comments: None
         */
        if ($this->GetProperty('subdirectory') != '') {
            $_temporaryFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . Clean($this->GetProperty('subdirectory')) . '/' . $this->GetProperty('filename');
        } else {
            $_temporaryFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $this->GetProperty('filename');
        }

        if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) {
            // IE Bug in download name workaround
            @ini_set('zlib.output_compression','Off');
        }

        header('Content-Type: application/force-download');
        header("Content-Disposition: attachment; filename=\"" . $this->GetProperty('originalfilename') . "\"");
        header("Content-Transfer-Encoding: binary");

        /** BUG FIX : Saloni Dhall <saloni.dhall@kayako.com>
         *
         * SWIFT-3164 : Custom field attachments are not visible if they have moved to cloud
         *
         * Comments : If the Remote file exists, then further passes the remote url to $_temporaryFilePath
         */
        if ($this->HasRemote()) {
            $_remoteFileInfo = $this->GetRemoteInfo();
            if (!$_remoteFileInfo) {
                echo 'Unable to locate remote file "' . $this->GetProperty('filename') . '". Please contact QuickSupport support for assistance.';
                return false;
            }

            $_temporaryFilePath = $_remoteFileInfo['url'];
        }

        $_filePointer = fopen($_temporaryFilePath, 'rb');
        if (!$_filePointer)
        {
            return false;
        }

        while(!feof($_filePointer)) {
            echo fread($_filePointer, 8192);
        }

        fclose($_filePointer);

        return true;
    }

    /**
     * Get BAse64 Data
     *
     * @author Varun Shoor
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetBase64()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_temporaryFilePath = './' . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $this->GetProperty('filename');

        if (!file_exists($_temporaryFilePath)) {
            return '';
        }

        return base64_encode(file_get_contents(($_temporaryFilePath)));
    }

    /**
     * Checks to see whether we have a remote file for the given filename. Remote files are used to upload hot copies of the original file to save local disk space.
     *
     * @author Varun Shoor
     * @param string $_fileName
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function HasRemoteFile($_fileName)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_originalFilePath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_FILES_DIRECTORY . '/' . $_fileName;
        $_remoteFileInfoPath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_FILES_DIRECTORY . '/_' . $_fileName;

        if (file_exists($_remoteFileInfoPath) && !file_exists($_originalFilePath)) {
            return true;
        }

        return false;
    }

    /**
     * Retrieves the Remote File Info
     *
     * @author Varun Shoor
     * @param string $_fileName
     * @return mixed Array(url, expiry, size, mtime) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetRemoteFileInfo($_fileName)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_remoteFileInfoPath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_FILES_DIRECTORY . '/_' . $_fileName;

        if (!file_exists($_remoteFileInfoPath)) {
            return false;
        }

        $_remoteFileInfo = @json_decode(@file_get_contents($_remoteFileInfoPath), true);
        if (!_is_array($_remoteFileInfo)) {
            return false;
        }

        // Array Structure: url, expiry, size, mtime
        if (!isset($_remoteFileInfo['url']) || !isset($_remoteFileInfo['expiry']) || !isset($_remoteFileInfo['size']) || !isset($_remoteFileInfo['mtime']) || $_remoteFileInfo['expiry'] < DATENOW) {
            return false;
        }

        return $_remoteFileInfo;
    }
}
