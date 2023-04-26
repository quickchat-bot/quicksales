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

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class View_ViewTest
 * @group tickets
 * @group tickets-staff
 */
class View_TicketDispatchTest extends \SWIFT_TestCase
{
    public static $_prop = [];
    public static $_next = 0;

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderDispatchTabReturnsTrue()
    {
        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        $mockCache->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'staffgrouplinkcache') {
                return [
                    1 => [
                        1 => [1],
                    ],
                ];
            }

            $arr = [
                1 => [
                    'isdefault' => 1,
                    'ticketstatusid' => 1,
                    'priorityid' => 1,
                    'tickettypeid' => 1,
                    'bgcolorcode' => '#ffffff',
                    'departmentid' => 1,
                    'parentdepartmentid' => &static::$_prop['parentdepartmentid'],
                    'ticketviewid' => 1,
                    'staffid' => 1,
                    'viewscope' => 1,
                    'setasowner' => 1,
                    'defaultstatusonreply' => 1,
                    'viewalltickets' => 0,
                    'viewassigned' => 0,
                    'viewunassigned' => 0,
                    'afterreplyaction' => 4,
                    'fields' => [
                        [
                            'ticketviewfieldid' => 1,
                        ],
                    ],
                ],
                'list' => [
                    1 => [
                        'email' => 'me@email.com',
                        'tgroupid' => '1',
                        'contents' => 'contents',
                        'departmentid' => 1,
                        'isenabled' => '1',
                    ],
                ],
            ];

            if (static::$_prop['removelist'] === 1) {
                $arr['list'] = [
                    1 => [
                        'email' => 'me@email.com',
                        'tgroupid' => '1',
                        'contents' => 'contents',
                        'departmentid' => 2,
                        'isenabled' => '1',
                    ],
                ];
            }

            if (static::$_prop['removelist'] === 2) {
                $arr['list'] = [
                    1 => [
                        'email' => 'me@email.com',
                        'tgroupid' => '3',
                        'contents' => 'contents',
                        'departmentid' => 0,
                        'isenabled' => '1',
                    ],
                ];
            }

            if (static::$_prop['removelist'] === 3) {
                $arr['list'] = [
                    3 => [
                        'email' => 'me@email.com',
                        'tgroupid' => '3',
                        'contents' => 'contents',
                        'departmentid' => 0,
                        'isenabled' => '1',
                    ],
                ];
            }

