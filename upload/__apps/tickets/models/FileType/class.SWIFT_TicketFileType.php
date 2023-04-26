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

namespace Tickets\Models\FileType;

use SWIFT;
use SWIFT_Exception;
use SWIFT_MIME_Exception;
use SWIFT_MIMEList;
use SWIFT_Model;

/**
 * The Ticket File Type Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketFileType extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketfiletypes';
    const PRIMARY_KEY        =    'ticketfiletypeid';

    const TABLE_STRUCTURE    =    "ticketfiletypeid I PRIMARY AUTO NOTNULL,
                                extension C(10) DEFAULT '' NOTNULL,
                                maxsize I DEFAULT '0' NOTNULL,

                                acceptsupportcenter I2 DEFAULT '0' NOTNULL,
                                acceptmailparser I2 DEFAULT '0' NOTNULL";

    const INDEX_1            =    'extension';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ticketFileTypeID The Ticket File Type ID
     * @throws SWIFT_FileType_Exception If the Class is not Loaded
     */
    public function __construct($_ticketFileTypeID)
    {
        parent::__construct();

        $this->Load->Library('MIME:MIMEList');

        if (!$this->LoadData($_ticketFileTypeID))
        {
            throw new SWIFT_FileType_Exception(SWIFT_CLASSNOTLOADED);
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
     * @throws SWIFT_FileType_Exception If the Class is not Loaded
     * @throws SWIFT_FileType_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketfiletypes', $this->GetUpdatePool(), 'UPDATE', "ticketfiletypeid = '" .
                (int) ($this->GetTicketFileTypeID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket File Type ID
     *
     * @author Varun Shoor
     * @return mixed "ticketfiletypeid" on Success, "false" otherwise
     * @throws SWIFT_FileType_Exception If the Class is not Loaded
     */
    public function GetTicketFileTypeID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_FileType_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketfiletypeid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ticketFileTypeID The Ticket File Type ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ticketFileTypeID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketfiletypes WHERE ticketfiletypeid = '" .
                $_ticketFileTypeID . "'");
        if (isset($_dataStore['ticketfiletypeid']) && !empty($_dataStore['ticketfiletypeid']))
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
     * @throws SWIFT_FileType_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_FileType_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_FileType_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_FileType_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_FileType_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new Ticket File Type Record
     *
     * @author Varun Shoor
     * @param string $_extension The File Extension
     * @param int $_maxSize The Maximum Permissible Size
     * @param bool $_acceptSupportCenter Accept Requests from Support Center
     * @param bool $_acceptMailParser Accept Requests from Mail Parser
     * @param bool $_rebuildCache Whether to Rebuild the Cache
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_FileType_Exception If Invalid Data Provided or If Creation Fails
     */
    public static function Create($_extension, $_maxSize, $_acceptSupportCenter, $_acceptMailParser, $_rebuildCache = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_extension))
        {
            throw new SWIFT_FileType_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX.'ticketfiletypes', array('extension' => $_extension, 'maxsize' =>  ($_maxSize),
            'acceptsupportcenter' => (int) ($_acceptSupportCenter), 'acceptmailparser' => (int) ($_acceptMailParser)), 'INSERT');
        $_ticketFileTypeID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketFileTypeID)
        {
            throw new SWIFT_FileType_Exception(SWIFT_CREATEFAILED);
        }

        if ($_rebuildCache)
        {
            self::RebuildCache();
        }

        return $_ticketFileTypeID;
    }

    /**
     * Update The Ticket File Type Record
     *
     * @author Varun Shoor
     * @param string $_extension The File Extension
     * @param int $_maxSize The Maximum Permissible Size
     * @param bool $_acceptSupportCenter Accept Requests from Support Center
     * @param bool $_acceptMailParser Accept Requests from Mail Parser
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_FileType_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function Update($_extension, $_maxSize, $_acceptSupportCenter, $_acceptMailParser)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_FileType_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_extension)) {
            throw new SWIFT_FileType_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('extension', $_extension);
        $this->UpdatePool('maxsize',  ($_maxSize));
        $this->UpdatePool('acceptsupportcenter', (int) ($_acceptSupportCenter));
        $this->UpdatePool('acceptmailparser', (int) ($_acceptMailParser));
        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the File Type record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_FileType_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_FileType_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketFileTypeID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a List of Ticket File Type Records
     *
     * @author Varun Shoor
     * @param array $_ticketFileTypeIDList The Ticket File Type ID List Container
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketFileTypeIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketFileTypeIDList))
        {
            return false;
        }

        $_SWIFT_MimeListObject = null;
        try
        {
            $_SWIFT_MimeListObject = new SWIFT_MIMEList();
        } catch (SWIFT_MIME_Exception $_SWIFT_MIME_ExceptionObject) {
            // Do Nothing
        }

        $_finalTicketFileTypeIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketfiletypes WHERE ticketfiletypeid IN (" .
                BuildIN($_ticketFileTypeIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalTicketFileTypeIDList[] = $_SWIFT->Database->Record['ticketfiletypeid'];

            $_mimeData = false;

            try
            {
                $_mimeData = $_SWIFT_MimeListObject->Get($_SWIFT->Database->Record['extension']);
            } catch (SWIFT_MIME_Exception $_SWIFT_MIME_ExceptionObject) {
                // Do Nothing
            }

            if ($_mimeData && isset($_mimeData[1]) && !empty($_mimeData[1]))
            {
                $_icon = $_mimeData[1];
            } else {
                $_icon = 'icon_file.gif';
            }

            $_icon = '<img src="'. SWIFT::Get('themepath') . 'images/' . $_icon .'" align="absmiddle" border="0" /> ';
            $_finalText .= $_index . '. ' . $_icon . htmlspecialchars($_SWIFT->Database->Record['extension']) . ' (' .
                    self::GetSize($_SWIFT->Database->Record['maxsize']) . ')<br />';

            $_index++;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelfiletype'), count($_finalTicketFileTypeIDList)),
                $_SWIFT->Language->Get('msgdelfiletype') . '<br />' . $_finalText);

        if (!count($_finalTicketFileTypeIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketfiletypes WHERE ticketfiletypeid IN (" .
                BuildIN($_finalTicketFileTypeIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Return an Appropriate Size for the File Type
     *
     * @author Varun Shoor
     * @param int $_fileSize The File Size
     * @return int The Formatted File Size
     */
    public static function GetSize($_fileSize)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_fileSize == 0)
        {
            return $_SWIFT->Language->Get('sizenolimit');
        }

        return FormattedSize($_fileSize * 1024);
    }

    /**
     * Rebuild the File Type Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketfiletypes ORDER BY extension ASC", 3);
        while ($_SWIFT->Database->NextRecord(3))
        {
            $_index++;
            $_cache[$_SWIFT->Database->Record3['ticketfiletypeid']] = $_SWIFT->Database->Record3;
        }

        $_SWIFT->Cache->Update('filetypecache', $_cache);

        return true;
    }
}
?>
