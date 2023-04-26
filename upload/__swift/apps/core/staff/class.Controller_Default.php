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

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;

// TODO: Remove extra properties required by other Controller_default classes after adding namespaces

/**
 * The Default Controller
 *
 * @property SWIFT_Compressor $Compressor
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property SWIFT_XML $XML
 * @method _LoadTemplateGroup($_templateGroupName = '')
 * @method _DispatchConfirmation()
 * @method _DispatchError($_msg = '')
 * @method bool _ProcessNews()
 * @method bool _ProcessKnowledgebaseCategories()
 * @author Varun Shoor
 */
class Controller_Default extends \Controller_staff
{
    public function GetInfo() {
        return true;
    }

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

    /**
     * Rebuild the Core Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RebuildCache() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_cacheContainer = array();

        // First the cache directory
        SWIFT_CacheManager::EmptyCacheDirectory();
        $_cacheContainer[] = $this->Language->Get('clearedcachedirectory');

        // Wipe the opcache if available
        if (extension_loaded('opcache')) {
            echo 'Wipe opcache<br />';
            opcache_reset();
        }

        $_cacheContainer = array_merge($_cacheContainer, SWIFT_CacheManager::RebuildEntireCache());

        foreach ($_cacheContainer as $_cache) {
            echo $_cache . '<br />';
        }

        return true;
    }
}
