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

namespace Tickets\Library\Ajax;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class TicketAjaxManagerTest
 * @group tickets
 * @group tickets-lib2
 */
class TicketAjaxManagerTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testGetTicketStatusOnDepartmentIdReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->GetTicketStatusOnDepartmentID(1, '', 1),
            'Returns false with invalid field');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ticketstatusid' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->expectOutputRegex('/select/');

        $this->setNextRecordType(static::NEXT_RECORD_RETURN_CALLBACK);

        static::$databaseCallback['NextRecord'] = function () {
            if (static::$nextRecordCount === 4) {
                \SWIFT::GetInstance()->Database->Record['staffvisibilitycustom'] = 1;
                \SWIFT::GetInstance()->Database->Record['ticketstatusid'] = 2;
                return true;
            }

            return static::$nextRecordCount % 2;
        };

        $this->assertTrue($obj->GetTicketStatusOnDepartmentID(1, 'field', 1));

        $this->assertTrue($obj->GetTicketStatusOnDepartmentID(1, 'field', 0, true, true));

        $this->assertClassNotLoaded($obj, 'GetTicketStatusOnDepartmentID', 1, '', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetTicketTypeOnDepartmentIdReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->GetTicketTypeOnDepartmentID(1, '', 1),
            'Returns false with invalid field');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ticketstatusid' => 1,
            'title' => 1,
            'tickettypeid' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => [
                    1 => [1],
                    'departmentapp' => 'tickets',
                    'parentdepartmentid' => 1,
                    'departmenttype' => 'public',
                ],
            ];
        };

        $this->expectOutputRegex('/select/');

        $this->setNextRecordType(static::NEXT_RECORD_RETURN_CALLBACK);

        static::$databaseCallback['NextRecord'] = function () {
            if (static::$nextRecordCount === 4) {
                \SWIFT::GetInstance()->Database->Record['staffvisibilitycustom'] = 1;
                \SWIFT::GetInstance()->Database->Record['ticketstatusid'] = 2;
                return true;
            }

            return static::$nextRecordCount % 2;
        };

        $this->assertTrue($obj->GetTicketTypeOnDepartmentID(1, 'field', 1));

        $this->assertTrue($obj->GetTicketTypeOnDepartmentID(1, 'field', 0, true, true));

        $this->assertClassNotLoaded($obj, 'GetTicketTypeOnDepartmentID', 1, '', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetTicketOwnerOnDepartmentIdReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->GetTicketOwnerOnDepartmentID(1, '', 1),
            'Returns false with invalid field');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ticketstatusid' => 1,
            'staffid' => 1,
            'fullname' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->expectOutputRegex('/select/');

        $this->setNextRecordType(static::NEXT_RECORD_RETURN_CALLBACK);

        static::$databaseCallback['NextRecord'] = function () {
            if (static::$nextRecordCount === 4) {
                \SWIFT::GetInstance()->Database->Record['staffvisibilitycustom'] = 1;
                \SWIFT::GetInstance()->Database->Record['ticketstatusid'] = 2;
                return true;
            }

            return static::$nextRecordCount % 2;
        };

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x === 'ticketcountcache') {
                return [];
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

            if ($x === 'staffcache') {
                return [
                    1 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '1',
                    ],
                    2 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '0',
                    ],
                ];
            }

            if ($x === 'groupassigncache') {
                return [
                    1 => [
                        1 => 1,
                        3 => 3,
                    ],
                ];
            }

            if ($x === 'tickettypecache' || $x === 'statuscache') {
                return [
                    1 => [
                        1 => 1,
                        'departmentid' => 2,
                    ],
                    2 => [
                        'departmentid' => 0,
                    ],
                ];
            }

            return [
                1 => [
                    1 => [1 => [1]],
                ],
            ];
        };

        $this->assertTrue($obj->GetTicketOwnerOnDepartmentID(1, 'field', 1));

        $this->assertTrue($obj->GetTicketOwnerOnDepartmentID(1, 'field', -2, true, true, true));

        $this->assertTrue($obj->GetTicketOwnerOnDepartmentID(1, 'field', -1, true, true, true));

        $this->assertTrue($obj->GetTicketOwnerOnDepartmentID(2, 'field', 0, true, true, true));

        $this->assertClassNotLoaded($obj, 'GetTicketOwnerOnDepartmentID', 1, '', 1);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketAjaxManagerMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Library\Ajax\SWIFT_TicketAjaxManagerMock', $services);
    }
}

class SWIFT_TicketAjaxManagerMock extends SWIFT_TicketAjaxManager
{
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

