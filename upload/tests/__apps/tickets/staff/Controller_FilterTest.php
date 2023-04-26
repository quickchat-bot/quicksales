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
 * Class Controller_FilterTest
 * @group tickets
 * @group tickets-staff
 */
class Controller_FilterTest extends \SWIFT_TestCase
{
    public static $_next = 0;

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Staff\Controller_Filter', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj::DeleteList([1]),
            'Returns false without csrfhash');

        $_POST['csrfhash'] = 'csrfhash';
        $this->assertTrue($obj::DeleteList([1]));

        $this->assertFalse($obj::DeleteList([1]),
            'Returns false without permission');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Delete(1));

        $this->assertClassNotLoaded($obj, 'Delete', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testManageReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Manage(),
            'Returns true with staff_tcanviewfilters = 1');

        $this->assertTrue($obj->Manage(),
            'Returns true with staff_tcanviewfilters = 0');

        $this->assertClassNotLoaded($obj, 'Manage');
    }

    /**
     * @throws \ReflectionException
     * @throws SWIFT_Exception
     */
    public function testRunChecksReturnsTrue()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('RunChecks');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false without csrfhash');

        $_POST['csrfhash'] = 'csrfhash';
        $this->assertFalse($method->invoke($obj, 1),
            'Returns false without title');

        $_POST['title'] = 'title';
        $_POST['rulecriteria'] = [1 => ['title', '=', 'title']];
        \SWIFT::Set('isdemo', true);
        $this->assertFalse($method->invoke($obj, 1),
            'Returns false in demo mode');
        \SWIFT::Set('isdemo', false);

        // advance permission
        \SWIFT::GetInstance()->Staff->GetPermission('admin_lscaninsertmacro');
        $this->assertFalse($method->invoke($obj, 2),
            'Returns false without permission');

        \SWIFT::GetInstance()->Staff->method('GetPermission')->willReturn('1');

        $this->assertTrue($method->invoke($obj, 2));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Insert(),
            'Returns true with staff_tcaninsertfilter = 1');

        $this->assertTrue($obj->Insert(),
            'Returns true with staff_tcaninsertfilter = 0');

        $this->assertClassNotLoaded($obj, 'Insert');
    }

    /**
     * @throws \ReflectionException
     */
    public function testRenderConfirmationReturnsTrue()
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
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryFetch')->willReturn([
            'ticketfilterid' => 1,
            'title' => 'title',
        ]);
        $mockDb->Record = [
            'ticketfilterid' => 1,
            'title' => 'title',
            'fieldtitle' => 'type',
            'fieldvalue' => null,
        ];
        $obj = $this->getMocked([
            'Database' => $mockDb,
        ]);
        \SWIFT::GetInstance()->Database = $mockDb;
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_RenderConfirmation');
        $method->setAccessible(true);

        $filter = $this->getMockBuilder('Tickets\Models\Filter\SWIFT_TicketFilter')
            ->disableOriginalConstructor()
            ->getMock();

        $_POST['ticketfilterid'] = 1;
        $this->assertTrue($method->invoke($obj, 1, $filter));

        $_POST['ticketfilterid'] = 0;
        $this->assertTrue($method->invoke($obj, 2, $filter));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1, $filter);
    }

    /**
     * @throws \ReflectionException
     */
    public function testLoadPostVariablesReturnsTrue()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_LoadPOSTVariables');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($obj, 2));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTicketFieldContainerReturnsArray()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('GetTicketFieldContainer');
        $method->setAccessible(true);

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketpostid' => 1,
            'ticketfilterid' => 1,
            'title' => 'title',
        ]);

        $obj->doRunChecks = true;

        $_POST['criteriaoptions'] = 1;
        $_POST['filtertype'] = 1;
        $_POST['restrictstaffgroupid'] = 1;
        $_POST['rulecriteria'] = [1 => ['title', '=', 'title']];
        $_POST['title'] = 'title';

        $this->assertTrue($obj->InsertSubmit());

        $obj->doRunChecks = false;

        $this->assertFalse($obj->InsertSubmit());

        $this->assertClassNotLoaded($obj, 'InsertSubmit');
    }

    public function testEditThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Edit', 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketfilterid' => 1,
        ]);

        $this->assertTrue($obj->Edit(1),
            'Returns true with staff_tcanupdatemacro = 1');

        $this->assertTrue($obj->Edit(1),
            'Returns true with staff_tcanupdatemacro = 0');

        $this->assertClassNotLoaded($obj, 'Edit', 1);
    }

    public function testEditSubmitThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'EditSubmit', 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketfilterid' => 1,
            'title' => 'title',
        ]);

        $_POST['criteriaoptions'] = 1;
        $_POST['filtertype'] = 1;
        $_POST['restrictstaffgroupid'] = 1;
        $_POST['rulecriteria'] = [1 => ['title', '=', 'title']];
        $_POST['title'] = 'title';

        $obj->doRunChecks = true;
        $this->assertTrue($obj->EditSubmit(1));

        $obj->doRunChecks = false;

        $this->assertFalse($obj->EditSubmit(1),
            'Returns false if checks fail');

        $this->assertClassNotLoaded($obj, 'EditSubmit', 1);
    }

    public function testLoadDisplayDataReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertClassNotLoaded($obj, '_LoadDisplayData');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetMenuReturnsTrue()
    {
        $obj = $this->getMocked();

//        $this->expectOutputRegex('/a/');
        $this->assertTrue($obj->GetMenu());
        $this->assertClassNotLoaded($obj, 'GetMenu');
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_FilterMock
     */
    private function getMocked(array $services = [])
    {
        $view = $this->getMockBuilder('Tickets\Staff\View_Filter')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->getMockObject('Tickets\Staff\Controller_FilterMock', array_merge($services, [
            'View' => $view,
        ]));
    }
}

class Controller_FilterMock extends Controller_Filter
{
    public $doRunChecks = -1;
    public $Database;

    protected function RunChecks($_mode, $_ticketFileTypeID = 0)
    {
        return $this->doRunChecks === -1 ? parent::RunChecks($_mode, $_ticketFileTypeID) : $this->doRunChecks;
    }

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

