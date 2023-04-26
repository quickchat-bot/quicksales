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

use Controller_client;
use SWIFT_Exception;
use SWIFT_XML;

/**
 * The Default Client Controller
 *
 * @property SWIFT_XML $XML
 * @method _DispatchError($_msg = '')
 * @method RebuildCache()
 * @method GetInfo()
 * @method bool _ProcessNews()
 * @method bool _ProcessKnowledgebaseCategories()
 * @method _DispatchConfirmation()
 * @method _LoadTemplateGroup($_templateGroupName = '')
 * @author Varun Shoor
 */
class Controller_Default extends Controller_client
{
    /**
     * The Client Index Page Rendering Function
     *
     * @author Varun Shoor
     * @param string $_templateGroupName (OPTIONAL) The Custom Template Group Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index($_templateGroupName = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->Template->Assign('_baseIndex', true);

        $this->_LoadTemplateGroup($_templateGroupName);

        $this->_ProcessNews();
        $this->_ProcessKnowledgebaseCategories();

        $this->UserInterface->Header('home');

        $this->Template->Render('homeindex');

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Dispatch the CSS
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function CSS()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        header('Content-Type: text/css');

        $this->Template->Render('clientcss');

        return true;
    }
}

?>
