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

namespace Archiver\Library\Archiver;

use SWIFT_App;
use SWIFT_Exception;

/**
 * Class ArchiverHelpTest
 * @group archiver
 */
class ArchiverHelpTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testHelpLinkReturnsReadmeFile()
    {
        $obj = new SWIFT_ArchiverHelp();
        $this->assertInstanceOf('\Archiver\Library\Archiver\SWIFT_ArchiverHelp', $obj);

        $link = SWIFT_ArchiverHelp::RetrieveHelpLink('archive_manager');
        $this->assertContains('guide-to-setting-up-archiver-custom-app-in-kayako', $link);
    }
}
