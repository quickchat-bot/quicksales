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
 * The Amazon S3 Object Handling Class
 *
 * @author Varun Shoor
 */
abstract class SWIFT_AmazonS3Object extends SWIFT_Library
{
    protected $_objectSize = 0;
    protected $_objectContentType = false;
    protected $_objectData = false;
    protected $_objectMD5 = false;

    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
        parent::__construct();
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
     * Retrieve the Object Size
     *
     * @author Varun Shoor
     * @return mixed "_objectSize" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSize()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_objectSize;
    }

    /**
     * Set the Object Size
     *
     * @author Varun Shoor
     * @param mixed $_objectSize The Object Size
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetSize($_objectSize)
    {
        $_objectSize = (int) ($_objectSize);

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_objectSize)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_objectSize = $_objectSize;

        return true;
    }

    /**
     * Set the Content Type
     *
     * @author Varun Shoor
     * @param string $_objectContentType The Object Content Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetContentType($_objectContentType)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_objectContentType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_objectContentType = $_objectContentType;

        return true;
    }

    /**
     * Retrieve the currently set Content Type
     *
     * @author Varun Shoor
     * @return mixed "_objectContentType" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetContentType()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_objectContentType;
    }

    /**
     * Set the Object Data
     *
     * @author Varun Shoor
     * @param string $_objectData The Object Data
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetData($_objectData)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_objectData = $_objectData;

        return true;
    }

    /**
     * Retrieve the currently set Object Data
     *
     * @author Varun Shoor
     * @return mixed "_objectData" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetData()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_objectData;
    }

    /**
     * Set the Object MD5 Hash
     *
     * @author Varun Shoor
     * @param string $_objectMD5 The MD5 Hash of the Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetMD5($_objectMD5)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_objectMD5 = $_objectMD5;

        return true;
    }

    /**
     * Retrieve the Object MD5
     *
     * @author Varun Shoor
     * @return mixed "_objectMD5" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetMD5()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_objectMD5;
    }

    /**
     * Get MIME type for file
     *
     * @internal Used to get mime types
     * @param string $_filePath File path
     * @return mixed "_contentType" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function GetMimeType($_filePath) {
        if (empty($_filePath) || !file_exists($_filePath))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_type = false;

        // Fileinfo documentation says fileinfo_open() will use the
        // MAGIC env var for the magic file

        if (extension_loaded('fileinfo') && isset($_ENV['MAGIC']) && ($_filePointerInfo = finfo_open(FILEINFO_MIME, $_ENV['MAGIC'])) !== false)
        {
            if (($_type = finfo_file($_filePointerInfo, $_filePath)) !== false)
            {
                // Remove the charset and grab the last content-type
                $_type = explode(' ', str_replace('; charset=', ';charset=', $_type));
                $_type = array_pop($_type);
                $_type = explode(';', $_type);
                $_type = array_shift($_type);
            }

            finfo_close($_filePointerInfo);

        // If anyone is still using mime_content_type()
        } else if (function_exists('mime_content_type')) {
            $_type = mime_content_type($_filePath);
        }

        if ($_type !== false && strlen($_type) > 0)
        {
            return $_type;
        }

        // Otherwise do it the old fashioned way
        $_extensionContainer = array(
            'jpg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png',
            'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'ico' => 'image/x-icon',
            'swf' => 'application/x-shockwave-flash', 'pdf' => 'application/pdf',
            'zip' => 'application/zip', 'gz' => 'application/x-gzip',
            'tar' => 'application/x-tar', 'bz' => 'application/x-bzip',
            'bz2' => 'application/x-bzip2', 'txt' => 'text/plain',
            'asc' => 'text/plain', 'htm' => 'text/html', 'html' => 'text/html',
            'xml' => 'text/xml', 'xsl' => 'application/xsl+xml',
            'ogg' => 'application/ogg', 'mp3' => 'audio/mpeg', 'wav' => 'audio/x-wav',
            'avi' => 'video/x-msvideo', 'mpg' => 'video/mpeg', 'mpeg' => 'video/mpeg',
            'mov' => 'video/quicktime', 'flv' => 'video/x-flv', 'php' => 'text/x-php'
        );

        $_extension = strtolower(pathinfo($_filePath, PATHINFO_EXTENSION));

        if (isset($_extensionContainer[$_extension]))
        {
            return $_extensionContainer[$_extension];
        }

        return 'application/octet-stream';
    }
}
?>