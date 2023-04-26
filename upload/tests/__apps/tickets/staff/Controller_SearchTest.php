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
 * Class Controller_SearchTest
 * @group tickets
 * @group tickets-staff
 * @group tickets-search
 */
class Controller_SearchTest extends \SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Staff\Controller_Search', $obj);
    }

    public function testLoadDisplayDataReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertClassNotLoaded($obj, '_LoadDisplayData');
    }

    /**
     * @throws \ReflectionException
     */
    public function testLoadSearchReturnsTrue()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('LoadSearch');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($obj, []));
        $this->assertTrue($method->invoke($obj, [1]));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, []);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testNewTicketsReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->NewTickets(),
            'Returns true with staff_tcansearch = 0');

        $this->assertTrue($obj->NewTickets(),
            'Returns true with staff_tcansearch = 1');

        $this->assertClassNotLoaded($obj, 'NewTickets');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUnresolvedOwnerReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->UnresolvedOwner(1),
            'Returns true with staff_tcansearch = 0');

        $this->assertTrue($obj->UnresolvedOwner(1),
            'Returns true with staff_tcansearch = 1');

        $this->assertClassNotLoaded($obj, 'UnresolvedOwner', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUnresolvedStatusReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->UnresolvedStatus(1),
            'Returns true with staff_tcansearch = 0');

        $this->assertTrue($obj->UnresolvedStatus(1),
            'Returns true with staff_tcansearch = 1');

        $this->assertClassNotLoaded($obj, 'UnresolvedStatus', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUnresolvedTypeReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->UnresolvedType(1),
            'Returns true with staff_tcansearch = 0');

        $this->assertTrue($obj->UnresolvedType(1),
            'Returns true with staff_tcansearch = 1');

        $this->assertClassNotLoaded($obj, 'UnresolvedType', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUnresolvedPriorityReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->UnresolvedPriority(1),
            'Returns true with staff_tcansearch = 0');

        $this->assertTrue($obj->UnresolvedPriority(1),
            'Returns true with staff_tcansearch = 1');

        $this->assertClassNotLoaded($obj, 'UnresolvedPriority', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testOverdueReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Overdue(),
            'Returns true with staff_tcansearch = 0');

        $this->assertTrue($obj->Overdue(),
            'Returns true with staff_tcansearch = 1');

        $this->assertClassNotLoaded($obj, 'Overdue');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testTicketIdReturnsTrue()
    {
        $obj = $this->getMocked();

        $_POST['query'] = 'query';

        $this->assertTrue($obj->TicketID(),
            'Returns true with staff_tcansearch = 0');

        $this->assertTrue($obj->TicketID(),
            'Returns true with staff_tcansearch = 1');

        $this->assertClassNotLoaded($obj, 'TicketID');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreatorReturnsTrue()
    {
        $obj = $this->getMocked();

        $_POST['query'] = 'query';

        $this->assertTrue($obj->Creator(),
            'Returns true with staff_tcansearch = 0');

        $this->assertTrue($obj->Creator(),
            'Returns true with staff_tcansearch = 1');

        $this->assertClassNotLoaded($obj, 'Creator');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testQuickSearchReturnsTrue()
    {
        $obj = $this->getMocked();

        $_POST['query'] = ' ';

        $this->assertTrue($obj->QuickSearch(),
            'Returns true with staff_tcansearch = 0');

        $this->assertTrue($obj->QuickSearch(),
            'Returns true with staff_tcansearch = 1');

        $this->assertClassNotLoaded($obj, 'QuickSearch');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testAdvancedReturnsTrue()
    {
        $obj = $this->getMocked();

        $_POST['query'] = 'query';

        $this->assertTrue($obj->Advanced(),
            'Returns true with staff_tcansearch = 0');

        $this->assertTrue($obj->Advanced(),
            'Returns true with staff_tcansearch = 1');

        $this->assertClassNotLoaded($obj, 'Advanced');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        $_POST['query'] = 'query';

        $this->assertTrue($obj->SearchSubmit(),
            'Returns true with staff_tcansearch = 0');

        $this->assertTrue($obj->SearchSubmit(),
            'Returns true with staff_tcansearch = 1');

        $this->assertClassNotLoaded($obj, 'SearchSubmit');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testFilterReturnsTrue()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false, true, false, true, false);
        $mockDb->method('QueryFetch')->willReturn([
            'ticketfilterid' => 1,
            'criteriaoptions' => 1,
            'title' => 'title',
        ]);

        $obj = $this->getMocked([
            'Database' => $mockDb,
        ]);

        \SWIFT::GetInstance()->Database = $mockDb;

        $this->assertTrue($obj->Filter(1));

        $this->assertClassNotLoaded($obj, 'Filter', 1);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_SearchMock
     */
    private function getMocked(array $services = [])
    {
        $view = $this->getMockBuilder('Tickets\Staff\View_Search')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetAssignedDepartments')->willReturn([1]);
        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls('0', '1', '0', '0', '0', '0');

        $mockObject = $this->getMockObject('Tickets\Staff\Controller_SearchMock', array_merge($services, [
            'View' => $view,
            'Staff' => $mockStaff,
        ]));

        \SWIFT::GetInstance()->Staff = $mockStaff;

        return $mockObject;
    }
}

class Controller_SearchMock extends Controller_Search
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

