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

/**
 * The Default Visitor Controller
 *
 * @property SWIFT_XML $XML
 * @method _DispatchError($_msg = '')
 * @method _DispatchConfirmation()
 * @method _LoadTemplateGroup($_templateGroupName = '')
 * @method RebuildCache()
 * @method GetInfo()
 * @method bool _ProcessNews()
 * @method bool _ProcessKnowledgebaseCategories()
 * @author Varun Shoor
 */
class Controller_Default extends Controller_visitor
{
    /**
     * The Index Controller
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }


        return true;
    }

    /**
     * The Compressor Dispatch
     *
     * @author Varun Shoor
     * @param mixed $_dispatchType The Dispatch Type
     * @param string $_fileList (OPTIONAL) The File List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Compressor($_dispatchType = '', $_fileList = '')
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (empty($_dispatchType)) {
            return false;
        }

        $this->Load->Library('Compressor:Compressor');
        $this->Compressor->Dispatch($_dispatchType, $_fileList);

        return true;
    }
}
?>
