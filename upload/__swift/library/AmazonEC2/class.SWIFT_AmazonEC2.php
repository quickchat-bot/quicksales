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

/**
 * Amazon EC2 PHP class
 *
 * @author Varun Shoor
 */
class SWIFT_AmazonEC2 extends SWIFT_Amazon {
    private $_useSSL = true;

    private $__accessKey; // AWS Access key
    private $__secretKey; // AWS Secret key
    private $_verifyHost = 2;
    private $_verifyPeer = 1;

    // Core Constants
    private $_baseURL = 'ec2.amazonaws.com';

    const INSTANCE_M1SMALL = 'm1.small';
    const INSTANCE_M1LARGE = 'm1.large';
    const INSTANCE_M1XLARGE = 'm1.xlarge';
    const INSTANCE_M2XLARGE = 'm2.xlarge';
    const INSTANCE_M22XLARGE = 'm2.2xlarge';
    const INSTANCE_M24XLARGE = 'm2.4xlarge';
    const INSTANCE_C1MEDIUM = 'c1.medium';
    const INSTANCE_C1XLARGE = 'c1.xlarge';
    const INSTANCE_CC14XLARGE = 'cc1.4xlarge';
    const INSTANCE_T1MICRO = 't1.micro';

    const REGION_USEAST = 'ec2.us-east-1.amazonaws.com';
    const REGION_USWEST = 'ec2.us-west-1.amazonaws.com';
    const REGION_EUWEST = 'ec2.eu-west-1.amazonaws.com';
    const REGION_ASIAPAC = 'ec2.ap-southeast-1.amazonaws.com';

    const ARC_X86 = 'i386';
    const ARC_X86_64 = 'x86_64';

