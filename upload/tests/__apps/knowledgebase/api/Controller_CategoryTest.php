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

namespace Knowledgebase\Api;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
 * Class Controller_CategoryTest
 * @group knowledgebase
 */
class Controller_CategoryTest extends \SWIFT_TestCase
{
    public function setUp()
    {
        parent::setUp();

        // reset test data
        unset($_POST);
    }

    /**
     * @param array $services
     * @return Controller_CategoryMock
     * @throws \SWIFT_Exception
     */
    protected function getController(array $services = [])
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('AutoExecute')->willReturn(1);

        $mockDb->method('NextRecord')
            ->willReturnOnConsecutiveCalls(true, false, true, false, true, false);

        $mockDb->method('QueryFetch')->willReturn([
            'parentkbcategoryid' => 1,
            'kbcategoryid' => 1,
            'staffid' => '1',
            'fullname' => 'fullname',
            'articlesortorder' => '1',
            'allowcomments' => '1',
            'allowrating' => '1',
            'ispublished' => '1',
            'title' => 'title',
            'categorytype' => '1',
            'displayorder' => '0',
            'uservisibilitycustom' => '1',
            'staffvisibilitycustom' => '1',
        ]);

        $this->mockProperty($mockDb, 'Record', [
            'kbcategoryid' => 1,
            'title' => 'title',
        ]);

        SWIFT::GetInstance()->Database = $mockDb;

        $settings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('Get')->willReturn('1');

        $mgr = $this->getMockBuilder('SWIFT_RESTManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mgr->method('Authenticate')->willReturn(true);

        $svr = $this->getMockBuilder('SWIFT_RESTServer')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['DispatchStatus', 'GetVariableContainer', 'Get'])
            ->getMock();

        $svr->method('GetVariableContainer')->willReturn(['salt' => 'salt']);
        $svr->method('Get')->willReturnArgument(0);

        $lang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $lang->method('Get')->willReturnArgument(0);

        $mockXml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockCache->method('Get')->willReturn([1 => [1]]);

        SWIFT::GetInstance()->Cache = $mockCache;

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockStaff->method('GetProperty')->willReturn(1);

        SWIFT::GetInstance()->Staff = $mockStaff;

        $services = array_merge($services, [
            'XML' => $mockXml,
            'Cache' => $mockCache,
        ]);

        return new Controller_CategoryMock($settings, $mgr, $svr, $lang, $mockDb, $services);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Knowledgebase\Api\Controller_Category', $obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->GetList(),
            'Returns true after rendering XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetList();
    }

    /**
     * @throws \SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testProcessTroubleshooterCategoriesReturnsTrue()
    {
        $obj = $this->getController();

        $ref = new \ReflectionClass($obj);
        $method = $ref->getMethod('ProcessKbCategories');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($obj, 1),
            'Returns true after rendering XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Get(1),
            'Returns true after rendering XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Get(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testPostReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->Post(),
            'Returns false without title');

        $_POST['title'] = 'title';
        $this->assertFalse($obj->Post(),
            'Returns false without categorytype');

        $_POST['categorytype'] = '1';
        $_POST['staffid'] = 1;
        $_POST['displayorder'] = 1;
        $_POST['description'] = 'description';
        $_POST['uservisibilitycustom'] = '1';
        $_POST['usergroupidlist'] = '1';
        $_POST['staffvisibilitycustom'] = '1';
        $_POST['staffgroupidlist'] = '1';
        $_POST['parentcategoryid'] = '1';
        $_POST['articlesortorder'] = '1';
        $_POST['allowcomments'] = '1';
        $_POST['allowrating'] = '1';
        $_POST['ispublished'] = '1';

        $this->assertTrue($obj->Post(),
            'Returns true after rendering XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Post();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testPutReturnsTrue()
    {
        $obj = $this->getController();

        $_POST['categorytype'] = '1';
        $_POST['staffid'] = 1;
        $_POST['displayorder'] = 1;
        $_POST['description'] = 'description';
        $_POST['uservisibilitycustom'] = '1';
        $_POST['usergroupidlist'] = '1';
        $_POST['staffvisibilitycustom'] = '1';
        $_POST['staffgroupidlist'] = '1';
        $_POST['parentkbcategoryid'] = '1';
        $_POST['articlesortorder'] = '1';
        $_POST['allowcomments'] = '1';
        $_POST['allowrating'] = '1';
        $_POST['ispublished'] = '1';

        $this->assertFalse($obj->Put(0),
            'Returns false with invalid ID');

        $_POST['title'] = '';
        $this->assertFalse($obj->Put(1),
            'Returns false with empty title');

        $_POST['title'] = 'title';
        $this->assertTrue($obj->Put(1),
            'Returns true after rendering XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Put(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->Delete(0),
            'Returns false with invalid id');

        $this->assertTrue($obj->Delete(1),
            'Returns true after deleting');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Delete(0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIsValidSortFieldReturnsBool()
    {
        $obj = $this->getController();
        $this->assertTrue($obj::IsValidSortField('title'));
    }
}

/**
 * Class Controller_CommentMock
 */
class Controller_CategoryMock extends Controller_Category
{
    public $RESTManager;
    public $RESTServer;

    public function __construct($settings, $mgr, $svr, $lang, $db, $services = [])
    {
        $this->Load = new LoaderMock();
        $this->Settings = $settings;
        $this->RESTManager = $mgr;
        $this->RESTServer = $svr;
        $this->Language = $lang;
        $this->Database = $db;
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
