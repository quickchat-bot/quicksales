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

namespace Base\Models\CustomField;

use PHPUnit_Framework_MockObject_MockObject;
use SWIFT_Exception;
use SWIFT_TestCase;

/**
 * Class SWIFT_CustomFieldValueTest
 * @group base
 * @group base_models
 * @group customfield
 */
class SWIFT_CustomFieldValueTest extends SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance(): void
    {
        $obj = $this->getMocked();
        self::assertInstanceOf(SWIFT_CustomFieldValue::class, $obj);
    }

    public function testDuplicateCustomfieldsCreatesTicketFieldsOnly(): void
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();
        $_SWIFT->Database->method('QueryFetchAll')->willReturnCallback(function ($x) {
            self::assertContains('grouptype in (3,4,9)', $x);
        });

        $obj::DuplicateCustomfields(1, 2);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|SWIFT_CustomFieldValueMock
     */
    private function getMocked()
    {
        $dataMock = $this->createMock(\SWIFT_DataID::class);
        $dataMock->method('GetIsClassLoaded')->willReturn(true);
        $dataMock->method('GetDataID')->willReturn(1);
        $services = [
            'data' => $dataMock,
        ];
        return $this->getMockObject(SWIFT_CustomFieldValueMock::class, $services);
    }
}

class SWIFT_CustomFieldValueMock extends SWIFT_CustomFieldValue
{
    public function __construct($services = [])
    {
        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);
    }
}
