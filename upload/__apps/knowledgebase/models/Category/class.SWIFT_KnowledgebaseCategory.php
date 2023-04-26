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

namespace Knowledgebase\Models\Category;

use Base\Library\Staff\SWIFT_Staff_Exception;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Base\Models\User\SWIFT_UserGroupAssign;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticleLink;
use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use SWIFT_StringConverter;

/**
 * The Knowledgebase Category Model Class
 *
 * @author Varun Shoor
 */
class SWIFT_KnowledgebaseCategory extends SWIFT_Model
{
    const TABLE_NAME        =    'kbcategories';
    const PRIMARY_KEY        =    'kbcategoryid';

    const TABLE_STRUCTURE    =    "kbcategoryid I PRIMARY AUTO NOTNULL,
                                parentkbcategoryid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                totalarticles I DEFAULT '0' NOTNULL,
                                categorytype I2 DEFAULT '0' NOTNULL,
                                displayorder I DEFAULT '0' NOTNULL,

                                articlesortorder I2 DEFAULT '0' NOTNULL,
                                allowcomments I2 DEFAULT '0' NOTNULL,
                                allowrating I2 DEFAULT '0' NOTNULL,
                                ispublished I2 DEFAULT '0' NOTNULL,

                                uservisibilitycustom I2 DEFAULT '0' NOTNULL,
                                staffvisibilitycustom I2 DEFAULT '0' NOTNULL,

                                isimporteddownloadcategory I2 DEFAULT '0' NOTNULL";

    const INDEX_1            =    'parentkbcategoryid';
    const INDEX_2            =    'categorytype, parentkbcategoryid, uservisibilitycustom, staffvisibilitycustom';
    const INDEX_3            =    'uservisibilitycustom, categorytype';
    const INDEX_4            =    'title, kbcategoryid';


    protected $_dataStore = array();
    static protected $_knowledgebaseCategoryCache = array();
    static protected $_parentCategoryTypeCache = array();

    // Core Constants
    const TYPE_GLOBAL = 1;
    const TYPE_PUBLIC = 2;
    const TYPE_PRIVATE = 3;
    const TYPE_INHERIT = 4;

