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

namespace Tickets\Models\View;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class TicketViewFieldTest
* @group tickets
*/
class TicketViewFieldTest extends \SWIFT_TestCase
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
            'ticketviewfieldid' => 1,
        ]);
        $data = new \SWIFT_DataStore([
            'ticketviewfieldid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\View\SWIFT_TicketViewFieldMock', [
            'Data' => $data,
        ]);
        $this->assertInstanceOf('Tickets\Models\View\SWIFT_TicketViewField', $obj);
    }
}

class SWIFT_TicketViewFieldMock extends SWIFT_TicketViewField
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

