<?php
/**
 * ###############################################
 *
 * Archiver App for Kayako
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @package       archiver
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       https://github.com/trilogy-group/kayako-classic-archiver/blob/master/LICENSE
 * @link          https://github.com/trilogy-group/kayako-classic-archiver
 *
 * ###############################################
 */

namespace Archiver;

use SWIFT_App;
use SWIFT_Exception;

/**
 * Class SetupDatabase_archiverTest
 * @group archiver
 */
class SetupDatabase_archiverTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = new SWIFT_SetupDatabase_archiverMock();

        $this->assertInstanceOf('\Archiver\SWIFT_SetupDatabase_archiver', $obj);
    }
}

class SWIFT_SetupDatabase_archiverMock extends SWIFT_SetupDatabase_archiver
{
    protected function SetAppName($_appName)
    {
        return true;
    }

    protected function Initialize()
    {
        return true;
    }
}
