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

namespace Troubleshooter\Client;

/**
 * Class ClientController_CommentsTest
 * @group troubleshooter
 */
class ClientController_CommentsTest extends \SWIFT_TestCase
{
    /**
     * @param bool $useCache
     * @param array $services
     * @param bool $allow
     * @return Controller_CommentsMock
     * @throws \SWIFT_Exception
     */
    protected function getController($useCache = true, array $services = [], $allow = true)
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

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

                if (false !== strpos($x, "troubleshooterstepid = '3'") ||
                    false !== strpos($x, "troubleshootercategoryid = '3'")) {
                    return [
                        'troubleshooterstepid' => 3,
                        'troubleshootercategoryid' => 3,
                        'categorytype' => 5,
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

        $settings->method('Get')
            ->willReturn('1');

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
            if ($allow) {
                // add user to group access
                $cache[9] = [
                    1 => [1]
                ];
            }
            $mockCache->method('Get')->willReturn($cache);
        } else {
            $mockCache->method('Get')->willReturn([]);
        }

        \SWIFT::GetInstance()->Cache = $mockCache;

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mockInt->method('GetIsClassLoaded')->willReturn(true);
        $mockInt->method('Error')->willReturn(true);

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        $mockMgr = $this->getMockBuilder('Base\Library\Comment\SWIFT_CommentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mockMgr->method('ProcessPOSTUser')->willReturn(true);

        $services = array_merge($services, [
            'UserInterface' => $mockInt,
            'Language' => $mockLang,
            'Settings' => $settings,
            'Database' => $mockDb,
            'CommentManager' => $mockMgr,
        ]);

        return new Controller_CommentsMock($settings, $services);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Troubleshooter\Client\Controller_Comments', $obj);

        // covers app is installed
        $this->getController(false);
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
            'Returns false with invalid category id');

        $this->assertTrue($obj->Submit(1),
            'Returns true with valid id');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Submit(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testSubmitWithInvalidCategoryThrowsAccessDenied()
    {
        $obj = $this->getController(true, [], false);
        $this->setExpectedException('SWIFT_Exception', 'Access Denied');
        $obj->Submit(3);
    }
}

/**
 * Class Controller_CommentMock
 * @package Troubleshooter\Client
 */
class Controller_CommentsMock extends Controller_Comments
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

/**
 * Class LoaderMock
 * @package Troubleshooter\Client
 */
class LoaderMock
{

    public $Load;

    public function __construct()
    {
        $this->Load = $this;
    }

    public function Controller()
    {
        return $this;
    }

    public function Index()
    {
        // do nothing
    }

    public function Library()
    {
        // do nothing
    }

    public function Method()
    {
        // do nothing
    }

    public function RenderForm()
    {
        // do nothing
    }
}
