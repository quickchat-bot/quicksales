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
* Class View_MaintenanceTest
* @group tickets
*/
class View_MaintenanceTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testRenderGridReturnsTrue()
    {
        $obj = $this->getView();
        $this->assertTrue($obj->Render(), 'Returns true');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Render(),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReIndexDataReturnsTrue()
    {
        $obj = $this->getView();

        $this->expectOutputRegex('/searchreindex/');

        $this->assertTrue($obj->RenderReIndexData(0, '', 0, 0, 0, 0),
            'Returns true');

        $this->assertTrue($obj->RenderReIndexData(100, 'url', 0, 0, 0, 0),
            'Returns true');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->RenderReIndexData(0, '', 0, 0, 0, 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReIndexPropertiesDataReturnsTrue()
    {
        $obj = $this->getView();

        $this->expectOutputRegex('/propertyreindex/');

        $this->assertTrue($obj->RenderReIndexPropertiesData(0, '', 0, 0, 0, 0),
            'Returns true');

        $this->assertTrue($obj->RenderReIndexPropertiesData(100, 'url', 0, 0, 0, 0),
            'Returns true');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->RenderReIndexPropertiesData(0, '', 0, 0, 0, 0);
    }

    /**
     * @return View_Maintenance
     */
    private function getView()
    {
        return $this->getMockObject('Tickets\Admin\View_MaintenanceMock');
    }
}

class View_MaintenanceMock extends View_Maintenance
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

