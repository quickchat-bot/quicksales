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

namespace Tickets\Models\Escalation;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class EscalationNotificationTest
* @group tickets
*/
class EscalationNotificationTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $this->getMockServices();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\SWIFT_Database $mockDb */
        $mockDb = $this->mockServices['Database'];
        $mockDb->method('QueryFetch')->willReturn([
            'escalationnotificationid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\Escalation\SWIFT_EscalationNotificationMock');
        $this->assertInstanceOf('Tickets\Models\Escalation\SWIFT_EscalationNotification', $obj);
    }
}

class SWIFT_EscalationNotificationMock extends SWIFT_EscalationNotification
{

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct(1);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

