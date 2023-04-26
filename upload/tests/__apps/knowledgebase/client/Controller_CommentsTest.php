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

namespace Knowledgebase\Client;

use Knowledgebase\Admin\LoaderMock;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use SWIFT;
use SWIFT_Exception;

/**
 * Class Controller_CommentsTest
 * @group knowledgebase
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
        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false, true, false);
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "knowledgebasearticleid = '0'")) {
                return [
                    'knowledgebasearticleid' => 0,
                ];
            }

            if (false !== strpos($x, "knowledgebasearticleid = '2'")) {
                return [
                    'knowledgebasearticleid' => 2,
                    'knowledgebasetype' => '3',
                ];
            }

            return [
                'knowledgebasearticleid' => 1,
                'kbarticleid' => '1',
                'knowledgebasetype' => '1',
                'seosubject' => 'seosubject',
                'knowledgebasestatus' => '2',
                'allowcomments' => '1',
                'uservisibilitycustom' => '0',
            ];
        });

        $this->mockProperty($mockDb, 'Record', [
            'knowledgebasearticleid' => '1',
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
                'widgetname' => 'knowledgebase',
                'isenabled' => '1',
            ]
        ]);

        SWIFT::GetInstance()->Cache = $mockCache;
    }

    /**
     * @return Controller_CommentsMock
     * @throws SWIFT_Exception
     */
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

        $mockArt = $this->getMockBuilder('Knowledgebase\Library\Article\SWIFT_KnowledgebaseArticleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mockArt->method('RetrieveForUser')->willReturnCallback(function ($x) {
            if ($x === 2) {
                return [1];
            }

            if ($x === 3) {
                return [new SWIFT_KnowledgebaseArticle(new \SWIFT_DataID(1))];
            }

            if ($x === 4) {
                return [new SWIFT_KnowledgebaseArticle(new \SWIFT_DataStore([
                    'knowledgebasearticleid' => 1,
                    'kbarticleid' => '1',
                    'knowledgebasetype' => '1',
                    'knowledgebasestatus' => '2',
                    'allowcomments' => '1',
                    'uservisibilitycustom' => '0',
                ]))];
            }

            return false;
        });

        return new Controller_CommentsMock([
            'Template' => $mockTpl,
            'Language' => $mockLang,
            'UserInterface' => $mockInt,
            'View' => $mockView,
            'CommentManager' => $mockMgr,
            'KnowledgebaseArticleManager' => $mockArt,
        ]);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Knowledgebase\Client\Controller_Comments', $obj);

        $this->getController();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testSubmitThrowsException()
    {
        $obj = $this->getController();

        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->Submit(2);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testSubmitReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->Submit(1),
            'Returns false without articles');

        $this->assertTrue($obj->Submit(3),
            'Returns true with articles');

        $this->assertTrue($obj->Submit(4),
            'Returns true with articles');

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
