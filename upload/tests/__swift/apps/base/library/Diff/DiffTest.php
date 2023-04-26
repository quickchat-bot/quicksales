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

namespace Base\Library\Diff;

use PHPUnit_Framework_MockObject_MockObject;
use SWIFT_TestCase;

/**
 * Class DiffTest
 * @group base
 * @group base_library
 */
class DiffTest extends SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance(): void
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf(Diff::class, $obj);
    }

    public function testCompareReturnsArray()
    {
        $obj = $this->getMocked();
        $this->assertNotEmpty($obj::compare('a', 'a'),
            'Returns diff array');
    }

    public function testCompareFilesReturnsArray()
    {
        $obj   = $this->getMocked();
        $file1 = tempnam(sys_get_temp_dir(), 'dt');
        file_put_contents($file1, 'a');
        $file2 = tempnam(sys_get_temp_dir(), 'dt');
        file_put_contents($file2, 'a');
        $this->assertNotEmpty($obj::compareFiles($file1, $file2),
            'Returns diff array');
        unlink($file1);
        unlink($file2);
    }

    public function testToHtmlReturnsString()
    {
        $obj  = $this->getMocked();
        $diff = $obj::compare('a', 'a');
        $this->assertContains('a', $obj::toHTML($diff),
            'Returns HTML');
    }

    public function testToStringReturnsString()
    {
        $obj  = $this->getMocked();
        $diff = $obj::compare('a', 'a');
        $this->assertContains('a', $obj::toString($diff),
            'Returns string');
    }

    public function testToTableReturnsString()
    {
        $obj  = $this->getMocked();
        $diff = $obj::compare('a', 'a');
        $this->assertContains('a', $obj::toTable($diff),
            'Returns table');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|DiffMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Library\Diff\DiffMock');
    }
}

class DiffMock extends Diff
{
    public function __construct($services = [])
    {

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }
    }
}

