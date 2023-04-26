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

namespace Parser\Library\EmailQueue;

use Knowledgebase\Admin\LoaderMock;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use Parser\Models\EmailQueue\SWIFT_EmailQueuePipe;
use PHPUnit\Framework\Constraint\IsType;
use SWIFT_Exception;

/**
 * Class EmailQueueTypeTest
 * @group parser-library
 */
class EmailQueueTypeTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Library\EmailQueue\SWIFT_EmailQueueType', $obj);

        $this->setExpectedException(SWIFT_EmailQueue_Exception::class);
        $obj->SpawnInstance('invalid');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetFromEmailQueueObjectReturnsEmailQueueTypeObject()
    {
        $obj = $this->getMocked();

        $this->assertInstanceOf(SWIFT_EmailQueueType::class, $obj->GetFromEmailQueueObject( new SWIFT_EmailQueuePipe( new \SWIFT_DataStore(
            $obj->_data
        ) ) ));

        $this->assertInstanceOf(SWIFT_EmailQueueType::class, $obj->GetFromEmailQueueObject( new SWIFT_EmailQueuePipe( new \SWIFT_DataStore(
            array_merge($obj->_data, [ 'type' => SWIFT_EmailQueueType::TYPE_TICKETS ])
        ) ) ));

        $this->assertInstanceOf(SWIFT_EmailQueueType::class, $obj->GetFromEmailQueueObject( new SWIFT_EmailQueuePipe( new \SWIFT_DataStore(
            array_merge($obj->_data, [ 'type' => SWIFT_EmailQueueType::TYPE_NEWS ])
        ) ) ));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetQueueTypeReturnsString()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_STRING, $obj->GetQueueType());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetQueueType');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSetValueReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->SetValue('key', 'value'));

        $this->setExpectedException(SWIFT_EmailQueue_Exception::class);
        $obj->SetValue('', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSetValueThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'SetValue', '', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetValueWorksFine()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_STRING, $obj->GetValue('key'));

        $obj->SetValue('key', 'value');
        $this->assertEquals('value', $obj->GetValue('key'));

        $this->setExpectedException(SWIFT_EmailQueue_Exception::class);
        $obj->GetValue('');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetValueThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetValue', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetValueContainerReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_ARRAY, $obj->GetValueContainer());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetValueContainer');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSetEmailQueueThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'SetEmailQueue', new SWIFT_EmailQueuePipe( new \SWIFT_DataID(1) ));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetEmailQueueReturnsEmailQueueObject()
    {
        $obj = $this->getMocked();

        $obj->SetEmailQueue(new SWIFT_EmailQueuePipe( new \SWIFT_DataID(1) ));
        $this->assertInstanceOf(SWIFT_EmailQueue::class, $obj->GetEmailQueue());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetEmailQueue');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_EmailQueueTypeMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Library\EmailQueue\SWIFT_EmailQueueTypeMock');
    }
}

class SWIFT_EmailQueueTypeMock extends SWIFT_EmailQueueType
{
    public $_emailQueueObject;
    public $_dataStore;
    public $_data;

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);
        $this->_data = [
            'departmentid' => 1,
            'emailqueueid' => 1,
            'priorityid' => 1,
            'queuesignatureid' => 1,
            'tgroupid' => 1,
            'ticketautoresponder' => false,
            'ticketpostid' => 1,
            'ticketslaplanid' => 1,
            'ticketstatusid' => 1,
            'tickettypeid' => 1,
            'type' => SWIFT_EmailQueueType::TYPE_BACKEND,
        ];

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($this->_data);

        parent::__construct(SWIFT_EmailQueueType::TYPE_BACKEND);
    }

    public function SpawnInstance($_queueType) {
        return parent::__construct($_queueType);
    }

    public function SetValue($_key, $_value)
    {
        return parent::SetValue($_key, $_value);
    }

    public function SetEmailQueue(SWIFT_EmailQueue $_SWIFT_EmailQueueObject)
    {
        return parent::SetEmailQueue($_SWIFT_EmailQueueObject); // TODO: Change the autogenerated stub
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

