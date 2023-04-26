<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
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
use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_BreaklineTest
 * @group parser
 * @group parser-admin
 */
class Controller_BreaklineTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Admin\Controller_Breakline', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->DeleteList([]),
            'Returns false');

        $this->assertTrue($obj->DeleteList([], true),
            'Returns true');

        $this->assertFalse($obj->DeleteList([], true),
            'Returns false');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Delete(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Delete', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testManageReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Manage(),
            'Returns true');

        $this->assertTrue($obj->Manage(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Manage');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRunChecksReturnsTrue()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod(Controller_BreaklineMock::class, 'RunChecks');

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $_POST['csrfhash'] = 'csrfhash';

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $_POST['breakline'] = 'br';

        \SWIFT::Set('isdemo', true);

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        \SWIFT::Set('isdemo', false);

        $this->assertTrue($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns true');

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception');
        $method->invoke($obj, SWIFT_UserInterface::MODE_INSERT);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Insert(),
            'Returns true');

        $this->assertTrue($obj->Insert(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Insert');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function test_RenderConfirmationReturnsTrue()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod(Controller_BreaklineMock::class, '_RenderConfirmation');

        $this->assertTrue($method->invoke($obj, SWIFT_UserInterface::MODE_EDIT),
            'Returns true');

        $this->assertTrue($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns true');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception');
        $method->invoke($obj, SWIFT_UserInterface::MODE_EDIT);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['breakline'] = 'br';

        $this->assertTrue($obj->InsertSubmit(),
            'Returns true');

        $this->assertFalse($obj->InsertSubmit(),
            'Returns false');

        $this->assertClassNotLoaded($obj, 'InsertSubmit');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['breaklineid' => 1]);

        $this->assertTrue($obj->Edit(1),
            'Returns true');

        $this->assertTrue($obj->Edit(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Edit', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditThrowsInvalidData()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_INVALIDDATA);
        $obj->Edit('');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['breakline'] = 'br';

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['breaklineid' => 1]);
        \SWIFT::GetInstance()->Database->Record = ['breaklineid' => 1];

        $this->assertTrue($obj->EditSubmit(1),
            'Returns true');

        $this->assertFalse($obj->EditSubmit(1),
            'Returns false');

        $this->assertClassNotLoaded($obj, 'EditSubmit', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditSubmitThrowsInvalidData()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_INVALIDDATA);
        $obj->EditSubmit('');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_BreaklineMock
     */
    private function getMocked()
    {
        $mockView = $this->getMockBuilder(View_Breakline::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockView->method('RenderGrid')->willReturn(true);

        $mockView->method('Render')->willReturn(true);

        return $this->getMockObject('Parser\Admin\Controller_BreaklineMock', ['View' => $mockView]);
    }
}

class Controller_BreaklineMock extends Controller_Breakline
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

