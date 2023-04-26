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

namespace Reports\Library\Render;

use Base\Library\KQL\SWIFT_KQLParserResult;
use Base\Library\KQL2\SWIFT_KQL2;
use Base\Library\KQL2\SWIFT_KQL2Compiler;
use News\Admin\LoaderMock;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionException;
use SWIFT_Exception;
use SWIFT_Report;
use SWIFT_ReportBase;
use SWIFT_ReportRender;

/**
 * Class SWIFT_ReportRenderTest
 * @group reports
 */
class SWIFT_ReportRenderTest extends \SWIFT_TestCase
{

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderProcessReturnsReport(): void
    {
        $obj = $this->getMocked();

        $reportObj = $this->getMockBuilder(SWIFT_Report::class)
            ->disableOriginalConstructor()
            ->getMock();
        $reportObj->method('GetIsClassLoaded')->willReturn(true);
        $reportObj->method('GetProperty')->willReturnCallback(static function ($x) {
            if ($x === 'kql') {
                return 'select 1';
            }

            return '';
        });

        $this->assertInstanceOf(SWIFT_ReportBase::class, $obj::Process($reportObj));
    }

    public function testProcessColumnValue() {
        $textWithHTMLTags = "<p>Hi</p> this is<br/>text <b>with</b> HTML<br />Tags";
        $response = SWIFT_ReportRender::ProcessColumnValue($textWithHTMLTags);
        $this->assertEquals($response, "Hi this is\ntext with HTML\nTags");
    }

    /**
     * @throws ReflectionException
     */
    public function testRenderColumnValueEscapesHtmlInValue()
    {
        $obj = $this->getMocked();
        $this->mockProperty($obj, '_aliasesToFieldsMap', ['_cf_6' => 'customfield6']);
        $method = $this->getMethod($obj, 'RenderColumnValue');
        $this->assertEquals('&lt;title&gt;', $method->invoke($obj, '_cf_6', '<title>'));
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|SWIFT_ReportRenderMock
     */
    private function getMocked()
    {
        $kqlComp = $this->getMockBuilder(SWIFT_KQL2Compiler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $kqlComp->method('GetExpressionByColumnName')->willReturn('_cf_6');
        $kqlComp->method('GetIsClassLoaded')->willReturn(true);

        $kqlObj = $this->getMockBuilder(SWIFT_KQL2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockProperty($kqlObj, 'Compiler', $kqlComp);
        $kqlObj->method('GetIsClassLoaded')->willReturn(true);

        $reportObj = $this->getMockBuilder(SWIFT_Report::class)
            ->disableOriginalConstructor()
            ->getMock();
        $reportObj->method('GetIsClassLoaded')->willReturn(true);

        $parserObj = $this->getMockBuilder(SWIFT_KQLParserResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parserObj->method('GetIsClassLoaded')->willReturn(true);

        return $this->getMockObject('Reports\Library\Render\SWIFT_ReportRenderMock', [
            'KQLObject'                   => $kqlObj,
            'SWIFT_KQL2Object'            => $kqlObj,
            'SWIFT_ReportObject'          => $reportObj,
            'SWIFT_KQLParserResultObject' => $parserObj,
        ]);
    }
}

class SWIFT_ReportRenderMock extends SWIFT_ReportRender
{
    /**
     * SWIFT_ReportRenderMock constructor.
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->Load = new LoaderMock();
        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }
        $this->SetIsClassLoaded(true);
        parent::__construct($this->SWIFT_KQL2Object, $this->SWIFT_ReportObject, $this->SWIFT_KQLParserResultObject);
    }

    /**
     * @return bool
     */
    public function Initialize()
    {
        return true;
    }
}
