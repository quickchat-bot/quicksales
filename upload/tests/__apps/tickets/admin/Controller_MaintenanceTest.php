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

namespace Tickets\Admin;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_MaintenanceTest
 * @group tickets
 */
class Controller_MaintenanceTest extends \SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Admin\Controller_Maintenance', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $obj = $this->getMocked();
        $view = $this->getMockBuilder('Tickets\Admin\View_Maintenance')
            ->disableOriginalConstructor()
            ->getMock();
        $obj->View = $view;

        $this->assertTrue($obj->Index(),
            'Returns true with admin_tcanrunmaintenance = 1');

        $this->assertTrue($obj->Index(),
            'Returns true with admin_tcanrunmaintenance = 0');

        $this->assertClassNotLoaded($obj, 'Index');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testReIndexReturnsTrue()
    {
        $obj = $this->getMocked();
        $view = $this->getMockBuilder('Tickets\Admin\View_Maintenance')
            ->disableOriginalConstructor()
            ->getMock();
        $obj->View = $view;

        $obj->Database->Record = [
            'ticketid' => 1,
            'ticketpostid' => 1,
            'contents' => 'contents',
        ];

        $this->assertTrue($obj->ReIndex(1, 2, 0, 0, 0));

        $this->assertTrue($obj->ReIndex(0, 'a'));

        $this->assertClassNotLoaded($obj, 'ReIndex', 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testReIndexPropertiesReturnsTrue()
    {
        $obj = $this->getMocked();
        $view = $this->getMockBuilder('Tickets\Admin\View_Maintenance')
            ->disableOriginalConstructor()
            ->getMock();
        $obj->View = $view;

        $obj->Database->Record = [
            'ticketid' => 1,
            'iswatched' => 0,
            'trasholddepartmentid' => 0,
            'ticketstatusid' => 0,
            'tickettypeid' => 0,
            'priorityid' => 0,
            'ownerstaffid' => 0,
            'departmentid' => 0,
            'lastactivity' => 0,
            'totalreplies' => 0,
            'isresolved' => 0,
            'dateline' => 0,
            'lastpostid' => 0,
        ];

        $this->assertTrue($obj->ReIndexProperties(1, 2, 0, 0));

        $this->assertTrue($obj->ReIndexProperties(0, 'a'));

        $this->assertClassNotLoaded($obj, 'ReIndexProperties', 0);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_MaintenanceMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Admin\Controller_MaintenanceMock');
    }
}

class Controller_MaintenanceMock extends Controller_Maintenance
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\SWIFT_Database */
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

