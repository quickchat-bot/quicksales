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

namespace Base\Console;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_GeoIPTest
 * @group base
 * @group base-console
 */
class Controller_GeoIPTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\Console\Controller_GeoIP', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRebuildReturnsTrue()
    {
        $obj = $this->getMocked();

        fclose(fopen('./__swift/geoip/lite/isp.csv', 'w'));
        fclose(fopen('./__swift/geoip/lite/netspeed.csv', 'w'));
        fclose(fopen('./__swift/geoip/lite/organization.csv', 'w'));
        fclose(fopen('./__swift/geoip/lite/citylocation.csv', 'w'));
        fclose(fopen('./__swift/geoip/lite/cityblocks.csv', 'w'));

        $this->assertTrue($obj->Rebuild(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Rebuild');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_GeoIPMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Console\Controller_GeoIPMock', [
            'Console' => new ConsoleMock()
        ]);
    }
}

class Controller_GeoIPMock extends Controller_GeoIP
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

