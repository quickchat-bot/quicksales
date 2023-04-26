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
*
* Copyright (c) 2009, Dan Myers.
* Parts copyright (c) 2008, Donovan Schnknecht.
* All rights reserved.
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
*
* This is a modified BSD license (the third clause has been removed).
* The BSD license may be found here:
* http://www.opensource.org/licenses/bsd-license.php
*
* Amazon SQS is a trademark of Amazon.com, Inc. or its affiliates.
*
* SQS is based on Donovan Sch�nknecht's Amazon S3 PHP class, found here:
* http://undesigned.org.za/2007/10/22/amazon-s3-php-class
*/


/**
 * Amazon SQS PHP class
 *
 * @link http://sourceforge.net/projects/php-sqs/
 * @version 0.9.1
 */
class SWIFT_AmazonSQS extends SWIFT_Library {
    private $_useSSL = false;

    private $__accessKey; // AWS Access key
    private $__secretKey; // AWS Secret key
    private $_awsAccountNumber; // AWS Account Number
    private $_verifyHost = 2;
    private $_verifyPeer = 0;

    // Core Constants
    const BASE_URL = 'queue.amazonaws.com';

    /**
     * Constructor, used if you're not calling the class statically
     *
     * @param string $_accessKey Access key
     * @param string $_secretKey Secret key
     * @param string $_awsAccountNumber The AWS Account Number
     * @param boolean $_useSSL Whether or not to use SSLe
     * @throws SWIFT_AmazonSQS_Exception If the Class could not be loaded
     */
    public function __construct($_accessKey, $_secretKey, $_awsAccountNumber, $_useSSL = false) {
        parent::__construct();

        if (!$this->SetAccessKey($_accessKey) || !$this->SetSecretKey($_secretKey) || !$this->SetAWSAccountNumber($_awsAccountNumber))
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            $this->SetIsClassLoaded(false);
    }

