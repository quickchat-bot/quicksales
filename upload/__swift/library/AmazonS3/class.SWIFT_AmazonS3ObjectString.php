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

SWIFT_Loader::LoadInterface('AmazonS3:AmazonS3Object');

/**
 * The Amazon S3 Object: String
 *
 * @author Varun Shoor
 */
class SWIFT_AmazonS3ObjectString extends SWIFT_AmazonS3Object implements SWIFT_AmazonS3Object_Interface
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_stringData The String Data Containere
     * @throws SWIFT_Exception If the Class could not be Loaded
     */
    public function __construct($_stringData)
    {
        parent::__construct();

        if (!$this->SetData($_stringData))
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->SetMD5(md5($_stringData, true));
        $this->SetSize(mb_strlen($_stringData));
        $this->SetContentType('application/octet-stream');
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
}
?>