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

namespace LiveChat\Visitor;

use News\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_TestCase;

/**
 * Class Controller_ChatTest
 * @group livechat
 * @group livechat-visitors
 */
class Controller_ChatTest extends SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testRequestTrue(): void
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Request(['_sessionID' => 'sessionid']),
            'Returns false for offline status');

        $this->assertClassNotLoaded($obj, 'Request');
    }

    public function testMessageSubmitWithHtmlTagsInFullName()
    {
        $obj = $this->getMocked();
        $_POST['fullname'] = "vivek sharma<br>";
        $_POST['email'] = "test@mail.com";
        $_POST['subject'] = "test vivek subject";
        $_POST['message'] = "test message";
        $_POST['departmentid'] = 4;
        self::assertTrue($obj->MessageSubmit(['_sessionID' => 'sessionid']));
    }

    /**
     * @param array $services
     * @return Controller_ChatMock
     */
    private function getMocked(array $services = []): Controller_ChatMock
    {
        $mockInt = $this->getMockBuilder('SWIFT_UserInterfaceControlPanel')
            ->setMethods(['ProcessDialogs', 'GetFooterScript'])
            ->disableOriginalConstructor()
            ->getMock();

        $cookie = $this->getMockBuilder('SWIFT_Cookie')
            ->disableOriginalConstructor()
            ->getMock();
        $cookie->method('Get')->willReturn('');

        $SWIFT         = SWIFT::GetInstance();
        $SWIFT->Cookie = $cookie;

        return $this->getMockObject(Controller_ChatMock::class, array_merge($services, [
            'UserInterface' => $mockInt,
            'Cookie'        => $cookie,
            'Template'      => $SWIFT->Template,
        ]));
    }
}

class Controller_ChatMock extends Controller_Chat
{
    /**
     * Controller_ChatMock constructor.
     * @param array $services
     * @throws SWIFT_Exception
     */
    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        SWIFT::GetInstance()->Interface->SetInterface(SWIFT_Interface::INTERFACE_TESTS);
        $this->SetIsClassLoaded(true);
    }

    public function Initialize(): bool
    {
        // override
        return true;
    }

    public function Message($_messageArguments = array())
    {
        return true;
    }
}
