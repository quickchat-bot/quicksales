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

namespace Tickets\Models\Macro;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class MacroReplyTest
* @group tickets
*/
class MacroReplyTest extends \SWIFT_TestCase
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
            'macroreplyid' => 1,
        ]);
        $data = new \SWIFT_DataStore([
            'macroreplyid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\Macro\SWIFT_MacroReplyMock', [
            'Data' => $data,
        ]);
        $this->assertInstanceOf('Tickets\Models\Macro\SWIFT_MacroReply', $obj);
    }
}

class SWIFT_MacroReplyMock extends SWIFT_MacroReply
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

