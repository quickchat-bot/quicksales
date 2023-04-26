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

namespace Tickets\Admin;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class Controller_AjaxTest
* @group tickets
*/
class Controller_AjaxTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Tickets\Admin\Controller_Ajax', $obj);
    }

    public function testGetTicketStatusOnDepartmentIdReturnsTrue() {
        /** @var Controller_Ajax $obj */
        $obj = $this->getController();
        $this->assertTrue($obj->GetTicketStatusOnDepartmentID(1, 'field', 1));
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->GetTicketStatusOnDepartmentID(1, 'field', 1);
    }

    public function testGetTicketTypeOnDepartmentIdReturnsTrue() {
        /** @var Controller_Ajax $obj */
        $obj = $this->getController();
        $this->assertTrue($obj->GetTicketTypeOnDepartmentID(1, 'field', 1));
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->GetTicketTypeOnDepartmentID(1, 'field', 1);
    }

    public function testGetTicketOwnerOnDepartmentIdReturnsTrue() {
        /** @var Controller_Ajax $obj */
        $obj = $this->getController();
        $this->assertTrue($obj->GetTicketOwnerOnDepartmentID(1, 'field', 1));
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->GetTicketOwnerOnDepartmentID(1, 'field', 1);
    }

    /**
     * @return mixed
     */
    private function getController()
    {
        $mgr = $this->getMockBuilder('Tickets\Library\Ajax\SWIFT_TicketAjaxManager')
            ->disableOriginalConstructor()
            ->getMock();
        return $this->getMockObject('Tickets\Admin\Controller_AjaxMock', [
            'TicketAjaxManager' => $mgr,
        ]);
    }
}

class Controller_AjaxMock extends Controller_Ajax
{

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

