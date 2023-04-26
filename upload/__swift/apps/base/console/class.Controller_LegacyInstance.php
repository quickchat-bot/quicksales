<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Mansi Wason
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2016, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Console;

use Controller_console;
use SWIFT_CacheManager;
use SWIFT_Exception;

/**
 * @author Mansi Wason <mansi.wason@kayako.com>
 */
class Controller_LegacyInstance extends Controller_console
{
    /**
     * Constructor
     *
     * @author Mansi Wason <mansi.wason@kayako.com>
     */
    public function __construct()
    {
        parent::__construct(false);
    }

    /**
     * @author Mansi Wason <mansi.wason@kayako.com>
     *
     * @param string $_productURL
     *
     * @return bool
     * @throws SWIFT_Exception
     */
    public function ChangeProductURL($_productURL)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_productURL = rtrim($_productURL, '/');
        $_productURL .= '/';

        $this->Database->AutoExecute(TABLE_PREFIX . 'settings', ['data' => '' . $_productURL . ''], 'UPDATE', 'section = "settings" and vkey = "general_producturl"');

        $_result = $this->Database->QueryFetch('SELECT data FROM ' . TABLE_PREFIX . 'registry where vkey = "settingscache"');

        foreach ($_result as $key => $val) {
            $_resultContainer = json_decode($val, true);

            str_replace(parse_url($_resultContainer['settings']['general_producturl']), $_productURL, $val);
        }

        SWIFT_CacheManager::EmptyCacheDirectory();
        SWIFT_CacheManager::RebuildEntireCache();

        return true;
    }
}

