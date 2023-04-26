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

namespace News\Staff;

use News\Admin\LoaderMock;
use SWIFT;

/**
 * Class Controller_CommentsTest
 * @group news
 */
class Controller_CommentsTest extends \SWIFT_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "newsitemid = '0'")) {
                return [
                    'newsitemid' => 0,
                ];
            }

            if (false !== strpos($x, "newsitemid = '2'")) {
                return [
                    'newsitemid' => 2,
                    'newstype' => '3',
                    'newsstatus' => '0',
                ];
            }

            return [
                'newsitemid' => 1,
                'newstype' => '1',
                'newsstatus' => '2',
                'allowcomments' => '1',
                'uservisibilitycustom' => '1',
                'staffvisibilitycustom' => '2',
            ];
        });

        $this->mockProperty($mockDb, 'Record', [
            'newsitemid' => '1',
        ]);

        SWIFT::GetInstance()->Database = $mockDb;

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetProperty')->willReturnArgument(0);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetPermission')
            ->willReturnOnConsecutiveCalls('0', '1');

        SWIFT::GetInstance()->Staff = $mockStaff;

        $mockSession = $this->getMockBuilder('SWIFT_Session')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSession->method('GetIsClassLoaded')->willReturn(true);
        $mockSession->method('GetProperty')->willReturnArgument(0);

        SWIFT::GetInstance()->Session = $mockSession;

        $mockRouter = $this->getMockBuilder('SWIFT_Router')
            ->disableOriginalConstructor()
            ->getMock();

        SWIFT::GetInstance()->Router = $mockRouter;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturnOnConsecutiveCalls([], [
            [
                'appname' => 'news',
                'isenabled' => '1',
            ]
        ]);

        SWIFT::GetInstance()->Cache = $mockCache;
    }

    public function getController()
    {
        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        $mockView = $this->getMockBuilder('SWIFT_View')
            ->disableOriginalConstructor()
            ->getMock();

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockMgr = $this->getMockBuilder('Base\Library\Comment\SWIFT_CommentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mockMgr->method('ProcessPOSTUser')->willReturn(true);

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturn(1);

        return new Controller_CommentsMock([
            'Template' => $mockTpl,
            'Language' => $mockLang,
            'UserInterface' => $mockInt,
            'View' => $mockView,
            'CommentManager' => $mockMgr,
            'Settings' => $mockSettings,
        ]);
    }

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('News\Staff\Controller_Comments', $obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testSubmitReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->Submit(0),
            'Returns false with invalid id');

        $this->assertFalse($obj->Submit(2),
            'Returns false with newstype = private');

        $this->assertTrue($obj->Submit(1),
            'Returns true with valid id');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Submit(1);
    }
}

class Controller_CommentsMock extends Controller_Comments
{
    /**
     * Controller_CommentsMock constructor.
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }

        parent::__construct();
    }

    public function Initialize()
    {
        return true;
    }
}
