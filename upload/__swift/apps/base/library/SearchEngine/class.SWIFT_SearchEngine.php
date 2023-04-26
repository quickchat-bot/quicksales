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

namespace Base\Library\SearchEngine;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;
use Base\Library\SearchEngine\SWIFT_SearchEngine_Exception;
use SWIFT_StringHTMLToText;

define('SEARCH_TABLE', "searchindex");
define('SEARCH_COL_ID', "id");
define('SEARCH_COL_OBJID', "objid");
define('SEARCH_COL_SUBOBJID', "subobjid");
define('SEARCH_COL_TYPE', "type");
define('SEARCH_COL_FULLTEXT', "ft");

// DB layer had a limitation which hinders fast MyISAM FULLTEXT Search, when using
// MySQL < 5.6.20. QuickSupport had implemented a strategy to overcome this limitation, by
// using "SearchIdentifier" within FULLTEXT contents. MySQL uses every word with length
// greater than 3 & and containing underscores & alphanumerics to create word index.
// When searching, identifier is passed with Search query and hence improves the searching.
define('SEARCH_IDENTIFIER_TYPE', '__SWIFTSEARCHENGINETYPE');

/**
 * @author    Ryan Lederman    <ryan.lederman@kayako.com>    (Feb 2011)
 * @author    John Haugeland (original)
 */
class SWIFT_SearchEngine extends SWIFT_Library
{
    protected $_maxResults = false;

    // Core Constants
    // If you add a constant, make sure to change IsValidType()
    const TYPE_TICKET = 1;
    const TYPE_KNOWLEDGEBASE = 2;
    const TYPE_CHAT = 3;
    const TYPE_TROUBLESHOOTER = 4;
    const TYPE_NEWS = 5;
    const TYPE_DOWNLOADS = 6;

    const MODE_AND = 1;
    const MODE_OR = 2;

    // Governs the maximum number of results retrieved when searching
    const MAX_SEARCH_RESULTS = 250;

    public function __construct()
    {
        parent::__construct();

        $this->_maxResults = self::MAX_SEARCH_RESULTS;
    }

    /**
     * Set the Max Results
     *
     * @author Varun Shoor
     * @param int $_maxResults
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetMaxResults($_maxResults)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_maxResults = $_maxResults;

        return true;
    }

    /**
     * Ensure a text type is valid
     *
     * @author Varun Shoor <varun.shoor@kayako.com>
     *
     * @param mixed $_textType The type to verify
     * @return bool
     */
    public static function IsValidType($_textType)
    {
        return ($_textType >= self::TYPE_TICKET && $_textType <= self::TYPE_DOWNLOADS);
    }


    /*
     * Public-facing CRUD methods  =============================================
     */


    public function Insert($objID, $subObjID, $objType, $text)
    {
        return $this->MySQLInsert($objID, $subObjID, $objType, $text);
    }

    public function Find($text, $objType, $objIDList = array(), $_useOR = true)
    {
        return $this->MySQLFind($text, $objType, $objIDList, $_useOR);
    }

    public function GetFindQuery($text, $objType, $objIDList = [], $_useOR = true, $showSubObject = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Clean up the data
        $text = SWIFT_SearchEngine::CleanSearchText($text);

        // Prepare Query
        $text = $this->PrepareQuery($this->Optimize($text), $objType);

        // If search text found empty, bail out
        if (empty($text)) {
            return false;
        }


        $_extendSQL = "";
        if (_is_array($objIDList)) {
            $_extendSQL = " AND " . SEARCH_COL_OBJID . " IN (" . BuildIN($objIDList, true) . ")";
        }

        $escape = $_SWIFT->Database->Escape($text);

        if (!$_useOR) {
            $escape = '"'.$escape.'"';
        }

        return "SELECT " . SEARCH_COL_OBJID . ($showSubObject ? ", " . SEARCH_COL_SUBOBJID : '') . " FROM " . TABLE_PREFIX . SEARCH_TABLE .
            " WHERE (" . SEARCH_COL_TYPE . " = '" . (int)($objType) . "'" . $_extendSQL . " AND MATCH (" . SEARCH_COL_FULLTEXT . ") AGAINST ('" . $escape . "' IN BOOLEAN MODE))";
    }

