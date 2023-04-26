<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */


/**
 * Class SWIFT_ExceptionTest
 * @group exception
 */
class SWIFT_ExceptionTest extends SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance()
    {
        $obj = new SWIFT_Exception('testException');
        self::assertInstanceOf('SWIFT_Exception', $obj);
    }

    /**
     * @throws \ReflectionException
     */
    public function testRenderErrorDoesNotPrintStacktrace()
    {
	    $obj = new SWIFT_Exception('testException');
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('RenderError');
        $method->setAccessible(true);
        $this->expectOutputRegex('//');
	    $method->invoke($obj, 'exceptionTitle', 'exceptionDescription');
    }

}
