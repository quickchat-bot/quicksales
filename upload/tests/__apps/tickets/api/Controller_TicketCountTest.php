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

namespace Tickets\Api;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_TicketCountTest
 * @group tickets
 * @group tickets-api
 */
class Controller_TicketCountTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Api\Controller_TicketCount', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetListReturnsTrue()
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
                    1 => [1 => 1],
                    'departments' => [
                        1 => [
                            'ownerstaff' => [
                                0 => [
                                    'totalunresolveditems' => 1,
                                    'staffid' => 1,
                                ],
                            ],
                            'ticketstatus' => [
                                1 => [
                                    'totalunresolveditems' => 1,
                                    'staffid' => 1,
                                ],
                            ],
                            'tickettypes' => [
                                1 => [
                                    'totalunresolveditems' => 1,
                                    'staffid' => 1,
                                ],
                            ],
                        ],
                        2 => [
                            'ownerstaff' => [],
                            'tickettypes' => [],
                            'ticketstatus' => [],
                        ]
                    ],
                    'ticketstatus' => [
                        1 => [
                            'totalunresolveditems' => 1,
                            'staffid' => 1,
                        ],
                    ],
                    'ownerstaff' => [
                        1 => [
                            'totalunresolveditems' => 1,
                            'staffid' => 1,
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

        $obj = $this->getMocked();

        $obj->Cache = $cache;
        \SWIFT::GetInstance()->Cache = $cache;

        $this->assertTrue($obj->GetList(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'GetList');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_TicketCountMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Api\Controller_TicketCountMock');
    }
}

class Controller_TicketCountMock extends Controller_TicketCount
{
    public $Database;
    public $Cache;

    public function __construct($services = [])
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

