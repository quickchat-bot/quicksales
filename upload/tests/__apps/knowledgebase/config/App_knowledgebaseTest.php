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

namespace Knowledgebase;

use SWIFT_Exception;

/**
 * Class App_knowledgebaseTest
 * @group knowledgebase
 */
class App_knowledgebaseTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testInitializeReturnsTrue()
    {
        $obj = new SWIFT_App_knowledgebase('knowledgebase');
        $this->assertInstanceOf('Knowledgebase\SWIFT_App_knowledgebase', $obj);

        $obj->SetIsClassLoaded(true);
        $this->assertTrue($obj->Initialize());
    }
}
