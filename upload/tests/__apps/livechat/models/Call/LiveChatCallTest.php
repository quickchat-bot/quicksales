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

namespace LiveChat\Models\Call;

use Base\Models\User\SWIFT_User;
use News\Admin\LoaderMock;
use SWIFT;
use SWIFT_TestCase;

/**
 * Class LiveChatCallTest
 * @group livechat
 * @group livechat-models
 */
class LiveChatCallTest extends SWIFT_TestCase
{
    public function testGetHistoryCountOnUserReturnsNumber(): void
    {
        $obj = $this->getMocked();

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        $arr = [
            'userid'      => 1,
            'callid'      => 1,
            'useremailid' => 1,
            'email'       => 'me@mail.com',
            'phone'       => '+50255554444',
            'phonenumber' => '+50255554444',
            'totalitems'  => 1,
        ];

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;

        $user = $this->getMockBuilder(SWIFT_User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(1, $obj::GetHistoryCountOnUser($user, ['me@mail.com']),
            'Returns one record');
    }

    public function testRetrieveHistoryExtendedReturnsArray(): void
    {
        $obj = $this->getMocked();

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        $arr = [
            'userid'      => 1,
            'callid'      => 1,
            'useremailid' => 1,
            'email'       => 'me@mail.com',
            'phone'       => '+50255554444',
            'phonenumber' => '+50255554444',
        ];

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;

        $history = $obj::RetrieveHistoryExtended(1, ['me@mail.com']);
        $this->assertNotEmpty($history, 'Call history is not empty');
        $this->assertContains('+50255554444', $history[1],
            'Array contains phone number');
    }

    /**
     * @param array $services
     * @return LiveChatCallMock
     */
    private function getMocked(array $services = []): LiveChatCallMock
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

        return $this->getMockObject(LiveChatCallMock::class, array_merge($services, [
            'Router'   => $rtr,
            'Cookie'   => $cookie,
            'FirePHP'  => $fire,
            'Template' => $SWIFT->Template,
        ]));
    }
}

class LiveChatCallMock extends SWIFT_Call
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
