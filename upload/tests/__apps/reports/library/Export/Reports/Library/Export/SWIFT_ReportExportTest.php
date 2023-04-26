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

namespace Reports\Library\Export;

use Base\Library\KQL\SWIFT_KQLParserResult;
use Base\Library\KQL2\SWIFT_KQL2;
use News\Admin\LoaderMock;
use PHPUnit_Framework_MockObject_MockObject;
use SWIFT_Exception;
use SWIFT_Report;
use SWIFT_ReportBase;
use SWIFT_ReportExport;

/**
 * Class SWIFT_ReportExportTest
 * @group reports
 */
class SWIFT_ReportExportTest extends \SWIFT_TestCase
{

    /**
     * @throws SWIFT_Exception
     */
    public function testExportProcessReturnsReport(): void
    {
        $obj = $this->getMocked();

        $reportObj = $this->getMockBuilder(SWIFT_Report::class)
            ->disableOriginalConstructor()
            ->getMock();
        $reportObj->method('GetIsClassLoaded')->willReturn(true);
        $reportObj->method('GetProperty')->willReturnCallback(static function($x) {
            if ($x === 'kql') {
                return 'select 1';
            }

            return '';
        });

        $this->assertInstanceOf(SWIFT_ReportBase::class, $obj::Process($reportObj));
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|SWIFT_ReportExportMock
     */
    private function getMocked()
    {
        $kqlObj = $this->getMockBuilder(SWIFT_KQL2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $kqlObj->method('GetIsClassLoaded')->willReturn(true);

        $reportObj = $this->getMockBuilder(SWIFT_Report::class)
            ->disableOriginalConstructor()
            ->getMock();
        $reportObj->method('GetIsClassLoaded')->willReturn(true);

        $parserObj = $this->getMockBuilder(SWIFT_KQLParserResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parserObj->method('GetIsClassLoaded')->willReturn(true);

        return $this->getMockObject('Reports\Library\Export\SWIFT_ReportExportMock', [
            'SWIFT_KQL2Object'            => $kqlObj,
            'SWIFT_ReportObject'          => $reportObj,
            'SWIFT_KQLParserResultObject' => $parserObj,
        ]);
    }
}

class SWIFT_ReportExportMock extends SWIFT_ReportExport
{
    /**
     * SWIFT_ReportExportMock constructor.
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
