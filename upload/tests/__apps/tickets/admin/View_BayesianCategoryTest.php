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
use Tickets\Models\Bayesian\SWIFT_BayesianCategory;

/**
* Class View_BayesianCategoryTest
* @group tickets
*/
class View_BayesianCategoryTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $_POST['_isDialog'] = 1;

        $obj = $this->getView();
        $this->assertTrue($obj->Render(2),
            'Returns true in insert mode');

        $mock = $this->getMockBuilder('Tickets\Models\Bayesian\SWIFT_BayesianCategory')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('GetProperty')->willReturnArgument(0);
        $this->assertTrue($obj->Render(1, $mock),
            'Returns true in edit mode');

        $mock = $this->getMockBuilder('Tickets\Models\Bayesian\SWIFT_BayesianCategory')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('GetProperty')->willReturn(1);
        $this->assertTrue($obj->Render(1, $mock),
            'Returns true in edit mode');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Render(1),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderGridReturnsTrue()
    {
        $obj = $this->getView();
        $obj->UserInterfaceGrid->method('GetMode')->willReturn(2);
        $this->assertTrue($obj->RenderGrid(),
            'Returns true');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderGrid(),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGridRenderReturnsArray()
    {
        $obj = $this->getView();
        $arr = ['ismaster' => 0];
        $this->assertCount(5, $obj::GridRender($arr), 'Returns true');
        $arr = ['ismaster' => 1];
        $this->assertCount(5, $obj::GridRender($arr), 'Returns true');
    }

    /**
     * @return View_BayesianCategoryMock
     */
    private function getView()
    {
        return $this->getMockObject('Tickets\Admin\View_BayesianCategoryMock');
    }
}

class View_BayesianCategoryMock extends View_BayesianCategory
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

