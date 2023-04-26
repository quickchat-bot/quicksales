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

namespace Tickets\Library\Search;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;
use Base\Library\Rules\SWIFT_Rules;

/**
 * Class TicketSearchTest
 * @group tickets
 * @group tickets-search
 */
class TicketSearchTest extends \SWIFT_TestCase
{
    public static $_prop = [];

    public function testExtendCustomCriteriaReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['CacheGet'] = function ($x) {
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
            ];
        };
        $this->setNextRecordLimit(static::NEXT_RECORD_RETURN_CALLBACK);
        static::$databaseCallback['Query'] = function ($x) {
            if (false !== strpos($x, 'swcustomfieldoptions')) {
                static::$_prop['custom'] = 1;
            }
            static::$nextRecordCount = 0;
        };
        static::$databaseCallback['NextRecord'] = function () {
            if (static::$nextRecordCount === 2 && isset(static::$_prop['custom'])) {
                \SWIFT::GetInstance()->Database->Record['parentcustomfieldoptionid'] = 1;
                return true;
            }

            return static::$nextRecordCount % 2;
        };
        $_SWIFT = \SWIFT::GetInstance();
        $_SWIFT->Database->Record = [
            'parentcustomfieldoptionid' => 0,
            'customfieldid' => 1,
            'customfieldoptionid' => 1,
        ];

        $arr = [
            'customfield__1' => [
                'field' => 'custom',
            ],
        ];

        $this->assertTrue($obj::ExtendCustomCriteria($arr));
    }

    public function testGetCriteriaPointerReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->setNextRecordLimit(static::NEXT_RECORD_RETURN_CALLBACK);
        static::$databaseCallback['Query'] = function ($x) {
            if (false !== strpos($x, 'customfieldgroups')) {
                static::$_prop['custom'] = 1;
            }
            static::$nextRecordCount = 0;
        };
        static::$databaseCallback['NextRecord'] = function () {
            if (isset(static::$_prop['custom'])) {
                if (static::$nextRecordCount === 2) {
                    \SWIFT::GetInstance()->Database->Record['fieldtype'] = 3;
                    return true;
                }
                if (static::$nextRecordCount === 3) {
                    \SWIFT::GetInstance()->Database->Record['fieldtype'] = 4;
                    return true;
                }
                if (static::$nextRecordCount === 4) {
                    \SWIFT::GetInstance()->Database->Record['fieldtype'] = 10;
                    return true;
                }
            }
            return static::$nextRecordCount % 2;
        };
        $_SWIFT = \SWIFT::GetInstance();
        $_SWIFT->Database->Record = [
            'customfieldgrouptitle' => 'custom',
            'customfieldgroupid' => 1,
            'customfieldid' => 1,
            'fieldtype' => 1,
        ];

        $this->assertNotEmpty($obj::GetCriteriaPointer());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketSearchMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Library\Search\SWIFT_TicketSearchMock');
    }
}

class SWIFT_TicketSearchMock extends SWIFT_TicketSearch
{

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct([], SWIFT_Rules::RULE_MATCHALL);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

