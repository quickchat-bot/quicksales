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

namespace Tickets\Models\Bayesian;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Library\Bayesian\SWIFT_Bayesian_Exception;

/**
 * The Bayesian Category Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_BayesianCategory extends SWIFT_Model
{
    const TABLE_NAME        =    'bayescategories';
    const PRIMARY_KEY        =    'bayescategoryid';

    const TABLE_STRUCTURE    =    "bayescategoryid I PRIMARY AUTO NOTNULL,
                                category C(255) DEFAULT '' NOTNULL,
                                probability N DEFAULT '0' NOTNULL,
                                wordcount I8 DEFAULT '0' NOTNULL,
                                categoryweight I2 DEFAULT '0' NOTNULL,
                                ismaster I2 DEFAULT '0' NOTNULL,
                                categorytype I2 DEFAULT '0' NOTNULL";

    const INDEX_1            =    'category'; // Unified Search


    protected $_dataStore = array();

    // Core Constants
    const CATEGORY_DEFAULT = 1;
    const CATEGORY_SPAM = 2;
    const CATEGORY_NOTSPAM = 3;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_bayesianCategoryID The Bayesian Category ID
     * @throws SWIFT_Bayesian_Exception If the Class is not Loaded
     */
    public function __construct($_bayesianCategoryID)
    {
        parent::__construct();

        if (!$this->LoadData($_bayesianCategoryID))
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Bayesian_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'bayescategories', $this->GetUpdatePool(), 'UPDATE', "bayescategoryid = '" .
                (int) ($this->GetBayesianCategoryID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Bayesian Category ID
     *
     * @author Varun Shoor
     * @return mixed "bayescategoryid" on Success, "false" otherwise
     * @throws SWIFT_Bayesian_Exception If the Class is not Loaded
     */
    public function GetBayesianCategoryID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['bayescategoryid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_bayesianCategoryID The Bayesian Category ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_bayesianCategoryID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "bayescategories WHERE bayescategoryid = '" .
                 ($_bayesianCategoryID) . "'");
        if (isset($_dataStore['bayescategoryid']) && !empty($_dataStore['bayescategoryid']))
        {
            $this->_dataStore = $_dataStore;

            return true;
        }

        return false;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Bayesian_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Bayesian_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Bayesian_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Bayesian Category
     *
     * @author Varun Shoor
     * @param string $_categoryTitle The Category Title
     * @param int $_categoryWeight The Category Weight
     * @param int $_isMaster (OPTIONAL) Whether this category is a master category which cannot be deleted.
     * @param mixed $_categoryType (OPTIONAL) The Category Type
     * @return mixed "_bayesianCategoryID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Bayesian_Exception If Invalid Data Provided or If Creation Fails
     * @throws SWIFT_Exception
     */
    public static function Create($_categoryTitle, $_categoryWeight, $_isMaster = 0, $_categoryType = self::CATEGORY_DEFAULT)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_categoryTitle))
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'bayescategories', array('category' => Clean($_categoryTitle), 'probability' => '0',
            'wordcount' => '0', 'categoryweight' =>  ($_categoryWeight), 'ismaster' => ($_isMaster),
            'categorytype' => (int) ($_categoryType)), 'INSERT');
        $_bayesianCategoryID = $_SWIFT->Database->Insert_ID();

        if (!$_bayesianCategoryID)
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CREATEFAILED);
        }

        self::RebuildCache();

        return $_bayesianCategoryID;
    }

    /**
     * Update The Bayesian Category Record
     *
     * @author Varun Shoor
     * @param string $_categoryTitle The Category Title
     * @param int $_categoryWeight The Category Weight
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Bayesian_Exception If the Class is not Loaded
     */
    public function Update($_categoryTitle, $_categoryWeight)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('category', Clean($_categoryTitle));
        $this->UpdatePool('categoryweight', ($_categoryWeight));

        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Delete Bayesian Category record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Bayesian_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetBayesianCategoryID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a List of Bayesian Categories
     *
     * @author Varun Shoor
     * @param array $_bayesianCategoryIDList The Bayesian Category ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_bayesianCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_bayesianCategoryIDList))
        {
            return false;
        }

        $_finalBayesianCategoryIDList = array();
        $_index = $_noDeleteIndex = 1;

        $_noDeleteFinalText = $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "bayescategories WHERE bayescategoryid IN (" . BuildIN($_bayesianCategoryIDList)
                . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            if ($_SWIFT->Database->Record['ismaster'] == 1)
            {
                $_noDeleteFinalText .= $_noDeleteIndex . '. ' . htmlspecialchars($_SWIFT->Database->Record['category']) . "<br />\n";
                $_noDeleteIndex++;

                continue;
            }

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['category']) . "<br />\n";
            $_finalBayesianCategoryIDList[] = $_SWIFT->Database->Record['bayescategoryid'];
            $_index++;
        }

        if ($_noDeleteIndex > 1)
        {
            SWIFT::Alert(sprintf($_SWIFT->Language->Get('titlebayesnodel'), ($_noDeleteIndex-1)), $_SWIFT->Language->Get('msgbayesnodel') .
                    '<br>' . $_noDeleteFinalText);
        }

        if (!count($_finalBayesianCategoryIDList))
        {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titlebayesdel'), count($_finalBayesianCategoryIDList)), $_SWIFT->Language->Get('msgbayesdel') .
                '<br>' . $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "bayescategories WHERE bayescategoryid IN (" .
                BuildIN($_finalBayesianCategoryIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "bayeswordsfreqs WHERE bayescategoryid IN (" .
                BuildIN($_finalBayesianCategoryIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the Bayesian Category Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "bayescategories ORDER BY category ASC", 3);
        while ($_SWIFT->Database->NextRecord(3))
        {
            $_index++;
            $_cache[$_SWIFT->Database->Record3['bayescategoryid']] = $_SWIFT->Database->Record3;
        }

        $_SWIFT->Cache->Update('bayesiancategorycache', $_cache);

        return true;
    }
}
?>
