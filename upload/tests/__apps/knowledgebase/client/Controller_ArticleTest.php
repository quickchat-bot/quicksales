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
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use PHPUnit\Framework\MockObject\MockObject;
use SWIFT;
use SWIFT_Exception;

/**
 * Class Controller_ArticleTest
 * @group knowledgebase
 */
class Controller_ArticleTest extends \SWIFT_TestCase
{
    /**
     * @var MockObject
     */
    private $_mockDb;

    /**
     * @var Controller_ArticleMock
     */
    private $_mockArticleController;

    public static $_next = 0;
    public static $_count = [];

    /**
     * @before
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockDb->method('QueryLimit')->willReturn(true);
        $this->_mockDb->method('AutoExecute')->willReturn(true);
        $this->_mockDb->method('Insert_ID')->willReturn(1);
        $this->_mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;
            return in_array(self::$_next, [2, 3, 6, 8, 11, 13, 16, 18, 23, 25, 27, 37], true);
        });
        $this->_mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "attachmentid = '3'")) {
                return [
                    'attachmentid' => '3',
                    'linktype' => '3',
                    'linktypeid' => '3',
                ];
            }

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
                'attachmentid' => '1',
                'linktype' => '5',
                'linktypeid' => '1',
                'filename' => 'file.txt',
                'storefilename' => 'file.txt',
                'kbarticleid' => '1',
                'kbcategoryid' => '1',
                'articlerating' => '1',
                'hasattachments' => '1',
                'knowledgebasetype' => '1',
                'knowledgebasestatus' => '2',
                'allowcomments' => '1',
                'creator' => '2',
                'isedited' => '1',
                'editedstaffid' => 0,
                'views' => 1,
                'uservisibilitycustom' => '0',
                'subject' => 'subject',
                'ratinghits' => 0,
                'ratingcount' => 0,
            ];
        });

        $this->mockProperty($this->_mockDb, 'Record', [
            'objid' => '1',
            'linktypeid' => '1',
            'kbarticleid' => '1',
            'kbcategoryid' => '1',
            'articlestatus' => '1',
            'attachmentid' => '1',
            'filename' => 'file.txt',
            'knowledgebasearticleid' => '1',
            'isedited' => '1',
            'relevance' => '1',
            'editedstaffid' => '1',
        ]);

        SWIFT::GetInstance()->Database = $this->_mockDb;

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

        $mockCache->method('Get')->willReturn([
            [
                '1' => [1],
                'appname' => 'knowledgebase',
                'widgetname' => 'knowledgebase',
                'isenabled' => '1',
            ],
            '7' => [1 => [1]],
        ]);

        SWIFT::GetInstance()->Cache = $mockCache;

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'charset') {
                return 'UTF-8';
            }

            return $x;
        });
        \SWIFT::GetInstance()->Language = $mockLang;

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

        $mockArt->method('RetrieveBySeoSubject')->willReturn([1]);

        $mockArt->method('RetrieveForUser')->willReturnCallback(function ($x) {
            if ($x === 2) {
                return [1];
            }

            if ($x === 3) {
                return [
                    new SWIFT_KnowledgebaseArticle(new \SWIFT_DataID(1)),
                ];
            }

            if ($x === 4) {
                $category = new SWIFT_KnowledgebaseCategory(new \SWIFT_DataID(1));
                $category->SetIsClassLoaded(false);
                return [
                    new SWIFT_KnowledgebaseArticle(new \SWIFT_DataID(1)),
                    $category,
                ];
            }

            return false;
        });

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturn(1);

        $mockCookie = $this->getMockBuilder('SWIFT_Cookie')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCookie->method('GetVariable')->willReturnCallback(function ($x) {
            if (!isset(self::$_count[$x])) {
                self::$_count[$x] = 0;
            }
            self::$_count[$x]++;

            if (false !== strpos($x, 'articleratings')) {
                return self::$_count[$x] <= 2;
            }

            return true;
        });

        $this->_mockArticleController = new Controller_ArticleMock([
            'Database' => $this->_mockDb,
            'Cache' => $mockCache,
            'Cookie' => $mockCookie,
            'Settings' => $mockSettings,
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
        $this->assertInstanceOf('Knowledgebase\Client\Controller_Article', $this->_mockArticleController);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIrsReturnsTrue()
    {
        $_POST['contents'] = 1;
        $this->assertTrue($this->_mockArticleController->IRS());

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([]);

        SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertFalse($this->_mockArticleController->IRS(),
            'Returns false if widget not installed');

        $this->_mockArticleController->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $this->_mockArticleController->IRS();
    }

    /**
     * @throws \SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testProcessArticleRatingThrowsException()
    {
        $mock = new \ReflectionClass($this->_mockArticleController);
        $method = $mock->getMethod('_ProcessArticleRating');
        $method->setAccessible(true);

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $this->_mockArticleController->SetIsClassLoaded(false);
        $method->invoke($this->_mockArticleController, new SWIFT_KnowledgebaseArticle(new \SWIFT_DataID(1)));
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewUsesSpecifiedKbCategoryIdInDbQueries(): void {
        // Arrange
        $kbCategoryId = 5;
        $this->_mockDb
            ->expects($this->exactly(3))
            ->method('QueryFetch')
            ->withConsecutive(
                [$this->anything()],
                [$this->stringContains(" kbcategoryid = '" . $kbCategoryId . "'")],
                [$this->stringContains(".kbcategoryid = '" . $kbCategoryId . "'")]
            );

        // Act
        $result = $this->_mockArticleController->View(3, $kbCategoryId);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewThrowsException()
    {
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $this->_mockArticleController->View('subject');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testViewReturnsTrue()
    {
        $this->assertFalse($this->_mockArticleController->View(0),
            'Returns false without id');

        $this->assertFalse($this->_mockArticleController->View(1),
            'Returns false with invalid id');

        $this->assertFalse($this->_mockArticleController->View(4, 0),
            'Returns false with empty category');

        $this->assertTrue($this->_mockArticleController->View(3, 5));

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([]);

        SWIFT::GetInstance()->Cache = $mockCache;
        $this->assertFalse($this->_mockArticleController->View(1),
            'Returns false if widget not installed');

        $this->_mockArticleController->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $this->_mockArticleController->View(1);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetAttachmentThrowsException()
    {
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $this->_mockArticleController->GetAttachment(2, 0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetAttachmentThrowsInvalidDataException()
    {
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $this->_mockArticleController->GetAttachment(3, 3);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetAttachmentReturnsTrue()
    {
        $this->assertFalse($this->_mockArticleController->GetAttachment(0, 0),
            'Returns false with invalid id');

        $this->assertTrue($this->_mockArticleController->GetAttachment(3, 1),
            'Returs true with valid attachment');

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([]);

        SWIFT::GetInstance()->Cache = $mockCache;
        $this->assertFalse($this->_mockArticleController->GetAttachment(0, 0),
            'Returs false if widget is not installed');

        $this->_mockArticleController->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $this->_mockArticleController->GetAttachment(0, 0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRateThrowsInvalidDataException()
    {
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $this->_mockArticleController->Rate(2);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRateReturnsTrue()
    {
        $this->assertFalse($this->_mockArticleController->Rate(0),
            'Returns false with invalid id');

        $this->assertTrue($this->_mockArticleController->Rate(3),
            'Returs true with articleratings = true');

        $this->assertTrue($this->_mockArticleController->Rate(3),
            'Returs true with valid articleratings = false');

        $this->assertTrue($this->_mockArticleController->Rate(3, 1),
            'Returs true with valid articleratings = false and ishelpful');

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([]);

        SWIFT::GetInstance()->Cache = $mockCache;
        $this->assertFalse($this->_mockArticleController->Rate(0),
            'Returs false if widget is not installed');

        $this->_mockArticleController->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $this->_mockArticleController->Rate(0);
    }
}

class Controller_ArticleMock extends Controller_Article
{
    /**
     * Controller_ArticleMock constructor.
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
