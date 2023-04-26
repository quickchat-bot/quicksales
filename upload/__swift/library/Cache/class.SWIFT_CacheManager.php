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
 * The Cache Manager Class
 *
 * @author Varun Shoor
 */
class SWIFT_CacheManager extends SWIFT_Library
{
    const CACHE_FILE = 'SWIFT_CacheManager.cache';

    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Empties the Cache Directory
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EmptyCacheDirectory()
    {
        // Itterate through the cache directory and remove each file
        if ($_directoryHandle = opendir('./'. SWIFT_BASEDIRECTORY .'/'. SWIFT_CACHEDIRECTORY)) {
            while (false !== ($_fileName = readdir($_directoryHandle))) {
                $_filePath = './'. SWIFT_BASEDIRECTORY .'/'. SWIFT_CACHEDIRECTORY . '/' . $_fileName;

                if ($_fileName != '.' && $_fileName != '..' && $_fileName != 'index.html' && !is_dir($_filePath)) {
                    unlink($_filePath);
                }
            }

            closedir($_directoryHandle);
        }

        SWIFT_Loader::WarmupCache();

        return true;
    }

    /**
     * Run RebuildCache on all available models
     *
     * @author Varun Shoor
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RebuildEntireCache()
    {
        $_cacheList = self::RetrieveCacheList();

        $_rebuiltCacheList = array();

        foreach ($_cacheList['model'] as $_modelContainer) {
            $_modelLoadName = $_modelContainer[0];
            $_modelName = $_modelContainer[1];
            $_modelFilePath = $_modelContainer[2];
            $_appName = $_modelContainer[3];

            SWIFT_Loader::LoadModel($_modelLoadName, $_appName);

            $_className = prepend_library_namespace(explode(':', $_modelLoadName), $_modelName, 'SWIFT_' . $_modelName, 'Models', $_appName);

            $_rebuiltCacheList[] = $_className . '::RebuildCache()';

            call_user_func_array(array($_className, 'RebuildCache'), array());
        }

        foreach ($_cacheList['lib'] as $_libContainer) {
            $_libLoadName = $_libContainer[0];
            $_libName = $_libContainer[1];
            $_libFilePath = $_libContainer[2];
            $_appName = $_libContainer[3];

            SWIFT_Loader::LoadLibrary($_libLoadName, $_appName);

            $_className = prepend_library_namespace(explode(':', $_libLoadName), $_libName, 'SWIFT_' . $_libName, 'Library', $_appName);

            $_rebuiltCacheList[] = $_className . '::RebuildCache()' . SWIFT_CRLF;

            call_user_func_array(array($_className, 'RebuildCache'), array());
        }

        return $_rebuiltCacheList;
    }

    /**
     * Retrieve the list of models/libs
     *
     * @author Varun Shoor
     * @return array (modelLoadString, modelName, modelFilePath, appName)
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveCacheList()
    {
        $_SWIFT = SWIFT::GetInstance();

        chdir(SWIFT_BASEPATH);

        $_cachePath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . self::CACHE_FILE;

        $_cache = array();
        if (file_exists($_cachePath)) {
            $_cache = unserialize(file_get_contents($_cachePath));
            if (!SWIFT::IsDebug() && _is_array($_cache)) {
                return $_cache;
            }
        }

        $_returnContainer = array('lib' => array(), 'model' => array());

        $_appList = SWIFT_App::GetInstalledApps();
        foreach ($_appList as $_appName) {
            $_SWIFT_AppObject = false;

            try {
                $_SWIFT_AppObject = SWIFT_App::Get($_appName);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            }

            if (!$_SWIFT_AppObject instanceof SWIFT_App || !$_SWIFT_AppObject->GetIsClassLoaded()) {
                continue;
            }

            $_returnContainer['model'] = array_merge($_returnContainer['model'], $_SWIFT_AppObject->RetrieveFileList(SWIFT_App::FILETYPE_MODEL, 'RebuildCache'));

            $_returnContainer['lib'] = array_merge($_returnContainer['lib'], $_SWIFT_AppObject->RetrieveFileList(SWIFT_App::FILETYPE_LIBRARY, 'RebuildCache'));
        }

        $_cache = $_returnContainer;

        if (!SWIFT::IsDebug()) {
            file_put_contents($_cachePath, serialize($_cache));
        }

        return $_returnContainer;
    }
}
?>