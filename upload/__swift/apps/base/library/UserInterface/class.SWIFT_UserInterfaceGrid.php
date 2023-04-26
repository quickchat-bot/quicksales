<?php
//=======================================
//###################################
// QuickSupport Singapore Pte. Ltd. - SWIFT Framework
//
// Source Copyright 2001QuickSupport Singapore Pte. Ltd.h Ltd.
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//                          www.kayako.com
//###################################
//=======================================
namespace Base\Library\UserInterface;

use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;

/**
 * The Grid Management Class
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class SWIFT_UserInterfaceGrid extends SWIFT_Model
{
    const URL_TO_REMOVE_PARAMS = ['CannedCategory/Manage'];

    private $_gridName = false;
    private $_gridSortFieldObject = false;
    private $_gridRecordsPerPage = 30;
    private $_gridType;
    private $_gridMode;
    private $_gridSearchQuery;
    private $_idFieldObject = false;

    private $_gridSearchStoreURL = false;

    private $_gridSortOffset = -1;

    private $_gridExtendedArguments = '';
    private $_gridURLArguments = '';

    private $_gridDialogURL = '';
    private $_gridDialogArguments = array();

    private $_gridCountCache = 0;

    private $_callbackContainer = array();

    private $_sortingEnabled = false;
    private $_sortingField = '';
    private $_sortableCallbackContainer = array();

    private $_rowClass = 'gridrow1';

    private $_gridSettings = array();

    private $_tagType = false;
    private $_tagModeEnabled = false;

    private $_gridSearchStoreLoaded = false;
    private $SearchStore = false;
    private $_gridSearchStoreID = '';

    protected $_newLink = false;
    protected $_newLinkViewport = false;

    // Custom Mass Action Panel
    protected $_massActionPanelEnabled = false;
    protected $_massActionPanelHTML = '';
    protected $_massActionPanelCallback = array();

    // Query Containers
    private $_gridSelectQuery = false;
    private $_gridCountQuery = false;

    private $_gridSearchSelectQuery = false;
    private $_gridSearchCountQuery = false;

    private $_gridTagSelectQuery = false;
    private $_gridTagCountQuery = false;

    private $_gridSearchStoreSelectQuery = false;

    // Sub Items
    private $_subItemIDContainer = array();
    private $_hasSubItems = false;
    private $_gridSubSelectQuery = false;
    private $_gridSubSelectField = false;

    // Containers
    private $_fieldContainer = array();
    private $_actionContainer = array();
    private $_massActionContainer = array();
    protected $_extendedButtonContainer = array();

    // Render Container
    private $_renderData;

    // Cache Container
    private $_gridCacheContainer;

    // Callback Container
    protected $_getItemCallbackContainer = array();

    // Sort Fields Mappings to allow sorting by labels (strings) mapped to database integer values specified in query
    protected $_sortFieldsMappings = [];
    // Sort fields in reverse to UI, useful for cases when UI values are converted based on DB. i.e converting timestamps to elapsed time
    protected $_sortFieldsReversed = [];

    // Core Constants
    const TYPE_DB = 1;
    const TYPE_ARRAY = 2;

    const MODE_DEFAULT = 1;
    const MODE_SEARCH = 2;

    const TAG_PREFIX = 'tag:';

    const BUTTON_DEFAULT = 1;
    const BUTTON_MENU = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_gridName The Grid Name
     * @param int $_gridType The Grid Type (OPTIONAL)
     * @param bool $_enableTagLookups Whether to enable tag lookups (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct($_gridName, $_gridType = 0, $_enableTagLookups = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        parent::__construct();

        if (!$this->SetName($_gridName)) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        if (self::IsValidType($_gridType)) {
            $this->SetType($_gridType);
        } else {
            $this->SetType(self::TYPE_DB);
        }

        $_gridCache = $_SWIFT->Cache->Get('gridcache');

        $this->_gridCacheContainer = $_gridCache;

        if ($_gridCache && isset($_gridCache[$_SWIFT->Staff->GetStaffID()]) && isset($_gridCache[$_SWIFT->Staff->GetStaffID()][$_gridName])) {
            $this->_gridSettings = $_gridCache[$_SWIFT->Staff->GetStaffID()][$_gridName];
        }

        if (!isset($this->_gridSettings['searchquery'])) {
            $this->_gridSettings['searchquery'] = '';
        }

        if ($_enableTagLookups) {
            $this->_tagModeEnabled = true;
        }

        $this->UpdateGridMode();

        $this->SetIsClassLoaded(true);
    }

    /**
     * Checks to see if its a valid grid mode
     *
     * @author Varun Shoor
     * @param int $_gridMode The Grid Mode
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidMode($_gridMode)
    {
        if ($_gridMode == self::MODE_DEFAULT || $_gridMode == self::MODE_SEARCH) {
            return true;
        }

        return false;
    }

    /**
     * Set the Grid Mode
     *
     * @author Varun Shoor
     * @param int $_gridMode The Grid Mode
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetMode($_gridMode)
    {
        if (!self::IsValidMode($_gridMode)) {
            return false;
        }

        $this->_gridMode = $_gridMode;

        return true;
    }

    /**
     * Retrieve the Grid Mode
     *
     * @author Varun Shoor
     * @return mixed "_gridMode" (INT) on Success, "false" otherwise
     */
    public function GetMode()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_gridMode;
    }

    /**
     * Set the Grid Search Query
     *
     * @author Varun Shoor
     * @param string $_gridSearchSelectQuery The Grid Search Query
     * @param string $_gridSearchCountQuery The Grid Search Count Query
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetSearchQuery($_gridSearchSelectQuery, $_gridSearchCountQuery)
    {
        if (empty($_gridSearchSelectQuery) || empty($_gridSearchCountQuery)) {
            return false;
        }

        $this->_gridSearchSelectQuery = $_gridSearchSelectQuery;
        $this->_gridSearchCountQuery = $_gridSearchCountQuery;

        return true;
    }

    /**
     * Set the Grid Tag Lookup Options
     *
     * @author Varun Shoor
     * @param int $_tagType The Tag Type
     * @param string $_gridTagSelectQuery The Grid Tag Select Query
     * @param string $_gridTagCountQuery The Grid Tag Count Query
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetTagOptions($_tagType, $_gridTagSelectQuery, $_gridTagCountQuery)
    {
        if (empty($_gridTagSelectQuery) || empty($_gridTagCountQuery)) {
            return false;
        }

        $this->_tagType = $_tagType;
        $this->_gridTagSelectQuery = $_gridTagSelectQuery;
        $this->_gridTagCountQuery = $_gridTagCountQuery;

        return true;
    }

    /**
     * Retrieve the currently set search query
     *
     * @author Varun Shoor
     * @return mixed "_gridSearchQuery" (STRING) on Success, "false" otherwise
     */
    public function GetSearchQueryString()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_gridSearchQuery;
    }

    /**
     * Update the Grid Mode depending upon search query
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    private function UpdateGridMode()
    {
        if (isset($_POST['_searchQuery']) && !empty($_POST['_searchQuery'])) {
            if (!isset($this->_gridSettings['searchquery']) || base64_decode($_POST['_searchQuery']) != $this->_gridSettings['searchquery']) {
                $this->UpdateGridCache('offset', 0);
                $this->UpdateGridCache('searchquery', base64_decode($_POST['_searchQuery']));
                $this->Cache->Update('gridcache', $this->_gridCacheContainer);
            }

            $this->SetMode(self::MODE_SEARCH);
            $this->SetSearchQueryString(base64_decode($_POST['_searchQuery']));

            return true;
        } elseif (isset($_POST['_searchQuery']) && empty($_POST['_searchQuery']) && !empty($this->_gridSettings['searchquery'])) {
            $this->UpdateGridCache('offset', 0);
            $this->UpdateGridCache('searchquery', '');
            $this->Cache->Update('gridcache', $this->_gridCacheContainer);
        }

        if (!isset($_POST['_searchQuery']) && !empty($this->_gridSettings['searchquery'])) {
            $this->SetMode(self::MODE_SEARCH);
            $this->SetSearchQueryString($this->_gridSettings['searchquery']);

            return true;
        }

        $this->SetMode(self::MODE_DEFAULT);

        return true;
    }

    /**
     * Check to see if its a valid grid type
     *
     * @author Varun Shoor
     * @param int $_gridType The Grid Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_gridType)
    {
        if ($_gridType == self::TYPE_DB || $_gridType == self::TYPE_ARRAY) {
            return true;
        }

        return false;
    }

    /**
     * Set the Grid Type
     *
     * @author Varun Shoor
     * @param int $_gridType The Grid Type
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetType($_gridType)
    {
        if (!self::IsValidType($_gridType)) {
            return false;
        }

        $this->_gridType = $_gridType;

        return true;
    }

    /**
     * Retrieve the Grid Type
     *
     * @author Varun Shoor
     * @return mixed "_gridType" (INT) on Success, "false" otherwise
     */
    public function GetType()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_gridType;
    }

    /**
     * Set the new link JS
     *
     * @author Varun Shoor
     * @param string $_newLink The New Link Javascript
     * @param string|false $_newLinkViewport The Viewport Linker
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetNewLink($_newLink, $_newLinkViewport = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_newLink = $_newLink;
        $this->_newLinkViewport = $_newLinkViewport;

        return true;
    }

    /**
     * Set the new link viewport JS
     *
     * @author Varun Shoor
     * @param string $_newLinkViewport The Viewport Linker
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetNewLinkViewport($_newLinkViewport)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_newLink = false;
        $this->_newLinkViewport = $_newLinkViewport;

        return true;
    }

    /**
     * Retrieve the New link
     *
     * @author Varun Shoor
     * @return mixed "_newLink" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNewLink()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_newLink;
    }

    /**
     * Retrieve the New link for Viewport
     *
     * @author Varun Shoor
     * @return mixed "_newLinkViewport" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNewLinkViewport()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_newLinkViewport;
    }

    /**
     * Set the Extended Arguments
     *
     * @author Varun Shoor
     * @param string $_gridExtendedArguments The Extended Arguments
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetExtendedArguments($_gridExtendedArguments)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_gridExtendedArguments = $_gridExtendedArguments;

        return true;
    }

    /**
     * Retrieve the Extended Arguments
     *
     * @author Varun Shoor
     * @return mixed "_gridExtendedArguments" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetExtendedArguments()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_gridExtendedArguments;
    }

    /**
     * Set the Extended Arguments
     *
     * @author Varun Shoor
     * @param string $_gridURLArguments The Extended Arguments
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetURLArguments($_gridURLArguments)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_gridURLArguments = $_gridURLArguments;

        return true;
    }

    /**
     * Retrieve the Extended Arguments
     *
     * @author Varun Shoor
     * @return mixed "_gridURLArguments" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetURLArguments()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_gridURLArguments;
    }

    /**
     * Set the Grid Name
     *
     * @author Varun Shoor
     * @param string $_gridName The Grid Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetName($_gridName)
    {
        if (empty($_gridName) || trim($_gridName) == '') {
            return false;
        }

        $this->_gridName = $_gridName;

        return true;
    }

    /**
     * Retrieve the Grid Name
     *
     * @author Varun Shoor
     * @return mixed "_gridName" (STRING) on Success, "false" otherwise
     */
    public function GetName()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_gridName;
    }

    /**
     * Set the Sort Field Object
     *
     * @author Varun Shoor
     * @param SWIFT_UserInterfaceGridField $_gridSortFieldObject The SWIFT_UserInterfaceGridField Object to be used for sorting...
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetSortFieldObject(SWIFT_UserInterfaceGridField $_gridSortFieldObject)
    {
        if (!$_gridSortFieldObject instanceof SWIFT_UserInterfaceGridField || !$_gridSortFieldObject->GetIsClassLoaded()) {
            return false;
        }

        $this->_gridSortFieldObject = $_gridSortFieldObject;

        return true;
    }

    /**
     * Return the currently set sort field object
     *
     * @author Varun Shoor
     * @return mixed "_gridSortFieldObject" SWIFT_UserInterfaceGridField Object on Success, "false" otherwise
     */
    public function GetSortFieldObject()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_gridSortFieldObject;
    }

    /**
     * Set the default number of records for per page
     *
     * @author Varun Shoor
     * @param string $_gridRecordsPerPage The Records Per Page
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetRecordsPerPage($_gridRecordsPerPage)
    {
        $_gridRecordsPerPage = (int)($_gridRecordsPerPage);
        if (empty($_gridRecordsPerPage)) {
            return false;
        }

        $this->_gridRecordsPerPage = $_gridRecordsPerPage;

        return true;
    }

    /**
     * Retrieve the currently set number of records per page
     *
     * @author Varun Shoor
     * @return mixed "_gridRecordsPerPage" (INT) on Success, "false" otherwise
     */
    public function GetRecordsPerPage()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_gridRecordsPerPage;
    }

    /**
     * Retrieve the applicable offset for the grid
     *
     * @author Varun Shoor
     * @return int The Grid Offset
     */
    public function GetOffset()
    {
        if ($this->_gridSortOffset < 0) {
            //            return $this->_gridSortOffset;
        }

        if (isset($_POST['_offset']) && !empty($_POST['_offset']) && is_numeric($_POST['_offset'])) {
            if (!isset($this->_gridSettings['offset']) || $_POST['_offset'] != $this->_gridSettings['offset']) {
                $this->UpdateGridCache('offset', $_POST['_offset']);
                $this->Cache->Update('gridcache', $this->_gridCacheContainer);
            }

            $this->_gridSortOffset = (int)$_POST['_offset'];

            return $_POST['_offset'];
        } elseif (isset($_POST['_offset']) && empty($_POST['_offset'])) {
            $this->UpdateGridCache('offset', 0);
            $this->Cache->Update('gridcache', $this->_gridCacheContainer);
        } elseif (isset($this->_gridSettings['offset']) && !empty($this->_gridSettings['offset']) && is_numeric($this->_gridSettings['offset'])) {
            $_recordOffset = $this->_gridSettings['offset'] * $this->GetRecordsPerPage();
            if ($this->GetTotalItemCount() < $_recordOffset) {
                $this->UpdateGridCache('offset', 0);
                $this->Cache->Update('gridcache', $this->_gridCacheContainer);

                return 0;
            }

            $this->_gridSortOffset = (int)$this->_gridSettings['offset'];

            return $this->_gridSettings['offset'];
        }

        $this->_gridSortOffset = 0;

        return 0;
    }

    /**
     * Set the default queries for retrieval of records
     *
     * @author Varun Shoor
     * @param string $_gridSelectQuery The SELECT Query
     * @param string $_gridCountQuery The SELECT COUNT Query (For Pagination Calculation)
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetQuery($_gridSelectQuery, $_gridCountQuery)
    {
        if (empty($_gridSelectQuery) || empty($_gridCountQuery)) {
            return false;
        }

        $this->_gridSelectQuery = $_gridSelectQuery;
        $this->_gridCountQuery = $_gridCountQuery;

        return true;
    }

    /**
     * Get the default select query
     *
     * @author Varun Shoor
     * @return bool "_gridSelectQuery" (STRING) on Success, "false" otherwise
     */
    public function GetSelectQuery()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_gridSelectQuery;
    }

    /**
     * Get the default count query
     *
     * @author Varun Shoor
     * @return bool "_gridCountQuery" (STRING) on Success, "false" otherwise
     */
    public function GetCountQuery()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_gridCountQuery;
    }

    /**
     * Set the default search queries
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query String
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetSearchQueryString($_searchQuery)
    {
        if (empty($_searchQuery)) {
            return false;
        }

        $this->_gridSearchQuery = $_searchQuery;

        return true;
    }

    /**
     * Return the Query string for the tag
     *
     * @author Varun Shoor
     * @return mixed "Processed _tagID List" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTagIDListFromQueryString()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tagQueryString = trim(mb_strtolower(mb_substr($this->GetSearchQueryString(), strlen(self::TAG_PREFIX))));
        // Has space?
        if (strrpos($_tagQueryString, ' ') !== false) {
            $_tagQueryList = explode(' ', $_tagQueryString);
        } else {
            $_tagQueryList = array($_tagQueryString);
        }

        // Try to retrieve the tag id..
        $_tagIDList = SWIFT_Tag::GetTagIDList($_tagQueryList);

        return BuildIN(SWIFT_TagLink::RetrieveLinkIDListOnTagList($this->_tagType, $_tagIDList));
    }

    /**
     * Get the default tag select query
     *
     * @author Varun Shoor
     * @return string|bool
     */
    public function GetTagSelectQuery()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return sprintf($this->_gridTagSelectQuery, $this->GetTagIDListFromQueryString());
    }

    /**
     * Get the default tag count query
     *
     * @author Varun Shoor
     * @return string|bool
     */
    public function GetTagCountQuery()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return sprintf($this->_gridTagCountQuery, $this->GetTagIDListFromQueryString());
    }

    /**
     * Get the default search select query
     *
     * @author Varun Shoor
     * @return bool "_gridSearchSelectQuery" (STRING) on Success, "false" otherwise
     */
    public function GetSearchSelectQuery()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_gridSearchSelectQuery;
    }

    /**
     * Get the default search count query
     *
     * @author Varun Shoor
     * @return bool "_gridSearchCountQuery" (STRING) on Success, "false" otherwise
     */
    public function GetSearchCountQuery()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_gridSearchCountQuery;
    }

    /**
     * Set the queries for sub fetching of records (tree based representation)
     *
     * @author Varun Shoor
     * @param string $_gridSubSelectQuery The SELECT Query
     * @param string $_gridSubSelectField The Sub Select Field
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetSubQuery($_gridSubSelectQuery, $_gridSubSelectField)
    {
        if (empty($_gridSubSelectQuery) || empty($_gridSubSelectField)) {
            return false;
        }

        $this->_gridSubSelectQuery = $_gridSubSelectQuery;
        $this->_gridSubSelectField = $_gridSubSelectField;

        return true;
    }

    /**
     * Set the options for search store
     *
     * @author Varun Shoor
     * @param int $_searchStoreID The Search Store ID
     * @param string $_gridSearchStoreSelectQuery The SELECT Query
     * @param mixed $_storeType The Search Store Type
     * @param string|false $_gridSearchStoreURL|false (OPTINAL) The Search Store URL
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetSearchStoreOptions($_searchStoreID, $_gridSearchStoreSelectQuery, $_storeType, $_gridSearchStoreURL = false)
    {

        // Are we supposed to clear the search store?
        if ($_searchStoreID == '-1' || (isset($_POST['searchstoreid']) && $_POST['searchstoreid'] == '-1')) {
            // Attempt to delete it
            if (isset($this->_gridSettings['searchstoreid']) && !empty($this->_gridSettings['searchstoreid'])) {
                SWIFT_SearchStore::DeleteList(array($this->_gridSettings['searchstoreid']));
            }

            $this->UpdateGridCache('searchstoreid', false);
            $this->Cache->Update('gridcache', $this->_gridCacheContainer);
        } elseif (isset($_POST['searchstoreid'])) {
            $_searchStoreID = (int)($_POST['searchstoreid']);

            // Do we have an existing search store set?
        } elseif (isset($this->_gridSettings['searchstoreid']) && !empty($this->_gridSettings['searchstoreid']) && empty($_searchStoreID)) {
            $_searchStoreID = $this->_gridSettings['searchstoreid'];
        }

        if (empty($_searchStoreID) || empty($_gridSearchStoreSelectQuery) || $_searchStoreID == '-1') {
            return false;
        }

        try {
            $_SWIFT_SearchStoreObject = new SWIFT_SearchStore($_searchStoreID);
            if ($_SWIFT_SearchStoreObject->GetIsClassLoaded() && $_SWIFT_SearchStoreObject->Verify($this->Session->GetSessionID(), $_storeType)) {
                $_SWIFT_SearchStoreObject->LoadSearchStoreData();

                $this->SearchStore = $_SWIFT_SearchStoreObject;
                $this->_gridSearchStoreID = $_searchStoreID;
                $this->_gridSearchStoreSelectQuery = $_gridSearchStoreSelectQuery;
                $this->_gridSearchStoreLoaded = true;

                // Update the cache
                if (!isset($this->_gridSettings['searchstoreid']) || empty($this->_gridSettings['searchstoreid']) || $this->_gridSettings['searchstoreid'] != $_searchStoreID) {
                    $this->UpdateGridCache('searchstoreid', $_searchStoreID);
                    $this->Cache->Update('gridcache', $this->_gridCacheContainer);
                }
            }
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
        }

        $this->SetSearchStoreURL($_gridSearchStoreURL);

        return true;
    }

    /**
     * Get the default sub select query
     *
     * @author Varun Shoor
     * @return string|false "_gridSearchSelectQuery" (STRING) on Success, "false" otherwise
     */
    public function GetSubSelectQuery()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_gridSubSelectQuery;
    }

    /**
     * Get the default sub select field
     *
     * @author Varun Shoor
     * @return bool "_gridSubSelectField" (STRING) on Success, "false" otherwise
     */
    public function GetSubSelectField()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_gridSubSelectField;
    }

    /**
     * Add the field to the container
     *
     * @author Varun Shoor
     * @param SWIFT_UserInterfaceGridField $_SWIFT_UserInterfaceGridFieldObject The UI Grid Field Object
     * @param bool $_isSortable Whether to sort on this field
     * @return bool "true" on Success, "false" otherwise
     */
    public function AddField(SWIFT_UserInterfaceGridField $_SWIFT_UserInterfaceGridFieldObject, $_isSortable = false)
    {
        if (!$_SWIFT_UserInterfaceGridFieldObject instanceof SWIFT_UserInterfaceGridField || !$_SWIFT_UserInterfaceGridFieldObject->GetIsClassLoaded()) {
            return false;
        }

        if ($_isSortable) {
            $this->SetSortFieldObject($_SWIFT_UserInterfaceGridFieldObject);
        }

        if ($_SWIFT_UserInterfaceGridFieldObject->GetType() == SWIFT_UserInterfaceGridField::TYPE_ID) {
            $this->SetIDField($_SWIFT_UserInterfaceGridFieldObject);
        }

        $this->_fieldContainer[] = $_SWIFT_UserInterfaceGridFieldObject;

        return true;
    }

    /**
     * Sets the ID Field
     *
     * @author Varun Shoor
     * @param SWIFT_UserInterfaceGridField $_SWIFT_UserInterfaceGridFieldObject The UI Grid Field Object
     * @return bool "true" on Success, "false" otherwise
     */
    private function SetIDField($_SWIFT_UserInterfaceGridFieldObject)
    {
        if (!$_SWIFT_UserInterfaceGridFieldObject instanceof SWIFT_UserInterfaceGridField || !$_SWIFT_UserInterfaceGridFieldObject->GetIsClassLoaded()) {
            return false;
        }

        $this->_idFieldObject = $_SWIFT_UserInterfaceGridFieldObject;

        return true;
    }

    /**
     * Retrieves the ID Field Object
     *
     * @author Varun Shoor
     * @return mixed "idFieldObject" _SWIFT_UserInterfaceGridFieldObject on Success, "false" otherwise
     */
    private function GetIDField()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_idFieldObject;
    }

    /**
     * Add mass action to the container
     *
     * @author Varun Shoor
     * @param SWIFT_UserInterfaceGridMassAction $_SWIFT_UserInterfaceGridMassActionObject The Mass Action Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function AddMassAction(SWIFT_UserInterfaceGridMassAction $_SWIFT_UserInterfaceGridMassActionObject)
    {
        if (!$_SWIFT_UserInterfaceGridMassActionObject instanceof SWIFT_UserInterfaceGridMassAction || !$_SWIFT_UserInterfaceGridMassActionObject->GetIsClassLoaded()) {
            return false;
        }

        $_SWIFT_UserInterfaceGridMassActionObject->CheckAndExecute();

        $this->_massActionContainer[] = $_SWIFT_UserInterfaceGridMassActionObject;

        return true;
    }

    /**
     * Add action to the container
     *
     * @author Parminder Singh
     * @return bool "true" on Success, "false" otherwise
     */
    public function AddAction($_actionContainer)
    {
        $this->_actionContainer[] = $_actionContainer;

        return true;
    }

    /**
     * Retrieve the Action Container
     *
     * @author Parminder Singh
     * @return mixed "_massActionContainer" (ARRAY) on Success, "false" otherwise
     */
    public function GetActionContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_actionContainer;
    }

    /**
     * Add a Render Callback
     *
     * @author Varun Shoor
     * @param mixed $_callbackContainer (ARRAY/STRING) Callback container for call_user_func
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetRenderCallback($_callbackContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->_callbackContainer = $_callbackContainer;

        return true;
    }

    /**
     * Retrieve the currently set call back
     *
     * @author Varun Shoor
     * @return mixed "_callbackContainer" (ARRAY/STRING) on Success, "false" otherwise
     */
    public function GetRenderCallback()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_callbackContainer;
    }

    /**
     * Set the Rendered Data
     *
     * @author Varun Shoor
     * @param string $_renderData The Rendered Data
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetRenderData($_renderData)
    {
        if (empty($_renderData)) {
            return false;
        }

        $this->_renderData = $_renderData;

        return true;
    }

    /**
     * Retrieve the Rendered Data
     *
     * @author Varun Shoor
     * @return mixed "_renderData" (STRING) on Success, "false" otherwise
     */
    public function GetRenderData()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_renderData;
    }

    /**
     * Update the local grid cache
     *
     * @author Varun Shoor
     * @param string $_key The Key
     * @param mixed $_value The Value
     * @return bool "true" on Success, "false" otherwise
     * @throws \Base\Library\Staff\SWIFT_Staff_Exception
     */
    public function UpdateGridCache($_key, $_value)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->_gridCacheContainer) {
            $this->_gridCacheContainer = array();
            $this->_gridCacheContainer[$_SWIFT->Staff->GetStaffID()] = array();
            $this->_gridCacheContainer[$_SWIFT->Staff->GetStaffID()][$this->GetName()] = array();
        }

        $this->_gridSettings[$_key] = $_value;

        $this->_gridCacheContainer[$_SWIFT->Staff->GetStaffID()][$this->GetName()][$_key] = $_value;

        return true;
    }

    /**
     * Reset the grid cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ResetGridCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_gridCacheContainer[$_SWIFT->Staff->GetStaffID()][$this->GetName()] = array();

        $this->Cache->Update('gridcache', $this->_gridCacheContainer);

        return true;
    }

    /**
     * Process the sort callback
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessSortCallback()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_rowID = false;
        $_idFieldObject = $this->GetIDField();
        if ($_idFieldObject instanceof SWIFT_UserInterfaceGridField && $_idFieldObject->GetIsClassLoaded() && $_idFieldObject->GetType() == SWIFT_UserInterfaceGridField::TYPE_ID) {
            $_rowID = $_idFieldObject->GetName();
        } else {
            return false;
        }

        $_gridSortObject = $this->GetSortFieldObject();
        if (!$_gridSortObject instanceof SWIFT_UserInterfaceGridField || !$_gridSortObject->GetIsClassLoaded()) {
            return false;
        }

        if (
            isset($_POST['_gridSort']) && !empty($_POST['_gridSort']) && $_POST['_gridSort'] == '1' && isset($_POST['sortitemid']) &&
            _is_array($_POST['sortitemid']) && $this->_sortingEnabled == true
        ) {
            // Do we need to reset the field that sorts?
            if (isset($this->_gridSettings['sortby']) && $this->_gridSettings['sortby'] != $this->_sortingField) {
                $this->UpdateGridCache('sortby', $this->_sortingField);
                $this->Cache->Update('gridcache', $this->_gridCacheContainer);
            }

            $_itemContainer = $this->GetItems(true);

            foreach ((array)$_itemContainer as $_key => $_item) {
                // Is there a sub query for this grid?
                if ($this->_hasSubItems && isset($_item[$_rowID])) {
                    $this->_subItemIDContainer[] = $_item[$_rowID];
                }
            }

            $_subItemContainer = $this->GetSubItems();

            // By now we have all the items and sub items. Itterate through all the items and build a reorder list
            $_sortOrderContainer = $_processedKeys = array();

            $_displayOrder = 1;
            if (isset($this->_gridSettings['sortorder']) && $this->_gridSettings['sortorder'] == SWIFT_UserInterfaceGridField::SORT_DESC) {
                $_displayOrder = count($_itemContainer);

                foreach ($_subItemContainer as $_val) {
                    $_displayOrder += count($_val);
                }
            }

            $_index = 1;
            $_offset = 0;
            foreach ((array)$_itemContainer as $_key => $_item) {
                if ($_index > $this->GetRecordsPerPage()) {
                    $_offset++;
                }

                $_rowIDValue = $_item[$_rowID];

                // Is it the current page?
                if ($_offset == $this->GetOffset()) {
                    // Yes it is.. we now need to go through our incoming sort items
                    foreach ($_POST['sortitemid'] as $_postItemKey => $_postItemID) {
                        if (in_array($_postItemID, $_processedKeys)) {
                            continue;
                        }

                        //                        echo 'DISPLAYORDER: ' . $_postItemID . ' = ' . $_displayOrder . '<BR />';

                        $_sortOrderContainer[$_postItemID] = $_displayOrder;

                        $_processedKeys[] = $_postItemID;

                        if (isset($this->_gridSettings['sortorder']) && $this->_gridSettings['sortorder'] == SWIFT_UserInterfaceGridField::SORT_DESC) {
                            $_displayOrder--;
                        } else {
                            $_displayOrder++;
                        }
                    }
                } else {
                    if (in_array($_rowIDValue, $_processedKeys)) {
                        continue;
                    }

                    //                    echo 'DISPLAYORDER2: ' . $_rowIDValue . ' = ' . $_displayOrder . '<BR />';

                    $_sortOrderContainer[$_rowIDValue] = $_displayOrder;

                    if (isset($this->_gridSettings['sortorder']) && $this->_gridSettings['sortorder'] == SWIFT_UserInterfaceGridField::SORT_DESC) {
                        $_displayOrder--;
                    } else {
                        $_displayOrder++;
                    }

                    // Process the sub items
                    if (isset($_subItemContainer[$_rowIDValue])) {
                        foreach ($_subItemContainer[$_rowIDValue] as $_subKey => $_subItem) {
                            $_subRowIDValue = $_subItem[$_rowID];

                            $_sortOrderContainer[$_subRowIDValue] = $_displayOrder;

                            $_processedKeys[] = $_subRowIDValue;

                            if (isset($this->_gridSettings['sortorder']) && $this->_gridSettings['sortorder'] == SWIFT_UserInterfaceGridField::SORT_DESC) {
                                $_displayOrder--;
                            } else {
                                $_displayOrder++;
                            }
                        }
                    }

                    $_processedKeys[] = $_rowIDValue;
                }

                $_index++;
            }

            $_callBackResult = call_user_func($this->_sortableCallbackContainer, $_sortOrderContainer);
        }

        $this->_subItemIDContainer = array();

        return true;
    }

    /**
     * Render the Grid based on given parameters
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if ($this->GetSubSelectQuery() && $this->GetSubSelectField()) {
            $this->_hasSubItems = true;
        }

        // Process Mass Action Dialog
        if (isset($_POST['_massActionDialog'])) {
            return true;
        }

        // Process sort callbacks
        $this->ProcessSortCallback();

        $_SWIFT->UserInterface->ProcessDialogs();

        $_gridOffset = 0;
        if ($this->_gridSortOffset > 0) {
            $_gridOffset = $this->_gridSortOffset;
        }

        $this->_gridDialogURL = $this->Router->GetCurrentURL() . $this->GetURLArguments() . '/' . $this->_gridSearchStoreID . '/' . $this->GetExtendedArguments();
        $this->_gridDialogArguments = array(
            "'csrfhash':'" . $_SWIFT->Session->GetProperty('csrfhash') . "'",
            "'_massAction':''", "'_massActionPanel':''", "'_gridSort':'0'", "'_offset':'" . $_gridOffset . "'", "'_gridURL':'" . $this->_gridDialogURL . "'"
        );

        $_renderHTML = '<div id="gridcontent' . $this->GetName() . '">';
        $_renderHTML .= '<form name="form_' . $this->GetName() . '" id="form_' . $this->GetName() . '" action="' . $this->Router->GetCurrentURL() . $this->GetURLArguments() . '/' . $this->_gridSearchStoreID . '/' . $this->GetExtendedArguments() . '" method="post" onsubmit="javascript: return false;">';
        $_renderHTML .= '<input type="hidden" name="csrfhash" value="' . $_SWIFT->Session->GetProperty('csrfhash') . '" />';

        $_renderHTML .= $this->RenderToolbar();

        $_renderHTML .= $this->RenderGrid();

        $_renderHTML .= '<input type="hidden" name="_massAction" id="_gridMassAction_' . $this->GetName() . '" value="" />';
        $_renderHTML .= '<input type="hidden" name="_massActionPanel" id="_gridMassActionPanel_' . $this->GetName() . '" value="" />';
        $_renderHTML .= '<input type="hidden" name="_gridSort" id="_gridSort_' . $this->GetName() . '" value="0" />';
        $_renderHTML .= '<input type="hidden" name="_offset" id="_gridSort_' . $this->GetName() . '" value="' . $this->GetOffset() . '" />';
        $_renderHTML .= '<input type="hidden" name="_searchStoreID" value="' . $this->_gridSearchStoreID . '" />';
        $_renderHTML .= '</form>';

        $_renderHTML .= '<script language="Javascript" type="text/javascript">';
        $_renderHTML .= 'if (window.$UIObject) { window.$UIObject.Queue(function() {';

        /*
         * BUG FIX - Simaranjit Singh
         *
         * SWIFT-2406 Page jumps to top instead of opening link.
         *
         * Comments: This code was fixing SWIFT-1837 Which is not replicable in the latest build of KD so removing that fix because it was causing this bug

         */
        $_renderHTML .= '$(\'#gridirs\').focus();';

        if ($this->_sortingEnabled) {
            $_renderHTML .= 'EnableGridSorting("' . $this->GetName() . '");';
        }

        $_renderHTML .= '}); }</script>';
        $_renderHTML .= '</div>';

        $this->SetRenderData($_renderHTML);

        return true;
    }

    /**
     * Display the Rendered Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Display()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        echo $this->GetRenderData();

        return true;
    }

    /**
     * Renders the Top Toolbar
     *
     * @author Varun Shoor
     * @return mixed "Processed HTML" (STRING) on Success, "false" otherwise
     */
    private function RenderToolbar()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_searchQuery = $this->GetSearchQueryString();

        $_massActionToolbarData = '<ul>';
        if ($this->HasMassAction()) {
            $_massActionContainer = $this->GetMassActionContainer();

            foreach ($_massActionContainer as $_key => $_val) {
                if ($_val instanceof SWIFT_UserInterfaceGridMassAction && $_val->GetIsClassLoaded()) {
                    $_massActionLink = 'GridMassAction(\'' . $this->GetName() . '\', \'' . md5($_val->GetTitle()) . '\', \'' . str_replace("\\\\n", '\n', addslashes($_val->GetConfirmMessage())) . '\');';

                    if ($_val->GetIsDialog() !== false) {
                        $_dialogArguments = $this->_gridDialogArguments;
                        $_dialogArguments[] = "'_massActionDialog':'" . $_val->GetName() . "'";

                        $_massActionLink = 'UICreateWindowGrid(\'' . $this->GetName() . '\', \'' . $this->_gridDialogURL . '\', {' . implode(',', $_dialogArguments) . '}, \'' . $_val->GetName() . '\', \'' . $_val->GetDialogTitle() . '\', \'' . $_val->GetDialogWidth() . '\', \'' . $_val->GetDialogHeight() . '\');';
                    }
                    $_massActionToolbarData .= '<li><a href="javascript:void(0);" onclick="javascript: ' . $_massActionLink . '">' . IIF($_val->GetIcon(), '<i class="fa ' . $_val->GetIcon() . '" aria-hidden="true"></i>') . $_val->GetTitle() . '</a></li>';
                }
            }
        }

        $_actionContainer = $this->GetActionContainer();
        $_actionToolbarData = '';
        if (isset($_actionContainer) && _is_array($_actionContainer)) {
            foreach ($_actionContainer as $_key => $_val) {
                $_actionLink = $_linkInfo = '';
                if (isset($_val[3]) && $_val[3] != '') {
                    $_actionLink = 'doConfirm(\'' . str_replace("\\\\n", '\n', addslashes($_val[3])) . '\', \'' . SWIFT::Get('basename') . $_val[2] . '\');';
                } elseif (!isset($_val[2]) && $_val[2] == '' && isset($_val[4]) && $_val[4] != '') {
                    $_actionLink = $_val[4];
                } else {
                    $_actionLink = 'loadViewportData(\'' . SWIFT::Get('basename') . $_val[2] . '\');';
                }

                $_linkInfo = (isset($_val[5]) && $_val[5] != '') ? 'link="' . SWIFT::Get('basename') . $_val[5] . '"' : '';
                $_actionToolbarData .= '<li><a ' . $_linkInfo . ' href="javascript:void(0);" onclick="javascript: ' . $_actionLink . '">' . IIF($_val[1], '<i class="fa ' . $_val[1] . '" aria-hidden="true"></i> ') . $_val[0] . '</a></li>';
            }
        }
        $_massActionToolbarData .= $_actionToolbarData;
        $_massActionToolbarData .= '</ul>';

        $_newLinkPointer = $this->GetNewLink();
        $_newLinkViewportPointer = $this->GetNewLinkViewport();
        $_showExtendedToolbar = false;
        if (!empty($_newLinkPointer) || _is_array($this->_extendedButtonContainer)) {
            $_showExtendedToolbar = true;
        }

        // ======= START THE EXTENDED TOOLBAR =======
        $_extendedToolbarHTML = '';
        if (_is_array($this->_extendedButtonContainer)) {
            foreach ($this->_extendedButtonContainer as $_key => $_val) {
                $_buttonID = '';
                if (isset($_val['id'])) {
                    $_buttonID = $_val['id'];
                }

                $_extendedToolbarHTML .= '<li' . IIF(!empty($_buttonID), ' id="' . $_buttonID . '"') .
                    '><a href="javascript: void(0);" onclick="javascript: ' . $_val['link'] . '"><i class="fa ' . $_val['icon'] . '" aria-hidden="true"></i> ' . $_val['title'] . IIF($_val['type'] == self::BUTTON_MENU, ' <i class="fa fa-chevron-circle-down" aria-hidden="true" style="margin-left: 4px;font-size: 18px !important;"></i>') . '</a></li>';
            }
        }


        // ======= START THE TOOLBAR =======
        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-SWIFT-2344 Script error in QuickSupport Desktop application when inserting a new notification rule.
         *
         * Comments: When clicking on "New" button, event will be handled by jquery, other part of fix is in __swift/themes/__cp/core.js
         */
        $_newLinkHTML = '';
        if (!empty($_newLinkPointer) && !empty($_newLinkViewportPointer)) {
            $_newLinkHTML = '<li><a href="' . $_newLinkViewportPointer . '" class="cancelLinkEvent" onclick="javascript: ' . $_newLinkPointer . '"><i class="fa fa-plus-circle" aria-hidden="true"></i>' . $this->Language->Get('new') . '</a></li>';
            $_showExtendedToolbar = true;
        } elseif (!empty($_newLinkPointer)) {
            $_newLinkHTML = '<li><a href="javascript: void(0);" class="cancelLinkEvent"  onclick="javascript: ' . $_newLinkPointer . '"><i class="fa fa-plus-circle" aria-hidden="true"></i>' . $this->Language->Get('new') . '</a></li>';
            $_showExtendedToolbar = true;
        } elseif (!empty($_newLinkViewportPointer)) {
            $_newLinkHTML = '<li><a href="' . $_newLinkViewportPointer . '" viewport="1"><i class="fa fa-plus-circle" aria-hidden="true"></i>' . $this->Language->Get('new') . '</a></li>';
            $_showExtendedToolbar = true;
        }

        $_returnHTML = '<div id="widthwrapper" style="width: 100%;"><div id="gridtoolbar">' .
            IIF($_showExtendedToolbar, '<div class="gridtoolbarnew" id="gridextendedtoolbar"><div class="gridtoolbarsub"><ul>' . $_extendedToolbarHTML . $_newLinkHTML . '</ul></div></div>') . SWIFT_CRLF;
        if ($this->_gridSearchStoreLoaded) {
            $url = $this->CutUrlRedundantParam();
            $_searchStoreURL = $url . $this->GetURLArguments() . '/-1';
	        
            if (!empty($this->_gridSearchStoreURL)) {
                $_searchStoreURL = $this->_gridSearchStoreURL;
            }
            $_returnHTML .= '<div class="gridtoolbarsubsearchmode"><div class="gridsearchmode"><a href="javascript: void(0);" onclick="javascript: loadViewportData(\'' . $_searchStoreURL . '\');"><i class="fa fa-times-circle" aria-hidden="true"></i> ' . $this->Language->Get('searchmodeactive') . '</a></div><div class="gridtoolbarsubsearchmodesub">&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/rebarsplitter.gif" align="absmiddle" />&nbsp;</div></div>';
        } else {
            $_returnHTML .= '<div class="gridtoolbarsubsearch"><input type="text" name="gridirs" value="' . htmlspecialchars($_searchQuery) . '" id="gridirs" class="gridirs removeiecross" autocomplete="off" onkeypress="return HandleGridEnter(\'' . $this->GetName() . '\', this, event);" onfocus="window.$gridirs = new GridIRSAutoComplete(this, \'' . $this->GetName() . '\', \'' . $this->Router->GetCurrentURL() . $this->GetURLArguments() . '/' . $this->GetExtendedArguments() . '\', \'' . $this->_gridSearchStoreID . '\');" />&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/rebarsplitter.gif" align="absmiddle" />&nbsp;</div>';
        }

        $_returnHTML .= '<div class="gridtoolbarsub">' . $_massActionToolbarData . '</div>';
        $_returnHTML .= '</div></div>';

        return $_returnHTML;
    }

    /**
     * Cut Url Redundant parameters
     * 
     * @return string
     */
    private function CutUrlRedundantParam() {
        $url = $this->Router->GetCurrentURL();
        foreach (self::URL_TO_REMOVE_PARAMS as $param) {
            $substring = strpos($this->Router->GetCurrentURL(), $param);            
            if ($substring) {
                return substr($url, 0, $substring + strlen($param));
            }
        }
        return $url;
    }

    /**
     * Renders the Main Grid
     *
     * @author Varun Shoor
     * @return string|bool
     */
    private function RenderGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_totalItemCount = $this->GetTotalItemCount();

        $_rowTemplate = $_rowTitleHTML = '';

        $_returnHTML = '<table cellpadding="0" cellspacing="0" border="0" width="100%" class="gridlayoutborder">' . SWIFT_CRLF;
        $_returnHTML .= '<tr><td class="gridcontentborder">';
        $_returnHTML .= '<table border="0" cellpadding="5" cellspacing="1" width="100%">' . SWIFT_CRLF;

        $_returnHTML .= '<tr>';

        $_fieldContainer = $this->GetFieldContainer();
        if (_is_array($_fieldContainer)) {
            foreach ($_fieldContainer as $_key => $_val) {
                if ($_val instanceof SWIFT_UserInterfaceGridField && $_val->GetIsClassLoaded()) {
                    if ($_val->GetType() == SWIFT_UserInterfaceGridField::TYPE_ID) {
                        continue;
                    }

                    $_sortImage = '';

                    $_gridSortFieldObject = $this->GetSortFieldObject();
                    $_gridSortOrder = SWIFT_UserInterfaceGridField::SORT_ASC;

                    if ((isset($_POST['_sortBy']) && isset($_POST['_sortOrder']) && $_POST['_sortBy'] == $_val->GetName() && SWIFT_UserInterfaceGridField::IsValidSortOrder($_POST['_sortOrder'])) || (!isset($this->_gridSettings['sortby']) && !isset($this->_gridSettings['sortorder']) && !isset($_POST['_sortBy']) && !isset($_POST['_sortOrder']) && $_gridSortFieldObject instanceof SWIFT_UserInterfaceGridField && $_gridSortFieldObject->GetIsClassLoaded() && $_gridSortFieldObject->GetName() == $_val->GetName()) || (!isset($_POST['_sortBy']) && !isset($_POST['_sortOrder']) && isset($this->_gridSettings['sortby']) && isset($this->_gridSettings['sortorder']) && $this->_gridSettings['sortby'] == $_val->GetName() && SWIFT_UserInterfaceGridField::IsValidSortOrder($this->_gridSettings['sortorder']))) {
                        $_rowTitleClass = 'gridtabletitlerowsel';
                        $_sortOrder = $_gridSortFieldObject->GetSortOrder();

                        if (isset($_POST['_sortBy']) && isset($_POST['_sortOrder'])) {
                            // Save to Cache
                            $_gridSortOrder = $_POST['_sortOrder'];
                            $_sortOrder = $_POST['_sortOrder'];

                            $this->UpdateGridCache('sortorder', $_sortOrder);
                            $this->UpdateGridCache('sortby', $_val->GetName());
                            $this->Cache->Update('gridcache', $this->_gridCacheContainer);
                        } elseif (isset($this->_gridSettings['sortby']) && isset($this->_gridSettings['sortorder'])) {
                            $_sortOrder = $this->_gridSettings['sortorder'];
                        }

                        $_val->SetSortOrder($_sortOrder);
                        $this->SetSortFieldObject($_val);

                        $_sortImage = '&nbsp;<img src="' . SWIFT::Get('themepath') . 'images/sort' . strtolower($_sortOrder) . '.gif" border="0" />';

                        // Invert
                        if ($_sortOrder == SWIFT_UserInterfaceGridField::SORT_ASC) {
                            $_gridSortOrder = SWIFT_UserInterfaceGridField::SORT_DESC;
                        } else {
                            $_gridSortOrder = SWIFT_UserInterfaceGridField::SORT_ASC;
                        }
                    } else {
                        $_rowTitleClass = 'gridtabletitlerow';
                    }
                    $_extendedClass = '';

                    if ($_val->GetName() == 'icon') {
                        $_rowTitleHTML .= '<td class="' . $_rowTitleClass . $_extendedClass . '"'
                            . IIF(in_array($_val->GetType(), [SWIFT_UserInterfaceGridField::TYPE_DB, SWIFT_UserInterfaceGridField::TYPE_CUSTOM], true), ' onmouseover="javascript: GridTitleMouseOver(this);" onmouseout="javascript: GridTitleMouseOut(this, \'' . $_rowTitleClass . '\');"')
                            . ' width="' . IIF($_val->GetWidth(), $_val->GetWidth(), '') . '" align="' . $_val->GetAlignmentText() . '" nowrap>';
                    } else {
                        $_rowTitleHTML .= '<td class="' . $_rowTitleClass . $_extendedClass . '"'
                            . IIF(in_array($_val->GetType(), [SWIFT_UserInterfaceGridField::TYPE_DB, SWIFT_UserInterfaceGridField::TYPE_CUSTOM], true), ' onclick="javascript:GridSortRequest(\'' . $this->GetName() . '\', \'' . $this->Router->GetCurrentURL() . $this->GetURLArguments() . '/'
                                . $this->_gridSearchStoreID . '/' . $this->GetExtendedArguments() . '\', \'' . $_val->GetName() . '\', \'' . $_gridSortOrder . '\');" onmouseover="javascript: GridTitleMouseOver(this);" onmouseout="javascript: GridTitleMouseOut(this, \'' . $_rowTitleClass . '\');"')
                            . ' width="' . IIF($_val->GetWidth(), $_val->GetWidth(), '') . '" align="' . $_val->GetAlignmentText() . '" nowrap>';
                    }

                    if ($_val->GetType() == SWIFT_UserInterfaceGridField::TYPE_DB) {
                        $_rowTitleHTML .= '&nbsp;' . $_val->GetTitle() . '&nbsp;';
                    } elseif ($_val->GetType() == SWIFT_UserInterfaceGridField::TYPE_CUSTOM) {
                        $_rowTitleHTML .= '&nbsp;' . $_val->GetTitle();
                    }

                    $_rowTitleHTML .= $_sortImage;

                    $_rowTitleHTML .= '</td>';

                    $_rowTemplate .= '<td style="[' . $_val->GetName() . ':]" align="' . $_val->GetAlignmentText() . '">[' . $_val->GetName() . ']</td>';
                }
            }
        }

        if ($this->HasMassAction()) {
            $_returnHTML .= '<td class="gridtabletitlerow gridtabletitlerowcenter" align="center" valign="middle" width=20 nowrap><input type="checkbox" name="allselect" class="swiftcheckbox swiftgridcheckbox" onClick="javascript:toggleAll(\'' . $this->GetName() . '\');" /></td>' . SWIFT_CRLF;
        }

        $_returnHTML .= $_rowTitleHTML;

        // Is sorting enabled? If yes, add the drag drop row
        if ($this->_sortingEnabled) {
            $_returnHTML .= '<td class="gridtabletitlerow" align="center" valign="middle" width=12 nowrap>&nbsp;</td>' . SWIFT_CRLF;
        }

        $_returnHTML .= '</tr>';
        $_gridContents = $this->RenderGridContents($_rowTemplate);
        if ($_gridContents) {
            $_returnHTML .= $_gridContents;
        } else {
            $_fieldCount = count($_fieldContainer);

            if ($this->HasMassAction()) {
                $_fieldCount += 1;
            }

            if ($this->_sortingEnabled) {
                $_fieldCount += 1;
            }

            $_returnHTML .= '<tr><td class="gridrowitalic" colspan="' . $_fieldCount . '" align="left" valign="top">' . $this->Language->Get('noinfoinview') . '</td></tr>';
        }

        $_returnHTML .= '</table></td></tr></table>';

        $_returnHTML .= '<div class="paginationtoolbar">' . SWIFT_CRLF;
        $_returnHTML .= '<table border="0" cellpadding="0" cellspacing="1" class="retborder" style="margin-left: 4px;"><tr>' . self::RenderPagination('javascript:GridPagination("' . $this->GetName() . '", "' . $this->Router->GetCurrentURL() . $this->GetURLArguments() . '/' . $this->_gridSearchStoreID . '/' . $this->GetExtendedArguments() . '", "' . addslashes($this->GetSearchQueryString()) . '", "', $_totalItemCount, $this->GetRecordsPerPage(), $this->GetOffset(), "5", "pageoftotal", TRUE, FALSE, TRUE) . '</tr></table>';
        $_returnHTML .= '</div>';

        if ($this->_massActionPanelEnabled == true) {
            $_returnHTML .= '<div class="massactionpanel" id="gridmassactionpanel">' . SWIFT_CRLF;
            $_returnHTML .= $this->_massActionPanelHTML;
            $_returnHTML .= '</div>';
        }

        return $_returnHTML;
    }

    /**
     * Renders the actual 'contents section' of the grid
     *
     * @author Varun Shoor
     * @param string $_rowTemplate The Row Template
     * @return mixed
     */
    private function RenderGridContents($_rowTemplate)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_itemContainer = $this->GetItems();
        if (!$_itemContainer) {
            return false;
        }

        $_returnHTML = '';

        $_loopCount = 0;
        $_idFieldObject = $this->GetIDField();
        if ($_idFieldObject instanceof SWIFT_UserInterfaceGridField && $_idFieldObject->GetIsClassLoaded() && $_idFieldObject->GetType() == SWIFT_UserInterfaceGridField::TYPE_ID) {
            $_idFieldName = $_idFieldObject->GetName();
        } else {
            return;
        }

        $_returnHTML .= '<tbody class="gridcontents_' . $this->GetName() . '_parent" gridname="' . $this->GetName() . '">';

        $_outputContainer = $_callbackResultContainer = array();

        // create array with all fields ready (including custom)
        foreach ($_itemContainer as $_key => $_val) {
            if (!isset($_val[$_idFieldName])) {
                continue;
            }
            $_callbackResultContainer[$_key] = call_user_func($this->GetRenderCallback(), $_val);
        }
        $sortFieldObject = $this->GetSortFieldObject();

        // sort by custom field
        if ($sortFieldObject->GetType() === SWIFT_UserInterfaceGridField::TYPE_CUSTOM) {
            uksort($_callbackResultContainer, function ($i, $j) use ($_callbackResultContainer, $sortFieldObject) {
                $field = $sortFieldObject->GetName();
                $a = isset($_callbackResultContainer[$i][$field]) ? $_callbackResultContainer[$i][$field] : 0;
                $b = isset($_callbackResultContainer[$j][$field]) ? $_callbackResultContainer[$j][$field] : 0;

                // compare numbers
                if (is_numeric($a) && is_numeric($b)) {
                    if ($a === $b) {
                        return 0;
                    }

                    if ($sortFieldObject->GetSortOrder() === 'desc') {
                        return ($a > $b) ? -1 : 1;
                    }

                    return ($a < $b) ? -1 : 1;
                }

                // compare as string
                if ($sortFieldObject->GetSortOrder() === 'desc') {
                    return strcasecmp($b, $a);
                }

                return strcasecmp($a, $b);
            });
        }

        foreach ($_callbackResultContainer as $_key => $_callBackResult) {
            $_rowID = false;

            $_loopCount++;

            if (!isset($_callBackResult[$_idFieldName])) {
                continue;
            }

            $_rowID = $_callBackResult[$_idFieldName];
            $_extendedStyle = '';
            if (isset($_callBackResult[':'])) {
                $_extendedStyle = $_callBackResult[':'];
            }

            $_rowHTML = '<tr id="gridrowid_' . $this->GetName() . '_' . $_rowID . '" style="' . $_extendedStyle . '" class="' . '%ROWCLASS%' . '" onmouseover="javascript: ' .
                "GridRowHighlight(this);" . '" onmouseout="javascript: ' . "ClearGridRowHighlight(this, '" . '%ROWCLASS%' . "');" .
                '" onclick="javascript: ' . "HandleGridClickRow('" . $this->GetName() . "', '" . $_rowID . "', this, '" . '%ROWCLASS%' . "');" . '">';
            if ($this->HasMassAction()) {
                $_rowHTML .= '<td align="center" valign="middle">' . "<input type='checkbox' name='itemid[]' value='" . $_rowID .
                    "' class=\"swiftcheckbox swiftgridcheckbox swiftgriditemid" . $this->GetName() . "\" onclick=\"HandleGridCheckboxClick('" . $this->GetName() . "', '" . $_rowID .
                    "');\"><input type='hidden' id='itemhighlight_" . $this->GetName() . "_" . $_rowID . "' name='itemhighlight_" .
                    $this->GetName() . "_" . $_rowID . "' value='0'>" . '</td>';
            }

            // We replace all instances of field names in [] braces with their respective values, *much* faster (trust me) than doing a for loop for fields inside another while loop ;)
            $_rowHTML .= @preg_replace_callback("/\[(.+)\]/Ui", function ($_matches) use ($_callBackResult) {
                return $_callBackResult[$_matches[1]] ?? '';
            }, $_rowTemplate);

            // Is sorting enabled? If yes, add the drag drop row
            if ($this->_sortingEnabled) {
                $_rowHTML .= '<td align="center" valign="middle" class="griddragdrop"><input type="hidden" name="sortitemid[]" value="' . $_rowID . '" /><img src="' . SWIFT::Get('themepathimages') . 'icon_dragdrop.png" align="middle" border="0" /></td>';
            }

            if ($this->_hasSubItems) {
                $this->ProcessSubItem($_callBackResult);
            }

            $_rowHTML .= '</tr>';

            $_outputContainer[$_rowID] = $_rowHTML;
        }


        /*
         * ###############################################
         * Process Sub Items
         * ###############################################
        */
        $_subItemContainer = $this->GetSubItems();
        foreach ($_outputContainer as $_outputKey => $_outputVal) {
            $_rowClass = $this->ToggleRowClass();

            $_returnHTML .= str_replace('%ROWCLASS%', $_rowClass, $_outputVal);

            if ($this->_hasSubItems && isset($_subItemContainer[$_outputKey]) && _is_array($_subItemContainer[$_outputKey])) {
                $_returnHTML .= '</tbody><tbody class="gridcontents_' . $this->GetName() . '_sub" gridname="' . $this->GetName() . '">';
                foreach ($_subItemContainer[$_outputKey] as $_key => $_val) {
                    $_rowClass = $this->ToggleRowClass();

                    if (!isset($_val[$_idFieldName])) {
                        continue;
                    }

                    $_rowID = $_val[$_idFieldName];
                    $_callBackResult = call_user_func($this->GetRenderCallback(), $_val, true);
                    $_extendedStyle = '';
                    if (isset($_callBackResult[':'])) {
                        $_extendedStyle = $_callBackResult[':'];
                    }

                    $_rowHTML = '<tr id="gridrowid_' . $this->GetName() . '_' . $_rowID . '" style="' . $_extendedStyle . '" class="' . '%ROWCLASS%' . '" onmouseover="javascript: ' . "GridRowHighlight(this);" . '" onmouseout="javascript: ' . "ClearGridRowHighlight(this, '" . '%ROWCLASS%' . "');" . '" onclick="javascript: ' . "HandleGridClickRow('" . $this->GetName() . "', '" . $_rowID . "', this, '" . '%ROWCLASS%' . "');" . '">';
                    if ($this->HasMassAction()) {
                        $_rowHTML .= '<td align="center" valign="middle">' . "<input type='checkbox' name='itemid[]' value='" . $_rowID . "' class=\"swiftcheckbox swiftgridcheckbox\" onclick=\"HandleGridCheckboxClick('" . $this->GetName() . "', '" . $_rowID . "');\"><input type='hidden' id='itemhighlight_" . $this->GetName() . "_" . $_rowID . "' name='itemhighlight_" . $this->GetName() . "_" . $_rowID . "' value='0'>" . '</td>';
                    }

                    // We replace all instances of field names in [] braces with their respective values, *much* faster (trust me) than doing a for loop for fields inside another while loop ;)
                    $_rowHTML .= @preg_replace_callback("/\[(.+)\]/Ui", function ($_matches) use ($_callBackResult) {
                        return $_callBackResult[$_matches[1]];
                    }, $_rowTemplate);
                    if ($this->_sortingEnabled) {
                        $_rowHTML .= '<td align="center" valign="middle" class="griddragdropsub"><input type="hidden" name="sortitemid[]" value="' . $_rowID . '" /><img src="' . SWIFT::Get('themepathimages') . 'icon_dragdrop.png" align="middle" border="0" /></td>';
                    }

                    $_rowHTML .= '</tr>';

                    $_returnHTML .= str_replace('%ROWCLASS%', $_rowClass, $_rowHTML);
                }
                $_returnHTML .= '</tbody><tbody class="gridcontents_' . $this->GetName() . '_parent" gridname="' . $this->GetName() . '">';
            }
        }

        $_returnHTML .= '</tbody>';

        return $_returnHTML;
    }

    /**
     * Toggles the Row Class
     *
     * @author Varun Shoor
     * @return mixed "_rowClass" (STRING) on Success, "false" otherwise
     */
    private function ToggleRowClass()
    {
        if ($this->_rowClass == 'gridrow2') {
            $this->_rowClass = 'gridrow1';
        } else {
            $this->_rowClass = 'gridrow2';
        }

        return $this->_rowClass;
    }

    /**
     * Set the item fetching callback container
     *
     * @author Varun Shoor
     * @param array $_callbackContainer The Callback Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetItemCallbackContainer($_callbackContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_getItemCallbackContainer = $_callbackContainer;

        return true;
    }

    /**
     * Set a Sort Field Mapping
     *
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @param string $fieldName
     */
    public function SetReversedSortField(string $fieldName)
    {
        $this->_sortFieldsReversed[] = $fieldName;
    }

    /**
     * Set a Sort Field Mapping
     *
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @param string $fieldName
     * @param string $mappingName
     */
    public function SetSortFieldMapping(string $fieldName, string $mappingName)
    {
        $this->_sortFieldsMappings[$fieldName] = $mappingName;
    }

    /**
     * Get sort field mapping query
     *
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @param array $mappingsArray
     * @param string $fieldName
     * @param string $fieldMappingName
     *
     * @return string
     */
    public static function GetSortFieldMappingsQuery(array $mappingsArray, string $fieldName, string $fieldMappingName)
    {
        $query = 'CASE ' . $fieldName;
        foreach ($mappingsArray as $fieldValue => $mappedValue) {
            $query .= ' WHEN ' . $fieldValue . " THEN '" . $mappedValue . "'";
        }
        $query .= ' END AS ' . $fieldMappingName;

        return $query;
    }

    /**
     * Get the Grid Items
     *
     * @author Varun Shoor
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @param bool $_fetchAll (OPTIONAL) Whether to fetch all items
     * @return array|bool
     */
    private function GetItems($_fetchAll = false)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_itemContainer = array();
        if ($this->GetType() == self::TYPE_DB) {
            $_selectQuery = $this->GetModeSelectQuery();
            if (!$_selectQuery || empty($_selectQuery)) {
                return $_itemContainer;
            }

            $_gridSortObject = $this->GetSortFieldObject();
            if (!$_gridSortObject instanceof SWIFT_UserInterfaceGridField || !$_gridSortObject->GetIsClassLoaded()) {
                return false;
            }

            if ($this->_getItemCallbackContainer) {
                return call_user_func_array($this->_getItemCallbackContainer, array($this, $_selectQuery, $_fetchAll));
            }

            // sort mappings help sort items by labels that are mapped to table integer values
            // i.e DB type id = 10 / UI type label = 'pending', so we sort by labels instead of ids
            $sortFieldName = $this->_sortFieldsMappings[$_gridSortObject->GetName()] ?? $_gridSortObject->GetName();

            // Set reversed sort order for reversed sort fields
            if (in_array($_gridSortObject->GetName(), $this->_sortFieldsReversed)) {
                $_reversedSortOrder = strtolower($_gridSortObject->GetSortOrder()) == 'asc' ? 'desc' : 'asc';
                $_gridSortObject->SetSortOrder($_reversedSortOrder);
            }

            $_selectQuery .= ' ORDER BY ' . $sortFieldName . ' ' . Clean($_gridSortObject->GetSortOrder());

            if ($_fetchAll == true) {
                $_queryResult = $this->Database->Query($_selectQuery, 3);
            } else {
                $_queryResult = $this->Database->QueryLimit($_selectQuery, $this->GetRecordsPerPage(), $this->GetOffset(), 3);
            }
            if (!$_queryResult) {
                return $_itemContainer;
            }

            while ($this->Database->NextRecord(3)) {
                $_itemContainer[] = $this->Database->Record3;
            }
        }

        return $_itemContainer;
    }

    /**
     * Process the Sub Item
     *
     * @author Varun Shoor
     * @param array $_itemContainer The Item Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function ProcessSubItem($_itemContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_idFieldObject = $this->GetIDField();
        if ($_idFieldObject instanceof SWIFT_UserInterfaceGridField && $_idFieldObject->GetIsClassLoaded() && $_idFieldObject->GetType() == SWIFT_UserInterfaceGridField::TYPE_ID) {
            $_rowID = $_idFieldObject->GetName();
        } else {
            return false;
        }

        if ($this->GetType() == self::TYPE_DB) {
            // Is there a sub query for this grid?
            if ($this->_hasSubItems && isset($_itemContainer[$_rowID])) {
                $this->_subItemIDContainer[] = $_itemContainer[$_rowID];
            }
        }

        return true;
    }

    /**
     * Get the Sub Items
     *
     * @author Varun Shoor
     * @return mixed "_subItemContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function GetSubItems()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        $_subItemContainer = array();

        $_idFieldObject = $this->GetIDField();
        if ($_idFieldObject instanceof SWIFT_UserInterfaceGridField && $_idFieldObject->GetIsClassLoaded() && $_idFieldObject->GetType() == SWIFT_UserInterfaceGridField::TYPE_ID) {
            $_rowID = $_idFieldObject->GetName();
        } else {
            return false;;
        }

        if ($this->GetType() == self::TYPE_DB) {
            // Is there a sub query for this grid?
            if ($this->_hasSubItems && count($this->_subItemIDContainer)) {
                $_subItemQuery = $this->GetSubSelectQuery();
                if (!$_subItemQuery) {
                    return false;
                }

                $_gridSortObject = $this->GetSortFieldObject();
                if (!$_gridSortObject instanceof SWIFT_UserInterfaceGridField || !$_gridSortObject->GetIsClassLoaded()) {
                    return false;
                }

                $_finalSubItemQuery = sprintf($_subItemQuery, BuildIN($this->_subItemIDContainer));

                $_finalSubItemQuery .= ' ORDER BY ' . $_gridSortObject->GetName() . ' ' . Clean($_gridSortObject->GetSortOrder());

                $_SWIFT->Database->Query($_finalSubItemQuery);
                while ($_SWIFT->Database->NextRecord()) {
                    if (!isset($_SWIFT->Database->Record[$_rowID])) {
                        return false;
                    }

                    $_subSelectField = $this->GetSubSelectField();

                    $_idFieldData = $_SWIFT->Database->Record[$_subSelectField];
                    $_subItemContainer[$_idFieldData][] = $_SWIFT->Database->Record;
                }
            }
        }

        return $_subItemContainer;
    }

    /**
     * Get the Total Item Count
     *
     * @author Varun Shoor
     * @return mixed
     */
    private function GetTotalItemCount()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (!empty($this->_gridCountCache)) {
            return $this->_gridCountCache;
        }

        if ($this->GetType() == self::TYPE_DB) {
            $_countQuery = $this->GetModeCountQuery();
            if (!$_countQuery || empty($_countQuery)) {
                return '0';
            }

            $_countContainer = $this->Database->QueryFetch($_countQuery);
            if (!$_countContainer) {
                return '0';
            }

            if (!isset($_countContainer['totalitems']) && !isset($_countContainer[0]) && !isset($_countContainer['COUNT(*)'])) {
                return '0';
            }

            if (isset($_countContainer[0]) && is_numeric($_countContainer[0])) {
                $_countContainer['totalitems'] = (int)($_countContainer[0]);
            } elseif (isset($_countContainer['COUNT(*)']) && is_numeric($_countContainer['COUNT(*)'])) {
                $_countContainer['totalitems'] = (int)($_countContainer['COUNT(*)']);
            } elseif (isset($_countContainer['totalitems']) && is_numeric($_countContainer['totalitems'])) {
                $_countContainer['totalitems'] = (int)($_countContainer['totalitems']);
            }

            if (!isset($_countContainer['totalitems']) || empty($_countContainer['totalitems'])) {
                return '0';
            }

            $this->_gridCountCache = (int)($_countContainer['totalitems']);

            return $this->_gridCountCache;
        }

        return '0';
    }

    /**
     * Check to see if the lookup was for a tag
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function HasTagLookup()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Did we just lookup using tag?
        if ($this->_tagModeEnabled == true && mb_strtolower(mb_substr($this->GetSearchQueryString(), 0, strlen(self::TAG_PREFIX))) == self::TAG_PREFIX) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the Select Query depending upon the active mode
     *
     * @author Varun Shoor
     * @return mixed Search Query (STRING) on Success, "false" otherwise
     */
    private function GetModeSelectQuery()
    {
        if ($this->_gridSearchStoreLoaded) {
            return sprintf($this->_gridSearchStoreSelectQuery, implode(',', $this->SearchStore->GetSearchStoreData()));
        } elseif ($this->GetMode() == self::MODE_SEARCH) {
            if ($this->HasTagLookup()) {
                return $this->GetTagSelectQuery();
            } else {
                return $this->GetSearchSelectQuery();
            }
        }

        return $this->GetSelectQuery();
    }

    /**
     * Retrieve the Count Query depending upon the active mode
     *
     * @author Varun Shoor
     * @return mixed Count Query (STRING) on Success, "false" otherwise
     */
    private function GetModeCountQuery()
    {
        if ($this->_gridSearchStoreLoaded) {
            return "SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "searchstoredata WHERE searchstoreid = '" . $this->_gridSearchStoreID . "'";
        } elseif ($this->GetMode() == self::MODE_SEARCH) {
            if ($this->HasTagLookup()) {
                return $this->GetTagCountQuery();
            } else {
                return $this->GetSearchCountQuery();
            }
        }

        return $this->GetCountQuery();
    }

    /*
    * string pagination(string base_url, int total_items, int items_per_page, int start_item,
    * int page_range, mixed pages_text, mixed prevnext_links, bool prevnext_always,
    * mixed firstlast_links, bool firstlast_always);
    *
    * Used for:
    * Creating a pagination link set, a pagination is used to divide a large list of items
    * into several pages. This function will help you link to those pages. It is important
    * that you use a good routine for this troughout your website, as it can get
    * frustrating to have to do this separately on each and every page you create.
    *
    * This function won't create the pages, you will have to do that yourself, but it will
    * provide you with a method to easily link to them, with per page customisable ranges
    * and looks.
    *
    * The pagination can look like or be similar to this:
    * 10 Pages � < 2 3 [4] 5 6 > �
    *
    * As you can see it does not display all the pages, but only a certain range, this range
    * can be set by you, just like various other options, which are explained below.
    * Parameters explained:
    * - base_url : The links will refer to this url, don't include arguments
    * - total_items : The total of items for the list
    * - items_per_page : Number of items to show per page
    * - start_item : Item to start at (start counting at 0, not 1!)
    * - page_range : The range of pages to show, can be 1 to infinite
    * - pages_text : The type for the page text to the left of the pagination:
    * - total : Only shows total pages
    * - page : Only shows current page
    * - pageoftotal : Shows current page and total
    * - FALSE (bool) : Shows nothing
    * - prevnext_links : The type for the previous and next links:
    * - num : Shows number for previous and next page
    * - nump : Only shows number for previous page
    * - numn : Only shows number for next page
    * - TRUE (bool) : Show, but no numbers
    * - FALSE (bool) : Shows nothing
    * - prevnext_always : Always show previous and next links, depending on prevnext_links
    * - firstlast_links : The type for the first and last links:
    * - num : Shows number for first and last page
    * - numf : Only shows number for first page
    * - numl : Only shows number for last page
    * - TRUE (bool) : Show, but no numbers
    * - FALSE (bool) : Show nothing
    * - firstlast_always : Always show first and last links, depending on firstlast_links
    */
    public static function RenderPagination($url, $total_items, $per_page, $start, $range = 5, $pages = 'total', $prevnext = TRUE, $prevnext_always = FALSE, $firstlast = TRUE, $firstlast_always = FALSE, $_displayViewAll = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $str_links = "<td class='gridnavpage'><a href='javascript: void(0);' onclick='%s\");' title='" . $_SWIFT->Language->Get('pagination') . "'>%s</a></td>" . SWIFT_CRLF;
        $str_selected = "<td class='gridnavpageselected'><a href='javascript: void(0);' onclick='%s\");' title='" . $_SWIFT->Language->Get('pagination') . "'>%s</a></td>" . SWIFT_CRLF;
        $str_prevnext = "<td class='gridnavpage'><a href='javascript: void(0);' onclick='%s\");'>%s</a></td>" . SWIFT_CRLF;
        $str_firstlast = "<td class='gridnavpage'><a href='javascript: void(0);' onclick='%s\");'>%s</a></td>" . SWIFT_CRLF;
        $_viewAllHTML = "<td class='gridnavpage'><a href='javascript: void(0);' onclick='%s\");' title='" . $_SWIFT->Language->Get('viewall') . "'>" . $_SWIFT->Language->Get('viewall') . "</a></td>" . SWIFT_CRLF;

        $str_pages = "%s ";

        $prev_txt = '&lt;%s';
        $next_txt = '%s&gt;';
        $first_txt = '&laquo; ' . $_SWIFT->Language->Get('first') . '<!--%s -->';
        $last_txt = '<!-- %s -->' . $_SWIFT->Language->Get('last') . ' &raquo;';

        $pages_txt_total = '%s';
        $pages_txt_page = 'Page %s';
        $pages_txt_pageoftotal = '<td class="gridhighlightpage">' . $_SWIFT->Language->Get('pagination') . '</td>' . SWIFT_CRLF;

        $str = '';

        $total_items = ($total_items < 0) ? 0 : $total_items;
        $per_page = ($per_page < 1) ? 1 : $per_page;
        $range = ($range < 1) ? 1 : $range;
        $sel_page = 1;

        $total_pages = ceil($total_items / $per_page);

        if ($total_pages > 1) {
            $sel_page = floor($start / $per_page) + 1;

            $range_min = ($range % 2 == 0) ? ($range / 2) - 1 : ($range - 1) / 2;
            $range_max = ($range % 2 == 0) ? $range_min + 1 : $range_min;
            $page_min = $sel_page - $range_min;
            $page_max = $sel_page + $range_max;

            $page_min = ($page_min < 1) ? 1 : $page_min;
            $page_max = ($page_max < ($page_min + $range - 1)) ? $page_min + $range - 1 : $page_max;
            if ($page_max > $total_pages) {
                $page_min = ($page_min > 1) ? $total_pages - $range + 1 : 1;
                $page_max = $total_pages;
            }

            for ($i = $page_min; $i <= $page_max; $i++) {
                if ($i != 0) {
                    $str .= sprintf((($i == $sel_page) ? $str_selected : $str_links), $url . (($i - 1) * $per_page), $i, $total_pages, $i);
                }
            }

            if (($prevnext) || (($prevnext) && ($prevnext_always))) {
                $prev_num = (($prevnext === 'num') || ($prevnext === 'nump')) ? $sel_page - 1 : '';
                $next_num = (($prevnext === 'num') || ($prevnext === 'numn')) ? $sel_page + 1 : '';

                $prev_txt = sprintf($prev_txt, $prev_num);
                $next_txt = sprintf($next_txt, $next_num);

                if (($sel_page > 1) || ($prevnext_always)) {
                    $start_at = ($sel_page - 2) * $per_page;
                    $start_at = ($start_at < 0) ? 0 : $start_at;
                    $str = sprintf($str_prevnext, $url . $start_at, $prev_txt) . $str;
                }

                if (($sel_page < $total_pages) || ($prevnext_always)) {
                    $start_at = $sel_page * $per_page;
                    $start_at = ($start_at >= $total_items) ? $total_items - $per_page : $start_at;
                    $str .= sprintf($str_prevnext, $url . $start_at, $next_txt);
                }
            }

            if (($firstlast) || (($firstlast) && ($firstlast_always))) {
                $first_num = 1;
                $last_num = $total_pages;

                $first_txt = sprintf($first_txt, $first_num);
                $last_txt = sprintf($last_txt, $last_num);

                if ((($sel_page > ($range - $range_min)) && ($total_pages > $range)) || ($firstlast_always)) {
                    $str = sprintf($str_firstlast, $url . '0', $first_txt) . $str;
                }
                if ((($sel_page < ($total_pages - $range_max)) && ($total_pages > $range)) || ($firstlast_always)) {
                    $lastoffset = self::GetLastPageOffset($total_items, $per_page);  // Fixed offset bug [VARUN]
                    $str .= sprintf($str_firstlast, $url . $lastoffset, $last_txt);
                }
            }
        }

        if ($_displayViewAll) {
            $str .= sprintf($_viewAllHTML, $url . '-1');
        }

        if ($pages) {
            switch ($pages) {
                case 'total':
                    $pages_txt = sprintf($pages_txt_total, $total_pages);
                    break;
                case 'page':
                    $pages_txt = sprintf($pages_txt_page, $sel_page);
                    break;
                case 'pageoftotal':
                    if ($total_pages == "0") {
                        $total_pages = "1";
                    }
                    $pages_txt = sprintf($pages_txt_pageoftotal, $sel_page, $total_pages);
                    break;
                default:
                    $pages_txt = '';
                    break;
            }
            $str = sprintf($str_pages, $pages_txt) . $str;
        }

        return $str;
    }


    /**
     * Render the Small Pagination
     *
     * @author Varun Shoor
     * @return string Parsed Pagination
     */
    public static function RenderSmallPagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = TRUE, $show_all = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $total_pages = ceil($num_items / $per_page);
        if ($total_pages == 1 || empty($total_pages)) {
            return "";
        }

        $on_page = floor($start_item / $per_page) + 1;

        $page_string = "";

        if ($total_pages > 8) {

            $init_page_max = ($total_pages > 2) ? 2 : $total_pages;

            for ($i = 1; $i < $init_page_max + 1; $i++) {
                $page_string .= ($i == $on_page) ? $i : "<a href=\"javascript: void(0);\" onclick=\"javascript: loadViewportData('" . sprintf($base_url, (($i - 1) * $per_page)) . "');\">" . $i . "</a>";
                if ($i < $init_page_max) {
                    $page_string .= ", ";
                }
            }

            if ($total_pages > 2) {
                if ($on_page > 1 && $on_page < $total_pages) {
                    $page_string .= ($on_page > 4) ? " ... " : ", ";

                    $init_page_min = ($on_page > 3) ? $on_page : 4;
                    $init_page_max = ($on_page < $total_pages - 3) ? $on_page : $total_pages - 3;

                    for ($i = $init_page_min - 1; $i < $init_page_max + 2; $i++) {
                        $page_string .= ($i == $on_page) ? $i : "<a href=\"javascript: void(0);\" onclick=\"javascript: loadViewportData('" . sprintf($base_url, (($i - 1) * $per_page)) . "');\">" . $i . "</a>";
                        if ($i < $init_page_max + 1) {
                            $page_string .= ", ";
                        }
                    }

                    $page_string .= ($on_page < $total_pages - 3) ? " ... " : ", ";
                } else {
                    $page_string .= " ... ";
                }

                for ($i = $total_pages - 1; $i < $total_pages + 1; $i++) {
                    $page_string .= ($i == $on_page) ? $i : "<a href=\"javascript: void(0);\" onclick=\"javascript: loadViewportData('" . sprintf($base_url, (($i - 1) * $per_page)) . "');\">" . $i . "</a>";
                    if ($i < $total_pages) {
                        $page_string .= ", ";
                    }
                }
            }
        } else {
            for ($i = 1; $i < $total_pages + 1; $i++) {
                $page_string .= ($i == $on_page) ? $i : "<a href=\"javascript: void(0);\" onclick=\"javascript: loadViewportData('" . sprintf($base_url, (($i - 1) * $per_page)) . "');\">" . $i . "</a>";
                if ($i < $total_pages) {
                    $page_string .= ", ";
                }
            }
        }
        $start_item++;
        $on_page = floor($start_item / $per_page) + 1;
        if ($on_page > 1) {
            $page_string = " <a href=\"javascript: void(0);\" onclick=\"javascript: loadViewportData('" . sprintf($base_url, (($on_page - 2) * $per_page)) . "');\">" . "&laquo;" . "</a>&nbsp;&nbsp;" . $page_string;
        }

        $allInsert = $show_all ? ", <a href=\"" . sprintf($base_url, '-1') . "\">" . $_SWIFT->Language->Get('paginall') . "</a>" : "";
        if (!empty($page_string)) {
            $page_string = "&nbsp;&nbsp;(<i class='fa fa-files-o'></i>" . " " . $page_string . $allInsert . ")";
        }
        return $page_string;
    }

    /**
     * Gets the last page offset
     *
     * @author Varun Shoor
     * @param int $_totalItems The Total Items
     * @param int $_itemsPerPage Number of Items per Page
     * @return number Last Offset
     */
    public static function GetLastPageOffset($_totalItems, $_itemsPerPage)
    {
        $_totalPages = ceil($_totalItems / $_itemsPerPage);
        $_lastOffsetPage = floor($_totalItems / $_itemsPerPage);
        if ($_lastOffsetPage == $_totalPages) {
            $_lastOffsetPage--;
        }
        $_lastOffset = $_lastOffsetPage * $_itemsPerPage;

        return $_lastOffset;
    }

    /**
     * Retrieve the Field Container
     *
     * @author Varun Shoor
     * @return mixed "_fieldContainer" (ARRAY) on Success, "false" otherwise
     */
    public function GetFieldContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_fieldContainer;
    }

    /**
     * Retrieve the Mass Action Container
     *
     * @author Varun Shoor
     * @return mixed "_massActionContainer" (ARRAY) on Success, "false" otherwise
     */
    public function GetMassActionContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_massActionContainer;
    }

    /**
     * Check to see if the grid has mass action elements
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function HasMassAction()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (count($this->_massActionContainer)) {
            return true;
        }

        return false;
    }

    /**
     * Build the SQL for searching on a field using LIKE and NOT LIKE statements
     *
     * @author Varun Shoor
     * @param string $_fieldName The Field Name
     * @param bool $_noParity Whether there should be parity in data
     * @return mixed
     */
    public function BuildSQLSearch($_fieldName, $_noParity = false)
    {
        if (!$this->GetIsClassLoaded() || $this->GetMode() != self::MODE_SEARCH || trim($this->GetSearchQueryString()) == '') {
            return '1=1';
        }

        $_searchQuery = $this->GetSearchQueryString();

        return BuildSQLSearch($_fieldName, $_searchQuery, $_noParity, false);
    }

    /**
     * Build the SQL for searching on a field using Full Text search
     *
     * @author Mansi Wason <mansi.wason@kayako.com>
     *
     * @param string $_fieldName
     *
     * @return string
     */
    public function BuildFullTextSearch($_fieldName)
    {

        $_SWIFT = SWIFT::GetInstance();
        /**
         * BUG FIX - Nidhi Gupta <nidhi.gupta@kayako.com>
         *
         * SWIFT-4895: Introduce "In Boolean Mode" for staff cp's full text search
         */
        return 'MATCH(' . $_SWIFT->Database->Escape($_fieldName) . " ) AGAINST('" . $_SWIFT->Database->Escape($this->GetSearchQueryString()) . "' IN BOOLEAN MODE)";
    }

    public function skipInlineImagesFromPostContentsSearchQuery($_fieldName)
    {
        $_SWIFT = SWIFT::GetInstance();
        $searchQuery = preg_quote($_SWIFT->Database->Escape($this->GetSearchQueryString()));
        return $_SWIFT->Database->Escape($_fieldName) . " NOT REGEXP concat(char(60),'img[',char(94),char(62),']*" . $searchQuery . "[',char(94),char(62),']*')";
    }

    /**
     * Retrieve the default sort field and order for a given grid
     *
     * @author Varun Shoor
     * @param string $_gridName The Grid Name
     * @param string $_defaultSortField The Default Sort Field
     * @param string $_defaultSortOrder The Default Sort Order
     * @return array Return an array with the sort field and order
     */
    public static function GetGridSortField($_gridName, $_defaultSortField, $_defaultSortOrder)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_gridCache = $_SWIFT->Cache->Get('gridcache');

        if (!isset($_gridCache[$_SWIFT->Staff->GetStaffID()][$_gridName])) {
            return array($_defaultSortField, strtoupper(Clean($_defaultSortOrder)));
        }

        $_gridSettings = $_gridCache[$_SWIFT->Staff->GetStaffID()][$_gridName];

        if (isset($_gridSettings['sortby']) && isset($_gridSettings['sortorder'])) {
            return array($_gridSettings['sortby'], strtoupper(Clean($_gridSettings['sortorder'])));
        }

        return array($_defaultSortField, strtoupper(Clean($_defaultSortOrder)));
    }

    /**
     * Handles the callback that receives the resorted fields
     *
     * @author Varun Shoor
     * @param string $_displayOrderField The Display Order Field Name
     * @param array $_callbackContainer The Callback Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetSortableCallback($_displayOrderField, $_callbackContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_sortingEnabled = true;
        $this->_sortingField = $_displayOrderField;
        $this->_sortableCallbackContainer = $_callbackContainer;

        return true;
    }

    /**
     * Set the extended button container
     *
     * @author Varun Shoor
     * @param array $_extendedButtonContainer The Button Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetExtendedButtons($_extendedButtonContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!_is_array($_extendedButtonContainer)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_extendedButtonContainer = $_extendedButtonContainer;

        return true;
    }

    /**
     * Set the Mass Action Panel HTML
     *
     * @author Varun Shoor
     * @param string $_massActionPanelHTML The Mass Action Panel HTML
     * @param array $_massActionPanelCallback The Callback Array
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetMassActionPanel($_massActionPanelHTML, $_massActionPanelCallback)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_massActionPanelHTML) || !_is_array($_massActionPanelCallback) || !class_exists($_massActionPanelCallback[0], false)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_massActionPanelEnabled = true;
        $this->_massActionPanelHTML = $_massActionPanelHTML;
        $this->_massActionPanelCallback = $_massActionPanelCallback;

        $_fieldName = '_massActionPanel';

        if (
            isset($_POST[$_fieldName]) && !empty($_POST[$_fieldName]) && $_POST[$_fieldName] == '1' && isset($_POST['itemid']) &&
            _is_array($_POST['itemid'])
        ) {
            $_callBackResult = call_user_func($this->_massActionPanelCallback, $_POST['itemid']);
        }

        return true;
    }

    /**
     * Set the Search Store URL
     *
     * @author Varun Shoor
     * @param string $_gridSearchStoreURL The Search Store URL
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetSearchStoreURL($_gridSearchStoreURL)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_gridSearchStoreURL = $_gridSearchStoreURL;

        return true;
    }
}
