<?php
/**
 * ###############################################
 *
 * Kayako Classic
 * _______________________________________________
 *
 * @author        Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Parser\Admin;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Knowledgebase\Admin\LoaderMock;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use SWIFT_Exception;

/**
 * Class View_EmailQueueTest
 * @group parser
 * @group parser-admin
 */
class View_EmailQueueTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Admin\View_EmailQueue', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $obj = $this->getMocked();

        $emailQueueMock = $this->getMockBuilder(SWIFT_EmailQueue::class)
            ->disableOriginalConstructor()
            ->getMock();

        $emailQueueMock->method('GetEmailQueueID')->willReturn(1);

        $props = [
            'type' => APP_TICKETS
        ];

        $emailQueueMock->method('GetProperty')->willReturnCallback(function ($x) use (&$props) {
            return $props[$x] ?? $x;
        });

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'departmentcache')
                return [
                    1 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ]
                ];

            if ($x == 'staffcache')
                return [1 => []];

            if ($x == 'tickettypecache')
                return [1 => ['departmentid' => '0']];

            if ($x == 'statuscache')
                return [1 => ['departmentid' => '0']];

            if ($x == 'prioritycache')
                return [1 => []];
        };

        $this->expectOutputRegex('/.*/');

        $this->assertTrue($obj->Render(SWIFT_UserInterface::MODE_EDIT, $emailQueueMock),
            'Returns true');

        $this->assertTrue($obj->Render(SWIFT_UserInterface::MODE_INSERT, $emailQueueMock),
            'Returns true');

        $_POST['type'] = APP_TICKETS;
        $_POST['host'] = 'mail.test.com';
        $_POST['port'] = '465';
        $_POST['username'] = 'testuser';
        $_POST['userpassword'] = 'testpass';
        $_POST['forcequeue'] = 1;
        $_POST['leavecopyonserver'] = 1;
        $_POST['usequeuesmtp'] = 1;
        $_POST['smtptype'] = 'tls';

        $this->assertTrue($obj->Render(SWIFT_UserInterface::MODE_INSERT, $emailQueueMock, 2),
            'Returns true');

        $obj->SetIsClassLoaded(false);

        $this->assertFalse($obj->Render(SWIFT_UserInterface::MODE_INSERT),
            'Returns false');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderGridReturnsTrue()
    {
        $obj = $this->getMocked();

        $obj->UserInterfaceGrid->method('GetMode')->willReturn(SWIFT_UserInterfaceGrid::MODE_SEARCH);

        $this->assertTrue($obj->RenderGrid(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'RenderGrid');
    }

    public function testBuildSqlSearchKeepsSpecialChars()
    {
        \SWIFT::GetInstance()->Database->method('Escape')->willReturnArgument(0);
        $obj = new SWIFT_UserInterfaceGrid('test');
        $obj->SetMode(SWIFT_UserInterfaceGrid::MODE_SEARCH);
        $addr = 'kayako.outlook+queue@gmail.com';
        $obj->SetSearchQueryString($addr);
        $this->assertContains($addr, $obj->BuildSQLSearch('email', true));
        $this->assertNotContains($addr, $obj->BuildSQLSearch('email', false));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGridRenderReturnsArray()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'departmentcache')
                return [1 => []];
        };

        $this->assertTrue(is_array($obj->GridRender(['isenabled' => '0'])),
            'Returns array');

        $this->assertTrue(is_array($obj->GridRender(['isenabled' => '0', 'departmentid' => 1])),
            'Returns array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderVerifyConnectionReturnsTrue()
    {
        $obj = $this->getMocked();

        $connection = [
            ['test'],
            ['test', 'test'],
        ];

        $this->assertTrue($obj->RenderVerifyConnection($connection),
            'Returns true');

        $obj->SetIsClassLoaded(false);

        $this->assertFalse($obj->RenderVerifyConnection([]),
            'Returns false');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|View_EmailQueueMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Admin\View_EmailQueueMock');
    }
}

class View_EmailQueueMock extends View_EmailQueue
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

