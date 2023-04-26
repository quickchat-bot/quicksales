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
 * @copyright      Copyright (c) 2001-2013, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The NS record management class
 *
 * @author Ravinder Singh
 */
class SWIFT_DynectNSRecord extends SWIFT_DynectRecord
{
    const BASE_URL = 'NSRecord';

    /**
     * Constructor
     *
     * @author Ravinder Singh
     *
     * @param SWIFT_DynectZone $_DynectZoneObject
     * @param string           $_FQDN
     * @param string           $_NSDomainName
     * @param int              $_ttlSeconds (OPTIONAL) The TTL value. Only use the following values!
     *
     * @throws SWIFT_Exception If Creation Fails
     */
    public function __construct(SWIFT_DynectZone $_DynectZoneObject, $_FQDN, $_NSDomainName, $_ttlSeconds = self::DEFAULT_TTL)
    {
        if (empty($_NSDomainName)) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_rData = array('nsdname' => $_NSDomainName);

        parent::__construct($_DynectZoneObject, $_FQDN, $_rData, $_ttlSeconds);
    }

    /**
     * Set the NS Domain
     *
     * @author Ravinder Singh
     *
     * @param string $_NSDomainName
     *
     * @return SWIFT_DynectNSRecord
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetNSDName($_NSDomainName)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_NSDomainName)) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
        }

        $this->SetRData(array('nsdname' => $_NSDomainName));

        return $this;
    }

    /**
     * Retrieve the currently set NS Domain
     *
     * @author Ravinder Singh
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNSDName()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_rData = $this->GetRData();

        return $_rData['nsdname'];
    }

    /**
     * Create an NS record
     *
     * @author Ravinder Singh
     *
     * @param SWIFT_DynectZone $_DynectZoneObject
     * @param string           $_FQDN
     * @param string           $_NSDomainName
     * @param int              $_ttlSeconds (OPTIONAL) The TTL Seconds. Only use valid TTL values!
     *
     * @return SWIFT_DynectNSRecord
     */
    public static function Create(SWIFT_DynectZone $_DynectZoneObject, $_FQDN, $_NSDomainName, $_ttlSeconds = self::DEFAULT_TTL)
    {
        $_rData = array('nsdname' => $_NSDomainName);

        $_JSONObject = parent::CreateBase(self::BASE_URL, $_DynectZoneObject, $_FQDN, $_rData, $_ttlSeconds);

        $_zoneName                   = isset($_JSONObject->data->zone) ? (string) $_JSONObject->data->zone : '';
        $_FQDN                       = isset($_JSONObject->data->fqdn) ? (string) $_JSONObject->data->fqdn : '';
        $_NSDName                    = isset($_JSONObject->data->rdata->nsdname) ? (string) $_JSONObject->data->rdata->nsdname : '';
        $_ttlSeconds                 = isset($_JSONObject->data->ttl) ? (string) $_JSONObject->data->ttl : '';
        $_SWIFT_DynectNSRecordObject = new SWIFT_DynectNSRecord($_DynectZoneObject, $_FQDN, $_NSDomainName, $_ttlSeconds);

        $_DynectZoneObject->Publish();

        return $_SWIFT_DynectNSRecordObject;
    }

    /**
     * Display NS Record Properties
     *
     * @author Ravinder Singh
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function __toString()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_returnData = 'Zone Name: ' . $this->GetDynectZone()->GetName() . SWIFT_CRLF;
        $_returnData .= 'FQDN: ' . $this->GetFQDN() . SWIFT_CRLF;
        $_rData = $this->GetRData();
        $_returnData .= 'Name Server: ' . $_rData['nsdname'] . SWIFT_CRLF;
        $_returnData .= 'TTL: ' . $this->GetTTLSeconds() . SWIFT_CRLF;

        return $_returnData;
    }

    /**
     * Retrieve the Record Object
     *
     * @author Ravinder Singh
     *
     * @param SWIFT_DynectZone $_DynectZoneObject
     * @param string           $_FQDN
     * @param string           $_recordID
     *
     * @return SWIFT_DynectNSRecord
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve($_DynectZoneObject, $_FQDN, $_recordID, $_ = null)
    {
        $_JSONObject = parent::Retrieve(self::BASE_URL, $_DynectZoneObject, $_FQDN, $_recordID);

        $_zoneName   = isset($_JSONObject->data->zone) ? (string) $_JSONObject->data->zone : '';
        $_FQDN       = isset($_JSONObject->data->fqdn) ? (string) $_JSONObject->data->fqdn : '';
        $_NSDName    = isset($_JSONObject->data->rdata->nsdname) ? (string) $_JSONObject->data->rdata->nsdname : '';
        $_ttlSeconds = isset($_JSONObject->data->ttl) ? (string) $_JSONObject->data->ttl : '';

        $_SWIFT_DynectNSRecordObject = new SWIFT_DynectNSRecord($_DynectZoneObject, $_FQDN, $_NSDName, $_ttlSeconds);

        return $_SWIFT_DynectNSRecordObject;
    }

    /**
     * Retrieve the Records
     *
     * @author Ravinder Singh
     *
     * @param SWIFT_DynectZone $_DynectZoneObject
     * @param string           $_FQDN
     *
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveAll($_DynectZoneObject, $_FQDN, $_ = null)
    {
        $_JSONObject = parent::RetrieveAll(self::BASE_URL, $_DynectZoneObject, $_FQDN);

        $_recordsURIList = array();

        if(isset($_JSONObject->data)) {
            foreach ($_JSONObject->data as $_val) {
                $_recordsURIList[] = (string)$_val;
            }
        }

        return $_recordsURIList;
    }

    /**
     * Delete the record
     *
     * @author Ravinder Singh
     *
     * @param string $_recordID (OPTIONAL)
     *
     * @return SWIFT_DynectNSRecord
     * @throws SWIFT_Exception If Class is not Loaded or if Invalid Data is Provided
     */
    public function Delete($_recordID = '', $_ = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        parent::Delete(self::BASE_URL, $_recordID);

        return $this;
    }

    /**
     * Update The record. Use SetNSDName/SetTTLSeconds function prior to calling
     *
     * @author Ravinder Singh
     *
     * @param string $_recordID (OPTIONAL)
     *
     * @return SWIFT_DynectNSRecord
     * @throws SWIFT_Exception If Class is not Loaded or if Invalid Data is Provided
     */
    public function Update($_recordID = '', $_ = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        parent::Update(self::BASE_URL, $_recordID);

        return $this;
    }
}
