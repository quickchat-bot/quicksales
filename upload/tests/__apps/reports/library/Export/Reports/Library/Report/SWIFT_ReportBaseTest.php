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

namespace Reports\Library\Report;

use Base\Library\KQL\SWIFT_KQLParserResult;
use Base\Library\KQL2\SWIFT_KQL2;
use News\Admin\LoaderMock;
use PHPUnit_Framework_MockObject_MockObject;
use SWIFT_Exception;
use SWIFT_Report;
use SWIFT_ReportBase;

/**
 * Class SWIFT_ReportBaseTest
 * @group reports
 */
class SWIFT_ReportBaseTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testSetAliasMapReturnsTrue(): void
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->SetAliasMap([]),
            'Returns true without errors');

        $this->assertClassNotLoaded($obj, 'SetAliasMap', []);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSetFunctionMapReturnsTrue(): void
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->SetFunctionMap([]),
            'Returns true without errors');

        $this->assertClassNotLoaded($obj, 'SetFunctionMap', []);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|SWIFT_ReportBaseMock
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

        return $this->getMockObject('Reports\Library\Report\SWIFT_ReportBaseMock', [
            'SWIFT_KQL2Object'            => $kqlObj,
            'SWIFT_ReportObject'          => $reportObj,
            'SWIFT_KQLParserResultObject' => $parserObj,
        ]);
    }
}

class SWIFT_ReportBaseMock extends SWIFT_ReportBase
{
    /**
     * SWIFT_ReportBaseMock constructor.
     * @param array $services
     * @throws SWIFT_Exception
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
