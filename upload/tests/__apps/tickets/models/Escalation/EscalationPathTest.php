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
* @license       http://opencart.com.vn/license
* @link          http://opencart.com.vn
*
* ###############################################
*/

namespace Tickets\Models\Escalation;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class EscalationPathTest
* @group tickets
*/
class EscalationPathTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $this->getMockServices();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\SWIFT_Database $mockDb */
        $mockDb = $this->mockServices['Database'];
        $mockDb->method('QueryFetch')->willReturn([
            'escalationpathid' => 1,
        ]);
        $data = new \SWIFT_DataStore([
            'escalationpathid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\Escalation\SWIFT_EscalationPathMock', [
            'Data' => $data,
        ]);
        $this->assertInstanceOf('Tickets\Models\Escalation\SWIFT_EscalationPath', $obj);
    }
}

class SWIFT_EscalationPathMock extends SWIFT_EscalationPath
{

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct($this->Data);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

