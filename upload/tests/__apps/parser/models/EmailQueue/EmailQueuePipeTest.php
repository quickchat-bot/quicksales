<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
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
 * Class EmailQueuePipeTest
 * @group parser-models
 */
class EmailQueuePipeTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Models\EmailQueue\SWIFT_EmailQueuePipe', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateIsSuccessful()
    {
        $obj = $this->getMocked();

        $_emailQueueTypeObject = new SWIFT_EmailQueueType_Backend();
        $this->assertInstanceOf(SWIFT_EmailQueuePipe::class, $obj->Create('email', $_emailQueueTypeObject, '', '',
            '', '', false, true));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateReturnsTrue()
    {
        $obj = $this->getMocked();

        $_emailQueueTypeObject = new SWIFT_EmailQueueType_Backend();
        $this->assertTrue($obj->Update('email', $_emailQueueTypeObject, '', '',
            '', '', false, true));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'Update', 'email', null, '', '',
            '', '', false, true);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_EmailQueuePipeMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Models\EmailQueue\SWIFT_EmailQueuePipeMock');
    }
}

class SWIFT_EmailQueuePipeMock extends SWIFT_EmailQueuePipe
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

