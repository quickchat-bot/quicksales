<?php
/**
 * ###############################################
 *
 * Kayako Classic
 * _______________________________________________
 *
 * @author        Banjo Mofesola Paul <banjo.paul@aurea.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Parser\Models\EmailQueue;

use Knowledgebase\Admin\LoaderMock;
use Parser\Library\EmailQueue\SWIFT_EmailQueueType_Backend;
use SWIFT_DataID;
use SWIFT_Exception;

/**
 * Class EmailQueueMailboxTest
 * @group parser-models
 */
class EmailQueueMailboxTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Models\EmailQueue\SWIFT_EmailQueueMailbox', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateIsSuccessful()
    {
        $obj = $this->getMocked();

        $_emailQueueTypeObject = new SWIFT_EmailQueueType_Backend();
        $this->assertInstanceOf(SWIFT_EmailQueueMailbox::class, $obj->Create('email', $_emailQueueTypeObject, 'imap', '', '',
            '', '', false, true, true, 0, '', '', false, false, false, false, false, false, false, false, '0',
            false, false, '', '', 'tls', false));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateThrowsException()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_EmailQueue_Exception::class);
        $obj->Create(SWIFT_EmailQueueMailbox::class, $obj->Create('email', '', 'imap', '', '',
            '', '', false, true, true, 0, '', '', false, false, false, false, false, false, false, false, '0',
            false, false, '', '', 'wrongSMTPtype', false));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateReturnsTrue()
    {
        $obj = $this->getMocked();

        $_emailQueueTypeObject = new SWIFT_EmailQueueType_Backend();
        $this->assertTrue($obj->Update('email', $_emailQueueTypeObject, 'imap', '', '',
            '', '', false, true, 'hostname', 0, '', '', false, false, false, false, false, false, false, false, '0',
            false, false, '', '', 'tls', false));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateThrowsClassNotLoadedException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'Update', 'email', '', 'imap', '', '',
            '', '', false, true, true, true, 0, '', '', false, false, false, false, false, false, false, false, '0',
            false, false, '', '', 'tls', false);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateThrowsException()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_EmailQueue_Exception::class);
        $obj->Update('email', '', 'imap', '', '',
            '', '', false, true, true, true, 0, '', '', false, false, false, false, false, false, false, false, '0',
            false, false, '', '', 'wrongSMTPtype', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_EmailQueueMailboxMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Models\EmailQueue\SWIFT_EmailQueueMailboxMock');
    }
}

class SWIFT_EmailQueueMailboxMock extends SWIFT_EmailQueueMailbox
{
    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'emailqueueid' => 1,
            'tgroupid' => 1,
            'type' => APP_BACKEND,
            'departmentid' => 1,
            'tickettypeid' => 1,
            'priorityid' => 1,
            'ticketstatusid' => 1,
            'ticketautoresponder' => 1,
            'queuesignatureid' => 1,
            'fetchtype' => 'imap',
        ]);

        parent::__construct(new SWIFT_DataID(1));
    }

    public function Initialize()
    {
        // override
        $this->EmailQueueSignature = new SWIFT_EmailQueueSignature(1);
        return true;
    }
}

