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

use Base\Models\PolicyLink\SWIFT_PolicyLink;

/**
 * The Default Client Controller
 *
 * @author Varun Shoor
 * @property SWIFT_XML $XML
 * @method RebuildCache()
 * @method GetInfo()
 * @method bool _ProcessNews()
 * @method bool _ProcessKnowledgebaseCategories()
 * @method _DispatchError($_msg = '')
 * @method _LoadTemplateGroup($_name = '')
 * @method _DispatchConfirmation()
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
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $languageID = $this->Cookie->GetVariable('client', 'languageid');
        $_registrationPolicyURL = SWIFT_PolicyLink::RetrieveURL($languageID);
        $this->Template->Assign('_registrationPolicyURL', $_registrationPolicyURL);

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
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        header('Content-Type: text/css');

        $this->Template->Render('clientcss');

        return true;
    }

    /**
     * The Compressor Dispatch
     *
     * @author Varun Shoor
     * @param mixed $_dispatchType (OPTIONAL) The Dispatch Type
     * @param string $_fileList (OPTIONAL) The File List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Compressor($_dispatchType = '', $_fileList = '')
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if ($_dispatchType == '') {
            return false;
        }

        $this->Load->Library('Compressor:Compressor');
        $this->Compressor->Dispatch($_dispatchType, $_fileList);

        return true;
    }
}
