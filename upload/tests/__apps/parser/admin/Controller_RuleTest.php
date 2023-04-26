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
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Parser\Admin;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Knowledgebase\Admin\LoaderMock;
use Parser\Models\Rule\SWIFT_ParserRule;
use SWIFT_Exception;

/**
 * Class Controller_RuleTest
 * @group parser
 * @group parser-admin
 */
class Controller_RuleTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Admin\Controller_Rule', $obj);
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
    public function testEnableListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->EnableList([]),
            'Returns false');

        $this->assertTrue($obj->EnableList([1], true),
            'Returns true');

        $this->assertFalse($obj->EnableList([], true),
            'Returns false');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDisableListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->DisableList([]),
            'Returns false');

        $this->assertTrue($obj->DisableList([1], true),
            'Returns true');

        $this->assertFalse($obj->DisableList([], true),
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

        $_SWIFT = \SWIFT::GetInstance();

        $method = $this->getMethod(Controller_RuleMock::class, 'RunChecks');

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $_POST['csrfhash'] = 'csrfhash';
        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $_POST['title'] = 'test';
        $_POST['rulecriteria'] = [1];

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $_POST['ruletype'] = 1;
        $_POST['replycontents'] = 'contents';
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
    public function test_GetCriteriaContainerReturnsArray()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod(Controller_RuleMock::class, '_GetCriteriaContainer');

        $_POST['rulecriteria'] = 'dummy';

        $_POST['rulecriteria'] = 'dummy';

        $this->assertTrue(is_array($method->invoke($obj)),
            'Returns array');

        $_POST['rulecriteria'] = [['dummy']];

        $this->assertTrue(is_array($method->invoke($obj)),
            'Returns array');

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $method->invoke($obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function test_GetCriteriaContainerThrowsInvalid()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod(Controller_RuleMock::class, '_GetCriteriaContainer');

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_INVALIDDATA);
        $method->invoke($obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function test_RenderConfirmationReturnsTrue()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod(Controller_RuleMock::class, '_RenderConfirmation');

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;
        static::$nextRecordCount = 0;
        static::$nextRecordLimit = 10;

        \SWIFT::GetInstance()->Database->Record = [
            'name' => 'ticketstatus',
            'title' => 'status',
            'ticketstatusid' => '1',
            'rulematch' => '1'
        ];

        $this->assertTrue($method->invokeArgs($obj, [SWIFT_UserInterface::MODE_EDIT, 1]),
            'Returns true');

        $this->assertTrue($method->invokeArgs($obj, [SWIFT_UserInterface::MODE_INSERT, 1]),
            'Returns true');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception');
        $method->invokeArgs($obj, [SWIFT_UserInterface::MODE_EDIT, 1]);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['title'] = 'test';
        $_POST['isenabled'] = '1';
        $_POST['sortorder'] = '1';
        $_POST['rulecriteria'] = [[1, 2, 3]];
        $_POST['ruletype'] = 1;
        $_POST['ruleoptions'] = 1;
        $_POST['stopprocessing'] = '1';
        $_POST['replycontents'] = 'contents';

        \SWIFT::GetInstance()->Database->method('QueryFetch')
            ->willReturn([
                'parserruleid' => 1,
                '_criteria' => 'criteria',
                'matchtype' => 1,
            ]);

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

        \SWIFT::GetInstance()->Database->method('QueryFetch')
            ->willReturn([
                'parserruleid' => 1,
                '_criteria' => 'criteria',
                'matchtype' => 1,
            ]);

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
    public function testEditThrowsInvalidData2()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_INVALIDDATA);
        $obj->Edit(1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['title'] = 'test';
        $_POST['isenabled'] = '1';
        $_POST['sortorder'] = '1';
        $_POST['rulecriteria'] = [[1, 2, 3]];
        $_POST['ruletype'] = 1;
        $_POST['ruleoptions'] = 1;
        $_POST['stopprocessing'] = '1';
        $_POST['replycontents'] = 'contents';

        \SWIFT::GetInstance()->Database->method('QueryFetch')
            ->willReturn([
                'parserruleid' => 1,
                '_criteria' => 'criteria',
                'matchtype' => 1,
            ]);

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
     * @throws SWIFT_Exception
     */
    public function test_LoadPOSTVariablesReturnsTrue()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod(Controller_RuleMock::class, '_LoadPOSTVariables');

        $parserRuleMock = $this->getMockBuilder(SWIFT_ParserRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $parserRuleMock->method('GetParserRuleID')->willReturn(1);
        $parserRuleMock->method('GetProperty')->willReturn('dummy');

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;
        static::$nextRecordCount = 0;
        static::$nextRecordLimit = 10;

        $this->assertTrue($method->invoke($obj, $parserRuleMock),
            'Returns true');

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $method->invoke($obj, $parserRuleMock);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function test_GetActionsContainerReturnsArray()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod(Controller_RuleMock::class, '_GetActionsContainer');

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;
        static::$nextRecordCount = 0;
        static::$nextRecordLimit = 10;

        $_POST['ruletype'] = 1;
        $_POST['replycontents'] = 'replycontents';
        $_POST['forwardemail'] = 'forwardemail';
        $_POST['ignore'] = '1';
        $_POST['noautoresponder'] = '1';
        $_POST['noalertrules'] = '1';
        $_POST['noticket'] = '1';

        $_POST['departmentid'] = '1';
        $_POST['ticketstatusid'] = '1';
        $_POST['tickettypeid'] = '1';
        $_POST['ticketpriorityid'] = '1';
        $_POST['staffid'] = '1';
        $_POST['flagtype'] = '1';
        $_POST['slaplanid'] = '1';
        $_POST['notes'] = '1';
        $_POST['movetotrash'] = '1';
        $_POST['private'] = '1';
        $_POST['taginput_addtags'] = 'tag1';
        $_POST['container_addtags'] = ['tag1', 'tag2'];
        $_POST['taginput_removetags'] = 'tag1';
        $_POST['container_removetags'] = ['tag1', 'tag2'];

        $this->assertTrue(is_array($method->invoke($obj)),
            'Returns array');

        $_POST['ruletype'] = 2;

        $this->assertTrue(is_array($method->invoke($obj)),
            'Returns array');

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $method->invoke($obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function test_ProcessActionContainerToPOSTReturnsTrue()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod(Controller_RuleMock::class, '_ProcessActionContainerToPOST');

        $actions = [
            ['name' => 'reply', 'typedata' => 'dummy'],
            ['name' => 'forward', 'typechar' => 'dummy'],
            ['name' => 'ignore'],
            ['name' => 'department', 'typeid' => '1'],
            ['name' => 'flagticket', 'typeid' => '1'],
            ['name' => 'addtags', 'typedata' => 'tag'],
        ];

        $this->assertTrue($method->invoke($obj, $actions),
            'Returns true');

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $method->invoke($obj, $actions);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function test_ResetPOSTVariablesClassNotLoaded()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod(Controller_RuleMock::class, '_ResetPOSTVariables');
        $this->setExpectedException(SWIFT_Exception::class, SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $method->invoke($obj);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_RuleMock
     */
    private function getMocked()
    {
        $mockView = $this->getMockBuilder(View_Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockView->method('RenderGrid')->willReturn(true);

        $mockView->method('Render')->willReturn(true);

        return $this->getMockObject('Parser\Admin\Controller_RuleMock', ['View' => $mockView]);
    }
}

class Controller_RuleMock extends Controller_Rule
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

