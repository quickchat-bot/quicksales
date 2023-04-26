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

SWIFT_Loader::LoadInterface('AmazonS3:AmazonS3Object');

/**
 * The Amazon S3 Object: File
 *
 * @author Varun Shoor
 */
class SWIFT_AmazonS3ObjectFile extends SWIFT_AmazonS3Object implements SWIFT_AmazonS3Object_Interface
{
    private $_objectFilePath = '';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_filePath The Complete File Path
     * @throws SWIFT_Exception If the Class could not be Loaded
     */
    public function __construct($_filePath)
    {
        parent::__construct();

        if (!$this->SetFilePath($_filePath))
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        try {
            $this->SetMD5(md5_file($_filePath, true));
            $this->SetSize(filesize($_filePath));
            $this->SetContentType(self::GetMimeType($_filePath));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . $_SWIFT_ExceptionObject->getMessage() . ': ' . $_filePath);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Set the Object File Path
     *
     * @author Varun Shoor
     * @param string $_filePath The object file path
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetFilePath($_filePath)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!file_exists($_filePath) || !is_readable($_filePath)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_objectFilePath = $_filePath;

        return true;
    }

    /**
     * Retrieve the Object File Path
     *
     * @author Varun Shoor
     * @return mixed "_objectFilePath" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFilePath()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_objectFilePath;
    }
}
?>