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
 * $Id: S3.php 33 2008-07-30 17:30:20Z don.schonknecht $
 *
 * Copyright (c) 2007, Donovan Schonknecht.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */


/**
 * Amazon S3 PHP class
 *
 * @link http://undesigned.org.za/2007/10/22/amazon-s3-php-class
 * @version 0.3.3
 */
class SWIFT_AmazonS3 extends SWIFT_Library {
    // ACL flags
    const ACL_PRIVATE = 'private';
    const ACL_PUBLIC_READ = 'public-read';
    const ACL_PUBLIC_READ_WRITE = 'public-read-write';

    const BASE_URL = 's3.amazonaws.com';
    const DEFAULT_EXPIRY = 1200; // 20 Minutes

    private $_useSSL = true;

    private $__accessKey; // AWS Access key
    private $__secretKey; // AWS Secret key

    /**
     * Constructor
     *
     * @param string $_accessKey Access key
     * @param string $_secretKey Secret key
     * @param boolean $_useSSL Whether or not to use SSLe
     * @throws SWIFT_AmazonS3_Exception If the Class could not be loaded
     */
    public function __construct($_accessKey, $_secretKey, $_useSSL = true) {
        parent::__construct();

        if (!$this->SetAccessKey($_accessKey) || !$this->SetSecretKey($_secretKey))
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            $this->SetIsClassLoaded(false);
    }

        $this->SetCanUseSSL($_useSSL);

