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

namespace Base\Models\PolicyLink;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Policy Link Model
 *
 * @author Arotimi Busayo
 */
class SWIFT_PolicyLink extends SWIFT_Model
{
    const TABLE_NAME = 'policylinks';
    const PRIMARY_KEY = 'policylinkid';

    const TABLE_STRUCTURE = "policylinkid I PRIMARY AUTO NOTNULL,
                                languageid I DEFAULT '0' NOTNULL,
                                isdefault I2 DEFAULT '0' NOTNULL,
                                url C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'policylinkid';
    const INDEX_2 = 'languageid';

    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Arotimi Busayo
     * @param int $_policyLinkId
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_policyLinkId)
    {
        parent::__construct();

        if (!$this->LoadData($_policyLinkId)) {
            throw new SWIFT_Exception('Unable to Load Policy Links: ' . $_policyLinkId);
        }
    }

    /**
     * Destructor
     *
     * @author Arotimi Busayo
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Arotimi Busayo
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'policylinks', $this->GetUpdatePool(), 'UPDATE', "policylinkid = '" . (int)($this->GetPolicyLinkID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves thee Policy Link ID
     *
     * @author Arotimi Busayo
     *
     * @return mixed "policylinkid" on Success
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPolicyLinkID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['policylinkid'];
    }

    /**
     * Load the Data
     *
     * @author Arotimi Busayo
     * @param int $_policyLinkID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_policyLinkID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "policylinks WHERE policylinkid = '" . $_policyLinkID . "'");
        if (isset($_dataStore['policylinkid']) && !empty($_dataStore['policylinkid'])) {
            $this->_dataStore = $_dataStore;

            return true;
        }

        return false;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Arotimi Busayo
     * @return mixed "_dataStore" Array on Success
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Creates A New Localized Policy Link
     *
     * @author Arotimi Busayo
     *
     * @param int $_languageId
     * @param string $_url
     * @param int $_isDefault
     * @return mixed "policyLinkID" on Success
     * @throws SWIFT_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_languageId, $_url, $_isDefault)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'policylinks',
            array(
                'languageid' => $_languageId,
                'url' => $_url,
                'isdefault' => $_isDefault
            ), 'INSERT');

        $_policyLinkID = $_SWIFT->Database->Insert_ID();
        if (!$_policyLinkID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_policyLinkID;
    }

    /**
     * Update A Policy Link
     *
     * @author Arotimi Busayo
     *
     * @param int $_languageID
     * @param int $_url
     * @params int $_isDefault
     * @return bool
     * @throws SWIFT_Exception If Invalid Data is Provided or If Creation Fails
     */
    public function Update($_languageID, $_url, $_isDefault)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('languageid', $_languageID);
        $this->UpdatePool('url', $_url);
        $this->UpdatePool('isdefault', (int)($_isDefault));

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * @author Arotimi Busayo
     *
     * @param int $_languageID
     * @param int $_url
     * @param int $_isDefault
     * @return bool
     */
    public static function UpdatePolicyLink($_languageID, $_url, $_isDefault)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'policylinks', array('url' => $_url, 'isdefault' => $_isDefault), 'UPDATE', "languageid = '" . $_languageID . "'");

        return true;
    }

    /**
     * Retrieve Policy Links
     *
     * @author Arotimi Busayo
     *
     * @return array $_policyLinksList
     */
    public static function RetrievePolicyLinks()
    {
        $_SWIFT = SWIFT::GetInstance();
        $_policyLinksList = [];

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "policylinks");

        while ($_SWIFT->Database->NextRecord()) {
            $_policyLinksList[$_SWIFT->Database->Record['languageid']] = $_SWIFT->Database->Record;
        }
        return $_policyLinksList;
    }

    /**
     * @author Arotimi Busayo
     *
     * @param int $_languageID
     * @return string
     */
    public static function RetrieveURL($_languageID)
    {
        $_SWIFT = SWIFT::GetInstance();
        $url = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "policylinks WHERE languageid = '" . $_languageID . "' OR isdefault =1");
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record['languageid'] == $_languageID) {
                if (!empty($_SWIFT->Database->Record['url'])) {
                    $url = $_SWIFT->Database->Record['url'];
                    break;
                }
            } else {
                $url = $_SWIFT->Database->Record['url'];
            }
        }

        return $url;
    }

    /**
     * @author Arotimi Busayo
     *
     * @param int $_languageID
     * @return array "queryResult"
     */
    public static function CheckPolicyExists($_languageID)
    {
        $_SWIFT = SWIFT::GetInstance();
        return $_SWIFT->Database->QueryFetch("SELECT languageid FROM " . TABLE_PREFIX . "policylinks WHERE languageid = '" . $_languageID . "'");
    }
}

?>
