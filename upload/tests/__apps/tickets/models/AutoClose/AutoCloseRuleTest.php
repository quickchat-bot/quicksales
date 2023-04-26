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

namespace Tickets\Models\AutoClose;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class AutoCloseRuleTest
* @group tickets
*/
class AutoCloseRuleTest extends \SWIFT_TestCase
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
            'autocloseruleid' => 1,
        ]);
        $data = new \SWIFT_DataStore([
            'autocloseruleid' => 1,
            '_criteria' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\AutoClose\SWIFT_AutoCloseRuleMock', [
            'Data' => $data,
        ]);
        $this->assertInstanceOf('Tickets\Models\AutoClose\SWIFT_AutoCloseRule', $obj);
    }
}

class SWIFT_AutoCloseRuleMock extends SWIFT_AutoCloseRule
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

