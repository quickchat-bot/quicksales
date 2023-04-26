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

namespace Tickets\Models\SLA;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class SLATest
* @group tickets
*/
class SLATest extends \SWIFT_TestCase
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
            'slaplanid' => 1,
        ]);
        $data = new \SWIFT_DataStore([
            'slaplanid' => 1,
            '_criteria' => 1,
            'ruletype' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\SLA\SWIFT_SLAMock', [
            'Data' => $data,
        ]);
        $this->assertInstanceOf('Tickets\Models\SLA\SWIFT_SLA', $obj);
    }
}

class SWIFT_SLAMock extends SWIFT_SLA
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

