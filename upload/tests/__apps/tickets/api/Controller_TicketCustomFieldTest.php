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

namespace Tickets\Api;

use Knowledgebase\Admin\LoaderMock;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use SWIFT_Exception;

/**
 * Class Controller_TicketCustomFieldTest
 * @group tickets
 * @group tickets-api
 */
class Controller_TicketCustomFieldTest extends \SWIFT_TestCase
{
    public static $_next = 0;

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Api\Controller_TicketCustomField', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->GetList(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'GetList');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Get(1),
            'Returns false with invalid id');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'fileid' => 1,
            'filename' => 'file.txt',
            'originalfilename' => 'file.txt',
            'creationmode' => 6,
            'customfieldgroupid' => 1,
            'customfieldid' => 1,
            'isserialized' => 1,
            'isencrypted' => 1,
            'fieldvalue' => SWIFT_CustomFieldManager::Encrypt(serialize([1 => 1])),
            'fieldtype' => 7, // selectmultiple
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        static::$databaseCallback['CacheGet'] = function ($x) use ($arr) {
            $arr2 = $arr;
            $arr2['fieldtype'] = 6; // select
            $arr2[1] = [
                '_fields' => [
                    1 => [
                        'fieldname' => 'name',
                        'fieldtype' => 'text',
                        'type' => 'custom',
                        'title' => 'title',
                    ],
                    5 => [],
                ]
            ];

            $arr3 = $arr;
            $arr3['fieldtype'] = 9; // selectlink

            $arr4 = $arr;
            $arr4['fieldtype'] = 11; // file

            return [
                1 => $arr,
                2 => [1 => 1],
                3 => $arr2,
                4 => $arr3,
                5 => $arr4,
            ];
        };

        static::$databaseCallback['NextRecord'] = function () use ($_SWIFT) {
            static::$_next++;
            if (in_array(static::$_next, [9, 10, 11, 12], true)) {
                unset($_SWIFT->Database->Record['isencrypted'], $_SWIFT->Database->Record['isserialized']);

                switch (static::$_next) {
                    case 9 :
                        $_SWIFT->Database->Record['fieldvalue'] = [1 => 1];
                        break;
                    case 11:
                        $_SWIFT->Database->Record['fieldvalue'] = [1, [2, 1]];
                        break;
                    default:
                        $_SWIFT->Database->Record['fieldvalue'] = 1;
                        break;
                }

                $_SWIFT->Database->Record['customfieldid'] = static::$_next - 7;
            }
            return in_array(static::$_next, [1, 3, 5, 7, 8, 9, 10, 11, 12], true);
        };

        static::$nextRecordType = static::NEXT_RECORD_RETURN_CALLBACK;

        $this->assertTrue($obj->Get(1),
            'Returns true without errors');

        $this->assertClassNotLoaded($obj, 'Get', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPostReturnsTrue()
    {
        $mockMgr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldManager')
            ->disableOriginalConstructor()
            ->getMock();
        $mockMgr->method('Check')->willReturnOnConsecutiveCalls([1 => [1]], [0 => [1]]);
        $obj = $this->getMocked([
            'CustomFieldManager' => $mockMgr,
        ]);

        $this->assertFalse($obj->Post(1),
            'Returns false with invalid id');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'creationmode' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->setNextRecordType(self::NEXT_RECORD_NO_LIMIT);

        $this->assertFalse($obj->Post(1),
            'Returns false without field');

        $this->assertTrue($obj->Post(1),
            'Returns Get');

        $this->assertClassNotLoaded($obj, 'Post', 1);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_TicketCustomFieldMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Api\Controller_TicketCustomFieldMock', $services);
    }
}

class Controller_TicketCustomFieldMock extends Controller_TicketCustomField
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

