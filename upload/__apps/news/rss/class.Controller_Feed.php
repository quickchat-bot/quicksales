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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace News\Rss;

use Controller_rss;
use News\Library\Rss\SWIFT_NewsRSSManager;
use SWIFT;
use SWIFT_Exception;

/**
 * The News Feed Controller
 *
 * @property SWIFT_NewsRSSManager $NewsRSSManager
 * @author Varun Shoor
 */
class Controller_Feed extends Controller_rss
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('RSS:NewsRSSManager', [], true, false, APP_KNOWLEDGEBASE);
    }

    /**
     * Dispatch the RSS Feed
     *
     * @author Varun Shoor
     * @param int $_newsCategoryID The News Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index($_newsCategoryID = 0)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->Settings->Get('nw_enablerss') == '0')
        {
            return false;
        }

        $this->NewsRSSManager->Dispatch($_newsCategoryID);

        return true;
    }
}