    const SORT_INHERIT = 1;
    const SORT_TITLE = 2;
    const SORT_RATING = 3;
    const SORT_CREATIONDATE = 4;
    const SORT_DISPLAYORDER = 5;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Exception
     * @throws SWIFT_Category_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Category_Exception('Failed to load Knowledgebase Category Object');
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function __destruct()
    {
        $this->_classLoaded = true;
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Category_Exception If the Class is not Loaded
     * @throws SWIFT_Exception
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Category_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'kbcategories', $this->GetUpdatePool(), 'UPDATE', "kbcategoryid = '" . ($this->GetKnowledgebaseCategoryID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Knowledgebase Category ID
     *
     * @author Varun Shoor
     * @return mixed "kbcategoryid" on Success, "false" otherwise
     * @throws SWIFT_Category_Exception If the Class is not Loaded
     */
    public function GetKnowledgebaseCategoryID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Category_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['kbcategoryid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        $isClassLoaded = $_SWIFT_DataObject->GetIsClassLoaded();
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $isClassLoaded)
        {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "kbcategories WHERE kbcategoryid = '" . ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['kbcategoryid']) && !empty($_dataStore['kbcategoryid']))
            {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $isClassLoaded) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['kbcategoryid']) || empty($this->_dataStore['kbcategoryid']))
            {
                throw new SWIFT_Category_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Category_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Category_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Category_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Category_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Category_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Category_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid category type
     *
     * @author Varun Shoor
     * @param mixed $_categoryType The Category Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidCategoryType($_categoryType)
    {
        return $_categoryType == self::TYPE_GLOBAL || $_categoryType == self::TYPE_PRIVATE || $_categoryType == self::TYPE_PUBLIC || $_categoryType == self::TYPE_INHERIT;
    }

    /**
     * Check to see if its a valid Article Sorting Order
     *
     * @author Varun Shoor
     * @param mixed $_articleSortOrder The Article Sort Order
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidArticleSortOrder($_articleSortOrder)
    {
        return $_articleSortOrder == self::SORT_TITLE || $_articleSortOrder == self::SORT_RATING || $_articleSortOrder == self::SORT_CREATIONDATE || $_articleSortOrder == self::SORT_DISPLAYORDER ||
            $_articleSortOrder == self::SORT_INHERIT;
    }

    /**
     * Create a new Knowledgebase Category
     *
     * @author Varun Shoor
     * @param int $_parentCategoryID The Parent Category ID
     * @param string $_title The Category Title
     * @param mixed $_categoryType The Category Type
     * @param int $_displayOrder The Category Display Order
     * @param mixed $_articleSortOrder The Article Sort Order
     * @param bool $_allowComments Whether to Allow Comments
     * @param bool $_allowRating Whether to Allow Rating
     * @param bool $_isPublished Whether the category is marked as Published
     * @param bool $_userVisibilityCustom (OPTIONAL) Whether to restrict category to certain user groups
     * @param array $_userGroupIDList (OPTIONAL) The User Group ID List
     * @param bool $_staffVisibilityCustom (OPTIONAL) Whether to restrict the category to certain staff groups
     * @param array $_staffGroupIDList (OPTIONAL) The Staff Group ID List
     * @param bool $_staffID (OPTIONAL) The Creator Staff ID
     * @return int The Knowledgebase Category ID
     * @throws SWIFT_Category_Exception If Invalid Data is Provided or If the Object could not be created
     * @throws SWIFT_Exception
     */
    public static function Create($_parentCategoryID, $_title, $_categoryType, $_displayOrder, $_articleSortOrder, $_allowComments, $_allowRating, $_isPublished, $_userVisibilityCustom = false,
            $_userGroupIDList = array(), $_staffVisibilityCustom = false, $_staffGroupIDList = array(), $_staffID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_title) || !self::IsValidArticleSortOrder($_articleSortOrder) || !self::IsValidCategoryType($_categoryType))
        {
            throw new SWIFT_Category_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'kbcategories', array('parentkbcategoryid' => ($_parentCategoryID), 'title' => $_title, 'categorytype' => ($_categoryType),
            'displayorder' => ($_displayOrder), 'articlesortorder' => ($_articleSortOrder), 'allowrating' => ($_allowRating), 'ispublished' => ($_isPublished),
            'staffid' => ($_staffID), 'uservisibilitycustom' => ($_userVisibilityCustom), 'staffvisibilitycustom' => ($_staffVisibilityCustom),
            'allowcomments' => ($_allowComments)), 'INSERT');
        $_knowledgebaseCategoryID = $_SWIFT->Database->Insert_ID();

        if (!$_knowledgebaseCategoryID)
        {
            throw new SWIFT_Category_Exception(SWIFT_CREATEFAILED);
        }

        if ($_userVisibilityCustom == true)
        {
            if (_is_array($_userGroupIDList))
            {
                foreach ($_userGroupIDList as $_userGroupID)
                {
                    SWIFT_UserGroupAssign::Insert($_knowledgebaseCategoryID, SWIFT_UserGroupAssign::TYPE_KBCATEGORY, $_userGroupID);
                }
            }
        }

        if ($_staffVisibilityCustom == true)
        {
            if (_is_array($_staffGroupIDList))
            {
                foreach ($_staffGroupIDList as $_staffGroupID)
                {
                    SWIFT_StaffGroupLink::Create($_staffGroupID, SWIFT_StaffGroupLink::TYPE_KBCATEGORY, $_knowledgebaseCategoryID);
                }
            }
        }

        self::RebuildCache(true);

        return $_knowledgebaseCategoryID;
    }

    /**
     * Update the Knowledgebase Category Record
     *
     * @author Varun Shoor
     * @param int $_parentCategoryID The Parent Category ID
     * @param string $_title The Category Title
     * @param mixed $_categoryType The Category Type
     * @param int $_displayOrder The Category Display Order
     * @param mixed $_articleSortOrder The Article Sort Order
     * @param bool $_allowComments Whether to Allow Comments
     * @param bool $_allowRating Whether to Allow Rating
     * @param bool $_isPublished Whether the category is marked as Published
     * @param bool $_userVisibilityCustom (OPTIONAL) Whether to restrict category to certain user groups
     * @param array $_userGroupIDList (OPTIONAL) The User Group ID List
     * @param bool $_staffVisibilityCustom (OPTIONAL) Whether to restrict the category to certain staff groups
     * @param array $_staffGroupIDList (OPTIONAL) The Staff Group ID List
     * @param bool $_staffID (OPTIONAL) The Creator Staff ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Category_Exception If the Class is not Loaded or If Invalid Data is Provided
     * @throws SWIFT_Exception
     * @throws SWIFT_Staff_Exception
     */
    public function Update($_parentCategoryID, $_title, $_categoryType, $_displayOrder, $_articleSortOrder, $_allowComments, $_allowRating, $_isPublished, $_userVisibilityCustom = false,
            $_userGroupIDList = array(), $_staffVisibilityCustom = false, $_staffGroupIDList = array(), $_staffID = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Category_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('parentkbcategoryid', ($_parentCategoryID));
        $this->UpdatePool('title', $_title);
        $this->UpdatePool('categorytype', ($_categoryType));
        $this->UpdatePool('displayorder', ($_displayOrder));
        $this->UpdatePool('articlesortorder', ($_articleSortOrder));
        $this->UpdatePool('allowrating', ($_allowRating));
        $this->UpdatePool('allowcomments', ($_allowComments));
        $this->UpdatePool('ispublished', ($_isPublished));
        $this->UpdatePool('staffid', ($_staffID));
        $this->UpdatePool('uservisibilitycustom', ($_userVisibilityCustom));
        $this->UpdatePool('staffvisibilitycustom', ($_staffVisibilityCustom));
        $this->ProcessUpdatePool();

        SWIFT_UserGroupAssign::DeleteList(array($this->GetKnowledgebaseCategoryID()), SWIFT_UserGroupAssign::TYPE_KBCATEGORY);
        if ($_userVisibilityCustom == true)
        {
            if (_is_array($_userGroupIDList))
            {
                foreach ($_userGroupIDList as $_userGroupID)
                {
                    SWIFT_UserGroupAssign::Insert($this->GetKnowledgebaseCategoryID(), SWIFT_UserGroupAssign::TYPE_KBCATEGORY, $_userGroupID);
                }
            }
        }

        SWIFT_StaffGroupLink::DeleteOnLinkList(SWIFT_StaffGroupLink::TYPE_KBCATEGORY, array($this->GetKnowledgebaseCategoryID()));
        if ($_staffVisibilityCustom == true)
        {
            if (_is_array($_staffGroupIDList))
            {
                foreach ($_staffGroupIDList as $_staffGroupID)
                {
                    SWIFT_StaffGroupLink::Create($_staffGroupID, SWIFT_StaffGroupLink::TYPE_KBCATEGORY, $this->GetKnowledgebaseCategoryID());
                }
            }
        }

        self::RebuildCache(true);

        return true;
    }

    /**
     * Delete the Knowledgebase Category record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Category_Exception If the Class is not Loaded
     * @throws SWIFT_Exception
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Category_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetKnowledgebaseCategoryID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Knowledgebase Categories
     *
     * @author Varun Shoor
     * @param array $_knowledgebaseCategoryIDList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_knowledgebaseCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_knowledgebaseCategoryIDList))
        {
            return false;
        }

        $_finalKnowledgebaseCategoryIDList = array_merge($_knowledgebaseCategoryIDList, self::RetrieveSubCategoryIDList($_knowledgebaseCategoryIDList));

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "kbcategories WHERE kbcategoryid IN (" . BuildIN($_finalKnowledgebaseCategoryIDList) . ")");

        SWIFT_KnowledgebaseArticleLink::DeleteOnLinkType(SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY, $_finalKnowledgebaseCategoryIDList);

        self::RebuildCache(true);

        return true;
    }

    /**
     * Retrieve list of sub categories
     *
     * @author Varun Shoor
     * @param array $_parentKnowledgebaseCategoryIDList
     * @return array
     * @throws SWIFT_Exception
     */
    public static function RetrieveSubCategoryIDList($_parentKnowledgebaseCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_parentKnowledgebaseCategoryIDList))
        {
            return array();
        }

        if (!static::$_knowledgebaseCategoryCache)
        {
            static::$_knowledgebaseCategoryCache = self::RetrieveCategories();
        }

        $_knowledgebaseParentMap = array();

        extract(static::$_knowledgebaseCategoryCache);

        $_subCategoryIDList = $_fetchParentCategoryIDList = [];

        foreach ($_parentKnowledgebaseCategoryIDList as $_knowledgebaseCategoryID)
        {
            if (isset($_knowledgebaseParentMap[$_knowledgebaseCategoryID]))
            {
                foreach ($_knowledgebaseParentMap[$_knowledgebaseCategoryID] as $_subKey => $_subVal)
                {
                    $_subCategoryIDList[] = $_subVal['kbcategoryid'];
                    $_fetchParentCategoryIDList[] = $_subVal['kbcategoryid'];
                }
            }
        }

        if (count($_fetchParentCategoryIDList) > 0)
        {
            $_subCategoryIDList = array_merge($_subCategoryIDList, self::RetrieveSubCategoryIDList($_fetchParentCategoryIDList));
        }

        return $_subCategoryIDList;
    }

    /**
     * Retrieve the knowledgebase categories in one go..
     *
     * @author Varun Shoor
     * @return array An array containig the categories and the parent <> child relationship map
     * @throws SWIFT_Exception
     */
    public static function RetrieveCategories()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_knowledgebaseCategoryContainer = $_knowledgebaseParentMap = array();

        /**
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-1326 "Knowledgebase category restrictions to staff teams do not take effect".
         *
         * Comment - Adjusting the restricted categories under StaffCP->Knowledgebase->Categories and New Article.
         */
        $_StaffKnowledgebaseCategoryIDList = ($_SWIFT->Staff instanceof  SWIFT_Staff)? SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_KBCATEGORY, $_SWIFT->Staff->GetProperty('staffgroupid')) : [];

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . SWIFT_KnowledgebaseCategory::TABLE_NAME . "
                                    WHERE categorytype IN ('" . SWIFT_KnowledgebaseCategory::TYPE_GLOBAL . "', '" . SWIFT_KnowledgebaseCategory::TYPE_PRIVATE . "', '" . SWIFT_KnowledgebaseCategory::TYPE_INHERIT . "', '" . SWIFT_KnowledgebaseCategory::TYPE_PUBLIC . "')
                                      AND (staffvisibilitycustom = '0' OR (staffvisibilitycustom = '1' AND kbcategoryid IN (" . BuildIN($_StaffKnowledgebaseCategoryIDList) . "))) ORDER BY displayorder ASC");

        while ($_SWIFT->Database->NextRecord())
        {
            $_knowledgebaseCategoryContainer[$_SWIFT->Database->Record['kbcategoryid']] = $_SWIFT->Database->Record;

            if (!isset($_knowledgebaseParentMap[$_SWIFT->Database->Record['parentkbcategoryid']]))
            {
                $_knowledgebaseParentMap[$_SWIFT->Database->Record['parentkbcategoryid']] = array();
            }

            $_knowledgebaseParentMap[$_SWIFT->Database->Record['parentkbcategoryid']][] = $_SWIFT->Database->Record;
        }

        return array('_knowledgebaseCategoryContainer' => $_knowledgebaseCategoryContainer, '_knowledgebaseParentMap' => $_knowledgebaseParentMap);
    }

    /**
     * Retrieve the Last Possible Display Order for a Knowledgebase Category
     *
     * @author Varun Shoor
     * @return int The Last Possible Display Order
     * @throws SWIFT_Exception
     */
    public static function GetLastDisplayOrder()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Cache->Load('kbcategorycache');

        $_statusCache = $_SWIFT->Cache->Get('kbcategorycache');

        if (!_is_array($_statusCache))
        {
            return 1;
        }

        // Get Last Insert ID
        $_lastInsertID = max(array_keys($_statusCache));

        return ($_statusCache[$_lastInsertID]['displayorder'] + 1);
    }

    /**
     * Retrieve the User Group ID's linked with this Knowledgebase Category
     *
     * @author Varun Shoor
     * @return mixed "_userGroupIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLinkedUserGroupIDList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_UserGroupAssign::RetrieveList(SWIFT_UserGroupAssign::TYPE_KBCATEGORY, $this->GetKnowledgebaseCategoryID());
    }

    /**
     * Retrieve the Staff Group ID's linked with this Knowledgebase Category
     *
     * @author Varun Shoor
     * @return mixed "_staffGroupIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLinkedStaffGroupIDList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_StaffGroupLink::RetrieveList(SWIFT_StaffGroupLink::TYPE_KBCATEGORY, $this->GetKnowledgebaseCategoryID());
    }

    /**
     * Rebuild the Knowledgebase Category Cache
     *
     * @author Varun Shoor
     * @param bool $_recount (OPTIONAL) Whether to recount the list
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RebuildCache($_recount = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cacheContainer = $_knowledgebaseCategoryIDList = $_parentKBCategoryIDList = array();

        $_SWIFT->Database->Query("SELECT kbcategoryid FROM " . TABLE_PREFIX . "kbcategories WHERE parentkbcategoryid = '0'");
        while ($_SWIFT->Database->NextRecord())
        {
            $_parentKBCategoryIDList[] = $_SWIFT->Database->Record['kbcategoryid'];
        }

        if ($_recount)
        {
            $_processedKBCategoryIDList = array();

            self::RecountKBTree($_parentKBCategoryIDList, $_processedKBCategoryIDList);
        }

        $_parentMap = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "kbcategories ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_cacheContainer[$_SWIFT->Database->Record['kbcategoryid']] = $_SWIFT->Database->Record;
            $_knowledgebaseCategoryIDList[] = $_SWIFT->Database->Record['kbcategoryid'];

            if (!isset($_parentMap[$_SWIFT->Database->Record['parentkbcategoryid']]))
            {
                $_parentMap[$_SWIFT->Database->Record['parentkbcategoryid']] = array();
            }

            $_parentMap[$_SWIFT->Database->Record['parentkbcategoryid']][] = $_SWIFT->Database->Record['kbcategoryid'];
        }

        foreach ($_cacheContainer as $_knowledgebaseCategoryID => $_knowledgebaseCategoryContainer)
        {
            if (!isset($_knowledgebaseCategoryContainer['childrenidlist']))
            {
                $_cacheContainer[$_knowledgebaseCategoryID]['childrenidlist'] = array();
            }

            if (isset($_parentMap[$_knowledgebaseCategoryID]))
            {
                $_cacheContainer[$_knowledgebaseCategoryID]['childrenidlist'] = $_parentMap[$_knowledgebaseCategoryID];
            }
        }

        $_SWIFT->Cache->Update('kbcategorycache', $_cacheContainer);

        return true;
    }

    /**
     * Recount the KB Tree
     *
     * @author Varun Shoor
     * @param array $_parentKBCategoryIDList The Parent Article ID List
     * @return int
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function RecountKBTree($_parentKBCategoryIDList, &$_processedKBCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_SWIFT->Settings->Get('kb_parcount') != '1')
        {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'kbcategories', array('totalarticles' => '0'), 'UPDATE', "1 = 1");

            return 0;
        }

        $_totalArticleCount = 0;

        foreach ($_parentKBCategoryIDList as $_knowledgebaseCategoryID)
        {
            $_activeCategoryCountContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems, kbarticlelinks.linktype, kbarticlelinks.linktypeid
                FROM " . TABLE_PREFIX . "kbarticlelinks AS kbarticlelinks
                LEFT JOIN " . TABLE_PREFIX . "kbarticles AS kbarticles ON (kbarticlelinks.kbarticleid = kbarticles.kbarticleid)
                WHERE kbarticles.articlestatus = '" . SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED . "'
                GROUP BY kbarticlelinks.linktypeid HAVING kbarticlelinks.linktype = '" . (SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY) . "'
                    AND kbarticlelinks.linktypeid = '" . ($_knowledgebaseCategoryID) . "'");
            // Have we already processed this category?
            if (!in_array($_knowledgebaseCategoryID, $_parentKBCategoryIDList) && in_array($_knowledgebaseCategoryID, $_processedKBCategoryIDList))
            {
                // @codeCoverageIgnoreStart
                // This code will never be executed
                return 0;
                // @codeCoverageIgnoreEnd
            }

            $_processedKBCategoryIDList[] = $_knowledgebaseCategoryID;

            $_subCategoryIDList = array();
            $_SWIFT->Database->Query("SELECT kbcategoryid FROM " . TABLE_PREFIX . "kbcategories WHERE parentkbcategoryid = '" . ($_knowledgebaseCategoryID) . "'");
            while ($_SWIFT->Database->NextRecord())
            {
                $_subCategoryIDList[] = $_SWIFT->Database->Record['kbcategoryid'];
            }

            $_articleCount = self::RecountKBTree($_subCategoryIDList, $_processedKBCategoryIDList) + $_activeCategoryCountContainer['totalitems'];

            $_totalArticleCount += $_articleCount;

            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'kbcategories', array('totalarticles' => $_articleCount), 'UPDATE', "kbcategoryid = '" . ($_knowledgebaseCategoryID) . "'");
        }

        return $_totalArticleCount;
    }

    /**
     * Retrieve the Knowledgebase Category Tree based on specified criteria
     *
     * @author Varun Shoor
     * @param array $_categoryTypeList The Category Type List
     * @param int $_staffGroupID (OPTIONAL) The Staff Group ID
     * @param int $_userGroupID (OPTIONAL) The User Group ID
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveTree($_categoryTypeList, $_staffGroupID = 0, $_userGroupID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_knowledgebaseCategoryContainer = array();

        $_filterUserKBCategoryIDList = $_filterStaffKBCategoryIDList = false;
        if (!empty($_userGroupID))
        {
            $_filterUserKBCategoryIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup($_userGroupID, SWIFT_UserGroupAssign::TYPE_KBCATEGORY);
        }

        if (!empty($_staffGroupID))
        {
            $_filterStaffKBCategoryIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_KBCATEGORY, $_staffGroupID);
        }

        $_knowledgebaseCategoryContainer = self::RetrieveTreeSub(0, $_categoryTypeList, $_filterUserKBCategoryIDList, $_filterStaffKBCategoryIDList);

        return $_knowledgebaseCategoryContainer;
    }

    /**
     * Retrieve the Tree Structure
     *
     * @author Varun Shoor
     * @param int $_parentCategoryID The Parent Category ID
     * @param array $_categoryTypeList The Category Type List
     * @param array $_filterUserKBCategoryIDList The Filter Category ID List for User Group
     * @param array $_filterStaffKBCategoryIDList The Filter Category ID List for Staff Group
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function RetrieveTreeSub($_parentCategoryID, $_categoryTypeList, $_filterUserKBCategoryIDList, $_filterStaffKBCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_knowledgebaseCategoryContainer = array('subcategories' => array(), 'articles' => array());

        $_sortOrder = 'DESC';
        if ($_SWIFT->Settings->Get('kb_catorder') == '1')
        {
            $_sortOrder = 'ASC';
        }

        $_sortField = 'displayorder';
        if ($_SWIFT->Settings->Get('kb_catdisplayorder') == '2')
        {
            $_sortField = 'title';
        } else if ($_SWIFT->Settings->Get('kb_catdisplayorder') == '3') {
            $_sortField = 'kbcategoryid';
        }

        $_finalKnowledgebaseCategoryIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "kbcategories AS kbcategories
            WHERE kbcategories.categorytype IN (" . BuildIN($_categoryTypeList) . ")
                AND kbcategories.parentkbcategoryid = '" . ($_parentCategoryID) . "'
                " . IIF(is_array($_filterUserKBCategoryIDList), "AND (kbcategories.uservisibilitycustom = '0' OR (kbcategories.uservisibilitycustom = '1' AND kbcategories.kbcategoryid IN (" . BuildIN($_filterUserKBCategoryIDList) . ")))") . "
                " . IIF(is_array($_filterStaffKBCategoryIDList), "AND (kbcategories.staffvisibilitycustom = '0' OR (kbcategories.staffvisibilitycustom = '1' AND kbcategories.kbcategoryid IN (" . BuildIN($_filterStaffKBCategoryIDList) . ")))") . "
            ORDER BY kbcategories." . $_sortField . " " . $_sortOrder);
        while ($_SWIFT->Database->NextRecord())
        {
            $_knowledgebaseCategoryContainer['subcategories'][$_SWIFT->Database->Record['kbcategoryid']] = $_SWIFT->Database->Record;
        }

        $_finalKnowledgebaseCategoryIDList[] = $_parentCategoryID;

        $_SWIFT->Database->Query("SELECT kbarticlelinks.linktypeid, kbarticles.* FROM " . TABLE_PREFIX . "kbarticlelinks AS kbarticlelinks
            LEFT JOIN " . TABLE_PREFIX . "kbarticles AS kbarticles ON (kbarticles.kbarticleid = kbarticlelinks.kbarticleid)
            WHERE kbarticlelinks.linktype = '" . (SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY) . "' AND kbarticlelinks.linktypeid = '" . ($_parentCategoryID) . "'
                AND kbarticles.articlestatus = '" . SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED . "'");
        while ($_SWIFT->Database->NextRecord())
        {
            $_knowledgebaseCategoryContainer['articles'][$_SWIFT->Database->Record['kbarticleid']] = $_SWIFT->Database->Record;
        }

        foreach ($_knowledgebaseCategoryContainer['subcategories'] as $_knowledgebaseCategoryID => $_knowledgebaseCategory)
        {
            $_knowledgebaseCategoryContainer['subcategories'][$_knowledgebaseCategoryID] = array_merge($_knowledgebaseCategory, self::RetrieveTreeSub($_knowledgebaseCategoryID, $_categoryTypeList, $_filterUserKBCategoryIDList, $_filterStaffKBCategoryIDList));
        }

        return $_knowledgebaseCategoryContainer;
    }

    /**
     * Retrieve the Knowledgebase Category Tree based on specified criteria
     *
     * @author Varun Shoor
     * @param array $_categoryTypeList The Category Type List
     * @param array $_parentCategoryIDList The Parent Category ID List
     * @param int $_staffGroupID (OPTIONAL) The Staff Group ID
     * @param int $_userGroupID (OPTIONAL) The User Group ID
     * @return bool|array "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveSubCategoryIDListExtended($_categoryTypeList, $_parentCategoryIDList, $_staffGroupID = 0, $_userGroupID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_filterUserKBCategoryIDList = $_filterStaffKBCategoryIDList = false;
        if (!empty($_userGroupID))
        {
            $_filterUserKBCategoryIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup($_userGroupID, SWIFT_UserGroupAssign::TYPE_KBCATEGORY);
        }

        if (!empty($_staffGroupID))
        {
            $_filterStaffKBCategoryIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_KBCATEGORY, $_staffGroupID);
        }

        $_knowledgebaseCategoryIDList = self::RetrieveSubCategoryIDListLoop($_parentCategoryIDList, $_categoryTypeList, $_filterUserKBCategoryIDList, $_filterStaffKBCategoryIDList);

        return $_knowledgebaseCategoryIDList;
    }

    /**
     * Retrieve the Tree Structure
     *
     * @author Varun Shoor
     * @param array $_parentCategoryIDList The Parent Category ID List
     * @param array $_categoryTypeList The Category Type List
     * @param array $_filterUserKBCategoryIDList The Filter Category ID List for User Group
     * @param array $_filterStaffKBCategoryIDList The Filter Category ID List for Staff Group
     * @return array|bool
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function RetrieveSubCategoryIDListLoop($_parentCategoryIDList, $_categoryTypeList, $_filterUserKBCategoryIDList, $_filterStaffKBCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_sortOrder = 'DESC';
        if ($_SWIFT->Settings->Get('kb_catorder') == '1')
        {
            $_sortOrder = 'ASC';
        }

        $_sortField = 'displayorder';
        if ($_SWIFT->Settings->Get('kb_catdisplayorder') == '2')
        {
            $_sortField = 'title';
        } else if ($_SWIFT->Settings->Get('kb_catdisplayorder') == '3') {
            $_sortField = 'kbcategoryid';
        }

        $_finalKnowledgebaseCategoryIDList = array();
        $_SWIFT->Database->Query("SELECT kbcategoryid FROM " . TABLE_PREFIX . "kbcategories AS kbcategories
            WHERE kbcategories.categorytype IN (" . BuildIN($_categoryTypeList) . ")
                AND kbcategories.parentkbcategoryid IN (" . BuildIN($_parentCategoryIDList) . ")
                " . IIF(is_array($_filterUserKBCategoryIDList), "AND (kbcategories.uservisibilitycustom = '0' OR (kbcategories.uservisibilitycustom = '1' AND kbcategories.kbcategoryid IN (" . BuildIN($_filterUserKBCategoryIDList) . ")))") . "
                " . IIF(is_array($_filterStaffKBCategoryIDList), "AND (kbcategories.staffvisibilitycustom = '0' OR (kbcategories.staffvisibilitycustom = '1' AND kbcategories.kbcategoryid IN (" . BuildIN($_filterStaffKBCategoryIDList) . ")))") . "
            ORDER BY kbcategories." . $_sortField . " " . $_sortOrder);
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalKnowledgebaseCategoryIDList[] = $_SWIFT->Database->Record['kbcategoryid'];
        }

        if (count($_finalKnowledgebaseCategoryIDList))
        {
            $_finalKnowledgebaseCategoryIDList = array_merge($_finalKnowledgebaseCategoryIDList, self::RetrieveSubCategoryIDListLoop($_finalKnowledgebaseCategoryIDList, $_categoryTypeList, $_filterUserKBCategoryIDList, $_filterStaffKBCategoryIDList));
        }

        return $_finalKnowledgebaseCategoryIDList;
    }

    /**
     * Retrieve the Knowledgebase Categories based on specified criteria
     *
     * @author Varun Shoor
     * @param array $_categoryTypeList The Category Type List
     * @param int $_parentCategoryID (OPTIONAL) The Parent Category ID
     * @param int $_staffGroupID (OPTIONAL) The Staff Group ID
     * @param int $_userGroupID (OPTIONAL) The User Group ID
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve($_categoryTypeList, $_parentCategoryID = 0, $_staffGroupID = 0, $_userGroupID = 0, $_retrieveArticles = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_knowledgebaseCategoryCache = $_SWIFT->Cache->Get('kbcategorycache');
        $_knowledgebaseCategoryContainer = array();

        $_filterUserKBCategoryIDList = $_filterStaffKBCategoryIDList = false;
        if (!empty($_userGroupID))
        {
            $_filterUserKBCategoryIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup($_userGroupID, SWIFT_UserGroupAssign::TYPE_KBCATEGORY);
        }

        if (!empty($_staffGroupID))
        {
            $_filterStaffKBCategoryIDList = SWIFT_StaffGroupLink::RetrieveListFromCacheOnStaffGroup(SWIFT_StaffGroupLink::TYPE_KBCATEGORY, $_staffGroupID);
        }

        $_kbCategoryContainer = array();

        $_sortOrder = $_SWIFT->Settings->Get('kb_catorder') == '1' ? SWIFT_KnowledgebaseArticle::SORT_ASC : SWIFT_KnowledgebaseArticle::SORT_DESC;

        $_sortField = 'displayorder';
        if ($_SWIFT->Settings->Get('kb_catdisplayorder') == '2')
        {
            $_sortField = 'title';
        } else if ($_SWIFT->Settings->Get('kb_catdisplayorder') == '3') {
            $_sortField = 'kbcategoryid';
        }

        $_finalKnowledgebaseCategoryIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "kbcategories AS kbcategories
            WHERE kbcategories.categorytype IN (" . BuildIN($_categoryTypeList) . ")
                AND kbcategories.parentkbcategoryid = '" . ($_parentCategoryID) . "'
                " . IIF(is_array($_filterUserKBCategoryIDList), "AND (kbcategories.uservisibilitycustom = '0' OR (kbcategories.uservisibilitycustom = '1' AND kbcategories.kbcategoryid IN (" . BuildIN($_filterUserKBCategoryIDList) . ")))") . "
                " . IIF(is_array($_filterStaffKBCategoryIDList), "AND (kbcategories.staffvisibilitycustom = '0' OR (kbcategories.staffvisibilitycustom = '1' AND kbcategories.kbcategoryid IN (" . BuildIN($_filterStaffKBCategoryIDList) . ")))") . "
            ORDER BY kbcategories." . $_sortField . " " . $_sortOrder);
        while ($_SWIFT->Database->NextRecord())
        {
            $_kbCategoryContainer[$_SWIFT->Database->Record['kbcategoryid']] = $_SWIFT->Database->Record;
            $_finalKnowledgebaseCategoryIDList[] = $_SWIFT->Database->Record['kbcategoryid'];
        }

        $_returnTotalKnowledgebaseCategoryIDList = $_finalKnowledgebaseCategoryIDList;

        // String accented characters converter
        $StringConverter = new SWIFT_StringConverter();

        foreach ($_kbCategoryContainer as $_kbCategoryID => $_kbCategory)
        {
            $_knowledgebaseCategoryContainer[$_kbCategoryID] = $_kbCategory;
            $_knowledgebaseCategoryContainer[$_kbCategoryID]['articles'] = array();
            $_knowledgebaseCategoryContainer[$_kbCategoryID]['subcategoryidlist'] = array();

            /**
             * Bug Fix : Mansi Wason <mansi.wason@kayako.com>
             *
             * SWIFT-5184 : Unable to see the article created under sub categories if the setting Count articles in sub categories is disabled
             *
             */
            $_filterResult = self::FilterTotalArticles($_categoryTypeList, array($_kbCategory['kbcategoryid']), $_filterUserKBCategoryIDList, $_filterStaffKBCategoryIDList);

            $_knowledgebaseCategoryContainer[$_kbCategoryID]['subcategoryidlist'] = $_filterResult[1];
            $_returnTotalKnowledgebaseCategoryIDList = array_merge($_returnTotalKnowledgebaseCategoryIDList, $_filterResult[1]);

            if ($_SWIFT->Settings->Get('kb_parcount') == '1')
            {
                $_knowledgebaseCategoryContainer[$_kbCategoryID]['totalarticles'] = $_filterResult[0];
            } else {
                $_knowledgebaseCategoryContainer[$_kbCategoryID]['totalarticles'] = '0';
            }

            /*
             * BUG FIX - Anjali Sharma
             *
             * SWIFT-3820 Knowledgebase Category name is not rendering correctly at Knowledgebase/List page.
             */
            $_knowledgebaseCategoryContainer[$_kbCategoryID]['title'] = IIF(false === strpos($_kbCategory['title'],
                    ' '), wordwrapWithZeroWidthSpace(htmlspecialchars($_kbCategory['title'])), htmlspecialchars($_kbCategory['title']));
            $_knowledgebaseCategoryContainer[$_kbCategoryID]['seotitle'] = str_replace(' ', '-', CleanURL($_kbCategory['title']));

            /**
             * Bug Fix : Saloni Dhall
             *
             * SWIFT-2712 : 'Override Article Display Order Field' option is not working under Knowledgebase setting
             */
            $_sortOrder = $_SWIFT->Settings->Get('kb_arorder') == '1' ? SWIFT_KnowledgebaseArticle::SORT_ASC : SWIFT_KnowledgebaseArticle::SORT_DESC;

            if ($_retrieveArticles && ($_SWIFT->Settings->Get('kb_maxcatarticles')) > 0)
            {
                $_fetchCategoryIDList = $_knowledgebaseCategoryContainer[$_kbCategoryID]['subcategoryidlist'];
                $_fetchCategoryIDList[] = $_kbCategoryID;
                $_SWIFT->Database->QueryLimit("SELECT kbarticlelinks.linktypeid, kbarticles.* FROM " . TABLE_PREFIX . "kbarticlelinks AS kbarticlelinks
                    LEFT JOIN " . TABLE_PREFIX . "kbarticles AS kbarticles ON (kbarticles.kbarticleid = kbarticlelinks.kbarticleid)
                    WHERE kbarticlelinks.linktype = '" . (SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY) . "' AND kbarticlelinks.linktypeid IN (" . BuildIN($_fetchCategoryIDList) . ")
                        AND kbarticles.articlestatus = '" . SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED . "' ORDER BY " . SWIFT_KnowledgebaseArticle::GetDefaultSortField() . " " . $_sortOrder, $_SWIFT->Settings->Get('kb_maxcatarticles'));
                while ($_SWIFT->Database->NextRecord())
                {
                    $_knowledgebaseCategoryContainer[$_kbCategoryID]['articles'][$_SWIFT->Database->Record['kbarticleid']] = $_SWIFT->Database->Record;
                    $_knowledgebaseCategoryContainer[$_kbCategoryID]['articles'][$_SWIFT->Database->Record['kbarticleid']]['subject'] = htmlspecialchars($_SWIFT->Database->Record['subject']);
                }
            }
        }

        return array($_knowledgebaseCategoryContainer, $_returnTotalKnowledgebaseCategoryIDList);
    }

    /**
     * Filter the total articles based on category properties
     *
     * @author Varun Shoor
     * @param array $_categoryTypeList The Category Type List
     * @param array $_parentCategoryIDList (OPTIONAL) The Parent Category ID
     * @param array $_filterUserKBCategoryIDList (OPTIONAL) The Staff Group ID
     * @param array $_filterStaffKBCategoryIDList (OPTIONAL) The User Group ID
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function FilterTotalArticles($_categoryTypeList, $_parentCategoryIDList = array(), $_filterUserKBCategoryIDList = array(), $_filterStaffKBCategoryIDList = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        $_kbCategoryCache = $_SWIFT->Cache->Get('kbcategorycache');

        $_totalArticles = 0;

        $_dispatchParentKBCategoryIDList = $_returnTotalKnowledgebaseCategoryIDList = array();

        $_SWIFT->Database->Query("SELECT kbcategories.kbcategoryid, kbcategories.totalarticles FROM " . TABLE_PREFIX . "kbcategories AS kbcategories
            WHERE kbcategories.categorytype IN (" . BuildIN($_categoryTypeList) . ")
                AND kbcategories.parentkbcategoryid IN (" . BuildIN($_parentCategoryIDList) . ")
                " . IIF(is_array($_filterUserKBCategoryIDList), "AND (kbcategories.uservisibilitycustom = '0' OR (kbcategories.uservisibilitycustom = '1' AND kbcategories.kbcategoryid IN (" . BuildIN($_filterUserKBCategoryIDList) . ")))") . "
                " . IIF(is_array($_filterStaffKBCategoryIDList), "AND (kbcategories.staffvisibilitycustom = '0' OR (kbcategories.staffvisibilitycustom = '1' AND kbcategories.kbcategoryid IN (" . BuildIN($_filterStaffKBCategoryIDList) . ")))") . "
            ORDER BY kbcategories.displayorder ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_dispatchParentKBCategoryIDList[] = $_SWIFT->Database->Record['kbcategoryid'];
        }

        $_returnTotalKnowledgebaseCategoryIDList = $_dispatchParentKBCategoryIDList;

        if (count($_parentCategoryIDList))
        {
            $_parentCountContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "kbarticlelinks AS kbarticlelinks
                LEFT JOIN " . TABLE_PREFIX . "kbarticles AS kbarticles ON (kbarticles.kbarticleid = kbarticlelinks.kbarticleid)
                WHERE kbarticlelinks.linktype = '" . SWIFT_KnowledgebaseArticleLink::LINKTYPE_CATEGORY . "' AND kbarticlelinks.linktypeid IN (" . BuildIN($_parentCategoryIDList) . ")
                    AND kbarticles.articlestatus = '" . SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED . "'");
            $_totalArticles += ($_parentCountContainer['totalitems']);
        }

        if (count($_dispatchParentKBCategoryIDList))
        {
            $_filterResult = self::FilterTotalArticles($_categoryTypeList, $_dispatchParentKBCategoryIDList, $_filterUserKBCategoryIDList, $_filterStaffKBCategoryIDList);

            $_totalArticles += $_filterResult[0];

            $_returnTotalKnowledgebaseCategoryIDList = array_merge($_returnTotalKnowledgebaseCategoryIDList, $_filterResult[1]);
        }

        return array($_totalArticles, $_returnTotalKnowledgebaseCategoryIDList);
    }

    /**
     * Work till you find a parent category of a given type
     *
     * @author Varun Shoor
     * @param array $_categoryTypeList The Category Type List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function IsParentCategoryOfType($_categoryTypeList)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_categoryTypeList) || $this->GetProperty('categorytype') != self::TYPE_INHERIT) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // If its marked as inherited and parent category is global, then ALWAYS return true
        if ($this->GetProperty('parentkbcategoryid') == '0')
        {
            return true;
        }

        return self::IsParentCategoryOfTypeLoop($this->GetProperty('parentkbcategoryid'), $_categoryTypeList);
    }

    /**
     * Check to see if parent category is of a given type
     *
     * @author Varun Shoor
     * @param int $_parentCategoryID The Knowledgebase Category ID
     * @param array $_categoryTypeList The Category Type List
     * @return bool "true" on Success, "false" otherwise
     */
    protected static function IsParentCategoryOfTypeLoop($_parentCategoryID, $_categoryTypeList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_parentCategoryID == '0')
        {
            return false;
        }

        if (isset(static::$_parentCategoryTypeCache[$_parentCategoryID]) && in_array(static::$_parentCategoryTypeCache[$_parentCategoryID], $_categoryTypeList))
        {
            return true;
        }

        $_knowledgebaseCategoryContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "kbcategories WHERE kbcategoryid = '" . ($_parentCategoryID) . "'");
        if (!isset($_knowledgebaseCategoryContainer['kbcategoryid']) || empty($_knowledgebaseCategoryContainer['kbcategoryid']))
        {
            return false;
        }

        if (in_array($_knowledgebaseCategoryContainer['categorytype'], $_categoryTypeList))
        {
            static::$_parentCategoryTypeCache[$_parentCategoryID] = $_knowledgebaseCategoryContainer['categorytype'];

            return true;
        }

        if ($_knowledgebaseCategoryContainer['parentkbcategoryid'] == '0')
        {
            return false;
        }

        return self::IsParentCategoryOfTypeLoop($_knowledgebaseCategoryContainer['parentkbcategoryid'], $_categoryTypeList);
    }

    /**
     * Retrieve the Parent Category List
     *
     * @author Parminder Singh
     * @param int $_categoryID The Category ID
     * @param array $_categoryTypeList The Category Type List
     * @param int $_userGroupID
     * @return array $_finalKnowledgebaseCategoryIDList having category ID and its title
     * @throws SWIFT_Exception
     */
    static function RetrieveParentCategoryList($_categoryID, $_categoryTypeList, $_userGroupID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_filterUserKBCategoryIDList = $_knowledgebaseCategoryContainer = array();
        if (!empty($_userGroupID))
        {
            $_filterUserKBCategoryIDList = SWIFT_UserGroupAssign::RetrieveListOnUserGroup($_userGroupID, SWIFT_UserGroupAssign::TYPE_KBCATEGORY);
        }

        $_finalKnowledgebaseCategoryIDList = array();
        $_knowledgebaseCategoryContainer = $_SWIFT->Database->QueryFetch("SELECT kbcategoryid, parentkbcategoryid, title FROM " . TABLE_PREFIX . "kbcategories AS kbcategories
            WHERE kbcategories.categorytype IN (" . BuildIN($_categoryTypeList) . ")
                AND kbcategories.kbcategoryid = '" . ($_categoryID) . "'
                " . IIF(is_array($_filterUserKBCategoryIDList), " AND (kbcategories.uservisibilitycustom = '0' OR (kbcategories.uservisibilitycustom = '1' AND kbcategories.kbcategoryid IN (" . BuildIN($_filterUserKBCategoryIDList) . ")))") );

        if (isset($_knowledgebaseCategoryContainer['parentkbcategoryid']) && !empty($_knowledgebaseCategoryContainer['parentkbcategoryid'])) {
            $_finalKnowledgebaseCategoryIDList = self::RetrieveParentCategoryList($_knowledgebaseCategoryContainer['parentkbcategoryid'], $_categoryTypeList, $_userGroupID);
        }

        $_finalKnowledgebaseCategoryIDList[$_knowledgebaseCategoryContainer['kbcategoryid']] = htmlspecialchars($_knowledgebaseCategoryContainer['title']);

        return $_finalKnowledgebaseCategoryIDList;
    }

    /**
     * Update links in child categories that are TYPE_INHERIT
     *
     * @param SWIFT_KnowledgebaseCategory $_SWIFT_KnowledgebaseCategoryObject
     * @param bool $_userVisibilityCustom
     * @param bool $_staffVisibilityCustom
     * @param array $_userGroupIdList
     * @param array $_staffGroupIDList
     * @return int number of child categories updated
     * @throws SWIFT_Exception
     */
    public static function UpdateChildrenInheritedLinks(
        SWIFT_KnowledgebaseCategory $_SWIFT_KnowledgebaseCategoryObject,
        bool $_userVisibilityCustom,
        bool $_staffVisibilityCustom,
        array $_userGroupIdList,
        array $_staffGroupIDList
    ): int {
        $_knowledgebaseCategoryID = $_SWIFT_KnowledgebaseCategoryObject->GetID();
        $ids = SWIFT_KnowledgebaseCategory::RetrieveSubCategoryIDListExtended([SWIFT_KnowledgebaseCategory::TYPE_INHERIT],
            [$_knowledgebaseCategoryID]);
        if (!empty($ids)) {
            foreach ($ids as $_childId) {
                $_childCat = SWIFT_KnowledgebaseCategory::GetOnID($_childId);

                $_childCat->UpdatePool('uservisibilitycustom', $_userVisibilityCustom);
                $_childCat->UpdatePool('staffvisibilitycustom', $_staffVisibilityCustom);
                $_childCat->ProcessUpdatePool();

                SWIFT_UserGroupAssign::DeleteList([$_childCat->GetID()], SWIFT_UserGroupAssign::TYPE_KBCATEGORY);
                if ($_userVisibilityCustom) {
                    foreach ($_userGroupIdList as $_userGroupID) {
                        SWIFT_UserGroupAssign::Insert($_childCat->GetID(),
                            SWIFT_UserGroupAssign::TYPE_KBCATEGORY, $_userGroupID);
                    }
                }

                SWIFT_StaffGroupLink::DeleteOnLinkList(SWIFT_StaffGroupLink::TYPE_KBCATEGORY,
                    [$_childCat->GetID()]);
                if ($_staffVisibilityCustom) {
                    foreach ($_staffGroupIDList as $_staffGroupID) {
                        SWIFT_StaffGroupLink::Create($_staffGroupID, SWIFT_StaffGroupLink::TYPE_KBCATEGORY,
                            $_childCat->GetID());
                    }
                }
            }
        }

        return count($ids);
    }
}
