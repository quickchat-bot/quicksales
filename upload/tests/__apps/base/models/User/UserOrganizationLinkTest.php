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

namespace Base\Models\User;

use Knowledgebase\Admin\LoaderMock;
use PHPUnit_Framework_MockObject_MockObject;
use SWIFT_Database;
use SWIFT_Exception;

/**
 * Class UserOrganizationLinkTest
 * @group user
 */
class UserOrganizationLinkTest extends \SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance()
    {
        $this->getMockServices();
        $mockDb = $this->mockServices['Database'];
        $mockDb->method('QueryFetch')->willReturn([
            'userorganizationlinkid' => 1,
        ]);
        $obj = $this->getMocked();
        $this->assertInstanceOf(SWIFT_UserOrganizationLink::class, $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateReturnsId()
    {
        $this->getMockServices();
        $mockDb = $this->mockServices['Database'];
        $mockDb->method('QueryFetch')->willReturn([
            'userorganizationlinkid' => 1,
        ]);
        $userMock = $this->getMockBuilder(SWIFT_User::class)
            ->disableOriginalConstructor()
            ->setMethods(['GetIsClassLoaded','GetID','GetUserID'])
            ->getMock();
        $userMock->method('GetIsClassLoaded')->willReturn(true);
        $userMock->method('GetID')->willReturn(1);
        $userMock->method('GetUserID')->willReturn(1);

        $obj = $this->getMocked();
        $this->assertEquals(1, $obj::Create($userMock, 1));
    }
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|SWIFT_UserOrganizationLinkMock
     */
    private function getMocked()
    {
        return $this->getMockObject(SWIFT_UserOrganizationLinkMock::class);
    }

}

class SWIFT_UserOrganizationLinkMock extends SWIFT_UserOrganizationLink
{

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct(new \SWIFT_DataID(1));
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

