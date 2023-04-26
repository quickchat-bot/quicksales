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

namespace Base\Admin;

use Knowledgebase\Admin\LoaderMock;
use PHPUnit_Framework_MockObject_MockObject;
use SWIFT_Exception;
use SWIFT_TestCase;
use Base\Admin\View_Diagnostics;

/**
 * Class View_DiagnosticsTest
 * @group base
 * @group base_admin
 */
class View_DiagnosticsTest extends SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance(): void
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf(View_Diagnostics::class, $obj);
    }

    public function testRenderActiveSessionsGridReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->expectOutputRegex('/activesessionsgrid/');
        $this->assertTrue($obj->RenderActiveSessionsGrid(),
            'Returns true without errors');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderActiveSessionsGrid(),
            'Returns false if class is not loaded');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|View_DiagnosticsMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Admin\View_DiagnosticsMock');
    }
}

class View_DiagnosticsMock extends View_Diagnostics
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
}

