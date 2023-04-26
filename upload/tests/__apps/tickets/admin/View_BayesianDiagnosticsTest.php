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

namespace Tickets\Admin;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class View_BayesianDiagnosticsTest
 * @group tickets
 */
class View_BayesianDiagnosticsTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $obj = $this->getView();
        $this->assertTrue($obj->Render('html'));

        $this->assertClassNotLoaded($obj, 'Render');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderProbabilityResultReturnsHtml()
    {
        $obj = $this->getView();
        $probabilityResult = [
            [
                '1' => [
                    'combined' => 1
                ]
            ],
            [
                '1' => [
                    'combined' => 1,
                    '2' => 2,
                ]
            ],
        ];
        $bayesianCategoryContainer = [
            '1' => [
                'category' => 1
            ],
            '2' => [
                'category' => 2
            ]
        ];
        $this->assertContains('settabletitlerowmain2', $obj->RenderProbabilityResult($probabilityResult, $bayesianCategoryContainer));

        $this->assertClassNotLoaded($obj, 'RenderProbabilityResult', [], []);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|View_BayesianDiagnosticsMock
     */
    private function getView()
    {
        return $this->getMockObject('Tickets\Admin\View_BayesianDiagnosticsMock');
    }
}

class View_BayesianDiagnosticsMock extends View_BayesianDiagnostics
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

