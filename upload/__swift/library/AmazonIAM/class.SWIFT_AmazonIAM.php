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

/**
 * Amazon IAM PHP class
 *
 * @author Varun Shoor
 */
class SWIFT_AmazonIAM extends SWIFT_Amazon {
    private $_useSSL = true;

    private $__accessKey; // AWS Access key
    private $__secretKey; // AWS Secret key
    private $_verifyHost = 2;
    private $_verifyPeer = 1;

    // Core Constants
    private $_baseURL = 'iam.amazonaws.com';

    /**
     * Constructor, used if you're not calling the class statically
     *
     * @param string $_accessKey Access key
     * @param string $_secretKey Secret key
     * @param boolean $_useSSL (OPTIONAL) Whether or not to use SSL
     * @throws SWIFT_AmazonIAM_Exception If the Class could not be loaded
     */
    public function __construct($_accessKey, $_secretKey, $_useSSL = true) {
        parent::__construct();

        if (!$this->SetAccessKey($_accessKey) || !$this->SetSecretKey($_secretKey))
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
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetAccessKey($_accessKey)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded
     */
    public function GetAccessKey()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetSecretKey($_secretKey)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded
     */
    public function GetSecretKey()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->__secretKey;
    }

    /**
     * Check to see if the user can use SSL
     *
     * @author Varun Shoor
     * @return int "1" on Success, "0" otherwise
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded
     */
    public function CanUseSSL()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);
        }

        return (int) ($this->_useSSL);
    }

    /**
     * Set the Can Use SSL property
     *
     * @author Varun Shoor
     * @param bool $_useSSL The Use SSL Property
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded
     */
    public function SetCanUseSSL($_useSSL)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

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
     * Check the Amazon IAM Response to make sure the error codes are right
     *
     * @author Varun Shoor
     * @param SWIFT_AmazonIAMResponse $_SWIFT_AmazonIAMResponseObject The SWIFT_AmazonIAMResponse Object Pointer
     * @param string $_callingFunction (OPTIONAL) The Name of Function Running this Check
     * @param int $_httpCode (OPTIONAL) The HTTP Code to Check Against
     * @param bool $_endExecution (BOOL) Whether to End the Execution if Error Encountered
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    private function CheckResponse(SWIFT_AmazonIAMResponse $_SWIFT_AmazonIAMResponseObject, $_callingFunction = '', $_httpCode = 200, $_endExecution = true)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!$_SWIFT_AmazonIAMResponseObject->GetIsClassLoaded()) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if ($_SWIFT_AmazonIAMResponseObject->GetError() === false && $_SWIFT_AmazonIAMResponseObject->GetHTTPCode() != $_httpCode)
        {
            $_SWIFT_AmazonIAMResponseObject->Error($_SWIFT_AmazonIAMResponseObject->GetHTTPCode(), 'Unexpected HTTP status (' . $_httpCode . ')');
        }

        if ($_SWIFT_AmazonIAMResponseObject->GetError() !== false) {

            if ($_endExecution)
            {
                $_errorContainer = $_SWIFT_AmazonIAMResponseObject->GetError();
                $_ErrorObject = $_SWIFT_AmazonIAMResponseObject->GetBodyObject();
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

                        $_errorMessageFinal .= sprintf("SWIFT_AmazonIAM::". $_callingFunction .": [%s] %s" . "\n" . $_awsCode . ': ' . $_awsMessage, $_errorContainer['code'], $_errorContainer['message']) . SWIFT_CRLF;
                    }
                } else if (isset($_ErrorObject->Error)) {
                    $_Error = $_ErrorObject->Error;

                    if (isset($_Error->Code))
                    {
                        $_awsCode = (string) $_Error->Code;
                    }

                    if (isset($_Error->Message))
                    {
                        $_awsMessage = (string) $_Error->Message;
                    }

                    $_errorMessageFinal .= sprintf("SWIFT_AmazonIAM::". $_callingFunction .": [%s] %s" . "\n" . $_awsCode . ': ' . $_awsMessage, $_errorContainer['code'], $_errorContainer['message']) . SWIFT_CRLF;

                }

                throw new SWIFT_AmazonIAM_Exception($_errorMessageFinal);

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
     * Retrieve the Base URL
     *
     * @author Varun Shoor
     * @return string|bool The Base URL
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
     * List all groups
     *
     * @author Varun Shoor
     * @param string $_pathPrefix (OPTIONAL)
     * @param string $_marker (OPTIONAL)
     * @param int $_maxItems (OPTIONAL)
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ListGroups($_pathPrefix = null, $_marker = null, $_maxItems = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'ListGroups');

        if ($_pathPrefix !== null && $_pathPrefix !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('PathPrefix', $_pathPrefix);
        }

        if ($_marker !== null && $_marker !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('Marker', $_marker);
        }

        if ($_maxItems !== null && $_maxItems !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('MaxItems', $_maxItems);
        }

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ListGroups(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_resultsContainer = array();

        foreach ($_BodyObject->ListGroupsResult->Groups->member as $_GroupObject) {
            $_resultsContainer[] = array('id' => (string)$_GroupObject->GroupId, 'name' => (string)$_GroupObject->GroupName, 'path' => (string)$_GroupObject->Path, 'arn' => (string)$_GroupObject->Arn, 'created' => (string)$_GroupObject->CreateDate);
        }

        if (isset($_BodyObject->ListGroupsResult->IsTruncated) && (string)$_BodyObject->ListGroupsResult->IsTruncated == 'false')
        {
            return $_resultsContainer;
        }

        // If Max items isnt specified then we retrieve all groups
        if ($_maxItems === null && isset($_BodyObject->ListGroupsResult->Marker)) {
            $_lastMarker = (string) $_BodyObject->ListGroupsResult->Marker;

            $_loopAttempts = 0;
            do {
                $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'ListGroups');

                if ($_pathPrefix != null && $_pathPrefix !== '') {
                    $_SWIFT_AmazonIAMRequestObject->SetParameter('PathPrefix', $_pathPrefix);
                }

                $_SWIFT_AmazonIAMRequestObject->SetParameter('Marker', $_lastMarker);

                if ($_maxItems != null && $_maxItems !== '') {
                    $_SWIFT_AmazonIAMRequestObject->SetParameter('MaxItems', $_maxItems);
                }

                $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
                if (!$this->CheckResponse($_ResponseObject, 'ListGroups(' . implode(', ', func_get_args()) . ')', 200))
                {
                    return false;
                }

                $_BodyObject = $_ResponseObject->GetBodyObject();

                foreach ($_BodyObject->ListGroupsResult->Groups->member as $_GroupObject) {
                    $_resultsContainer[] = array('id' => (string)$_GroupObject->GroupId, 'name' => (string)$_GroupObject->GroupName, 'path' => (string)$_GroupObject->Path, 'arn' => (string)$_GroupObject->Arn, 'created' => (string)$_GroupObject->CreateDate);
                }

                if (isset($_BodyObject->ListGroupsResult->Marker)) {
                    $_lastMarker = (string) $_BodyObject->ListGroupsResult->Marker;
                } else {
                    $_lastMarker = false;
                }

                $_loopAttempts++;

            } while ($_ResponseObject !== false && $_lastMarker != false && (string)$_BodyObject->ListGroupResult->IsTruncated == 'true');
        }

        return $_resultsContainer;
    }

    /**
     * Create a new Group
     *
     * @author Varun Shoor
     * @param string $_groupName
     * @param string $_path (OPTIONAL) Must begin with and end with '/' and contain only alphanumeric characters
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateGroup($_groupName, $_path = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_groupName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'CreateGroup');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('GroupName', $_groupName);

        if ($_path !== null && $_path !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('Path', $_path);
        }

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateGroup(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->CreateGroupResult->Group->GroupName)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->CreateGroupResult->Group);

        return $_resultsContainer;
    }

    /**
     * Update Group Details
     *
     * @author Varun Shoor
     * @param string $_groupName
     * @param string $_newGroupName (OPTIONAL)
     * @param string $_newPath (OPTIONAL) Must begin with and end with '/' and contain only alphanumeric characters
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateGroup($_groupName, $_newGroupName = null, $_newPath = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_groupName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'UpdateGroup');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('GroupName', $_groupName);

        if ($_newPath !== null && $_newPath !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('NewPath', $_newPath);
        }

        if ($_newGroupName !== null && $_newGroupName !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('NewGroupName', $_newGroupName);
        }

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'UpdateGroup(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->UpdateGroupResult->Group->GroupName)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->UpdateGroupResult->Group);

        return $_resultsContainer;
    }

    /**
     * Delete a group
     *
     * @author Varun Shoor
     * @param string $_groupName
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteGroup($_groupName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_groupName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'DeleteGroup');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('GroupName', $_groupName);

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteGroup(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->ResponseMetadata)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->ResponseMetadata);

        return $_resultsContainer;
    }


    /**
     * List group policies
     *
     * @author Varun Shoor
     * @param string $_groupName
     * @param string $_marker (OPTIONAL)
     * @param int $_maxItems (OPTIONAL)
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ListGroupPolicies($_groupName, $_marker = null, $_maxItems = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_groupName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'ListGroupPolicies');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('GroupName', $_groupName);

        if ($_marker !== null && $_marker !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('Marker', $_marker);
        }

        if ($_maxItems !== null && $_maxItems !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('MaxItems', $_maxItems);
        }

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ListGroupPolicies(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_resultsContainer = array();

        foreach ($_BodyObject->ListGroupPoliciesResult->PolicyNames->member as $_PolicyObject) {
            $_resultsContainer[] = (string) $_PolicyObject;
        }

        if (isset($_BodyObject->ListGroupPoliciesResult->IsTruncated) && (string)$_BodyObject->ListGroupPoliciesResult->IsTruncated == 'false')
        {
            return $_resultsContainer;
        }

        // If Max items isnt specified then we retrieve all groups
        if ($_maxItems === null && isset($_BodyObject->ListGroupPoliciesResult->Marker)) {
            $_lastMarker = (string) $_BodyObject->ListGroupPoliciesResult->Marker;

            $_loopAttempts = 0;
            do {
                $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'ListGroupPolicies');

                $_SWIFT_AmazonIAMRequestObject->SetParameter('GroupName', $_groupName);

                $_SWIFT_AmazonIAMRequestObject->SetParameter('Marker', $_lastMarker);

                if ($_maxItems != null && $_maxItems !== '') {
                    $_SWIFT_AmazonIAMRequestObject->SetParameter('MaxItems', $_maxItems);
                }

                $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
                if (!$this->CheckResponse($_ResponseObject, 'ListGroupPolicies(' . implode(', ', func_get_args()) . ')', 200))
                {
                    return false;
                }

                $_BodyObject = $_ResponseObject->GetBodyObject();

                foreach ($_BodyObject->ListGroupPoliciesResult->PolicyNames->member as $_PolicyObject) {
                    $_resultsContainer[] = (string) $_PolicyObject;
                }

                if (isset($_BodyObject->ListGroupPoliciesResult->Marker)) {
                    $_lastMarker = (string) $_BodyObject->ListGroupPoliciesResult->Marker;
                } else {
                    $_lastMarker = false;
                }

                $_loopAttempts++;

            } while ($_ResponseObject !== false && $_lastMarker != false && (string)$_BodyObject->ListGroupPoliciesResult->IsTruncated == 'true');
        }

        return $_resultsContainer;
    }

    /**
     * Create a group policy
     *
     * @author Varun Shoor
     * @param string $_groupName
     * @param string $_policyDocument
     * @param string $_policyName
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function PutGroupPolicy($_groupName, $_policyDocument, $_policyName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_groupName) || empty($_policyDocument) || empty($_policyName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_POST, 'PutGroupPolicy');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('GroupName', $_groupName);
        $_SWIFT_AmazonIAMRequestObject->SetParameter('PolicyDocument', $_policyDocument);
        $_SWIFT_AmazonIAMRequestObject->SetParameter('PolicyName', $_policyName);

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'PutGroupPolicy(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->ResponseMetadata)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->ResponseMetadata);

        return $_resultsContainer;
    }

    /**
     * Retrieve a group policy
     *
     * @author Varun Shoor
     * @param string $_groupName
     * @param string $_policyName
     * @return string | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetGroupPolicy($_groupName, $_policyName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_groupName) || empty($_policyName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'GetGroupPolicy');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('GroupName', $_groupName);
        $_SWIFT_AmazonIAMRequestObject->SetParameter('PolicyName', $_policyName);

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'GetGroupPolicy(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->GetGroupPolicyResult->GroupName)) {
            return false;
        }

        $_policyDocument = isset($_BodyObject->GetGroupPolicyResult->PolicyDocument) ?
                           (string)$_BodyObject->GetGroupPolicyResult->PolicyDocument : '';

        return urldecode($_policyDocument);
    }

    /**
     * Delete a group policy
     *
     * @author Varun Shoor
     * @param string $_groupName
     * @param string $_policyName
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteGroupPolicy($_groupName, $_policyName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_groupName) || empty($_policyName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'DeleteGroupPolicy');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('GroupName', $_groupName);
        $_SWIFT_AmazonIAMRequestObject->SetParameter('PolicyName', $_policyName);

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteGroupPolicy(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->ResponseMetadata)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->ResponseMetadata);

        return $_resultsContainer;
    }

    /**
     * List all users
     *
     * @author Varun Shoor
     * @param string $_pathPrefix (OPTIONAL)
     * @param string $_marker (OPTIONAL)
     * @param int $_maxItems (OPTIONAL)
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ListUsers($_pathPrefix = null, $_marker = null, $_maxItems = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'ListUsers');

        if ($_pathPrefix !== null && $_pathPrefix !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('PathPrefix', $_pathPrefix);
        }

        if ($_marker !== null && $_marker !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('Marker', $_marker);
        }

        if ($_maxItems !== null && $_maxItems !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('MaxItems', $_maxItems);
        }

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ListUsers(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_resultsContainer = array();

        foreach ($_BodyObject->ListUsersResult->Users->member as $_UserObject) {
            $_resultsContainer[] = array('id' => (string)$_UserObject->UserId, 'username' => (string)$_UserObject->UserName, 'path' => (string)$_UserObject->Path, 'arn' => (string)$_UserObject->Arn);
        }

        if (isset($_BodyObject->ListUsersResult->IsTruncated) && (string)$_BodyObject->ListUsersResult->IsTruncated == 'false')
        {
            return $_resultsContainer;
        }

        // If Max items isnt specified then we retrieve all users
        if ($_maxItems === null && isset($_BodyObject->ListUsersResult->Marker)) {
            $_lastMarker = (string) $_BodyObject->ListUsersResult->Marker;

            $_loopAttempts = 0;
            do {
                $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'ListUsers');

                if ($_pathPrefix != null && $_pathPrefix !== '') {
                    $_SWIFT_AmazonIAMRequestObject->SetParameter('PathPrefix', $_pathPrefix);
                }

                $_SWIFT_AmazonIAMRequestObject->SetParameter('Marker', $_lastMarker);

                if ($_maxItems != null && $_maxItems !== '') {
                    $_SWIFT_AmazonIAMRequestObject->SetParameter('MaxItems', $_maxItems);
                }

                $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
                if (!$this->CheckResponse($_ResponseObject, 'ListUsers(' . implode(', ', func_get_args()) . ')', 200))
                {
                    return false;
                }

                $_BodyObject = $_ResponseObject->GetBodyObject();

                foreach ($_BodyObject->ListUsersResult->Users->member as $_UserObject) {
                    $_resultsContainer[] = array('id' => (string)$_UserObject->UserId, 'username' => (string)$_UserObject->UserName, 'path' => (string)$_UserObject->Path, 'arn' => (string)$_UserObject->Arn);
                }

                if (isset($_BodyObject->ListUsersResult->Marker)) {
                    $_lastMarker = (string) $_BodyObject->ListUsersResult->Marker;
                } else {
                    $_lastMarker = false;
                }

                $_loopAttempts++;

            } while ($_ResponseObject !== false && $_lastMarker != false && (string)$_BodyObject->ListUsersResult->IsTruncated == 'true');
        }

        return $_resultsContainer;
    }

    /**
     * Retrieve a User Details
     *
     * @author Varun Shoor
     * @param string $_userName
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetUser($_userName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'GetUser');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'GetUser(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->GetUserResult->User->UserName)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->GetUserResult->User);

        return $_resultsContainer;
    }

    /**
     * Create a new User
     *
     * @author Varun Shoor
     * @param string $_userName
     * @param string $_path (OPTIONAL) Must begin with and end with '/' and contain only alphanumeric characters
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateUser($_userName, $_path = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'CreateUser');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);

        if ($_path !== null && $_path !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('Path', $_path);
        }

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateUser(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->CreateUserResult->User->UserName)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->CreateUserResult->User);

        return $_resultsContainer;
    }

    /**
     * Update an existing User
     *
     * @author Varun Shoor
     * @param string $_userName
     * @param string $_newPath (OPTIONAL)
     * @param string $_newUserName (OPTIONAL)
     * @return bool
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateUser($_userName, $_newPath = null, $_newUserName = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_userName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'UpdateUser');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);

        if ($_newPath !== null && $_newPath !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('NewPath', $_newPath);
        }
        if ($_newUserName !== null && $_newUserName !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('NewUserName', $_newUserName);
        }

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'UpdateUser(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->ResponseMetadata->ResponseId)) {
            return false;
        }

        return true;
    }

    /**
     * Add a user to a group
     *
     * @author Varun Shoor
     * @param string $_groupName
     * @param string $_userName
     * @return bool
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function AddUserToGroup($_groupName, $_userName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_groupName) || empty($_userName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'AddUserToGroup');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('GroupName', $_groupName);
        $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'AddUserToGroup(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->ResponseMetadata->ResponseId)) {
            return false;
        }

        return true;
    }

    /**
     * Remove a user to a group
     *
     * @author Varun Shoor
     * @param string $_groupName
     * @param string $_userName
     * @return bool
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RemoveUserFromGroup($_groupName, $_userName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_groupName) || empty($_userName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'RemoveUserFromGroup');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('GroupName', $_groupName);
        $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'RemoveUserFromGroup(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->ResponseMetadata->ResponseId)) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve all users that exist in a group
     *
     * @author Varun Shoor
     * @param string $_groupName
     * @param string $_marker (OPTIONAL)
     * @param int $_maxItems (OPTIONAL)
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetGroup($_groupName, $_marker = null, $_maxItems = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_groupName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'GetGroup');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('GroupName', $_groupName);

        if ($_marker !== null && $_marker !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('Marker', $_marker);
        }

        if ($_maxItems !== null && $_maxItems !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('MaxItems', $_maxItems);
        }

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'GetGroup(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_resultsContainer = array();

        foreach ($_BodyObject->GetGroupResult->Users->member as $_UserObject) {
            $_resultsContainer[] = array('id' => (string)$_UserObject->UserId, 'username' => (string)$_UserObject->UserName, 'path' => (string)$_UserObject->Path, 'arn' => (string)$_UserObject->Arn);
        }

        if (isset($_BodyObject->GetGroupResult->IsTruncated) && (string)$_BodyObject->GetGroupResult->IsTruncated == 'false')
        {
            return $_resultsContainer;
        }

        // If Max items isnt specified then we retrieve all users
        if ($_maxItems === null && isset($_BodyObject->GetGroupResult->Marker)) {
            $_lastMarker = (string) $_BodyObject->GetGroupResult->Marker;

            $_loopAttempts = 0;
            do {
                $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'GetGroup');

                $_SWIFT_AmazonIAMRequestObject->SetParameter('GroupName', $_groupName);

                $_SWIFT_AmazonIAMRequestObject->SetParameter('Marker', $_lastMarker);

                if ($_maxItems != null && $_maxItems !== '') {
                    $_SWIFT_AmazonIAMRequestObject->SetParameter('MaxItems', $_maxItems);
                }

                $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
                if (!$this->CheckResponse($_ResponseObject, 'GetGroup(' . implode(', ', func_get_args()) . ')', 200))
                {
                    return false;
                }

                $_BodyObject = $_ResponseObject->GetBodyObject();

                foreach ($_BodyObject->GetGroupResult->Users->member as $_UserObject) {
                    $_resultsContainer[] = array('id' => (string)$_UserObject->UserId, 'username' => (string)$_UserObject->UserName, 'path' => (string)$_UserObject->Path, 'arn' => (string)$_UserObject->Arn);
                }

                if (isset($_BodyObject->GetGroupResult->Marker)) {
                    $_lastMarker = (string) $_BodyObject->GetGroupResult->Marker;
                } else {
                    $_lastMarker = false;
                }

                $_loopAttempts++;

            } while ($_ResponseObject !== false && $_lastMarker != false && (string)$_BodyObject->GetGroupResult->IsTruncated == 'true');
        }

        return $_resultsContainer;
    }

    /**
     * List all groups for a user
     *
     * @author Varun Shoor
     * @param string $_userName
     * @param string $_marker (OPTIONAL)
     * @param int $_maxItems (OPTIONAL)
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ListGroupsForUser($_userName, $_marker = null, $_maxItems = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'ListGroupsForUser');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);

        if ($_marker !== null && $_marker !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('Marker', $_marker);
        }

        if ($_maxItems !== null && $_maxItems !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('MaxItems', $_maxItems);
        }

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ListGroupsForUser(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_resultsContainer = array();

        foreach ($_BodyObject->ListGroupsForUserResult->Groups->member as $_GroupObject) {
            $_resultsContainer[] = array('id' => (string)$_GroupObject->GroupId, 'name' => (string)$_GroupObject->GroupName, 'path' => (string)$_GroupObject->Path, 'arn' => (string)$_GroupObject->Arn);
        }

        if (isset($_BodyObject->ListGroupsForUserResult->IsTruncated) && (string)$_BodyObject->ListGroupsForUserResult->IsTruncated == 'false')
        {
            return $_resultsContainer;
        }

        // If Max items isnt specified then we retrieve all groups
        if ($_maxItems === null && isset($_BodyObject->GetGroupResult->Marker)) {
            $_lastMarker = (string) $_BodyObject->GetGroupResult->Marker;

            $_loopAttempts = 0;
            do {
                $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'ListGroupsForUser');

                $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);

                $_SWIFT_AmazonIAMRequestObject->SetParameter('Marker', $_lastMarker);

                if ($_maxItems != null && $_maxItems !== '') {
                    $_SWIFT_AmazonIAMRequestObject->SetParameter('MaxItems', $_maxItems);
                }

                $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
                if (!$this->CheckResponse($_ResponseObject, 'ListGroupsForUser(' . implode(', ', func_get_args()) . ')', 200))
                {
                    return false;
                }

                $_BodyObject = $_ResponseObject->GetBodyObject();

                foreach ($_BodyObject->ListGroupsForUserResult->Groups->member as $_GroupObject) {
                    $_resultsContainer[] = array('id' => (string)$_GroupObject->GroupId, 'name' => (string)$_GroupObject->GroupName, 'path' => (string)$_GroupObject->Path, 'arn' => (string)$_GroupObject->Arn);
                }

                if (isset($_BodyObject->ListGroupsForUserResult->Marker)) {
                    $_lastMarker = (string) $_BodyObject->ListGroupsForUserResult->Marker;
                } else {
                    $_lastMarker = false;
                }

                $_loopAttempts++;

            } while ($_ResponseObject !== false && $_lastMarker != false && (string)$_BodyObject->ListGroupsForUserResult->IsTruncated == 'true');
        }

        return $_resultsContainer;
    }

    /**
     * Delete a user
     *
     * @author Varun Shoor
     * @param string $_userName
     * @return bool
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteUser($_userName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_userName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'DeleteUser');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteUser(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->ResponseMetadata->ResponseId)) {
            return false;
        }

        return true;
    }

    /**
     * List user policies
     *
     * @author Varun Shoor
     * @param string $_userName
     * @param string $_marker (OPTIONAL)
     * @param int $_maxItems (OPTIONAL)
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ListUserPolicies($_userName, $_marker = null, $_maxItems = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'ListUserPolicies');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);

        if ($_marker !== null && $_marker !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('Marker', $_marker);
        }

        if ($_maxItems !== null && $_maxItems !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('MaxItems', $_maxItems);
        }

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ListUserPolicies(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_resultsContainer = array();

        foreach ($_BodyObject->ListUserPoliciesResult->PolicyNames->member as $_PolicyObject) {
            $_resultsContainer[] = (string) $_PolicyObject;
        }

        if (isset($_BodyObject->ListUserPoliciesResult->IsTruncated) && (string)$_BodyObject->ListUserPoliciesResult->IsTruncated == 'false')
        {
            return $_resultsContainer;
        }

        // If Max items isnt specified then we retrieve all users
        if ($_maxItems === null && isset($_BodyObject->ListUserPoliciesResult->Marker)) {
            $_lastMarker = (string) $_BodyObject->ListUserPoliciesResult->Marker;

            $_loopAttempts = 0;
            do {
                $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'ListUserPolicies');

                $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);

                $_SWIFT_AmazonIAMRequestObject->SetParameter('Marker', $_lastMarker);

                if ($_maxItems != null && $_maxItems !== '') {
                    $_SWIFT_AmazonIAMRequestObject->SetParameter('MaxItems', $_maxItems);
                }

                $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
                if (!$this->CheckResponse($_ResponseObject, 'ListUserPolicies(' . implode(', ', func_get_args()) . ')', 200))
                {
                    return false;
                }

                $_BodyObject = $_ResponseObject->GetBodyObject();

                foreach ($_BodyObject->ListUserPoliciesResult->PolicyNames->member as $_PolicyObject) {
                    $_resultsContainer[] = (string) $_PolicyObject;
                }

                if (isset($_BodyObject->ListUserPoliciesResult->Marker)) {
                    $_lastMarker = (string) $_BodyObject->ListUserPoliciesResult->Marker;
                } else {
                    $_lastMarker = false;
                }

                $_loopAttempts++;

            } while ($_ResponseObject !== false && $_lastMarker != false && (string)$_BodyObject->ListUserPoliciesResult->IsTruncated == 'true');
        }

        return $_resultsContainer;
    }

    /**
     * Create a user policy
     *
     * @author Varun Shoor
     * @param string $_userName
     * @param string $_policyDocument
     * @param string $_policyName
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function PutUserPolicy($_userName, $_policyDocument, $_policyName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userName) || empty($_policyDocument) || empty($_policyName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_POST, 'PutUserPolicy');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);
        $_SWIFT_AmazonIAMRequestObject->SetParameter('PolicyDocument', $_policyDocument);
        $_SWIFT_AmazonIAMRequestObject->SetParameter('PolicyName', $_policyName);

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'PutUserPolicy(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->ResponseMetadata)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->ResponseMetadata);

        return $_resultsContainer;
    }

    /**
     * Retrieve a user policy
     *
     * @author Varun Shoor
     * @param string $_userName
     * @param string $_policyName
     * @return string | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetUserPolicy($_userName, $_policyName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userName) || empty($_policyName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'GetUserPolicy');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);
        $_SWIFT_AmazonIAMRequestObject->SetParameter('PolicyName', $_policyName);

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'GetUserPolicy(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->GetUserPolicyResult->UserName)) {
            return false;
        }

        $_policyDocument = isset($_BodyObject->GetUserPolicyResult->PolicyDocument) ?
                           (string) $_BodyObject->GetUserPolicyResult->PolicyDocument : '';

        return urldecode($_policyDocument);
    }

    /**
     * Delete a user policy
     *
     * @author Varun Shoor
     * @param string $_userName
     * @param string $_policyName
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteUserPolicy($_userName, $_policyName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userName) || empty($_policyName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'DeleteUserPolicy');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);
        $_SWIFT_AmazonIAMRequestObject->SetParameter('PolicyName', $_policyName);

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteUserPolicy(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->ResponseMetadata)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->ResponseMetadata);

        return $_resultsContainer;
    }

    /**
     * Create an access key
     *
     * @author Varun Shoor
     * @param string $_userName (OPTIONAL)
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateAccessKey($_userName = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'CreateAccessKey');

        if ($_userName !== null && $_userName !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);
        }

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateAccessKey(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->CreateAccessKeyResult->AccessKey)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->CreateAccessKeyResult->AccessKey);

        return $_resultsContainer;
    }

    /**
     * List access keys
     *
     * @author Varun Shoor
     * @param string $_userName (OPTIONAL)
     * @param string $_marker (OPTIONAL)
     * @param int $_maxItems (OPTIONAL)
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ListAccessKeys($_userName = null, $_marker = null, $_maxItems = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'ListAccessKeys');

        if ($_userName !== null && $_userName !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);
        }

        if ($_marker !== null && $_marker !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('Marker', $_marker);
        }

        if ($_maxItems !== null && $_maxItems !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('MaxItems', $_maxItems);
        }

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ListAccessKeys(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_resultsContainer = array();

        foreach ($_BodyObject->ListAccessKeysResult->AccessKeyMetadata->member as $_AccessKeyObject) {
            $_resultsContainer[] = array('username' => (string)$_AccessKeyObject->UserName, 'accesskeyid' => (string)$_AccessKeyObject->AccessKeyId, 'status' => (string)$_AccessKeyObject->Status);
        }

        if (isset($_BodyObject->ListAccessKeysResult->IsTruncated) && (string)$_BodyObject->ListAccessKeysResult->IsTruncated == 'false')
        {
            return $_resultsContainer;
        }

        // If Max items isnt specified then we retrieve all access keys
        if ($_maxItems === null && isset($_BodyObject->ListAccessKeysResult->Marker)) {
            $_lastMarker = (string) $_BodyObject->ListAccessKeysResult->Marker;

            $_loopAttempts = 0;
            do {
                $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'ListAccessKeys');

                if ($_userName != null && $_userName !== '') {
                    $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);
                }

                $_SWIFT_AmazonIAMRequestObject->SetParameter('Marker', $_lastMarker);

                if ($_maxItems != null && $_maxItems !== '') {
                    $_SWIFT_AmazonIAMRequestObject->SetParameter('MaxItems', $_maxItems);
                }

                $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
                if (!$this->CheckResponse($_ResponseObject, 'ListAccessKeys(' . implode(', ', func_get_args()) . ')', 200))
                {
                    return false;
                }

                $_BodyObject = $_ResponseObject->GetBodyObject();

                foreach ($_BodyObject->ListAccessKeysResult->AccessKeyMetadata->member as $_AccessKeyObject) {
                    $_resultsContainer[] = array('username' => (string)$_AccessKeyObject->UserName, 'accesskeyid' => (string)$_AccessKeyObject->AccessKeyId, 'status' => (string)$_AccessKeyObject->Status);
                }

                if (isset($_BodyObject->ListAccessKeysResult->Marker)) {
                    $_lastMarker = (string) $_BodyObject->ListAccessKeysResult->Marker;
                } else {
                    $_lastMarker = false;
                }

                $_loopAttempts++;

            } while ($_ResponseObject !== false && $_lastMarker != false && (string)$_BodyObject->ListAccessKeysResult->IsTruncated == 'true');
        }

        return $_resultsContainer;
    }

    /**
     * Update an access key
     *
     * @author Varun Shoor
     * @param string $_accessKeyID
     * @param string $_status
     * @param string $_userName (OPTIONAL)
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateAccessKey($_accessKeyID, $_status, $_userName = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_accessKeyID) || empty($_status)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'UpdateAccessKey');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('AccessKeyId', $_accessKeyID);
        $_SWIFT_AmazonIAMRequestObject->SetParameter('Status', $_status);

        if ($_userName !== null && $_userName !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);
        }

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'UpdateAccessKey(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->ResponseMetadata->RequestId)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->ResponseMetadata);

        return $_resultsContainer;
    }

    /**
     * Delete an access key
     *
     * @author Varun Shoor
     * @param string $_accessKeyID
     * @param string $_userName (OPTIONAL)
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteAccessKey($_accessKeyID, $_userName = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_accessKeyID)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'DeleteAccessKey');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('AccessKeyId', $_accessKeyID);

        if ($_userName !== null && $_userName !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);
        }

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteAccessKey(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->ResponseMetadata->RequestId)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->ResponseMetadata);

        return $_resultsContainer;
    }

    /**
     * Retrieve the account summary
     *
     * @author Varun Shoor
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetAccountSummary() {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'GetAccountSummary');

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'GetAccountSummary(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->GetAccountSummaryResult->SummaryMap)) {
            return false;
        }

        $_resultsContainer = array();

        foreach ($_BodyObject->GetAccountSummaryResult->SummaryMap->entry as $_EntryObject) {
            $_resultsContainer[(string) $_EntryObject->key] = (string) $_EntryObject->value;
        }

        return $_resultsContainer;
    }

    /**
     * Create a login profile
     *
     * @author Varun Shoor
     * @param string $_userName
     * @param string $_password
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateLoginProfile($_userName, $_password) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userName) || empty($_password)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'CreateLoginProfile');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);
        $_SWIFT_AmazonIAMRequestObject->SetParameter('Password', $_password);

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateLoginProfile(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->ResponseMetadata->RequestId)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->ResponseMetadata);

        return $_resultsContainer;
    }

    /**
     * Update a login profile
     *
     * @author Varun Shoor
     * @param string $_userName
     * @param string $_password (OPTIONAL)
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateLoginProfile($_userName, $_password = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'UpdateLoginProfile');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);

        if ($_password !== null && $_password !== '') {
            $_SWIFT_AmazonIAMRequestObject->SetParameter('Password', $_password);
        }

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'UpdateLoginProfile(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->ResponseMetadata->RequestId)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->ResponseMetadata);

        return $_resultsContainer;
    }

    /**
     * Get a login profile
     *
     * @author Varun Shoor
     * @param string $_userName
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetLoginProfile($_userName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'GetLoginProfile');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'GetLoginProfile(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->GetLoginProfileResult->LoginProfile)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->GetLoginProfileResult->LoginProfile);

        return $_resultsContainer;
    }

    /**
     * Delete a login profile
     *
     * @author Varun Shoor
     * @param string $_userName
     * @return array | false
     * @throws SWIFT_AmazonIAM_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteLoginProfile($_userName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_userName)) {
            throw new SWIFT_AmazonIAM_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonIAMRequestObject = new SWIFT_AmazonIAMRequest(SWIFT_AmazonIAMRequest::ACTION_GET, 'DeleteLoginProfile');

        $_SWIFT_AmazonIAMRequestObject->SetParameter('UserName', $_userName);

        $_ResponseObject = $_SWIFT_AmazonIAMRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteLoginProfile(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (!isset($_BodyObject->ResponseMetadata->RequestId)) {
            return false;
        }

        $_resultsContainer = $this->ConvertXMLObjectToArray($_BodyObject->ResponseMetadata);

        return $_resultsContainer;
    }
}
?>
