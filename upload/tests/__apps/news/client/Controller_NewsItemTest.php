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

namespace News\Client;

use News\Admin\LoaderMock;
use SWIFT;

/**
 * Class Controller_NewsItemTest
 * @group news
 */
class Controller_NewsItemTest extends \SWIFT_TestCase
{
    public static $_cacheCount = [];

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
            if (false !== strpos($x, "newsitemid = '2'")) {
                return false;
            }

            if (false !== strpos($x, "newsitemid = '3'")) {
                return [
                    'newsitemid' => 3,
                    'newstype' => '3',
                ];
            }

            return [
                'newsitemid' => 1,
                'newstype' => '1',
                'start' => '1',
                'expiry' => '2',
                'newsstatus' => '2',
                'allowcomments' => '1',
                'subject' => 'subject',
                'uservisibilitycustom' => '0',
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

        $mockCache->method('Get')->willReturnCallback(function ($x) {
            if (!isset(self::$_cacheCount[$x])) {
                self::$_cacheCount[$x] = 0;
            }

            self::$_cacheCount[$x]++;

            if ($x === 'widgetcache' && self::$_cacheCount[$x] === 1) {
                return [
                    [
                        'appname' => 'news',
                        'isenabled' => '1',
                    ]
                ];
            }

            return [];
        });

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

        return new Controller_NewsItemMock([
            'Template' => $mockTpl,
            'Language' => $mockLang,
            'UserInterface' => $mockInt,
            'View' => $mockView,
            'CommentManager' => $mockMgr,
        ]);
    }

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('News\Client\Controller_NewsItem', $obj);

        // covers app is installed
        $this->getController();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->View(0),
            'Returns false with invalid id');

        $this->assertFalse($obj->View(2),
            'Returns false with wrong id');

        $this->assertFalse($obj->View(3),
            'Returns false with newstype = private');

        $this->assertTrue($obj->View(1),
            'Returns true with correct id');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $this->assertFalse($obj->View(1));
    }

}

class Controller_NewsItemMock extends Controller_NewsItem
{
    /**
     * Controller_NewsItemMock constructor.
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

    protected function _ProcessNewsCategories() {
        // do nothing
    }
}
