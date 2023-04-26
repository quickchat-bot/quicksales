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

namespace News;

/**
 * Class App_newsTest
 * @group news
 */
class App_newsTest extends \SWIFT_TestCase
{
    /**
     * @throws \SWIFT_Exception
     */
    public function testInitializeReturnsTrue()
    {
        $obj = new SWIFT_App_news('news');
        $this->assertInstanceOf('News\SWIFT_App_news', $obj);

        $obj->SetIsClassLoaded(true);
        $this->assertTrue($obj->Initialize());
    }
}
