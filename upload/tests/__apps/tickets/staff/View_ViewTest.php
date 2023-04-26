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
 * Class View_ViewTest
 * @group tickets
 * @group tickets-staff
 */
class View_ViewTest extends \SWIFT_TestCase
{
    public static $_next = 0;

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $mockStaff->method('GetAssignedDepartments')->willReturn([1, 3]);
        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls('1', '0');
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetProperty')->willReturnCallback(function ($x) {
            if (strtolower(substr($x, -2)) === 'id') {
                return '1';
            }

            return $x;
        });
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;
            if (self::$_next >= 4 && self::$_next <= 7) {
                \SWIFT::GetInstance()->Database->Record = [
                    'staffgroupid' => 1,
                    'fieldtype' => 2,
                    'linktype' => self::$_next - 2,
                    'linktypeid' => 3,
                    'ticketviewlinkid' => self::$_next - 2,
                ];
                return true;
            }
            return self::$_next % 2;
        });

        $obj = $this->getMocked([
            'Database' => $mockDb,
            'Staff' => $mockStaff,
        ]);

        \SWIFT::GetInstance()->Database = $mockDb;

        $view = $this->getMockBuilder('Tickets\Models\View\SWIFT_TicketView')
            ->disableOriginalConstructor()
            ->getMock();

        $view->method('GetTicketViewID')->willReturn(1);
        $view->method('GetProperty')->willReturnCallback(function ($x) {
            if (strtolower(substr($x, -2)) === 'id') {
                return '1';
            }

            return $x;
        });

        $obj->Database->Record = [
            'staffgroupid' => 1,
            'fieldtype' => 2,
            'linktype' => 1,
            'linktypeid' => 1,
            'ticketviewlinkid' => 1,
        ];

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        $mockCache->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'departmentcache') {
                return [
                    1 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    2 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    3 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                    4 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                ];
            }

            if ($x === 'staffcache') {
                return [
                    1 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '1',
                    ],
                    2 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '0',
                    ],
                ];
            }

            if ($x === 'groupassigncache') {
                return [
                    1 => [
                        1 => 1,
                        3 => 3,
                    ],
                ];
            }

            if ($x === 'tickettypecache' || $x === 'statuscache') {
                return [
                    1 => [
                        1 => 1,
                        'departmentid' => 2,
                    ],
                    2 => [
                        'departmentid' => 0,
                    ],
                ];
            }

            return [
                1 => [
                    1 => [1 => [1]],
                ],
            ];
        });
        $obj->Cache = $mockCache;
        \SWIFT::GetInstance()->Cache = $mockCache;

        $_POST['_isDialog'] = 1;
        $_POST['viewfields'] = [1];
        $this->assertTrue($obj->Render(1, $view));

        unset($_POST['viewfields']);
        $this->assertTrue($obj->Render(2, $view));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Render(1));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderGridReturnsTrue() {
        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $mockStaff->method('GetAssignedDepartments')->willReturn([1]);
        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls('1', '0');
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetProperty')->willReturnCallback(function ($x) {
            if (strtolower(substr($x, -2)) === 'id') {
                return '1';
            }

            return $x;
        });
        $obj = $this->getMocked([
            'Staff' => $mockStaff,
        ]);

        $this->assertTrue($obj->RenderGrid());
        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderGrid());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGridRenderReturnsArray() {
        $obj = $this->getMocked();

        $this->assertNotEmpty($obj::GridRender([
            'staffid' => 1,
            'viewscope' => 1,
        ]));
        $this->assertNotEmpty($obj::GridRender([
            'staffid' => 2,
            'viewscope' => 2,
        ]));
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_ViewMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Staff\View_ViewMock', $services);
    }
}

class View_ViewMock extends View_View
{
    public $Database;
    public $Cache;

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

