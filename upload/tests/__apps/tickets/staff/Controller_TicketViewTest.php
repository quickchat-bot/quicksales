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
 * Class Controller_TicketTest
 * @group tickets
 * @group tickets-staff
 */
class Controller_TicketViewTest extends \SWIFT_TestCase
{
    public static $_next = 0;

    /**
     * @throws SWIFT_Exception
     */
    public function testViewReturnsTrue()
    {
        $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
            ->disableOriginalConstructor()
            ->getMock();

        $settings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('Get')->willReturnCallback(function ($x) {
            if ($x === 't_enpagin') {
                return '1';
            }

            if ($x === 't_postorder') {
                return 'desc';
            }

            return $x;
        });

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            static::$_next++;

            $is_user = in_array(static::$_next, [3, 4], true);

            $is_staff = in_array(static::$_next, [13, 14], true);

            $is_3rd = in_array(static::$_next, [23, 24], true);

            $is_bcc = in_array(static::$_next, [33, 34], true);

            $arr = [
                'ticketid' => static::$_next,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'ticketstatusid' => 1,
                'subject' => 'subject',
                'fullname' => 'fullname',
                'ticketpostid' => static::$_next,
                'creator' => '2',
                'staffid' => '1',
                'userid' => '1',
            ];

            if ($is_user) {
                \SWIFT::GetInstance()->Database->Record = $arr;
            }

            if ($is_staff) {
                $arr['creator'] = '1';
                \SWIFT::GetInstance()->Database->Record = $arr;
            }

            if ($is_3rd) {
                $arr['creator'] = '5';
                \SWIFT::GetInstance()->Database->Record = $arr;
            }

            if ($is_bcc) {
                $arr['creator'] = '4';
                \SWIFT::GetInstance()->Database->Record = $arr;
            }

            return $is_user || $is_staff || $is_3rd || $is_bcc || 1 === (static::$_next % 2);
        });
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "ticketmaskid = ''")) {
                return [];
            }

            return [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'ticketstatusid' => 1,
                'subject' => 'subject',
                'fullname' => 'fullname',
                'ticketmaskid' => '1',
                'ownerstaffid' => '2',
                'userid' => '1',
                'userorganizationid' => '0',
            ];
        });
        $mockDb->Record = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketstatusid' => 1,
            'subject' => 'subject',
            'fullname' => 'fullname',
            'ticketpostid' => '1',
            'creator' => '2',
            'staffid' => '1',
            'userid' => '1',
        ];

        $obj = $this->getMocked([
            'Emoji' => $mockEmoji,
            'Settings' => $settings,
            'Database' => $mockDb,
        ]);

        \SWIFT::GetInstance()->UserInterface = $obj->UserInterface;
        \SWIFT::GetInstance()->Database = $mockDb;

        $this->assertFalse($obj->View(0));

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $this->assertFalse($obj->View('none'));

        $this->assertFalse($obj->View(1),
            'Returns false with staff_tcanviewtickets = 0');

        $this->assertTrue($obj->View(1, 'inbox', -1, -1, -1, 'none', -2),
            'Returns true with creator = user');

        $this->assertTrue($obj->View(1, 'inbox', -1, -1, -1, 'none', 'none'),
            'Returns true with creator = staff');

        $this->assertTrue($obj->View(1, 'inbox', -1, -1, -1, -1, -1),
            'Returns true with creator = 3rd party');

        $this->assertTrue($obj->View(1, 'inbox', -1, -1, -1, -1, -1),
            'Returns true with creator = bcc');

        $this->assertClassNotLoaded($obj, 'View', 1);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_TicketMock
     */
    private function getMocked(array $services = [])
    {
        $view = $this->getMockBuilder('Tickets\Staff\View_Ticket')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->getMockObject('Tickets\Staff\Controller_TicketMock', array_merge([
            'View' => $view,
        ], $services));
    }
}
