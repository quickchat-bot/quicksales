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

namespace Tickets\Library\Bayesian;

use SWIFT;
use Tickets\Library\Bayesian\SWIFT_Bayesian_Exception;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * Thayes Bayesian Algorithm Class
 *
 * @author Varun Shoor
 */
class SWIFT_Bayesian extends SWIFT_Library
{
    private $_cacheCategories = false;
    private $_cacheBayesCategories = array();
    private $_cacheBayesCategoryIDList = array();
    static protected $_bayesCacheContainer = array();

    // Core Constants
    const BAYES_TRAIN = 1;
    const BAYES_UNTRAIN = 2;

    const BAYESMAX = 0.9999;
    const BAYESMIN = 0.0001;
    const BAYESUNKNOWN = 0.4;

    // max length of swbayeswords.word table column
    const MAX_WORD_LENGTH = 500;

    /**
     * Trains the Bayesian filter
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID to Train with
     * @param int $_bayesCategoryID The Bayesian Category ID
     * @param string $_text The Text to Train with
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Train($_ticketID, $_bayesCategoryID, $_text) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tokenContainer = static::Tokenize($_text);

        if (!_is_array($_tokenContainer)) {
            return false;
        }

        $_wordIDList = $this->GetWordIDList($_tokenContainer);
        $_tokenCounter = $this->GetTokenCounter($_tokenContainer);
        $_wordIDReference = $this->GetWordCategoryLink($_wordIDList, $_bayesCategoryID); // Get the words which are already linked to the category

        foreach ($_tokenContainer as $_token) {
            if (!isset($_wordIDList[strtolower($_token)])) { continue; }

            $_bayesWordID = $_wordIDList[$_token];

            if (!$_bayesWordID) {
                continue;
            } // KACT 5/pages/247                        // No Word ID? ERROR! ERROR!

            $_oldScore = (isset($_wordIDReference[$_bayesWordID])) ? $_wordIDReference[$_bayesWordID]['wordcount'] : 0;

            $this->UpdateWordToCategoryLink($_bayesWordID, $_bayesCategoryID, ($_tokenCounter[$_token]) + $_oldScore);
        }

        $this->UpdateProbabilities();

        return true;
    }

    /**
     * Untrains the Bayesian filter
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_bayesCategoryID The Category ID to Untrain with
     * @param string $_text The Text to Untrain
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Untrain($_ticketID, $_bayesCategoryID, $_text) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tokenContainer = static::Tokenize($_text);
        if (!_is_array($_tokenContainer)) {
            return false;
        }

        $_wordIDList = $this->GetWordIDList($_tokenContainer); // Get the Word IDs
        $_tokenCounter = $this->GetTokenCounter($_tokenContainer); // Count the tokens in use
        $_wordIDReference = $this->GetWordCategoryLink($_wordIDList, $_bayesCategoryID); // Now get the words which are already linked to the category

        foreach ($_tokenContainer as $_token) {
            if (!isset($_wordIDList[strtolower($_token)])) { continue; }

            $_bayesWordID = $_wordIDList[$_token];

            if (!$_bayesWordID) {
                continue;
            } // TRFC KACT5/pages/247; No Word ID? ERROR! ERROR!

            if (!isset($_wordIDReference[$_bayesWordID])) {
                continue;
            } // Is this word already linked to this category?  If so skip to next.e

            $_referenceCount = ($_wordIDReference[$_bayesWordID]['wordcount']); // get the count of this token, total, as the reference counter
            $_textCount = ($_tokenCounter[$_token]); // get the count in this instance, to be removed

            if ($_referenceCount <= $_textCount) { // Reference counter is less than or equal to current text's count?
                $this->UnlinkWordToCategory($_bayesWordID, $_bayesCategoryID); // nuke the entire link

            } else { // Otherwise, it's more than in current text
                $this->UpdateWordToCategoryLink($_bayesWordID, $_bayesCategoryID, $_referenceCount - $_textCount); // so deduct current text's count from reference counter
            }
        }

        $this->UpdateProbabilities();

        return true;
    }

    /**
     * Get the Expected Probabilities for a given text
     *
     * @author Varun Shoor
     * @param string $_text The Text to get Probability for
     * @return array|bool (combinedProbability, finalWordProbabilityList)
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_text) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_contentHash = md5($_text);
        if (isset(static::$_bayesCacheContainer[$_contentHash])) {
            return static::$_bayesCacheContainer[$_contentHash];
        }

        $_tokenContainer = static::Tokenize($_text);
        if (!_is_array($_tokenContainer)) {
            return false;
        }

        $_bayesScores = array();
        $_wordProbabilityList = array();
        $_finalWordProbabilityList = array();

        $_bayesCategoryContainer = array();
        $_bayesCategoryIDList = array();

        list($_bayesCategoryContainer, $_bayesCategoryIDList) = $this->GetCategories(true);
        $_categoryCount = count($_bayesCategoryContainer);

        $_totalWordCount = 0;
        foreach ($_bayesCategoryContainer as $_thisCategory) {
            $_totalWordCount += $_thisCategory['wordcount'];
        }

        $_wordIDList = $this->GetWordIDList($_tokenContainer);
        $_tokenCounter = $this->GetTokenCounter($_tokenContainer);
        $_wordIDReference = $this->GetWordCategoryLinkGrouped($_wordIDList, $_bayesCategoryIDList);

        foreach ($_tokenContainer as $_token) {
            if (!isset($_wordIDList[$_token]))
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                continue;
                // @codeCoverageIgnoreEnd
            }

            $_bayesWordID = $_wordIDList[$_token];

            if (!$_bayesWordID) {
                continue;
            } // TRFC KACT 5/pages/247   // No Word ID? ERROR! ERROR!

            if (!isset($_wordIDReference[$_bayesWordID])) {
                continue;
            } // Is this word already linked to this category?

            $_wordProbability = $this->GetWordProbability($_token, $_bayesWordID, $_bayesCategoryContainer, $_wordIDReference);
            $_wordProbabilityList[] = $_wordProbability;
            $_finalWordProbabilityList[$_token] = $_wordProbability;
        }

        $_combinedProbability = $this->CombineWordProbability($_bayesCategoryContainer, $_wordProbabilityList);

        $_result = array($_combinedProbability, $_finalWordProbabilityList);
        static::$_bayesCacheContainer[$_contentHash] = $_result;

        return $_result;
    }

    /**
     * Update the probability data for each category
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function UpdateProbabilities() {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_totalWordCount = 0;
        $_categoryCount = array();

        // First get the total word frequencies
        $this->Database->Query("SELECT bayescategoryid, SUM(wordcount) AS totalwords FROM " . TABLE_PREFIX . "bayeswordsfreqs
            GROUP BY bayescategoryid");
        while ($this->Database->NextRecord()) {
            $_totalWordCount += $this->Database->Record['totalwords']; // TRFC KACT 5/pages/246
            $_categoryCount[$this->Database->Record['bayescategoryid']] = ($this->Database->Record['totalwords']); // TRFC KACT 5/pages/246
        }

        // No word count?
        if (!$_totalWordCount) {
            $this->Database->AutoExecute(TABLE_PREFIX . 'bayescategories', array('wordcount' => '0', 'probability' => '0'), 'UPDATE', '1=1');
            return true;
        }

        foreach ($_categoryCount as $_key => $_val) {
            $mainProbability = $_val / $_totalWordCount;
            $this->Database->AutoExecute(TABLE_PREFIX . 'bayescategories', array('wordcount' => $_val, 'probability' => $mainProbability), 'UPDATE',
                    "bayescategoryid = '" . ($_key) . "'");
        }

        return true;
    }

    /**
     * Combine the Word Probability to return a single probability
     *
     * @author Varun Shoor
     * @param array $_bayesCategoryContainer The Bayesian Category Container Array
     * @param array $_wordProbabilityList The Bayesian Word Probability List
     * @return array The Combined Probability
     * @throws SWIFT_Bayesian_Exception If the Class is not Loaded
     */
    private function CombineWordProbability($_bayesCategoryContainer, $_wordProbabilityList) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_combinedProbability = array();

