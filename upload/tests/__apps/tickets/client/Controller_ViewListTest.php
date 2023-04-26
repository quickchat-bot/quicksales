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

namespace Tickets\Client;

use Base\Models\User\SWIFT_User;
use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_ViewListTest
 * @group tickets
 * @group tickets-client
 */
class Controller_ViewListTest extends \SWIFT_TestCase
{
    public static $_prop = [];

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Client\Controller_ViewList', $obj);

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => [
                    'appname' => 'tickets',
                    'widgetname' => 'viewtickets',
                    'isenabled' => '1',
                ],
            ];
        };

        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Client\Controller_ViewList', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $_SWIFT = \SWIFT::GetInstance();

        $mockInput = $this->getMockBuilder('SWIFT_Input')
            ->disableOriginalConstructor()
            ->getMock();

        $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = $this->getMocked([
            'Input' => $mockInput,
            'Emoji' => $mockEmoji,
        ]);

        $_SWIFT->User = null;
        $this->assertFalse($obj->Index(),
            'Returns false with invalid user');

        $mockUser = $this->getMockBuilder(SWIFT_User::class)
            ->disableOriginalConstructor()
            ->setMethods(['GetUserID', 'GetIsClassLoaded', 'GetEmailList', 'GetOrganization', 'GetProperty', '__destruct'])
            ->getMock();
        $mockUser->method('__destruct')->willReturn(true);
        $mockUser->method('GetIsClassLoaded')->willReturn(true);
        $mockUser->method('GetUserID')->willReturn(1);
        $mockUser->method('GetProperty')->willReturn(1);
        $mockUser->method('GetOrganization')->willReturn(1);
        $mockUser->method('GetEmailList')->willReturn([1 => 'me@mail.com']);
        $_SWIFT->User = $mockUser;

        $this->assertTrue($obj->Index());

        static::$databaseCallback['Query'] = function ($x) {
            if (false !== strpos($x, 'usersettings WHERE userid')) {
                static::$_prop['set'] = 1;
            } else {
                unset(static::$_prop['set']);
            }
        };

        $userSettings = [
            [
                'name' => 'sortby',
                'value' => 'userid',
            ],
            [
                'name' => 'sortorder',
                'value' => 'asc',
            ],
            [
                'name' => 'showresolved',
                'value' => '1',
            ],
        ];

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ticketmaskid' => 0,
            'lastactivity' => 0,
            'laststaffreplytime' => &static::$_prop['laststaffreplytime'],
            'lastuserreplytime' => 0,
            'lastreplier' => 0,
            'ticketstatusid' => 1,
            'tickettypeid' => 1,
            'priorityid' => 1,
            'subject' => 'subject',
        ];

        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });

        static::$databaseCallback['NextRecord'] = function () use($userSettings, $_SWIFT, $arr) {
            if (isset(static::$_prop['set'])) {
                \SWIFT::GetInstance()->Database->Record = $userSettings[static::$_prop['set'] - 1];
                static::$_prop['set']++;
                if (static::$_prop['set'] - 1 > count($userSettings)) {
                    unset(static::$_prop['set']);
                    return false;
                }

                return true;
            }

            $_SWIFT->Database->Record = $arr;

            return static::$nextRecordCount % 2;
        };

        $this->setNextRecordType(self::NEXT_RECORD_RETURN_CALLBACK);

        static::$_prop['laststaffreplytime'] = 0;
        $this->assertTrue($obj->Index(true, 'no'));

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => [
                    1 => 1,
                    'markasresolved' => 1,
                    'departmenttype' => 'public',
                    'type' => 'public',
                    'statustype' => 'public',
                    'department' => 1,
                    'title' => 1,
                ]
            ];
        };
        static::$_prop['laststaffreplytime'] = 1;
        $this->assertTrue($obj->Index(2, -1));

        unset(static::$databaseCallback['NextRecord']);
        $this->setNextRecordType(static::NEXT_RECORD_NO_LIMIT);
        static::$databaseCallback['Query'] = function ($x) {
            if (false !== strpos($x, "departmentid <> '0'")) {
                static::$nextRecordCount++;
            }
        };
        $this->assertTrue($obj->Index(0, -1));

        $this->assertClassNotLoaded($obj, 'Index');
    }

    public function testSortThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Sort', '', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSortReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->User = $this->getMockBuilder('Base\Models\User\SWIFT_User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertTrue($obj->Sort('userid', 'desc'),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Sort', 'userid', 'desc');
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_ViewListMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject(Controller_ViewListMock::class, $services);
    }
}

class Controller_ViewListMock extends Controller_ViewList
{
    public static $_sortContainer = [
        'userid' => 1,
    ];

    public static $_sortOrderContainer = [
        'desc' => 1,
    ];

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

