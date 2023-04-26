<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Banjo Mofesola Paul <banjo.paul@aurea.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Parser\Models\CatchAll;

use Knowledgebase\Admin\LoaderMock;
use PHPUnit\Framework\Constraint\IsType;
use SWIFT_Exception;

/**
 * Class CatchAllRuleTest
 * @group parser-models
 */
class CatchAllRuleTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();

        $this->assertInstanceOf('Parser\Models\CatchAll\SWIFT_CatchAllRule', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessUpdatePoolReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->ProcessUpdatePool(),
            'Returns false');

        $obj->_updatePool = ['key' => 'value'];

        $this->assertTrue($obj->ProcessUpdatePool(),
            'Returns true');


        $obj->SetIsClassLoaded(false);

        $this->assertFalse($obj->ProcessUpdatePool(),
            'Returns false');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetCatchAllRuleIDReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_INT, $obj->GetCatchAllRuleID(),
            'Returns int');

        $obj->SetIsClassLoaded(false);

        $this->expectException(SWIFT_CatchAll_Exception::class);
        $obj->GetCatchAllRuleID();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testLoadDataReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->LoadData(1),
            'Returns true');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDataStoreReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertEquals($obj->_dataStore, $obj->GetDataStore(),
            'Returns _dataStore');

        $obj->SetIsClassLoaded(false);

        $this->assertClassNotLoaded($obj, 'GetDataStore');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertyReturnsTrue()
    {
        $obj = $this->getMocked();

        $obj->_dataStore['key'] = 'value';
        $this->assertEquals('value', $obj->GetProperty('key'));

        $this->setExpectedException(SWIFT_CatchAll_Exception::class);
        $obj->GetProperty('no_key');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertyClassNotLoaded()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetProperty', ['key']);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateWithEmptyParams()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_CatchAll_Exception::class);
        $obj->Create('', '', '', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateWithNullInsertID()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['Insert_ID'] = function() {
            return null;
        };

        $this->setExpectedException(SWIFT_CatchAll_Exception::class);
        $obj->Create('title', 'expr', 1, SORT_ASC);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertEquals(1, $obj->Create('title', 'expr', 1, SORT_ASC));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Update('title', 'expr', 1, SORT_ASC));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'Update', 'title', 'expr', 1, SORT_ASC);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateWithEmptyParams()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_CatchAll_Exception::class);
        $obj->Update('', '', '', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Delete());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'Delete');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListReturnsFalse()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->DeleteList('non_array'));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteOnEmailQueueReturnsFalse()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->DeleteOnEmailQueue('non_array'));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteOnEmailQueueReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->DeleteOnEmailQueue([2]));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_CatchAllRuleMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Models\CatchAll\SWIFT_CatchAllRuleMock');
    }
}

class SWIFT_CatchAllRuleMock extends SWIFT_CatchAllRule
{
    public $Database;
    public $_updatePool;
    public $_dataStore;

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['catchallruleid' => 1]);

        parent::__construct(1);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

