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
 * The SOA record management class
 *
 * @author Ravinder Singh
 */
class SWIFT_DynectSOARecord extends SWIFT_DynectRecord
{
    const BASE_URL = 'SOARecord';

    /**
     * Constructor
     *
     * @author Ravinder Singh
     *
     * @param SWIFT_DynectZone $_DynectZoneObject
     * @param string           $_FQDN
     * @param string           $_adminContactEmail
     * @param int              $_ttlSeconds (OPTIONAL) The TTL value. Only use valid TTL values!
     *
     * @throws SWIFT_Exception If Creation Fails
     */
    public function __construct(SWIFT_DynectZone $_DynectZoneObject, $_FQDN, $_adminContactEmail, $_ttlSeconds = self::DEFAULT_TTL)
    {
        if (empty($_adminContactEmail)) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_rData = array('rname' => $_adminContactEmail);

        parent::__construct($_DynectZoneObject, $_FQDN, $_rData, $_ttlSeconds);
    }

    /**
     * Set the Administrator Contact Email
     *
     * @author Ravinder Singh
     *
     * @param string $_adminContactEmail
     *
     * @return SWIFT_DynectSOARecord
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetAdminContactEmail($_adminContactEmail)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_adminContactEmail)) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
        }

        $this->SetRData(array('rname' => $_adminContactEmail));

        return $this;
    }

    /**
     * Retrieve the currently set Admin Contact Email
     *
     * @author Ravinder Singh
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetAdminContactEmail()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_rData = $this->GetRData();

        return $_rData['rname'];
    }

    /**
     * Display SOA Record Properties
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
        $_returnData .= 'Admin Contact Email: ' . $_rData['rname'] . SWIFT_CRLF;
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
     * @return SWIFT_DynectSOARecord
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve($_DynectZoneObject, $_FQDN, $_recordID, $_ = null)
    {
        $_JSONObject = parent::Retrieve(self::BASE_URL, $_DynectZoneObject, $_FQDN, $_recordID);

        $_zoneName          = isset($_JSONObject->data->zone) ? (string) $_JSONObject->data->zone : '';
        $_FQDN              = isset($_JSONObject->data->fqdn) ? (string) $_JSONObject->data->fqdn : '';
        $_adminContactEmail = isset($_JSONObject->data->rdata->rname) ? (string) $_JSONObject->data->rdata->rname : '';
        $_ttlSeconds        = isset($_JSONObject->data->ttl) ? (string) $_JSONObject->data->ttl : '';

        $_SWIFT_DynectSOARecordObject = new SWIFT_DynectSOARecord($_DynectZoneObject, $_FQDN, $_adminContactEmail, $_ttlSeconds);

        return $_SWIFT_DynectSOARecordObject;
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
     * Update The record. Use SetAdminContactEmail/SetTTLSeconds function prior to calling
     *
     * @author Ravinder Singh
     *
     * @param string $_recordID (OPTIONAL)
     *
     * @return SWIFT_DynectSOARecord
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
