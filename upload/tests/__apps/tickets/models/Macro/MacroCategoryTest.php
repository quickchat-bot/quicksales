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

namespace Tickets\Models\Macro;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class MacroCategoryTest
* @group tickets
*/
class MacroCategoryTest extends \SWIFT_TestCase
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
            'macrocategoryid' => 1,
        ]);
        $data = new \SWIFT_DataStore([
            'macrocategoryid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\Macro\SWIFT_MacroCategoryMock', [
            'Data' => $data,
        ]);
        $this->assertInstanceOf('Tickets\Models\Macro\SWIFT_MacroCategory', $obj);
    }
}

class SWIFT_MacroCategoryMock extends SWIFT_MacroCategory
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

