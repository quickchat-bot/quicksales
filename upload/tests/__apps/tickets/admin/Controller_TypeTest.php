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

namespace {

    // This allow us to configure the behavior of the "global mock"
    global $mockIsUploadedFile;
    $mockIsUploadedFile = false;
}

namespace Tickets\Admin {

    use Knowledgebase\Admin\LoaderMock;
    use SWIFT_Exception;

    function is_uploaded_file($f)
    {
        global $mockIsUploadedFile;
        if ($mockIsUploadedFile === true) {
            return true;
        }

        return call_user_func_array('\is_uploaded_file', func_get_args());
    }

    /**
     * Class Controller_TypeTest
     * @group tickets
     */
    class Controller_TypeTest extends \SWIFT_TestCase
    {
        public function setUp()
        {
            parent::setUp();

            global $mockIsUploadedFile;
            $mockIsUploadedFile = true;
        }

        public function testConstructorReturnsClassInstance()
        {
            $obj = $this->getController();
            $this->assertInstanceOf('Tickets\Admin\Controller_Type', $obj);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testSortListWorks()
        {
            $obj = $this->getController();

            $this->assertFalse($obj::SortList([]),
                'Returns false without csrfhash');

            $_POST['csrfhash'] = 'csrfhash';

            $this->assertTrue($obj::SortList([1]),
                'Returns true with admin_tcanupdatetype = 1');

            $this->assertFalse($obj::SortList([]),
                'Returns false with admin_tcanupdatetype = 0');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testDeleteListWorks()
        {
            $obj = $this->getController();

            \SWIFT::GetInstance()->Database->Record = [
                'ismaster' => 0,
                'tickettypeid' => 1,
                'title' => 1,
            ];

            $this->assertTrue($obj::DeleteList([1], true),
                'Returns true after deleting with admin_tcandeletetypes = 1');

            $this->assertFalse($obj::DeleteList([], true),
                'Returns false after rendering with admin_tcandeletetypes = 0');

            $db = $this->getMockBuilder('SWIFT_Database')
                ->disableOriginalConstructor()
                ->getMock();
            $db->Record = [
                'ismaster' => 1,
                'tickettypeid' => 1,
                'title' => 1,
            ];
            $db->method('NextRecord')
                ->willReturnOnConsecutiveCalls(true, false);

            \SWIFT::GetInstance()->Database = $db;

            \SWIFT::GetInstance()->Staff->method('GetPermission')->willReturn(1);

            $this->assertTrue($obj::DeleteList([1], true),
                'Returns true if ismaster = 1');

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
                'Returns true with admin_tcanviewtypes = 1');

            $this->assertTrue($obj->Manage(),
                'Returns true with admin_tcanviewtypes = 0');

            $this->assertClassNotLoaded($obj, 'Manage');
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

            $_POST['title'] = 'title';
            $_POST['displayorder'] = 1;

            \SWIFT::Set('isdemo', true);

            $this->assertFalse($method->invoke($obj, 2),
                'Returns false in demo mode');

            \SWIFT::Set('isdemo', false);

            $_FILES['file_displayicon']['tmp_name'] = __DIR__ . '/test';
            $this->assertFalse($method->invoke($obj, 1),
                'Returns false with invalid file');

            $this->assertFalse($method->invoke($obj, 1),
                'Returns false with admin_tcaninserttype = 0');

            \SWIFT::GetInstance()->Staff->method('GetPermission')->willReturn(1);
            $_FILES['file_displayicon']['tmp_name'] = __DIR__ . '/test.gif';
            $_FILES['file_displayicon']['name'] = 'test.gif';

            $this->assertTrue($method->invoke($obj, 1),
                'Returns true with admin_tcaninserttype = 1 and valid file');

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $method->invoke($obj, 1);
        }

        /**
         * @throws \ReflectionException
         */
        public function testGetAssignedUserGroupIdListThrowsException()
        {
            $obj = $this->getController();
            $class = new \ReflectionClass($obj);
            $method = $class->getMethod('_GetAssignedUserGroupIDList');
            $method->setAccessible(true);
            $obj->SetIsClassLoaded(false);
            $this->assertEmpty($method->invoke($obj));
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testInsertReturnsTrue()
        {
            $obj = $this->getController();

            $_POST['usergroupidlist'] = [1 => 1];

            $this->assertTrue($obj->Insert(),
                'Returns true with admin_tcaninserttype = 1');

            $this->assertTrue($obj->Insert(),
                'Returns true with admin_tcaninserttype = 0');

            $this->assertClassNotLoaded($obj, 'Insert');
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

            $_POST['type'] = 'title';
            $_POST['displayorder'] = 1;
            $_POST['displayicon'] = 1;

            $this->assertTrue($method->invoke($obj, 1),
                'Returns true in insert mode');

            $_POST['departmentid'] = '1';

            $this->assertTrue($method->invoke($obj, 2),
                'Returns true in edit mode');

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $method->invoke($obj, 1);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testInsertSubmitReturnsTrue()
        {
            $obj = $this->getController();

            $this->assertFalse($obj->InsertSubmit());

            $obj->_passChecks = true;
            $_POST['title'] = 'title';
            $_POST['type'] = '1';
            $_POST['departmentid'] = 'title';
            $_POST['uservisibilitycustom'] = 'title';
            $_POST['displayorder'] = 1;
            $_POST['displayicon'] = 1;
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
                'tickettypeid' => 1,
            ]);

            $this->assertTrue($obj->Edit(1),
                'Returns true with admin_tcanupdatetype = 1');

            $this->assertTrue($obj->Edit(1),
                'Returns true with admin_tcanupdatetype = 0');

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
                'tickettypeid' => 1,
            ]);

            $this->assertFalse($obj->EditSubmit(1));

            $obj->_passChecks = true;
            $_POST['title'] = 'title';
            $_POST['type'] = '1';
            $_POST['departmentid'] = 'title';
            $_POST['uservisibilitycustom'] = 'title';
            $_POST['displayorder'] = 1;
            $_POST['displayicon'] = 1;
            $_POST['usergroupidlist'] = [1 => 1];
            $this->assertTrue($obj->EditSubmit(1));

            $this->assertClassNotLoaded($obj, 'EditSubmit', 1);
        }

        /**
         * @return \PHPUnit_Framework_MockObject_MockObject|Controller_TypeMock
         */
        private function getController()
        {
            $view = $this->getMockBuilder('Tickets\Admin\View_Type')
                ->disableOriginalConstructor()
                ->getMock();

            $lpl = $this->getMockBuilder('Base\Library\Language\SWIFT_LanguagePhraseLinked')
                ->disableOriginalConstructor()
                ->getMock();

            return $this->getMockObject('Tickets\Admin\Controller_TypeMock', [
                'View' => $view,
                'LanguagePhraseLinked' => $lpl,
            ]);
        }
    }

    class Controller_TypeMock extends Controller_Type
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
}
