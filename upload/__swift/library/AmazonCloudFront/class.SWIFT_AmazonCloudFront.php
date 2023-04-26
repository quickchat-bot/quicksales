<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
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
 * Amazon CloudFront PHP class
 *
 * @link    http://undesigned.org.za/2007/10/22/amazon-s3-php-class
 * @version 0.3.3
 */
class SWIFT_AmazonCloudFront extends SWIFT_Library
{
    const BASE_URL       = 'cloudfront.amazonaws.com';
    const DEFAULT_EXPIRY = 1200; // 20 Minutes
    const API_VERSION = '2009-12-01';

    private $_useSSL = true;

    private $__accessKey; // AWS Access key
    private $__secretKey; // AWS Secret key

    /**
     * Constructor
     *
     * @param string  $_accessKey Access key
     * @param string  $_secretKey Secret key
     * @param boolean $_useSSL    Whether or not to use SSL
     *
     * @throws SWIFT_AmazonCloudFront_Exception If the Class could not be loaded
     */
    public function __construct($_accessKey, $_secretKey, $_useSSL = true)
    {
        parent::__construct();

        if (!$this->SetAccessKey($_accessKey) || !$this->SetSecretKey($_secretKey)) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->SetCanUseSSL($_useSSL);
    }

    /**
     * Set the Access Key
     *
     * @author Varun Shoor
     *
     * @param string $_accessKey Access key
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetAccessKey($_accessKey)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_accessKey)) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_INVALIDDATA);
        }

        $this->__accessKey = $_accessKey;

        return true;
    }

    /**
     * Retrieve the Currently Set Access Key
     *
     * @author Varun Shoor
     * @return mixed "__accessKey" (STRING) on Success, "false" otherwise
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded
     */
    public function GetAccessKey()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->__accessKey;
    }

    /**
     * Set the Secret Key
     *
     * @author Varun Shoor
     *
     * @param string $_secretKey Secret key
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetSecretKey($_secretKey)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_secretKey)) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_INVALIDDATA);
        }

        $this->__secretKey = $_secretKey;

        return true;
    }

    /**
     * Get the Secret Key
     *
     * @author Varun Shoor
     * @return mixed "__secretKey" (STRING) on Success, "false" otherwise
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded
     */
    public function GetSecretKey()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->__secretKey;
    }

    /**
     * Check to see if the user can use SSL
     *
     * @author Varun Shoor
     * @return int "1" on Success, "0" otherwise
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded
     */
    public function CanUseSSL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        return (int) ($this->_useSSL);
    }

    /**
     * Set the Can Use SSL property
     *
     * @author Varun Shoor
     *
     * @param bool $_useSSL The Use SSL Property
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded
     */
    public function SetCanUseSSL($_useSSL)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_useSSL = (int) ($_useSSL);

        $this->_useSSL = $_useSSL;

        return true;
    }

    /**
     * Check the Amazon CloudFront Response to make sure the error codes are right
     *
     * @author Varun Shoor
     *
     * @param SWIFT_AmazonCloudFrontResponse $_SWIFT_AmazonCloudFrontResponseObject The SWIFT_AmazonCloudFrontResponse Object Pointer
     * @param string                         $_callingFunction                      (OPTIONAL) The Name of Function Running this Check
     * @param int                            $_httpCode                             (OPTIONAL) The HTTP Code to Check Against
     * @param bool                           $_endExecution                         (BOOL) Whether to End the Execution if Error Encountered
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    private function CheckResponse(SWIFT_AmazonCloudFrontResponse $_SWIFT_AmazonCloudFrontResponseObject, $_callingFunction = '', $_httpCode = 200, $_endExecution = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_SWIFT_AmazonCloudFrontResponseObject->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT_AmazonCloudFrontResponseObject->GetError() === false && $_SWIFT_AmazonCloudFrontResponseObject->GetHTTPCode() !== $_httpCode) {
            $_SWIFT_AmazonCloudFrontResponseObject->Error($_SWIFT_AmazonCloudFrontResponseObject->GetHTTPCode(), 'Unexpected HTTP status' . SWIFT_CRLF . $_SWIFT_AmazonCloudFrontResponseObject->GetBody());
        }

        if ($_SWIFT_AmazonCloudFrontResponseObject->GetError() !== false) {

            if ($_endExecution) {
                $_errorContainer = $_SWIFT_AmazonCloudFrontResponseObject->GetError();

                throw new SWIFT_AmazonCloudFront_Exception(sprintf("SWIFT_AmazonCloudFront::" . $_callingFunction . ": [%s] %s", $_errorContainer['code'], $_errorContainer['message']));
            }

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
     *
     * @param string $_string String to sign
     *
     * @return string
     */
    public function __GetSignature($_string)
    {
        return 'AWS ' . $this->GetAccessKey() . ':' . base64_encode(hash_hmac('sha1', $_string, $this->GetSecretKey(), true));
    }

    /**
     * Get a DistributionConfig DOMDocument
     *
     * Keys for the $_options parameter:
     * CNAME - _string_|_array_ (Optional) A DNS CNAME to use to map to the CloudFront distribution. If setting more than one, use an indexed array. Supports 1-10 CNAMEs.
     * Comment - _integer_ (Optional) A comment to apply to the distribution. Cannot exceed 128 characters.
     *    OriginAccessIdentity - _string_ (Optional) The Origin Access Identity associated with this distribution. Use the Identity ID, not the CanonicalId.
     *    TrustedSigners - _array_ (Optional) Array of AWS Account numbers who are trusted signers. You must explicitly add "Self" (exactly as shown) to the array if you want your own account to be a trusted signer.
     * Enabled - _bool_ (Optional) Defaults to true. Use this to set Enabled to false.
     *
     * @author   Varun Shoor
     * @internal Used to create XML in createDistribution() and updateDistribution()
     *
     * @param string|false $_bucketName      Origin bucket
     * @param string|false $_callerReference Caller reference
     * @param array  $_options         Array of Options
     *
     * @return string "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded
     */
    protected function __GetCloudFrontDistributionConfigXML($_bucketName, $_callerReference = '0', $_options = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_DOMDocument               = new DOMDocument('1.0', 'UTF-8');
        $_DOMDocument->formatOutput = true;

        $_distributionConfig = $_DOMDocument->createElement('DistributionConfig');
        $_distributionConfig->setAttribute('xmlns', 'http://cloudfront.amazonaws.com/doc/' . self::API_VERSION . '/');

        // Origin
        if ($_bucketName !== false) {
            if (stripos($_bucketName, '.s3.amazonaws.com') !== false) {
                $_distributionConfig->appendChild($_DOMDocument->createElement('Origin', $_bucketName));
            } else {
                $_distributionConfig->appendChild($_DOMDocument->createElement('Origin', $_bucketName . '.s3.amazonaws.com'));
            }
        } else {
            $_distributionConfig->appendChild($_DOMDocument->createElement('Origin', ''));
        }

        if ($_callerReference !== false) {
            $_distributionConfig->appendChild($_DOMDocument->createElement('CallerReference', $_callerReference));
        } else {
            $_distributionConfig->appendChild($_DOMDocument->createElement('CallerReference', ''));
        }

        // CNAME
        if (isset($_options['CNAME'])) {
            if (is_array($_options['CNAME'])) {
                foreach ($_options['CNAME'] as $_cname) {
                    $_distributionConfig->appendChild($_DOMDocument->createElement('CNAME', $_cname));
                }
            } else {
                $_distributionConfig->appendChild($_DOMDocument->createElement('CNAME', $_options['CNAME']));
            }
        }

        // Comment
        if (isset($_options['Comment'])) {
            $_distributionConfig->appendChild($_DOMDocument->createElement('Comment', $_options['Comment']));
        }

        // Enabled
        if (isset($_options['Enabled'])) {
            $_distributionConfig->appendChild($_DOMDocument->createElement('Enabled', $_options['Enabled'] ? 'true' : 'false'));
        } else {
            $_distributionConfig->appendChild($_DOMDocument->createElement('Enabled', 'true'));
        }

        // Logging
        if (isset($_options['Logging'])) {
            if (is_array($_options['Logging'])) {
                $_logging           = $_DOMDocument->createElement('Logging');
                $_loggingBucketName = $_options['Logging']['Bucket'];

                // Origin
                if (stripos($_loggingBucketName, '.s3.amazonaws.com') !== false) {
                    $_logging->appendChild($_DOMDocument->createElement('Bucket', $_loggingBucketName));
                } else {
                    $_logging->appendChild($_DOMDocument->createElement('Bucket', $_loggingBucketName . '.s3.amazonaws.com'));
                }

                $_logging->appendChild($_DOMDocument->createElement('Prefix', $_options['Logging']['Prefix']));
                $_distributionConfig->appendChild($_logging);
            }
        }

        // Origin Access Identity
        if (isset($_options['OriginAccessIdentity'])) {
            $_distributionConfig->appendChild($_DOMDocument->createElement('OriginAccessIdentity', 'origin-access-identity/cloudfront/' . $_options['OriginAccessIdentity']));
        }

        // Trusted Signers
        if (isset($_options['TrustedSigners'])) {
            if (is_array($_options['TrustedSigners'])) {
                $_trustedSigners = $_DOMDocument->createElement('TrustedSigners');
                foreach ($_options['TrustedSigners'] as $_signer) {
                    if ($_signer == 'Self') {
                        $_trustedSigners->appendChild($_DOMDocument->createElement('Self'));
                    } else {
                        $_trustedSigners->appendChild($_DOMDocument->createElement('AwsAccountNumber', $_signer));
                    }
                }

                $_distributionConfig->appendChild($_trustedSigners);
            }
        }

        $_DOMDocument->appendChild($_distributionConfig);

        return $_DOMDocument->saveXML();
    }

    /**
     * Parse a CloudFront distribution config
     *
     * @author   Varun Shoor
     * @internal Used to parse the CloudFront DistributionConfig node to an array
     *
     * @param object $_BodyObject DOMNode
     *
     * @return array
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded
     */
    protected function __ParseCloudFrontDistributionConfig($_BodyObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_distributionContainer = array();

        if (isset($_BodyObject->Id, $_BodyObject->Status, $_BodyObject->LastModifiedTime, $_BodyObject->DomainName)) {
            $_distributionContainer['Id']               = (string) $_BodyObject->Id;
            $_distributionContainer['Status']           = (string) $_BodyObject->Status;
            $_distributionContainer['LastModifiedTime'] = strtotime((string) $_BodyObject->LastModifiedTime);
            $_distributionContainer['DomainName']       = (string) $_BodyObject->DomainName;
        }

        if (isset($_BodyObject->CallerReference)) {
            $_distributionContainer['CallerReference'] = (string) $_BodyObject->CallerReference;
        }

        if (isset($_BodyObject->Comment)) {
            $_distributionContainer['Comment'] = (string) $_BodyObject->Comment;
        }

        if (isset($_BodyObject->OriginAccessIdentity)) {
            $_distributionContainer['OriginAccessIdentity'] = (string) $_BodyObject->OriginAccessIdentity;
        }

        if (isset($_BodyObject->Enabled, $_BodyObject->Origin)) {
            $_distributionContainer['Origin']  = (string) $_BodyObject->Origin;
            $_distributionContainer['Enabled'] = (string) $_BodyObject->Enabled == 'true' ? true : false;
        } elseif (isset($_BodyObject->DistributionConfig)) {
            $_distributionContainer = array_merge($_distributionContainer, self::__ParseCloudFrontDistributionConfig($_BodyObject->DistributionConfig));
        }

        if (isset($_BodyObject->TrustedSigners)) {
            $_distributionContainer['TrustedSigners'] = array();
            if (isset($_BodyObject->TrustedSigners->Self)) {
                $_distributionContainer['TrustedSigners']['Self'] = true;
            }

            if (isset($_BodyObject->TrustedSigners->AwsAccountNumber)) {
                $_distributionContainer['TrustedSigners']['AwsAccountNumber'] = array();
                foreach ($_BodyObject->TrustedSigners->AwsAccountNumber as $_AwsAccountNumber) {
                    $_distributionContainer['TrustedSigners']['AwsAccountNumber'][] = (string) $_AwsAccountNumber;
                }
            }
        }

        if (isset($_BodyObject->Logging, $_BodyObject->Logging->Bucket, $_BodyObject->Logging->Prefix)) {
            $_distributionContainer['Logging']           = array();
            $_distributionContainer['Logging']['Bucket'] = (string) $_BodyObject->Logging->Bucket;
            $_distributionContainer['Logging']['Prefix'] = (string) $_BodyObject->Logging->Prefix;
        }

        if (isset($_BodyObject->CNAME)) {
            $_distributionContainer['CNAME'] = array();
            foreach ($_BodyObject->CNAME as $_cname) {
                $_distributionContainer['CNAME'][] = (string) $_cname;
            }
        }

        return $_distributionContainer;
    }

    /**
     * Get a list of Distributions
     *
     * @param int|bool $_marker   (OPTIONAL) Specify the pagination marker
     * @param int|bool $_maxItems (OPTIONAL) Maximum items to return in list
     *
     * @return array | false
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded
     */
    public function ListDistributions($_marker = false, $_maxItems = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_AmazonCloudFrontRequestObject = new SWIFT_AmazonCloudFrontRequest(SWIFT_AmazonCloudFrontRequest::ACTION_GET, self::API_VERSION . '/distribution');

        $_ResponseObject = $_SWIFT_AmazonCloudFrontRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ListDistributions(' . $_marker . ', ' . $_maxItems . ')', 200)) {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_results    = array();

        if (isset($_BodyObject->Marker, $_BodyObject->MaxItems, $_BodyObject->IsTruncated)) {
            $_results['Marker']      = (string) $_BodyObject->Marker;
            $_results['MaxItems']    = (int) $_BodyObject->MaxItems;
            $_results['IsTruncated'] = (string) $_BodyObject->IsTruncated == 'true' ? true : false;

            if (isset($_BodyObject->NextMarker)) {
                $_results['NextMarker'] = (int) $_BodyObject->NextMarker;
            }
        }

        $_results['Summary'] = array();
        foreach ($_BodyObject->DistributionSummary as $_SummaryContainer) {
            $_results['Summary'][(string) $_SummaryContainer->Id] = self::__ParseCloudFrontDistributionConfig($_SummaryContainer);
        }

        return $_results;
    }

    /**
     * Create a Distribution
     *
     * @param string $_bucketName Bucket name
     * @param bool   $_isEnabled  (OPTIONAL) Whether this distribution is enabled by default
     * @param array  $_cnameList  (OPTIONAL) The CNAME List Container Array
     * @param string $_comment    (OPTIONAL) The comment to be associated with this distribution
     *
     * @return mixed
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded
     */
    public function CreateDistribution($_bucketName, $_isEnabled = true, $_cnameList = array(), $_comment = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_AmazonCloudFrontRequestObject = new SWIFT_AmazonCloudFrontRequest(SWIFT_AmazonCloudFrontRequest::ACTION_POST, self::API_VERSION . '/distribution');

        $_options            = array();
        $_options['Enabled'] = $_isEnabled;
        $_options['CNAME']   = $_cnameList;
        $_options['Comment'] = $_comment;

        $_SWIFT_AmazonCloudFrontRequestObject->SetData($this->__GetCloudFrontDistributionConfigXML($_bucketName, (string) microtime(true), $_options));
        $_SWIFT_AmazonCloudFrontRequestObject->SetHeader('Content-Type', 'application/xml');

        $_ResponseObject = $_SWIFT_AmazonCloudFrontRequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'CreateDistribution(' . $_bucketName . ', ' . (int) ($_isEnabled) . ', ' . var_export($_cnameList, true) . ', ' . $_comment . ')', 201)) {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_results = $this->__ParseCloudFrontDistributionConfig($_BodyObject);

        return $_results;
    }

    /**
     * Delete a Distribution
     *
     * @param array $_distributionContainer The Valid Distribution Container
     *
     * @return bool
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteDistribution($_distributionContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($_distributionContainer['Id']) || !isset($_distributionContainer['Hash'])) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_INVALIDDATA);
        }

        // Is it enabled? If yes, we need to disable it and update the ETag value according to spec.
        if ($_distributionContainer['Enabled'] == '1') {
            $_disableDistributionContainer            = $_distributionContainer;
            $_disableDistributionContainer['Enabled'] = false;

            $_updatedDistributionContainer    = $this->UpdateDistributionConfig($_disableDistributionContainer);
            $_distributionContainer['Hash']   = $_updatedDistributionContainer['Hash'];
            $_distributionContainer['Status'] = 'InProgress';
        }

        // Dont move on till the distribution status is Deployed
        if ($_distributionContainer['Status'] == 'InProgress') {
            $_distributionActive = false;

            while ($_distributionActive == false) {
                $_activeDistributionContainer = $this->GetDistribution($_distributionContainer['Id']);
                if ($_activeDistributionContainer['Status'] == 'Deployed') {
                    $_distributionActive = true;
                }

                sleep(10);
            }
        }

        $_SWIFT_AmazonCloudFrontRequestObject = new SWIFT_AmazonCloudFrontRequest(SWIFT_AmazonCloudFrontRequest::ACTION_DELETE, self::API_VERSION . '/distribution/' . $_distributionContainer['Id']);
        $_SWIFT_AmazonCloudFrontRequestObject->SetHeader('If-Match', $_distributionContainer['Hash']);

        $_ResponseObject = $_SWIFT_AmazonCloudFrontRequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'DeleteDistribution(' . var_export($_distributionContainer, true) . ')', 204)) {
            return false;
        }

        return true;
    }

    /**
     * Get a Distribution Info
     *
     * @param string $_distributionID The Distribution ID
     *
     * @return array|bool
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetDistribution($_distributionID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_distributionID)) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonCloudFrontRequestObject = new SWIFT_AmazonCloudFrontRequest(SWIFT_AmazonCloudFrontRequest::ACTION_GET, self::API_VERSION . '/distribution/' . $_distributionID);

        $_ResponseObject = $_SWIFT_AmazonCloudFrontRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'GetDistribution(' . $_distributionID . ')', 200)) {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_results         = $this->__ParseCloudFrontDistributionConfig($_BodyObject);
        $_results['Hash'] = $_ResponseObject->GetHeader('hash');

        return $_results;
    }

    /**
     * Get a Distribution Config
     *
     * @param string $_distributionID The Distribution ID
     *
     * @return array|bool
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetDistributionConfig($_distributionID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_distributionID)) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonCloudFrontRequestObject = new SWIFT_AmazonCloudFrontRequest(SWIFT_AmazonCloudFrontRequest::ACTION_GET, self::API_VERSION . '/distribution/' . $_distributionID . '/config');

        $_ResponseObject = $_SWIFT_AmazonCloudFrontRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'GetDistributionConfig(' . $_distributionID . ')', 200)) {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_results         = $this->__ParseCloudFrontDistributionConfig($_BodyObject);
        $_results['Hash'] = $_ResponseObject->GetHeader('hash');

        return $_results;
    }

    /**
     * Update a Distribution Config
     *
     * @param array $_distributionContainer The Valid Distribution Container
     *
     * @return mixed
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateDistributionConfig($_distributionContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($_distributionContainer['Id']) || !isset($_distributionContainer['Hash'])) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonCloudFrontRequestObject = new SWIFT_AmazonCloudFrontRequest(SWIFT_AmazonCloudFrontRequest::ACTION_PUT, self::API_VERSION . '/distribution/' . $_distributionContainer['Id'] . '/config');

        $_SWIFT_AmazonCloudFrontRequestObject->SetData($this->__GetCloudFrontDistributionConfigXML($_distributionContainer['Origin'], $_distributionContainer['CallerReference'], $_distributionContainer));
        $_SWIFT_AmazonCloudFrontRequestObject->SetHeader('If-Match', $_distributionContainer['Hash']);
        $_SWIFT_AmazonCloudFrontRequestObject->SetHeader('Content-Type', 'application/xml');

        $_ResponseObject = $_SWIFT_AmazonCloudFrontRequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'UpdateDistribution(' . var_export($_distributionContainer, true) . ')', 200)) {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_results         = $this->__ParseCloudFrontDistributionConfig($_BodyObject);
        $_results['Hash'] = $_ResponseObject->GetHeader('hash');

        return $_results;
    }

    /**
     * Parse a CloudFront Origin Access identity config
     *
     * @author   Varun Shoor
     * @internal Used to parse the CloudFront OriginAccessIdentityConfig node to an array
     *
     * @param object $_BodyObject DOMNode
     *
     * @return array
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded
     */
    protected function __ParseCloudFrontOriginAccessIdentityConfig($_BodyObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_oaiContainer = array();

        if (isset($_BodyObject->Id, $_BodyObject->S3CanonicalUserId)) {
            $_oaiContainer['Id']                = (string) $_BodyObject->Id;
            $_oaiContainer['S3CanonicalUserId'] = (string) $_BodyObject->S3CanonicalUserId;
        }

        if (isset($_BodyObject->Comment)) {
            $_oaiContainer['Comment'] = (string) $_BodyObject->Comment;
        }

        if (isset($_BodyObject->CallerReference)) {
            $_oaiContainer['CallerReference'] = (string) $_BodyObject->CallerReference;
        }

        return $_oaiContainer;
    }

    /**
     * Parse a CloudFront Origin Access identity
     *
     * @author   Varun Shoor
     * @internal Used to parse the CloudFront OriginAccessIdentity node to an array
     *
     * @param object $_BodyObject DOMNode
     *
     * @return array
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded
     */
    protected function __ParseCloudFrontOriginAccessIdentity($_BodyObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_oaiContainer = array();

        if (isset($_BodyObject->Id, $_BodyObject->S3CanonicalUserId)) {
            $_oaiContainer['Id']                = (string) $_BodyObject->Id;
            $_oaiContainer['S3CanonicalUserId'] = (string) $_BodyObject->S3CanonicalUserId;
        }

        $_CloudFrontOriginAccessIdentityConfig = isset($_BodyObject->CloudFrontOriginAccessIdentityConfig)?
                                                 $_BodyObject->CloudFrontOriginAccessIdentityConfig : new stdClass();

        // Merge the config details into this base array
        $_oaiContainer = array_merge($_oaiContainer, $this->__ParseCloudFrontOriginAccessIdentityConfig($_CloudFrontOriginAccessIdentityConfig));

        return $_oaiContainer;
    }

    /**
     * Get a OriginAccessIdentityConfig DOMDocument
     *
     * @author Varun Shoor
     *
     * @param string|false $_callerReference Caller reference
     * @param string $_comment         The Comments Associated with this OAI
     *
     * @return string "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded
     */
    protected function __GetCloudFrontOriginAccessIdentityConfigXML($_callerReference, $_comment = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_DOMDocument               = new DOMDocument('1.0', 'UTF-8');
        $_DOMDocument->formatOutput = true;

        $_distributionConfig = $_DOMDocument->createElement('CloudFrontOriginAccessIdentityConfig');
        $_distributionConfig->setAttribute('xmlns', 'http://cloudfront.amazonaws.com/doc/' . self::API_VERSION . '/');

        if ($_callerReference !== false) {
            $_distributionConfig->appendChild($_DOMDocument->createElement('CallerReference', $_callerReference));
        } else {
            $_distributionConfig->appendChild($_DOMDocument->createElement('CallerReference', ''));
        }

        // Comment
        if (!empty($_comment)) {
            $_distributionConfig->appendChild($_DOMDocument->createElement('Comment', $_comment));
        }

        $_DOMDocument->appendChild($_distributionConfig);

        return $_DOMDocument->saveXML();
    }

    /**
     * Get a list of Origin Access Identity Lists
     *
     * @param int|bool $_marker   (OPTIONAL) Specify the pagination marker
     * @param int|bool $_maxItems (OPTIONAL) Maximum items to return in list
     *
     * @return array|bool
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded
     */
    public function ListOriginAccessIdentity($_marker = false, $_maxItems = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_AmazonCloudFrontRequestObject = new SWIFT_AmazonCloudFrontRequest(SWIFT_AmazonCloudFrontRequest::ACTION_GET, self::API_VERSION . '/origin-access-identity/cloudfront');

        $_ResponseObject = $_SWIFT_AmazonCloudFrontRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ListOriginAccessIdentity(' . $_marker . ', ' . $_maxItems . ')', 200)) {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_results    = array();

        if (isset($_BodyObject->Marker, $_BodyObject->MaxItems, $_BodyObject->IsTruncated)) {
            $_results['Marker']      = (string) $_BodyObject->Marker;
            $_results['MaxItems']    = (int) $_BodyObject->MaxItems;
            $_results['IsTruncated'] = (string) $_BodyObject->IsTruncated == 'true' ? true : false;

            if (isset($_BodyObject->NextMarker)) {
                $_results['NextMarker'] = (int) $_BodyObject->NextMarker;
            }
        }

        $_results['Summary'] = array();
        foreach ($_BodyObject->CloudFrontOriginAccessIdentitySummary as $_SummaryContainer) {
            $_results['Summary'][(string) $_SummaryContainer->Id] = self::__ParseCloudFrontOriginAccessIdentityConfig($_SummaryContainer);
        }

        return $_results;
    }

    /**
     * Create a new Origin Access Identity
     *
     * @param string $_comment (OPTIONAL) The comment to be associated with this Origin Access identity
     *
     * @return mixed
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded
     */
    public function CreateOriginAccessIdentity($_comment = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_AmazonCloudFrontRequestObject = new SWIFT_AmazonCloudFrontRequest(SWIFT_AmazonCloudFrontRequest::ACTION_POST, self::API_VERSION . '/origin-access-identity/cloudfront');

        $_SWIFT_AmazonCloudFrontRequestObject->SetData($this->__GetCloudFrontOriginAccessIdentityConfigXML((string) microtime(true), $_comment));
        $_SWIFT_AmazonCloudFrontRequestObject->SetHeader('Content-Type', 'application/xml');

        $_ResponseObject = $_SWIFT_AmazonCloudFrontRequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'CreateOriginAccessIdentity(' . $_comment . ')', 201)) {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_results = $this->__ParseCloudFrontOriginAccessIdentity($_BodyObject);

        return $_results;
    }

    /**
     * Get a Origin Access Identity Info
     *
     * @param string $_originAccessIdentityID The Origin Access Identity ID
     *
     * @return array|bool
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetOriginAccessIdentity($_originAccessIdentityID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_originAccessIdentityID)) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonCloudFrontRequestObject = new SWIFT_AmazonCloudFrontRequest(SWIFT_AmazonCloudFrontRequest::ACTION_GET, self::API_VERSION . '/origin-access-identity/cloudfront/' . $_originAccessIdentityID);

        $_ResponseObject = $_SWIFT_AmazonCloudFrontRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'GetOriginAccessIdentity(' . $_originAccessIdentityID . ')', 200)) {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_results         = $this->__ParseCloudFrontOriginAccessIdentity($_BodyObject);
        $_results['Hash'] = $_ResponseObject->GetHeader('hash');

        return $_results;
    }

    /**
     * Delete a Origin Access Identity
     *
     * @param array $_oaiContainer The Valid Origin Access Identity Container
     *
     * @return bool
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteOriginAccessIdentity($_oaiContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!_is_array($_oaiContainer) || !isset($_oaiContainer['Id']) || !isset($_oaiContainer['Hash'])) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_INVALIDDATA);
        }

        // We first fetch ALL distributions and see if this origin access identity is associated with any of em.. if we find any associations, we nuke em
        $_distributionsContainer = $this->ListDistributions();
        if (isset($_distributionsContainer['Summary'])) {
            foreach ($_distributionsContainer['Summary'] as $_distributionID => $_distributionContainerList) {
                $_distributionContainer = $this->GetDistribution($_distributionID);
                if (isset($_distributionContainer['OriginAccessIdentity']) && $_distributionContainer['OriginAccessIdentity'] == 'origin-access-identity/cloudfront/' . $_oaiContainer['Id']) {
                    // Seems like we found a distribution linked to this OAI, we need to unassociate it
                    $_updateDistributionContainer = $_distributionContainer;
                    unset($_updateDistributionContainer['OriginAccessIdentity']);

                    $this->UpdateDistributionConfig($_updateDistributionContainer);
                }
            }
        }

        // By now we would have unassociated the OAI with all linked distributions, list all distributions again and wait till InProgress turns to deployed for each one of em
        $_distributionsContainer = $this->ListDistributions();
        if (isset($_distributionsContainer['Summary'])) {
            foreach ($_distributionsContainer['Summary'] as $_distributionContainer) {
                // Dont move on till the distribution status is Deployed
                if ($_distributionContainer['Status'] == 'InProgress') {
                    $_distributionActive = false;

                    while ($_distributionActive == false) {
                        $_activeDistributionContainer = $this->GetDistribution($_distributionContainer['Id']);
                        if ($_activeDistributionContainer['Status'] == 'Deployed') {
                            $_distributionActive = true;
                        }

                        sleep(10);
                    }
                }
            }
        }

        // Now all distributions would have turned to Deployed (if there were any associations) and we can safely delete the OAI
        $_SWIFT_AmazonCloudFrontRequestObject = new SWIFT_AmazonCloudFrontRequest(SWIFT_AmazonCloudFrontRequest::ACTION_DELETE, self::API_VERSION . '/origin-access-identity/cloudfront/' . $_oaiContainer['Id']);
        $_SWIFT_AmazonCloudFrontRequestObject->SetHeader('If-Match', $_oaiContainer['Hash']);

        $_ResponseObject = $_SWIFT_AmazonCloudFrontRequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'DeleteOriginAccessIdentity(' . var_export($_oaiContainer, true) . ')', 204)) {
            return false;
        }

        return true;
    }

    /**
     * Get a Origin Access Identity Config
     *
     * @param string $_oaiID The OAI ID
     *
     * @return array|bool
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetOriginAccessIdentityConfig($_oaiID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_oaiID)) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonCloudFrontRequestObject = new SWIFT_AmazonCloudFrontRequest(SWIFT_AmazonCloudFrontRequest::ACTION_GET, self::API_VERSION . '/origin-access-identity/cloudfront/' . $_oaiID . '/config');

        $_ResponseObject = $_SWIFT_AmazonCloudFrontRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'GetOriginAccessIdentityConfig(' . $_oaiID . ')', 200)) {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_results         = $this->__ParseCloudFrontOriginAccessIdentityConfig($_BodyObject);
        $_results['Hash'] = $_ResponseObject->GetHeader('hash');

        return $_results;
    }

    /**
     * Update a Origin Access Identity Config
     *
     * @param array $_oaiContainer The Valid Origin Access Identity Container
     *
     * @return mixed
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateOriginAccessIdentityConfig($_oaiContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($_oaiContainer['Id']) || !isset($_oaiContainer['Hash'])) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonCloudFrontRequestObject = new SWIFT_AmazonCloudFrontRequest(SWIFT_AmazonCloudFrontRequest::ACTION_PUT, self::API_VERSION . '/origin-access-identity/cloudfront/' . $_oaiContainer['Id'] . '/config');

        $_SWIFT_AmazonCloudFrontRequestObject->SetData($this->__GetCloudFrontOriginAccessIdentityConfigXML($_oaiContainer['CallerReference'], $_oaiContainer['Comment']));
        $_SWIFT_AmazonCloudFrontRequestObject->SetHeader('If-Match', $_oaiContainer['Hash']);
        $_SWIFT_AmazonCloudFrontRequestObject->SetHeader('Content-Type', 'application/xml');

        $_ResponseObject = $_SWIFT_AmazonCloudFrontRequestObject->GetResponse($this);

        if (!$this->CheckResponse($_ResponseObject, 'UpdateOriginAccessIdentityConfig(' . var_export($_oaiContainer, true) . ')', 200)) {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_results         = $this->__ParseCloudFrontOriginAccessIdentityConfig($_BodyObject);
        $_results['Hash'] = $_ResponseObject->GetHeader('hash');

        return $_results;
    }

    /**
     * Create a canned signed url for a resource
     *
     * @author Varun Shoor
     *
     * @param string $_resourceURL    The Resource URL
     * @param mixed  $_expiryTimeline The UNIX Epoch Expiry Timeline
     * @param string $_keyPairID      The Key Pair ID
     * @param string $_privateKey     The Private Key
     *
     * @return string "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded
     */
    public function CreateCannedSignedURL($_resourceURL, $_expiryTimeline, $_keyPairID, $_privateKey)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_signature    = '';
        $_cannedPolicy = '{"Statement":[{"Resource":"' . $_resourceURL . '","Condition":{"DateLessThan":{"AWS:EpochTime":' . $_expiryTimeline . '}}}]}';

        $_privateKeyID = openssl_get_privatekey($_privateKey);
        openssl_sign($_cannedPolicy, $_signature, $_privateKeyID);
        openssl_free_key($_privateKeyID);

        $_finalSignature = self::URLSafeEncode($_signature);

        // Now we need to process the URL
        $_urlContainer = parse_url($_resourceURL);

        // If it has query parameters then we append signature + other details using &
        $_finalParameters = 'Expires=' . (int) ($_expiryTimeline) . '&Signature=' . $_finalSignature . '&Key-Pair-Id=' . $_keyPairID;

        $_finalURL = $_resourceURL . '?' . $_finalParameters;
        if (isset($_urlContainer['query']) && !empty($_urlContainer['query'])) {
            $_finalURL = $_resourceURL . '&' . $_finalParameters;
        }

        return $_finalURL;
    }

    /**
     * Create a custom signed url for a resource
     *
     * @author Varun Shoor
     *
     * @param string      $_resourceURL     The Resource URL
     * @param string      $_keyPairID       The Key Pair ID
     * @param string      $_privateKey      The Private Key
     * @param int|bool    $_dateLessThan    (OPTIONAL) The UNIX Epoch less than threshold date
     * @param int|bool    $_dateGreaterThan (OPTIONAL) The UNIX Epoch based greater than threshold date
     * @param string|bool $_ipAddress       (OPTIONAL) The IP Address to restrict the request to
     *
     * @return string The Final URL
     * @throws SWIFT_AmazonCloudFront_Exception If the Class is not Loaded or If Condition Processing Fials
     */
    public function CreateCustomSignedURL($_resourceURL, $_keyPairID, $_privateKey, $_dateLessThan = false, $_dateGreaterThan = false, $_ipAddress = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_AmazonCloudFront_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_conditionContainer = array();
        if ($_dateLessThan !== false) {
            $_conditionContainer[] = '"DateLessThan":{"AWS:EpochTime":' . (int) ($_dateLessThan) . '}';
        }

        if ($_dateGreaterThan !== false) {
            $_conditionContainer[] = '"DateGreaterThan":{"AWS:EpochTime":' . (int) ($_dateGreaterThan) . '}';
        }

        if ($_ipAddress !== false) {
            $_conditionContainer[] = '"IpAddress":{"AWS:SourceIp":' . $_ipAddress . '}';
        }

        if (!count($_conditionContainer)) {
            throw new SWIFT_AmazonCloudFront_Exception('Need atleast one valid condition to generate a custom policy signed url');
        }

        $_signature    = '';
        $_cannedPolicy = '{"Statement":[{"Resource":"' . $_resourceURL . '","Condition":{' . implode(',', $_conditionContainer) . '}}]}';

        $_privateKeyID = openssl_get_privatekey($_privateKey);
        openssl_sign($_cannedPolicy, $_signature, $_privateKeyID);
        openssl_free_key($_privateKeyID);

        $_finalSignature = self::URLSafeEncode($_signature);

        // Now we need to process the URL
        $_urlContainer = parse_url($_resourceURL);
        // If it has query parameters then we append signature + other details using &
        $_finalParameters = 'Policy=' . self::URLSafeEncode($_cannedPolicy) . '&Signature=' . $_finalSignature . '&Key-Pair-Id=' . $_keyPairID;

        $_finalURL = $_resourceURL . '?' . $_finalParameters;
        if (isset($_urlContainer['query']) && !empty($_urlContainer['query'])) {
            $_finalURL = $_resourceURL . '&' . $_finalParameters;
        }

        return $_finalURL;
    }

    /**
     * URL Safe Encode for Amazon Cloud Front
     *
     * @author Varun Shoor
     *
     * @param string $_data The Data to Process
     *
     * @return string The Processed Data
     */
    protected static function URLSafeEncode($_data)
    {
        return str_replace('+', '-', str_replace('=', '_', str_replace('/', '~', base64_encode($_data))));
    }
}
