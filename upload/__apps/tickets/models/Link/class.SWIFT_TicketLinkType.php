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

namespace Tickets\Models\Link;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * The Ticket Link Type Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketLinkType extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketlinktypes';
    const PRIMARY_KEY        =    'ticketlinktypeid';

    const TABLE_STRUCTURE    =    "ticketlinktypeid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                displayorder I DEFAULT '0' NOTNULL,
                                linktypetitle C(255) DEFAULT '' NOTNULL";


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketLinkTypeID The Ticket Link Type ID
     * @throws SWIFT_Link_Exception If the Class is not Loaded
     */
    public function __construct($_ticketLinkTypeID)
    {
        parent::__construct();

        if (!$this->LoadData($_ticketLinkTypeID))
        {
            throw new SWIFT_Link_Exception(SWIFT_CLASSNOTLOADED);
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
     * @throws SWIFT_Link_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketlinktypes', $this->GetUpdatePool(), 'UPDATE', "ticketlinktypeid = '" .
                (int) ($this->GetTicketLinkTypeID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Link Type ID
     *
     * @author Varun Shoor
     * @return mixed "ticketlinktypeid" on Success, "false" otherwise
     * @throws SWIFT_Link_Exception If the Class is not Loaded
     */
    public function GetTicketLinkTypeID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Link_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketlinktypeid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketLinkTypeID The Ticket Link Type ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketLinkTypeID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketlinktypes WHERE ticketlinktypeid = '" .
                $_ticketLinkTypeID . "'");
        if (isset($_dataStore['ticketlinktypeid']) && !empty($_dataStore['ticketlinktypeid']))
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
     * @throws SWIFT_Link_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Link_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Link_Exception If the Class is not Loaded or If Invalid Data Specified
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Link_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Link_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Ticket Link Type Record
     *
     * @author Varun Shoor
     * @param string $_linkTypeTitle The Link Title
     * @param int $_displayOrder The Link Display Order
     * @param bool $_rebuildCache Whether to Rebuild Cache after Creation
     * @return mixed "_ticketLinkTypeID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Link_Exception If Invalid Data Provided or If Creation Fails
     */
    public static function Create($_linkTypeTitle, $_displayOrder, $_rebuildCache = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_linkTypeTitle))
        {
            throw new SWIFT_Link_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketlinktypes', array('dateline' => DATENOW, 'displayorder' =>  ($_displayOrder),
            'linktypetitle' => $_linkTypeTitle), 'INSERT');

        $_ticketLinkTypeID = $_SWIFT->Database->Insert_ID();
        if (!$_ticketLinkTypeID)
        {
            throw new SWIFT_Link_Exception(SWIFT_CREATEFAILED);
        }

        if ($_rebuildCache)
        {
            self::RebuildCache();
        }

        return $_ticketLinkTypeID;
    }

    /**
     * Update The Link Type Record
     *
     * @author Varun Shoor
     * @param string $_linkTypeTitle The Link Title
     * @param int $_displayOrder The Link Display Order
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Link_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_linkTypeTitle, $_displayOrder)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Link_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_linkTypeTitle)) {
            throw new SWIFT_Link_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('linktypetitle', $_linkTypeTitle);
        $this->UpdatePool('displayorder', $_displayOrder);

        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the Ticket Link Type record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Link_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Link_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketLinkTypeID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Link Types
     *
     * @author Varun Shoor
     * @param string $_ticketLinkTypeIDList The Ticket Link Type ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketLinkTypeIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketLinkTypeIDList))
        {
            return false;
        }

        $_index = 0;
        $_finalTicketLinkTypeIDList = array();

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketlinktypes WHERE ticketlinktypeid IN (" . BuildIN($_ticketLinkTypeIDList) .
                ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalTicketLinkTypeIDList[] = $_SWIFT->Database->Record['ticketlinktypeid'];

            $_finalText .= ($_index+1).'. '.'<img src="'. SWIFT::Get('themepath') .'images/icon_link.png" align="absmiddle" border="0" /> ' .
            htmlspecialchars($_SWIFT->Database->Record['linktypetitle'])."<br>\n";

            $_index++;
        }

        if (!count($_finalTicketLinkTypeIDList))
        {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelticketlinkmul'), count($_finalTicketLinkTypeIDList)),
                sprintf($_SWIFT->Language->Get('msgdelticketlinkmul'), $_finalText));

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketlinktypes WHERE ticketlinktypeid IN (" .
                BuildIN($_finalTicketLinkTypeIDList) . ")");

        self::RebuildCache();

        // Clear the chains
        SWIFT_TicketLinkChain::DeleteOnTicketLinkType($_finalTicketLinkTypeIDList);

        return true;

    }

    /**
     * Retrieve the Last Possible Display Order for a Ticket Link Types
     *
     * @author Varun Shoor
     * @return int The Last Possible Display Order
     */
    public static function GetLastDisplayOrder()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Cache->Load('ticketlinktypecache');

        $_ticketLinkTypeCache = $_SWIFT->Cache->Get('ticketlinktypecache');

        if (!$_ticketLinkTypeCache)
        {
            return 1;
        }

        // Get Last Insert ID
        $_lastInsertID = max(array_keys($_ticketLinkTypeCache));

        return ($_ticketLinkTypeCache[$_lastInsertID]['displayorder']+1);
    }

    /**
     * Rebuild the Ticket Link Type Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketlinktypes ORDER BY displayorder ASC", 3);
        while ($_SWIFT->Database->NextRecord(3))
        {
            $_index++;
            $_cache[$_SWIFT->Database->Record3['ticketlinktypeid']] = $_SWIFT->Database->Record3;

            $_cache[$_SWIFT->Database->Record3['ticketlinktypeid']]['index'] = $_index;
        }

        $_SWIFT->Cache->Update('ticketlinktypecache', $_cache);

        return true;
    }

    /**
     * Update the Display Order List
     *
     * @author Varun Shoor
     * @param array $_ticketLinkTypeIDSortList The Ticket Link Type ID Sort List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateDisplayOrderList($_ticketLinkTypeIDSortList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketLinkTypeIDSortList)) {
            return false;
        }

        foreach ($_ticketLinkTypeIDSortList as $_ticketLinkTypeID => $_displayOrder) {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketlinktypes', array('displayorder' => (int) ($_displayOrder)), 'UPDATE',
                    "ticketlinktypeid = '" . $_ticketLinkTypeID . "'");
        }

        self::RebuildCache();

        return true;
    }
}
?>
