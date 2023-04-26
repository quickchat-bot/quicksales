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
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Knowledgebase\Rss;

use Controller_rss;
use Knowledgebase\Library\Rss\SWIFT_KnowledgebaseRSSManager;
use SWIFT_Exception;

/**
 * The News Feed Controller
 *
 * @author Varun Shoor
 *
 * @property SWIFT_KnowledgebaseRSSManager $KnowledgebaseRSSManager
 */
class Controller_Feed extends Controller_rss
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws \SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('RSS:KnowledgebaseRSSManager', [], true, false, APP_KNOWLEDGEBASE);
    }

    /**
     * Dispatch the RSS Feed
     *
     * @author Varun Shoor
     * @param int $_knowledgebaseCategoryID The Knowledgebase Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index($_knowledgebaseCategoryID = 0)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ((int)$this->Settings->Get('kb_enrss') === 0)
        {
            return false;
        }

        $this->KnowledgebaseRSSManager->Dispatch($_knowledgebaseCategoryID);

        return true;
    }
}
