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

namespace Tickets\Models\Watcher;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class TicketWatcherTest
* @group tickets
*/
class TicketWatcherTest extends \SWIFT_TestCase
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
            'ticketwatcherid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\Watcher\SWIFT_TicketWatcherMock');
        $this->assertInstanceOf('Tickets\Models\Watcher\SWIFT_TicketWatcher', $obj);
    }
}

class SWIFT_TicketWatcherMock extends SWIFT_TicketWatcher
{

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct(1);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}
