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
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Parser\Models\EmailQueue;

use Knowledgebase\Admin\LoaderMock;
use PHPUnit\Framework\Constraint\IsType;
use SWIFT_Exception;

/**
 * Class EmailQueueSignatureTest
 * @group parser-models
 */
class EmailQueueSignatureTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Models\EmailQueue\SWIFT_EmailQueueSignature', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Create(1, 'signature'));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'Create', '', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Update(1, 'signature'));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'Update', '', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Delete());

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

        $this->setExpectedException(SWIFT_EmailQueue_Exception::class);
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
    public function testGetEmailQueueIDReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_INT, $obj->GetEmailQueueID());

        $obj->SetIsClassLoaded(false);

        $this->expectException(SWIFT_EmailQueue_Exception::class);
        $obj->GetEmailQueueID();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetEmailQueueSignatureIDReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_INT, $obj->GetEmailQueueSignatureID());

        $obj->SetIsClassLoaded(false);

        $this->expectException(SWIFT_EmailQueue_Exception::class);
        $obj->GetEmailQueueSignatureID();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessUpdatePoolReturnsTrue()
    {
        $obj = $this->getMocked();

        $obj->_updatePool = ['key' => 'value'];

        $this->assertTrue($obj->ProcessUpdatePool(),
            'Returns true');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_EmailQueueSignatureMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Models\EmailQueue\SWIFT_EmailQueueSignatureMock');
    }
}

class SWIFT_EmailQueueSignatureMock extends SWIFT_EmailQueueSignature
{
    public $Database;
    public $_dataStore;
    public $_updatePool;

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'emailqueueid' => 1,
            'tgroupid' => 1,
            'type' => APP_BACKEND,
            'departmentid' => 1,
            'tickettypeid' => 1,
            'priorityid' => 1,
            'ticketstatusid' => 1,
            'ticketautoresponder' => 1,
            'queuesignatureid' => 1,
            'fetchtype' => 'imap',
        ]);

        parent::__construct(1);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