        foreach ($_wordProbabilityList as $_key => $_val) {
            foreach ($_val as $_wordKey => $_wordVal) {
                if (!isset($_combinedProbability[$_wordKey]['AB'])) {
                    $_combinedProbability[$_wordKey]['AB'] = 1;
                } // KACT 5/pages/258

                if (!isset($_combinedProbability[$_wordKey]['ZY'])) {
                    $_combinedProbability[$_wordKey]['ZY'] = 1;
                }

                $_combinedProbability[$_wordKey]['AB'] *= $_wordVal;
                $_combinedProbability[$_wordKey]['ZY'] *= (1 - $_wordVal);
            }
        }

        foreach ($_bayesCategoryContainer as $_key => $_val) {
            if (!isset($_combinedProbability[$_key]))
            {
                $_combinedProbability[$_key]['combined'] = static::BAYESMIN;
                continue;
            }

            $_total = ($_combinedProbability[$_key]['AB'] + $_combinedProbability[$_key]['ZY']);
            if (!$_total) {
                $_combinedProbability[$_key]['combined'] = static::BAYESMIN;
                continue;
            }

            $_combinedProbability[$_key]['combined'] = $_combinedProbability[$_key]['AB'] / $_total;

            if ($_combinedProbability[$_key]['combined'] > static::BAYESMAX) {
                $_combinedProbability[$_key]['combined'] = static::BAYESMAX;

            } else if ($_combinedProbability[$_key]['combined'] < static::BAYESMIN) {
                $_combinedProbability[$_key]['combined'] = static::BAYESMIN;
            }
        }

