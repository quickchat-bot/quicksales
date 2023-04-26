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

namespace Base\Library\Search;

use Base\Models\User\SWIFT_User;
use Knowledgebase\Library\Search\SWIFT_KnowledgebaseSearch;
use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Library;
use SWIFT_Loader;
use Tickets\Library\Search\SWIFT_TicketSearchManager;

/**
 * The Search Manager Class
 *
 * @author Varun Shoor
 */
class SWIFT_SearchManager extends SWIFT_Library
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Search for given query under the provided user group
     *
     * @author Varun Shoor
     * @param string $_searchQuery The Search Query
     * @param int $_userGroupID The User Group ID
     * @return array The Search Results Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Search($_searchQuery, $_userGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_searchQuery) || empty($_userGroupID)) {
            return array();
        }

        $_searchResultContainer = array();

        // Tickets Search
        if (SWIFT_App::IsInstalled(APP_TICKETS) && $_SWIFT->Session->IsLoggedIn() && $_SWIFT->User instanceof SWIFT_User) {
            SWIFT_Loader::LoadLibrary('Search:TicketSearchManager', APP_TICKETS);
            $_searchResultContainer = array_merge($_searchResultContainer, SWIFT_TicketSearchManager::SupportCenterSearch($_searchQuery));
        }

        // Knowledgebase Search
        if (SWIFT_App::IsInstalled(APP_KNOWLEDGEBASE)) {
            SWIFT_Loader::LoadLibrary('Search:KnowledgebaseSearch', APP_KNOWLEDGEBASE);
            $_searchResultContainer = array_merge($_searchResultContainer, SWIFT_KnowledgebaseSearch::Search($_searchQuery, $_userGroupID));
        }

        return $_searchResultContainer;
    }
}

?>
