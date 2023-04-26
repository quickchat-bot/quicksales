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

namespace Base\Models\Attachment;

use Base\Library\Attachment\SWIFT_Attachment_Exception;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Attachment Storage Abstract Class
 *
 * @author Varun Shoor
 * @method string GetFilePath()
 */
abstract class SWIFT_AttachmentStore extends SWIFT_Model
{
    protected $_fileName = '';
    protected $_fileSize = 0;
    protected $_fileType = '';
    protected $_dataContainer = '';
    protected $_chunkSize = 0;
    protected $_contentID = '';

    // Core Constants
    const DEFAULT_CHUNKSIZE = 2097152; // 2MB

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_fileName
     * @param int $_fileSize
     * @param string $_fileType
     * @param string $_contentID (OPTIONAL)
     */
    public function __construct($_fileName, $_fileSize, $_fileType, $_contentID = '')
    {
        parent::__construct();

        $this->SetFileName($_fileName);
        $this->SetFileSize($_fileSize);
        $this->SetFileType($_fileType);
        $this->SetContentID($_contentID);
    }

    /**
     * Set the File Name
     *
     * @author Varun Shoor
     * @param string $_fileName The File Name
     * @return bool
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetFileName($_fileName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_fileName)) {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        $this->_fileName = $_fileName;

        return true;
    }

    /**
     * Set the Content ID
     * @author Pankaj Garg
     *
     * @param string $_contentID
     *
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetContentID($_contentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_contentID = trim($_contentID);

        return true;
    }

    /**
     * @author Pankaj Garg
     *
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetContentID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_contentID;
    }

    /**
     * Retrieve the File Name
     *
     * @author Varun Shoor
     * @return mixed "_fileName" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function GetFileName()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_fileName;
    }

    /**
     * Set the File Size
     *
     * @author Varun Shoor
     * @param int $_fileSize The File Size
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    protected function SetFileSize($_fileSize)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_fileSize)) {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        $this->_fileSize = $_fileSize;

        return true;
    }

    /**
     * Retrieve the File Size
     *
     * @author Varun Shoor
     * @return int "_fileSize" (INT) on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function GetFileSize()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_fileSize;
    }

    /**
     * Set the File Type
     *
     * @author Varun Shoor
     * @param string $_fileType The File Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    protected function SetFileType($_fileType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_fileType)) {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        $this->_fileType = $_fileType;

        return true;
    }

    /**
     * Retrieve the File Type
     *
     * @author Varun Shoor
     * @return bool "_fileType" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function GetFileType()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_fileType;
    }

    /**
     * Set the Data
     *
     * @author Varun Shoor
     * @param string $_dataContainer The Data Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    protected function SetData($_dataContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_dataContainer)) {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        $this->_dataContainer = $_dataContainer;

        return true;
    }

    /**
     * Retrieve the Data Container
     *
     * @author Varun Shoor
     * @return mixed "_dataContainer" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function GetData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataContainer;
    }

    abstract public function GetChunk();
    abstract public function Reset();
    abstract public function GetSHA1($_attachmentType);

    /**
     * protected helper function to write AttachmentStore data to a file
     *
     * @return mixed temp file name if succesful and false otherwise
     * @author Werner Garcia
     */
    protected function writeChunks()
    {
        $_finalFilePath = tempnam(sys_get_temp_dir(), 'swift_');
        // wb mode for binary safety from PHP documentation
        $_filePointer = @fopen($_finalFilePath, 'wb');

        /** @codeCoverageIgnoreStart */
        // This case cannot be tested. It happens if system temp dir is full
        if (!$_filePointer) {
            return false;
        }
        /** @codeCoverageIgnoreEnd */

        do {
            // this will never throw an exception, fileResource can never be 0 in
            // SWIFT_AttachmentStoreFile child class
            $_chunkData = $this->GetChunk();
            if (!$_chunkData)
            {
                break;
            }

            fwrite($_filePointer, $_chunkData);
        } while (true);

        fclose($_filePointer);
        $this->Reset();

        return $_finalFilePath;
    }
}

?>