        return $_combinedProbability;
    }

    /**
     * Gets the probability for the given word
     */
    /**
     * Get the Probability for the Given Word
     *
     * @author Varun Shoor
     * @param string $_wordToken The Word Token
     * @param int $_bayesWordID The Bayesian Word ID
     * @param array $_bayesCategoryContainer The Bayesian Category Container
     * @param array $_bayesReference The Bayesian Refrence Table
     * @return float|array The Word Probability
     * @throws SWIFT_Bayesian_Exception If the Class is not Loaded
     */
    private function GetWordProbability($_wordToken, $_bayesWordID, $_bayesCategoryContainer, $_bayesReference) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_thresholdTotal = 0;
        $_wordProbability = array();

        foreach ($_bayesCategoryContainer as $_key => $_val) {
            $_categoryTotal = max($_val['wordcount'], 1);

            $_wordTotal = 0;
            if (isset($_bayesReference[$_bayesWordID][$_val['bayescategoryid']]))
            {
                $_wordTotal = ((int)$_bayesReference[$_bayesWordID][$_val['bayescategoryid']]['wordcount']) * $_val['categoryweight'];
            }

            $_threshold = ($_categoryTotal === 0)? 0 : min(($_wordTotal / $_categoryTotal), 1);

            $_thresholdTotal += $_threshold;
            $_wordProbability[$_val['bayescategoryid']] = static::BAYESUNKNOWN;
        }

        foreach ($_bayesCategoryContainer as $_key => $_val) {
            $_categoryTotal = max($_val['wordcount'], 1);
            $_wordTotal = 0;
            if (isset($_bayesReference[$_bayesWordID][$_val['bayescategoryid']]))
            {
                $_wordTotal = ($_bayesReference[$_bayesWordID][$_val['bayescategoryid']]['wordcount']) * $_val['categoryweight'];
            }
            $_threshold = ($_categoryTotal === 0)? 0 : min(($_wordTotal / $_categoryTotal), 1);
            $_loopWordProbability = ($_thresholdTotal === 0) ? 0 : max(min(($_threshold / $_thresholdTotal), static::BAYESMAX), static::BAYESMIN);
            $_wordProbability[$_val['bayescategoryid']] = $_loopWordProbability;
        }

        return $_wordProbability;
    }

    /**
     * Gets all the categories
     *
     * @author Varun Shoor
     * @param bool $_returnGrouped Return the Categories as Grouped
     * @return array The Category List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetCategories($_returnGrouped = false) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->_cacheCategories) {
            return ($_returnGrouped) ? array($this->_cacheBayesCategories, $this->_cacheBayesCategoryIDList) : $this->_cacheBayesCategories;
        }

        $_bayesCategoryContainer = array();
        $_bayesCategoryIDList = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "bayescategories ORDER BY bayescategoryid ASC");
        while ($this->Database->NextRecord()) {
            $_bayesCategoryContainer[$this->Database->Record['bayescategoryid']] = $this->Database->Record; // TRFC KACT 5/pages/246
            $_bayesCategoryIDList[] = $this->Database->Record['bayescategoryid']; // TRFC KACT 5/pages/246
        }

        $this->_cacheCategories = true;
        $this->_cacheBayesCategories = $_bayesCategoryContainer;
        $this->_cacheBayesCategoryIDList = $_bayesCategoryIDList;

        return ($_returnGrouped) ? array($_bayesCategoryContainer, $_bayesCategoryIDList) : $_bayesCategoryContainer;
    }

    /**
     * Unlink a bayes word to a given category
     *
     * @author Varun Shoor
     * @param int $_bayesWordID The Bayesian Word ID
     * @param int $_bayesCategoryID The Bayesian Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function UnlinkWordToCategory($_bayesWordID, $_bayesCategoryID) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "bayeswordsfreqs WHERE bayeswordid = '" . ($_bayesWordID) . "'
            AND bayescategoryid = '" . ($_bayesCategoryID) . "'");

        return true;
    }

    /**
     * Links a bayes word to a given category
     *
     * @author Varun Shoor
     * @param int $_bayesWordID The Bayesian Word ID
     * @param int $_bayesCategoryID The Bayesian Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Bayesian_Exception If the Class is not Loaded
     */
    private function LinkWordToCategory($_bayesWordID, $_bayesCategoryID) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        try
        {
            $this->Database->AutoExecute(TABLE_PREFIX . 'bayeswordsfreqs', array('bayeswordid' => ($_bayesWordID),
                'bayescategoryid' => ($_bayesCategoryID), 'wordcount' => 1), 'INSERT');

        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            return false;
        }

        return true;
    }

    /**
     * Increments a count for a bayes word to a given category
     *
     * @author Varun Shoor
     * @param int $_bayesWordID The Bayesian Word ID
     * @param int $_bayesCategoryID The Bayesian Category ID
     * @param int $_count The Updated Count
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function UpdateWordToCategoryLink($_bayesWordID, $_bayesCategoryID, $_count) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Replace(TABLE_PREFIX . 'bayeswordsfreqs', array('bayeswordid' => ($_bayesWordID),
            'bayescategoryid' => ($_bayesCategoryID), 'wordcount' => ($_count)), array('bayeswordid', 'bayescategoryid'));

        return true;
    }

    /**
     * Gets the words which are linked to the given category
     *
     * @author Varun Shoor
     * @param array $_bayesWordIDList The Bayesian Word ID List
     * @param int $_bayesCategoryID The Bayesian Category ID
     * @return bool|array "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function GetWordCategoryLink($_bayesWordIDList, $_bayesCategoryID) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_bayesWordFrequencyList = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "bayeswordsfreqs WHERE bayeswordid IN (" . BuildIN($_bayesWordIDList) . ")
            AND bayescategoryid = '" . ($_bayesCategoryID) . "'");
        while ($this->Database->NextRecord()) {
            $_bayesWordFrequencyList[$this->Database->Record['bayeswordid']] = $this->Database->Record;
        }

        return $_bayesWordFrequencyList;
    }

    /**
     * Gets the words which are linked to the given category
     *
     * @author Varun Shoor
     * @param array $_bayesWordIDList The Bayesian Word ID List
     * @param array $_bayesCategoryIDList The Bayesian Category ID List
     * @return array Retrieve the Bayesian Word Frequency based on the above parameters
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function GetWordCategoryLinkGrouped($_bayesWordIDList, $_bayesCategoryIDList) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "bayeswordsfreqs WHERE bayeswordid IN (" . BuildIN($_bayesWordIDList) . ")
            AND bayescategoryid IN(" . BuildIN($_bayesCategoryIDList) . ")");
        $_bayesWordFrequencyList = array();
        while ($this->Database->NextRecord()) {
            $_bayesWordFrequencyList[$this->Database->Record['bayeswordid']][$this->Database->Record['bayescategoryid']] = $this->Database->Record;
        }

        return $_bayesWordFrequencyList;
    }

    /**
     * Splits the words into the number of times they appear
     *
     * @author Varun Shoor
     * @param array $_tokenContainer The Token Container
     * @return array
     */
    public static function GetTokenCounter($_tokenContainer) {
        return array_count_values($_tokenContainer);
    }

    /**
     * Gets the word ids from a given chunk array
     *
     * @author Varun Shoor
     * @param array $_tokenContainer The TOken Container
     * @return array The Word ID List Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function GetWordIDList($_tokenContainer) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Bayesian_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_tokenContainer)) {
            return array();
        }

        $_wordIDList = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "bayeswords WHERE word IN (" . BuildIN($_tokenContainer) . ")");
        while ($this->Database->NextRecord()) {
            $_wordIDList[strtolower($this->Database->Record['word'])] = $this->Database->Record['bayeswordid'];
        }

        foreach ($_tokenContainer as $_token) {
            if (!isset($_wordIDList[strtolower($_token)])) {
                // TRFC KACT 5/pages/254
                try
                {
                    $this->Database->AutoExecute(TABLE_PREFIX . 'bayeswords', array('word' => $_token), 'INSERT');
                } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                    continue;
                }

                $_wordIDList[strtolower($_token)] = $this->Database->Insert_ID(); // the id of the term in the term table
            }
        }

        return $_wordIDList;
    }

    /**
     * Splits text into chunks and returns as array
     *
     * @author Varun Shoor
     * @param string $_text The Text to Process
     * @return array The Tokens
     */
    public static function Tokenize($_text) {
        // exclude very long words
        $_text = preg_replace(sprintf('/\S{%d,}/', static::MAX_WORD_LENGTH), '', $_text);
        $_tokensContainer = explode(' ', static::ReturnSanitizedText($_text));

        if (count($_tokensContainer) == 0) {
            // @codeCoverageIgnoreStart
            // this code will never be executed because explode always returns a value
            return array();
            // @codeCoverageIgnoreEnd
        }

        $_finalTokensContainer = array();

        foreach ($_tokensContainer as $_token) {
            if (static::ShouldIndexWord($_token)) {
                $_finalTokensContainer[] = strtolower($_token);
            }
        }

        return $_finalTokensContainer;
    }

    /**
     * Returns Boolean value depending upon the various settings configured on whether a word should be indexed or not
     *
     * @author Varun Shoor
     * @param string $_word The Word to Check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ShouldIndexWord($_word) {
        $_SWIFT = SWIFT::GetInstance();

        $_matches = array();
        $_wordLength = strlen($_word); // Cache length of word
        $_isNumber = preg_match("/^([0-9]+)$/", $_word); // Cache whether the word is just a number

        // Apply rules
        if (preg_match("/&#([0-9]+);/i", $_word)) {
            return true;
        }

        if ($_wordLength < $_SWIFT->Settings->Get('tb_minwordlength') || $_wordLength > $_SWIFT->Settings->Get('tb_maxwordlength')) {
            return false;
        } // if word is too short or too long to index

        if (in_array(strtolower($_word), static::GetStopWordContainer())) {
            return false;
        } // if the word is in the index-prohibited wordlist

        // number-only rules
        if ($_isNumber) {
            if ($_SWIFT->Settings->Get('tb_indexnumbers') != 1) {
                return false;
            } // Its a number and we arent supposed to index it

            if ($_wordLength < $_SWIFT->Settings->Get('tb_minnumberlength')) {
                return false;
            } // Its a number and its length is less than the minimum number length.. false false!
        }

        if (($_SWIFT->Settings->Get('tb_signores') == 1) && (!(preg_match('/^[a-z0-9]+$/i', $_word))) &&
                (!(preg_match('/&#(x[a-f0-9]+|[0-9]+);/i', trim($_word), $_matches)))) {
            return false;
        } // Word has symbol in it, return false

        return true;
    }

    /**
     * Returns a sanitized text for searches.. strips text of all symbols and returns a space delimited text
     *
     * @author Varun Shoor
     * @param string $_originalContents The Original Contents Container
     * @return string The Processed Text
     */
    public static function ReturnSanitizedText($_originalContents) {
        $_stopData = array("#\s+#s", "#(\r\n|\r|\n)#s", "/[^a-zA-Z0-9\-\_\s\x80-\xff\&\#;]/"); // POSIX Regexp clause to strip white spaces, words containing asterisks, new lines and all symbols
        $_replaceSpacePreg = array(" ", " ", ""); // replace above clauses with a space, a space or emptiness, respectively

        $_strippedContents = strip_tags($_originalContents); // Strip HTML Tags
        $_cleanedContents = preg_replace($_stopData, $_replaceSpacePreg, $_strippedContents); // Apply first pair of preg clauses
        $_finalContents = preg_replace('/&#(x[a-f0-9]+|[0-9]+);/i', ' &#$1; ', $_cleanedContents);

        return trim((static::HasUnicodeChars($_finalContents)) ? static::UnicodeToEntities(static::UTF8ToUnicode($_finalContents)) : $_finalContents);
    }

    /**
     * Splits the word into chars and returns true if unicode char is encountered
     *
     * @author Varun Shoor
     * @param string $_word The Word to Check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function HasUnicodeChars($_word) {
        for ($i = 0, $iC = strlen($_word); $i < $iC; ++ $i) {
            if (ord($_word[$i]) > 127) {
                return true;
            }
        }

        return false;
    }

    /**
     * Converts UTF8 data to unicode
     *
     * @author Varun Shoor
     * @param string $_string The UTF8 to Unicode Check
     * @return array Unicode Data Container
     */
    public static function UTF8ToUnicode($_string) {
        $_unicodeContainer = array();
        $_valuesContainer = array();
        $_lookingFor = 1;

        $strlen = strlen($_string);
        for ($i = 0; $i < $strlen; $i ++) {
            $_thisValue = ord($_string[$i]);

            if ($_thisValue < 128) {
                $_unicodeContainer[] = $_thisValue;
            } else {
                if (count($_valuesContainer) == 0)
                {
                    $_lookingFor = ($_thisValue < 224) ? 2 : 3;
                }

                $_valuesContainer[] = $_thisValue;
                if (count($_valuesContainer) == $_lookingFor) {
                    $_numberData = ($_lookingFor == 3) ? (($_valuesContainer[0] % 16) * 4096) + (($_valuesContainer[1] % 64) * 64) + ($_valuesContainer[2] % 64) : (($_valuesContainer[0] % 32) * 64) + ($_valuesContainer[1] % 64);

                    $_unicodeContainer[] = $_numberData;
                    $_valuesContainer = array();
                    $_lookingFor = 1;
                }
            }
        }

        return $_unicodeContainer;
    }

    /**
     * Converts unicode data to respective html entities
     *
     * @author Varun Shoor
     * @param array $_unicodeContainer The Unicode Value Container
     * @return string The Entities Text
     */
    public static function UnicodeToEntities($_unicodeContainer) {
        $_entities = '';

        if (!_is_array($_unicodeContainer))
        {
            return $_entities;
        }

        foreach ($_unicodeContainer as $_value) {
            $_entities .= ($_value > 127) ? "&#$_value;" : chr($_value);
        }

        return $_entities;
    }

    /**
     * Retrieve the Stop Words Container Array
     *
     * @author Varun Shoor
     * @return array The Stop Words Container Array
     */
    public static function GetStopWordContainer()
    {
        $_stopWordsContainer = array (
            '&amp', '&quot', 'a', 'a\'s', 'able', 'about', 'above', 'according', 'accordingly', 'across', 'actually', 'after', 'afterwards',
            'again', 'against', 'ain\'t', 'aint', 'all', 'allow', 'allows', 'almost', 'alone', 'along', 'already', 'also', 'although', 'always',
            'am', 'among', 'amongst', 'an', 'and', 'another', 'any', 'anybody', 'anyhow', 'anyone', 'anything', 'anyway', 'anyways', 'anywhere',
            'apart', 'appear', 'appreciate', 'appropriate', 'are', 'aren\'t', 'arent', 'around', 'as', 'aside', 'ask', 'asking', 'associated', 'at',
            'available', 'away', 'awfully', 'b', 'be', 'became', 'because', 'become', 'becomes', 'becoming', 'been', 'before', 'beforehand', 'behind',
            'being', 'believe', 'below', 'beside', 'besides', 'best', 'better', 'between', 'beyond', 'both', 'brief', 'but', 'by', 'c', 'c\'mon',
            'c\'s', 'came', 'can', 'can\'t', 'cannot', 'cant', 'cause', 'causes', 'certain', 'certainly', 'changes', 'clearly', 'cmon', 'co', 'com',
            'come', 'comes', 'concerning', 'consequently', 'consider', 'considering', 'contain', 'containing', 'contains', 'corresponding', 'could',
            'couldn\'t', 'couldnt', 'course', 'cs', 'currently', 'd', 'definitely', 'described', 'despite', 'did', 'didn\'t', 'didnt', 'different',
            'do', 'does', 'doesn\'t', 'doesnt', 'doing', 'don\'t', 'done', 'dont', 'down', 'downwards', 'during', 'e', 'each', 'edu', 'eg', 'eight',
            'either', 'else', 'elsewhere', 'enough', 'entirely', 'especially', 'et', 'etc', 'even', 'ever', 'every', 'everybody', 'everyone',
            'everything', 'everywhere', 'ex', 'exactly', 'example', 'except', 'f', 'far', 'few', 'fifth', 'first', 'five', 'followed', 'following',
            'follows', 'for', 'former', 'formerly', 'forth', 'four', 'from', 'further', 'furthermore', 'g', 'get', 'gets', 'getting', 'given',
            'gives', 'go', 'goes', 'going', 'gone', 'got', 'gotten', 'greetings', 'h', 'had', 'hadn\'t', 'hadnt', 'happens', 'hardly', 'has',
            'hasn\'t', 'hasnt', 'have', 'haven\'t', 'havent', 'having', 'he', 'he\'s', 'hello', 'help', 'hence', 'her', 'here', 'here\'s',
            'hereafter', 'hereby', 'herein', 'heres', 'hereupon', 'hers', 'herself', 'hes', 'hi', 'him', 'himself', 'his', 'hither', 'hopefully',
            'how', 'howbeit', 'however', 'i', 'i\'d', 'i\'ll', 'i\'m', 'i\'ve', 'id', 'ie', 'if', 'ignored', 'ill', 'im', 'immediate', 'in',
            'inasmuch', 'inc', 'indeed', 'indicate', 'indicated', 'indicates', 'inner', 'insofar', 'instead', 'into', 'inward', 'is', 'isn\'t',
            'isnt', 'ist', 'it', 'it\'d', 'it\'ll', 'it\'s', 'itd', 'itll', 'its', 'itself', 'ive', 'j', 'just', 'k', 'keep', 'keeps', 'kept',
            'know', 'known', 'knows', 'l', 'last', 'lately', 'later', 'latter', 'latterly', 'least', 'less', 'lest', 'let', 'let\'s', 'lets',
            'like', 'liked', 'likely', 'little', 'look', 'looking', 'looks', 'ltd', 'm', 'mainly', 'many', 'may', 'maybe', 'me', 'mean', 'meanwhile',
            'merely', 'might', 'more', 'moreover', 'most', 'mostly', 'much', 'must', 'my', 'myself', 'n', 'name', 'namely', 'nd', 'near', 'nearly',
            'necessary', 'need', 'needs', 'neither', 'never', 'nevertheless', 'new', 'next', 'nine', 'no', 'nobody', 'non', 'none', 'noone', 'nor',
            'normally', 'not', 'nothing', 'novel', 'now', 'nowhere', 'o', 'obviously', 'of', 'off', 'often', 'oh', 'ok', 'okay', 'old', 'on', 'once',
            'one', 'ones', 'only', 'onto', 'or', 'originally', 'other', 'others', 'otherwise', 'ought', 'our', 'ours', 'ourselves', 'out', 'outside',
            'over', 'overall', 'own', 'p', 'particular', 'particularly', 'per', 'perhaps', 'placed', 'please', 'plus', 'possible', 'posted',
            'presumably', 'probably', 'provides', 'q', 'que', 'quite', 'quote', 'qv', 'r', 'rather', 'rd', 're', 'really', 'reasonably', 'regarding',
            'regardless', 'regards', 'relatively', 'respectively', 'right', 's', 'said', 'same', 'saw', 'say', 'saying', 'says', 'second',
            'secondly', 'see', 'seeing', 'seem', 'seemed', 'seeming', 'seems', 'seen', 'self', 'selves', 'sensible', 'sent', 'serious', 'seriously',
            'seven', 'several', 'shall', 'she', 'should', 'shouldn\'t', 'shouldnt', 'since', 'six', 'so', 'some', 'somebody', 'somehow', 'someone',
            'something', 'sometime', 'sometimes', 'somewhat', 'somewhere', 'soon', 'sorry', 'specified', 'specify', 'specifying', 'still', 'sub',
            'such', 'sup', 'sure', 't', 't\'s', 'take', 'taken', 'tell', 'tends', 'th', 'than', 'thank', 'thanks', 'thanx', 'that', 'that\'s',
            'thats', 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'thence', 'there', 'there\'s', 'thereafter', 'thereby', 'therefore',
            'therein', 'theres', 'thereupon', 'these', 'they', 'they\'d', 'they\'ll', 'they\'re', 'they\'ve', 'theyd', 'theyll', 'theyre', 'theyve',
            'think', 'third', 'this', 'thorough', 'thoroughly', 'those', 'though', 'three', 'through', 'throughout', 'thru', 'thus', 'to',
            'together', 'too', 'took', 'toward', 'towards', 'tried', 'tries', 'truly', 'try', 'trying', 'ts', 'twice', 'two', 'u', 'un', 'under',
            'unfortunately', 'unless', 'unlikely', 'until', 'unto', 'up', 'upon', 'us', 'use', 'used', 'useful', 'uses', 'using', 'usually', 'v',
            'value', 'various', 'very', 'via', 'viz', 'vs', 'w', 'want', 'wants', 'was', 'wasn\'t', 'wasnt', 'way', 'we', 'we\'d', 'we\'ll',
            'we\'re', 'we\'ve', 'wed', 'welcome', 'well', 'went', 'were', 'weren\'t', 'werent', 'weve', 'what', 'what\'s', 'whatever', 'whats',
            'when', 'whence', 'whenever', 'where', 'where\'s', 'whereafter', 'whereas', 'whereby', 'wherein', 'wheres', 'whereupon', 'wherever',
            'whether', 'which', 'while', 'whither', 'who', 'who\'s', 'whoever', 'whole', 'whom', 'whos', 'whose', 'why', 'will', 'willing', 'wish',
            'with', 'within', 'without', 'won\'t', 'wonder', 'wont', 'would', 'wouldn\'t', 'wouldnt', 'x', 'y', 'yes', 'yet', 'you', 'you\'d',
            'you\'ll', 'you\'re', 'you\'ve', 'youd', 'youll', 'your', 'youre', 'yours', 'yourself', 'yourselves', 'youve', 'z', 'zero',
        );

        return $_stopWordsContainer;
    }
}
