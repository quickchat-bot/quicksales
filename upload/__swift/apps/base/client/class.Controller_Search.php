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

namespace Base\Client;

use Base\Library\Search\SWIFT_SearchManager;
use Controller_client;
use SWIFT;
use SWIFT_Exception;

/**
 * The Search Controller
 *
 * @property SWIFT_SearchManager $SearchManager
 * @author Varun Shoor
 */
class Controller_Search extends Controller_client
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Search:SearchManager', [], true, false, 'base');
    }

    /**
     * Search submission processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // Check for empty fields..
        if (!isset($_POST['searchquery']) || trim($_POST['searchquery']) == '') {
            $this->UserInterface->CheckFields('searchquery');

            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));

            $this->Load->Controller('Default', 'Base')->Load->Index();

            return false;

        }

        $_searchResultContainer = $this->SearchManager->Search($_POST['searchquery'], SWIFT::Get('usergroupid'));
        $this->Template->Assign('_searchResultContainer', $_searchResultContainer);
        $this->Template->Assign('_searchResultCount', count($_searchResultContainer));
        $this->Template->Assign('_searchquery', urlencode($_POST['searchquery']));

        $this->UserInterface->Header('');

        $this->Template->Render('searchresults');

        $this->UserInterface->Footer();

        return true;
    }
}

?>
