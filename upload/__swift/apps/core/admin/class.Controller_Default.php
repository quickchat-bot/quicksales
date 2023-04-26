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
 * The Default Controller
 *
 * @author Varun Shoor
 * @property SWIFT_XML $XML
 * @method _DispatchError($_msg = '')
 * @method _LoadTemplateGroup($_name = '')
 * @method _DispatchConfirmation()
 * @method GetInfo()
 * @method _ProcessNews()
 * @method _ProcessKnowledgebaseCategories()
 */
class Controller_Default extends Controller_admin
{
    /**
     * The Index Function
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Index()
    {
        return parent::_LoginIndex();
    }

    /**
     * Login the Staff Member
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Login()
    {
        return parent::_Login();
    }

    /**
     * Logout the Staff
     *
     * @author Varun Shoor
     * @param mixed $_logoutType The Logout Type
     * @return bool "true" on Success, "false" otherwise
     */
    public function Logout($_logoutType = false)
    {
        return parent::_Logout($_logoutType);
    }

    /**
     * The CSS Display Function
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function CSS()
    {
        return parent::_CSS();
    }

    /**
     * The Compressor Dispatch Function
     *
     * @author Varun Shoor
     * @param mixed $_dispatchType The Dispatch Type
     * @param string $_fileList (OPTIONAL) The File List
     * @return bool "true" on Success, "false" otherwise
     */
    public function Compressor($_dispatchType, $_fileList = '')
    {
        return parent::_Compressor($_dispatchType, $_fileList);
    }
}
?>
