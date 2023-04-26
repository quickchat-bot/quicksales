<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Varun Shoor
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The Template Engine Loader
 *
 * @author Varun Shoor
 */
class SWIFT_TemplateEngineLoader extends \Dwoo\Loader
{
    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct($_cacheDir)
    {
        parent::__construct($_cacheDir);
        $this->corePluginDir = DWOO_DIRECTORY . 'Plugins';
        $this->cacheDir = $_cacheDir . DIRECTORY_SEPARATOR;

        // include class paths or rebuild paths if the cache file isn't there
        $_classPathFile = $this->cacheDir . md5('classpath.cache.d' . \Dwoo\Core::RELEASE_TAG . SWIFT::Get('InstallationHash')) .'.php';

        $_fileContents = false;
        if (file_exists($_classPathFile))
        {
            $_fileContents = file_get_contents($_classPathFile);
        }

        if ($_fileContents) {
            $this->classPath = mb_unserialize($_fileContents) + $this->classPath;
        } else {
            $this->rebuildClassPathCache($this->corePluginDir, $_classPathFile);

            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-1003 An error in the templates causes irreversible warnings on On Demand helpdeks
             *
             * Comments: Made the system chmod the files to a global writable flag
             */
            @chmod($_classPathFile, 0666);
        }
    }
}
?>