    /**
     * Constructor, used if you're not calling the class statically
     *
     * @param string $_accessKey Access key
     * @param string $_secretKey Secret key
     * @param boolean $_useSSL (OPTIONAL) Whether or not to use SSL
     * @param mixed $_region (OPTIONAL) The Regione
     * @throws SWIFT_AmazonEC2_Exception If the Class could not be loaded
     */
    public function __construct($_accessKey, $_secretKey, $_useSSL = true, $_region = self::REGION_USEAST) {
        parent::__construct();

        if (!$this->SetAccessKey($_accessKey) || !$this->SetSecretKey($_secretKey) || !$this->SetRegion($_region))
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_INVALIDDATA);
        }

        $this->SetCanUseSSL($_useSSL);
    }

    /**
     * Set the Access Key
     *
     * @author Varun Shoor
     * @param string $_accessKey Access key
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetAccessKey($_accessKey)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function GetAccessKey()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetSecretKey($_secretKey)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function GetSecretKey()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->__secretKey;
    }

    /**
     * Check to see if the user can use SSL
     *
     * @author Varun Shoor
     * @return int "1" on Success, "0" otherwise
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function CanUseSSL()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        }

        return (int) ($this->_useSSL);
    }

    /**
     * Set the Can Use SSL property
     *
     * @author Varun Shoor
     * @param bool $_useSSL The Use SSL Property
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function SetCanUseSSL($_useSSL)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_useSSL = (int) ($_useSSL);

        $this->_useSSL = $_useSSL;

        return true;
    }

    /**
     * Set the Verify Host Value (Only for SSL)
     *
     * @author Varun Shoor
     * @param bool $_verifyHost The Verify Host Value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetVerifyHost($_verifyHost)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->_verifyHost = (int) ($_verifyHost);

        return true;
    }

    /**
     * Retrieve the currently set verify host value
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetVerifyHost()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_verifyHost;
    }

    /**
     * Get the currently set verify peer value
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetVerifyPeer()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_verifyPeer;
    }

    /**
     * Set the Verify Peer value
     *
     * @author Varun Shoor
     * @param bool $_verifyPeer The Verify Peer Value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetVerifyPeer($_verifyPeer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->_verifyPeer = (int) ($_verifyPeer);

        return true;
    }

    /**
     * Check the Amazon EC2 Response to make sure the error codes are right
     *
     * @author Varun Shoor
     * @param SWIFT_AmazonEC2Response $_SWIFT_AmazonEC2ResponseObject The SWIFT_AmazonEC2Response Object Pointer
     * @param string $_callingFunction (OPTIONAL) The Name of Function Running this Check
     * @param int $_httpCode (OPTIONAL) The HTTP Code to Check Against
     * @param bool $_endExecution (BOOL) Whether to End the Execution if Error Encountered
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    private function CheckResponse(SWIFT_AmazonEC2Response $_SWIFT_AmazonEC2ResponseObject, $_callingFunction = '', $_httpCode = 200, $_endExecution = true)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!$_SWIFT_AmazonEC2ResponseObject->GetIsClassLoaded()) {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if ($_SWIFT_AmazonEC2ResponseObject->GetError() === false && $_SWIFT_AmazonEC2ResponseObject->GetHTTPCode() != $_httpCode)
        {
            $_SWIFT_AmazonEC2ResponseObject->Error($_SWIFT_AmazonEC2ResponseObject->GetHTTPCode(), 'Unexpected HTTP status (' . $_httpCode . ')');
        }

        if ($_SWIFT_AmazonEC2ResponseObject->GetError() !== false) {

            if ($_endExecution)
            {
                $_errorContainer = $_SWIFT_AmazonEC2ResponseObject->GetError();
                $_ErrorObject = $_SWIFT_AmazonEC2ResponseObject->GetBodyObject();
                $_awsCode = '0';
                $_awsMessage = $_errorMessageFinal = '';

                if (isset($_ErrorObject->Errors)) {
                    foreach ($_ErrorObject->Errors->Error as $_Error) {
                        if (isset($_Error->Code))
                        {
                            $_awsCode = (string) $_Error->Code;
                        }

                        if (isset($_Error->Message))
                        {
                            $_awsMessage = (string) $_Error->Message;
                        }

                        $_errorMessageFinal .= sprintf("SWIFT_AmazonEC2::". $_callingFunction .": [%s] %s" . "\n" . $_awsCode . ': ' . $_awsMessage, $_errorContainer['code'], $_errorContainer['message']) . SWIFT_CRLF;
                    }
                }

                throw new SWIFT_AmazonEC2_Exception($_errorMessageFinal);

                return false;
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
     * @param string $_string String to sign
     * @return string
     */
    public function __GetSignature($_string) {
        return base64_encode(hash_hmac('sha256', $_string, $this->GetSecretKey(), true));
    }

    /**
     * Check to see if its a valid Instance Type
     *
     * @author Varun Shoor
     * @param mixed $_instanceType The Instance Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidInstanceType($_instanceType)
    {
        if ($_instanceType == self::INSTANCE_M1SMALL || $_instanceType == self::INSTANCE_M1LARGE || $_instanceType == self::INSTANCE_M1XLARGE ||
                $_instanceType == self::INSTANCE_M22XLARGE || $_instanceType == self::INSTANCE_M24XLARGE || $_instanceType == self::INSTANCE_C1MEDIUM ||
                $_instanceType == self::INSTANCE_C1XLARGE || $_instanceType == self::INSTANCE_CC14XLARGE || $_instanceType == self::INSTANCE_T1MICRO ||
                $_instanceType == self::INSTANCE_M2XLARGE)
        {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid region
     *
     * @author Varun Shoor
     * @param mixed $_region The Region
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidRegion($_region)
    {
        if ($_region == self::REGION_ASIAPAC || $_region == self::REGION_EUWEST || $_region == self::REGION_USEAST || $_region == self::REGION_USWEST)
        {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the Base URL
     *
     * @author Varun Shoor
     * @return mixed The Base URL
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetBaseURL()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_baseURL;
    }

    /**
     * Set the Region
     *
     * @author Varun Shoor
     * @param mixed $_region The Region
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetRegion($_region)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->_baseURL = $_region;

        return true;
    }

    /**
     * Describes Regions that are currently available to the account.
     *
     * IMPORTANT: Does not support filters yet
     *
     * @author Varun Shoor
     * @param array $_regionNameList (OPTIONAL) Filter by List of Region Names
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function DescribeRegions($_regionNameList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DescribeRegions');

        if ($_regionNameList !== null && _is_array($_regionNameList))
        {
            $_index = 1;
            foreach ($_regionNameList as $_regionName) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('RegionName.' . $_index, $_regionName);

                $_index++;
            }
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeRegions(' . print_r($_regionNameList, true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->regionInfo->item)) {
            return false;
        }

        if (isset($_BodyObject->regionInfo->item)) {
            $_index = 0;

            foreach ($_BodyObject->regionInfo->item as $_ItemObject) {
                $_resultsContainer[$_index]['name'] = (string) $_ItemObject->regionName;
                $_resultsContainer[$_index]['endpoint'] = (string) $_ItemObject->regionEndpoint;

                $_index++;
            }
        }

        return $_resultsContainer;
    }

    /**
     * Displays Availability Zones that are currently available to the account. The results include zones only for the Region you're currently using.
     *
     * Note Availability Zones are not the same across accounts. The Availability Zone us-east-1a for account A is not necessarily the same as us-east-1a for account B. Zone assignments are mapped independently for each account.
     *
     * IMPORTANT: Does not support filters yet
     *
     * @author Varun Shoor
     * @param array $_availabilityZoneNameList (OPTIONAL) Filter by List of Availability Zones
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function DescribeAvailabilityZones($_availabilityZoneNameList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DescribeAvailabilityZones');

        if ($_availabilityZoneNameList !== null && _is_array($_availabilityZoneNameList))
        {
            $_index = 1;
            foreach ($_availabilityZoneNameList as $_availabilityZoneName) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('ZoneName.' . $_index, $_availabilityZoneName);

                $_index++;
            }
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeAvailabilityZones(' . print_r($_availabilityZoneNameList, true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->availabilityZoneInfo->item)) {
            return false;
        }

        if (isset($_BodyObject->availabilityZoneInfo->item)) {
            $_index = 0;

            foreach ($_BodyObject->availabilityZoneInfo->item as $_ItemObject) {
                $_resultsContainer[$_index]['name'] = (string) $_ItemObject->zoneName;
                $_resultsContainer[$_index]['state'] = (string) $_ItemObject->zoneState;
//                $_resultsContainer[$_index]['region'] = (string) $_ItemObject->regionName;

                $_index++;
            }
        }

        return $_resultsContainer;
    }

    /**
     * Lists elastic IP addresses assigned to your account or provides information about a specific address.
     *
     * IMPORTANT: Does not support filters yet
     *
     * @author Varun Shoor
     * @param array $_publicIPAddressList (OPTIONAL) Filter by List of Public IP Addresses
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function DescribeAddresses($_publicIPAddressList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DescribeAddresses');

        if ($_publicIPAddressList !== null && _is_array($_publicIPAddressList))
        {
            $_index = 1;
            foreach ($_publicIPAddressList as $_publicIPAddress) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('PublicIp.' . $_index, $_publicIPAddress);

                $_index++;
            }
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeAddresses(' . print_r($_publicIPAddressList, true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->addressesSet->item)) {
            return false;
        }

        if (isset($_BodyObject->addressesSet->item)) {
            $_index = 0;

            foreach ($_BodyObject->addressesSet->item as $_ItemObject) {
                $_resultsContainer[$_index]['ip'] = (string) $_ItemObject->publicIp;
                $_resultsContainer[$_index]['instanceid'] = (string) $_ItemObject->instanceId;

                $_index++;
            }
        }

        return $_resultsContainer;
    }

    /**
     * Acquires an elastic IP address for use with your account.
     *
     * @author Varun Shoor
     * @return string | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function AllocateAddresses() {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'AllocateAddress');

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'AllocateAddress()', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->publicIp)) {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_publicIPAddress = (string) $_BodyObject->publicIp;

        return $_publicIPAddress;
    }

    /**
     * Releases an elastic IP address associated with your account.
     *
     * If you run this operation on an elastic IP address that is already released, the address might be assigned to another account which will cause Amazon EC2 to return an error.
     * Note: Releasing an IP address automatically disassociates it from any instance with which it is associated.
     *         To disassociate an IP address without releasing it, use the DisassociateAddress operation.
     *
     * Important: After releasing an elastic IP address, it is released to the IP address pool and might no longer be available to your account.
     *         Make sure to update your DNS records and any servers or devices that communicate with the address.
     *
     * @author Varun Shoor
     * @param string $_publicIPAddress The Public IP Address to Release from Account
     * @return bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ReleaseAddress($_publicIPAddress) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_publicIPAddress)) {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'ReleaseAddress');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('PublicIp', $_publicIPAddress);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ReleaseAddress(' . $_publicIPAddress . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Associates an elastic IP address with an instance. If the IP address is currently assigned to another instance, the IP address is assigned to the new instance.
     * This is an idempotent operation. If you enter it more than once, Amazon EC2 does not return an error.
     *
     * @author Varun Shoor
     * @param string $_publicIPAddress The Public IP Address to Release from Account
     * @param string $_instanceID The Instance to Associate the IP Address With
     * @return bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function AssociateAddress($_publicIPAddress, $_instanceID) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_publicIPAddress) || empty($_instanceID)) {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'AssociateAddress');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('PublicIp', $_publicIPAddress);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId', $_instanceID);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'AssociateAddress(' . $_publicIPAddress . ', ' . $_instanceID . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Disassociates the specified elastic IP address from the instance to which it is assigned. This is an idempotent operation.
     * If you enter it more than once, Amazon EC2 does not return an error.
     *
     * @author Varun Shoor
     * @param string $_publicIPAddress The Public IP Address to Release from Account
     * @return bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DisassociateAddress($_publicIPAddress) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_publicIPAddress)) {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DisassociateAddress');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('PublicIp', $_publicIPAddress);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DisassociateAddress(' . $_publicIPAddress . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Creates a new 2048-bit RSA key pair with the specified name. The public key is stored by Amazon EC2 and the private key is displayed on the console.
     *  The private key is returned as an unencrypted PEM encoded PKCS#8 private key. If a key with the specified name already exists, Amazon EC2 returns an error.
     *
     * @author Varun Shoor
     * @param string $_keyName A unique name for the key pair
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateKeyPair($_keyName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_keyName)) {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'CreateKeyPair');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('KeyName', $_keyName);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateKeyPair(' . $_keyName . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->keyName) || !isset($_BodyObject->keyFingerprint) || !isset($_BodyObject->keyMaterial)) {
            return false;
        }

        $_releaseResult = isset($_BodyObject->return) ? (string) $_BodyObject->return : '';
        $_resultsContainer = array();

        $_resultsContainer['name'] = (string) $_BodyObject->keyName;
        $_resultsContainer['fingerprint'] = (string) $_BodyObject->keyFingerprint;
        $_resultsContainer['material'] = (string) $_BodyObject->keyMaterial;

        return $_resultsContainer;
    }

    /**
     * Returns information about key pairs available to you. If you specify key pairs, information about those key pairs is returned. Otherwise, information for all registered key pairs is returned.
     *
     * IMPORTANT: Does not support filters yet
     *
     * @author Varun Shoor
     * @param array $_keyNameList (OPTIONAL) Filter by List of Key Names
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function DescribeKeyPairs($_keyNameList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DescribeKeyPairs');

        if ($_keyNameList !== null && _is_array($_keyNameList))
        {
            $_index = 1;
            foreach ($_keyNameList as $_keyName) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('KeyName.' . $_index, $_keyName);

                $_index++;
            }
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeKeyPairs(' . print_r($_keyNameList, true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->keySet->item)) {
            return false;
        }

        if (isset($_BodyObject->keySet->item)) {
            $_index = 0;

            foreach ($_BodyObject->keySet->item as $_ItemObject) {
                $_resultsContainer[$_index]['name'] = (string) $_ItemObject->keyName;
                $_resultsContainer[$_index]['fingerprint'] = (string) $_ItemObject->keyFingerprint;

                $_index++;
            }
        }

        return $_resultsContainer;
    }

    /**
     * Deletes the specified key pair, by removing the public key from Amazon EC2. You must own the key pair.
     *
     * @author Varun Shoor
     * @param string $_keyName The Key Name to Delete from Account
     * @return bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteKeyPair($_keyName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_keyName)) {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DeleteKeyPair');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('KeyName', $_keyName);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteKeyPair(' . $_keyName . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Returns information about AMIs, AKIs, and ARIs.
     * Images available to you include public images, private images that you own, and private images owned by other AWS accounts but for which you have explicit launch permissions.
     *
     * Launch permissions fall into three categories:
     * Launch Permission    Description
     * public                The owner of the AMI granted launch permissions for the AMI to the all group. All AWS accounts have launch permissions for these AMIs.
     * explicit                The owner of the AMI granted launch permissions to a specific AWS account.
     * implicit                An AWS account has implicit launch permissions for all the AMIs it owns.
     *
     * The list of AMIs returned can be modified by specifying AMI IDs, AMI owners, or AWS accounts with launch permissions.
     * If no options are specified, Amazon EC2 returns all AMIs for which you have launch permissions.
     *
     * If you specify one or more AMI IDs, only AMIs that have the specified IDs are returned. If you specify an invalid AMI ID, a fault is returned.
     * If you specify an AMI ID for which you do not have access, it will not be included in the returned results.
     *
     * If you specify one or more AMI owners, only AMIs from the specified owners and for which you have access are returned.
     * The results can include the account IDs of the specified owners, amazon for AMIs owned by Amazon, or self for AMIs that you own.
     *
     * If you specify a list of executable users, only AMIs with launch permissions for those users are returned.
     * You can specify account IDs (if you own the AMI(s)), self for AMIs for which you own or have explicit permissions, or all for public AMIs.
     *
     * @author Varun Shoor
     * @param array $_executableByList (OPTIONAL) Returns AMIs for which the specified user ID has explicit launch permissions. The user ID can be an AWS account ID, self to return AMIs for which the sender of the request has explicit launch permissions, or all to return AMIs with public launch permissions.
     * @param array $_imageIDList (OPTIONAL) AMI IDs to describe.
     * @param array $_ownerList (OPTIONAL) Returns AMIs owned by the specified owner. Multiple owner values can be specified. The IDs amazon and self can be used to include AMIs owned by Amazon or AMIs owned by you, respectively
     * @return mixed
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DescribeImages($_executableByList = null, $_imageIDList = null, $_ownerList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        }

        // If no arguments are supplied we default to owner == self && amazon
        if ($_executableByList === null && $_imageIDList === null && $_ownerList === null) {
            $_ownerList = array('self', 'amazon');
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DescribeImages');

        if ($_executableByList !== null && _is_array($_executableByList)) {
            $_index = 1;
            foreach ($_executableByList as $_executableBy) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('ExecutableBy.' . $_index, $_executableBy);

                $_index++;
            }
        }

        if ($_imageIDList !== null && _is_array($_imageIDList)) {
            $_index = 1;
            foreach ($_imageIDList as $_imageID) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('ImageId.' . $_index, $_imageID);

                $_index++;
            }
        }

        if ($_ownerList !== null && _is_array($_ownerList)) {
            $_index = 1;
            foreach ($_ownerList as $_owner) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('Owner.' . $_index, $_owner);

                $_index++;
            }
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeImages()', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->imagesSet->item)) {
            return false;
        }

        $_resultsContainer = array();

        $_index = 0;

        foreach ($_BodyObject->imagesSet->item as $_ItemObject) {
            $_resultsContainer[$_index] = $this->ConvertXMLObjectToArray($_ItemObject);

            $_index++;
        }

        return $_resultsContainer;
    }

    /**
     * Creates an AMI that uses an Amazon EBS root device from a "running" or "stopped" instance.
     *
     * AMIs that use an Amazon EBS root device boot faster than AMIs that use instance stores. They can be up to 1 TiB in size, use storage that persists on instance failure, and can be stopped and started.
     *
     * @author Varun Shoor
     * @param string $_instanceID The Instance ID to Create Image From
     * @param string $_imageName The Image Name
     * @param string $_description (OPTIONAL) The description of the AMI that was provided during image creation
     * @param bool $_noReboot (OPTIONAL) By default this property is set to false, which means Amazon EC2 attempts to cleanly shut down the instance before image creation and reboots the instance afterwards. When set to true, Amazon EC2 does not shut down the instance before creating the image. When this option is used, file system integrity on the created image cannot be guaranteed.
     * @return string | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateImage($_instanceID, $_imageName, $_description = null, $_noReboot = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_instanceID) || empty($_imageName)) {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'CreateImage');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId', $_instanceID);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('Name', $_imageName);

        if ($_description !== null && $_description !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('Description', $_description);
        }

        if ($_noReboot !== null) {
            $_finalNoReboot = (int) ($_noReboot);

            $_SWIFT_AmazonEC2RequestObject->SetParameter('NoReboot', IIF($_finalNoReboot == '1', 'true', 'false'));
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateImage(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->imageId)) {
            return false;
        }

        $_imageID = (string)  $_BodyObject->imageId;

        return $_imageID;
    }

    /**
     * Deregisters the specified AMI. Once deregistered, the AMI cannot be used to launch new instances.
     *
     * @author Varun Shoor
     * @param string $_imageID The AMI Image ID to Deregister from Account
     * @return bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeregisterImage($_imageID) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_imageID)) {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DeregisterImage');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('ImageId', $_imageID);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeregisterImage(' . $_imageID . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Registers an AMI with Amazon EC2. Images must be registered before they can be launched. To launch instances, use the RunInstances operation.
     *
     * Each AMI is associated with an unique ID which is provided by the Amazon EC2 service through this operation. If needed, you can deregister an AMI at any time.
     *
     * Note
     * For EBS-backed instances, the CreateImage operation enables you to create and register an image in a single request. You can use the RegisterImage operation to register a snapshot of an instance backed by Amazon EBS.
     * Amazon EBS snapshots are not guaranteed to be bootable. For information on creating AMIs backed by Amazon EBS, go to the Amazon Elastic Compute Cloud Developer Guide or Amazon Elastic Compute Cloud User Guide.
     * Any modifications to an AMI backed by Amazon S3 invalidates this registration. If you make changes to an image, deregister the previous image and register the new image.
     *
     * @author Varun Shoor
     * @param array $_blockDeviceMapping (OPTIONAL) The Block Device Mapping Array. array('devicename', 'virtualname', array('ebs' => 'snapshotid', 'volumesize', 'nodevice', deleteontermination'));
     * @param string $_imageLocation (OPTIONAL) Full path to your AMI manifest in Amazon S3 storage.
     * @param string $_imageName (OPTIONAL) A name for your AMI
     * @param string $_description (OPTIONAL) The description of the AMI.
     * @param mixed $_architecture (OPTIONAL) ARC_X86 | ARC_X86_64
     * @param string $_kernelID (OPTIONAL) The ID of the kernel to select.
     * @param string $_ramDiskID (OPTIONAL) The ID of the RAM disk to select. Some kernels require additional drivers at launch. Check the kernel requirements for information on whether you need to specify a RAM disk. To find kernel requirements, refer to the Resource Center and search for the kernel ID.
     * @param string $_rootDeviceName (OPTIONAL) The root device name (ex: /dev/sda1)
     * @return string | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RegisterImage($_blockDeviceMapping = null, $_imageLocation = null, $_imageName = null, $_description = null, $_architecture = null, $_kernelID = null, $_ramDiskID = null,
            $_rootDeviceName = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if ($_architecture !== null && $_architecture !== self::ARC_X86 && $_architecture != self::ARC_X86_64) {
            throw new SWIFT_Exception('Invalid Architecture');
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'RegisterImage');

        if ($_imageLocation !== null && $_imageLocation !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('ImageLocation', $_imageLocation);
        }

        if ($_imageName !== null && $_imageName !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('Name', $_imageName);
        }

        if ($_description !== null && $_description !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('Description', $_description);
        }

        if ($_architecture !== null && $_architecture !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('Architecture', $_architecture);
        }

        if ($_kernelID !== null && $_kernelID !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('KernelId', $_kernelID);
        }

        if ($_ramDiskID !== null && $_ramDiskID !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('RamdiskId', $_ramDiskID);
        }

        if ($_rootDeviceName !== null && $_rootDeviceName !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('RootDeviceName', $_rootDeviceName);
        }

        if ($_blockDeviceMapping !== null && _is_array($_blockDeviceMapping)) {
            $_index = 1;
            foreach ($_blockDeviceMapping as $_BlockDeviceMap) {
                if (isset($_BlockDeviceMap['devicename'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.DeviceName', $_BlockDeviceMap['devicename']);
                }

                if (isset($_BlockDeviceMap['virtualname'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.VirtualName', $_BlockDeviceMap['virtualname']);
                }

                if (isset($_BlockDeviceMap['ebs']['snapshotid'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.Ebs.SnapshotId', $_BlockDeviceMap['ebs']['snapshotid']);
                }

                if (isset($_BlockDeviceMap['ebs']['volumesize'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.Ebs.VolumeSize', $_BlockDeviceMap['ebs']['volumesize']);
                }

                if (isset($_BlockDeviceMap['ebs']['nodevice'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.Ebs.NoDevice', $_BlockDeviceMap['ebs']['nodevice']);
                }

                if (isset($_BlockDeviceMap['ebs']['deleteontermination'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.Ebs.DeleteOnTermination', $_BlockDeviceMap['ebs']['deleteontermination']);
                }

                $_index++;
            }
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'RegisterImage(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->imageId)) {
            return false;
        }

        $_imageID = (string)  $_BodyObject->imageId;

        return $_imageID;
    }

    /**
     * Returns information about an attribute of an AMI. You can get information about only one attribute per call.
     *
     * @author Varun Shoor
     * @param string $_imageID The Image ID
     * @param string $_attribute The Attribute to Fetch Information On. Options: description | kernel | ramdisk | launchPermission | productCodes | blockDeviceMapping
     * @return mixed
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DescribeImageAttribute($_imageID, $_attribute) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_imageID) || empty($_attribute)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DescribeImageAttribute');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('ImageId', $_imageID);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('Attribute', $_attribute);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeImageAttribute(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->imagesId)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject);

        return $_resultsContainer;
    }

    /**
     * Resets an attribute of an AMI to its default value.
     *
     * Note
     * The productCodes attribute cannot be reset.
     *
     * @author Varun Shoor
     * @param string $_imageID The Image ID
     * @param string $_attribute The Attribute to Fetch Information On. Options: description | kernel | ramdisk | launchPermission | blockDeviceMapping
     * @return bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ResetImageAttribute($_imageID, $_attribute) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_imageID) || empty($_attribute)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'ResetImageAttribute');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('ImageId', $_imageID);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('Attribute', $_attribute);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ResetImageAttribute(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Modifies an attribute of an AMI.
     *
     * @author Varun Shoor
     * @param string $_imageID The Image ID
     * @param array $_userIDList The ID of the AWS account you want to give permission to.
     * @param array $_groupList Name of the group to give permission to. Use this if you want to make an AMI public or private. The only valid value is all.
     * @param array $_productCodeList (OPTIONAL) Product code. Once you add a product code to an AMI, it can't be removed.
     * @param string $_attribute (OPTIONAL) The Attribute to Modify
     * @param string $_operationType (OPTIONAL) Specifies the operation to perform on the attribute. Valid Values: add | remove
     * @return bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ModifyImageAttribute($_imageID, $_userIDList, $_groupList, $_productCodeList = null, $_attribute = null, $_operationType = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_imageID) || !_is_array($_userIDList) || _is_array($_groupList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'ModifyImageAttribute');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('ImageId', $_imageID);

        $_index = 1;
        foreach ($_userIDList as $_userID) {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('UserId.' . $_index, $_userID);

            $_index++;
        }

        $_index = 1;
        foreach ($_groupList as $_group) {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('Group.' . $_index, $_group);

            $_index++;
        }

        if ($_productCodeList !== null && _is_array($_productCodeList)) {
            $_index = 1;
            foreach ($_productCodeList as $_productCode) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('ProductCode.' . $_index, $_productCode);

                $_index++;
            }
        }

        $_SWIFT_AmazonEC2RequestObject->SetParameter('Attribute', $_attribute);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('OperationType', $_operationType);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ModifyImageAttribute(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Launches a specified number of instances of an AMI for which you have permissions.
     *
     * If Amazon EC2 cannot launch the minimum number of instances you request, no instances will be launched. If there is insufficient capacity to launch the maximum number of instances you request, Amazon EC2 launches the minimum number specified and allocates the remaining available instances using round robin.
     *
     * Note
     * Every instance is launched in a security group (created using the CreateSecurityGroup operation). If no security group is specified in the RunInstances request, the "default" security group will be used.
     * For Linux instances, you can provide an optional key pair ID in the launch request (created using the CreateKeyPair operation). The instances will have access to the public key at boot. You can use this key to provide secure access to an instance of an image on a per-instance basis. Amazon EC2 public images use this feature to provide secure access without passwords.
     *
     * Important
     * Launching public images without a key pair ID will leave them inaccessible.
     * The public key material is made available to the instance at boot time by placing it in the openssh_id.pub file on a logical device that is exposed to the instance as /dev/sda2 (the instance store). The format of this file is suitable for use as an entry within ~/.ssh/authorized_keys (the OpenSSH format). This can be done at boot (e.g., as part of rc.local) allowing for secure access without passwords.
     *
     * Optional user data can be provided in the launch request. All instances that collectively comprise the launch request have access to this data. For more information, go to the Amazon Elastic Compute Cloud Developer Guide or Amazon Elastic Compute Cloud User Guide.
     *
     * Note
     * If any of the AMIs have a product code attached for which the user has not subscribed, the RunInstances call will fail.
     *
     * @author Varun Shoor
     * @param string $_imageID The AMI ID
     * @param int $_minCount (OPTIONAL) The Minimum number of instances to launch
     * @param int $_maxCount (OPTIONAL) The Maximum number of of instances to launch
     * @param string $_keyName (OPTIONAL) The Name of Key Pair
     * @param array $_securityGroupList (OPTIONAL) The List of Security Groups to associate instance with
     * @param mixed $_instanceType (OPTIONAL) The Instance Type
     * @param string $_kernelID (OPTIONAL) The ID of the kernel with which to launch the instance
     * @param string $_ramDiskID (OPTIONAL) The ID of the RAM disk to select. Some kernels require additional drivers at launch. Check the kernel requirements for information on whether you need to specify a RAM disk. To find kernel requirements, refer to the Resource Center and search for the kernel ID.
     * @param bool $_isMonitoringEnabled (OPTIONAL) Enables monitoring for the instance
     * @param array $_blockDeviceMapping (OPTIONAL) The Block Device Mapping Details
     * @param string $_subnetID (OPTIONAL) If you're using Amazon Virtual Private Cloud, this specifies the ID of the subnet you want to launch the instance into.
     * @param string $_privateIPAddress (OPTIONAL) If you're using Amazon Virtual Private Cloud, you can optionally use this parameter to assign the instance a specific available IP address from the subnet (e.g., 10.0.0.25).
     * @param bool $_disableAPITermination (OPTIONAL) Specifies whether the instance can be terminated using the APIs. You must modify this attribute using the ec2-modify-instance command before you can terminate any "locked" instances via the APIs.
     * @param string $_instanceInitiatedShutdownBehavior (OPTIONAL) Determines whether the instance stops or terminates on instance-initiated shutdown.
     * @param string $_placementAvailabilityZone (OPTIONAL) Specifies the Availability Zone you want to launch the instance into.
     * @param string $_placementGroupName (OPTIONAL) Specifies the name of an existing placement group you want to launch the instance into (for cluster compute instances).
     * @param string $_clientToken (OPTIONAL) Unique, case-sensitive identifier you provide to ensure idempotency of the request. For more information, go to How to Ensure Idempotency in the Amazon Elastic Compute Cloud Developer Guide.
     * @return string | false | array
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RunInstances($_imageID, $_minCount = 1, $_maxCount = 1, $_keyName = null, $_securityGroupList = null, $_instanceType = null, $_kernelID = null, $_ramDiskID = null,
            $_isMonitoringEnabled = null, $_blockDeviceMapping = null, $_subnetID = null, $_privateIPAddress = null, $_disableAPITermination = null, $_instanceInitiatedShutdownBehavior = null,
            $_placementAvailabilityZone = null, $_placementGroupName = null, $_clientToken = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_imageID) || empty($_maxCount) || empty($_minCount)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if ($_instanceType !== null && !self::IsValidInstanceType($_instanceType)) {
            throw new SWIFT_Exception('Invalid Instance Type');
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'RunInstances');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('ImageId', $_imageID);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('MinCount', ($_minCount));
        $_SWIFT_AmazonEC2RequestObject->SetParameter('MaxCount', ($_maxCount));

        if ($_keyName !== null && $_keyName !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('KeyName', $_keyName);
        }

        if ($_securityGroupList !== null && _is_array($_securityGroupList)) {
            $_index = 1;
            foreach ($_securityGroupList as $_securityGroup) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('SecurityGroup.' . $_index, $_securityGroup);

                $_index++;
            }
        }

        if ($_instanceType !== null && $_instanceType !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceType', $_instanceType);
        }

        if ($_kernelID !== null && $_kernelID !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('KernelId', $_kernelID);
        }

        if ($_ramDiskID !== null && $_ramDiskID !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('RamdiskId', $_ramDiskID);
        }

        if ($_blockDeviceMapping !== null && _is_array($_blockDeviceMapping)) {
            $_index = 1;
            foreach ($_blockDeviceMapping as $_BlockDeviceMap) {
                if (isset($_BlockDeviceMap['devicename'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.DeviceName', $_BlockDeviceMap['devicename']);
                }

                if (isset($_BlockDeviceMap['virtualname'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.VirtualName', $_BlockDeviceMap['virtualname']);
                }

                if (isset($_BlockDeviceMap['ebs']['snapshotid'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.Ebs.SnapshotId', $_BlockDeviceMap['ebs']['snapshotid']);
                }

                if (isset($_BlockDeviceMap['ebs']['volumesize'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.Ebs.VolumeSize', $_BlockDeviceMap['ebs']['volumesize']);
                }

                if (isset($_BlockDeviceMap['ebs']['nodevice'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.Ebs.NoDevice', $_BlockDeviceMap['ebs']['nodevice']);
                }

                if (isset($_BlockDeviceMap['ebs']['deleteontermination'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.Ebs.DeleteOnTermination', $_BlockDeviceMap['ebs']['deleteontermination']);
                }

                $_index++;
            }
        }

        if ($_isMonitoringEnabled !== null && is_bool($_isMonitoringEnabled)) {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('Monitoring.Enabled', IIF($_isMonitoringEnabled == true, 'true', 'false'));
        }

        if ($_subnetID !== null && $_subnetID !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('SubnetId', $_subnetID);
        }

        if ($_privateIPAddress !== null && $_privateIPAddress !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('PrivateIpAddress', $_privateIPAddress);
        }

        if ($_disableAPITermination !== null && is_bool($_disableAPITermination)) {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('DisableApiTermination', IIF($_disableAPITermination == true, 'true', 'false'));
        }

        if ($_instanceInitiatedShutdownBehavior !== null && $_instanceInitiatedShutdownBehavior !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceInitiatedShutdownBehavior', $_instanceInitiatedShutdownBehavior);
        }

        if ($_placementAvailabilityZone !== null && $_placementAvailabilityZone !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('Placement.AvailabilityZone', $_placementAvailabilityZone);
        }

        if ($_placementGroupName !== null && $_placementGroupName !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('Placement.GroupName', $_placementGroupName);
        }

        if ($_clientToken !== null && $_clientToken !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('ClientToken', $_clientToken);
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'RunInstances(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_resultsContainer = array();

        if (!isset($_BodyObject->instancesSet)) {
            return false;
        }

        if (isset($_BodyObject->instancesSet->item)) {
            foreach ($_BodyObject->instancesSet->item as $_InstanceItemObject) {
                $_resultsContainer[] = $this->ConvertXMLObjectToArray($_InstanceItemObject);
            }
        }


        return $_resultsContainer;
    }

    /**
     * Returns information about instances that you own.
     *
     * If you specify one or more instance IDs, Amazon EC2 returns information for those instances. If you do not specify instance IDs, Amazon EC2 returns information for all relevant instances. If you specify an invalid instance ID, a fault is returned. If you specify an instance that you do not own, it will not be included in the returned results.
     *
     * Recently terminated instances might appear in the returned results.This interval is usually less than one hour.
     *
     * IMPORTANT: Does not support filters yet
     *
     * @author Varun Shoor
     * @param array $_instanceIDList (OPTIONAL) Filter by List of Instance IDs
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function DescribeInstances($_instanceIDList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DescribeInstances');

        if ($_instanceIDList !== null && _is_array($_instanceIDList))
        {
            $_index = 1;
            foreach ($_instanceIDList as $_instanceID) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId.' . $_index, $_instanceID);

                $_index++;
            }
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeInstances(' . print_r($_instanceIDList, true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->reservationSet->item)) {
            return false;
        }

        if (isset($_BodyObject->reservationSet->item)) {
            $_index = 0;
            foreach ($_BodyObject->reservationSet->item as $_ReservationItemObject) {
                if (isset($_ReservationItemObject->instancesSet->item)) {
                    foreach ($_ReservationItemObject->instancesSet->item as $_InstanceItemObject) {
                        $_resultsContainer[$_index] = $this->ConvertXMLObjectToArray($_InstanceItemObject);
                        $_index++;
                    }
                }

            }
        }

        return $_resultsContainer;
    }

    /**
     * Enables monitoring for a running instance. For more information, refer to the Amazon CloudWatch Developer Guide.
     *
     * @author Varun Shoor
     * @param array $_instanceIDList (OPTIONAL) List of Instance IDs to Enable Monitoring for
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function MonitorInstances($_instanceIDList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'MonitorInstances');

        if ($_instanceIDList !== null && _is_array($_instanceIDList))
        {
            $_index = 1;
            foreach ($_instanceIDList as $_instanceID) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId.' . $_index, $_instanceID);

                $_index++;
            }
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'MonitorInstances(' . print_r($_instanceIDList, true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->instancesSet->item)) {
            return false;
        }

        if (isset($_BodyObject->instancesSet->item)) {
            $_index = 0;

            foreach ($_BodyObject->instancesSet->item as $_InstanceItemObject) {
                $_resultsContainer[$_index] = $this->ConvertXMLObjectToArray($_InstanceItemObject);

                $_index++;
            }
        }

        return $_resultsContainer;
    }

    /**
     * Disables monitoring for a running instance. For more information, refer to the Amazon CloudWatch Developer Guide.
     *
     * @author Varun Shoor
     * @param array $_instanceIDList (OPTIONAL) List of Instance IDs to Disable Monitoring for
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function UnmonitorInstances($_instanceIDList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'UnmonitorInstances');

        if ($_instanceIDList !== null && _is_array($_instanceIDList))
        {
            $_index = 1;
            foreach ($_instanceIDList as $_instanceID) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId.' . $_index, $_instanceID);

                $_index++;
            }
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'UnmonitorInstances(' . print_r($_instanceIDList, true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->instancesSet->item)) {
            return false;
        }

        if (isset($_BodyObject->instancesSet->item)) {
            $_index = 0;

            foreach ($_BodyObject->instancesSet->item as $_InstanceItemObject) {
                $_resultsContainer[$_index] = $this->ConvertXMLObjectToArray($_InstanceItemObject);

                $_index++;
            }
        }

        return $_resultsContainer;
    }

    /**
     * Requests a reboot of one or more instances. This operation is asynchronous; it only queues a request to reboot the specified instance(s).
     * The operation will succeed if the instances are valid and belong to you. Requests to reboot terminated instances are ignored.
     *
     * @author Varun Shoor
     * @param array $_instanceIDList (OPTIONAL) List of Instance IDs to Reboot
     * @return array | bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function RebootInstances($_instanceIDList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'RebootInstances');

        if ($_instanceIDList !== null && _is_array($_instanceIDList))
        {
            $_index = 1;
            foreach ($_instanceIDList as $_instanceID) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId.' . $_index, $_instanceID);

                $_index++;
            }
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'RebootInstances(' . print_r($_instanceIDList, true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Stops an instance that uses an Amazon EBS volume as its root device.
     *
     * Important
     * Although Spot Instances can use Amazon EBS-backed AMIs, they don't support Stop/Start. In other words, you can't stop and start Spot Instances launched from an AMI with an Amazon EBS root device.
     * Instances that use Amazon EBS volumes as their root devices can be quickly stopped and started. When an instance is stopped, the compute resources are released and you are not billed for hourly instance usage. However, your root partition Amazon EBS volume remains, continues to persist your data, and you are charged for Amazon EBS volume usage. You can restart your instance at any time.
     *
     * Note
     * Before stopping an instance, make sure it is in a state from which it can be restarted. Stopping an instance does not preserve data stored in RAM.
     * Performing this operation on an instance that uses an instance store as its root device returns an error.
     *
     * @author Varun Shoor
     * @param array $_instanceIDList (OPTIONAL) List of Instance IDs to Stop
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function StopInstances($_instanceIDList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'StopInstances');

        if ($_instanceIDList !== null && _is_array($_instanceIDList))
        {
            $_index = 1;
            foreach ($_instanceIDList as $_instanceID) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId.' . $_index, $_instanceID);

                $_index++;
            }
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'StopInstances(' . print_r($_instanceIDList, true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->instancesSet->item)) {
            return false;
        }

        if (isset($_BodyObject->instancesSet->item)) {
            $_index = 0;

            foreach ($_BodyObject->instancesSet->item as $_InstanceItemObject) {
                $_resultsContainer[$_index] = $this->ConvertXMLObjectToArray($_InstanceItemObject);

                $_index++;
            }
        }

        return $_resultsContainer;
    }

    /**
     * Starts an instance that uses an Amazon EBS volume as its root device.
     *
     * Instances that use Amazon EBS volumes as their root devices can be quickly stopped and started. When an instance is stopped, the compute resources are released and you are not billed for hourly instance usage. However, your root partition Amazon EBS volume remains, continues to persist your data, and you are charged for Amazon EBS volume usage. You can restart your instance at any time.
     *
     * Note
     * Before stopping an instance, make sure it is in a state from which it can be restarted. Stopping an instance does not preserve data stored in RAM.
     * Performing this operation on an instance that uses an instance store as its root device returns an error.
     *
     * @author Varun Shoor
     * @param array $_instanceIDList (OPTIONAL) List of Instance IDs to Start
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function StartInstances($_instanceIDList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'StartInstances');

        if ($_instanceIDList !== null && _is_array($_instanceIDList))
        {
            $_index = 1;
            foreach ($_instanceIDList as $_instanceID) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId.' . $_index, $_instanceID);

                $_index++;
            }
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'StartInstances(' . print_r($_instanceIDList, true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->instancesSet->item)) {
            return false;
        }

        if (isset($_BodyObject->instancesSet->item)) {
            $_index = 0;

            foreach ($_BodyObject->instancesSet->item as $_InstanceItemObject) {
                $_resultsContainer[$_index] = $this->ConvertXMLObjectToArray($_InstanceItemObject);

                $_index++;
            }
        }

        return $_resultsContainer;
    }

    /**
     * Shuts down one or more instances. This operation is idempotent; if you terminate an instance more than once, each call will succeed.
     *
     * Terminated instances will remain visible after termination (approximately one hour).
     *
     * Note
     * By default, Amazon EC2 deletes all Amazon EBS volumes that were attached when the instance launched. Amazon EBS volumes attached after instance launch continue running.
     *
     * @author Varun Shoor
     * @param string $_instanceID The Instance ID to Terminate
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function TerminateInstances($_instanceID) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_instanceID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'TerminateInstances');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId', $_instanceID);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'TerminateInstances(' . $_instanceID . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->instancesSet->item)) {
            return false;
        }

        if (isset($_BodyObject->instancesSet->item)) {
            $_index = 0;

            foreach ($_BodyObject->instancesSet->item as $_InstanceItemObject) {
                $_resultsContainer[$_index] = $this->ConvertXMLObjectToArray($_InstanceItemObject);

                $_index++;
            }
        }

        return $_resultsContainer;
    }

    /**
     * Retrieves console output for the specified instance.
     *
     * Instance console output is buffered and posted shortly after instance boot, reboot, and termination.
     *
     * Amazon EC2 preserves the most recent 64 KB output which will be available for at least one hour after the most recent post.
     *
     * @author Varun Shoor
     * @param string $_instanceID The Instance ID
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetConsoleOutput($_instanceID) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_instanceID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'GetConsoleOutput');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId', $_instanceID);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'GetConsoleOutput(' . $_instanceID . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->instanceId)) {
            return false;
        }

        $_resultsContainer['instanceid'] = (string) $_BodyObject->instanceId;
        $_resultsContainer['timestamp'] = isset($_BodyObject->timestamp) ? (string) $_BodyObject->timestamp : '';
        $_resultsContainer['output'] = isset($_BodyObject->output) ? (string) $_BodyObject->output : '';

        $_resultsContainer['output'] = base64_decode($_resultsContainer['output']);

        return $_resultsContainer;
    }

    /**
     * Retrieves the encrypted administrator password for the instances running Windows.
     *
     * Note
     * The Windows password is only generated the first time an AMI is launched. It is not generated for rebundled AMIs or after the password is changed on an instance.
     *
     * The password is encrypted using the key pair that you provided.
     *
     * @author Varun Shoor
     * @param string $_instanceID The Instance ID
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetPasswordData($_instanceID) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_instanceID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'GetPasswordData');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId', $_instanceID);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'GetPasswordData(' . $_instanceID . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->instanceId)) {
            return false;
        }

        $_resultsContainer['instanceid'] = (string) $_BodyObject->instanceId;
        $_resultsContainer['timestamp'] = isset($_BodyObject->timestamp) ? (string) $_BodyObject->timestamp : '';
        $_resultsContainer['password'] = isset($_BodyObject->passwordData) ? (string) $_BodyObject->passwordData : '';

        return $_resultsContainer;
    }

    /**
     * Returns information about an attribute of an instance. You can get information about only one attribute per call.
     *
     * @author Varun Shoor
     * @param string $_instanceID The Instance ID to Terminate
     * @param string $_attributeName The Attribute Name. Options: instanceType | kernel | ramdisk | userData | disableApiTermination | instanceInitiatedShutdownBehavior | rootDeviceName | blockDeviceMapping
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DescribeInstanceAttribute($_instanceID, $_attributeName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_instanceID) || empty($_attributeName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DescribeInstanceAttribute');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId', $_instanceID);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('Attribute', $_attributeName);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeInstanceAttribute(' . $_instanceID . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->instanceId)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject);

        return $_resultsContainer;
    }

    /**
     * Modifies an attribute of an instance.
     *
     * Note
     * If you want to add ephemeral storage to an Amazon EBS-backed instance, you can't do it by modifying the instance's block device mapping attribute.
     * Instead, you must add the ephemeral storage at the time you launch the instance.
     * For more information, go to Overriding the AMI's Block Device Mapping in the Amazon Elastic Compute Cloud User Guide, or
     * to Adding Default Local Instance Storage in the Amazon Elastic Compute Cloud User Guide.
     *
     * @author Varun Shoor
     * @param string $_instanceID The Instance ID
     * @param string $_attributeName The Attribute Name. Options: instanceType | kernel | ramdisk | userData | disableApiTermination | instanceInitiatedShutdownBehavior | rootDeviceName | blockDeviceMapping
     * @return array | bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ModifyInstanceAttribute($_instanceID, $_attributeName, $_attributeValue) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_instanceID) || empty($_attributeName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'ModifyInstanceAttribute');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId', $_instanceID);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('Attribute', $_attributeName);

        if ($_attributeName == 'blockDeviceMapping') {
            if (!_is_array($_attributeValue)) {
                throw new SWIFT_Exception('Block Device Mapping Expects Value to be an Array');
            }

            $_index = 1;
            foreach ($_attributeValue as $_BlockDeviceMap) {
                if (isset($_BlockDeviceMap['devicename'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.DeviceName', $_BlockDeviceMap['devicename']);
                }

                if (isset($_BlockDeviceMap['virtualname'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.VirtualName', $_BlockDeviceMap['virtualname']);
                }

                if (isset($_BlockDeviceMap['ebs']['snapshotid'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.Ebs.SnapshotId', $_BlockDeviceMap['ebs']['snapshotid']);
                }

                if (isset($_BlockDeviceMap['ebs']['volumesize'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.Ebs.VolumeSize', $_BlockDeviceMap['ebs']['volumesize']);
                }

                if (isset($_BlockDeviceMap['ebs']['nodevice'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.Ebs.NoDevice', $_BlockDeviceMap['ebs']['nodevice']);
                }

                if (isset($_BlockDeviceMap['ebs']['deleteontermination'])) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('BlockDeviceMapping.' . $_index . '.Ebs.DeleteOnTermination', $_BlockDeviceMap['ebs']['deleteontermination']);
                }

                $_index++;
            }
        } else {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('Value', $_attributeValue);
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ModifyInstanceAttribute(' . $_instanceID . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Resets an attribute of an instance to its default value.
     *
     * @author Varun Shoor
     * @param string $_instanceID The Instance ID
     * @param string $_attributeName The Attribute Name. Options: kernel | ramdisk
     * @return array | bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ResetInstanceAttribute($_instanceID, $_attributeName, $_attributeValue) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_instanceID) || empty($_attributeName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'ResetInstanceAttribute');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId', $_instanceID);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('Attribute', $_attributeName);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('Value', $_attributeValue);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ResetInstanceAttribute(' . $_instanceID . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Creates a new security group. Group names must be unique per account.
     *
     * Every instance is launched in a security group. If no security group is specified during launch, the instances are launched in the default security group.
     * Instances within the same security group have unrestricted network access to each other. Instances will reject network access attempts from other instances in a different security group.
     * As the owner of instances you can grant or revoke specific permissions using the AuthorizeSecurityGroupIngress and RevokeSecurityGroupIngress operations.
     *
     * @author Varun Shoor
     * @param string $_groupName The Group Name
     * @param string $_groupDescription The Security Group Description
     * @return array | bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateSecurityGroup($_groupName, $_groupDescription) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_groupName) || empty($_groupDescription)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'CreateSecurityGroup');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('GroupName', $_groupName);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('GroupDescription', $_groupDescription);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateSecurityGroup(' . $_groupName . ', ' . $_groupDescription . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Returns information about security groups that you own.
     *
     * IMPORTANT: does not support filters yet
     *
     * @author Varun Shoor
     * @param array $_groupNameList (OPTIONAL) The Group Name List
     * @return array | bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function DescribeSecurityGroups($_groupNameList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DescribeSecurityGroups');

        if ($_groupNameList !== null && _is_array($_groupNameList)) {
            $_index = 1;
            foreach ($_groupNameList as $_groupName) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('GroupName.' . $_index, $_groupName);

                $_index++;
            }
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeSecurityGroups(' . print_r($_groupNameList, true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->securityGroupInfo->item)) {
            return false;
        }

        $_resultsContainer = array();
        foreach ($_BodyObject->securityGroupInfo->item as $_ItemObject) {
            $_resultsContainer[] = $this->ConvertXMLObjectToArray($_ItemObject);
        }

        return $_resultsContainer;
    }

    /**
     * Deletes a security group that you own.
     *
     * Note
     * If you attempt to delete a security group that contains instances, a fault is returned.
     *
     * If you attempt to delete a security group that is referenced by another security group, a fault is returned.
     * For example, if security group B has a rule that allows access from security group A, security group A cannot be deleted until the allow rule is removed.
     *
     * @author Varun Shoor
     * @param string $_groupName The Group Name
     * @return array | bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteSecurityGroup($_groupName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_groupName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DeleteSecurityGroup');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('GroupName', $_groupName);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteSecurityGroup(' . $_groupName . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Adds a rule to a security group. Specifically, this either gives one or more CIDR IP address ranges permission to access a security group in your account, or gives one or more security groups (called the source groups) permission to access a security group in your account.
     * A source group can be in your own AWS account, or another.
     *
     * The permission is comprised of the IP protocol (TCP, UDP or ICMP) and the CIDR range or source group.
     * For TCP and UDP, you also specify the source and destination port ranges; for ICMP, you also specify the ICMP types. You can use -1 as a wildcard for the ICMP type.
     *
     * Permission changes are propagated to instances within the security group as quickly as possible. However, depending on the number of instances, a small delay might occur.
     *
     * Caution
     * Adding hundreds of rules to a security group might cause problems when you access the instance. We recommend you condense your rules as much as possible.
     *
     * @author Varun Shoor
     * @param string $_groupName The Group Name
     * @param array $_permissionsContainer The Permissions Container
     * @return array | bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function AuthorizeSecurityGroupIngress($_groupName, $_permissionsContainer) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_groupName) || !_is_array($_permissionsContainer)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'AuthorizeSecurityGroupIngress');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('GroupName', $_groupName);

        $_index = 1;
        foreach ($_permissionsContainer as $_permission) {
            if (!isset($_permission['ipprotocol']) || !isset($_permission['fromport']) || !isset($_permission['toport'])) {
                throw new SWIFT_Exception('AuthorizeSecurityGroupIngress required protocol, fromport and toport as required values');
            }

            $_SWIFT_AmazonEC2RequestObject->SetParameter('IpPermissions.' . $_index . '.IpProtocol', $_permission['ipprotocol']);
            $_SWIFT_AmazonEC2RequestObject->SetParameter('IpPermissions.' . $_index . '.FromPort', $_permission['fromport']);
            $_SWIFT_AmazonEC2RequestObject->SetParameter('IpPermissions.' . $_index . '.ToPort', $_permission['toport']);

            $_subIndex = 1;
            if (isset($_permission['userid']) && _is_array($_permission['userid']) && isset($_permission['groupname']) && _is_array($_permission['groupname'])) {
                foreach ($_permission['userid'] as $_userID) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('IpPermissions.' . $_index . '.Groups.' . $_subIndex . '.UserId', $_userID);

                    $_subIndex++;
                }

                foreach ($_permission['groupname'] as $_sourceSecurityGroup) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('IpPermissions.' . $_index . '.Groups.' . $_subIndex . '.GroupName', $_sourceSecurityGroup);

                    $_subIndex++;
                }
            } else if (isset($_permission['cidrip']) && _is_array($_permission['cidrip'])) {
                foreach ($_permission['cidrip'] as $_cidrIP) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('IpPermissions.' . $_index . '.IpRanges.' . $_subIndex . '.CidrIp', $_cidrIP);

                    $_subIndex++;
                }
            }

            $_index++;
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'AuthorizeSecurityGroupIngress(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Revokes permissions from a security group. The permissions used to revoke must be specified using the same values used to grant the permissions.
     *
     * The permission is comprised of the IP protocol (TCP, UDP or ICMP) and the CIDR range or source group. For TCP and UDP, you also specify the source and destination port ranges; for ICMP, you also specify the ICMP types. You can use -1 as a wildcard for the ICMP type.
     *
     * Permission changes are quickly propagated to instances within the security group. However, depending on the number of instances in the group, a small delay might occur.
     *
     * @author Varun Shoor
     * @param string $_groupName The Group Name
     * @param array $_permissionsContainer The Permissions Container
     * @return array | bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RevokeSecurityGroupIngress($_groupName, $_permissionsContainer) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_groupName) || !_is_array($_permissionsContainer)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'RevokeSecurityGroupIngress');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('GroupName', $_groupName);

        $_index = 1;
        foreach ($_permissionsContainer as $_permission) {
            if (!isset($_permission['ipprotocol']) || !isset($_permission['fromport']) || !isset($_permission['toport'])) {
                throw new SWIFT_Exception('AuthorizeSecurityGroupIngress required protocol, fromport and toport as required values');
            }

            $_SWIFT_AmazonEC2RequestObject->SetParameter('IpPermissions.' . $_index . '.IpProtocol', $_permission['ipprotocol']);
            $_SWIFT_AmazonEC2RequestObject->SetParameter('IpPermissions.' . $_index . '.FromPort', $_permission['fromport']);
            $_SWIFT_AmazonEC2RequestObject->SetParameter('IpPermissions.' . $_index . '.ToPort', $_permission['toport']);

            $_subIndex = 1;
            if (isset($_permission['userid']) && _is_array($_permission['userid']) && isset($_permission['groupname']) && _is_array($_permission['groupname'])) {
                foreach ($_permission['userid'] as $_userID) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('IpPermissions.' . $_index . '.Groups.' . $_subIndex . '.UserId', $_userID);

                    $_subIndex++;
                }

                foreach ($_permission['groupname'] as $_sourceSecurityGroup) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('IpPermissions.' . $_index . '.Groups.' . $_subIndex . '.GroupName', $_sourceSecurityGroup);

                    $_subIndex++;
                }
            } else if (isset($_permission['cidrip']) && _is_array($_permission['cidrip'])) {
                foreach ($_permission['cidrip'] as $_cidrIP) {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('IpPermissions.' . $_index . '.IpRanges.' . $_subIndex . '.CidrIp', $_cidrIP);

                    $_subIndex++;
                }
            }

            $_index++;
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'RevokeSecurityGroupIngress(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Creates a snapshot of an Amazon EBS volume and stores it in Amazon S3. You can use snapshots for backups, to make identical copies of instance devices, and to save data before shutting down an instance. For more information about Amazon EBS, go to the Amazon Elastic Compute Cloud Developer Guide or Amazon Elastic Compute Cloud User Guide.
     *
     * When taking a snapshot of a file system, we recommend unmounting it first. This ensures the file system metadata is in a consistent state, that the 'mounted indicator' is cleared, and that all applications using that file system are stopped and in a consistent state. Some file systems, such as xfs, can freeze and unfreeze activity so a snapshot can be made without unmounting.
     *
     * For Linux/UNIX, enter the following command from the command line.
     *
     * umount -d /dev/sdh
     *
     * For Windows, open Disk Management, right-click the volume to unmount, and select Change Drive Letter and Path. Then, select the mount point to remove and click Remove.
     *
     * @author Varun Shoor
     * @param string $_volumeID The Volume ID
     * @param string $_description (OPTIONAL) The Description of EBS Snapshot
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateSnapshot($_volumeID, $_description = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_volumeID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'CreateSnapshot');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('VolumeId', $_volumeID);

        if ($_description !== null && $_description !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('Description', $_description);
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateSnapshot(' . $_volumeID . ',' . $_description . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->snapshotId)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject);

        return $_resultsContainer;
    }

    /**
     * Returns information about Amazon EBS snapshots available to you. Snapshots available to you include public snapshots available for any AWS account to launch, private snapshots you own, and private snapshots owned by another AWS account but for which you've been given explicit create volume permissions.
     *
     * The create volume permissions fall into 3 categories:
     *
     * Permission    Description
     * public    The owner of the snapshot granted create volume permissions for the snapshot to the all group. All AWS accounts have create volume permissions for these snapshots.
     * explicit    The owner of the snapshot granted create volume permissions to a specific AWS account.
     * implicit    An AWS account has implicit create volume permissions for all snapshots it owns.
     *
     * The list of snapshots returned can be modified by specifying snapshot IDs, snapshot owners, or AWS accounts with create volume permissions. If no options are specified, Amazon EC2 returns all snapshots for which you have create volume permissions.
     *
     * If you specify one or more snapshot IDs, only snapshots that have the specified IDs are returned. If you specify an invalid snapshot ID, a fault is returned. If you specify a snapshot ID for which you do not have access, it will not be included in the returned results.
     *
     * If you specify one or more snapshot owners, only snapshots from the specified owners and for which you have access are returned. The results can include the AWS account IDs of the specified owners, amazon for snapshots owned by Amazon, or self for snapshots that you own.
     *
     * If you specify a list of restorable users, only snapshots with create snapshot permissions for those users are returned. You can specify AWS account IDs (if you own the snapshot(s)), self for snapshots for which you own or have explicit permissions, or all for public snapshots.
     *
     * @author Varun Shoor
     * @param array $_snapshotIDList (OPTIONAL) The ID of the Amazon EBS snapshot.
     * @param array $_ownerList (OPTIONAL) Returns snapshots owned by the specified owner. Multiple owners can be specified. Valid Values: self | amazon | AWS Account ID
     * @param array $_restorableByList (OPTIONAL) ID of an AWS account that can create volumes from the snapshot.
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function DescribeSnapshots($_snapshotIDList = null, $_ownerList = null, $_restorableByList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DescribeSnapshots');

        if ($_snapshotIDList !== null && _is_array($_snapshotIDList)) {
            $_index = 1;

            foreach ($_snapshotIDList as $_snapshotID) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('SnapshotId.' . $_index, $_snapshotID);

                $_index++;
            }
        }

        if ($_ownerList !== null && _is_array($_ownerList)) {
            $_index = 1;

            foreach ($_ownerList as $_owner) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('Owner.' . $_index, $_owner);

                $_index++;
            }
        }

        if ($_restorableByList !== null && _is_array($_restorableByList)) {
            $_index = 1;

            foreach ($_restorableByList as $_restorableBy) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('RestorableBy.' . $_index, $_restorableBy);

                $_index++;
            }
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeSnapshots(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->snapshotSet->item)) {
            return false;
        }

        $_resultsContainer = array();

        foreach ($_BodyObject->snapshotSet->item as $_SnapshotItemObject) {
            $_resultsContainer[] = $this->ConvertXMLObjectToArray($_SnapshotItemObject);
        }

        return $_resultsContainer;
    }

    /**
     * Deletes a snapshot of an Amazon EBS volume that you own.
     *
     * Note
     * If you make periodic snapshots of a volume, the snapshots are incremental so that only the blocks on the device that have changed since your last snapshot are incrementally saved in the new snapshot.
     * Even though snapshots are saved incrementally, the snapshot deletion process is designed so that you need to retain only the most recent snapshot in order to restore the volume.
     *
     * @author Varun Shoor
     * @param string $_snapshotID The Snapshot ID
     * @return array | bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteSnapshot($_snapshotID) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_snapshotID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DeleteSnapshot');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('SnapshotId', $_snapshotID);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteSnapshot(' . $_snapshotID . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Returns information about an attribute of a snapshot. You can get information about only one attribute per call.
     *
     * @author Varun Shoor
     * @param string $_snapshotID The Snapshot ID
     * @param string $_attribute (OPTIONAL) The Attribute to Fetch Information For
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DescribeSnapshotAttribute($_snapshotID, $_attribute = 'createVolumePermission') {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_snapshotID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DescribeSnapshotAttribute');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('SnapshotId', $_snapshotID);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('Attribute', $_attribute);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeSnapshotAttribute(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->snapshotId)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject);

        return $_resultsContainer;
    }

    /**
     * Resets permission settings for the specified snapshot.
     *
     * @author Varun Shoor
     * @param string $_snapshotID The Snapshot ID
     * @param string $_attribute (OPTIONAL) The Attribute to Reset
     * @return array | bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ResetSnapshotAttribute($_snapshotID, $_attribute = 'createVolumePermission') {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_snapshotID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'ResetSnapshotAttribute');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('SnapshotId', $_snapshotID);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('Attribute', $_attribute);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ResetSnapshotAttribute(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Adds or remove permission settings for the specified snapshot.
     *
     * @author Varun Shoor
     * @param string $_snapshotID The Snapshot ID
     * @param string $_operationType The Operation Type. Valid Values: add | remove
     * @param string $_attribute The Attribute to Reset
     * @param array $_userIDList (OPTIONAL) ID of an AWS account that can create volumes from the snapshot.
     * @param array $_userGroupList (OPTIONAL) Group that is allowed to create volumes from the snapshot (currently supports "all").
     * @return bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ModifySnapshotAttribute($_snapshotID, $_operationType, $_attribute = 'createVolumePermission', $_userIDList = null, $_userGroupList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_snapshotID) || empty($_operationType) || empty($_attribute)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'ModifySnapshotAttribute');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('SnapshotId', $_snapshotID);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('Attribute', $_attribute);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('OperationType', $_operationType);

        if ($_userIDList !== null && _is_array($_userIDList)) {
            $_index = 1;

            foreach ($_userIDList as $_userID) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('UserId.' . $_index, $_userID);

                $_index++;
            }
        }

        if ($_userGroupList !== null && _is_array($_userGroupList)) {
            $_index = 1;

            foreach ($_userGroupList as $_userGroup) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('UserGroup.' . $_index, $_userGroup);

                $_index++;
            }
        }


        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ModifySnapshotAttribute(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Describes your Amazon EBS volumes. For more information about Amazon EBS, go to the Amazon Elastic Compute Cloud Developer Guide or Amazon Elastic Compute Cloud User Guide.
     *
     * IMPORTANT: Does not support filters yet!
     *
     * @author Varun Shoor
     * @param array $_volumeIDList (OPTIONAL) The Volume ID List
     * @return array | bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function DescribeVolumes($_volumeIDList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DescribeVolumes');

        if ($_volumeIDList !== null && _is_array($_volumeIDList)) {
            $_index = 1;

            foreach ($_volumeIDList as $_volumeID) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('VolumeId.' . $_index, $_volumeID);

                $_index++;
            }
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeVolumes(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->volumeSet->item)) {
            return false;
        }

        $_resultsContainer = array();

        foreach ($_BodyObject->volumeSet->item as $_VolumeItemObject) {
            $_resultsContainer[] = $this->ConvertXMLObjectToArray($_VolumeItemObject);
        }

        return $_resultsContainer;
    }

    /**
     * Deletes an Amazon EBS volume that you own. The volume must be in the available state (not attached to an instance). For more information about Amazon EBS, go to the Amazon Elastic Compute Cloud Developer Guide or Amazon Elastic Compute Cloud User Guide.
     *
     * Note
     * The volume remains in the deleting state for several minutes after you enter this command.
     *
     * @author Varun Shoor
     * @param string $_volumeID The Volume ID
     * @return bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteVolume($_volumeID) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_volumeID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DeleteVolume');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('VolumeId', $_volumeID);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteVolume(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Creates a new Amazon EBS volume to which any Amazon EC2 instance can attach within the same Availability Zone. For more information about Amazon EBS, go to the Amazon Elastic Compute Cloud Developer Guide or Amazon Elastic Compute Cloud User Guide.
     *
     * Note
     * You must specify an Availability Zone when creating a volume. The volume and the instance to which it attaches must be in the same Availability Zone.
     *
     * @author Varun Shoor
     * @param string $_size The size of the volume, in GiBs. Required if you are not creating a volume from a snapshot. Valid Values: 1 -1024
     * @param string $_availabilityZone The Availability Zone in which to create the new volume
     * @param string $_snapshotID (OPTIONAL) The Snapshot ID from which to create new volume
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateVolume($_size, $_availabilityZone, $_snapshotID = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_size) || empty($_availabilityZone)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'CreateVolume');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('Size', $_size);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('AvailabilityZone', $_availabilityZone);

        if ($_snapshotID !== null && $_snapshotID !== '') {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('SnapshotId', $_snapshotID);
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateVolume(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->volumeId)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject);

        return $_resultsContainer;
    }

    /**
     * Attaches an Amazon EBS volume to a running instance and exposes it as the specified device.
     *
     * Note
     * Windows instances currently support devices xvda through xvdp. Devices xvda and xvdb are reserved by the operating system, xvdc is assigned to drive C:\, and, depending on the instance type, devices xvdd through xvde might be reserved by the instance stores. Any device that is not reserved can be attached to an Amazon EBS volume. For a list of devices that are reserved by the instance stores, go to the Amazon Elastic Compute Cloud Developer Guide or Amazon Elastic Compute Cloud User Guide.
     *
     * @author Varun Shoor
     * @param string $_volumeID Volume ID
     * @param string $_instanceID The Instance ID
     * @param string $_device Specifies how the device is exposed to the instance (ex: /dev/sdh)
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function AttachVolume($_volumeID, $_instanceID, $_device) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_volumeID) || empty($_instanceID) || empty($_device)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'AttachVolume');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('VolumeId', $_volumeID);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId', $_instanceID);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('Device', $_device);

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'AttachVolume(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->volumeId)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject);

        return $_resultsContainer;
    }

    /**
     * Detaches an Amazon EBS volume from an instance. For more information about Amazon EBS, go to the Amazon Elastic Compute Cloud Developer Guide or Amazon Elastic Compute Cloud User Guide.
     *
     * Important
     * Make sure to unmount any file systems on the device within your operating system before detaching the volume. Failure to unmount file systems, or otherwise properly release the device from use, can result in lost data and will corrupt the file system.
     *
     * Note
     * If an Amazon EBS volume is the root device of an instance, it cannot be detached while the instance is in the ‘running’ state. To detach the root volume, stop the instance first.
     *
     * @author Varun Shoor
     * @param string $_volumeID Volume ID
     * @param string $_instanceID The Instance ID
     * @param string $_device Specifies how the device is exposed to the instance (ex: /dev/sdh)
     * @param bool $_useForce (OPTIONAL) Forces detachment if the previous detachment attempt did not occur cleanly (logging into an instance, unmounting the volume, and detaching normally). This option can lead to data loss or a corrupted file system. Use this option only as a last resort to detach a volume from a failed instance. The instance will not have an opportunity to flush file system caches nor file system meta data. If you use this option, you must perform file system check and repair procedures.
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DetachVolume($_volumeID, $_instanceID, $_device, $_useForce = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_volumeID) || empty($_instanceID) || empty($_device)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DetachVolume');

        $_SWIFT_AmazonEC2RequestObject->SetParameter('VolumeId', $_volumeID);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('InstanceId', $_instanceID);
        $_SWIFT_AmazonEC2RequestObject->SetParameter('Device', $_device);

        if ($_useForce !== null && is_bool($_useForce)) {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('Force', IIF($_useForce == true, 'true', 'false'));
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DetachVolume(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->volumeId)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject);

        return $_resultsContainer;
    }

    /**
     * Adds or overwrites one or more tags for the specified resource or resources. Each resource can have a maximum of 10 tags. Each tag consists of a key and optional value. Tag keys must be unique per resource.
     *
     * For more information about tags, go to Using Tags in the Amazon Elastic Compute Cloud Developer Guide.
     *
     * @author Varun Shoor
     * @param array $_resourceIDList The Resource ID List to Tag
     * @param array $_tagList The Key > Value List of Tags
     * @return bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateTags($_resourceIDList, $_tagList) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!_is_array($_resourceIDList) || !_is_array($_tagList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'CreateTags');

        $_index = 1;
        foreach ($_resourceIDList as $_resourceID) {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('ResourceId.' . $_index, $_resourceID);

            $_index++;
        }

        $_index = 1;
        foreach ($_tagList as $_tagName => $_tagValue) {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('Tag.' . $_index . '.Key', $_tagName);
            $_SWIFT_AmazonEC2RequestObject->SetParameter('Tag.' . $_index . '.Value', $_tagValue);

            $_index++;
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateTags(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Deletes a specific set of tags from a specific set of resources. This call is designed to follow a DescribeTags call. You first determine what tags a resource has, and then you call DeleteTags with the resource ID and the specific tags you want to delete.
     *
     * For more information about tags, go to Using Tags in the Amazon Elastic Compute Cloud Developer Guide.
     *
     * @author Varun Shoor
     * @param array $_resourceIDList The Resource ID List to Tag
     * @param array $_tagList The Key > Value List of Tags
     * @return bool
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteTags($_resourceIDList, $_tagList) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!_is_array($_resourceIDList) || !_is_array($_tagList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DeleteTags');

        $_index = 1;
        foreach ($_resourceIDList as $_resourceID) {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('ResourceId.' . $_index, $_resourceID);

            $_index++;
        }

        $_index = 1;
        foreach ($_tagList as $_tagName => $_tagValue) {
            $_SWIFT_AmazonEC2RequestObject->SetParameter('Tag.' . $_index . '.Key', $_tagName);
            $_SWIFT_AmazonEC2RequestObject->SetParameter('Tag.' . $_index . '.Value', $_tagValue);

            $_index++;
        }

        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteTags(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->return)) {
            return false;
        }

        $_releaseResult = (string)  $_BodyObject->return;
        if ($_releaseResult == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Lists your tags. For more information about tags, go to Using Tags in the Amazon Elastic Compute Cloud Developer Guide.
     *
     * You can use filters to limit the results when describing tags. For example, you could get only the tags for a particular resource type. You can specify multiple values for a filter. A tag must match at least one of the specified values for it to be included in the results.
     *
     * You can specify multiple filters (e.g., limit the results to a specific resource type, and get only tags with values that contain the string database). The result includes information for a particular tag only if it matches all your filters. If there's no match, no special message is returned; the response is simply empty.
     *
     * You can use wildcards with the filter values: * matches zero or more characters, and ? matches exactly one character. You can escape special characters using a backslash before the character. For example, a value of \*amazon\?\\ searches for the literal string *amazon?\.
     *
     * The following table shows the available filters.
     *
     * Filter Name     Description
     * key                Tag key.
     * resource-id        Resource ID.
     * resource-type    Resource type. Valid Values: customer-gateway | dhcp-options | image | instance | snapshot | spot-instances-request | subnet | volume | vpc | vpn-connection | vpn-gateway
     * value            Tag value.
     *
     * @author Varun Shoor
     * @param array $_filterList (OPTIONAL) The Filter List
     * @return array | false
     * @throws SWIFT_AmazonEC2_Exception If the Class is not Loaded
     */
    public function DescribeTags($_filterList = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonEC2RequestObject = new SWIFT_AmazonEC2Request(SWIFT_AmazonEC2Request::ACTION_GET, 'DescribeTags');

        if ($_filterList !== null && _is_array($_filterList)) {
            $_index = 1;
            foreach ($_filterList as $_filterName => $_filterValue) {
                $_SWIFT_AmazonEC2RequestObject->SetParameter('Filter.' . $_index . '.Name', $_filterName);

                if (_is_array($_filterValue)) {
                    $_valueIndex = 1;
                    foreach ($_filterValue as $_filterValueX) {
                        $_SWIFT_AmazonEC2RequestObject->SetParameter('Filter.' . $_index . '.Value.' . $_valueIndex, $_filterValueX);

                        $_valueIndex++;
                    }
                } else {
                    $_SWIFT_AmazonEC2RequestObject->SetParameter('Filter.' . $_index . '.Value.1', $_filterValue);
                }

                $_index++;
            }
        }


        $_ResponseObject = $_SWIFT_AmazonEC2RequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeTags(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->tagSet->item)) {
            return false;
        }

        $_resultsContainer = array();
        foreach ($_BodyObject->tagSet->item as $_TagItemObject) {
            $_resultsContainer[] = $this->ConvertXMLObjectToArray($_TagItemObject);
        }

        return $_resultsContainer;
    }

    /**
     * Get the available instance type list
     *
     * @author Varun Shoor
     * @return array The Instance Type List
     */
    public static function GetInstanceTypeList() {
        $_instanceTypeList = array();

        $_instanceTypeList[] = self::INSTANCE_M1SMALL;
        $_instanceTypeList[] = self::INSTANCE_M1LARGE;
        $_instanceTypeList[] = self::INSTANCE_M1XLARGE;
        $_instanceTypeList[] = self::INSTANCE_M2XLARGE;
        $_instanceTypeList[] = self::INSTANCE_M22XLARGE;
        $_instanceTypeList[] = self::INSTANCE_M24XLARGE;
        $_instanceTypeList[] = self::INSTANCE_T1MICRO;
        $_instanceTypeList[] = self::INSTANCE_C1MEDIUM;
        $_instanceTypeList[] = self::INSTANCE_C1XLARGE;
        $_instanceTypeList[] = self::INSTANCE_CC14XLARGE;

        return $_instanceTypeList;
    }
}
?>
