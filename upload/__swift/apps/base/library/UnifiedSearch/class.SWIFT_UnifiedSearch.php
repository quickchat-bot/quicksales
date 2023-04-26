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

namespace Base\Library\UnifiedSearch;

use Base\Models\Staff\SWIFT_Staff;
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Library;
use SWIFT_Loader;
use SWIFT_Log;
use SWIFT_StringHighlighter;
use Base\Library\UnifiedSearch\SWIFT_UnifiedSearchResult;

/**
 * The Primary Unified Search Library
 *
 * @property SWIFT_StringHighlighter $StringHighlighter
 * @author Varun Shoor
 */
class SWIFT_UnifiedSearch extends SWIFT_Library
{
    protected $_unifiedSearchObjectContainer = array();

    const DEFAULT_RESULTS = 5;
    const MIN_LENGTH = 3;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('String:StringHighlighter');

        $this->Language->Load('unifiedsearch');
    }

    /**
     * Initiate a unified search
     *
     * @author Varun Shoor
     * @param string $_query The Search Query
     * @param mixed $_interfaceType The Interface Type
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param int $_maxResults (OPTIONAL)
     * @return array An array of unified search results
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Search($_query, $_interfaceType, SWIFT_Staff $_SWIFT_StaffObject, $_maxResults = self::DEFAULT_RESULTS)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (strlen($_query) < self::MIN_LENGTH && !is_numeric($_query)) {
            return array();
        }

        $_highlightQuery = str_replace(array('"', '\''), array('', ''), $_query);

        // Load the Unified Search objects
        $this->LoadUnifiedSearchObjects($_query, $_interfaceType, $_SWIFT_StaffObject, $_maxResults);

        // Run the search
        $_searchResults = $_finalSearchResults = array();

        foreach ($this->_unifiedSearchObjectContainer as $_SWIFT_UnifiedSearchBaseObject) {
            $_interimSearchResults = $_SWIFT_UnifiedSearchBaseObject->Search();

            foreach ($_interimSearchResults as $_searchTitle => $_returnSearchResults) {
                if (!isset($_searchResults[$_searchTitle])) {
                    $_searchResults[$_searchTitle] = array();
                }

                $_finalReturnSearchResults = array();
                foreach ($_returnSearchResults as $_index => $_returnSearchResultContainer) {
                    $_extendedDate = $_extendedInfo = '';
                    if (isset($_returnSearchResultContainer[2])) {
                        $_extendedDate = $_returnSearchResultContainer[2];
                        if (trim($_extendedDate) != '' && $_extendedDate != $this->Language->Get('edjustnow')) {
                            $_extendedDate .= ' ' . $this->Language->Get('ago');
                        }
                    }

                    if (isset($_returnSearchResultContainer[3])) {
                        $_extendedInfo = $this->StringHighlighter->Highlight($_returnSearchResultContainer[3], $_highlightQuery, SWIFT_StringHighlighter::HIGHLIGHT_SIMPLE, '<span class="searchighlightbold">\1</span>');
                    }

                    $_finalReturnSearchResults[$_index] = array($this->StringHighlighter->Highlight(StripName($_returnSearchResultContainer[0], 130), $_highlightQuery, SWIFT_StringHighlighter::HIGHLIGHT_SIMPLE, '<span class="searchighlightbold">\1</span>'), $_returnSearchResultContainer[1], $_extendedDate, $_extendedInfo);
                }

                $_searchResults[$_searchTitle] = array_merge($_searchResults[$_searchTitle], $_finalReturnSearchResults);
            }
        }

        ksort($_searchResults);

        foreach ($_searchResults as $_resultTitle => $_results) {
            if (!count($_results)) {
                continue;
            }

            $_finalSearchResults[$_resultTitle] = new SWIFT_UnifiedSearchResult($_resultTitle, '', $_results);
        }

        return $_finalSearchResults;
    }

    /**
     * Load the Unified Search Objects
     *
     * @author Varun Shoor
     * @param string $_query The Search Query
     * @param mixed $_interfaceType The Interface Type
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param int $_maxResults
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function LoadUnifiedSearchObjects($_query, $_interfaceType, SWIFT_Staff $_SWIFT_StaffObject, $_maxResults)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_installedAppList = SWIFT_App::GetInstalledApps();
        foreach ($_installedAppList as $_appName) {
            /**
             * BUG FIX - Simaranjit Singh
             *
             * SWIFT-2828: Unified search does not work if we have any custom app installed in database which has been directly deleted from "_apps" directory.
             */
            try {
                $_App = SWIFT_App::Get($_appName);

                if (!$_App instanceof SWIFT_App || !$_App->GetIsClassLoaded()) {
                    $this->Log('Unable to load ' . $_appName, SWIFT_Log::TYPE_WARNING);

                    continue;
                }
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                $this->Log('Unable to locate ' . $_appName . ' application in ./' . SWIFT_APPSDIRECTORY . ' directory', SWIFT_Log::TYPE_WARNING);

                continue;
            }

            $_appDirectory = $_App->GetDirectory();
            if (!is_dir($_appDirectory)) {
                continue;
            }

            $_unifiedSearchDirectory = $_appDirectory . '/' . SWIFT_LIBRARY_DIRECTORY . '/UnifiedSearch/';
            if (!is_dir($_unifiedSearchDirectory)) {
                continue;
            }

            $_directoryHandle = opendir($_unifiedSearchDirectory);
            if ($_directoryHandle) {
                while (false !== ($_fileName = readdir($_directoryHandle))) {
                    $_fileNameMatches = array();

                    if ($_fileName != '.' && $_fileName != '..' && preg_match('/^class.SWIFT_UnifiedSearch_(.*).php$/i', $_fileName, $_fileNameMatches)) {
                        $_libraryName = 'UnifiedSearch_' . $_fileNameMatches[1];
                        $_fullLibraryName = 'SWIFT_' . $_libraryName;

                        SWIFT_Loader::LoadLibrary('UnifiedSearch:' . $_libraryName, $_appName, true, $_unifiedSearchDirectory . $_fileName);

                        $_fullLibraryName = prepend_library_namespace(['UnifiedSearch', 'UnifiedSearch'], $_libraryName, $_fullLibraryName, 'Library', $_appName);

                        $this->_unifiedSearchObjectContainer[] = new $_fullLibraryName($_query, $_interfaceType, $_SWIFT_StaffObject, $_maxResults);
                    }
                }

                closedir($_directoryHandle);
            }
        }

        return true;
    }
}

?>
