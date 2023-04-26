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

namespace Tickets\Library\API;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class TicketAPIManagerTest
 * @group tickets
 * @group tickets-lib2
 */
class TicketAPIManagerTest extends \SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Library\API\SWIFT_TicketAPIManager', $obj);

        $this->setExpectedException('SWIFT_Exception', 'Invalid XML Object');
        $this->getMocked([], false);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderTicketsReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ticketmaskid' => 0,
            'userid' => 1,
            'ticketpostid' => 1,
            'userorganizationid' => 1,
            'organizationname' => 1,
            'tagid' => 1,
            'linkid' => 1,
            'tagname' => 1,
            'staffid' => 1,
            'linktypeid' => 1,
            'tgroupid' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => [
                    1 => [1],
                    'title' => 1,
                ],
            ];
        };

        static::$databaseCallback['SettingsGet'] = function ($x) {
            if ($x === 't_postorder') {
                return 'desc';
            }

            return 1;
        };

        $this->setNextRecordNoLimit();

        $this->assertTrue($obj->RenderTickets([1], true),
            'Returns true without errors');

        $this->assertClassNotLoaded($obj, 'RenderTickets', []);
    }

    /**
     * @param array $services
     * @param bool $isLoaded
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketAPIManagerMock
     */
    public function getMocked(array $services = [], $isLoaded = true)
    {
        $xml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();
        $xml->method('GetIsClassLoaded')->willReturn($isLoaded);
        $obj = $this->getMockObject('Tickets\Library\API\SWIFT_TicketAPIManagerMock', array_merge($services, [
            'XML' => $xml,
        ]));

        return $obj;
    }
}

class SWIFT_TicketAPIManagerMock extends SWIFT_TicketAPIManager
{

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct($this->XML);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

