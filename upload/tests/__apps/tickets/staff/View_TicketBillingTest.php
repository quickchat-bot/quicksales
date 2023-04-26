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

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class View_ViewTest
 * @group tickets
 * @group tickets-staff
 */
class View_TicketBillingTest extends \SWIFT_TestCase
{
    public static $_next = 0;

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderBillingReturnsTrue()
    {
        $obj = $this->getMocked();

        $mock = $obj->getTicketMock($this);

        $this->expectOutputRegex('/script/');

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'languagecache') {
                return [1 => ['languagecode' => 'en-us']];
            }

            if ($x == 'templategroupcache') {
                return [1 => ['languageid' => 1, 'tgroupid' => 1]];
            }
            return [
                1 => [
                    'departmentapp' => 'tickets',
                    'parentdepartmentid' => 0,
                    'uservisibilitycustom' => 0,
                    'departmenttype' => 'public',
                    'type' => 'public',
                    'ticketpriorityid' => '1',
                    'ruletype' => '1',
                    'isenabled' => '1',
                    'tgroupid' => '1',
                    '_criteria' => [
                        1 => [
                            'event',
                            'event',
                            'event',
                        ],
                    ],
                ],
            ];
        };

        $this->assertTrue($obj->RenderBilling($mock, 1, 1, 1, 1, 0));

        $obj->_renderBillingEntries = false;
        $this->assertTrue($obj->RenderBilling($mock, 1, 1, 1, 1, 0));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderBilling($mock, 1, 1, 1, 1, 0),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderBillingUserReturnsTrue()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                return self::$_next = 1;
            }
            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            return self::$_next % 2;
        });

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketmaskid' => 0,
            'workerstaffid' => 1,
            'dateline' => 0,
            'workdateline' => 0,
            'timespent' => 0,
            'timebillable' => 0,
            'tickettimetracknoteid' => 1,
            'notecolor' => '#ffffff',
            'notes' => 'notes',
            'editedstaffid' => 0,
            'isedited' => 1,
        ];

        $mockDb->method('QueryFetch')->willReturn($arr);

        $mockDb->Record = $arr;

        $obj = $this->getMocked([
            'Database' => $mockDb,
        ]);

        \SWIFT::GetInstance()->Database = $mockDb;

        $mock = $obj->getUserMock($this);

        $this->expectOutputRegex('/script/');

        $this->assertTrue($obj->RenderBillingUser($mock));

        $obj->_renderBillingEntries = false;

        $this->assertTrue($obj->RenderBillingUser($mock));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderBillingUser($mock),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     * @throws \Tickets\Library\Ticket\SWIFT_Ticket_Exception
     */
    public function testRenderBillingFormReturnsTrue()
    {
        $obj = $this->getMocked();

        $mock = $obj->getTicketMock($this);
        $mock2 = $this->getMockBuilder('Tickets\Models\TimeTrack\SWIFT_TicketTimeTrack')
            ->disableOriginalConstructor()
            ->getMock();
        $mock2->method('GetIsClassLoaded')->willReturn(true);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'workerstaffid' => 1,
            'dateline' => 0,
            'workdateline' => 0,
            'timespent' => 0,
            'timebillable' => 0,
            'tickettimetracknoteid' => 1,
            'notecolor' => '#ffffff',
            'notes' => 'notes',
        ]);
        $this->assertTrue($obj->RenderBillingForm(1, $mock, $mock2));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderBillingForm(1, $mock, $mock2),
            'Returns false if class is not loaded');
    }

    public function testRenderBillingEntriesThrowsException()
    {
        $obj = $this->getMocked();

        $mock = $this->getMockBuilder('SWIFT_Base')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInvalidData($obj, 'RenderBillingEntries', $mock);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderBillingEntriesReturnsEmpty()
    {
        $obj = $this->getMocked();

        $mock = $this->getMockBuilder('Base\Models\User\SWIFT_User')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('GetIsClassLoaded')->willReturn(true);

        $this->assertEmpty($obj->RenderBillingEntries($mock));

        $this->assertClassNotLoaded($obj, 'RenderBillingEntries', $mock);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderBillingEntriesReturnsHtml()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                return self::$_next = 1;
            }
            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            return self::$_next % 2;
        });

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketmaskid' => 0,
            'workerstaffid' => 1,
            'dateline' => 1,
            'workdateline' => 2,
            'timespent' => 0,
            'timebillable' => 0,
            'tickettimetracknoteid' => 1,
            'notecolor' => '#ffffff',
            'notes' => 'notes',
            'editedstaffid' => 1,
            'isedited' => 1,
        ];

        $mockDb->method('QueryFetch')->willReturn($arr);

        $mockDb->Record = $arr;

        $obj = $this->getMocked([
            'Database' => $mockDb,
        ]);

        \SWIFT::GetInstance()->Database = $mockDb;
        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturn('1');
        \SWIFT::GetInstance()->Staff = $mockStaff;

        $mock = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('GetIsClassLoaded')->willReturn(true);

        $this->assertNotEmpty($obj->RenderBillingEntries($mock));
    }

    /**
     * @throws \ReflectionException
     */
    public function testCreateBillingFormThrowsException()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('CreateBillingForm');
        $method->setAccessible(true);

        $tab = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceTab')
            ->disableOriginalConstructor()
            ->getMock();

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, $tab, 0, 0, 0);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_TicketMock
     */
    private function getMocked(array $services = [])
    {
        $mgr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldRendererStaff')
            ->disableOriginalConstructor()
            ->getMock();
        $mgr->method('Render')->willReturn('html');
        $ctr = $this->getMockBuilder('Tickets\Staff\Controller_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ctr->CustomFieldRendererStaff = $mgr;
        $services = array_merge($services, [
            'Controller' => $ctr,
        ]);
        return $this->getMockObject('Tickets\Staff\View_TicketMock', $services);
    }
}
