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

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class View_FilterTest
 * @group tickets
 * @group tickets-staff
 */
class View_FilterTest extends \SWIFT_TestCase
{
    public static $prop = [];

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false, true, false);

        $obj = $this->getMocked([
            'Database' => $mockDb,
        ]);

        $filter = $this->getMockBuilder('Tickets\Models\Filter\SWIFT_TicketFilter')
            ->disableOriginalConstructor()
            ->getMock();

        $filter->method('GetProperty')->willReturnCallback(function ($x) {
            if (!isset(static::$prop[$x])) {
                if (strtolower(substr($x, -2)) === 'id') {
                    static::$prop[$x] = 1;
                } else {
                    static::$prop[$x] = $x;
                }
            }

            return static::$prop[$x];
        });

        $obj->Database->Record['staffgroupid'] = 1;

        $this->expectOutputRegex('/script/');

        $this->assertTrue($obj->Render(2, $filter));

        $_POST['rulecriteria'] = [1 => ['title', '=', 'title']];
        static::$prop['criteriaoptions'] = 2;
        $this->assertTrue($obj->Render(1, $filter));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Render(1));
    }

    public function testRenderGridReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->expectOutputRegex('/script/');
        $this->assertTrue($obj->RenderGrid());
        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderGrid());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGridRenderReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertNotEmpty($obj::GridRender([
            'lastactivity' => 1,
            'categorytitle' => 1,
            'lastusage' => 1,
            'staffid' => 1,
        ]));

        $this->assertNotEmpty($obj::GridRender([
            'lastactivity' => 0,
            'categorytitle' => 1,
            'lastusage' => 1,
            'staffid' => 2,
        ]));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderMenuReturnsHtml()
    {
        $obj = $this->getMocked();

        $this->assertContains('swiftdropdown', $obj->RenderMenu([1 => ['ticketfilterid' => 1]]));
        $this->assertClassNotLoaded($obj, 'RenderMenu', []);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_FilterMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Staff\View_FilterMock', $services);
    }
}

class View_FilterMock extends View_Filter
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

