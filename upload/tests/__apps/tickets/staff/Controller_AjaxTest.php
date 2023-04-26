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
 * Class Controller_AjaxTest
 * @group tickets
 * @group tickets-staff
 */
class Controller_AjaxTest extends \SWIFT_TestCase
{
    public static $_next = 0;

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Staff\Controller_Ajax', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetTicketStatusOnDepartmentIdReturnsTrue()
    {
        $obj = $this->getMocked();
        $this->assertTrue($obj->GetTicketStatusOnDepartmentID(1, 'email', 1));

        $this->assertClassNotLoaded($obj, 'GetTicketStatusOnDepartmentID', 1, 'email', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetTicketTypeOnDepartmentIdReturnsTrue()
    {
        $obj = $this->getMocked();
        $this->assertTrue($obj->GetTicketTypeOnDepartmentID(1, 'email', 1));

        $this->assertClassNotLoaded($obj, 'GetTicketTypeOnDepartmentID', 1, 'email', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetTicketOwnerOnDepartmentIdReturnsTrue()
    {
        $obj = $this->getMocked();
        $this->assertTrue($obj->GetTicketOwnerOnDepartmentID(1, 'email', 1));

        $this->assertClassNotLoaded($obj, 'GetTicketOwnerOnDepartmentID', 1, 'email', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testFlagReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
        ]);

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturnOnConsecutiveCalls([], [1]);

        $this->assertFalse($obj->Flag(1));

        $this->assertTrue($obj->Flag(1));

        $this->assertClassNotLoaded($obj, 'Flag', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testClearFlagReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
        ]);

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturnOnConsecutiveCalls([], [1]);

        $this->assertFalse($obj->ClearFlag(1));

        $this->assertTrue($obj->ClearFlag(1));

        $this->assertClassNotLoaded($obj, 'ClearFlag', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchEmailReturnsTrue()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('QueryLimit')->willReturnCallback(function () {
            self::$_next = 0;
        });

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            if (in_array(self::$_next, [1, 5], true)) {
                \SWIFT::GetInstance()->Database->Record['email'] .= self::$_next;
            } else {
                if (self::$_next === 3) {
                    \SWIFT::GetInstance()->Database->Record['email'] = 'invalid';
                } else {
                    \SWIFT::GetInstance()->Database->Record['email'] = 'me@mail.com';
                }
            }

            return in_array(self::$_next, [1, 2, 3, 4, 6, 7, 8], true);
        });

        $mockDb->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
        ]);

        $mockDb->Record = [
            'email' => 'me@mail.com',
            'fullname' => 'fullname',
            'organizationname' => 'organizationname',
        ];

        $obj = $this->getMocked([
            'Database' => $mockDb,
        ]);

        \SWIFT::GetInstance()->Database = $mockDb;

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturnOnConsecutiveCalls([], [1]);

        $this->assertFalse($obj->SearchEmail());

        $this->expectOutputRegex('/@/');

        $_POST['q'] = 'search';
        $this->assertTrue($obj->SearchEmail());

        $this->assertClassNotLoaded($obj, 'SearchEmail');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testReplyLockReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
        ]);

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturnOnConsecutiveCalls([], [1]);

        $this->assertFalse($obj->ReplyLock(0),
            'Returns false with invalid id');

        $this->assertFalse($obj->ReplyLock(1),
            'Returns false without access');

        $_POST['contents'] = 'contents';
        $this->assertTrue($obj->ReplyLock(1));

        $this->assertClassNotLoaded($obj, 'ReplyLock', 1);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_AjaxMock
     */
    private function getMocked(array $services = [])
    {
        $mockAjax = $this->getMockBuilder('Tickets\Library\Ajax\SWIFT_TicketAjaxManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mockView = $this->getMockBuilder('Tickets\Staff\View_Ajax')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->getMockObject('Tickets\Staff\Controller_AjaxMock', array_merge($services, [
            'View' => $mockView,
            'TicketAjaxManager' => $mockAjax,
        ]));
    }
}

class Controller_AjaxMock extends Controller_Ajax
{
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

