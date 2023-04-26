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

namespace Tickets\Models\Ticket;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
* Class TicketTest
* @group tickets
*/
class TicketTest extends \SWIFT_TestCase
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
            'ticketid' => 1,
        ]);
        $data = new \SWIFT_DataStore([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
        ]);
        $obj = $this->getMockObject('Tickets\Models\Ticket\SWIFT_TicketMock', [
            'Data' => $data,
        ]);
        $this->assertInstanceOf('Tickets\Models\Ticket\SWIFT_Ticket', $obj);
    }

    public function testUpdateSetsReplyTo() {
        $this->getMockServices();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\SWIFT_Database $mockDb */
        $mockDb = $this->mockServices['Database'];
        $mockDb->method('QueryFetch')->willReturn([
            'ticketid' => 1,
        ]);
        $data = new \SWIFT_DataStore([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 0,
            'subject' => 'subject',
            'fullname' => 'fullname',
            'email' => 'mail1@test.com',
            'replyto' => 'mail1@test.com',
        ]);
        $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
            ->disableOriginalConstructor()
            ->getMock();
        $mockEmoji->method('decode')->willReturnArgument(0);
        $mockEmoji->method('encode')->willReturnArgument(0);
        $obj = $this->getMocked([
            'Data' => $data,
            'Emoji' => $mockEmoji,
        ]);

        $reflectionObj = new \ReflectionObject($obj);
        $property = $reflectionObj->getProperty('_dataStore');
        $property->setAccessible(true);

        $this->assertTrue($obj->Update('subject', 'fullname', 'mail2@test.com', false));
        $dataStore = $property->getValue($obj);
        $this->assertEquals('mail1@test.com', $dataStore['replyto']);

        $this->assertTrue($obj->Update('subject', 'fullname', 'mail2@test.com', true));
        $dataStore = $property->getValue($obj);
        $this->assertEquals('mail2@test.com', $dataStore['replyto']);
    }

    public function testUpdateSetsLastActivity() {
        $this->getMockServices();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\SWIFT_Database $mockDb */
        $mockDb = $this->mockServices['Database'];
        $mockDb->method('QueryFetch')->willReturn([
            'ticketid' => 1,
        ]);
        $oldActivity = 1614680773;
        $data = new \SWIFT_DataStore([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 0,
            'subject' => 'subject',
            'fullname' => 'fullname',
            'email' => 'mail1@test.com',
            'replyto' => 'mail1@test.com',
            'dateline' => $oldActivity,
            'lastactivity' => $oldActivity,
            'ticketstatustitle' => 'Open'
        ]);

        $obj = $this->getMocked([
            'Data' => $data
        ]);

        $reflectionObj = new \ReflectionObject($obj);
        $property = $reflectionObj->getProperty('_dataStore');
        $property->setAccessible(true);

        # First verify the lastactivity is not changed yet.
        $dataStore = $property->getValue($obj);
        $this->assertEquals($oldActivity, $dataStore['lastactivity']);

        # Now change and verify the lastactivity is changed to DATENOW.
        $this->assertTrue($obj->UpdatePool('ticketstatustitle', 'Closed'));
        $dataStore = $property->getValue($obj);
        $this->assertEquals(DATENOW, $dataStore['lastactivity']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketMock
     */
    private function getMocked($services)
    {
        return $this->getMockObject(SWIFT_TicketMock::class, $services);
    }
}

class SWIFT_TicketMock extends SWIFT_Ticket
{

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct($this->Data);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

