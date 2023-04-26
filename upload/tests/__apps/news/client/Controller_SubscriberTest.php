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

namespace News\Client;

use News\Admin\LoaderMock;
use SWIFT;

/**
 * Class Controller_SubscriberTest
 * @group news
 */
class Controller_SubscriberTest extends \SWIFT_TestCase
{
    public static $_queryCount = [];

    public function setUp()
    {
        parent::setUp();

        // clear post values
        unset ($_POST);

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Escape')->willReturnArgument(0);
        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (!isset(self::$_queryCount[$x])) {
                self::$_queryCount[$x] = 0;
            }

            self::$_queryCount[$x]++;

            if (false !== strpos($x, "email = '") && self::$_queryCount[$x] > 1) {
                return false;
            }

            if (false !== strpos($x, "newssubscriberid = '0'")) {
                return false;
            }

            if (false !== strpos($x, "hash = 'hash'")) {
                return [
                    'newssubscriberhashid' => 1,
                    'newssubscriberid' => 1,
                ];
            }

            return [
                'newssubscriberid' => 1,
                'email' => 'me@email.com',
            ];
        });

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
        ], []);

        SWIFT::GetInstance()->Cache = $mockCache;

        $mockUser = $this->getMockBuilder('Base\Models\User\SWIFT_User')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['GetIsClassLoaded', 'GetUserID', 'ProcessNotifications', '__destruct'])
            ->getMock();

        $mockUser->method('__destruct')->willReturn(true);
        $mockUser->method('ProcessNotifications')->willReturn(true);
        $mockUser->method('GetIsClassLoaded')->willReturn(true);
        $mockUser->method('GetUserID')->willReturn(1);

        $mockMgr = $this->getMockBuilder('Base\Library\Notification\SWIFT_NotificationManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockProperty($mockUser, 'NotificationManager', $mockMgr);

        SWIFT::GetInstance()->User = $mockUser;

        $mockTg = $this->getMockBuilder('Base\Models\Template\SWIFT_TemplateGroup')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTg->method('GetTemplateGroupID')->willReturn(0);

        SWIFT::GetInstance()->TemplateGroup = $mockTg;

        SWIFT::GetInstance()->Load = new LoaderMock();
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

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturnOnConsecutiveCalls('1', '0');

        return new Controller_SubscriberMock([
            'Settings' => $mockSettings,
            'Template' => $mockTpl,
            'Language' => $mockLang,
            'UserInterface' => $mockInt,
            'View' => $mockView,
        ]);
    }

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('News\Client\Controller_Subscriber', $obj);

        // cover app is installed
        $this->getController();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testSubscribeReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->Subscribe(),
            'Returns false without _csrfhash');

        $_POST['_csrfhash'] = 'csrfhash';
        $this->assertFalse($obj->Subscribe(),
            'Returns false without subscribeemail');

        $_POST['subscribeemail'] = 'subscribeemail';
        $this->assertFalse($obj->Subscribe(),
            'Returns false without valid email');

        $_POST['subscribeemail'] = 'me@email.com';
        $this->assertFalse($obj->Subscribe(),
            'Returns false without registrationconsent');

        $_POST['registrationconsent'] = 'yes';
        $this->assertFalse($obj->Subscribe(),
            'Returns false if email is subscribed');

        $this->assertTrue($obj->Subscribe(),
            'Returns true with nw_svalidate = 1');

        $this->assertTrue($obj->Subscribe(),
            'Returns true with nw_svalidate = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $this->assertFalse($obj->Subscribe());
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testValidateReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->Validate(''),
            'Returns false without valid hash');

        $this->assertTrue($obj->Validate('hash'),
            'Returns true with valid newssubscriberid');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $this->assertFalse($obj->Validate(''));
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testUnsubscribeReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->Unsubscribe(0, ''),
            'Returns false with invalid id');

        $this->assertFalse($obj->Unsubscribe(1, ''),
            'Returns false with invalid hash');

        $this->assertTrue($obj->Unsubscribe(1, '8f9dc04e6abdcc9fea53'),
            'Returns true with valid hash');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $this->assertFalse($obj->Unsubscribe(0, ''));
    }
}

class Controller_SubscriberMock extends Controller_Subscriber
{
    /**
     * Controller_SubscriberMock constructor.
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->Load = new LoaderMock();
        $this->_sendEmails = false;

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