        $this->SetCanUseSSL($_useSSL);
    }

    /**
     * Set the Access Key
     *
     * @author Varun Shoor
     * @param string $_accessKey Access key
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetAccessKey($_accessKey)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded
     */
    public function GetAccessKey()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetSecretKey($_secretKey)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded
     */
    public function GetSecretKey()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->__secretKey;
    }

    /**
     * Set the AWS Account Number
     *
     * @author Varun Shoor
     * @param string $_awsAccountNumber AWS Account Number
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetAWSAccountNumber($_awsAccountNumber)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_awsAccountNumber)) {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_awsAccountNumber = $_awsAccountNumber;

        return true;
    }

    /**
     * Get the AWS Account Number
     *
     * @author Varun Shoor
     * @return mixed "_awsAccountNumber" (STRING) on Success, "false" otherwise
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded
     */
    public function GetAWSAccountNumber()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_awsAccountNumber;
    }


    /**
     * Check to see if the user can use SSL
     *
     * @author Varun Shoor
     * @return int "1" on Success, "0" otherwise
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded
     */
    public function CanUseSSL()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);
        }

        return (int) ($this->_useSSL);
    }

    /**
     * Set the Can Use SSL property
     *
     * @author Varun Shoor
     * @param bool $_useSSL The Use SSL Property
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded
     */
    public function SetCanUseSSL($_useSSL)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

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
     * Check the Amazon SQS Response to make sure the error codes are right
     *
     * @author Varun Shoor
     * @param SWIFT_AmazonSQSResponse $_SWIFT_AmazonSQSResponseObject The SWIFT_AmazonSQSResponse Object Pointer
     * @param string $_callingFunction (OPTIONAL) The Name of Function Running this Check
     * @param int $_httpCode (OPTIONAL) The HTTP Code to Check Against
     * @param bool $_endExecution (BOOL) Whether to End the Execution if Error Encountered
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    private function CheckResponse(SWIFT_AmazonSQSResponse $_SWIFT_AmazonSQSResponseObject, $_callingFunction = '', $_httpCode = 200, $_endExecution = true)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!$_SWIFT_AmazonSQSResponseObject->GetIsClassLoaded()) {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if ($_SWIFT_AmazonSQSResponseObject->GetError() === false && $_SWIFT_AmazonSQSResponseObject->GetHTTPCode() !== $_httpCode)
        {
            $_SWIFT_AmazonSQSResponseObject->Error($_SWIFT_AmazonSQSResponseObject->GetHTTPCode(), 'Unexpected HTTP status');
        }

        if ($_SWIFT_AmazonSQSResponseObject->GetError() !== false) {

            if ($_endExecution)
            {
                $_errorContainer = $_SWIFT_AmazonSQSResponseObject->GetError();
                $_ErrorObject = $_SWIFT_AmazonSQSResponseObject->GetBodyObject();
                $_awsCode = '0';
                $_awsMessage = '';
                if (isset($_ErrorObject->Error->Code))
                {
                    $_awsCode = (string) $_ErrorObject->Error->Code;
                }

                if (isset($_ErrorObject->Error->Message))
                {
                    $_awsMessage = (string) $_ErrorObject->Error->Message;
                }

                // Ignore strings
                if (stristr($_errorContainer['message'], 'Problem with the SSL')) {
                    return false;
                } else if (strstr($_errorContainer['message'], 'resolve host')) {
                    return false;
                }

                throw new SWIFT_AmazonSQS_Exception(sprintf("SWIFT_AmazonSQS::". $_callingFunction .": [%s] %s" . "\n" . $_awsCode . ': ' . $_awsMessage, $_errorContainer['code'], $_errorContainer['message']), $_errorContainer['code']);

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
     * Get a list of queues
     *
     * @param string $_prefix String to use to filter the queue results
     * @return array | false
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded
     */
    public function ListQueues($_prefix = '') {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonSQSRequestObject = new SWIFT_AmazonSQSRequest(SWIFT_AmazonSQSRequest::ACTION_GET, 'ListQueues', '');

        if (!empty($_prefix))
        {
            $_SWIFT_AmazonSQSRequestObject->SetParameter('QueueNamePrefix', $_prefix);
        }

        $_ResponseObject = $_SWIFT_AmazonSQSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ListQueues(' . $_prefix . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();
        if (!isset($_BodyObject->ListQueuesResult, $_BodyObject->ListQueuesResult->QueueUrl))
        {
            return $_resultsContainer;
        }

        foreach($_BodyObject->ListQueuesResult->QueueUrl as $_Queue)
        {
            // We expect the queue name as URL
            $_urlPath = parse_url((string) $_Queue, PHP_URL_PATH);
            if (empty($_urlPath))
            {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_queueName = pathinfo($_urlPath, PATHINFO_BASENAME);
            $_resultsContainer[] = $_queueName;
        }

        return $_resultsContainer;
    }

    /**
     * Create a SQS Queue
     *
     * @param string $_queueName The Queue Name
     * @param int $_visibilityTimeout The Visibility Timeout for messages in this queue (IN SECONDS)
     * @return bool
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateQueue($_queueName, $_visibilityTimeout = 0) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_queueName)) {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_AmazonSQSRequestObject = new SWIFT_AmazonSQSRequest(SWIFT_AmazonSQSRequest::ACTION_PUT, 'CreateQueue', '');

        $_SWIFT_AmazonSQSRequestObject->SetParameter('QueueName', $_queueName);

        if (!empty($_visibilityTimeout))
        {
            if ($_visibilityTimeout > 7200)
            {
                $_visibilityTimeout = 7200;
            }

            $_SWIFT_AmazonSQSRequestObject->SetParameter('DefaultVisibilityTimeout', $_visibilityTimeout);
        }

        $_ResponseObject = $_SWIFT_AmazonSQSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateQueue(' . $_queueName . ', ' . $_visibilityTimeout . ')', 200))
        {
            return false;
        }

        return true;
    }

    /**
     * Delete a SQS Queue
     *
     * @param string $_queueName The Queue Name
     * @return bool
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteQueue($_queueName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_queueName)) {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_AmazonSQSRequestObject = new SWIFT_AmazonSQSRequest(SWIFT_AmazonSQSRequest::ACTION_DELETE, 'DeleteQueue', $_queueName);

        $_ResponseObject = $_SWIFT_AmazonSQSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteQueue(' . $_queueName . ')', 200))
        {
            return false;
        }

        return true;
    }

    /**
     * Get Attributes for the SQS Queue
     *
     * @param string $_queueName The Queue Name
     * @param string $_attribute Which attribute to retrieve (default is 'All')
     * @return array|false
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetQueueAttributes($_queueName, $_attribute = 'All') {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_queueName) || empty($_attribute)) {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonSQSRequestObject = new SWIFT_AmazonSQSRequest(SWIFT_AmazonSQSRequest::ACTION_GET, 'GetQueueAttributes', $_queueName);
        $_SWIFT_AmazonSQSRequestObject->SetParameter('AttributeName', $_attribute);

        $_ResponseObject = $_SWIFT_AmazonSQSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'GetQueueAttributes(' . $_queueName . ', ' . $_attribute . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_resultsContainer = array();
        if (!isset($_BodyObject->GetQueueAttributesResult, $_BodyObject->GetQueueAttributesResult->Attribute))
        {
            return $_resultsContainer;
        }

        foreach($_BodyObject->GetQueueAttributesResult->Attribute as $_Attribute)
        {
            $_resultsContainer[(string)($_Attribute->Name)] = (string)($_Attribute->Value);
        }

        return $_resultsContainer;
    }

    /**
     * Set attributes on a queue
     *
     * @param string $_queueName The queue for which to set attributes
     * @param string $_attribute The name of the attribute to set
     * @param string $_value The value of the attribute
     * @return boolean
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetQueueAttributes($_queueName, $_attribute, $_value) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_queueName) || empty($_attribute)) {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_AmazonSQSRequestObject = new SWIFT_AmazonSQSRequest(SWIFT_AmazonSQSRequest::ACTION_PUT, 'SetQueueAttributes', $_queueName);
        $_SWIFT_AmazonSQSRequestObject->SetParameter('Attribute.Name', $_attribute);
        $_SWIFT_AmazonSQSRequestObject->SetParameter('Attribute.Value', $_value);

        $_ResponseObject = $_SWIFT_AmazonSQSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'SetQueueAttributes(' . $_queueName . ', ' . $_attribute . ', ' . $_value . ')', 200))
        {
            return false;
        }

        return true;
    }

    /**
     * Send a message to a queue
     *
     * @param string $_queueName The queue which will receive the message
     * @param string $_messageBody The body of the message to send
     * @param int $_retryCounter (OPTIONAL) The Retry Attempt Counter
     * @return boolean
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SendMessage($_queueName, $_messageBody, $_retryCounter = 0) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_queueName) || empty($_messageBody)) {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_AmazonSQSRequestObject = new SWIFT_AmazonSQSRequest(SWIFT_AmazonSQSRequest::ACTION_PUT, 'SendMessage', $_queueName);
        $_SWIFT_AmazonSQSRequestObject->SetParameter('MessageBody', $_messageBody);

        $_ResponseObject = $_SWIFT_AmazonSQSRequestObject->GetResponse($this);

        $_responseResult = false;
        $_errorCode = 0;
        $_errorMessage = '';

        try
        {
            $_responseResult = $this->CheckResponse($_ResponseObject, 'SendMessage(' . $_queueName . ', ' . $_messageBody . ')', 200);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_errorCode = $_SWIFT_ExceptionObject->getCode();
            $_errorMessage = $_SWIFT_ExceptionObject->getMessage();
        }

        // If we failed then retry sending again.. limited to four retries
        if (!$_responseResult && !empty($_errorCode) && $_retryCounter < 4)
        {
            $_retryCounter++;
            if (isset($this->Console) && $this->Console instanceof SWIFT_Console) {
                $this->Console->Message('Message send failed because of: ' . $_errorMessage . ', retrying and sending..' . $_messageBody . ' to ' . $_queueName);
            }
            sleep(10);

            return $this->SendMessage($_queueName, $_messageBody, $_retryCounter);

        } else if (!$_responseResult) {
            throw new SWIFT_AmazonSQS_Exception($_errorMessage, $_errorCode);
        }

        return true;
    }

    /**
     * Receive a message from a queue
     *
     * @param string $_queueName The queue for which to retrieve attributes
     * @param integer $_numberOfMessages The maximum number of messages to retrieve
     * @param integer $_visibilityTimeout The visibility timeout of the retrieved message
     * @param int $_retryCounter (OPTIONAL) The Retry Attempt Counter
     * @return array|bool
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ReceiveMessage($_queueName, $_numberOfMessages = null, $_visibilityTimeout = null, $_retryCounter = 0) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_queueName)) {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AmazonSQSRequestObject = new SWIFT_AmazonSQSRequest(SWIFT_AmazonSQSRequest::ACTION_GET, 'ReceiveMessage', $_queueName);

        if (!empty($_numberOfMessages))
        {
            $_SWIFT_AmazonSQSRequestObject->SetParameter('MaxNumberOfMessages', $_numberOfMessages);
        }

        if (!empty($_visibilityTimeout))
        {
            $_SWIFT_AmazonSQSRequestObject->SetParameter('VisibilityTimeout', $_visibilityTimeout);
        }

        $_ResponseObject = $_SWIFT_AmazonSQSRequestObject->GetResponse($this);

        $_responseResult = false;
        $_errorCode = 0;
        $_errorMessage = '';

        try
        {
            $_responseResult = $this->CheckResponse($_ResponseObject, 'ReceiveMessage(' . $_queueName . ', ' . $_numberOfMessages . ', ' . $_visibilityTimeout . ')', 200);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_errorCode = $_SWIFT_ExceptionObject->getCode();
            $_errorMessage = $_SWIFT_ExceptionObject->getMessage();
        }

        // 28 = Operation timed out
        // 503 = Service unavailable
        // 7 = Couldnt connect to host
        if ($_errorCode == '28' || $_errorCode == '503' || $_errorCode == '7') {
            return false;
        }

        // If we failed with 500 then retry sending again.. limited to four retries
        if (!$_responseResult && $_errorCode == 500 && $_retryCounter < 4)
        {
            $_retryCounter++;
            sleep(60);

            return $this->ReceiveMessage($_queueName, $_numberOfMessages, $_visibilityTimeout);

        } else if (!$_responseResult) {
            throw new SWIFT_AmazonSQS_Exception($_errorMessage, $_errorCode);
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        $_resultsContainer = array();
        if (!isset($_BodyObject->ReceiveMessageResult, $_BodyObject->ReceiveMessageResult->Message))
        {
            return $_resultsContainer;
        }

        foreach ($_BodyObject->ReceiveMessageResult->Message as $_Message)
        {
            $_messageContainer = array();
            $_messageContainer['MessageID'] = (string) ($_Message->MessageId);
            $_messageContainer['ReceiptHandle'] = (string) ($_Message->ReceiptHandle);
            $_messageContainer['MD5OfBody'] = (string) ($_Message->MD5OfBody);
            $_messageContainer['Body'] = (string) ($_Message->Body);
            $_resultsContainer[] = $_messageContainer;
        }

        return $_resultsContainer;
    }

    /**
     * Delete a message from a queue
     *
     * @param string $_queueName The queue containing the message to delete
     * @param string $_receiptHandle The request id of the message to delete
     * @param int $_retryCounter (OPTIONAL) The Retry Attempt Counter
     * @return boolean
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteMessage($_queueName, $_receiptHandle, $_retryCounter = 0) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_queueName) || empty($_receiptHandle)) {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_AmazonSQSRequestObject = new SWIFT_AmazonSQSRequest(SWIFT_AmazonSQSRequest::ACTION_DELETE, 'DeleteMessage', $_queueName);
        $_SWIFT_AmazonSQSRequestObject->SetParameter('ReceiptHandle', $_receiptHandle);

        $_ResponseObject = $_SWIFT_AmazonSQSRequestObject->GetResponse($this);

        $_responseResult = false;
        $_errorCode = 0;
        $_errorMessage = '';

        try
        {
            $_responseResult = $this->CheckResponse($_ResponseObject, 'DeleteMessage(' . $_queueName . ', ' . $_receiptHandle . ')', 200);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_errorCode = $_SWIFT_ExceptionObject->getCode();
            $_errorMessage = $_SWIFT_ExceptionObject->getMessage();
        }

        // If we failed then retry delete again.. limited to four retries
        if (!$_responseResult && !empty($_errorCode) && $_retryCounter < 4)
        {
            $_retryCounter++;
            sleep(300);

            return $this->DeleteMessage($_queueName, $_receiptHandle, $_retryCounter);

        } else if (!$_responseResult) {
            throw new SWIFT_AmazonSQS_Exception($_errorMessage, $_errorCode);
        }

        return true;
    }

    /**
     * Change message visibility of a message
     *
     * @param string $_queueName The queue containing the message to delete
     * @param string $_receiptHandle The request id of the message to delete
     * @param int $_visibilityTimeout The new Visibility Timeout
     * @return boolean
     * @throws SWIFT_AmazonSQS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ChangeMessageVisibility($_queueName, $_receiptHandle, $_visibilityTimeout) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_queueName) || empty($_receiptHandle)) {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_AmazonSQSRequestObject = new SWIFT_AmazonSQSRequest(SWIFT_AmazonSQSRequest::ACTION_PUT, 'ChangeMessageVisibility', $_queueName);
        $_SWIFT_AmazonSQSRequestObject->SetParameter('ReceiptHandle', $_receiptHandle);
        $_SWIFT_AmazonSQSRequestObject->SetParameter('VisibilityTimeout', $_visibilityTimeout);

        $_ResponseObject = $_SWIFT_AmazonSQSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ChangeMessageVisibility(' . $_queueName . ', ' . $_receiptHandle . ', ' . $_visibilityTimeout . ')', 200))
        {
            return false;
        }

        return true;
    }
}
?>
