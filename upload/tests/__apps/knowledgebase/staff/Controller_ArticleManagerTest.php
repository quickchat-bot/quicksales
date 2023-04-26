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
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Knowledgebase\Staff;

use Knowledgebase\Admin\LoaderMock;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use SWIFT;
use SWIFT_Exception;

/**
 * Class Controller_ArticleManagerTest
 * @group knowledgebase
 */
class Controller_ArticleManagerTest extends \SWIFT_TestCase
{
    public static $_record = [];

    public function setUp()
    {
        parent::setUp();

        unset($_POST);
    }

    /**
     * @param array $services
     * @return Controller_ArticleManagerMock
     * @throws SWIFT_Exception
     */
    public function getController(array $services = [])
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "kbarticleid = '0'")) {
                return [
                    'kbarticleid' => 0,
                ];
            }

            if (false !== strpos($x, "kbarticleid = '2'")) {
                return [
                    'kbarticleid' => 2,
                    'newstype' => '3',
                    'newsstatus' => '0',
                ];
            }

            return [
                'kbarticleid' => 1,
                'newstype' => '1',
                'newsstatus' => '2',
                'allowcomments' => '1',
                'uservisibilitycustom' => '1',
                'staffvisibilitycustom' => '2',
            ];
        });

        $this->mockProperty($mockDb, 'Record', [
            'kbarticleid' => '1',
            'kbcategoryid' => '1',
        ]);

        SWIFT::GetInstance()->Database = $mockDb;

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetProperty')->willReturn(1);
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
            ],
        ]);

        SWIFT::GetInstance()->Cache = $mockCache;

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

        $mockKb = $this->getMockBuilder('Knowledgebase\Library\Article\SWIFT_KnowledgebaseArticleManager')
            ->disableOriginalConstructor()
            ->getMock();
        $mockKb->method('RetrieveForStaff')->willReturnCallback(function ($x) {
            if ($x === 0) {
                return false;
            }
            if ($x === 2) {
                return [0];
            }
            return [
                new SWIFT_KnowledgebaseArticle(new \SWIFT_DataID(1)),
            ];
        });

        $mockMgr = $this->getMockBuilder('Base\Library\Comment\SWIFT_CommentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mockMgr->method('ProcessPOSTUser')->willReturn(true);

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturn(1);

        return new Controller_ArticleManagerMock(array_merge([
            'Database' => $mockDb,
            'Template' => $mockTpl,
            'Language' => $mockLang,
            'UserInterface' => $mockInt,
            'View' => $mockView,
            'CommentManager' => $mockMgr,
            'Settings' => $mockSettings,
            'KnowledgebaseArticleManager' => $mockKb,
        ], $services));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Knowledgebase\Staff\Controller_ArticleManager', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetLookupReturnsTrue()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, true, false, true, false, true, false);
        $mockDb->Record = &self::$_record;

        $obj = $this->getController([
            'Database' => $mockDb,
        ]);
        $this->assertFalse($obj->GetLookup());

        $_POST['q'] = 'q';
        self::$_record = [
            'kbcategoryid' => 1,
            'parentkbcategoryid' => '0',
        ];
        $this->expectOutputRegex('/icon_kbarticle/');
        $this->assertTrue($obj->GetLookup());
        self::$_record['categorytype'] = 4;
        self::$_record['kbcategoryid'] = 2;
        $this->assertTrue($obj->GetLookup());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetLookup();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetMenuReturnsTrue()
    {
        $obj = $this->getController();
        $this->expectOutputRegex('/<ul>/');
        $this->assertTrue($obj->GetMenu());
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetMenu();
    }

    /**
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testRenderMenuReturnsHtml()
    {
        $obj = $this->getController();
        $mock = new \ReflectionClass($obj);
        $method = $mock->getMethod('RenderMenu');
        $method->setAccessible(true);
        $this->assertEquals('', $method->invoke($obj, []));
        $this->assertContains('<ul>', $method->invoke($obj, ['articles' => [1 => ['subject' => 'subject']]]));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, []);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getController();
        $this->expectOutputRegex('/contentstext/');
        $this->assertFalse($obj->Get(0, 1));
        $this->expectOutputRegex('/contentstext/');
        $this->assertTrue($obj->Get(1, 1));
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Get(1, 1);
    }
}

class Controller_ArticleManagerMock extends Controller_ArticleManager
{
    /**
     * Controller_ArticleManagerMock constructor.
     * @param array $services
     * @throws SWIFT_Exception
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
