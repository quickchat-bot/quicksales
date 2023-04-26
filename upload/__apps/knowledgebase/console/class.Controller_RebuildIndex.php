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

namespace Knowledgebase\Console;

use Controller_console;
use SWIFT;
use SWIFT_Console;
use SWIFT_Exception;
use Base\Library\SearchEngine\SWIFT_SearchEngine;

/**
 * The Search Engine Index Rebuild
 *
 * @author Varun Shoor
 */
class Controller_RebuildIndex extends Controller_console
{
    const DEFAULT_BATCH_SIZE = 100;
    const MAX_BATCH_SIZE     = 1000;

    /**
     * Start the Index Rebuilding
     *
     * @author Ryan Lederman
     * @param int $_startAt (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Start($_startAt = 0)
    {
        $_SWIFT = SWIFT::GetInstance();
        
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $c = &$this->Console;
        $s = SWIFT::GetInstance();

        $bProceed = false;

        $numArticles = $batchSize = 0;

        while (!$bProceed)
        {
            $confirm = $c->Prompt('This script rebuilds the search engine index for Knowledgebase articles.  It is HIGHLY RECOMMENDED that you back up the \'' . TABLE_PREFIX . 'searchindex\' table before proceeding!  Type "CONFIRM" to proceed or "Q" to quit: ');

            if (0 == strcasecmp("confirm", $confirm))
            {
                $bProceed = true;
            }
            else if (0 == strcasecmp("q", $confirm))
            {
                $c->Message('Ciao!');
                return true;
            }
        }

        $c->Message('Please wait, counting articles...', SWIFT_Console::CONSOLE_INFO);

        $_kbArticleCount = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "kbarticledata");

                if (isset($_kbArticleCount['totalitems']))
        {
            $numArticles = ($_kbArticleCount['totalitems']);
        }

        if (!$_kbArticleCount)
        {
            $c->Message('No Article found to index; quitting.', SWIFT_Console::CONSOLE_WARNING);
            return true;
        }

        $c->Message(number_format($numArticles, 0) . ' KB Articles found.');
        $_startAt = ($_startAt);

        while ($_startAt > $numArticles)
        {
            $c->Message('The specified start offset (' . number_format($_startAt, 0) . ') is greater than the total article count.', SWIFT_Console::CONSOLE_ERROR);

            $newStart = $c->Prompt('Please enter a start offset lower than ' . number_format($numArticles, 0) . ': ');

            $_startAt = ($newStart);
        }

        $bProceed = false;

        while (!$bProceed)
        {
            $c->Message('The batch size determines how many Knowledgebase articles are processed at a time.  If you experience issues with memory or timeout errors, try lowering the batch size.',
                    SWIFT_Console::CONSOLE_INFO);

            $batchSize = $c->Prompt('Enter the number of articles to process in each batch or press return to use the default size (' . self::DEFAULT_BATCH_SIZE . '): ');
            $batchSize = ('' === $batchSize) ? self::DEFAULT_BATCH_SIZE : ($batchSize);

            if (0 < $batchSize && $batchSize <= self::MAX_BATCH_SIZE)
            {
                if ($batchSize > $numArticles)
                    $batchSize = $numArticles;

                $bProceed = true;
            }
        }

        $bDelete = false;

        while (true)
        {
            $shouldDelete = $c->Prompt('Type "DELETE" to delete all existing  Knowledgebase articles search engine records (recommended if offset = 0), or hit return to skip: ');

            if ('' === $shouldDelete)
            {
                break;
            }

            if (0 == strcasecmp("delete", $shouldDelete))
            {
                $bDelete = true;
                break;
            }

        }

        if ($bDelete)
        {
            $c->Message('Deleting all existing Knowledgebase articles search engine records...');

            $se = new SWIFT_SearchEngine();
            $se->DeleteAll(SWIFT_SearchEngine::TYPE_KNOWLEDGEBASE);
        }

        $c->Message('Beginning index rebuild at offset ' . number_format($_startAt, 0) . ' with a batch size of ' . $batchSize . '...');

        $numDone   = 0;
        $startTime = 0;
        $endTime   = 0;
        $totalTime = 0;

        while (true)
        {
            // Measure the time each batch takes for better HCI
            $startTime = getmicrotime();

            if (!$s->Database->QueryLimit("SELECT kbdata.kbarticledataid, kbdata.kbarticleid, kbdata.contentstext, kbarticles.subject FROM " . TABLE_PREFIX . "kbarticledata AS kbdata LEFT JOIN " . TABLE_PREFIX . "kbarticles AS kbarticles ON (kbdata.kbarticleid = kbarticles.kbarticleid) ORDER BY kbdata.kbarticleid ASC", $batchSize, $_startAt + $numDone))
            {
                $c->Message('Database error! Quitting!', SWIFT_Console::CONSOLE_ERROR);
            }

            $bResults = false;
            $thisBatchSize = 0;

            $se = new SWIFT_SearchEngine();

            while ($s->Database->NextRecord())
            {
                $bResults = true;
                $se->Insert($s->Database->Record['kbarticleid'], $s->Database->Record['kbarticledataid'], SWIFT_SearchEngine::TYPE_KNOWLEDGEBASE, $s->Database->Record['subject'] . " " . $s->Database->Record['contentstext']);
                $numDone++;
                $thisBatchSize++;
            }

            unset($se);

            $endTime = getmicrotime();
            $totalTime += ($endTime - $startTime);
            $timeString = '';

            if ($totalTime < 60)
            {
                $timeString = number_format($totalTime, 2) . ' seconds';
            }
            else if ($totalTime >= 60 && $totalTime < 3600)
            {
                $timeString = number_format(($totalTime / 60), 2) . ' minutes';
            }
            else
            {
                $timeString = number_format(($totalTime / 60 / 60), 2) . ' hours';
            }

            if ($bResults)
            {
                $c->Message($thisBatchSize . ' processed, total: ' . number_format($numDone, 0) . ' (~' . number_format((($numDone * 100) / $numArticles), 0) . ' %) in ' . number_format(($endTime - $startTime), 3) . ' seconds; total elapsed: ' . $timeString . '; mem usage: ' . number_format(memory_get_usage() / 1024, 2) . ' KiB...');
            }
            else
            {
                // All done.
                break;
            }
        }

        $c->Message('All done! Ciao!');

        return true;
    }
}
