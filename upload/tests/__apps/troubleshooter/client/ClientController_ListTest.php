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

namespace Troubleshooter\Client;

/**
 * Class ClientController_ListTest
 * @group troubleshooter
 */
class ClientController_ListTest extends \SWIFT_TestCase
{
    public static $tr_displayviews = 1;

    /**
     * @param bool $useCache
     * @param array $services
     * @return Controller_ListMock
     * @throws \SWIFT_Exception
     */
    protected function getController($useCache = true, array $services = [])
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false, true, false);

        $this->mockProperty($mockDb, 'Record', [
            'troubleshootercategoryid' => 1,
            'title' => 'title',
            'description' => 'description',
            'views' => 'views',
        ]);

        $mockDb->method('QueryFetch')
            ->willReturnCallback(function ($x) {
                if (false !== strpos($x, "troubleshooterstepid = '2'")) {
                    return [
                        'troubleshooterstepid' => 2,
                        'troubleshootercategoryid' => 0,
                        'categorytype' => 1,
                        'staffvisibilitycustom' => 1,
                        'uservisibilitycustom' => 1,
                    ];
                }

                return [
                    'troubleshooterstepid' => 1,
                    'troubleshootercategoryid' => 1,
                    'categorytype' => 1,
                    'staffvisibilitycustom' => 1,
                    'uservisibilitycustom' => 1,
                ];
            });

        \SWIFT::GetInstance()->Database = $mockDb;

        $settings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'tr_displayviews') {
                return self::$tr_displayviews++;
            }

            return '1';
        });

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        if ($useCache) {
            $cache = [
                [
                    'appname' => 'troubleshooter',
                    'displayinnavbar' => '1',
                    'uservisibilitycustom' => '0',
                    'widgetvisibility' => '0',
                    'isenabled' => '1',
                ]
            ];

            $mockCache->method('Get')->willReturn($cache);
        } else {
            $mockCache->method('Get')->willReturn([]);
        }

        \SWIFT::GetInstance()->Cache = $mockCache;

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceClient')
            ->disableOriginalConstructor()
            ->getMock();

        $mockInt->method('GetIsClassLoaded')->willReturn(true);
        $mockInt->method('Error')->willReturn(true);
        $mockInt->method('Header')->willReturn(true);
        $mockInt->method('Footer')->willReturn(true);

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        $services = array_merge($services, [
            'UserInterface' => $mockInt,
            'Language' => $mockLang,
            'Settings' => $settings,
            'Database' => $mockDb,
        ]);

        return new Controller_ListMock($settings, $services);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Troubleshooter\Client\Controller_List', $obj);

        $obj = $this->getController(false);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testIndexReturnsTrueAfterPrintingList()
    {
        $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTpl->method('Assign')->willReturn(true);
        $mockTpl->method('Render')->willReturn(true);

        $obj = $this->getController(true, ['Template' => $mockTpl]);

        $this->assertTrue($obj->Index(),
            'Returns true with tr_displayviews=1');

        $this->assertTrue($obj->Index(),
            'Returns true with tr_displayviews=0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Index();
    }
}

/**
 * Class Controller_CommentMock
 * @package Troubleshooter\Client
 */
class Controller_ListMock extends Controller_List
{

    public function __construct($settings, $services = [])
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
