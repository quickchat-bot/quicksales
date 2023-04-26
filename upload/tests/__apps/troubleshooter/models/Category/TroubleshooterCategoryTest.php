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

namespace Troubleshooter\Models\Category;

use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;

/**
 * Class TroubleshooterCategoryTest
 * @group troubleshooter
 */
class TroubleshooterCategoryTest extends \SWIFT_TestCase
{
    /**
     * @param bool $loaded
     * @return CategoryMock
     * @throws SWIFT_Exception
     */
    public function getCategory($loaded = true)
    {
        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            '1' => [
                'displayorder' => 0,
            ]
        ]);

        $mockDB = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDB->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);
        $mockDB->method('AutoExecute')->willReturn(true);
        $mockDB->method('QueryFetch')
            ->willReturn([
                'troubleshootercategoryid' => 1,
                'categorytype' => '1',
                'staffvisibilitycustom' => '1',
            ]);
        $mockDB->method('Insert_ID')
            ->willReturnOnConsecutiveCalls(1, 0);

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        SWIFT::GetInstance()->Language = $mockLang;
        SWIFT::GetInstance()->Database = $mockDB;
        SWIFT::GetInstance()->Cache = $mockCache;

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
        $this->assertInstanceOf('Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory', $obj);

        $obj->__destruct();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorThrowsException()
    {
        $this->setExpectedException('SWIFT_Exception', 'Failed to load Troubleshooter Object');
        $this->getCategory(false);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessUpdatePoolThrowsException()
    {
        $obj = $this->getCategory();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->ProcessUpdatePool();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetTroubleshooterCategoryIDThrowsException()
    {
        $obj = $this->getCategory();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetTroubleshooterCategoryID();
    }

    /**
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testLoadDataThrowsExceptionWithNullParameter()
    {
        $obj = $this->getCategory();

        // LoadData is protected. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('LoadData');
        $method->setAccessible(true);

        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invoke($obj, null);
    }

    /**
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testLoadDataThrowsExceptionWithEmptyData()
    {
        $obj = $this->getCategory();

        // LoadData is protected. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('LoadData');
        $method->setAccessible(true);

        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invoke($obj, new \SWIFT_DataStore([]));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDataStoreThrowsException()
    {
        $obj = $this->getCategory();
        $this->assertNotNull($obj->GetDataStore());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetDataStore();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertyThrowsException()
    {
        $obj = $this->getCategory();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetProperty('');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertyThrowsInvalidDataException()
    {
        $obj = $this->getCategory();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->GetProperty('invalid');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIsValidTypeReturnsFalse()
    {
        $obj = $this->getCategory();
        $this->assertFalse($obj::IsValidType(0));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateThrowsFailedException()
    {
        $obj = $this->getCategory();
        // decrease id
        SWIFT::GetInstance()->Database->Insert_ID();
        $this->setExpectedException('SWIFT_Exception', SWIFT_CREATEFAILED);
        $obj::Create('title', 'description', 1, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateReturnsId()
    {
        $obj = $this->getCategory();
        $this->assertEquals(1, $obj::Create('title',
            'description', 1, 1,
            true, [1], true, [1]));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateThrowsException()
    {
        $obj = $this->getCategory();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj::Create('', '', 0, 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateThrowsNotLoadedException()
    {
        $obj = $this->getCategory();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Update('', '', 0, 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateThrowsInvalidDataException()
    {
        $obj = $this->getCategory();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->Update('', '', 0, 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateReturnsTrue()
    {
        $obj = $this->getCategory();
        $this->assertTrue($obj->Update('title',
            'description', 1, 1,
            true, [1], true, [1]));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getCategory();
        $this->assertTrue($obj->Delete(),
            'Returns true after deleting');

        // class is already unloaded
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Delete();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListReturnsFalse()
    {
        $obj = $this->getCategory();
        $this->assertFalse($obj::DeleteList(''),
            'Returns false if array is not provided');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetCategoryTypeLabelReturnsLabel()
    {
        $obj = $this->getCategory();
        $this->assertEquals('public',
            $obj::GetCategoryTypeLabel(2));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetCategoryTypeLabelThrowsException()
    {
        $obj = $this->getCategory();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj::GetCategoryTypeLabel(0);
    }

//    /**
//     * @throws SWIFT_Exception
//     */
//    public function testRebuildCacheReturnsTrue()
//    {
//        $obj = $this->getCategory();
//        $this->assertTrue($obj::RebuildCache(),
//            'Returns true after updating cache');
//    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetLastDisplayOrderReturnsInt()
    {
        $obj = $this->getCategory();
        $this->assertEquals(1, $obj::GetLastDisplayOrder());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetLinkedUserGroupIDListThrowsException()
    {
        $obj = $this->getCategory();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetLinkedUserGroupIDList();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetLinkedStaffGroupIDListThrowsException()
    {
        $obj = $this->getCategory();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetLinkedStaffGroupIDList();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRetrieveReturnsArray()
    {
        $obj = $this->getCategory();
        $this->assertInternalType('array',
            $obj::Retrieve([], 1, 1));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCanAccessReturnsFalse()
    {
        $obj = $this->getCategory();
        $this->assertFalse($obj->CanAccess(''),
            'Returns false if categorylist is not an array');

        $this->assertFalse($obj->CanAccess(['1'], 1, 1),
            'Returns false if id not in categorylist');

        $this->assertFalse($obj->CanAccess(['2'], 1, 1),
            'Returns false if type not in typelist');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->CanAccess([]);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementViewsThrowsException()
    {
        $obj = $this->getCategory();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->IncrementViews();
    }
}

class CategoryMock extends SWIFT_TroubleshooterCategory
{
    public function __destruct()
    {
        // prevent exception to be thrown when destroying the object and it's not loaded
        $this->SetIsClassLoaded(true);
        parent::__destruct();
    }
}
