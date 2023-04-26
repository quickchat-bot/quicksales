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

namespace Troubleshooter\Staff;

use SWIFT_Exception;

/**
 * Class StaffController_CategoryTest
 * @group troubleshooter
 */
class StaffController_CategoryTest extends \SWIFT_TestCase
{
    /**
     * @return Controller_Category
     * @throws SWIFT_Exception
     */
    public function getController()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryFetch')->willReturnOnConsecutiveCalls([
            'troubleshootercategoryid' => 1,
        ], false);

        $this->mockProperty($mockDb, 'Record', ['title' => 'title']);

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls('1', '0', '1');
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetProperty')->willReturnArgument(1);

        $mockSession = $this->getMockBuilder('SWIFT_Session')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSession->method('GetIsClassLoaded')->willReturn(true);
        $mockSession->method('GetProperty')->willReturnArgument(0);

        \SWIFT::GetInstance()->Database = $mockDb;
        \SWIFT::GetInstance()->Staff = $mockStaff;
        \SWIFT::GetInstance()->Session = $mockSession;

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceClient')
            ->disableOriginalConstructor()
            ->setMethods(['DisplayError', 'Header', 'Footer', 'Error', 'CheckFields'])
            ->getMock();

        $mockView = $this->getMockBuilder('SWIFT_View')
            ->disableOriginalConstructor()
            ->setMethods(['RenderGrid', 'Render', 'RenderViewAll'])
            ->getMock();

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturn('%s');

        \SWIFT::GetInstance()->Language = $mockLang;

        $obj = new Controller_Category();
        $this->mockProperty($obj, 'Load', new LoaderMock());
        $this->mockProperty($obj, 'Database', $mockDb);
        $this->mockProperty($obj, 'UserInterface', $mockInt);
        $this->mockProperty($obj, 'View', $mockView);
        $this->mockProperty($obj, 'Language', $mockLang);

        return $obj;
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = new Controller_Category();
        $this->assertInstanceOf('Troubleshooter\Staff\Controller_Category', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testLoadDisplayDataDoesNothing()
    {
        $obj = new Controller_Category();
        $this->assertTrue($obj->_LoadDisplayData(),
            'Returns true and does nothing');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->_LoadDisplayData();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Delete(0),
            'Returns true after delete');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Delete(0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testManageReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Manage(),
            'Returns true after rendering with staff_trcanviewcategories = 1');

        $this->assertTrue($obj->Manage(),
            'Returns true after rendering with staff_trcanviewcategories = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Manage();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Insert(),
            'Returns true after rendering with staff_trcaninsertcategory = 1');

        $this->assertTrue($obj->Insert(),
            'Returns true after rendering with staff_trcaninsertcategory = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Insert();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListWorks()
    {
        $obj = $this->getController();

        $this->assertTrue($obj::DeleteList([], true),
            'Returns true after deleting with staff_trcandeletecategory = 1');

        $this->assertFalse($obj::DeleteList([], true),
            'Returns false after rendering with staff_trcandeletecategory = 0');

        $this->assertFalse($obj::DeleteList([], false),
            'Returns false if csrfhash is not provided');
    }

    /**
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testRunChecksReturnsTrue()
    {
        $obj = $this->getController();

        // runchecks is private. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('RunChecks');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($obj, 2),
            'Returns false if csrfhash is not provided');

        $_POST['csrfhash'] = 'csrfhash';
        $this->assertFalse($method->invoke($obj, 2),
            'Returns false if title is not provided');

        $_POST['title'] = 'title';
        \SWIFT::Set('isdemo', true);
        $this->assertFalse($method->invoke($obj, 2),
            'Returns false if demo mode is enabled');

        \SWIFT::Set('isdemo', false);

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls('0', '1');

        \SWIFT::GetInstance()->Staff = $mockStaff;

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false when staff_trcanupdatecategory = 0 in edit mode');

        $this->assertFalse($method->invoke($obj, 2),
            'Returns false with invalid category id');

        $this->assertTrue($method->invoke($obj, 2),
            'Returns true if all checks pass in insert mode');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 2);
    }

    /**
     * @throws \ReflectionException
     * @throws SWIFT_Exception
     */
    public function testRenderConfirmationReturnsTrue()
    {
        $obj = $this->getController();

        // _RenderConfirmation is private. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('_RenderConfirmation');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($obj, 1),
            'Returns true without categorytype');

        $_POST['categorytype'] = 1;
        $this->assertTrue($method->invoke($obj, 1),
            'Returns true with categorytype = global');

        $_POST['categorytype'] = 2;
        $this->assertTrue($method->invoke($obj, 1),
            'Returns true with categorytype = public');

        $_POST['categorytype'] = 3;
        $this->assertTrue($method->invoke($obj, 1),
            'Returns true with categorytype = private');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testInsertSubmitReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->InsertSubmit(),
            'Returns false if RunChecks fails');

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('QueryFetch')->willReturn(['troubleshootercategoryid' => 0]);
        $mockDb->method('Insert_ID')->willReturn(1);

        $this->mockProperty($obj, 'Database', $mockDb);

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['title'] = 'title';
        $_POST['categorytype'] = 1;
        $this->assertTrue($obj->InsertSubmit(),
            'Returns true if RunChecks passes');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->InsertSubmit();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testEditThrowsExceptionWithInvalidId()
    {
        $obj = $this->getController();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->Edit(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testEditReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Edit(1),
            'Returns true after rendering with staff_trcanupdatecategory = 1');

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('QueryFetch')->willReturn(['troubleshootercategoryid' => 1]);

        $this->mockProperty($obj, 'Database', $mockDb);

        $this->assertTrue($obj->Edit(1),
            'Returns true after rendering with staff_trcanupdatecategory = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Edit(1);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testEditSubmitThrowsExceptionWithInvalidId()
    {
        $obj = $this->getController();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->EditSubmit(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testEditSubmitReturnsTrue()
    {
        $obj = $this->getController();

        unset($_POST['title']);
        $this->assertFalse($obj->EditSubmit(1),
            'Returns false if RunChecks fails');

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryFetch')->willReturn(['troubleshootercategoryid' => 1]);

        $this->mockProperty($obj, 'Database', $mockDb);

        $cache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $cache->method('Get')->willReturn([1 => [1 => 1]]);
        \SWIFT::GetInstance()->Cache = $cache;

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['title'] = 'title';
        $_POST['staffgroupidlist'] = [1 => 1];
        $_POST['usergroupidlist'] = [1 => 1];
        $_POST['description'] = 'description';
        $_POST['categorytype'] = 1;
        $_POST['displayorder'] = 1;
        $_POST['uservisibilitycustom'] = 1;
        $_POST['staffvisibilitycustom'] = 1;
        $this->assertTrue($obj->EditSubmit(1),
            'Returns true if RunChecks passes');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->EditSubmit(1);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewAllReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->ViewAll(),
            'Returns true after rendering with staff_trcanviewsteps = 1');

        $this->assertTrue($obj->ViewAll(),
            'Returns true after rendering with staff_trcanviewsteps = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->ViewAll();
    }
}

/**
 * Class LoaderMock
 * @package Troubleshooter\Staff
 */
class LoaderMock
{
    public $Load;

    public function __construct()
    {
        $this->Load = $this;
    }

    /**
     * @return string
     */
    public function Manage()
    {
        return 'manage';
    }

    public function Insert()
    {
        // do nothing
    }

    public function Edit()
    {
        // do nothing
    }

    public function Library()
    {
        // do nothing
    }

    public function NewTicketForm()
    {
        // do nothing
    }

    public function NewTicket()
    {
        // do nothing
    }

    /**
     * @return LoaderMock
     */
    public function Controller()
    {
        return new self;
    }
}
