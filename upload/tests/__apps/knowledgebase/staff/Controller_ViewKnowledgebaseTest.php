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

namespace Knowledgebase\Staff;

use Knowledgebase\Admin\LoaderMock;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use SWIFT;
use SWIFT_Exception;

/**
 * Class Controller_ViewKnowledgebaseTest
 * @group knowledgebase
 */
class Controller_ViewKnowledgebaseTest extends \SWIFT_TestCase
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

            if (false !== strpos($x, "attachmentid = '2'")) {
                return [
                    'attachmentid' => 2,
                    'linktype' => 2,
                    'linktypeid' => 2,
                ];
            }

            return [
                'newsitemid' => 1,
                'kbarticleid' => 1,
                'attachmentid' => 1,
                'linktype' => 5,
                'linktypeid' => 1,
                'newstype' => '1',
                'newsstatus' => '2',
                'allowcomments' => '1',
                'uservisibilitycustom' => '1',
                'staffvisibilitycustom' => '2',
            ];
        });

        $this->mockProperty($mockDb, 'Record', [
            'kbarticleid' => '1',
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
                'appname' => 'knowledgebase',
                'isenabled' => '1',
            ],
        ]);

        SWIFT::GetInstance()->Cache = $mockCache;
    }

    /**
     * @param array $services
     * @return Controller_ViewKnowledgebaseMock
     * @throws SWIFT_Exception
     */
    public function getController(array $services = [])
    {
        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        $mockView = $this->getMockBuilder('Knowledgebase\Staff\View_ViewKnowledgebase')
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
                new SWIFT_KnowledgebaseArticle(new \SWIFT_DataStore([
                    'kbarticleid' => 1,
                    'views' => 1,
                    'subject' => 'subject',
                    'ratinghits' => 1,
                    'ratingcount' => 1,
                ])),
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

        $mockRender = $this->getMockBuilder('Knowledgebase\Library\Render\SWIFT_KnowledgebaseRenderManager')
            ->disableOriginalConstructor()
            ->getMock();
        $mockRender->method('RenderViewKnowledgebaseTree')->willReturn('');

        return new Controller_ViewKnowledgebaseMock(array_merge([
            'Template' => $mockTpl,
            'Language' => $mockLang,
            'UserInterface' => $mockInt,
            'View' => $mockView,
            'CommentManager' => $mockMgr,
            'Settings' => $mockSettings,
            'KnowledgebaseArticleManager' => $mockKb,
            'KnowledgebaseRenderManager' => $mockRender,
        ], $services));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = new Controller_ViewKnowledgebase();
        $this->assertInstanceOf('Knowledgebase\Staff\Controller_ViewKnowledgebase', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testLoadDisplayDataForViewArticleReturnsTrue()
    {
        $obj = $this->getController();
        $this->assertTrue($obj->_LoadDisplayDataForViewArticle());
        $this->setExpectedException('SWIFT_Exception');
        $obj->SetIsClassLoaded(false);
        $obj->_LoadDisplayDataForViewArticle();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $obj = $this->getController();
        $this->assertTrue($obj->Index(),
            'Returns true with staff_kbcanviewarticles = 0');
        $this->assertTrue($obj->Index(),
            'Returns true with staff_kbcanviewarticles = 1');
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->Index();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testArticleReturnsTrue()
    {
        $obj = $this->getController();
        $this->assertFalse($obj->Article(0),
            'Returns false with invalid id');
        $this->assertTrue($obj->Article(1),
            'Returns true with staff_kbcanviewarticles = 0');
        $this->assertTrue($obj->Article(1),
            'Returns true with staff_kbcanviewarticles = 1');
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->Article(1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessArticleRatingReturnsArray()
    {
        $art = new SWIFT_KnowledgebaseArticle(new \SWIFT_DataStore([
            'kbarticleid' => 1,
            'articlerating' => 1,
        ]));
        $arr = ['_hasNotRated' => false, '_articleRating' => 1];
        $cookieMock = $this->getMockBuilder('SWIFT_Cookie')
            ->disableOriginalConstructor()
            ->getMock();
        $cookieMock->method('GetVariable')->willReturn(true);
        $obj = $this->getController([
            'Cookie' => $cookieMock,
        ]);
        $this->assertEquals($arr, $obj->_ProcessArticleRating($art));
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->_ProcessArticleRating($art);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRateThrowsException()
    {
        $obj = $this->getController();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->Rate(2);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRateReturnsTrue()
    {
        $cookieMock = $this->getMockBuilder('SWIFT_Cookie')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = $this->getController([
            'Cookie' => $cookieMock,
        ]);

        $this->assertFalse($obj->Rate(0),
            'Returns false with invalid id');

        $this->assertTrue($obj->Rate(1, 0),
            'Returns true with _isHelpful = 0');

        $this->assertTrue($obj->Rate(1, 1),
            'Returns true with _isHelpful = 1');

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->Rate(1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetAttachmentThrowsException()
    {
        $obj = $this->getController();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->GetAttachment(2, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetAttachmentThrowsInvalidDataException()
    {
        $obj = $this->getController();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->GetAttachment(1, 2);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetAttachmentReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->GetAttachment(0, 1),
            'Returns false with invalid id');

        $this->assertTrue($obj->GetAttachment(1, 1),
            'Returns true');

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->GetAttachment(1, 1);
    }
}

class Controller_ViewKnowledgebaseMock extends Controller_ViewKnowledgebase
{
    /**
     * Controller_ViewKnowledgebaseMock constructor.
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
