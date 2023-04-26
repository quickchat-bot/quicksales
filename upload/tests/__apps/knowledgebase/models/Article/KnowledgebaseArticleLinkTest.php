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

namespace Knowledgebase\Models\Article;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;

/**
 * Class KnowledgebaseArticleLinkTest
 * @group knowledgebase
 */
class KnowledgebaseArticleLinkTest extends \SWIFT_TestCase
{
    /**
     * @param bool $loaded
     * @param bool|array $pool
     * @return LinkMock
     * @throws SWIFT_Exception
     */
    public function getModel($loaded = true, $pool = [1])
    {
        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            '1' => [
                'displayorder' => 0,
            ],
        ]);

        $mockDB = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDB->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);
        $mockDB->method('AutoExecute')->willReturn(true);
        $mockDB->method('QueryFetch')
            ->willReturn([
                'kbarticlelinkid' => 1,
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
     * @throws SWIFT_Exception
     */
    public function testDestructCallsDestructor()
    {
        $obj = $this->getModel();
        $this->assertNotNull($obj);
        $obj->__destruct();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getModel();
        $this->assertInstanceOf('Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticleLink', $obj);
        $this->setExpectedException('SWIFT_Exception',
            'Failed to load Knowledgebase Article Link Object');
        $this->getModel(false);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessUpdatePoolReturnsFalse()
    {
        $obj = $this->getModel();
        $obj->SetUpdatePool([]);
        $this->assertFalse($obj->ProcessUpdatePool());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->ProcessUpdatePool();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetKnowledgebaseArticleLinkIdThrowsException()
    {
        $obj = $this->getModel();

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetKnowledgebaseArticleLinkID();
    }

    /**
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testLoadDataThrowsException()
    {
        $obj = $this->getModel();
        $data = new SWIFT_DataStore([]);
        $data->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $mock = new \ReflectionClass($obj);
        $method = $mock->getMethod('LoadData');
        $method->setAccessible(true);
        $method->invoke($obj, $data);
    }

    /**
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testLoadDataReturnsTrue()
    {
        $obj = $this->getModel();
        $data = new SWIFT_DataStore(['kbarticlelinkid' => 1]);
        $mock = new \ReflectionClass($obj);
        $method = $mock->getMethod('LoadData');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke($obj, $data));
        $data = new SWIFT_DataStore([]);
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invoke($obj, $data);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertyThrowsException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->GetProperty('key');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertyReturnsValue()
    {
        $obj = $this->getModel();
        $this->assertEquals(1, $obj->GetProperty('kbarticlelinkid'));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetProperty('key');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDataStoreReturnsArray()
    {
        $obj = $this->getModel();
        $this->assertInternalType('array', $obj->GetDataStore());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetDataStore();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateThrowsException()
    {
        $obj = $this->getModel();

        SWIFT::GetInstance()->Database->Insert_ID(); // advance id
        $this->setExpectedException('SWIFT_Exception', SWIFT_CREATEFAILED);
        $obj::Create(1, 1, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateReturnsId()
    {
        $obj = $this->getModel();
        $this->assertEquals(1, $obj::Create(1, 1, 1));

        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj::Create(0, 1, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListReturnsFalse()
    {
        $obj = $this->getModel();

        $this->assertFalse($obj::DeleteList([]));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getModel();
        $this->assertTrue($obj->Delete());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Delete();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteOnLinkTypeReturnsFalse()
    {
        $obj = $this->getModel();
        $this->assertFalse($obj::DeleteOnLinkType(1, []));

        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj::DeleteOnLinkType(0, []);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRetrieveLinkIdListOnArticleThrowsException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj::RetrieveLinkIDListOnArticle(0, -1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteOnKnowledgebaseArticleReturnsBoolean()
    {
        $obj = $this->getModel();

        $this->assertFalse($obj::DeleteOnKnowledgebaseArticle([]));
    }
}

class LinkMock extends SWIFT_KnowledgebaseArticleLink
{
    private $_getPool;

    public function __construct(SWIFT_Data $_SWIFT_DataObject, $pool = [1])
    {
        $this->Load = new LoaderMock();
        parent::__construct($_SWIFT_DataObject);

        $this->_getPool = $pool;
    }

    public function __destruct()
    {
        // prevent exception to be thrown when destroying the object and it's not loaded
        $this->SetIsClassLoaded(true);
        parent::__destruct();
    }

    public function GetUpdatePool()
    {
        return $this->_getPool;
    }

    public function SetUpdatePool($_pool)
    {
        return $this->_getPool = $_pool;
    }

    public function ProcessUpdatePool()
    {
        if (empty($this->_dataStore)) {
            return true;
        }
        return parent::ProcessUpdatePool();
    }
}