    public function FindByRelevance($text, $objType)
    {
        return $this->MySQLFindByRelevance($text, $objType);
    }

    public function Update($objID, $subObjID, $objType, $newText)
    {
        return $this->MySQLUpdate($objID, $subObjID, $objType, $newText);
    }

    public function Delete($objID, $subObjID, $objType)
    {
        return $this->MySQLDelete($objID, $subObjID, $objType);
    }

    public function DeleteAll($objType)
    {
        return $this->MySQLDelete(0, 0, $objType);
    }

    public function DeleteList($objIDList, $objType)
    {
        return $this->MySQLDeleteList($objIDList, $objType);
    }


    /*
     * End public-facing CRUD methods =============================================
     */


    /*
     * MySQL-specific =============================================
     */


    protected function MySQLInsert($objID, $subObjID, $objType, $text)
    {
        $_SWIFT = $this->InitQueryMethod($objType);

        if (!$objID)
            throw new SWIFT_SearchEngine_Exception(SWIFT_INVALIDDATA);

        // Clean up the data
        $text = SWIFT_SearchEngine::CleanSearchText($text);

        $objType = (int)($objType);
        $text = $_SWIFT->Database->Escape($text);

        // Prepare Text
        $text = $this->Optimize($text);
        $text .= ' ' . SEARCH_IDENTIFIER_TYPE . $objType;

        // Insert into the search index
        if (false === $_SWIFT->Database->AutoExecute(TABLE_PREFIX . SEARCH_TABLE,
                array(
                    SEARCH_COL_OBJID => $objID,
                    SEARCH_COL_SUBOBJID => $subObjID,
                    SEARCH_COL_TYPE => $objType,
                    SEARCH_COL_FULLTEXT => $text
                ), 'INSERT')) {
            throw new SWIFT_SearchEngine_Exception(SWIFT_DATABASEERROR);
        }


        // Viola!
        return true;
    }

    protected function MySQLFind($text, $objType, $objIDList = array(), $_useOR = true)
    {
        $_SWIFT = $this->InitQueryMethod($objType);

        $arrReturn = array();

        $query = $this->GetFindQuery($text, $objType, $objIDList, $_useOR);
        if ($query === false)
            return $arrReturn;

        if (!$_SWIFT->Database->QueryLimit($query, $this->_maxResults)) {
            throw new SWIFT_SearchEngine_Exception(SWIFT_DATABASEERROR);
        }

        while ($_SWIFT->Database->NextRecord()) {
            $arrReturn[] = $_SWIFT->Database->Record;
        }

        return $arrReturn;
    }

    protected function MySQLFindByRelevance($text, $objType)
    {
        $_SWIFT = $this->InitQueryMethod($objType);

        $arrReturn = array();

        // Clean up the data
        $text = SWIFT_SearchEngine::CleanSearchText($text);

        // Prepare Query
        $text = $this->PrepareQuery($this->Optimize($text), $objType);

        // If search text found empty, bail out
        if (empty($text)) {
            return $arrReturn;
        }

        //SELECT objid, subobjid, ft, MATCH (ft) AGAINST ('internet explorer') AS relevance FROM swsearchindex WHERE MATCH (ft) AGAINST ('internet explorer' IN BOOLEAN MODE) ORDER BY relevance DESC LIMIT 0 , 300
        if (!$_SWIFT->Database->QueryLimit("SELECT " . SEARCH_COL_OBJID . ", " . SEARCH_COL_SUBOBJID . ", MATCH (" . SEARCH_COL_FULLTEXT . ") AGAINST ('" . $_SWIFT->Database->Escape($text) . "') AS relevance FROM " . TABLE_PREFIX . SEARCH_TABLE .
            " WHERE (" . SEARCH_COL_TYPE . " = '" . (int)($objType) . "' AND MATCH (" . SEARCH_COL_FULLTEXT . ") AGAINST ('" . $_SWIFT->Database->Escape($text) . "' IN BOOLEAN MODE))" .
            " ORDER BY relevance DESC", $this->_maxResults)) {
            throw new SWIFT_SearchEngine_Exception(SWIFT_DATABASEERROR);
        }

        while ($_SWIFT->Database->NextRecord()) {
            $arrReturn[] = $_SWIFT->Database->Record;
        }

        return $arrReturn;
    }