            return $arr;
        });

        $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
            ->disableOriginalConstructor()
            ->getMock();

        $ctr = $this->getMockBuilder('Tickets\Staff\Controller_Ticket')
            ->disableOriginalConstructor()
            ->getMock();

        $rdr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldRendererStaff')
            ->disableOriginalConstructor()
            ->getMock();

        $ctr->CustomFieldRendererStaff = $rdr;

        $obj = $this->getMocked([
            'Controller' => $ctr,
            'Cache' => $mockCache,
            'Emoji' => $mockEmoji,
        ]);

        \SWIFT::GetInstance()->Cache = $mockCache;

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $mockDb->method('QueryFetch')->willReturn([
            'ticketviewid' => 1,
            'ticketdraftid' => 1,
            'contents' => 'contents',
        ]);
        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'ticketrecipients') ||
                false !== strpos($x, 'ticketemails')) {
                static::$_prop['ticketrecipients'] = 1;
                static::$_prop['recipienttype'] = 0;
            }
            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            if (isset(static::$_prop['ticketrecipients'])) {
                static::$_prop['recipienttype']++;
                if (static::$_prop['recipienttype'] === 4) {
                    unset(static::$_prop['ticketrecipients']);

                    return false;
                }

                \SWIFT::GetInstance()->Database->Record = [
                    'ticketrecipientid' => static::$_prop['recipienttype'],
                    'recipienttype' => static::$_prop['recipienttype'],
                    'ticketemailid' => static::$_prop['recipienttype'],
                    'email' => 'me' . static::$_prop['recipienttype'] . '@mail.com',
                ];

                return true;
            }

            return self::$_next % 2;
        });
        $mockDb->method('Insert_ID')->willReturn(1);
        static::$_prop['recipienttype'] = 0;
        $mockDb->Record = [
            'ticketrecipientid' => &static::$_prop['recipienttype'],
            'recipienttype' => &static::$_prop['recipienttype'],
            'ticketemailid' => &static::$_prop['recipienttype'],
            'email' => &static::$_prop['recipientemail'],
        ];
        \SWIFT::GetInstance()->Database = $mockDb;

        $mock = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceTab')
            ->disableOriginalConstructor()
            ->getMock();
        $mock2 = $obj->getTicketMock($this, true, false);
        $mock2->method('GetProperty')->willReturnCallback(function ($x) {
            if (!isset(static::$_prop[$x])) {
                static::$_prop[$x] = 1;
            }

            return static::$_prop[$x];
        });
        $mock3 = $obj->getUserMock($this);

        static::$_prop['parentdepartmentid'] = 1;
        static::$_prop['removelist'] = 0;
        $this->assertTrue($obj->RenderDispatchTab(2, $mock, $mock2, $mock3));

        static::$_prop['hasdraft'] = 0;
        static::$_prop['replyto'] = '';
        static::$_prop['removelist'] = 1;
        static::$_prop['parentdepartmentid'] = 2;
        $this->assertTrue($obj->RenderDispatchTab(3, $mock, $mock2, $mock3));

        static::$_prop['fullname'] = '';
        static::$_prop['removelist'] = 2;
        static::$_prop['tgroupid'] = 3;
        static::$_prop['departmentid'] = 0;
        static::$_prop['creator'] = 2;
        $this->assertTrue($obj->RenderDispatchTab(1, $mock, $mock2, $mock3, [1 => 1]));

        static::$_prop['departmentid'] = 0;
        static::$_prop['tgroupid'] = 1;
        static::$_prop['removelist'] = 3;
        static::$_prop['emailqueueid'] = 3;
        $this->assertTrue($obj->RenderDispatchTab(1, $mock, $mock2, $mock3));

        $this->assertTrue($obj->RenderDispatchTab(4, $mock));

        $this->assertClassNotLoaded($obj, 'RenderDispatchTab', 2, $mock, $mock2, $mock3);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderDispatchFormReturnsTrue()
    {
        $cache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $cache->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'staffcache') {
                return [
                    1 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '1',
                        'staffid' => '1',
                    ],
                    2 => [
                        'staffgroupid' => '2',
                        'groupassigns' => '2',
                        'isenabled' => '0',
                    ],
                    3 => [
                        'staffgroupid' => '3',
                        'groupassigns' => '1',
                        'isenabled' => '1',
                        'staffid' => '2',
                    ],
                    4 => [
                        'staffgroupid' => '4',
                        'groupassigns' => '4',
                        'isenabled' => '1',
                        'staffid' => '4',
                    ],
                ];
            }

            if ($x === 'ticketcountcache') {
                return [
                    'ownerstaff' => [
                        1 => [
                            'totalunresolveditems' => 1,
                            'staffid' => 1,
                        ],
                        4 => [
                            'totalunresolveditems' => 21,
                            'staffid' => 4,
                        ],
                    ],
                ];
            }

            if ($x === 'departmentcache') {
                return [
                    1 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    2 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    3 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                    4 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                ];
            }

            if ($x === 'groupassigncache') {
                return [
                    1 => [
                        1 => 1,
                        2 => 2,
                    ],
                ];
            }

            if ($x === 'tickettypecache' || $x === 'statuscache') {
                return [
                    1 => [
                        1 => 1,
                    ],
                    2 => [
                        'departmentid' => 2,
                    ],
                ];
            }

            return [
                1 => [
                    1 => [1 => [1]],
                ],
                3 => [
                    1 => [1 => [1]],
                ],
                4 => [
                    1 => [1 => [1]],
                ],
            ];
        });

        $obj = $this->getMocked([
            'Cache' => $cache,
        ]);

        \SWIFT::GetInstance()->Cache = $cache;
        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $mock = $obj->getTicketMock($this);

        $this->assertTrue($obj->RenderDispatchForm(1, $mock));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderDispatchForm(1, $mock),
            'Returns false if class is not loaded');
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_TicketMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Staff\View_TicketMock', $services);
    }
}
