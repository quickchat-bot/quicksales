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

namespace Knowledgebase\Admin;

use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use Base\Library\SearchEngine\SWIFT_SearchEngine;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;

/**
 * The Ticket Maintenance Controller
 *
 * @author Mahesh Salaria
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Maintenance $View
 *
 */
class Controller_Maintenance extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 5;

    /**
     * Constructor
     *
     * @author Mahesh Salaria
     * @throws \SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('knowledgebase');
        $this->Language->Load('admin_kbmaintenance');
    }

    /**
     * Render the Maintenance Tabs
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('knowledgebase') . ' > ' . $this->Language->Get('maintenance'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanrunmaintenance') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Re-Index the KB Articles
     *
     * @author Mahesh Salaria
     * @param int $_articlesPerPass Number of Articles to Process in a Single Pass
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ReIndex($_articlesPerPass, $_totalArticles = 0, $_startTime = 0, $_processCount = 0, $_firstpass = 1)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $bPerformDbOps = ($_firstpass == 1) ? false : true;

        if (!is_numeric($_articlesPerPass) || ($_articlesPerPass) <= 0)
        {
            $_articlesPerPass = 100;
        }

        $_SWIFT = SWIFT::GetInstance();

        if (!is_numeric($_totalArticles)) {
            $_totalArticles = 0;
        }

        if (empty($_totalArticles))
        {
                        $_kbArticleCount = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "kbarticledata");
            $_totalArticles = $_kbArticleCount['totalitems'];
            $_startTime       = DATENOW;

            if (!$bPerformDbOps)
            {
                $_SWIFT_SearchEngineObject = new SWIFT_SearchEngine();
                $_SWIFT_SearchEngineObject->DeleteAll(SWIFT_SearchEngine::TYPE_KNOWLEDGEBASE);
            }
        } else {
            $_startTime = ($_startTime);
        }
        // Cap in case pass size is greater than total count
        if ($_articlesPerPass > $_totalArticles)
        {
            $_articlesPerPass = $_totalArticles;
        }

        $_processCount = ($_processCount);
        if (empty($_processCount))
        {
            $_processCount = 0;
        }

        // Process this chunk of Articles in an iterative fashion; don't load them
        // into memory and then process.
        if ($bPerformDbOps)
        {
            $_SWIFT_SearchEngineObject = new SWIFT_SearchEngine();

            $this->Database->QueryLimit("SELECT kbdata.kbarticledataid, kbdata.kbarticleid, kbdata.contentstext, kbarticles.subject FROM " . TABLE_PREFIX . "kbarticledata AS kbdata LEFT JOIN " . TABLE_PREFIX . "kbarticles AS kbarticles ON (kbdata.kbarticleid = kbarticles.kbarticleid) ORDER BY kbdata.kbarticleid ASC", $_articlesPerPass, $_processCount);

            while ($this->Database->NextRecord())
            {
                $_SWIFT_SearchEngineObject->Insert($this->Database->Record['kbarticleid'], $this->Database->Record['kbarticledataid'], SWIFT_SearchEngine::TYPE_KNOWLEDGEBASE, $this->Database->Record['subject'] . " " . $this->Database->Record['contentstext']);
                $_processCount++;
            }
        }

        $_percent = 100;

                if (0 < $_totalArticles)
        {
            $_percent = floor(($_processCount * 100) / $_totalArticles);
        }

        $_articlesRemaining  = ($_totalArticles - $_processCount);
        $_averageArticleTime = 0;

        if (0 < $_processCount)
        {
            $_averageArticleTime = ((DATENOW - $_startTime) / $_processCount);
        }

        $_timeRemaining = ($_articlesRemaining * $_averageArticleTime);

        $_redirectURL = false;

        if ($_percent <= 100 && ($_processCount < $_totalArticles))
        {
            $_redirectURL = SWIFT::Get('basename') . '/Knowledgebase/Maintenance/ReIndex/' . ($_articlesPerPass) . '/' . ($_totalArticles) . '/' . ($_startTime) . '/' . ($_processCount) . '/0';
        }

        $this->View->RenderReIndexData($_percent, $_redirectURL, $_processCount, $_totalArticles, $_startTime, $_timeRemaining);

        return true;
    }
}