    protected function MySQLUpdate($objID, $subObjID, $objType, $newText)
    {
        $_SWIFT = $this->InitQueryMethod($objType);

        if (!$objID)
            throw new SWIFT_SearchEngine_Exception(SWIFT_INVALIDDATA);

        // Clean up the data
        $newText = SWIFT_SearchEngine::CleanSearchText($newText);

        $objType = (int)($objType);
        $newText = $_SWIFT->Database->Escape($newText);

        // Prepare Text
        $newText = $this->Optimize($newText);
        $newText .= ' ' . SEARCH_IDENTIFIER_TYPE . $objType;

        // Update the row and bail!
        return $_SWIFT->Database->AutoExecute(TABLE_PREFIX . SEARCH_TABLE, array(SEARCH_COL_FULLTEXT => $newText), 'UPDATE', $this->GenerateWHERE($objID, $subObjID, $objType));
    }

    protected function MySQLDelete($objID, $subObjID, $objType)
    {
        $_SWIFT = $this->InitQueryMethod($objType);

        // Clean up the data
        $objType = (int)($objType);

        return $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . SEARCH_TABLE . " WHERE (" . $this->GenerateWHERE($objID, $subObjID, $objType) . ");");
    }

    protected function MySQLDeleteList($objIDList, $objType)
    {
        $_SWIFT = $this->InitQueryMethod($objType);
        $objType = (int)($objType);

        return $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . SEARCH_TABLE . " WHERE (" . $this->GenerateWHEREIN($objIDList, $objType) . ");");
    }


    /*
     * End of MySQL-specific =============================================
     */


    protected function InitQueryMethod($objType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_SearchEngine_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!self::IsValidType($objType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return SWIFT::GetInstance();
    }

    protected function GenerateWHERE($objID, $subObjID, $objType)
    {
        // Always match on type
        $strWhere = SEARCH_COL_TYPE . " = '" . $objType . "'";

        if (0 < $objID) {
            // If the object ID is specified, include that
            $strWhere .= " AND " . SEARCH_COL_OBJID . " = '" . $objID . "'";
        }

        if (0 < $subObjID) {
            // If this is a sub-object, match on the object and sub-object IDs
            $strWhere .= " AND " . SEARCH_COL_SUBOBJID . " = '" . $subObjID . "'";
        }

        return $strWhere;
    }

    protected function GenerateWHEREIN($objIDList, $objType)
    {
        return $strWhere = SEARCH_COL_TYPE . " = '" . $objType . "' AND " . SEARCH_COL_OBJID . " IN (" . BuildIN($objIDList) . ")";
    }

    protected static function CleanSearchText($text)
    {
        return SWIFT_SearchEngine::StripStopWords(SWIFT_SearchEngine::StripHTMLAndJavaScript($text));
    }

    /*
     * Used to filter indexable data; strips common words.
     *
     * @author Ryan Lederman <ryan.lederman@kayako.com>
     *
     * @param $text string The data to strip words from
     * @return string The data, after all stop words have been removed
     */
    protected static function StripStopWords($text)
    {
        $arrStrip = array(
            /*
            * BUG FIX - Mansi Wason
            *
            * SWIFT-3371 Remove 'email' and 'emails' from the stop word list
            *
            * Comments - None
            */
            '&amp', '&quot', 'a', 'able', 'about', 'above', 'according', 'accordingly', 'across', 'actually',
            'after', 'afterwards', 'again', 'against', 'ain\'t', 'aint', 'all', 'allow', 'allows', 'almost', 'alone',
            'along', 'already', 'also', 'although', 'always', 'am', 'among', 'amongst', 'an', 'and', 'another', 'any',
            'anybody', 'anyhow', 'anyone', 'anything', 'anyway', 'anyways', 'anywhere', 'apart', 'appear', 'appreciate',
            'appropriate', 'are', 'aren\'t', 'arent', 'around', 'as', 'aside', 'ask', 'asking', 'associated', 'at',
            'available', 'away', 'awfully', 'be', 'became', 'because', 'become', 'becomes', 'becoming', 'been',
            'before', 'beforehand', 'behind', 'being', 'believe', 'below', 'beside', 'besides', 'best', 'better', 'between',
            'beyond', 'both', 'brief', 'but', 'by', 'c\'mon', 'came', 'can', 'can\'t', 'cannot', 'cant',
            'cause', 'causes', 'certain', 'certainly', 'changes', 'chat', 'clearly', 'cmon', 'co', 'com', 'come', 'comes',
            'concerning', 'consequently', 'consider', 'considering', 'contain', 'containing', 'contains', 'corresponding',
            'could', 'couldn\'t', 'couldnt', 'course', 'cs', 'currently', 'definitely', 'described', 'desk', 'despite', 'did',
            'didn\'t', 'didnt', 'different', 'do', 'does', 'doesn\'t', 'doesnt', 'doing', 'don\'t', 'done', 'dont', 'down',
            'downwards', 'during', 'each', 'edu', 'eg', 'eight', 'either', 'else', 'elsewhere', 'enough', 'entirely',
            'especially', 'et', 'etc', 'even', 'ever', 'every', 'everybody', 'everyone', 'everything', 'everywhere', 'ex',
            'exactly', 'example', 'except', 'far', 'few', 'fifth', 'first', 'five', 'followed', 'following', 'follows',
            'for', 'former', 'formerly', 'forth', 'four', 'from', 'further', 'furthermore', 'get', 'gets', 'getting',
            'given', 'gives', 'go', 'goes', 'going', 'gone', 'got', 'gotten', 'greetings', 'had', 'hadn\'t', 'hadnt',
            'happens', 'hardly', 'has', 'hasn\'t', 'hasnt', 'have', 'haven\'t', 'havent', 'having', 'he', 'he\'d', 'hello',
            'help', 'hence', 'her', 'here', 'here\'s', 'hereafter', 'hereby', 'herein', 'heres', 'hereupon', 'hers',
            'herself', 'hes', 'hi', 'him', 'himself', 'his', 'hither', 'hopefully', 'how', 'howbeit', 'however', 'http', 'https',
            'i', 'i\'ll', 'i\'d', 'i\'ve', 'id', 'ie', 'if', 'ignored', 'ill', 'im', 'immediate', 'in', 'inasmuch',
            'inc', 'indeed', 'indicate', 'indicated', 'indicates', 'inner', 'insofar', 'instead', 'into', 'inward', 'is',
            'isn\'t', 'isnt', 'ist', 'it', 'it\'s', 'it\'ll', 'it\'d', 'itd', 'itll', 'its', 'itself', 'ive', 'just',
            'keep', 'keeps', 'kept', 'know', 'known', 'knows', 'last', 'lately', 'later', 'latter', 'latterly',
            'least', 'less', 'lest', 'let', 'let\'s', 'lets', 'like', 'liked', 'likely', 'little', 'look', 'looking', 'looks',
            'ltd', 'mainly', 'many', 'may', 'maybe', 'me', 'mean', 'meanwhile', 'merely', 'might', 'more', 'moreover',
            'most', 'mostly', 'much', 'must', 'my', 'myself', 'name', 'namely', 'nd', 'near', 'nearly', 'necessary',
            'need', 'needs', 'neither', 'never', 'nevertheless', 'new', 'next', 'nine', 'no', 'nobody', 'non', 'none', 'noone',
            'nor', 'normally', 'not', 'nothing', 'now', 'nowhere', 'obviously', 'of', 'off', 'often', 'oh',
            'ok', 'okay', 'old', 'on', 'once', 'one', 'ones', 'only', 'onto', 'or', 'originally', 'other', 'others', 'otherwise',
            'ought', 'our', 'ours', 'ourselves', 'out', 'outside', 'over', 'overall', 'own', 'particular', 'particularly',
            'per', 'perhaps', 'placed', 'please', 'plus', 'possible', 'posted', 'presumably', 'probably', 'provides', 'que',
            'queue', 'quite', 'quote', 'qv', 'rather', 're', 'really', 'reasonably', 'regarding', 'regardless', 'regards',
            'relatively', 'respectively', 'right', 'said', 'same', 'saw', 'say', 'saying', 'says', 'second', 'secondly',
            'see', 'seeing', 'seem', 'seemed', 'seeming', 'seems', 'seen', 'self', 'selves', 'sensible', 'sent', 'serious',
            'seriously', 'seven', 'several', 'shall', 'she', 'should', 'shouldn\'t', 'shouldnt', 'since', 'six', 'so', 'some',
            'somebody', 'somehow', 'someone', 'something', 'sometime', 'sometimes', 'somewhat', 'somewhere', 'soon', 'sorry',
            'specified', 'specify', 'specifying', 'still', 'sub', 'submit', 'such', 'support', 'sure', 'take', 'taken', 'tell',
            'tends', 'than', 'thank', 'thanks', 'thanx', 'that', 'that\'s', 'thats', 'the', 'their', 'theirs', 'them',
            'themselves', 'then', 'thence', 'there', 'there\'s', 'thereafter', 'thereby', 'therefore', 'therein', 'theres',
            'thereupon', 'these', 'they', 'they\'d', 'they\'ll', 'they\'re', 'they\'ve', 'theyd', 'theyll', 'theyre', 'theyve',
            'think', 'third', 'this', 'thorough', 'thoroughly', 'those', 'though', 'three', 'through', 'throughout', 'thru',
            'thus', 'ticket', 'tickets', 'to', 'together', 'too', 'took', 'toward', 'towards', 'tried', 'tries', 'truly', 'try', 'trying',
            'twice', 'two', 'un', 'under', 'unfortunately', 'unless', 'unlikely', 'until', 'unto', 'up', 'upon', 'us',
            'use', 'used', 'useful', 'uses', 'using', 'usually', 'value', 'various', 'very', 'via', 'viz', 'vs', 'w',
            'want', 'wants', 'was', 'wasn\'t', 'wasnt', 'way', 'we', 'we\'ll', 'we\'ll', 'we\'re', 'we\'ve', 'wed', 'welcome',
            'well', 'went', 'were', 'weren\'t', 'werent', 'weve', 'what', 'what\'s', 'whatever', 'whats', 'when', 'whence',
            'whenever', 'where', 'where\'s', 'whereafter', 'whereas', 'whereby', 'wherein', 'wheres', 'whereupon', 'wherever',
            'whether', 'which', 'while', 'whither', 'who', 'who\'s', 'whoever', 'whole', 'whom', 'whos', 'whose', 'why', 'will',
            'willing', 'wish', 'with', 'within', 'without', 'won\'t', 'wonder', 'wont', 'would', 'wouldn\'t', 'wouldnt',
            'yes', 'yet', 'you', 'you\'d', 'you\'ll', 'you\'re', 'you\'ve', 'youd', 'youll', 'your', 'youre', 'yours', 'yourself',
            'yourselves', 'youve', 'zero', 'de', 'en', 'la', 'www', 'a\'s', 'c\'s', 'he\'s', 'i\'m', 'novel', 'rd', 'sup', 't\'s', 'th', 'we\'d'
        );

        foreach ($arrStrip as $stripWord) {

            /*
             * BUG FIX - Mansi Wason
             *
             * SWIFT-3377 - Issue with Advanced Search & umlauts characters.
             *
             * Comments - http://stackoverflow.com/questions/10590098/javascript-regexp-word-boundaries-unicode-characters (For Reference)
             */
            $regex = "@\b(?<=\s|^)" . $stripWord . "\b(?=\s|$)@im";
            $tmpText = preg_replace($regex, '', $text);

            // In case of error, skip.
            if (NULL == $tmpText) {
                continue;
            }

            $text = $tmpText;
        }

        return $text;
    }

    /*
     * Strips unneccessary data from database input
     *
     * @author Ryan Lederman <ryan.lederman@kayako.com>
     *
     * @param string $text The text to strip HTML and JS from.
     * @return Text after HTML and JavaScript has been removed.
     */
    protected static function StripHTMLAndJavaScript($text)
    {
        $_SWIFT_StringHTMLToTextObject = new SWIFT_StringHTMLToText();

        return $_SWIFT_StringHTMLToTextObject->Convert($text, false /* disable word-wrapping */);
    }

    /*
     * Pre-process the query for integrated type search
     *
     * @author Nidhi Gupta
     * @author Bishwanath Jha
     *
     * @param string $text
     * @param string $objtype
     *
     * @return string Prepared search query
     */
    protected function PrepareQuery($text, $objType)
    {
        $text = trim($text);
        if (empty($text) || !preg_match('/^[\w]+$/', $text)) {
            return $text;
        }

        // Double quote in a query had specific handling of 'literal' search. An unbalanced
        // (double) quote query would be removed by MySQL. Therefore, cleaning unbalanced
        // (double) quote.
        $text = preg_replace('/("(?=\S)[^"]*(?<=\S)")|"/', "$1", $text);

        $preparedText = '';

        // Prepare search query
        $preparedText .= ' +(' . $text . ') ';

        // Append Search Identifier
        $preparedText .= ' +(' . SEARCH_IDENTIFIER_TYPE . $objType . ')';

        return $preparedText;
    }

    /**
     * @author Utsav Handa <utsav.handa@kayako.com>
     *
     * @param string $content
     * @param int $minimumLength
     * @param int $maximumLength
     *
     * @return string
     */
    protected function Optimize($content, $minimumLength = 4, $maximumLength = 84)
    {
        if (empty($content) || !is_numeric($minimumLength) || !is_numeric($maximumLength)) {
            return $content;
        }

        // The minimum length prefix would be used to 'pad' words
        $minimumLengthPrefix = 'UH';

        // Minimum word length to support for words whose length is less than specified
        $optimizedContent = preg_replace_callback('/\b[\'\"a-zA-Z0-9&\:\.\?_\;\@]{2,' . $minimumLength . '}\b/', function ($matches) use ($minimumLength, $minimumLengthPrefix) {
            return str_pad($matches[0], $minimumLength, $minimumLengthPrefix);
        }, $content);

        // Maximum words length is applied for ensuring much longer words are removed
        if (is_numeric($maximumLength) && $maximumLength > 0) {
            $optimizedContent = preg_replace('/\b[\'\"a-zA-Z0-9&\:\.\?_\;\@\-]{' . $maximumLength . ',}\b/', '', $optimizedContent);
        }

        // Clean
        $optimizedContent = trim(preg_replace('/(\s|\-|\||\.|\,){2,}/', ' ', $optimizedContent));

        return $optimizedContent;
    }
}
