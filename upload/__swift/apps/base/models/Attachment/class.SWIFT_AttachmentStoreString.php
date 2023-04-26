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

namespace Base\Models\Attachment;

use Base\Library\Attachment\SWIFT_Attachment_Exception;

/**
 * The String Attachment Store Handling Class
 *
 * @author Varun Shoor
 */
class SWIFT_AttachmentStoreString extends SWIFT_AttachmentStore
{
    private $_chunkOffset = 0;
    private $_maxChunkOffsetCount = 0;

    /**
     * @author Varun Shoor
     *
     * @param string $_fileName
     * @param string $_fileType
     * @param string $_dataContainer
     * @param string $_contentID (OPTIONAL)
     */
    public function __construct($_fileName, $_fileType, $_dataContainer, $_contentID = '')
    {
        /*
         * BUG FIX - Rahul Bhattacharya
         *
         * SWIFT-3031 Incorrect handling of attachments created via REST API
         *
         */
        parent::__construct($_fileName, mb_strlen($_dataContainer, '8bit'), $_fileType, $_contentID);

        $this->SetData($_dataContainer);
    }

    /**
     * Retrieve the Chunk
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

        $this->_maxChunkOffsetCount = ceil($this->GetFileSize() / SWIFT_Attachment::GetChunkSize());

        $_chunkProcessedOffset = $this->GetChunkOffset() * SWIFT_Attachment::GetChunkSize();

        if ($this->GetChunkOffset() > $this->_maxChunkOffsetCount) {
            return false;
        }

        $_chunkData = mb_substr($this->GetData(), $_chunkProcessedOffset, SWIFT_Attachment::GetChunkSize());

        $this->IncrementChunkOffset();

        return $_chunkData;
    }

    /**
     * Set the Chunk Offset
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    protected function IncrementChunkOffset()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_chunkOffset++;

        return true;
    }

    /**
     * Retrieve the Chunk Offset
     *
     * @author Varun Shoor
     * @return mixed "_chunkOffset" (INT) on Success, "false" otherwise
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    protected function GetChunkOffset()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_chunkOffset;
    }

    /**
     * Resets chunkoffset
     * @author Werner Garcia
     */
    public function Reset(): void
    {
        $this->_chunkOffset = 0;
    }

    /**
     * Utility method to get SHA1 hash from attachment data provided by child
     * classes (File and String)
     * @param int $_attachmentType
     * @return string the SHA1 hash from the store data or empty string in case of error
     * @author Werner Garcia
     */
    public function GetSHA1($_attachmentType): string
    {
        $_sha1 = '';
        $_tempFilePath = $this->writeChunks();
        if (@file_exists($_tempFilePath)) {
            $_sha1 = sha1_file($_tempFilePath);
            @unlink($_tempFilePath);
        }

        return $_sha1;
    }
}

?>
