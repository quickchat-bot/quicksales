<?php
/**
 * ###############################################
 *
 * Kayako Classic
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Knowledgebase\Models\Category;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;

/**
 * Class KnowledgebaseCategoryTest
 * @group knowledgebase
 */
class KnowledgebaseCategoryTest extends \SWIFT_TestCase
{
    /**
     * @param bool $loaded
     * @param bool $loadCache
     * @return CategoryMock
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function getCategory($loaded = true, $loadCache = true)
    {
        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        if ($loadCache) {
            $mockCache->method('Get')->willReturnOnConsecutiveCalls([
                '1' => [
                    'displayorder' => 0,
                ],
            ], false);
        } else {
            $mockCache->method('Get')->willReturn([
                '1' => [
                    'displayorder' => 0,
                ],
            ]);
        }

        $mockDB = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDB->method('NextRecord')->willReturnOnConsecutiveCalls(true, false, true, false);
        $mockDB->method('AutoExecute')->willReturn(true);
        $mockDB->method('QueryFetch')
            ->willReturnCallback(function ($x) {
                if (false !== strpos($x, "kbcategoryid = '2'") ||
                    false !== strpos($x, "kbcategoryid = '0'")) {
                    return false;
                }

                if (false !== strpos($x, "kbcategoryid = '3'")) {
                    return [
                        'kbcategoryid' => 1,
                        'categorytype' => '1',
                        'parentkbcategoryid' => '0',
                        'staffvisibilitycustom' => '1',
                    ];
                }

                if (false !== strpos($x, "kbcategoryid = '5'")) {
                    return [
                        'parentkbcategoryid' => '1',
                    ];
                }

                return [
                    'kbcategoryid' => 1,
                    'categorytype' => '1',
                    'staffvisibilitycustom' => '1',
                ];
            });

        $mockDB->method('Insert_ID')->willReturnOnConsecutiveCalls(1, 0);

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'charset') {
                return 'UTF-8';
            }

            return $x;
        });

        SWIFT::GetInstance()->Language = $mockLang;
        SWIFT::GetInstance()->Database = $mockDB;
        SWIFT::GetInstance()->Cache = $mockCache;

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetProperty')->willReturn('1');

        SWIFT::GetInstance()->Staff = $mockStaff;

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturnOnConsecutiveCalls('1', '2', '3', '3', '3', '3', '3', '3', '3', '3',
            '3', '3', '3', '3', '3', '3');

        SWIFT::GetInstance()->Settings = $mockSettings;

        $data = new SWIFT_DataID(1);
        $data->SetIsClassLoaded($loaded);
        $obj = new CategoryMock($data);
        $this->mockProperty($obj, 'Database', $mockDB);

        return $obj;
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getCategory();
        $this->assertInstanceOf('Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory', $obj);

        $this->setExpectedException('Knowledgebase\Models\Category\SWIFT_Category_Exception',
            'Failed to load Knowledgebase Category Object');
        $this->getCategory(false);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDestructorCallsDestruct()
    {
        $obj = $this->getCategory();
        $this->assertNotNull($obj);

        $obj->__destruct();
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testProcessUpdatePoolThrowsException()
    {
        $obj = $this->getCategory();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('Knowledgebase\Models\Category\SWIFT_Category_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->ProcessUpdatePool();
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testGetKnowledgebaseCategoryIdThrowsException()
    {
        $obj = $this->getCategory();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('Knowledgebase\Models\Category\SWIFT_Category_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetKnowledgebaseCategoryID();
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testLoadDataThrowsException()
    {
        $obj = $this->getCategory();
        $data = new SWIFT_DataStore([]);
        $this->setExpectedException('Knowledgebase\Models\Category\SWIFT_Category_Exception',
            SWIFT_INVALIDDATA);
        $mock = new \ReflectionClass($obj);
        $method = $mock->getMethod('LoadData');
        $method->setAccessible(true);
        $method->invoke($obj, $data);
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testGetDataStoreThrowsException()
    {
        $obj = $this->getCategory();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('Knowledgebase\Models\Category\SWIFT_Category_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetDataStore();
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testGetPropertyThrowsException()
    {
        $obj = $this->getCategory();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('Knowledgebase\Models\Category\SWIFT_Category_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetProperty('key');
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testGetPropertyThrowsInvalidDataException()
    {
        $obj = $this->getCategory();
        $this->setExpectedException('Knowledgebase\Models\Category\SWIFT_Category_Exception',
            SWIFT_INVALIDDATA . ': key');
        $obj->GetProperty('key');
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testCreateThrowsInvalidDataException()
    {
        $obj = $this->getCategory();
        $this->setExpectedException('Knowledgebase\Models\Category\SWIFT_Category_Exception',
            SWIFT_INVALIDDATA);
        $obj::Create(1, '', 1, 1, 1, true, true, true);
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testCreateThrowsCreateFailedException()
    {
        $obj = $this->getCategory();
        SWIFT::GetInstance()->Database->Insert_ID(); // advance id
        $this->setExpectedException('Knowledgebase\Models\Category\SWIFT_Category_Exception',
            SWIFT_CREATEFAILED);
        $obj::Create(1, 'title', 1, 1, 1, true, true, true);
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testUpdateThrowsCreateFailedException()
    {
        $obj = $this->getCategory();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('Knowledgebase\Models\Category\SWIFT_Category_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->Update(1, 'title', 1, 1, 1, true, true, true);
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getCategory();
        $this->assertTrue($obj->Delete());
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('Knowledgebase\Models\Category\SWIFT_Category_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->Delete();
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testDeleteListReturnsFalse()
    {
        $obj = $this->getCategory();
        $this->assertFalse($obj::DeleteList([]));
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testRetrieveSubCategoryIdListReturnsArray()
    {
        $obj = $this->getCategory();
        $this->assertEmpty($obj::RetrieveSubCategoryIDList([]));
        $obj->setCache([
            '_knowledgebaseParentMap' => [
                1 => [
                    'kbcategoryid' => 1,
                ],
            ],
        ]);
        $this->assertCount(1, $obj::RetrieveSubCategoryIDList([1]));
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testGetLastDisplayOrderReturnsNumber()
    {
        $obj = $this->getCategory();
        $this->assertEquals(1, (int)$obj::GetLastDisplayOrder(),
            'Returns displayorder');
        $this->assertEquals(1, (int)$obj::GetLastDisplayOrder(),
            'Returns 1 without cache');
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testGetLinkedUserGroupIdListThrowsException()
    {
        $obj = $this->getCategory();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetLinkedUserGroupIDList();
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testGetLinkedStaffGroupIdListThrowsException()
    {
        $obj = $this->getCategory();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetLinkedStaffGroupIDList();
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testRetrieveTreeReturnsArray()
    {
        $obj = $this->getCategory();
        $this->assertInternalType('array', $obj::RetrieveTree([1], 1, 1),
            'Returns array with kb_catorder = 1');
        $this->assertInternalType('array', $obj::RetrieveTree([1], 1, 1),
            'Returns array with kb_catdisplayorder = 2');
        $this->assertInternalType('array', $obj::RetrieveTree([1], 1, 1),
            'Returns array with kb_catdisplayorder = 3');
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testIsParentCategoryOfTypeLoopReturnsBoolean()
    {
        $obj = $this->getCategory();
        $mock = new \ReflectionClass($obj);
        $method = $mock->getMethod('IsParentCategoryOfTypeLoop');
        $method->setAccessible(true);
        $this->assertFalse($method->invoke($obj, 0, [0]));
        $this->assertFalse($method->invoke($obj, 2, [0]));
        $this->assertFalse($method->invoke($obj, 3, [0]));
//        $this->assertFalse($method->invoke($obj, 1, [0]));
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testRebuildCacheReturnsTrue()
    {
        $obj = $this->getCategory();
        SWIFT::GetInstance()->Database->Record = [
            'kbcategoryid' => 1,
            'parentkbcategoryid' => 1,
        ];
        $this->assertTrue($obj::RebuildCache());
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testRetrieveSubCategoryIDListLoopReturnsArray()
    {
        $obj = $this->getCategory();
        $mock = new \ReflectionClass($obj);
        $method = $mock->getMethod('RetrieveSubCategoryIDListLoop');
        $method->setAccessible(true);
        $this->assertInternalType('array', $method->invoke($obj, [], [], [], []),
            'Returns array with kb_catorder = 1');
        $this->assertInternalType('array', $method->invoke($obj, [], [], [], []),
            'Returns array with kb_catdisplayorder = 2');
        $this->assertInternalType('array', $method->invoke($obj, [], [], [], []),
            'Returns array with kb_catdisplayorder = 3');
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testRetrieveReturnsArray()
    {
        $obj = $this->getCategory();
        $this->assertInternalType('array', $obj::Retrieve([1], 1, 1),
            'Returns array with kb_catorder = 1');
        $this->assertInternalType('array', $obj::Retrieve([1], 1, 1),
            'Returns array with kb_catdisplayorder = 2');
        $this->assertInternalType('array', $obj::Retrieve([1], 1, 1),
            'Returns array with kb_catdisplayorder = 3');
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testIsParentCategoryOfTypeReturnsTrue()
    {
        $obj = $this->getCategory();
        $obj->setData([
            'parentkbcategoryid' => '0',
            'kbcategoryid' => 1,
            'categorytype' => '4',
            'staffvisibilitycustom' => '1',
        ]);
        $this->assertTrue($obj->IsParentCategoryOfType([1]));
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->IsParentCategoryOfType([]);
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testIsParentCategoryOfTypeThrowsInvalidDataException()
    {
        $obj = $this->getCategory();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->IsParentCategoryOfType([]);
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testRetrieveParentCategoryListReturnsArray()
    {
        $obj = $this->getCategory();
        $this->assertIsArray($obj::RetrieveParentCategoryList(5, [5]));
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws SWIFT_Exception
     */
    public function testUpdateChildrenInheritedLinksUpdatesChildren()
    {
        $obj = $this->getCategory(true, false);

        SWIFT::GetInstance()->Database->Record = [
            'kbcategoryid' => 1,
            'parentkbcategoryid' => 1,
        ];

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => []
            ];
        };

        $this->assertEquals(2, SWIFT_KnowledgebaseCategory::UpdateChildrenInheritedLinks($obj, true, true, [1], [1]));
    }

    public function testRetrieveCategoryOrder()
    {
        $services = $this->getMockServices();
        $services['Database']->method('Query')
            ->will($this->returnCallback(
                function($query) {
                    $this->assertTrue(1 === preg_match('/.*ORDER BY displayorder ASC$/', $query));
                }
            ));

        SWIFT_KnowledgebaseCategory::RetrieveCategories();
    }
}

class CategoryMock extends SWIFT_KnowledgebaseCategory
{
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        $this->Load = new LoaderMock();
        parent::__construct($_SWIFT_DataObject);
    }

    public function __destruct()
    {
        // prevent exception to be thrown when destroying the object and it's not loaded
        $this->SetIsClassLoaded(true);
        parent::__destruct();
    }

    public function setCache($_cache)
    {
        static::$_knowledgebaseCategoryCache = $_cache;
    }

    public function setData($_data)
    {
        $this->_dataStore = $_data;
    }
}
