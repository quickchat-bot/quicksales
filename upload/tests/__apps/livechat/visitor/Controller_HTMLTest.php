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

namespace LiveChat\Visitor;

use News\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;
use SWIFT_TestCase;

/**
 * Class Controller_HTMLTest
 * @group livechat
 * @group livechat-visitors
 */
class Controller_HTMLTest extends SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testNoJSImageReturnsTrue(): void
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->NoJSImage(),
            'Returns true without errors');

        $this->assertClassNotLoaded($obj, 'NoJSImage');
    }

    /**
     * @param array $services
     * @return Controller_HTMLMock
     */
    private function getMocked(array $services = []): Controller_HTMLMock
    {
        $rtr = $this->getMockBuilder('SWIFT_Router')
            ->disableOriginalConstructor()
            ->getMock();
        $rtr->method('GetRawQueryString')->willReturn('');

        $cookie = $this->getMockBuilder('SWIFT_Cookie')
            ->disableOriginalConstructor()
            ->getMock();
        $cookie->method('Get')->willReturn('1');

        $fire = $this->getMockBuilder('SWIFT_FirePHP')
            ->disableOriginalConstructor()
            ->getMock();

        $SWIFT         = SWIFT::GetInstance();
        $SWIFT->Cookie = $cookie;

        return $this->getMockObject(Controller_HTMLMock::class, array_merge($services, [
            'Router'   => $rtr,
            'Cookie'   => $cookie,
            'FirePHP'  => $fire,
            'Template' => $SWIFT->Template,
        ]));
    }
}

class Controller_HTMLMock extends Controller_HTML
{
    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);
        SWIFT::GetInstance()->Interface->SetInterface(\SWIFT_Interface::INTERFACE_TESTS);
    }

    public function Initialize(): bool
    {
        // override
        return true;
    }
}
