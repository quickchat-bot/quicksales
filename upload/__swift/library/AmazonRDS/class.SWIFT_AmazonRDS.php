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
 * Amazon RDS PHP class
 *
 * @author Varun Shoor
 */
class SWIFT_AmazonRDS extends SWIFT_Library {
    private $_useSSL = true;

    private $__accessKey; // AWS Access key
    private $__secretKey; // AWS Secret key
    private $_verifyHost = 2;
    private $_verifyPeer = 1;

    // Core Constants
    private $_baseURL = 'rds.amazonaws.com';

    const INSTANCE_M1SMALL = 'db.m1.small';
    const INSTANCE_M1LARGE = 'db.m1.large';
    const INSTANCE_M1XLARGE = 'db.m1.xlarge';
    const INSTANCE_M2XLARGE = 'db.m2.xlarge';
    const INSTANCE_M22XLARGE = 'db.m2.2xlarge';
    const INSTANCE_M24XLARGE = 'db.m2.4xlarge';

    const SOURCETYPE_DBINSTANCE = 'db-instance';
    const SOURCETYPE_DBSECURITYGROUP = 'db-security-group';
    const SOURCETYPE_DBPARAMETERGROUP = 'db-parameter-group';
    const SOURCETYPE_DBSNAPSHOT = 'db-snapshot';

    const ENGINE_MYSQL = 'MySQL';

    const DBPARAMETERGROUPFAMILY_MYSQL51 = 'MySQL5.1';

    const PARAMETERSOURCE_USER = 'user';
    const PARAMETERSOURCE_SYSTEM = 'system';
    const PARAMETERSOURCE_ENGINEDEFAULT = 'engine-default';

    const REGION_USEAST = 'rds.us-east-1.amazonaws.com';
    const REGION_USWEST = 'rds.us-west-1.amazonaws.com';
    const REGION_EUWEST = 'rds.eu-west-1.amazonaws.com';
    const REGION_ASIAPAC = 'rds.ap-southeast-1.amazonaws.com';

    /**
     * Constructor, used if you're not calling the class statically
     *
     * @param string $_accessKey Access key
     * @param string $_secretKey Secret key
     * @param boolean $_useSSL (OPTIONAL) Whether or not to use SSL
     * @param mixed $_region (OPTIONAL) The Regione
     * @throws SWIFT_AmazonRDS_Exception If the Class could not be loaded
     */
    public function __construct($_accessKey, $_secretKey, $_useSSL = true, $_region = self::REGION_USEAST) {
        parent::__construct();

        if (!$this->SetAccessKey($_accessKey) || !$this->SetSecretKey($_secretKey) || !$this->SetRegion($_region))
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetAccessKey($_accessKey)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded
     */
    public function GetAccessKey()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetSecretKey($_secretKey)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded
     */
    public function GetSecretKey()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->__secretKey;
    }

