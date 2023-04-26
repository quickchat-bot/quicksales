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
 * Class Controller_ManageTest
 * @group tickets
 * @group tickets-staff
 */
class Controller_ManageTest extends \SWIFT_TestCase
{
    public static $_next = 0;
    public static $_prop = [];

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Staff\Controller_Manage', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPutBackListThrowsException()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([]);

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'ticketmaskid' => 0,
            'departmentid' => 1,
        ];
        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;

        $this->setExpectedException('SWIFT_Exception', 'Access Denied to Ticket: 1');
        $obj::PutBackList([1], true);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPutBackListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj::PutBackList([], true),
            'Returns true after deleting with staff_trcandeletesubscriber = 1');

        $this->assertFalse($obj::PutBackList([], true),
            'Returns false after rendering with staff_trcandeletesubscriber = 0');

        unset($_POST['csrfhash']);
        $this->assertFalse($obj::PutBackList([], false),
            'Returns false if csrfhash is not provided');

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetAssignedDepartments')->willReturn([1]);
        $mockStaff->method('GetPermission')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $mockStaff;

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'ticketmaskid' => 0,
            'trasholddepartmentid' => 0,
            'departmentid' => &static::$_prop['departmentid'],
            'ticketstatusid' => 1,
        ];
        static::$_prop['departmentid'] = 1;
        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                return self::$_next = 1;
            }
            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            static::$_next++;

            if (static::$_next === 2) {
                static::$_prop['departmentid'] = 0;
            }

            return in_array(static::$_next, [1, 2], true);
        });
        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryFetch')->willReturn($arr);
        $mockDb->Record = $arr;
        \SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            0 => [1 => [1]],
            1 => [1 => [1]],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertTrue($obj::PutBackList([1], true));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'ticketmaskid' => 0,
            'trasholddepartmentid' => 0,
            'departmentid' => 1,
            'ticketstatusid' => 1,
        ];

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;
        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $this->assertTrue($obj->PutBack(1));

        $this->assertClassNotLoaded($obj, 'PutBack', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListThrowsException()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([]);

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'ticketmaskid' => 0,
            'departmentid' => 1,
        ];
        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;

        $this->setExpectedException('SWIFT_Exception', 'Access Denied to Ticket: 1');
        $obj::DeleteList([1], true);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj::DeleteList([], true),
            'Returns true after deleting with staff_trcandeletesubscriber = 1');

        $this->assertFalse($obj::DeleteList([], true),
            'Returns false after rendering with staff_trcandeletesubscriber = 0');

        unset($_POST['csrfhash']);
        $this->assertFalse($obj::DeleteList([], false),
            'Returns false if csrfhash is not provided');

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetAssignedDepartments')->willReturn([1]);
        $mockStaff->method('GetPermission')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $mockStaff;

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'ticketmaskid' => 0,
            'trasholddepartmentid' => 0,
            'departmentid' => &static::$_prop['departmentid'],
            'ticketstatusid' => 1,
        ];
        static::$_prop['departmentid'] = 1;
        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                return self::$_next = 1;
            }
            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            static::$_next++;

            if (static::$_next === 2) {
                static::$_prop['departmentid'] = 0;
            }

            return in_array(static::$_next, [1, 2], true);
        });
        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryFetch')->willReturn($arr);
        $mockDb->Record = $arr;
        \SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            0 => [1 => [1]],
            1 => [1 => [1]],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertTrue($obj::DeleteList([1], true));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEmptyTrashThrowsException()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([]);

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'ticketmaskid' => 0,
            'departmentid' => 1,
        ];
        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;

        $this->setExpectedException('SWIFT_Exception', 'Access Denied to Ticket: 1');
        $obj->EmptyTrash();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEmptyTrashReturnsTrue()
    {
        $obj = $this->getMocked();

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetAssignedDepartments')->willReturn([1]);
        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls(1, 0);
        \SWIFT::GetInstance()->Staff = $mockStaff;

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'ticketmaskid' => 0,
            'trasholddepartmentid' => 0,
            'departmentid' => &static::$_prop['departmentid'],
            'ticketstatusid' => 1,
        ];
        static::$_prop['departmentid'] = 1;
        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                return self::$_next = 1;
            }
            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            static::$_next++;

            if (static::$_next === 2) {
                static::$_prop['departmentid'] = 0;
            }

            return in_array(static::$_next, [1, 2], true);
        });
        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryFetch')->willReturn($arr);
        $mockDb->Record = $arr;
        \SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            0 => [1 => [1]],
            1 => [1 => [1]],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertTrue($obj->EmptyTrash(),
            'Returns true with staff_tcandeleteticket = 1');

        $this->assertFalse($obj->EmptyTrash(),
            'Returns false with staff_tcandeleteticket = 0');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testTrashListThrowsException()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([]);

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'ticketmaskid' => 0,
            'departmentid' => 1,
        ];
        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;

        $this->setExpectedException('SWIFT_Exception', 'Access Denied to Ticket: 1');
        $obj::TrashList([1], true);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testTrashListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj::TrashList([], true),
            'Returns true after deleting with staff_trcandeletesubscriber = 1');

        $this->assertFalse($obj::TrashList([], true),
            'Returns false after rendering with staff_trcandeletesubscriber = 0');

        unset($_POST['csrfhash']);
        $this->assertFalse($obj::TrashList([], false),
            'Returns false if csrfhash is not provided');

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetAssignedDepartments')->willReturn([1]);
        $mockStaff->method('GetPermission')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $mockStaff;

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'duetime' => 0,
            'resolutionduedateline' => 0,
            'ticketmaskid' => 0,
            'trasholddepartmentid' => 0,
            'departmentid' => &static::$_prop['departmentid'],
            'ticketstatusid' => 1,
        ];
        static::$_prop['departmentid'] = 1;
        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                return self::$_next = 1;
            }
            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            static::$_next++;

            if (static::$_next === 2) {
                static::$_prop['departmentid'] = 0;
            }

            return in_array(static::$_next, [1, 2], true);
        });
        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryFetch')->willReturn($arr);
        $mockDb->Record = $arr;
        \SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            0 => [1 => [1]],
            1 => [1 => [1]],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertTrue($obj::TrashList([1], true));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testTrashReturnsTrue()
    {
        $obj = $this->getMocked();

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'ticketmaskid' => 0,
            'trasholddepartmentid' => 0,
            'departmentid' => 1,
            'ticketstatusid' => 1,
        ];

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;
        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $this->assertTrue($obj->Trash(1));

        $this->assertClassNotLoaded($obj, 'Trash', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSpamListThrowsException()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([]);

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'ticketmaskid' => 0,
            'departmentid' => 1,
        ];
        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;

        $this->setExpectedException('SWIFT_Exception', 'Access Denied to Ticket: 1');
        $obj::SpamList([1], true);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSpamListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj::SpamList([], true),
            'Returns true after deleting with staff_trcandeletesubscriber = 1');

        $this->assertFalse($obj::SpamList([], true),
            'Returns false after rendering with staff_trcandeletesubscriber = 0');

        unset($_POST['csrfhash']);
        $this->assertFalse($obj::SpamList([], false),
            'Returns false if csrfhash is not provided');

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetAssignedDepartments')->willReturn([1]);
        $mockStaff->method('GetPermission')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $mockStaff;

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'duetime' => 0,
            'resolutionduedateline' => 0,
            'ticketmaskid' => 0,
            'trasholddepartmentid' => 0,
            'departmentid' => &static::$_prop['departmentid'],
            'ticketstatusid' => 1,
        ];
        static::$_prop['departmentid'] = 1;
        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                return self::$_next = 1;
            }
            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            static::$_next++;

            if (static::$_next === 2) {
                static::$_prop['departmentid'] = 0;
            }

            return in_array(static::$_next, [1, 2], true);
        });
        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryFetch')->willReturn($arr);
        $mockDb->Record = $arr;
        \SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            0 => [1 => [1]],
            1 => [
                1 => [1],
                'bayescategoryid' => 1,
                'categorytype' => 2,
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertTrue($obj::SpamList([1], true));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSpamReturnsTrue()
    {
        $obj = $this->getMocked();

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'ticketmaskid' => 0,
            'trasholddepartmentid' => 0,
            'departmentid' => 1,
            'ticketstatusid' => 1,
        ];

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;
        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            0 => [1 => [1]],
            1 => [
                1 => [1],
                'bayescategoryid' => 1,
                'categorytype' => 2,
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertTrue($obj->Spam(1));

        $this->assertClassNotLoaded($obj, 'Spam', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testMergeListThrowsException()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([]);

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'ticketmaskid' => 0,
            'departmentid' => 1,
        ];
        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;

        $this->setExpectedException('SWIFT_Exception', 'Access Denied to Ticket: 1');
        $obj::MergeList([1], true);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testMergeListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj::MergeList([], true),
            'Returns true after deleting with staff_trcandeletesubscriber = 1');

        $this->assertFalse($obj::MergeList([], true),
            'Returns false after rendering with staff_trcandeletesubscriber = 0');

        unset($_POST['csrfhash']);
        $this->assertFalse($obj::MergeList([], false),
            'Returns false if csrfhash is not provided');

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetAssignedDepartments')->willReturn([1]);
        $mockStaff->method('GetPermission')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $mockStaff;

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'duetime' => 0,
            'resolutionduedateline' => 0,
            'ticketmaskid' => 0,
            'trasholddepartmentid' => 0,
            'departmentid' => &static::$_prop['departmentid'],
            'ticketstatusid' => 1,
        ];
        static::$_prop['departmentid'] = 1;
        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                return self::$_next = 1;
            }
            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            static::$_next++;

            if (static::$_next === 2) {
                static::$_prop['departmentid'] = 0;
            }

            return in_array(static::$_next, [1, 2], true);
        });
        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryFetch')->willReturn($arr);
        $mockDb->Record = $arr;
        \SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            0 => [1 => [1]],
            1 => [1 => [1]],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertTrue($obj::MergeList([1], true));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testWatchListThrowsException()
    {
        $obj = $this->getMocked();

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetAssignedDepartments')->willReturn([]);
        $mockStaff->method('GetPermission')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $mockStaff;

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'ticketmaskid' => 0,
            'departmentid' => 1,
        ];
        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;

        $this->setExpectedException('SWIFT_Exception', 'Access Denied to Ticket: 1');
        $obj::WatchList([1], true);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testWatchListReturnsTrue()
    {
        $obj = $this->getMocked();

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetAssignedDepartments')->willReturn([1]);
        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls(1, 1, 0, 1, 1);
        \SWIFT::GetInstance()->Staff = $mockStaff;

        $this->assertTrue($obj::WatchList([], true),
            'Returns true after deleting with staff_trcandeletesubscriber = 1');

        $this->assertFalse($obj::WatchList([], true),
            'Returns false after rendering with staff_trcandeletesubscriber = 0');

        unset($_POST['csrfhash']);
        $this->assertFalse($obj::WatchList([], false),
            'Returns false if csrfhash is not provided');

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'duetime' => 0,
            'resolutionduedateline' => 0,
            'ticketmaskid' => 0,
            'trasholddepartmentid' => 0,
            'departmentid' => &static::$_prop['departmentid'],
            'ticketstatusid' => 1,
        ];
        static::$_prop['departmentid'] = 1;
        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                return self::$_next = 1;
            }
            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            static::$_next++;

            if (static::$_next === 2) {
                static::$_prop['departmentid'] = 0;
            }

            return in_array(static::$_next, [1, 2], true);
        });
        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryFetch')->willReturn($arr);
        $mockDb->Record = $arr;
        \SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            0 => [1 => [1]],
            1 => [1 => [1]],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertTrue($obj::WatchList([1], true));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testMassReplyListReturnsTrue()
    {
        $obj = $this->getMocked();

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetProperty')->willReturnCallback(function ($x) {
            if (strtolower(substr($x, -2)) === 'id') {
                return '1';
            }

            return $x;
        });
        $mockStaff->method('GetAssignedDepartments')->willReturnOnConsecutiveCalls([0], [1]);
        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls(1, 1, 0, 1, 1);
        \SWIFT::GetInstance()->Staff = $mockStaff;

        $_POST['replycontents'] = 'replycontents';

        $this->assertTrue($obj::MassReplyList([], true),
            'Returns true after deleting with staff_trcandeletesubscriber = 1');

        $this->assertFalse($obj::MassReplyList([], true),
            'Returns false after rendering with staff_trcandeletesubscriber = 0');

        unset($_POST['csrfhash']);
        $this->assertFalse($obj::MassReplyList([], false),
            'Returns false if csrfhash is not provided');

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'userid' => 0,
            'ticketslaplanid' => 0,
            'slaplanid' => 0,
            'firstresponsetime' => 0,
            'averageresponsetimehits' => 0,
            'totalreplies' => 0,
            'ticketpostid' => 1,
            'duetime' => 0,
            'resolutionduedateline' => 0,
            'ticketmaskid' => 0,
            'subject' => 'subject',
            'emailqueueid' => '0',
            'trasholddepartmentid' => 0,
            'departmentid' => &static::$_prop['departmentid'],
            'ticketstatusid' => 1,
        ];
        static::$_prop['departmentid'] = 1;
        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                return self::$_next = 1;
            }
            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            static::$_next++;

            if (static::$_next === 2) {
                static::$_prop['departmentid'] = 0;
            }

            return in_array(static::$_next, [1, 2], true);
        });
        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryFetch')->willReturn($arr);
        $mockDb->Record = $arr;
        \SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            0 => [1 => [1]],
            1 => [1 => [1]],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertTrue($obj::MassReplyList([1, 1, 0], true));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testMassReplyDialogReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->_MassReplyDialog(),
            'Returns true after deleting with staff_tcanupdateticket = 1');

        $this->assertTrue($obj->_MassReplyDialog(),
            'Returns true after rendering with staff_tcanupdateticket = 0');

        $this->assertClassNotLoaded($obj, '_MassReplyDialog');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testMassActionPanelReturnsTrue()
    {
        $obj = $this->getMocked();

        $_POST['replycontents'] = 'replycontents';

        $this->assertTrue($obj::MassActionPanel([], true),
            'Returns true after deleting with staff_trcandeletesubscriber = 1');

        $this->assertFalse($obj::MassActionPanel([], true),
            'Returns false after rendering with staff_trcandeletesubscriber = 0');

        unset($_POST['csrfhash']);
        $this->assertFalse($obj::MassActionPanel([], false),
            'Returns false if csrfhash is not provided');

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetAssignedDepartments')->willReturnOnConsecutiveCalls([1], [], [1], [1]);
        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls(1, 0, 1, 1);
        \SWIFT::GetInstance()->Staff = $mockStaff;

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $arr = [
            'ticketid' => 1,
            'staffid' => 1,
            'ownerstaffid' => 1,
            'tickettypeid' => 1,
            'priorityid' => 1,
            'lastactivity' => 0,
            'iswatched' => 0,
            'lastpostid' => 0,
            'userid' => 0,
            'ticketslaplanid' => 0,
            'slaplanid' => 0,
            'firstresponsetime' => 0,
            'averageresponsetimehits' => 0,
            'totalreplies' => 0,
            'ticketpostid' => 1,
            'duetime' => 0,
            'resolutionduedateline' => 0,
            'ticketmaskid' => 0,
            'subject' => 'subject',
            'emailqueueid' => '0',
            'trasholddepartmentid' => 0,
            'departmentid' => &static::$_prop['departmentid'],
            'ticketstatusid' => 1,
            'flagtype' => 1,
            'creator' => 1,
            'lastreplier' => 0,
            'charset' => 'utf-8',
            'tgroupid' => 0,
            'wasreopened' => 0,
            'bayescategoryid' => 0,
        ];
        static::$_prop['departmentid'] = 1;
        $mockDb->method('Query')->willReturnCallback(function () {
            if (!isset(static::$_prop['stop'])) {
                self::$_next = 0;
            }
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            static::$_next++;

            if (static::$_next === 2) {
                static::$_prop['departmentid'] = 2;
                static::$_prop['stop'] = 1;
            }

            return in_array(static::$_next, [1, 2, 4], true);
        });
        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryFetch')->willReturn($arr);
        $mockDb->Record = $arr;
        \SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            0 => [1 => [1]],
            1 => [1 => [1]],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $_POST['departmentid'] = 3;
        $this->assertFalse($obj::MassActionPanel([1], true));

        $_POST['departmentid'] = 1;
        $_POST['ticketflagid'] = 1;
        $_POST['ticketlinktypeid'] = 1;
        $_POST['bayescategoryid'] = 1;
        $_POST['ticketpriorityid'] = 1;
        $_POST['ticketstatusid'] = 1;
        $_POST['tickettypeid'] = 1;
        $_POST['staffid'] = 1;
        static::$_prop['departmentid'] = 1;
        $this->assertTrue($obj::MassActionPanel([1], true));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Index('no', true));

        $this->assertTrue($obj->Index());

        $this->assertClassNotLoaded($obj, 'Index');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testFilterReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Filter('no', 'no', 'no', 'no'));

        $this->assertTrue($obj->Filter());

        $this->assertClassNotLoaded($obj, 'Filter');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testMyTicketsReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->MyTickets('no'));

        $this->assertTrue($obj->MyTickets());

        $this->assertClassNotLoaded($obj, 'MyTickets');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUnassignedReturnsTrue()
    {
        $obj = $this->getMocked();

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls(0, 1, 1);
        \SWIFT::GetInstance()->Staff = $mockStaff;

        $this->assertFalse($obj->Unassigned(),
            'Returns false without permission');

        $this->assertTrue($obj->Unassigned('no'));
        $this->assertTrue($obj->Unassigned());

        $this->assertClassNotLoaded($obj, 'Unassigned');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRedirectReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Redirect('mytickets'));

        $this->assertTrue($obj->Redirect('unassigned'));

        $this->assertClassNotLoaded($obj, 'Redirect');
    }

    public function testViewThrowsExceptionWithInvalidId()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'View', 2);
    }

    public function testViewThrowsInvalidDataException()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([]);

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            1 => [
                'ticketviewid' => 1,
                'staffid' => 1,
                'viewscope' => -1,
                'viewalltickets' => 0,
                'viewassigned' => 0,
                'viewunassigned' => 0,
                'afterreplyaction' => 1,
                'fields' => [
                    [
                        'ticketviewfieldid' => 1,
                    ],
                ],
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertInvalidData($obj, 'View', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testViewReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            1 => [
                'ticketviewid' => 1,
                'staffid' => 1,
                'viewscope' => 1,
                'viewalltickets' => 0,
                'viewassigned' => 0,
                'viewunassigned' => 0,
                'afterreplyaction' => 1,
                'fields' => [
                    [
                        'ticketviewfieldid' => 1,
                    ],
                ],
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertTrue($obj->View(1, 'no', 'no', 'no'));

        $this->assertTrue($obj->View(1));

        $this->assertClassNotLoaded($obj, 'View', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Search(1));

        $this->assertClassNotLoaded($obj, 'Search', 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testRenderIsLoaded()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_Render');
        $method->setAccessible(true);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $method->invoke($obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testLoadDisplayDataReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->_LoadDisplayData());

        $this->assertClassNotLoaded($obj, '_LoadDisplayData');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPreviewThrowsException()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketmaskid' => 0,
        ]);

        $this->setExpectedException('SWIFT_Exception', 'Access Denied to Ticket: 1');
        $obj->Preview(1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPreviewReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
        ]);

        $this->assertTrue($obj->Preview(1));

        $this->assertClassNotLoaded($obj, 'Preview', 1);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_ManageMock
     */
    private function getMocked(array $services = [])
    {
        $view = $this->getMockBuilder('Tickets\Staff\View_Manage')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->getMockObject('Tickets\Staff\Controller_ManageMock', array_merge($services, [
            'View' => $view,
        ]));
    }
}

class Controller_ManageMock extends Controller_Manage
{
    protected static $_sendEmail = false;

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

