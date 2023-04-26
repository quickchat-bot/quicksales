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

use Base\Library\Help\SWIFT_Help;

/**
 * Class SWIFT_ArchiverHelp
 */
class SWIFT_ArchiverHelp extends SWIFT_Help
{
    /**
     * @inheritdoc
     */
    public static function RetrieveHelpLink($_linkName)
    {
        parent::$_linkContainer['archive_manager'] = 'https://classic.kayako.com/article/1501-guide-to-setting-up-archiver-custom-app-in-kayako';

        return parent::RetrieveHelpLink($_linkName);
    }
}