    /**
     * Check to see if the user can use SSL
     *
     * @author Varun Shoor
     * @return int "1" on Success, "0" otherwise
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded
     */
    public function CanUseSSL()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);
        }

        return (int) ($this->_useSSL);
    }

    /**
     * Set the Can Use SSL property
     *
     * @author Varun Shoor
     * @param bool $_useSSL The Use SSL Property
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded
     */
    public function SetCanUseSSL($_useSSL)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

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
     * Check the Amazon RDS Response to make sure the error codes are right
     *
     * @author Varun Shoor
     * @param SWIFT_AmazonRDSResponse $_SWIFT_AmazonRDSResponseObject The SWIFT_AmazonRDSResponse Object Pointer
     * @param string $_callingFunction (OPTIONAL) The Name of Function Running this Check
     * @param int $_httpCode (OPTIONAL) The HTTP Code to Check Against
     * @param bool $_endExecution (BOOL) Whether to End the Execution if Error Encountered
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    private function CheckResponse(SWIFT_AmazonRDSResponse $_SWIFT_AmazonRDSResponseObject, $_callingFunction = '', $_httpCode = 200, $_endExecution = true)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!$_SWIFT_AmazonRDSResponseObject->GetIsClassLoaded()) {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if ($_SWIFT_AmazonRDSResponseObject->GetError() === false && $_SWIFT_AmazonRDSResponseObject->GetHTTPCode() != $_httpCode)
        {
            $_SWIFT_AmazonRDSResponseObject->Error($_SWIFT_AmazonRDSResponseObject->GetHTTPCode(), 'Unexpected HTTP status (' . $_httpCode . ')');
        }

        if ($_SWIFT_AmazonRDSResponseObject->GetError() !== false) {

            if ($_endExecution)
            {
                $_errorContainer = $_SWIFT_AmazonRDSResponseObject->GetError();
                $_ErrorObject = $_SWIFT_AmazonRDSResponseObject->GetBodyObject();
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

                throw new SWIFT_AmazonRDS_Exception(sprintf("SWIFT_AmazonRDS::". $_callingFunction .": [%s] %s" . "\n" . $_awsCode . ': ' . $_awsMessage, $_errorContainer['code'], $_errorContainer['message']));

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
     * Check to see if its a valid DB Instance class
     *
     * @author Varun Shoor
     * @param mixed $_dbInstanceClass The DB Instance Class
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidDBInstanceClass($_dbInstanceClass)
    {
        if ($_dbInstanceClass == self::INSTANCE_M1SMALL || $_dbInstanceClass == self::INSTANCE_M1LARGE || $_dbInstanceClass == self::INSTANCE_M1XLARGE ||
                $_dbInstanceClass == self::INSTANCE_M2XLARGE || $_dbInstanceClass == self::INSTANCE_M22XLARGE || $_dbInstanceClass == self::INSTANCE_M24XLARGE)
        {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid db engine
     *
     * @author Varun Shoor
     * @param mixed $_dbEngine The Database Engine
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidDatabaseEngine($_dbEngine)
    {
        if ($_dbEngine == self::ENGINE_MYSQL)
        {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid source type
     *
     * @author Varun Shoor
     * @param mixed $_sourceType The Source Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidSourceType($_sourceType)
    {
        if ($_sourceType == self::SOURCETYPE_DBINSTANCE || $_sourceType == self::SOURCETYPE_DBPARAMETERGROUP || $_sourceType == self::SOURCETYPE_DBSECURITYGROUP ||
                $_sourceType == self::SOURCETYPE_DBSNAPSHOT)
        {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid DB Parameter Group Family
     *
     * @author Varun Shoor
     * @param mixed $_dbParameterGroupFamily The DB Parameter Group Family
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidDBParameterGroupFamily($_dbParameterGroupFamily)
    {
        if ($_dbParameterGroupFamily == self::DBPARAMETERGROUPFAMILY_MYSQL51)
        {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid parameter source
     *
     * @author Varun Shoor
     * @param mixed $_parameterSource The Parameter Source
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidParameterSource($_parameterSource)
    {
        if ($_parameterSource == self::PARAMETERSOURCE_ENGINEDEFAULT || $_parameterSource == self::PARAMETERSOURCE_SYSTEM ||
                $_parameterSource == self::PARAMETERSOURCE_USER)
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
     * Get a List of Database Instances
     *
     * @author Varun Shoor
     * @param string $_dbInstanceIdentifier (OPTIONAL) The Database Instance Identifier
     * @param mixed $_maxRecords (OPTIONAL) The Maximum records to retrieve, capped at 100
     * @param string $_marker (OPTIONAL) The marker of last instance received
     * @return array | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded
     */
    public function DescribeDBInstances($_dbInstanceIdentifier = null, $_maxRecords = null, $_marker = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'DescribeDBInstances');

        if ($_dbInstanceIdentifier !== null && $_dbInstanceIdentifier !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('DBInstanceIdentifier', $_dbInstanceIdentifier);
        }

        if ($_maxRecords !== null && $_maxRecords !== '' && $_maxRecords >= 20 && $_maxRecords <= 100)
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('MaxRecords', (int) ($_maxRecords));
        }

        if ($_marker !== null && $_marker !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('Marker', $_marker);
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeDBInstances(' . $_dbInstanceIdentifier . ',' . $_maxRecords . ' ,' . $_marker . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->DescribeDBInstancesResult->DBInstances->DBInstance))
        {
            return array();
        }

        $_index = 0;
        /** @var mixed $_DBInstanceObject */
        foreach ($_BodyObject->DescribeDBInstancesResult->DBInstances->DBInstance as $_DBInstanceObject) {
            $_resultsContainer[$_index] = array();

            $_resultsContainer[$_index]['latestrestorabletime'] = (string) $_DBInstanceObject->LatestRestorableTime;
            $_resultsContainer[$_index]['engine'] = (string) $_DBInstanceObject->Engine;
            $_resultsContainer[$_index]['backupretentionperiod'] = (string) $_DBInstanceObject->BackupRetentionPeriod;
            $_resultsContainer[$_index]['dbinstancestatus'] = (string) $_DBInstanceObject->DBInstanceStatus;
            $_resultsContainer[$_index]['multiaz'] = (string) $_DBInstanceObject->MultiAZ;
            $_resultsContainer[$_index]['dbinstanceidentifier'] = (string) $_DBInstanceObject->DBInstanceIdentifier;
            $_resultsContainer[$_index]['preferredbackupwindow'] = (string) $_DBInstanceObject->PreferredBackupWindow;
            $_resultsContainer[$_index]['preferredmaintenancewindow'] = (string) $_DBInstanceObject->PreferredMaintenanceWindow;
            $_resultsContainer[$_index]['availabilityzone'] = (string) $_DBInstanceObject->AvailabilityZone;
            $_resultsContainer[$_index]['instancecreatetime'] = (string) $_DBInstanceObject->InstanceCreateTime;
            $_resultsContainer[$_index]['allocatedstorage'] = (string) $_DBInstanceObject->AllocatedStorage;
            $_resultsContainer[$_index]['dbinstanceclass'] = (string) $_DBInstanceObject->DBInstanceClass;
            $_resultsContainer[$_index]['masterusername'] = (string) $_DBInstanceObject->MasterUsername;
            $_resultsContainer[$_index]['autominorversionupgrade'] = (string) $_DBInstanceObject->AutoMinorVersionUpgrade;

            if (isset($_DBInstanceObject->Endpoint)) {
                $_resultsContainer[$_index]['endpointaddress'] = (string) $_DBInstanceObject->Endpoint->Address;
                $_resultsContainer[$_index]['endpointport'] = (string) $_DBInstanceObject->Endpoint->Port;
            }

            // Process Databases
            $_resultsContainer[$_index]['databases'] = array();
            if (isset($_DBInstanceObject->DBName))
            {
                if (is_array($_DBInstanceObject->DBName)) {
                    foreach ($_DBInstanceObject->DBName as $_DBNameObject) {
                        $_resultsContainer[$_index]['databases'][] = $_DBNameObject;
                    }
                }
            }

            // Process Parameter Groups
            $_resultsContainer[$_index]['parametergroups'] = array();
            if (isset($_DBInstanceObject->DBParameterGroups->DBParameterGroup))
            {
                foreach ($_DBInstanceObject->DBParameterGroups->DBParameterGroup as $_DBParameterGroupObject) {
                    $_groupStatus = (string) $_DBParameterGroupObject->ParameterApplyStatus;
                    $_groupName = (string) $_DBParameterGroupObject->DBParameterGroupName;

                    $_resultsContainer[$_index]['parametergroups'][] = array('status' => $_groupStatus, 'name' => $_groupName);
                }
            }

            $_resultsContainer[$_index]['securitygroups'] = array();
            if (isset($_DBInstanceObject->DBSecurityGroups->DBSecurityGroup))
            {
                foreach ($_DBInstanceObject->DBSecurityGroups->DBSecurityGroup as $_DBSecurityGroupObject) {
                    $_groupStatus = (string) $_DBSecurityGroupObject->Status;
                    $_groupName = (string) $_DBSecurityGroupObject->DBSecurityGroupName;

                    $_resultsContainer[$_index]['securitygroups'][] = array('status' => $_groupStatus, 'name' => $_groupName);
                }
            }


            $_index++;
        }

        $_marker = false;
        if (isset($_BodyObject->Marker))
        {
            $_marker = (string) $_BodyObject->Marker;
        }

        return array($_marker, $_resultsContainer);
    }

    /**
     * Retrieve all the Database Instances and continue itterating till marker is done
     *
     * @author Varun Shoor
     * @param string $_marker (OPTIONAL) The marker of last instance received
     * @return array|bool
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function _DescribeAllDBInstances($_marker = null) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_dbInstanceContainer = array();

        $_fetchDBInstanceContainer = $this->DescribeDBInstances(null, null, $_marker);
        if (!is_array($_fetchDBInstanceContainer) || !isset($_fetchDBInstanceContainer[0]) || !isset($_fetchDBInstanceContainer[1])) {
            return false;
        }

        $_dbInstanceContainer = $_fetchDBInstanceContainer[1];

        // Do we have a marker?
        $_fetchMarker = $_fetchDBInstanceContainer[0];
        if (!empty($_fetchMarker)) {
            $_markerDBInstanceContainer = $this->_DescribeAllDBInstances($_fetchMarker);
            if (_is_array($_markerDBInstanceContainer)) {
                foreach ($_markerDBInstanceContainer as $_dbInstance) {
                    $_dbInstanceContainer[] = $_dbInstance;
                }
            }
        }

        return $_dbInstanceContainer;
    }

    /**
     * Create a Database Instance
     *
     * @author Varun Shoor
     * @param string $_dbInstanceIdentifier The Database Instance Identifier
     * @param int $_allocatedStorage The allocated storage for this instance in Gigabytes (5-1024)
     * @param mixed $_dbInstanceClass The Database Instance Class
     * @param mixed $_dbEngine The Database Engine
     * @param string $_masterUserName The Master User Name
     * @param string $_masterUserPassword The Master User Password
     * @param int $_port (OPTIONAL) The Port
     * @param bool $_isMultiAZ (OPTIONAL) Whether its a multi az deployment
     * @param string $_availabilityZone (OPTIONAL) The Availability Zone
     * @param string $_dbEngineVersion (OPTIONAL) The Database Engine Version
     * @param string $_dbName (OPTIONAL) The DB to Create when Instance is Created
     * @param string $_dbParameterGroup (OPTIONAL) The DB Parameter Group
     * @param array $_dbSecurityGroupList (OPTIONAL) The DB Security Group List
     * @param string $_preferredMaintenanceWindow (OPTIONAL) The Preferred Maintenance Window
     * @param int $_backupRetentionPeriod (OPTIONAL) The Backup Retention Period. Number of days 0-8
     * @param string $_preferredBackupWindow (OPTIONAL) The Preffered Backup Window
     * @param bool $_autoMinorVersionUpgrade (OPTIONAL) Indicates that upgrades will be applied automatically during
     *     the maintenance window
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateDBInstance($_dbInstanceIdentifier, $_allocatedStorage, $_dbInstanceClass, $_dbEngine, $_masterUserName, $_masterUserPassword,
            $_port = 3306, $_isMultiAZ = false, $_availabilityZone = null, $_dbEngineVersion = null, $_dbName = null, $_dbParameterGroup = null, $_dbSecurityGroupList = null,
            $_preferredMaintenanceWindow = null, $_backupRetentionPeriod = null, $_preferredBackupWindow = null, $_autoMinorVersionUpgrade = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbInstanceIdentifier) || strlen($_dbInstanceIdentifier) > 63) {
            throw new SWIFT_Exception('Invalid DB Instance Identifier');
        } else if (empty($_masterUserName) || empty($_masterUserPassword) || strlen($_masterUserName) > 15 || strlen($_masterUserPassword) > 16 ||
                strlen($_masterUserPassword) < 4) {
            throw new SWIFT_Exception('Invalid Master User or Password');
        } else if (!self::IsValidDBInstanceClass($_dbInstanceClass)) {
            throw new SWIFT_Exception('Invalid DB Instance Class');
        } else if (!self::IsValidDatabaseEngine($_dbEngine)) {
            throw new SWIFT_Exception('Invalid DB Engine');
        } else if ($_port < 1150 || $_port > 65535) {
            throw new SWIFT_Exception('Invalid Port Value');
        } else if ($_isMultiAZ == true && $_availabilityZone !== null && $_availabilityZone !== '') {
            throw new SWIFT_Exception('You cannot specify Multi-AZ with Availability Zone');
        }

        if ($_allocatedStorage < 5)
        {
            $_allocatedStorage = 5;
        } else if ($_allocatedStorage > 1024) {
            $_allocatedStorage = 1024;
        }


        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'CreateDBInstance');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBInstanceIdentifier', $_dbInstanceIdentifier);
        $_SWIFT_AmazonRDSRequestObject->SetParameter('AllocatedStorage', $_allocatedStorage);
        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBInstanceClass', $_dbInstanceClass);
        $_SWIFT_AmazonRDSRequestObject->SetParameter('Engine', $_dbEngine);
        $_SWIFT_AmazonRDSRequestObject->SetParameter('MasterUsername', $_masterUserName);
        $_SWIFT_AmazonRDSRequestObject->SetParameter('MasterUserPassword', $_masterUserPassword);
        $_SWIFT_AmazonRDSRequestObject->SetParameter('Port', ($_port));

        $_finalIsMultiAZ = 'false';
        if ($_isMultiAZ == true)
        {
            $_finalIsMultiAZ = 'true';
        }
        $_SWIFT_AmazonRDSRequestObject->SetParameter('MultiAZ', $_finalIsMultiAZ);

        if ($_availabilityZone !== null && $_availabilityZone !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('AvailabilityZone', $_availabilityZone);
        }

        if ($_dbEngineVersion !== null && $_dbEngineVersion !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('EngineVersion', $_dbEngineVersion);
        }

        if ($_dbName !== null && $_dbName !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('DBName', $_dbName);
        }

        if ($_dbParameterGroup !== null && $_dbParameterGroup !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('DBParameterGroup', $_dbParameterGroup);
        }

        if ($_dbSecurityGroupList !== null && _is_array($_dbSecurityGroupList))
        {
            $_index = 1;
            foreach ($_dbSecurityGroupList as $_dbSecurityGroup) {
                $_SWIFT_AmazonRDSRequestObject->SetParameter('DBSecurityGroups.member.' . $_index, $_dbSecurityGroup);

                $_index++;
            }
        }

        if ($_preferredMaintenanceWindow !== null && $_preferredMaintenanceWindow !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('PreferredMaintenanceWindow', $_preferredMaintenanceWindow);
        }

        if ($_backupRetentionPeriod !== null && $_backupRetentionPeriod !== '' && $_backupRetentionPeriod >= 0 && $_backupRetentionPeriod <= 8)
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('BackupRetentionPeriod', ($_backupRetentionPeriod));
        }

        if ($_preferredBackupWindow !== null && $_preferredBackupWindow !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('PreferredBackupWindow', $_preferredBackupWindow);
        }

        if ($_autoMinorVersionUpgrade !== null && $_autoMinorVersionUpgrade !== '')
        {
            $_finalAutoMinorVersionUpgrade = 'true';
            if ($_autoMinorVersionUpgrade === false)
            {
                $_finalAutoMinorVersionUpgrade = 'false';
            }

            $_SWIFT_AmazonRDSRequestObject->SetParameter('AutoMinorVersionUpgrade', $_finalAutoMinorVersionUpgrade);
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateDBInstance(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->CreateDBInstanceResult->DBInstance->DBInstanceIdentifier))
        {
            $_instanceIdentifier = (string) $_BodyObject->CreateDBInstanceResult->DBInstance->DBInstanceIdentifier;
            if ($_instanceIdentifier == $_dbInstanceIdentifier)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Create a Database Instance Read Replica
     *
     * @author Varun Shoor
     * @param string $_dbInstanceIdentifier The Database Instance Identifier
     * @param string $_sourceDBInstanceIdentifier The Database Instance Identifier
     * @param mixed $_dbInstanceClass (OPTIONAL) The Database Instance Class
     * @param int $_port (OPTIONAL) The Port
     * @param string $_availabilityZone (OPTIONAL) The Availability Zone
     * @param bool $_autoMinorVersionUpgrade (OPTIONAL) Indicates that upgrades will be applied automatically during
     *     the maintenance window
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateDBInstanceReadReplica($_dbInstanceIdentifier, $_sourceDBInstanceIdentifier, $_dbInstanceClass = null, $_port = 3306, $_availabilityZone = null,
            $_autoMinorVersionUpgrade = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbInstanceIdentifier) || strlen($_dbInstanceIdentifier) > 63 || empty($_sourceDBInstanceIdentifier) || strlen($_sourceDBInstanceIdentifier) > 63) {
            throw new SWIFT_Exception('Invalid DB Instance Identifier');
        } else if ($_dbInstanceClass !== null && !self::IsValidDBInstanceClass($_dbInstanceClass)) {
            throw new SWIFT_Exception('Invalid DB Instance Class');
        } else if ($_port < 1150 || $_port > 65535) {
            throw new SWIFT_Exception('Invalid Port Value');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'CreateDBInstanceReadReplica');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBInstanceIdentifier', $_dbInstanceIdentifier);
        $_SWIFT_AmazonRDSRequestObject->SetParameter('SourceDBInstanceIdentifier', $_sourceDBInstanceIdentifier);

        if ($_dbInstanceClass !== null) {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('DBInstanceClass', $_dbInstanceClass);
        }

        $_SWIFT_AmazonRDSRequestObject->SetParameter('Port', $_port);

        if ($_availabilityZone !== null && $_availabilityZone !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('AvailabilityZone', $_availabilityZone);
        }

        if ($_autoMinorVersionUpgrade !== null && $_autoMinorVersionUpgrade !== '')
        {
            $_finalAutoMinorVersionUpgrade = 'true';
            if ($_autoMinorVersionUpgrade === false)
            {
                $_finalAutoMinorVersionUpgrade = 'false';
            }

            $_SWIFT_AmazonRDSRequestObject->SetParameter('AutoMinorVersionUpgrade', $_finalAutoMinorVersionUpgrade);
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateDBInstanceReadReplica(' . print_r(func_get_args(), true) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->CreateDBInstanceReadReplicaResult->DBInstance->DBInstanceIdentifier))
        {
            $_instanceIdentifier = (string) $_BodyObject->CreateDBInstanceReadReplicaResult->DBInstance->DBInstanceIdentifier;
            if ($_instanceIdentifier == $_dbInstanceIdentifier)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Modify a Database Instance
     *
     * @author Varun Shoor
     * @param string $_dbInstanceIdentifier The Database Instance Identifier
     * @param string $_dbParameterGroupName (OPTIONAL) The DB Parameter Group
     * @param array $_dbSecurityGroupList (OPTIONAL) The DB Security Group List
     * @param string $_preferredMaintenanceWindow (OPTIONAL) The Preferred Maintenance Window
     * @param string $_masterUserPassword (OPTIONAL) The Master User Password
     * @param int $_allocatedStorage (OPTIONAL) The allocated storage for this instance in Gigabytes (5-1024)
     * @param mixed $_dbInstanceClass (OPTIONAL) The Database Instance Class
     * @param bool $_isMultiAZ (OPTIONAL) Whether this is a Mutli AZ deployment
     * @param int $_backupRetentionPeriod (OPTIONAL) The Backup Retention Period. Number of days 0-8
     * @param string $_preferredBackupWindow (OPTIONAL) The Preffered Backup Window
     * @param string $_dbEngineVersion (OPTIONAL) The DB Engine Version
     * @param bool $_autoMinorVersionUpgrade (OPTIONAL) Whether to run minor upgrades automatically during maintenance
     *     period
     * @param bool $_allowMajorVersionUpgrade (OPTIONAL) Whether major version upgrades are allowed
     * @param bool $_applyImmediately (OPTIONAL) Whether to apply the changes immediately or not
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ModifyDBInstance($_dbInstanceIdentifier, $_dbParameterGroupName = null, $_dbSecurityGroupList = null, $_preferredMaintenanceWindow = null,
            $_masterUserPassword = null, $_allocatedStorage = null, $_dbInstanceClass = null, $_isMultiAZ = null, $_backupRetentionPeriod = null,
            $_preferredBackupWindow = null, $_dbEngineVersion = null, $_autoMinorVersionUpgrade = null, $_allowMajorVersionUpgrade = null, $_applyImmediately = true) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbInstanceIdentifier) || strlen($_dbInstanceIdentifier) > 63) {
            throw new SWIFT_Exception('Invalid DB Instance Identifier');
        } else if ($_masterUserPassword !== null && (strlen($_masterUserPassword) > 16 || strlen($_masterUserPassword) < 4)) {
            throw new SWIFT_Exception('Invalid Master User or Password');
        } else if ($_dbInstanceClass !== null && !self::IsValidDBInstanceClass($_dbInstanceClass)) {
            throw new SWIFT_Exception('Invalid DB Instance Class');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'ModifyDBInstance');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBInstanceIdentifier', $_dbInstanceIdentifier);


        if ($_dbParameterGroupName !== null && $_dbParameterGroupName !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('DBParameterGroupName', $_dbParameterGroupName);
        }

        if ($_dbSecurityGroupList !== null && _is_array($_dbSecurityGroupList))
        {
            foreach ($_dbSecurityGroupList as $_dbSecurityGroup) {
                $_SWIFT_AmazonRDSRequestObject->SetParameter('DBSecurityGroups', $_dbSecurityGroup);
            }
        }

        if ($_preferredMaintenanceWindow !== null && $_preferredMaintenanceWindow !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('PreferredMaintenanceWindow', $_preferredMaintenanceWindow);
        }

        if ($_masterUserPassword != null && $_masterUserPassword != '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('MasterUserPassword', $_masterUserPassword);
        }

        if ($_allocatedStorage !== null && $_allocatedStorage !== '')
        {
            if ($_allocatedStorage < 5)
            {
                $_allocatedStorage = 5;
            } else if ($_allocatedStorage > 1024) {
                $_allocatedStorage = 1024;
            }

            $_SWIFT_AmazonRDSRequestObject->SetParameter('AllocatedStorage', $_allocatedStorage);
        }

        if ($_dbInstanceClass !== null && $_dbInstanceClass !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('DBInstanceClass', $_dbInstanceClass);
        }

        if ($_isMultiAZ !== null && $_isMultiAZ !== '')
        {
            $_finalIsMultiAZ = 'false';
            if ($_isMultiAZ == true)
            {
                $_finalIsMultiAZ = 'true';
            }
            $_SWIFT_AmazonRDSRequestObject->SetParameter('MultiAZ', $_finalIsMultiAZ);
        }

        if ($_backupRetentionPeriod !== null && $_backupRetentionPeriod !== '' && $_backupRetentionPeriod >= 0 && $_backupRetentionPeriod <= 8)
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('BackupRetentionPeriod', $_backupRetentionPeriod);
        }

        if ($_preferredBackupWindow !== null && $_preferredBackupWindow !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('PreferredBackupWindow', $_preferredBackupWindow);
        }

        if ($_dbEngineVersion !== null && $_dbEngineVersion !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('EngineVersion', $_dbEngineVersion);
        }

        if ($_autoMinorVersionUpgrade !== null && $_autoMinorVersionUpgrade !== '')
        {
            $_finalAutoMinorVersionUpgrade = 'true';
            if ($_autoMinorVersionUpgrade === false)
            {
                $_finalAutoMinorVersionUpgrade = 'false';
            }

            $_SWIFT_AmazonRDSRequestObject->SetParameter('AutoMinorVersionUpgrade', $_finalAutoMinorVersionUpgrade);
        }

        if ($_allowMajorVersionUpgrade !== null && $_allowMajorVersionUpgrade !== '')
        {
            $_finalAllowMajorVersionUpgrade = 'true';
            if ($_allowMajorVersionUpgrade === false)
            {
                $_finalAllowMajorVersionUpgrade = 'false';
            }

            $_SWIFT_AmazonRDSRequestObject->SetParameter('AllowMajorVersionUpgrade', $_finalAllowMajorVersionUpgrade);
        }

        $_finalApplyImmediately = 'true';
        if ($_applyImmediately == false)
        {
            $_finalApplyImmediately = 'false';
        }

        $_SWIFT_AmazonRDSRequestObject->SetParameter('ApplyImmediately', $_finalApplyImmediately);

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ModifyDBInstance(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->ModifyDBInstanceResult->DBInstance->DBInstanceIdentifier))
        {
            $_instanceIdentifier = (string) $_BodyObject->ModifyDBInstanceResult->DBInstance->DBInstanceIdentifier;
            if ($_instanceIdentifier == $_dbInstanceIdentifier)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete a Database Instance
     *
     * @author Varun Shoor
     * @param string $_dbInstanceIdentifier The Database Instance Identifier
     * @param bool $_skipFinalSnapshot (OPTIONAL) Whether to skip final snapshot creation
     * @param string $_finalDBSnapshotIdentifier (OPTIONAL) The Final DB Snapshot Identifier
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteDBInstance($_dbInstanceIdentifier, $_skipFinalSnapshot = false, $_finalDBSnapshotIdentifier = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbInstanceIdentifier) || strlen($_dbInstanceIdentifier) > 63) {
            throw new SWIFT_Exception('Invalid DB Instance Identifier');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'DeleteDBInstance');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBInstanceIdentifier', $_dbInstanceIdentifier);

        $_finalSkipFinalSnapshot = 'false';
        $_snapshotIdentifier = 'final-' . $_dbInstanceIdentifier;
        if ($_skipFinalSnapshot)
        {
            $_finalSkipFinalSnapshot = 'true';
        } else {
            if ($_finalDBSnapshotIdentifier !== null && $_finalDBSnapshotIdentifier !== '')
            {
                $_snapshotIdentifier = $_finalDBSnapshotIdentifier;
            }
        }

        $_SWIFT_AmazonRDSRequestObject->SetParameter('SkipFinalSnapshot', $_finalSkipFinalSnapshot);
        $_SWIFT_AmazonRDSRequestObject->SetParameter('FinalDBSnapshotIdentifier', $_snapshotIdentifier);


        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteDBInstance(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->DeleteDBInstanceResult->DBInstance->DBInstanceIdentifier))
        {
            $_instanceIdentifier = (string) $_BodyObject->DeleteDBInstanceResult->DBInstance->DBInstanceIdentifier;
            if ($_instanceIdentifier == $_dbInstanceIdentifier)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Reboot a Database Instance
     *
     * @author Varun Shoor
     * @param string $_dbInstanceIdentifier The Database Instance Identifier
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RebootDBInstance($_dbInstanceIdentifier) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbInstanceIdentifier) || strlen($_dbInstanceIdentifier) > 63) {
            throw new SWIFT_Exception('Invalid DB Instance Identifier');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'RebootDBInstance');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBInstanceIdentifier', $_dbInstanceIdentifier);

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'RebootDBInstance(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->RebootDBInstanceResult->DBInstance->DBInstanceIdentifier))
        {
            $_instanceIdentifier = (string) $_BodyObject->RebootDBInstanceResult->DBInstance->DBInstanceIdentifier;
            if ($_instanceIdentifier == $_dbInstanceIdentifier)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Create a Snapshot for Database Instance
     *
     * @author Varun Shoor
     * @param string $_dbSnapshotIdentifier The Snapshot Identifier Name
     * @param string $_dbInstanceIdentifier The Database Instance Identifier
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateDBSnapshot($_dbSnapshotIdentifier, $_dbInstanceIdentifier) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbInstanceIdentifier) || strlen($_dbInstanceIdentifier) > 63) {
            throw new SWIFT_Exception('Invalid DB Instance Identifier');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'CreateDBSnapshot');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBInstanceIdentifier', $_dbInstanceIdentifier);
        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBSnapshotIdentifier', $_dbSnapshotIdentifier);

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateDBSnapshot(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->CreateDBSnapshotResult->DBSnapshot->DBInstanceIdentifier) && isset($_BodyObject->CreateDBSnapshotResult->DBSnapshot->DBSnapshotIdentifier))
        {
            $_instanceIdentifier = (string) $_BodyObject->CreateDBSnapshotResult->DBSnapshot->DBInstanceIdentifier;
            $_snapshotIdentifier = (string) $_BodyObject->CreateDBSnapshotResult->DBSnapshot->DBSnapshotIdentifier;
            if ($_instanceIdentifier == $_dbInstanceIdentifier && $_snapshotIdentifier == $_dbSnapshotIdentifier)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a List of Database Snapshots
     *
     * @author Varun Shoor
     * @param string $_dbInstanceIdentifier The Database Instance Identifier
     * @param string $_dbSnapshotIdentifier (OPTIONAL) The Database Snapshot Identifier
     * @param int $_maxRecords (OPTIONAL) The Maximum records to retrieve, capped at 100
     * @param string $_marker (OPTIONAL) The marker of last instance received
     * @return array | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded
     */
    public function DescribeDBSnapshots($_dbInstanceIdentifier, $_dbSnapshotIdentifier = null, $_maxRecords = null, $_marker = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_dbInstanceIdentifier) || strlen($_dbInstanceIdentifier) > 63) {
            throw new SWIFT_Exception('Invalid DB Instance Identifier');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'DescribeDBSnapshots');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBInstanceIdentifier', $_dbInstanceIdentifier);

        if ($_dbSnapshotIdentifier !== null && $_dbSnapshotIdentifier !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('DBSnapshotIdentifier', $_dbSnapshotIdentifier);
        }

        if ($_maxRecords !== null && $_maxRecords !== '' && $_maxRecords >= 20 && $_maxRecords <= 100)
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('MaxRecords', $_maxRecords);
        }

        if ($_marker !== null && $_marker !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('Marker', $_marker);
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeDBSnapshots(' . $_dbInstanceIdentifier . ', ' . $_dbSnapshotIdentifier . ', ' . $_maxRecords . ', ' . $_marker . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->DescribeDBSnapshotsResult->DBSnapshots->DBSnapshot))
        {
            return array();
        }

        $_index = 0;
        foreach ($_BodyObject->DescribeDBSnapshotsResult->DBSnapshots->DBSnapshot as $_DBSnapshotObject) {
            $_resultsContainer[$_index] = array();

            $_resultsContainer[$_index]['snapshotcreatetime'] = (string) $_DBSnapshotObject->SnapshotCreateTime;
            $_resultsContainer[$_index]['port'] = (string) $_DBSnapshotObject->Port;
            $_resultsContainer[$_index]['status'] = (string) $_DBSnapshotObject->Status;
            $_resultsContainer[$_index]['engine'] = (string) $_DBSnapshotObject->Engine;
            $_resultsContainer[$_index]['availabilityzone'] = (string) $_DBSnapshotObject->AvailabilityZone;
            $_resultsContainer[$_index]['instancecreatetime'] = (string) $_DBSnapshotObject->InstanceCreateTime;
            $_resultsContainer[$_index]['allocatedstorage'] = (string) $_DBSnapshotObject->AllocatedStorage;
            $_resultsContainer[$_index]['dbinstanceidentifier'] = (string) $_DBSnapshotObject->DBInstanceIdentifier;
            $_resultsContainer[$_index]['masterusername'] = (string) $_DBSnapshotObject->MasterUsername;
            $_resultsContainer[$_index]['dbsnapshotidentifier'] = (string) $_DBSnapshotObject->DBSnapshotIdentifier;

            $_index++;
        }

        $_marker = false;
        if (isset($_BodyObject->Marker))
        {
            $_marker = (string) $_BodyObject->Marker;
        }

        return array($_marker, $_resultsContainer);
    }

    /**
     * Delete a Snapshot
     *
     * @author Varun Shoor
     * @param string $_dbSnapshotIdentifier The Snapshot Identifier Name
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteDBSnapshot($_dbSnapshotIdentifier) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbSnapshotIdentifier)) {
            throw new SWIFT_Exception('Invalid DB Snapshot Identifier');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'DeleteDBSnapshot');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBSnapshotIdentifier', $_dbSnapshotIdentifier);

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteDBSnapshot(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->DeleteDBSnapshotResult->DBSnapshot->DBSnapshotIdentifier))
        {
            $_snapshotIdentifier = (string) $_BodyObject->DeleteDBSnapshotResult->DBSnapshot->DBSnapshotIdentifier;
            if ($_snapshotIdentifier == $_dbSnapshotIdentifier)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Restore a Database Instance from a DB Snapshot
     *
     * @author Varun Shoor
     * @param string $_dbSnapshotIdentifier The Database Snapshot Identifier
     * @param string $_dbInstanceIdentifier The Database Instance Identifier
     * @param mixed $_dbInstanceClass The Database Instance Class
     * @param int $_port (OPTIONAL) The Port
     * @param bool $_isMultiAZ (OPTIONAL) Whether its a multi az deployment
     * @param string $_availabilityZone (OPTIONAL) The Availability Zone
     * @param bool $_autoMinorVersionUpgrade (OPTIONAL) Indicates that upgrades will be applied automatically during
     *     the maintenance window
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RestoreDBInstanceFromDBSnapshot($_dbSnapshotIdentifier, $_dbInstanceIdentifier, $_dbInstanceClass, $_port = 3306, $_isMultiAZ = false,
            $_availabilityZone = null, $_autoMinorVersionUpgrade = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbSnapshotIdentifier)) {
            throw new SWIFT_Exception('Invalid DB Snapshot Identifier');
        } else if (empty($_dbInstanceIdentifier) || strlen($_dbInstanceIdentifier) > 63) {
            throw new SWIFT_Exception('Invalid DB Instance Identifier');
        } else if (!self::IsValidDBInstanceClass($_dbInstanceClass)) {
            throw new SWIFT_Exception('Invalid DB Instance Class');
        } else if ($_port < 1150 || $_port > 65535) {
            throw new SWIFT_Exception('Invalid Port Value');
        } else if ($_isMultiAZ == true && $_availabilityZone !== null && $_availabilityZone !== '') {
            throw new SWIFT_Exception('You cannot specify Multi-AZ with Availability Zone');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'RestoreDBInstanceFromDBSnapshot');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBSnapshotIdentifier', $_dbSnapshotIdentifier);
        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBInstanceIdentifier', $_dbInstanceIdentifier);
        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBInstanceClass', $_dbInstanceClass);

        $_SWIFT_AmazonRDSRequestObject->SetParameter('Port', $_port);

        $_finalIsMultiAZ = 'false';
        if ($_isMultiAZ == true)
        {
            $_finalIsMultiAZ = 'true';
        }
        $_SWIFT_AmazonRDSRequestObject->SetParameter('MultiAZ', $_finalIsMultiAZ);

        if ($_autoMinorVersionUpgrade !== null && $_autoMinorVersionUpgrade !== '')
        {
            $_finalAutoMinorVersionUpgrade = 'true';
            if ($_autoMinorVersionUpgrade === false)
            {
                $_finalAutoMinorVersionUpgrade = 'false';
            }

            $_SWIFT_AmazonRDSRequestObject->SetParameter('AutoMinorVersionUpgrade', $_finalAutoMinorVersionUpgrade);
        }

        if ($_availabilityZone !== null && $_availabilityZone !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('AvailabilityZone', $_availabilityZone);
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'RestoreDBInstanceFromDBSnapshot(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->RestoreDBInstanceFromDBSnapshotResult->DBInstance->DBInstanceIdentifier))
        {
            $_instanceIdentifier = (string) $_BodyObject->RestoreDBInstanceFromDBSnapshotResult->DBInstance->DBInstanceIdentifier;
            if ($_instanceIdentifier == $_dbInstanceIdentifier)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Restore a Database Instance to a Point in Time
     *
     * @author Varun Shoor
     * @param string $_sourceDBInstanceIdentifier The Source Database Instance Identifier
     * @param string $_targetDatabaseInstanceIdentifier THe Target Database Instance Identifier
     * @param bool $_useLatestRestorableTime (CONDITIONAL) Whether to use the last possible restorable time
     * @param string $_restoreTime (CONDITIONAL) Specify a fix restore time
     * @param mixed $_dbInstanceClass (OPTIONAL) The Database Instance Class
     * @param int $_port (OPTIONAL) The Port
     * @param bool $_isMultiAZ (OPTIONAL) Whether its a multi az deployment
     * @param string $_availabilityZone (OPTIONAL) The Availability Zone
     * @param bool $_autoMinorVersionUpgrade (OPTIONAL) Indicates that upgrades will be applied automatically during
     *     the maintenance window
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RestoreDBInstanceToPointInTime($_sourceDBInstanceIdentifier, $_targetDatabaseInstanceIdentifier, $_useLatestRestorableTime = null, $_restoreTime = null,
            $_dbInstanceClass = null, $_port = 3306, $_isMultiAZ = false, $_availabilityZone = null, $_autoMinorVersionUpgrade = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_sourceDBInstanceIdentifier) || strlen($_sourceDBInstanceIdentifier) > 63) {
            throw new SWIFT_Exception('Invalid Source DB Instance Identifier');
        } else if (empty($_targetDatabaseInstanceIdentifier) || strlen($_targetDatabaseInstanceIdentifier) > 63) {
            throw new SWIFT_Exception('Invalid Target DB Instance Identifier');
        } else if ($_dbInstanceClass !== null && !self::IsValidDBInstanceClass($_dbInstanceClass)) {
            throw new SWIFT_Exception('Invalid DB Instance Class');
        } else if ($_useLatestRestorableTime === null && $_restoreTime === null) {
            throw new SWIFT_Exception('Need to specify atleast one conditional argument');
        } else if ($_port < 1150 || $_port > 65535) {
            throw new SWIFT_Exception('Invalid Port Value');
        } else if ($_isMultiAZ == true && $_availabilityZone !== null && $_availabilityZone !== '') {
            throw new SWIFT_Exception('You cannot specify Multi-AZ with Availability Zone');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'RestoreDBInstanceToPointInTime');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('SourceDBInstanceIdentifier', $_sourceDBInstanceIdentifier);
        $_SWIFT_AmazonRDSRequestObject->SetParameter('TargetDBInstanceIdentifier', $_targetDatabaseInstanceIdentifier);

        $_SWIFT_AmazonRDSRequestObject->SetParameter('Port', $_port);

        $_useLatestRestorableTimeString = 'false';

        if ($_useLatestRestorableTime !== null && $_useLatestRestorableTime !== '' && $_useLatestRestorableTime == true)
        {
            $_useLatestRestorableTimeString = 'true';
        }

        if ($_useLatestRestorableTimeString == 'true')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('UseLatestRestorableTime', $_useLatestRestorableTimeString);
        } else if ($_restoreTime !== null && $_restoreTime !== '') {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('RestoreTime', $_restoreTime);
        }

        if ($_dbInstanceClass !== null && $_dbInstanceClass !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('DBInstanceClass', $_dbInstanceClass);
        }

        if ($_availabilityZone !== null && $_availabilityZone !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('AvailabilityZone', $_availabilityZone);
        }

        $_finalIsMultiAZ = 'false';
        if ($_isMultiAZ == true)
        {
            $_finalIsMultiAZ = 'true';
        }
        $_SWIFT_AmazonRDSRequestObject->SetParameter('MultiAZ', $_finalIsMultiAZ);

        if ($_autoMinorVersionUpgrade !== null && $_autoMinorVersionUpgrade !== '')
        {
            $_finalAutoMinorVersionUpgrade = 'true';
            if ($_autoMinorVersionUpgrade === false)
            {
                $_finalAutoMinorVersionUpgrade = 'false';
            }

            $_SWIFT_AmazonRDSRequestObject->SetParameter('AutoMinorVersionUpgrade', $_finalAutoMinorVersionUpgrade);
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'RestoreDBInstanceToPointInTime(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->RestoreDBInstanceToPointInTimeResult->DBInstance->DBInstanceIdentifier))
        {
            $_instanceIdentifier = (string) $_BodyObject->RestoreDBInstanceToPointInTimeResult->DBInstance->DBInstanceIdentifier;
            if ($_instanceIdentifier == $_sourceDBInstanceIdentifier)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a List of RDS Events
     *
     * @author Varun Shoor
     * @param string $_sourceIdentifier (OPTIONAL) The Source Identifier
     * @param mixed $_sourceType (OPTIONAL) The Source Type
     * @param string $_startTime (OPTIONAL) The Start Time
     * @param string $_endTime (OPTIONAL) The End Time
     * @param int $_duration (OPTIONAL) The number of minutes to retrieve events for
     * @param int $_maxRecords (OPTIONAL) The Maximum records to retrieve, capped at 100
     * @param string $_marker (OPTIONAL) The marker of last instance received
     * @return array | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded
     */
    public function DescribeEvents($_sourceIdentifier = null, $_sourceType = null, $_startTime = null, $_endTime = null, $_duration = null,
            $_maxRecords = null, $_marker = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if ($_sourceType !== null && $_sourceType !== '' && !self::IsValidSourceType($_sourceType)) {
            throw new SWIFT_Exception('Invalid Source Type');
        } else if ($_sourceIdentifier !== null && $_sourceIdentifier !== '' && !self::IsValidSourceType($_sourceType)) {
            throw new SWIFT_Exception('Source Identifier specified with Invalid Source Type');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'DescribeEvents');

        if ($_sourceType !== null && $_sourceType !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('SourceType', $_sourceType);
        }

        if ($_sourceIdentifier !== null && $_sourceIdentifier !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('SourceIdentifier', $_sourceIdentifier);
        }

        if ($_startTime !== null && $_startTime !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('StartTime', $_startTime);
        }

        if ($_endTime !== null && $_endTime !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('EndTime', $_endTime);
        }

        if ($_duration !== null && $_duration !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('Duration', $_duration);
        }

        if ($_maxRecords !== null && $_maxRecords !== '' && $_maxRecords >= 20 && $_maxRecords <= 100)
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('MaxRecords', $_maxRecords);
        }

        if ($_marker !== null && $_marker !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('Marker', $_marker);
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeEvents(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->DescribeEventsResult->Events->Event))
        {
            return array();
        }

        $_index = 0;
        foreach ($_BodyObject->DescribeEventsResult->Events->Event as $_EventObject) {
            $_resultsContainer[$_index] = array();

            $_resultsContainer[$_index]['message'] = (string) $_EventObject->Message;
            $_resultsContainer[$_index]['sourcetype'] = (string) $_EventObject->SourceType;
            $_resultsContainer[$_index]['date'] = (string) $_EventObject->Date;
            $_resultsContainer[$_index]['sourceidentifier'] = (string) $_EventObject->SourceIdentifier;

            $_index++;
        }

        $_marker = false;
        if (isset($_BodyObject->Marker))
        {
            $_marker = (string) $_BodyObject->Marker;
        }

        return array($_marker, $_resultsContainer);
    }

    /**
     * Get a list of DB Versions
     *
     * @author Varun Shoor
     * @param string $_dbParameterGroupFamily (OPTIONAL) The name of specific database parameter group family to return
     *     details for
     * @param bool $_defaultOnly (OPTIONAL) Indicates that only the default version of the specified engine or engine
     *     and major version combination is returned.
     * @param mixed $_dbEngine (OPTIONAL) Specifies which database engine to return.
     * @param string $_dbEngineVersion (OPTIONAL) Specifies which database engine version to return.
     * @param int $_maxRecords (OPTIONAL) The Maximum records to retrieve, capped at 100
     * @param string $_marker (OPTIONAL) The marker of last instance received
     * @return array | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded
     */
    public function DescribeDBEngineVersions($_dbParameterGroupFamily = null, $_defaultOnly = null, $_dbEngine = null, $_dbEngineVersion = null, $_maxRecords = null,
            $_marker = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if ($_dbEngine !== null && $_dbEngine !== '' && !self::IsValidDatabaseEngine($_dbEngine)) {
            throw new SWIFT_Exception('Invalid Database Engine');
        } else if ($_dbParameterGroupFamily !== null && $_dbParameterGroupFamily !== '' && !self::IsValidDBParameterGroupFamily($_dbParameterGroupFamily)) {
            throw new SWIFT_Exception('Invalid Database Parameter Group Family');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'DescribeDBEngineVersions');

        if ($_dbParameterGroupFamily !== null && $_dbParameterGroupFamily !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('DBParameterGroupFamily', $_dbParameterGroupFamily);
        }

        if ($_defaultOnly !== null && $_defaultOnly !== '')
        {
            $_finalDefaultOnly = 'false';
            if ($_defaultOnly == true)
            {
                $_finalDefaultOnly = 'true';
            }

            $_SWIFT_AmazonRDSRequestObject->SetParameter('DefaultOnly', $_defaultOnly);
        }

        if ($_dbEngine !== null && $_dbEngine !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('DBEngine', $_dbEngine);
        }

        if ($_dbEngineVersion !== null && $_dbEngineVersion !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('DBEngineVersion', $_dbEngineVersion);
        }

        if ($_maxRecords !== null && $_maxRecords !== '' && $_maxRecords >= 20 && $_maxRecords <= 100)
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('MaxRecords', $_maxRecords);
        }

        if ($_marker !== null && $_marker !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('Marker', $_marker);
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeDBEngineVersions(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->DescribeDBEngineVersionsResult->DBEngineVersions->DBEngineVersion))
        {
            return array();
        }

        $_index = 0;
        foreach ($_BodyObject->DescribeDBEngineVersionsResult->DBEngineVersions->DBEngineVersion as $_DBEngineVersionObject) {
            $_resultsContainer[$_index] = array();

            $_resultsContainer[$_index]['engine'] = (string) $_DBEngineVersionObject->Engine;
            $_resultsContainer[$_index]['dbparametergroupfamily'] = (string) $_DBEngineVersionObject->DBParameterGroupFamily;
            $_resultsContainer[$_index]['engineversion'] = (string) $_DBEngineVersionObject->EngineVersion;

            $_index++;
        }

        $_marker = false;
        if (isset($_BodyObject->Marker))
        {
            $_marker = (string) $_BodyObject->Marker;
        }

        return array($_marker, $_resultsContainer);
    }

    /**
     * Get a list of default engine parameters
     *
     * @author Varun Shoor
     * @param string $_dbParameterGroupFamily The name of specific database parameter group family to return details for
     * @param int $_maxRecords (OPTIONAL) The Maximum records to retrieve, capped at 100
     * @param string $_marker (OPTIONAL) The marker of last instance received
     * @return array | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded
     */
    public function DescribeEngineDefaultParameters($_dbParameterGroupFamily, $_maxRecords = null, $_marker = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!self::IsValidDBParameterGroupFamily($_dbParameterGroupFamily)) {
            throw new SWIFT_Exception('Invalid Database Parameter Group Family');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'DescribeEngineDefaultParameters');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBParameterGroupFamily', $_dbParameterGroupFamily);

        if ($_maxRecords !== null && $_maxRecords !== '' && $_maxRecords >= 20 && $_maxRecords <= 100)
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('MaxRecords', $_maxRecords);
        }

        if ($_marker !== null && $_marker !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('Marker', $_marker);
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeEngineDefaultParameters(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->DescribeEngineDefaultParametersResult->EngineDefaults->DBParameterGroupFamily))
        {
            return array();
        }

        $_index = 0;
        if(isset($_BodyObject->DescribeEngineDefaultParametersResult->EngineDefaults->Parameters)) {
            foreach ($_BodyObject->DescribeEngineDefaultParametersResult->EngineDefaults->Parameters->Parameter as $_ParameterObject) {
                $_resultsContainer[$_index] = array();

                $_resultsContainer[$_index]['parametername'] = (string)$_ParameterObject->ParameterName;
                $_resultsContainer[$_index]['datatype'] = (string)$_ParameterObject->DataType;
                $_resultsContainer[$_index]['source'] = (string)$_ParameterObject->Source;
                $_resultsContainer[$_index]['ismodifiable'] = (string)$_ParameterObject->IsModifiable;
                $_resultsContainer[$_index]['description'] = (string)$_ParameterObject->Description;
                $_resultsContainer[$_index]['applytype'] = (string)$_ParameterObject->ApplyType;
                $_resultsContainer[$_index]['allowedvalues'] = (string)$_ParameterObject->AllowedValues;

                $_index++;
            }
        }

        $_marker = false;
        if (isset($_BodyObject->DescribeEngineDefaultParametersResult->EngineDefaults->Marker))
        {
            $_marker = (string) $_BodyObject->DescribeEngineDefaultParametersResult->EngineDefaults->Marker;
        }

        return array($_marker, $_resultsContainer);
    }

    /**
     * Create a Database Parameter Group
     *
     * @author Varun Shoor
     * @param string $_dbParameterGroupName The Database Parameter Group Name
     * @param string $_dbParameterGroupFamily The Database Parameter Group Family
     * @param string $_description The Description for this Parameter Group
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateDBParameterGroup($_dbParameterGroupName, $_dbParameterGroupFamily, $_description) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbParameterGroupName) || strlen($_dbParameterGroupName) > 255) {
            throw new SWIFT_Exception('Invalid DB Parameter Group Name');
        } else if (!self::IsValidDBParameterGroupFamily($_dbParameterGroupFamily)) {
            throw new SWIFT_Exception('Invalid DB Parameter Group Family');
        } else if (empty($_description)) {
            throw new SWIFT_Exception('Empty Description not Allowed');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'CreateDBParameterGroup');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBParameterGroupName', $_dbParameterGroupName);
        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBParameterGroupFamily', $_dbParameterGroupFamily);
        $_SWIFT_AmazonRDSRequestObject->SetParameter('Description', $_description);

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateDBParameterGroup(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->CreateDBParameterGroupResult->DBParameterGroup->DBParameterGroupName))
        {
            $_parameterGroupName = (string) $_BodyObject->CreateDBParameterGroupResult->DBParameterGroup->DBParameterGroupName;
            if ($_parameterGroupName == $_dbParameterGroupName)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns information about all DB Parameter Groups for an account if no DB Parameter Group name is supplied, or
     * displays information about a specific named DB Parameter Group. You can call this operation recursively using
     * the Marker parameter.
     *
     * @author Varun Shoor
     * @param string $_dbParameterGroupName (OPTIONAL) The DB Parameter Group Name
     * @param int $_maxRecords (OPTIONAL) The Maximum records to retrieve, capped at 100
     * @param string $_marker (OPTIONAL) The marker of last instance received
     * @return array | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded
     */
    public function DescribeDBParameterGroups($_dbParameterGroupName = null, $_maxRecords = null, $_marker = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'DescribeDBParameterGroups');

        if ($_dbParameterGroupName !== null && $_dbParameterGroupName !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('DBParameterGroupName', $_dbParameterGroupName);
        }

        if ($_maxRecords !== null && $_maxRecords !== '' && $_maxRecords >= 20 && $_maxRecords <= 100)
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('MaxRecords', $_maxRecords);
        }

        if ($_marker !== null && $_marker !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('Marker', $_marker);
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeDBParameterGroups(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->DescribeDBParameterGroupsResult->DBParameterGroups->DBParameterGroup))
        {
            return array();
        }

        $_index = 0;
        foreach ($_BodyObject->DescribeDBParameterGroupsResult->DBParameterGroups->DBParameterGroup as $_DBParameterGroupObject) {
            $_resultsContainer[$_index] = array();

            $_resultsContainer[$_index]['dbparametergroupfamily'] = (string) $_DBParameterGroupObject->DBParameterGroupFamily;
            $_resultsContainer[$_index]['description'] = (string) $_DBParameterGroupObject->Description;
            $_resultsContainer[$_index]['dbparametergroupname'] = (string) $_DBParameterGroupObject->DBParameterGroupName;

            $_index++;
        }

        $_marker = false;
        if (isset($_BodyObject->Marker))
        {
            $_marker = (string) $_BodyObject->Marker;
        }

        return array($_marker, $_resultsContainer);
    }

    /**
     * Get a list of default engine parameters
     *
     * @author Varun Shoor
     * @param string $_dbParameterGroupName The name of specific database parameter group family to return details for
     * @param mixed $_parameterSource (OPTIONAL) The Parameter Source
     * @param int $_maxRecords (OPTIONAL) The Maximum records to retrieve, capped at 100
     * @param string $_marker (OPTIONAL) The marker of last instance received
     * @return array | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded
     */
    public function DescribeDBParameters($_dbParameterGroupName, $_parameterSource = null, $_maxRecords = null, $_marker = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbParameterGroupName)) {
            throw new SWIFT_Exception('Invalid Database Parameter Group Name');
        } else if ($_parameterSource !== null && $_parameterSource !== '' && !self::IsValidParameterSource ($_parameterSource)) {
            throw new SWIFT_Exception('Invalid Database Parameter Source');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'DescribeDBParameters');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBParameterGroupName', $_dbParameterGroupName);

        if ($_parameterSource !== null && $_parameterSource !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('Source', $_parameterSource);
        }

        if ($_maxRecords !== null && $_maxRecords !== '' && $_maxRecords >= 20 && $_maxRecords <= 100)
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('MaxRecords', $_maxRecords);
        }

        if ($_marker !== null && $_marker !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('Marker', $_marker);
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeDBParameters(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->DescribeDBParametersResult->Parameters->Parameter))
        {
            return array();
        }

        $_index = 0;
        foreach ($_BodyObject->DescribeDBParametersResult->Parameters->Parameter as $_ParameterObject) {
            $_resultsContainer[$_index] = array();

            $_resultsContainer[$_index]['parametername'] = (string) $_ParameterObject->ParameterName;
            $_resultsContainer[$_index]['parametervalue'] = (string) $_ParameterObject->ParameterValue;
            $_resultsContainer[$_index]['datatype'] = (string) $_ParameterObject->DataType;
            $_resultsContainer[$_index]['source'] = (string) $_ParameterObject->Source;
            $_resultsContainer[$_index]['ismodifiable'] = (string) $_ParameterObject->IsModifiable;
            $_resultsContainer[$_index]['description'] = (string) $_ParameterObject->Description;
            $_resultsContainer[$_index]['applytype'] = (string) $_ParameterObject->ApplyType;

            $_index++;
        }

        $_marker = false;
        if (isset($_BodyObject->Marker))
        {
            $_marker = (string) $_BodyObject->Marker;
        }

        return array($_marker, $_resultsContainer);
    }

    /**
     * Delete a DB Parameter Group
     *
     * @author Varun Shoor
     * @param string $_dbParameterGroupName The Parameter Group Name
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteDBParameterGroup($_dbParameterGroupName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbParameterGroupName)) {
            throw new SWIFT_Exception('Invalid DB Parameter Group Name');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'DeleteDBParameterGroup');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBParameterGroupName', $_dbParameterGroupName);

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteDBParameterGroup(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->ResponseMetadata->RequestId))
        {
            return true;
        }

        return false;
    }

    /**
     * Modify a DB Parameter Group
     *
     * @author Varun Shoor
     * @param string $_dbParameterGroupName The Parameter Group Name
     * @param array $_dbParameterList The Database Parameter List
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ModifyDBParameterGroup($_dbParameterGroupName, $_dbParameterList) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbParameterGroupName)) {
            throw new SWIFT_Exception('Invalid DB Parameter Group Name');
        } else if (!_is_array($_dbParameterList) || count($_dbParameterList) > 20) {
            throw new SWIFT_Exception('Invalid DB Parameter List. Make sure there is atleast one parameter and the count does not exceed 20');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'ModifyDBParameterGroup');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBParameterGroupName', $_dbParameterGroupName);

        $_index = 1;
        foreach ($_dbParameterList as $_key => $_value) {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('Parameters.members.' . $_index . '.ParameterName', $_key);
            $_SWIFT_AmazonRDSRequestObject->SetParameter('Parameters.members.' . $_index . '.ParameterValue', $_value);
            $_SWIFT_AmazonRDSRequestObject->SetParameter('Parameters.members.' . $_index . '.ApplyMethod', 'immediate');

            $_index++;
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ModifyDBParameterGroup(' . $_dbParameterGroupName . ', array(' . print_r($_dbParameterList, true) . ')' . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->ModifyDBParameterGroupResult->DBParameterGroupName))
        {
            $_parameterGroupName = (string) $_BodyObject->ModifyDBParameterGroupResult->DBParameterGroupName;

            if ($_dbParameterGroupName == $_parameterGroupName)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Modify a DB Parameter Group
     *
     * @author Varun Shoor
     * @param string $_dbParameterGroupName The Parameter Group Name
     * @param array $_dbParameterList The Database Parameter List
     * @param bool $_resetAllParameters (OPTIONAL) Whether to reset all parameters
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ResetDBParameterGroup($_dbParameterGroupName, $_dbParameterList, $_resetAllParameters = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbParameterGroupName)) {
            throw new SWIFT_Exception('Invalid DB Parameter Group Name');
        } else if ((!_is_array($_dbParameterList) || count($_dbParameterList) > 20) && $_resetAllParameters === null) {
            throw new SWIFT_Exception('Invalid DB Parameter List. Make sure there is atleast one parameter and the count does not exceed 20');
        } else if ($_resetAllParameters !== null && _is_array($_dbParameterList)) {
            throw new SWIFT_Exception('May not specify both ResetAllParameters and a list of parameters to reset.');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'ResetDBParameterGroup');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBParameterGroupName', $_dbParameterGroupName);

        $_index = 1;
        foreach ($_dbParameterList as $_value) {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('Parameters.members.' . $_index . '.ParameterName', $_value);
            $_SWIFT_AmazonRDSRequestObject->SetParameter('Parameters.members.' . $_index . '.ApplyMethod', 'immediate');

            $_index++;
        }

        if ($_resetAllParameters !== null)
        {
            $_finalResetAllParameters = 'false';
            if ($_resetAllParameters == 'true')
            {
                $_finalResetAllParameters = 'true';
            }

            $_SWIFT_AmazonRDSRequestObject->SetParameter('ResetAllParameters', $_finalResetAllParameters);
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'ResetDBParameterGroup(' . $_dbParameterGroupName . ', array(' . print_r($_dbParameterList, true) . ')' . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->ResetDBParameterGroupResult->DBParameterGroupName))
        {
            $_parameterGroupName = (string) $_BodyObject->ResetDBParameterGroupResult->DBParameterGroupName;

            if ($_dbParameterGroupName == $_parameterGroupName)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Create a Database Security Group
     *
     * @author Varun Shoor
     * @param string $_dbSecurityGroupName The Database Security Group Name
     * @param string $_description The Description for this Security Group
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function CreateDBSecurityGroup($_dbSecurityGroupName, $_description) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbSecurityGroupName) || strlen($_dbSecurityGroupName) > 255) {
            throw new SWIFT_Exception('Invalid DB Secyrity Group Name');
        } else if (empty($_description)) {
            throw new SWIFT_Exception('Empty Description not Allowed');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'CreateDBSecurityGroup');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBSecurityGroupName', $_dbSecurityGroupName);
        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBSecurityGroupDescription', $_description);

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'CreateDBSecurityGroup(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->CreateDBSecurityGroupResult->DBSecurityGroup->DBSecurityGroupName))
        {
            $_securityGroupName = (string) $_BodyObject->CreateDBSecurityGroupResult->DBSecurityGroup->DBSecurityGroupName;
            if ($_securityGroupName == $_dbSecurityGroupName)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns all the DB Security Group details for a particular AWS account, or for a particular DB Security Group if
     * a name is specified. You can call this operation recursively using the Marker parameter.
     *
     * @author Varun Shoor
     * @param string $_dbSecurityGroupName (OPTIONAL) The DB Security Group Name
     * @param int $_maxRecords (OPTIONAL) The Maximum records to retrieve, capped at 100
     * @param string $_marker (OPTIONAL) The marker of last instance received
     * @return array | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded
     */
    public function DescribeDBSecurityGroups($_dbSecurityGroupName = null, $_maxRecords = null, $_marker = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'DescribeDBSecurityGroups');

        if ($_dbSecurityGroupName !== null && $_dbSecurityGroupName !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('DBSecurityGroupName', $_dbSecurityGroupName);
        }

        if ($_maxRecords !== null && $_maxRecords !== '' && $_maxRecords >= 20 && $_maxRecords <= 100)
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('MaxRecords', $_maxRecords);
        }

        if ($_marker !== null && $_marker !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('Marker', $_marker);
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DescribeDBSecurityGroups(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();
        $_resultsContainer = array();

        if (!isset($_BodyObject->DescribeDBSecurityGroupsResult->DBSecurityGroups->DBSecurityGroup))
        {
            return array();
        }

        $_index = 0;
        foreach ($_BodyObject->DescribeDBSecurityGroupsResult->DBSecurityGroups->DBSecurityGroup as $_DBSecurityGroupObject) {
            $_resultsContainer[$_index] = array();

            $_resultsContainer[$_index]['dbsecuritygroupname'] = (string) $_DBSecurityGroupObject->DBSecurityGroupName;
            $_resultsContainer[$_index]['ownerid'] = (string) $_DBSecurityGroupObject->OwnerId;
            $_resultsContainer[$_index]['dbsecuritygroupdescription'] = (string) $_DBSecurityGroupObject->DBSecurityGroupDescription;

            $_resultsContainer[$_index]['ec2securitygroups'] = array();
            if (isset($_DBSecurityGroupObject->EC2SecurityGroups->EC2SecurityGroup))
            {
                $_ec2Index = 0;
                foreach ($_DBSecurityGroupObject->EC2SecurityGroups->EC2SecurityGroup as $_EC2SecurityGroupObject) {
                    $_resultsContainer[$_index]['ec2securitygroups'][$_ec2Index] = array();
                    $_resultsContainer[$_index]['ec2securitygroups'][$_ec2Index]['status'] = (string) $_EC2SecurityGroupObject->Status;
                    $_resultsContainer[$_index]['ec2securitygroups'][$_ec2Index]['ec2securitygroupname'] = (string) $_EC2SecurityGroupObject->EC2SecurityGroupName;
                    $_resultsContainer[$_index]['ec2securitygroups'][$_ec2Index]['ec2securitygroupownerid'] = (string) $_EC2SecurityGroupObject->EC2SecurityGroupOwnerId;

                    $_ec2Index++;
                }
            }


            $_resultsContainer[$_index]['ipranges'] = array();
            if (isset($_DBSecurityGroupObject->IPRanges->IPRange))
            {
                $_ipRangeIndex = 0;
                foreach ($_DBSecurityGroupObject->IPRanges->IPRange as $_IPRangeObject) {
                    $_resultsContainer[$_index]['ipranges'][$_ipRangeIndex] = array();
                    $_resultsContainer[$_index]['ipranges'][$_ipRangeIndex]['cidrip'] = (string) $_IPRangeObject->CIDRIP;
                    $_resultsContainer[$_index]['ipranges'][$_ipRangeIndex]['status'] = (string) $_IPRangeObject->Status;

                    $_ipRangeIndex++;
                }
            }



            $_index++;
        }

        $_marker = false;
        if (isset($_BodyObject->Marker))
        {
            $_marker = (string) $_BodyObject->Marker;
        }

        return array($_marker, $_resultsContainer);
    }

    /**
     * Delete a DB Security Group
     *
     * @author Varun Shoor
     * @param string $_dbSecurityGroupName The Security Group Name
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function DeleteDBSecurityGroup($_dbSecurityGroupName) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbSecurityGroupName)) {
            throw new SWIFT_Exception('Invalid DB Security Group Name');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'DeleteDBSecurityGroup');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBSecurityGroupName', $_dbSecurityGroupName);

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'DeleteDBSecurityGroup(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->ResponseMetadata->RequestId))
        {
            return true;
        }

        return false;
    }

    /**
     * Authorizes network ingress for an Amazon EC2 security group or an IP address range.
     * EC2 security groups can be added to the DBSecurityGroup if the application using the database is running on EC2
     * instances. IP ranges are available if the application accessing your database is running on the Internet.
     *
     * @author Varun Shoor
     * @param string $_dbSecurityGroupName The Security Group Name
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function AuthorizeDBSecurityGroupIngress($_dbSecurityGroupName, $_cidrIP = null, $_ec2SecurityGroupName = null, $_ec2SecurityGroupOwnerID = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbSecurityGroupName)) {
            throw new SWIFT_Exception('Invalid DB Security Group Name');
        } else if ($_cidrIP === null && $_ec2SecurityGroupName === null && $_ec2SecurityGroupOwnerID === null) {
            throw new SWIFT_Exception('Need to specify either CIDR IP or EC2 Security Group Name + EC2 Security Group Owner ID');
        } else if ($_cidrIP !== null && ($_ec2SecurityGroupName !== null || $_ec2SecurityGroupOwnerID !== null)) {
            throw new SWIFT_Exception('Can either specify CIDR IP or EC2 Security Group Name|EC2 Security Group Owner ID');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'AuthorizeDBSecurityGroupIngress');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBSecurityGroupName', $_dbSecurityGroupName);

        if ($_cidrIP !== null && $_cidrIP !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('CIDRIP', $_cidrIP);
        }

        if ($_ec2SecurityGroupName !== null && $_ec2SecurityGroupName !== '' && $_ec2SecurityGroupOwnerID !== null && $_ec2SecurityGroupOwnerID !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('EC2SecurityGroupName', $_ec2SecurityGroupName);
            $_SWIFT_AmazonRDSRequestObject->SetParameter('EC2SecurityGroupOwnerId', $_ec2SecurityGroupOwnerID);
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'AuthorizeDBSecurityGroupIngress(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->AuthorizeDBSecurityGroupIngressResult->DBSecurityGroup->DBSecurityGroupName))
        {
            $_securityGroupName = (string) $_BodyObject->AuthorizeDBSecurityGroupIngressResult->DBSecurityGroup->DBSecurityGroupName;
            if ($_securityGroupName == $_dbSecurityGroupName)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Revokes network ingress for an Amazon EC2 security group or an IP address range.
     *
     * @author Varun Shoor
     * @param string $_dbSecurityGroupName The Security Group Name
     * @return bool true | false
     * @throws SWIFT_AmazonRDS_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RevokeDBSecurityGroupIngress($_dbSecurityGroupName, $_cidrIP = null, $_ec2SecurityGroupName = null, $_ec2SecurityGroupOwnerID = null) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dbSecurityGroupName)) {
            throw new SWIFT_Exception('Invalid DB Security Group Name');
        } else if ($_cidrIP === null && $_ec2SecurityGroupName === null && $_ec2SecurityGroupOwnerID === null) {
            throw new SWIFT_Exception('Need to specify either CIDR IP or EC2 Security Group Name + EC2 Security Group Owner ID');
        } else if ($_cidrIP !== null && ($_ec2SecurityGroupName !== null || $_ec2SecurityGroupOwnerID !== null)) {
            throw new SWIFT_Exception('Can either specify CIDR IP or EC2 Security Group Name|EC2 Security Group Owner ID');
        }

        $_SWIFT_AmazonRDSRequestObject = new SWIFT_AmazonRDSRequest(SWIFT_AmazonRDSRequest::ACTION_GET, 'RevokeDBSecurityGroupIngress');

        $_SWIFT_AmazonRDSRequestObject->SetParameter('DBSecurityGroupName', $_dbSecurityGroupName);

        if ($_cidrIP !== null && $_cidrIP !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('CIDRIP', $_cidrIP);
        }

        if ($_ec2SecurityGroupName !== null && $_ec2SecurityGroupName !== '' && $_ec2SecurityGroupOwnerID !== null && $_ec2SecurityGroupOwnerID !== '')
        {
            $_SWIFT_AmazonRDSRequestObject->SetParameter('EC2SecurityGroupName', $_ec2SecurityGroupName);
            $_SWIFT_AmazonRDSRequestObject->SetParameter('EC2SecurityGroupOwnerId', $_ec2SecurityGroupOwnerID);
        }

        $_ResponseObject = $_SWIFT_AmazonRDSRequestObject->GetResponse($this);
        if (!$this->CheckResponse($_ResponseObject, 'RevokeDBSecurityGroupIngress(' . implode(', ', func_get_args()) . ')', 200))
        {
            return false;
        }

        $_BodyObject = $_ResponseObject->GetBodyObject();

        if (isset($_BodyObject->RevokeDBSecurityGroupIngressResult->DBSecurityGroup->DBSecurityGroupName))
        {
            $_securityGroupName = (string) $_BodyObject->RevokeDBSecurityGroupIngressResult->DBSecurityGroup->DBSecurityGroupName;
            if ($_securityGroupName == $_dbSecurityGroupName)
            {
                return true;
            }
        }

        return false;
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
        } else if (!self::IsValidRegion($_region)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_baseURL = $_region;

        return true;
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

        return $_instanceTypeList;
    }
}
?>
