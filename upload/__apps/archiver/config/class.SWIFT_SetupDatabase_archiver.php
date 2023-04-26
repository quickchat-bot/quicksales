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

use SWIFT_Exception;
use SWIFT_SetupDatabase;

/**
 * Class SWIFT_SetupDatabase_archiver
 */
class SWIFT_SetupDatabase_archiver extends SWIFT_SetupDatabase
{
    /**
     * SWIFT_SetupDatabase_archiver constructor.
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct('archiver');
    }
}