        $this->Load->_Interface('AmazonS3:AmazonS3Object');
    }

    /**
     * Set the Access Key
     *
     * @author Varun Shoor
     * @param string $_accessKey Access key
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonS3_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetAccessKey($_accessKey)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_accessKey)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->__accessKey = $_accessKey;

        return true;
    }

    /**
     * Retrieve the Currently Set Access Key
     *
     * @author Varun Shoor
     * @return mixed "__accessKey" (STRING) on Success, "false" otherwise
     * @throws SWIFT_AmazonS3_Exception If the Class is not Loaded
     */
    public function GetAccessKey()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->__accessKey;
    }

    /**
     * Set the Secret Key
     *
     * @author Varun Shoor
     * @param string $_secretKey Secret key
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonS3_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetSecretKey($_secretKey)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_secretKey)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->__secretKey = $_secretKey;

        return true;
    }

    /**
     * Get the Secret Key
     *
     * @author Varun Shoor
     * @return mixed "__secretKey" (STRING) on Success, "false" otherwise
     * @throws SWIFT_AmazonS3_Exception If the Class is not Loaded
     */
    public function GetSecretKey()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->__secretKey;
    }

    /**
     * Check to see if the user can use SSL
     *
     * @author Varun Shoor
     * @return int "1" on Success, "0" otherwise
     * @throws SWIFT_AmazonS3_Exception If the Class is not Loaded
     */
    public function CanUseSSL()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);
        }

        return (int) ($this->_useSSL);
    }

    /**
     * Set the Can Use SSL property
     *
     * @author Varun Shoor
     * @param bool $_useSSL The Use SSL Property
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonS3_Exception If the Class is not Loaded
     */
    public function SetCanUseSSL($_useSSL)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_useSSL = (int) ($_useSSL);

        $this->_useSSL = $_useSSL;

        return true;
    }

    /**
     * Check the Amazon S3 Response to make sure the error codes are right
     *
     * @author Varun Shoor
     * @param SWIFT_AmazonS3Response $_SWIFT_AmazonS3ResponseObject The SWIFT_AmazonS3Response Object Pointer
     * @param string $_callingFunction (OPTIONAL) The Name of Function Running this Check
     * @param int $_httpCode (OPTIONAL) The HTTP Code to Check Against
     * @param bool $_endExecution (BOOL) Whether to End the Execution if Error Encountered
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonS3_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    private function CheckResponse(SWIFT_AmazonS3Response $_SWIFT_AmazonS3ResponseObject, $_callingFunction = '', $_httpCode = 200, $_endExecution = true)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!$_SWIFT_AmazonS3ResponseObject->GetIsClassLoaded()) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if ($_SWIFT_AmazonS3ResponseObject->GetError() === false && $_SWIFT_AmazonS3ResponseObject->GetHTTPCode() !== $_httpCode)
        {
            $_SWIFT_AmazonS3ResponseObject->Error($_SWIFT_AmazonS3ResponseObject->GetHTTPCode(), 'Unexpected HTTP status');
        }

        if ($_SWIFT_AmazonS3ResponseObject->GetError() !== false) {

            if ($_endExecution)
            {
                $_errorContainer = $_SWIFT_AmazonS3ResponseObject->GetError();

                throw new SWIFT_AmazonS3_Exception(sprintf("SWIFT_AmazonS3::". $_callingFunction .": [%s] %s", $_errorContainer['code'], $_errorContainer['message']));

                return false;
            }

            return false;
        }

        return true;
    }

    /**
     * Get a list of buckets
     *
     * @param bool $_detailedResults Returns detailed bucket list when true
     * @return array | false
     * @throws SWIFT_AmazonS3_Exception If the Class is not Loaded
     */
    public function ListBuckets($_detailedResults = false) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_GET, '', '');

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ListBuckets()', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_results = array();
        if (!isset($_BodyObject->Buckets))
        {
            return $_results;
        }

        if ($_detailedResults)
        {
            if (property_exists($_BodyObject, 'Owner') && isset($_BodyObject->Owner->ID, $_BodyObject->Owner->DisplayName))
            {
                $_results['owner'] = array('id' => (string)$_BodyObject->Owner->ID, 'name' => (string)$_BodyObject->Owner->ID);
            }

            $_results['buckets'] = array();

            foreach ($_BodyObject->Buckets->Bucket as $_bucket)
            {
                $_results['buckets'][] = array('name' => (string)$_bucket->Name, 'time' => strtotime((string)$_bucket->CreationDate));
            }

        } else {
            foreach ($_BodyObject->Buckets->Bucket as $_bucket)
            {
                $_results[] = (string)$_bucket->Name;
            }
        }

        return $_results;
    }


    /*
     * Get contents for a bucket
     *
     * If maxKeys is null this method will loop through truncated result sets
     *
     * @param string $_bucketName Bucket name
     * @param string $_prefix Prefix
     * @param string $_marker Marker (last file listed)
     * @param string $_maxKeys Max keys (maximum number of keys to return)
     * @param string $_delimiter Delimiter
     * @return array | false
     * @throws SWIFT_AmazonS3_Exception If the Class is not Loaded
     */
    public function GetBucket($_bucketName, $_prefix = null, $_marker = null, $_maxKeys = null, $_delimiter = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_GET, $_bucketName, '');

        if ($_prefix !== null && $_prefix !== '')
        {
            $_SWIFT_AmazonS3RequestObject->SetParameter('prefix', $_prefix);
        }

        if ($_marker !== null && $_marker !== '')
        {
            $_SWIFT_AmazonS3RequestObject->SetParameter('marker', $_marker);
        }

        if ($_maxKeys !== null && $_maxKeys !== '')
        {
            $_SWIFT_AmazonS3RequestObject->SetParameter('max-keys', $_maxKeys);
        }

        if ($_delimiter !== null && $_delimiter !== '')
        {
            $_SWIFT_AmazonS3RequestObject->SetParameter('delimiter', $_delimiter);
        }

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'GetBucket()', 200))
        {
            return false;
        }

        $_results = array();

        $_lastMarker = null;
        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject, $_BodyObject->Contents))
        {
            foreach ($_BodyObject->Contents as $_Contents) {
                $_results[(string)$_Contents->Key] = array(
                    'name' => (string)$_Contents->Key,
                    'time' => strtotime((string)$_Contents->LastModified),
                    'size' => (int)$_Contents->Size,
                    'hash' => substr((string)$_Contents->ETag, 1, -1)
                );

                $_lastMarker = (string)$_Contents->Key;
            }
        }

        if (isset($_BodyObject->NextMarker)) {
            $_lastMarker = (string) $_BodyObject->NextMarker;
        }

        if (isset($_BodyObject->IsTruncated) && (string)$_BodyObject->IsTruncated == 'false')
        {
            return $_results;
        }

        // Loop through truncated results if maxKeys isn't specified
        if ($_lastMarker !== null && (string)$_BodyObject->IsTruncated == 'true')
        {
            $_loopAttempts = 0;
            do
            {
                $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_GET, $_bucketName, '');

                if ($_prefix !== null && $_prefix !== '')
                {
                    $_SWIFT_AmazonS3RequestObject->SetParameter('prefix', $_prefix);
                }

                $_SWIFT_AmazonS3RequestObject->SetParameter('marker', $_lastMarker);

                if ($_maxKeys !== null && $_maxKeys !== '')
                {
                    $_SWIFT_AmazonS3RequestObject->SetParameter('max-keys', $_maxKeys);
                }

                if ($_delimiter !== null && $_delimiter !== '')
                {
                    $_SWIFT_AmazonS3RequestObject->SetParameter('delimiter', $_delimiter);
                }

                $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

                if (!$this->CheckResponse($_ResponseObject, 'GetBucket()', 200, true))
                {
                    break;
                }

                $_BodyObject = $_ResponseObject->GetBodyObject();

                if (isset($_BodyObject, $_BodyObject->Contents))
                {
                    foreach ($_BodyObject->Contents as $_Content) {
                        $_results[(string)$_Content->Key] = array(
                            'name' => (string)$_Content->Key,
                            'time' => strtotime((string)$_Content->LastModified),
                            'size' => (int)$_Content->Size,
                            'hash' => substr((string)$_Content->ETag, 1, -1)
                        );

                        $_lastMarker = (string)$_Content->Key;
                    }
                }

                if (isset($_BodyObject->NextMarker)) {
                    $_lastMarker = (string) $_BodyObject->NextMarker;
                }

                $_loopAttempts++;

            } while ($_ResponseObject !== false && (string)$_BodyObject->IsTruncated == 'true');
        }

        return $_results;
    }


    /**
     * Create a bucket
     *
     * @param string $_bucketName Bucket name
     * @param mixed $_acl ACL flag
     * @param string|false $_location Set as "EU" to create buckets hosted in Europe
     * @return boolean
     * @throws SWIFT_AmazonS3_Exception If the Class is not Loaded
     */
    public function CreateBucket($_bucketName, $_acl = self::ACL_PRIVATE, $_location = false) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_PUT, $_bucketName, '');

        $_SWIFT_AmazonS3RequestObject->SetAmazonHeader('x-amz-acl', $_acl);

        if ($_location !== false) {
            $_DOMObject = new DOMDocument();
            $_createBucketConfiguration = $_DOMObject->createElement('CreateBucketConfiguration');
            $_locationConstraint = $_DOMObject->createElement('LocationConstraint', strtoupper($_location));
            $_createBucketConfiguration->appendChild($_locationConstraint);
            $_DOMObject->appendChild($_createBucketConfiguration);
            $_SWIFT_AmazonS3RequestObject->SetData($_DOMObject->saveXML());
            $_SWIFT_AmazonS3RequestObject->SetSize(strlen($_SWIFT_AmazonS3RequestObject->GetData()));
            $_SWIFT_AmazonS3RequestObject->SetHeader('Content-Type', 'application/xml');
        }

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'CreateBucket('. $_bucketName .', '. $_acl .', '. $_location .')', 200))
        {
            return false;
        }

        return true;
    }


    /**
     * Delete an empty bucket
     *
     * @param string $_bucketName Bucket name
     * @return boolean
     * @throws SWIFT_AmazonS3_Exception If the Class is not Loaded
     */
    public function DeleteBucket($_bucketName = '') {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_DELETE, $_bucketName, '');

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'DeleteBucket('. $_bucketName .')', 204))
        {
            return false;
        }

        return true;
    }


    /**
     * Copy an object
     *
     * @param string $_sourceBucketName
     * @param string $_sourceURI
     * @param string $_destinationBucketName
     * @param string $_destinationURI
     * @param mixed $_acl The ACL constant
     * @param array $_metaHeaders Array of x-amz-meta-* headers
     * @param mixed $_requestHeaders Array of request headers or content type as a string
     * @return boolean
     * @throws SWIFT_AmazonS3_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CopyObject($_sourceBucketName, $_sourceURI, $_destinationBucketName, $_destinationURI, $_acl = self::ACL_PRIVATE, $_metaHeaders = array(), $_requestHeaders = array()) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_sourceBucketName) || empty($_sourceURI) || empty($_destinationBucketName) || empty($_destinationURI)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if (substr($_destinationURI, 0, 1) == '/') {
            $_destinationURI = substr($_destinationURI, 1);
        }

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_PUT, $_destinationBucketName, $_destinationURI);

        // Custom request headers (Content-Type, Content-Disposition, Content-Encoding)
        if (is_array($_requestHeaders))
        {
            foreach ($_requestHeaders as $_key => $_val)
            {
                $_SWIFT_AmazonS3RequestObject->SetHeader($_key, $_val);
            }
        }

        if (substr($_sourceURI, 0, 1) == '/') {
            $_sourceURI = substr($_sourceURI, 1);
        }

        $_SWIFT_AmazonS3RequestObject->SetAmazonHeader('x-amz-copy-source', '/' . $_sourceBucketName . '/' . $_sourceURI);

        $_SWIFT_AmazonS3RequestObject->SetAmazonHeader('x-amz-acl', $_acl);

        if (is_array($_metaHeaders))
        {
            foreach ($_metaHeaders as $_key => $_val)
            {
                $_SWIFT_AmazonS3RequestObject->SetAmazonHeader('x-amz-meta-' . $_key, $_val);
            }
        }

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'CopyObject(' . implode(func_get_args()) . ')', 200))
        {
            return false;
        }

        return true;
    }


    /**
     * Move an object
     *
     * @param string $_sourceBucketName
     * @param string $_sourceURI
     * @param string $_destinationBucketName
     * @param string $_destinationURI
     * @param mixed $_acl The ACL constant
     * @param array $_metaHeaders Array of x-amz-meta-* headers
     * @param mixed $_requestHeaders Array of request headers or content type as a string
     * @return boolean
     * @throws SWIFT_AmazonS3_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function MoveObjects($_sourceBucketName, $_sourceURI, $_destinationBucketName, $_destinationURI, $_acl = self::ACL_PRIVATE, $_metaHeaders = array(), $_requestHeaders = array()) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_sourceBucketName) || empty($_sourceURI) || empty($_destinationBucketName) || empty($_destinationURI)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if (substr($_destinationURI, 0, 1) == '/') {
            $_destinationURI = substr($_destinationURI, 1);
        }

        if (substr($_sourceURI, 0, 1) == '/') {
            $_sourceURI = substr($_sourceURI, 1);
        }

        $_destinationURI = StripTrailingSlash($_destinationURI);
        $_sourceURI = StripTrailingSlash($_sourceURI);

        $_bucketObjectList = $this->GetBucket($_sourceBucketName, $_sourceURI);
        foreach ($_bucketObjectList as $_fileURI => $_file) {
            $_filePathInfo = pathinfo($_fileURI);

            $_filePathExtended = '';
            if (strtolower(substr($_fileURI, 0, strlen($_sourceURI))) == strtolower($_sourceURI)) {
                $_newURI = substr($_fileURI, strlen($_sourceURI));

                $_filePathExtended = substr($_newURI, 0, strrpos($_newURI, '/' . $_filePathInfo['basename']));
            }

            $this->CopyObject($_sourceBucketName, $_fileURI, $_destinationBucketName, $_destinationURI . $_filePathExtended . '/' . $_filePathInfo['basename']);
            $this->DeleteObject($_sourceBucketName, $_fileURI);
        }

        return true;
    }


    /**
     * Put an object
     *
     * @param SWIFT_AmazonS3Object $_SWIFT_AmazonS3Object The SWIFT_AmazonS3Object Pointer
     * @param string $_bucketName Bucket name
     * @param string $_uri Object URI
     * @param mixed $_acl The ACL constant
     * @param array $_metaHeaders Array of x-amz-meta-* headers
     * @param mixed $_requestHeaders Array of request headers or content type as a string
     * @return boolean
     * @throws SWIFT_AmazonS3_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function PutObject(SWIFT_AmazonS3Object $_SWIFT_AmazonS3Object, $_bucketName, $_uri, $_acl = self::ACL_PRIVATE, $_metaHeaders = array(), $_requestHeaders = array()) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!$_SWIFT_AmazonS3Object->GetIsClassLoaded() || empty($_bucketName) || empty($_uri)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_PUT, $_bucketName, $_uri);

        if ($_SWIFT_AmazonS3Object instanceof SWIFT_AmazonS3ObjectFile) {
            $_SWIFT_AmazonS3RequestObject->SetFilePointer(fopen($_SWIFT_AmazonS3Object->GetFilePath(), 'rb'));
        } else if ($_SWIFT_AmazonS3Object instanceof SWIFT_AmazonS3ObjectString) {
            $_SWIFT_AmazonS3RequestObject->SetData($_SWIFT_AmazonS3Object->GetData());
        } else {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonS3RequestObject->SetSize($_SWIFT_AmazonS3Object->GetSize());

        // Custom request headers (Content-Type, Content-Disposition, Content-Encoding)
        if (is_array($_requestHeaders))
        {
            foreach ($_requestHeaders as $_key => $_val)
            {
                $_SWIFT_AmazonS3RequestObject->SetHeader($_key, $_val);
            }
        }

        if (!isset($_requestHeaders['Content-Type']))
        {
            $_SWIFT_AmazonS3RequestObject->SetHeader('Content-Type', $_SWIFT_AmazonS3Object->GetContentType());
        }

        if (!isset($_requestHeaders['Content-MD5']))
        {
            $_SWIFT_AmazonS3RequestObject->SetHeader('Content-MD5', base64_encode($_SWIFT_AmazonS3Object->GetMD5()));
        }

        $_SWIFT_AmazonS3RequestObject->SetAmazonHeader('x-amz-acl', $_acl);

        if (is_array($_metaHeaders))
        {
            foreach ($_metaHeaders as $_key => $_val)
            {
                $_SWIFT_AmazonS3RequestObject->SetAmazonHeader('x-amz-meta-' . $_key, $_val);
            }
        }

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'PutObject(' . $_bucketName . ', ' . $_uri . ', ' . $_acl . ')', 200))
        {
            return false;
        }

        return true;
    }

    /**
     * Get an object
     *
     * @param string $_bucketName Bucket name
     * @param string $_uri Object URI
     * @param mixed $_saveTo Filename or resource to write to
     * @return mixed "_SWIFT_AmazonS3Response" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_AmazonS3_Exception If the Class is not loaded or If Invalid Data is Provided
     */
    public function GetObject($_bucketName, $_uri, $_saveTo = false) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_bucketName) || empty($_uri)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_GET, $_bucketName, $_uri);

        if ($_saveTo !== false)
        {
            if (is_resource($_saveTo))
            {
                $_SWIFT_AmazonS3RequestObject->SetFilePointer($_saveTo);
            } else {
                if (($_filePointer = fopen($_saveTo, 'wb')) == false)
                {
                    throw new SWIFT_AmazonS3_Exception('Unable to open save file for writing: ' . $_saveTo);

                    return false;
                }

                $_SWIFT_AmazonS3RequestObject->SetFilePointer($_filePointer);
            }
        }

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'GetObject(' . $_bucketName . ', ' . $_uri . ')', 200))
        {
            return false;
        }

        return $_ResponseObject;
    }


    /**
     * Get object information
     *
     * @param string $_bucketName Bucket name
     * @param string $_uri Object URI
     * @param boolean $_returnInfo Return response information
     * @return mixed | false
     * @throws SWIFT_AmazonS3_Exception If the Class is not loaded or If Invalid Data is Provided
     */
    public function GetObjectInfo($_bucketName, $_uri, $_returnInfo = true) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_bucketName) || empty($_uri)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_HEAD, $_bucketName, $_uri);

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'GetObjectInfo(' . $_bucketName . ', ' . $_uri . ')', 200))
        {
            return false;
        }

        if (!$_returnInfo)
        {
            return true;
        }

        return $_ResponseObject->GetHeaders();
    }

    /**
     * Get object access URL
     *
     * @param string $_bucketName Bucket name
     * @param string $_uri Object URI
     * @param int $_expiry The Expiry UNIX Epoch Timeline (Default is 20 minutes)
     * @param bool $_isSecure Whether to use secure URL
     * @return string | false
     * @throws SWIFT_AmazonS3_Exception If the Class is not loaded or If Invalid Data is Provided
     */
    public function GetObjectURL($_bucketName, $_uri, $_expiry = 0, $_isSecure = true) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_bucketName) || empty($_uri)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        // Set to default expiry if none provided
        if (!$_expiry)
        {
            $_expiry = DATENOW + self::DEFAULT_EXPIRY;
        }

        $_returnURL = 'http://';
        if ($_isSecure)
        {
            $_returnURL = 'https://';
        }

        $_signatureContainer = "GET\n\n\n" . $_expiry . "\n" . "/" . $_bucketName . "/" . $_uri;
        $_actualSignature = $this->__GetSignature($_signatureContainer);
        $_finalSignature = substr($_actualSignature, strpos($_actualSignature, ':')+1);

        $_returnURL .= self::BASE_URL . '/' . $_bucketName . '/' . $_uri . '?AWSAccessKeyId=' . urlencode($this->GetAccessKey()) . '&Expires=' . urlencode($_expiry) . '&Signature=' . urlencode($_finalSignature);

        return $_returnURL;
    }


    /**
     * Delete an object
     *
     * @param string $_bucketName Bucket name
     * @param string $_uri Object URI
     * @return mixed
     * @throws SWIFT_AmazonS3_Exception If the Class could not be loaded or If Invalid Data is Provided
     */
    public function DeleteObject($_bucketName, $_uri) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_bucketName)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_DELETE, $_bucketName, $_uri);

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'DeleteObject(' . $_bucketName . ', ' . $_uri . ')', 204))
        {
            return false;
        }

        return true;
    }


    /**
     * Generate the auth string: "AWS AccessKey:Signature"
     *
     * This uses the hash extension if loaded
     *
     * @internal Signs the request
     * @param string $_string String to sign
     * @return string
     */
    public function __GetSignature($_string) {
        return 'AWS ' . $this->GetAccessKey() . ':' . base64_encode(hash_hmac('sha1', $_string, $this->GetSecretKey(), true));
    }


    /**
     * Get a bucket's location
     *
     * @param string $_bucketName Bucket name
     * @return string | false
     * @throws SWIFT_AmazonS3_Exception If the Class could not be loaded or If Invalid Data is Provided
     */
    public function GetBucketLocation($_bucketName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_bucketName)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_GET, $_bucketName, '');
        $_SWIFT_AmazonS3RequestObject->SetParameter('location', null);

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'GetBucketLocation(' . $_bucketName . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        return (isset($_BodyObject[0]) && (string)$_BodyObject[0] !== '') ? (string)$_BodyObject[0] : 'US';
    }


    /**
     * Set lifecycle for a bucket
     *
     * @param string $_bucketName Bucket name
     * @param string $_ruleID The Rule Identifier
     * @param string $_objectPrefix The Object Prefix
     * @param bool $_isEnabled
     * @param int $_expirationDays The number of days to keep the object for
     * @return boolean
     * @throws SWIFT_AmazonS3_Exception If the Class could not be loaded or If Invalid Data is Provided
     */
    public function SetBucketLifecycle($_bucketName, $_ruleID, $_objectPrefix, $_isEnabled, $_expirationDays) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_bucketName) || empty($_ruleID) || empty($_expirationDays)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_XMLObject = new SWIFT_XML();
        $_SWIFT_XMLObject->BuildXML('UTF-8', array(), true);
        $_SWIFT_XMLObject->AddParentTag('LifecycleConfiguration');
            $_SWIFT_XMLObject->AddParentTag('Rule');
                $_SWIFT_XMLObject->AddTag('ID', $_ruleID);
                $_SWIFT_XMLObject->AddTag('Prefix', $_objectPrefix);
                $_SWIFT_XMLObject->AddTag('Status', IIF($_isEnabled == true, 'Enabled', 'Disabled'));
                $_SWIFT_XMLObject->AddParentTag('Expiration');
                    $_SWIFT_XMLObject->AddTag('Days', $_expirationDays);
                $_SWIFT_XMLObject->EndParentTag('Expiration');
            $_SWIFT_XMLObject->EndParentTag('Rule');
        $_SWIFT_XMLObject->EndParentTag('LifecycleConfiguration');

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_PUT, $_bucketName, '');
        $_SWIFT_AmazonS3RequestObject->SetParameter('lifecycle', null);
        $_SWIFT_AmazonS3RequestObject->SetHeader('Content-MD5', base64_encode(md5($_SWIFT_XMLObject->ReturnXML(), true)));
        $_SWIFT_AmazonS3RequestObject->SetHeader('Content-Length', strlen($_SWIFT_XMLObject->ReturnXML()));
        $_SWIFT_AmazonS3RequestObject->SetHeader('Content-Type', 'text/xml');
        $_SWIFT_AmazonS3RequestObject->SetData($_SWIFT_XMLObject->ReturnXML());
        $_SWIFT_AmazonS3RequestObject->SetSize(strlen($_SWIFT_AmazonS3RequestObject->GetData()));

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'SetBucketLifecycle(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        return true;
    }


    /**
     * Retrieve the bucket life cycle rules
     *
     * @param string $_bucketName Bucket name
     * @return array | false
     * @throws SWIFT_AmazonS3_Exception If the Class could not be loaded or If Invalid Data is Provided
     */
    public function GetBucketLifecycle($_bucketName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_bucketName)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_GET, $_bucketName, '');
        $_SWIFT_AmazonS3RequestObject->SetParameter('lifecycle', null);

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'GetBucketLifecycle(' . $_bucketName . ')', 200, false))
        {
            return false;
        }

        $_BodyObject = simplexml_load_string($_ResponseObject->GetBody());

        $_bucketLifecycleRules = array();
        if (!isset($_BodyObject->Rule)) {
            return false;
        }

        foreach ($_BodyObject as $_RuleObject) {
            $_ruleID = (string) $_RuleObject->ID;
            $_rulePrefix = (string) $_RuleObject->Prefix;
            $_ruleStatus = (string) $_RuleObject->Status;
            $_ruleExpiry = (string) $_RuleObject->Expiration->Days;

            $_bucketLifecycleRules[] = array('id' => $_ruleID, 'prefix' => $_rulePrefix, 'status' => IIF($_ruleStatus == 'Enabled', true, false), 'expiry' => $_ruleExpiry);
        }

        return $_bucketLifecycleRules;
    }


    /**
     * Delete all the bucket life cycle rules
     *
     * @param string $_bucketName Bucket name
     * @return true | false
     * @throws SWIFT_AmazonS3_Exception If the Class could not be loaded or If Invalid Data is Provided
     */
    public function DeleteBucketLifecycle($_bucketName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_bucketName)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_DELETE, $_bucketName, '');
        $_SWIFT_AmazonS3RequestObject->SetParameter('lifecycle', null);

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'DeleteBucketLifecycle(' . $_bucketName . ')', 204))
        {
            return false;
        }

        return true;
    }


    /**
     * Set logging for a bucket
     *
     * @param string $_bucketName Bucket name
     * @param string $_targetBucketName Target bucket (where logs are stored)
     * @param string $_targetPrefix Log prefix (e,g; domain.com-)
     * @return boolean
     * @throws SWIFT_AmazonS3_Exception If the Class could not be loaded or If Invalid Data is Provided
     */
    public function SetBucketLogging($_bucketName, $_targetBucketName, $_targetPrefix) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_bucketName) || empty($_targetBucketName) || empty($_targetPrefix)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_DOMObject = new DOMDocument;
        $_bucketLoggingStatus = $_DOMObject->createElement('BucketLoggingStatus');
        $_bucketLoggingStatus->setAttribute('xmlns', 'http://s3.amazonaws.com/doc/2006-03-01/');

        $_loggingEnabled = $_DOMObject->createElement('LoggingEnabled');

        $_loggingEnabled->appendChild($_DOMObject->createElement('TargetBucket', $_targetBucketName));
        $_loggingEnabled->appendChild($_DOMObject->createElement('TargetPrefix', $_targetPrefix));

        // TODO: Add TargetGrants

        $_bucketLoggingStatus->appendChild($_loggingEnabled);
        $_DOMObject->appendChild($_bucketLoggingStatus);

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_PUT, $_bucketName, '');
        $_SWIFT_AmazonS3RequestObject->SetParameter('logging', null);
        $_SWIFT_AmazonS3RequestObject->SetHeader('Content-Type', 'application/xml');
        $_SWIFT_AmazonS3RequestObject->SetData($_DOMObject->saveXML());
        $_SWIFT_AmazonS3RequestObject->SetSize(strlen($_SWIFT_AmazonS3RequestObject->GetData()));

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'SetBucketLogging(' . $_bucketName . ', ' . $_targetBucketName . ', ' . $_targetPrefix . ')', 200))
        {
            return false;
        }

        return true;
    }


    /**
     * Get logging status for a bucket
     *
     * This will return false if logging is not enabled.
     * Note: To enable logging, you also need to grant write access to the log group
     *
     * @param string $_bucketName Bucket name
     * @return array | false
     * @throws SWIFT_AmazonS3_Exception If the Class could not be loaded or If Invalid Data is Provided
     */
    public function GetBucketLogging($_bucketName = '') {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_bucketName)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_GET, $_bucketName, '');
        $_SWIFT_AmazonS3RequestObject->SetParameter('logging', null);

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'GetBucketLogging(' . $_bucketName . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        // No Logging
        if (!isset($_BodyObject->LoggingEnabled))
        {
            return false;
        }

        return array(
            'targetBucket' => (string) $_BodyObject->LoggingEnabled->TargetBucket,
            'targetPrefix' => (string) $_BodyObject->LoggingEnabled->TargetPrefix,
        );
    }


    /**
     * Set object or bucket Access Control Policy
     *
     * @param string $_bucketName Bucket name
     * @param string $_uri Object URI
     * @param array $_acp Access Control Policy Data (same as the data returned from self::GetAccessControlPolicy)
     * @return boolean
     * @throws SWIFT_AmazonS3_Exception If the Class could not be loaded or If Invalid Data is Provided
     */
    public function SetAccessControlPolicy($_bucketName, $_uri = '', $_acp = array()) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_bucketName) || !isset($_acp['owner'], $_acp['owner']['id'], $_acp['owner']['name'], $_acp['acl']) || empty($_acp['owner']['name']) || empty($_acp['owner']['id']) || !_is_array($_acp['acl'])) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_DOMObject = new DOMDocument;
        $_DOMObject->formatOutput = true;
        $_accessControlPolicy = $_DOMObject->createElement('AccessControlPolicy');
        $_accessControlList = $_DOMObject->createElement('AccessControlList');

        // It seems the owner has to be passed along too
        $_owner = $_DOMObject->createElement('Owner');
        $_owner->appendChild($_DOMObject->createElement('ID', $_acp['owner']['id']));
        $_owner->appendChild($_DOMObject->createElement('DisplayName', $_acp['owner']['name']));
        $_accessControlPolicy->appendChild($_owner);

        foreach ($_acp['acl'] as $_granteeVal) {
            if (!isset($_granteeVal['permission']) || (!isset($_granteeVal['id']) && !isset($_granteeVal['email']) && !isset($_granteeVal['type'])))
            {
                throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);
            }

            $_grant = $_DOMObject->createElement('Grant');
            $_grantee = $_DOMObject->createElement('Grantee');
            $_grantee->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            if (isset($_granteeVal['id'])) { // CanonicalUser (DisplayName is omitted)
                $_grantee->setAttribute('xsi:type', 'CanonicalUser');
                $_grantee->appendChild($_DOMObject->createElement('ID', $_granteeVal['id']));
            } else if (isset($_granteeVal['email'])) { // AmazonCustomerByEmail
                $_grantee->setAttribute('xsi:type', 'AmazonCustomerByEmail');
                $_grantee->appendChild($_DOMObject->createElement('EmailAddress', $_granteeVal['email']));
            } elseif ($_granteeVal['type'] == 'Group') { // Group
                $_grantee->setAttribute('xsi:type', 'Group');
                $_grantee->appendChild($_DOMObject->createElement('URI', $_granteeVal['uri']));
            }

            $_grant->appendChild($_grantee);
            $_grant->appendChild($_DOMObject->createElement('Permission', $_granteeVal['permission']));
            $_accessControlList->appendChild($_grant);
        }

        $_accessControlPolicy->appendChild($_accessControlList);
        $_DOMObject->appendChild($_accessControlPolicy);

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_PUT, $_bucketName, $_uri);
        $_SWIFT_AmazonS3RequestObject->SetParameter('acl', null);
        $_SWIFT_AmazonS3RequestObject->SetHeader('Content-Type', 'application/xml');
        $_SWIFT_AmazonS3RequestObject->SetData($_DOMObject->saveXML());
        $_SWIFT_AmazonS3RequestObject->SetSize(strlen($_SWIFT_AmazonS3RequestObject->GetData()));

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'SetAccessControlPolicy(' . $_bucketName . ', ' . $_uri . ')', 200))
        {
            return false;
        }

        return true;
    }


    /**
     * Get object or bucket Access Control Policy
     *
     * Currently this will trigger an error if there is no ACL on an object (will fix soon)
     *
     * @param string $_bucketName Bucket name
     * @param string $_uri Object URI
     * @return mixed | false
     * @throws SWIFT_AmazonS3_Exception If the Class could not be loaded or If Invalid Data is Provided
     */
    public function GetAccessControlPolicy($_bucketName, $_uri = '') {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonS3_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_bucketName)) {
            throw new SWIFT_AmazonS3_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_AmazonS3RequestObject = new SWIFT_AmazonS3Request(SWIFT_AmazonS3Request::ACTION_GET, $_bucketName, $_uri);
        $_SWIFT_AmazonS3RequestObject->SetParameter('acl', null);

        $_ResponseObject = $_SWIFT_AmazonS3RequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'GetAccessControlPolicy(' . $_bucketName . ', ' . $_uri . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_acpContainer = array();

        if (isset($_BodyObject->Owner, $_BodyObject->Owner->ID, $_BodyObject->Owner->DisplayName)) {
            $_acpContainer['owner'] = array(
                'id' => (string) $_BodyObject->Owner->ID, 'name' => (string) $_BodyObject->Owner->DisplayName
            );
        }

        if (isset($_BodyObject->AccessControlList)) {
            $_acpContainer['acl'] = array();

            foreach ($_BodyObject->AccessControlList->Grant as $_GrantObject) {
                if (!isset($_GrantObject->Grantee))
                {
                    continue;
                }

                foreach ($_GrantObject->Grantee as $_GranteeObject) {
                    $Permission = isset($_GrantObject->Permission) ? (string) $_GrantObject->Permission : '';
                    if (isset($_GranteeObject->ID, $_GranteeObject->DisplayName))
                    { // CanonicalUser
                        $_acpContainer['acl'][] = array(
                            'type' => 'CanonicalUser',
                            'id' => (string) $_GranteeObject->ID,
                            'name' => (string) $_GranteeObject->DisplayName,
                            'permission' => $Permission
                        );
                    } else if (isset($_GranteeObject->EmailAddress)) { // AmazonCustomerByEmail
                        $_acpContainer['acl'][] = array(
                            'type' => 'AmazonCustomerByEmail',
                            'email' => (string) $_GranteeObject->EmailAddress,
                            'permission' => $Permission
                        );
                    } else if (isset($_GranteeObject->URI)) { // Group
                        $_acpContainer['acl'][] = array(
                            'type' => 'Group',
                            'uri' => (string) $_GranteeObject->URI,
                            'permission' => $Permission
                        );
                    } else {
                        continue;
                    }
                }
            }
        }

        return $_acpContainer;
    }
}

?>
