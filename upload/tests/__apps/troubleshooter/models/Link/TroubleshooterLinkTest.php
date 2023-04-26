<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
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

namespace Troubleshooter\Models\Link;

use SWIFT_Data;
use SWIFT_Exception;

/**
 * Class TroubleshooterLinkTest
 * @group troubleshooter
 */
class TroubleshooterLinkTest extends \SWIFT_TestCase
{
    /**
     * @param bool $loaded
     * @param bool|array $pool
     * @return SWIFT_TroubleshooterLink
     * @throws SWIFT_Exception
     */
    public function getLink($loaded = true, $pool = [1])
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
                'troubleshooterlinkid' => 1,
                'linktype' => '1',
                'staffvisibilitycustom' => '1',
            ]);
        $mockDB->method('Insert_ID')
            ->willReturnOnConsecutiveCalls(1, 0);

        \SWIFT::GetInstance()->Database = $mockDB;
        \SWIFT::GetInstance()->Cache = $mockCache;

        $data = new \SWIFT_DataID(1);
        $data->SetIsClassLoaded($loaded);
        $obj = new LinkMock($data, $pool);
        $this->mockProperty($obj, 'Database', $mockDB);

        return $obj;
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getLink();
        $this->assertInstanceOf('Troubleshooter\Models\Link\SWIFT_TroubleshooterLink', $obj);

        $obj->__destruct();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testConstructorThrowsException()
    {
        $this->setExpectedException('SWIFT_Exception', 'Failed to load Troubleshooter Link Object');
        $this->getLink(false);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testProcessUpdatePoolThrowsException()
    {
        $obj = $this->getLink();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->ProcessUpdatePool();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testProcessUpdatePoolReturnsTrue()
    {
        $obj = $this->getLink();

        $this->assertTrue($obj->ProcessUpdatePool(),
            'Returns true after updating');

        $obj = $this->getLink(true, false);

        $this->assertFalse($obj->ProcessUpdatePool(),
            'Returns false if update pool is not array');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetTroubleshooterLinkIDThrowsException()
    {
        $obj = $this->getLink();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetTroubleshooterLinkID();
    }

    /**
     * @throws \SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testLoadDataThrowsExceptionWithNullParameter()
    {
        $obj = $this->getLink();

        // LoadData is protected. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('LoadData');
        $method->setAccessible(true);

        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invoke($obj, null);
    }

    /**
     * @throws \SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testLoadDataThrowsExceptionWithEmptyData()
    {
        $obj = $this->getLink();

        // LoadData is protected. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('LoadData');
        $method->setAccessible(true);

        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invoke($obj, new \SWIFT_DataStore([]));
    }

    /**
     * @throws \SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testLoadDataReturnsTrue()
    {
        $obj = $this->getLink();

        // LoadData is protected. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('LoadData');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($obj,
            new \SWIFT_DataStore(['troubleshooterlinkid' => 1])));
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetDataStoreThrowsException()
    {
        $obj = $this->getLink();
        $this->assertNotNull($obj->GetDataStore());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetDataStore();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetPropertyThrowsException()
    {
        $obj = $this->getLink();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetProperty('');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetPropertyThrowsInvalidDataException()
    {
        $obj = $this->getLink();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->GetProperty('invalid');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetPropertyReturnsValue()
    {
        $obj = $this->getLink();
        $this->assertEquals(1, $obj->GetProperty('troubleshooterlinkid'));
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testCreateThrowsFailedException()
    {
        $obj = $this->getLink();
        // decrease id
        \SWIFT::GetInstance()->Database->Insert_ID();
        $this->setExpectedException('SWIFT_Exception', SWIFT_CREATEFAILED);
        $obj::Create('title', 'description', 1);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testCreateThrowsException()
    {
        $obj = $this->getLink();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj::Create('', '', 0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getLink();
        $this->assertTrue($obj->Delete(),
            'Returns true after deleting');

        // class is already unloaded
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Delete();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testDeleteListReturnsFalse()
    {
        $obj = $this->getLink();
        $this->assertFalse($obj::DeleteList(''),
            'Returns false if array is not provided');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testDeleteOnTroubleshooterCategoryReturnsFalse()
    {
        $obj = $this->getLink();
        $this->assertFalse($obj::DeleteOnTroubleshooterCategory(''),
            'Returns false if array is not provided');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testDeleteOnTroubleshooterStepReturnsTrue()
    {
        $obj = $this->getLink();

        $this->assertFalse($obj::DeleteOnTroubleshooterStep(''),
            'Returns false if array is not provided');

        $this->assertTrue($obj::DeleteOnTroubleshooterStep([1]),
            'Returns true after deleting');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testDeleteOnChildTroubleshooterStepReturnsFalse()
    {
        $obj = $this->getLink();

        $this->assertFalse($obj::DeleteOnChildTroubleshooterStep(''),
            'Returns false if array is not provided');

        // advance record pointer and return false
        \SWIFT::GetInstance()->Database->NextRecord();
        $this->assertFalse($obj::DeleteOnChildTroubleshooterStep([1]),
            'Returns false if there are no link ids');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRetrieveOnChildReturnsArray()
    {
        $obj = $this->getLink();

        $this->assertFalse($obj::RetrieveOnChild(''),
            'Returns false if array is not provided');

        $this->assertInternalType('array', $obj::RetrieveOnChild([1]));
    }
}

class LinkMock extends SWIFT_TroubleshooterLink
{
    private $_getPool;

    public function __construct(SWIFT_Data $_SWIFT_DataObject, $pool = [1])
    {
        parent::__construct($_SWIFT_DataObject);

        $this->_getPool = $pool;
    }

    public function __destruct()
    {
        // prevent exception to be thrown when destroying the object and it's not loaded
      //  $this->SetIsClassLoaded(true);
//        parent::__destruct();
    }

    public function GetUpdatePool()
    {
        return $this->_getPool;
    }
}
