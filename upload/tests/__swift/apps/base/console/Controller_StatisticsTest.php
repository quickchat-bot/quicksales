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

namespace Base\Console;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * Class Controller_StatisticsTest
 * @group base
 * @group base-console
 */
class Controller_StatisticsTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\Console\Controller_Statistics', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $obj = $this->getMocked();

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('QueryFetch')->willReturn(['totalitems' => 5]);
        $mockDb->method('QueryFetchAll')->willReturn([['Name' => TABLE_PREFIX . SWIFT_Ticket::TABLE_NAME, 'Rows' => 5]]);

        $_SWIFT = \SWIFT::GetInstance();
        $_SWIFT->Database = $mockDb;
        $obj->Database = $mockDb;

        $this->expectOutputRegex('/{[\"\w\":.+\,+]*}/');

        $this->assertTrue($obj->Index(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Index');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_StatisticsMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Console\Controller_StatisticsMock');
    }
}

class Controller_StatisticsMock extends Controller_Statistics
{
    public $Database;

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct();
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

