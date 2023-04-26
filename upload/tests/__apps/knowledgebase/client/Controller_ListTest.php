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

namespace Knowledgebase\Client;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
 * Class Controller_ListTest
 * @group knowledgebase
 */
class Controller_ListTest extends \SWIFT_TestCase
{
    public static $_next = 0;
    public static $_cacheCount = [];
    public static $_settingsCount = [];

    public function setUp()
    {
        parent::setUp();

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            if (self::$_next === 9) {
                SWIFT::GetInstance()->Database->Record = [
                    0 => [1],
                    'kbcategoryid' => 0,
                ];
            }

            if (self::$_next % 3 === 0 || self::$_next === 1) {
                return true;
            }

            return false;
        });

        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "categoryid = '2'")) {
                return false;
            }

            if (false !== strpos($x, "categoryid = '3'")) {
                return [
                    'kbcategoryid' => 3,
                    'categorytype' => 3,
                ];
            }

            if (false !== strpos($x, "categoryid = '4'")) {
                return [
                    'kbcategoryid' => 4,
                    'categorytype' => 1,
                    'uservisibilitycustom' => '1',
                ];
            }

            return [
                'kbcategoryid' => 1,
                'categorytype' => 1,
                'uservisibilitycustom' => '1',
            ];
        });

        $mockDb->Record = [
            0 => [1],
            'kbcategoryid' => 1,
        ];

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

            if ($x === 'widgetcache' && self::$_cacheCount[$x] % 2 === 1) {
                return [];
            }

            if ($x === 'usergroupassigncache' && self::$_cacheCount[$x] === 3) {
                return [7 => [1 => [1]]];
            }

            self::$_cacheCount[$x]++;

            return [
                [
                    'appname' => 'knowledgebase',
                    'widgetname' => 'knowledgebase',
                    'isenabled' => '1',
                ],
            ];
        });

        SWIFT::GetInstance()->Cache = $mockCache;
    }

    /**
     * @return Controller_ListMock
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

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturnCallback(function ($x) {
            if (!isset(self::$_settingsCount[$x])) {
                self::$_settingsCount[$x] = 0;
            }

            self::$_settingsCount[$x]++;

            if ($x === 'kb_categorycolumns' && self::$_settingsCount[$x] === 6) {
                return -1;
            }

            return self::$_settingsCount[$x];
        });

        SWIFT::GetInstance()->Settings = $mockSettings;

        return new Controller_ListMock([
            'Settings' => $mockSettings,
            'Template' => $mockTpl,
            'Language' => $mockLang,
            'UserInterface' => $mockInt,
            'View' => $mockView,
        ]);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Knowledgebase\Client\Controller_List', $obj);

        $this->getController();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->Index(2),
            'Returns false with invalid id');

        $this->assertFalse($obj->Index(3),
            'Returns false with private category type');

        $this->assertFalse($obj->Index(4),
            'Returns false with uservisibilitycustom = 1');

        $this->assertTrue($obj->Index(1),
            'Returns true without uservisibilitycustom');

        $this->assertTrue($obj->Index(0),
            'Returns true without id');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $this->assertFalse($obj->Index(1));
    }
}

class Controller_ListMock extends Controller_List
{
    /**
     * Controller_ListMock constructor.
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

    protected function _ProcessKnowledgebaseCategories()
    {
        // do nothing
    }
}
