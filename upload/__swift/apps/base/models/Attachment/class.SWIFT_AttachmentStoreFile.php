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

/**
 * The File Attachment Store
 *
 * @author Varun Shoor
 */
class SWIFT_AttachmentStoreFile extends SWIFT_AttachmentStore
{
    private $_filePath = '';
    private $_fileResource = 0;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_filePath The File Path
     * @param string $_fileType The File Type
     * @param string $_fileNameOpt (OPTIONAL) The Custom File Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If Invalid Data is Provided
     */
    public function __construct($_filePath, $_fileType, $_fileNameOpt = '')
    {
        if (!file_exists($_filePath)) {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        $_fileSize = filesize($_filePath);

        $_fileName = $_fileNameOpt;
        if (empty($_fileNameOpt)) {
            $_pathInfoContainer = pathinfo($_filePath);
            if (!isset($_pathInfoContainer['basename']) || empty($_pathInfoContainer['basename'])) {
                throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
            }

            $_fileName = $_pathInfoContainer['basename'];
        }

        parent::__construct($_fileName, $_fileSize, $_fileType);

        $this->SetFilePath($_filePath);

        /**
         * Open the File
         */

        $this->OpenFile();
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __destruct()
    {
        $this->CloseFile();

        parent::__destruct();
    }

    /**
     * Open the File and Set the File Resource
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    protected function OpenFile()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Add the chunks
        $_filePointer = fopen($this->GetFilePath(), 'rb');
        if (!$_filePointer) {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        $this->SetFileResource($_filePointer);

        return true;
    }

    /**
     * Close the File Resource
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    protected function CloseFile()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fileResource = $this->GetFileResource();
        if (!$_fileResource) {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        fclose($_fileResource);

        return true;
    }

    /**
     * Set the File Resource
     *
     * @author Varun Shoor
     * @param int|resource $_fileResource The File Resource
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    protected function SetFileResource($_fileResource)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_fileResource) {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        $this->_fileResource = $_fileResource;

        return true;
    }

    /**
     * Retrieve the File Resource
     *
     * @author Varun Shoor
     * @return mixed "_fileResource" (RESOURCE) on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    protected function GetFileResource()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_fileResource;
    }

    /**
     * Set the File Path
     *
     * @author Varun Shoor
     * @param string $_filePath The File Path
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    protected function SetFilePath($_filePath)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_filePath)) {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        $this->_filePath = $_filePath;

        return true;
    }

    /**
     * Get the File Path
     *
     * @author Varun Shoor
     * @return mixed "_filePath" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function GetFilePath()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_filePath;
    }

    /**
     * Retrieve the File Chunk
     *
     * @author Varun Shoor
     * @return mixed "_chunkData" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function GetChunk()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fileResource = $this->GetFileResource();
        if (!$_fileResource) {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        $_chunkData = fread($_fileResource, SWIFT_Attachment::GetChunkSize());
        if (strlen($_chunkData) == 0) {
            return false;
        }

        return $_chunkData;
    }

    /**
     * Resets the file pointer
     * @throws SWIFT_Attachment_Exception from GetFileResource if class not loaded
     * @author Werner Garcia
     */
    public function Reset() : void {
        @rewind($this->GetFileResource());
    }

    /**
     * Utility method to get SHA1 hash from attachment data provided by child
     * classes (File and String)
     * @param int $_attachmentType
     * @return string the SHA1 hash from the store data or empty string in case of error
     * @throws SWIFT_Attachment_Exception if class is not loaded
     * @author Werner Garcia
     */
    public function GetSHA1($_attachmentType): string
    {
        $_sha1 = '';
        if ($_attachmentType === SWIFT_Attachment::TYPE_FILE) {
            if (file_exists($this->GetFilePath())) {
                $_sha1 = sha1_file($this->GetFilePath());
            }
            // Else case is not needed. If the attachment file does not
            // exist, sha1 is empty and the attachment needs to be processed
        } else if ($_attachmentType === SWIFT_Attachment::TYPE_DATABASE) {
            $_tempFilePath = $this->writeChunks();
            if (@file_exists($_tempFilePath)) {
                $_sha1 = sha1_file($_tempFilePath);
                @unlink($_tempFilePath);
            }
        }

        return $_sha1;
    }
}

?>
