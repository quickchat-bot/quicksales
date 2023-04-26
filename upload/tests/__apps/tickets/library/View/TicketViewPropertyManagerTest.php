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

namespace Tickets\Library\View;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
 *
 * Class TicketViewPropertyManagerTest
 * @group tickets-view
 */
class TicketViewPropertyManagerTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Library\View\SWIFT_TicketViewPropertyManager', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementTicketStatusReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->IncrementTicketStatus(1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'IncrementTicketStatus', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementTicketStatusThrowsInvalidData()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'IncrementTicketStatus', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementTicketTypeReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->IncrementTicketType(1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'IncrementTicketType', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementTicketTypeThrowsInvalidData()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'IncrementTicketType', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementTicketPriorityReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->IncrementTicketPriority(1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'IncrementTicketPriority', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementTicketPriorityThrowsInvalidData()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'IncrementTicketPriority', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementDepartmentReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->IncrementDepartment(1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'IncrementDepartment', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementDepartmentThrowsInvalidData()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'IncrementDepartment', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementStaffReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->IncrementStaff(1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'IncrementStaff', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementStaffThrowsInvalidData()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'IncrementStaff', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementTicketLinkReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->IncrementTicketLink(1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'IncrementTicketLink', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementTicketLinkThrowsInvalidData()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'IncrementTicketLink', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementTicketFlagReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->IncrementTicketFlag(1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'IncrementTicketFlag', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementTicketFlagThrowsInvalidData()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'IncrementTicketFlag', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementBayesianReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->IncrementBayesian(1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'IncrementBayesian', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementBayesianThrowsInvalidData()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'IncrementBayesian', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetTopTicketItemsReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertTrue(is_array($obj->GetTopTicketItems('type')),
            'Returns Empty Array');

        $this->assertTrue(is_array($obj->GetTopTicketItems(\SWIFT::GetInstance()->Staff->GetStaffID())),
            'Returns Array');

        $this->assertClassNotLoaded($obj, 'GetTopTicketItems', 'type');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRebuildCacheReturns()
    {
        $obj = $this->getMocked();

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        $mockCache->method('Get')->willReturnCallback(function ($x) {
            return null;
        });
        $obj->Cache = $mockCache;
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertTrue($obj->RebuildCache(),
            'Returns true with permission');

        \SWIFT::GetInstance()->Staff = null;
        $this->assertFalse($obj->RebuildCache(),
            'Returns false without permission');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketViewPropertyManagerMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Library\View\SWIFT_TicketViewPropertyManagerMock');
    }
}

class SWIFT_TicketViewPropertyManagerMock extends SWIFT_TicketViewPropertyManager
{
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

