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

namespace Tickets\Admin;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_FileTypeTest
 * @group tickets
 */
class Controller_FileTypeTest extends \SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Tickets\Admin\Controller_FileType', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListWorks()
    {
        $obj = $this->getController();

        $this->assertTrue($obj::DeleteList([1], true),
            'Returns true after deleting with admin_tcandeletefiletypes = 1');

        $this->assertFalse($obj::DeleteList([], true),
            'Returns false after rendering with admin_tcandeletefiletypes = 0');

        $this->assertFalse($obj::DeleteList([], false),
            'Returns false if csrfhash is not provided');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Delete(1));

        $this->assertClassNotLoaded($obj, 'Delete', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testManageReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Manage(),
            'Returns true with admin_tcanviewfiletypes = 1');

        $this->assertTrue($obj->Manage(),
            'Returns true with admin_tcanviewfiletypes = 0');

        $this->assertClassNotLoaded($obj, 'Manage');
    }

    /**
     * @throws \ReflectionException
     */
    public function testRenderConfirmationReturnsTrue()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_RenderConfirmation');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($obj, 1, 1),
            'Returns true in insert mode');

        $this->assertTrue($method->invoke($obj, 2, 1),
            'Returns true in edit mode');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1, 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testRunChecksReturnsTrue()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('RunChecks');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false without csrfhash');

        $_POST['csrfhash'] = 'csrfhash';

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false with empty POST');

        $_POST['extension'] = 'txt';
        $_POST['maxsize'] = 1;

        \SWIFT::Set('isdemo', true);

        $this->assertFalse($method->invoke($obj, 2),
            'Returns false in demo mode');

        \SWIFT::Set('isdemo', false);

        $this->assertTrue($method->invoke($obj, 1),
            'Returns true with admin_tcaninsertfiletype = 1');

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false with admin_tcaninsertfiletype = 0');

        \SWIFT::GetInstance()->Staff->method('GetPermission')->willReturn(1);
        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketfiletypeid' => 1,
        ]);
        $this->assertFalse($method->invoke($obj, 1),
            'Returns false with invalid ticketfiletypeid');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Insert(),
            'Returns true with admin_tcaninsertfiletype = 1');

        $this->assertTrue($obj->Insert(),
            'Returns true with admin_tcaninsertfiletype = 0');

        $this->assertClassNotLoaded($obj, 'Insert');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertSubmitReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->InsertSubmit());

        $obj->_passChecks = true;
        $_POST['extension'] = 'txt';
        $_POST['maxsize'] = 1;
        $_POST['acceptsupportcenter'] = 0;
        $_POST['acceptmailparser'] = 0;
        $this->assertTrue($obj->InsertSubmit());

        $this->assertClassNotLoaded($obj, 'InsertSubmit');
    }

    public function testEditThrowsException()
    {
        $obj = $this->getController();
        $this->assertInvalidData($obj, 'Edit', 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditReturnsTrue()
    {
        $obj = $this->getController();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketfiletypeid' => 1,
        ]);

        $this->assertTrue($obj->Edit(1),
            'Returns true with admin_tcaninsertbayescategory = 1');

        $this->assertTrue($obj->Edit(1),
            'Returns true with admin_tcaninsertbayescategory = 0');

        $this->assertClassNotLoaded($obj, 'Edit', 1);
    }

    public function testEditSubmitThrowsException()
    {
        $obj = $this->getController();
        $this->assertInvalidData($obj, 'EditSubmit', 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditSubmitReturnsTrue()
    {
        $obj = $this->getController();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketfiletypeid' => 1,
        ]);

        $this->assertFalse($obj->EditSubmit(1));

        $obj->_passChecks = true;
        $_POST['extension'] = 'txt';
        $_POST['maxsize'] = 1;
        $_POST['acceptsupportcenter'] = 0;
        $_POST['acceptmailparser'] = 0;
        $this->assertTrue($obj->EditSubmit(1));

        $this->assertClassNotLoaded($obj, 'EditSubmit', 1);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_FileTypeMock
     */
    private function getController()
    {
        $view = $this->getMockBuilder('Tickets\Admin\View_FileType')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->getMockObject('Tickets\Admin\Controller_FileTypeMock', [
            'View' => $view,
        ]);
    }
}

class Controller_FileTypeMock extends Controller_FileType
{
    public $_passChecks = false;

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

    protected function RunChecks($_mode, $_ticketFileTypeID = 0)
    {
        if ($this->_passChecks) {
            return true;
        }

        return parent::RunChecks($_mode, $_ticketFileTypeID);
    }
}

