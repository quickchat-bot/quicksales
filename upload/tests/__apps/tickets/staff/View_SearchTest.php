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

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class View_SearchTest
 * @group tickets
 * @group tickets-staff
 * @group tickets-search
 */
class View_SearchTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $obj = $this->getMocked();
        $this->expectOutputRegex('/script/');
        $this->assertTrue($obj->Render());
        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Render());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|View_SearchMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Staff\View_SearchMock');
    }
}

class View_SearchMock extends View_Search
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

