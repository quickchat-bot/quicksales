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
 * The Default Controller for Setup Interface
 *
 * @property SWIFT_Compressor $Compressor
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
class Controller_Default extends SWIFT_Controller
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Cache:CacheManager');
        $this->Language->Load('setup');
    }

    /**
     * The Index Function (render the option menu here)
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Index()
    {
        $_productInstalled = $_registryContainer = false;

        $_queryResult = $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "registry WHERE vkey = 'settingscache'", 1, true);

        if ($_queryResult)
        {
            $_registryContainer = $this->Database->NextRecord();
        }

        if (isset($_registryContainer['data']) && !empty($_registryContainer['data']))
        {
            $_productInstalled = true;
        }

        $this->Template->Assign('_productInstalled', $_productInstalled);
        $this->Template->Assign('_productName', SWIFT_PRODUCT);

        $this->Template->Render('setup_index');

        SWIFT_CacheManager::EmptyCacheDirectory();

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
    public function Compressor($_dispatchType, $_fileList = '')
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->Load->Library('Compressor:Compressor');
        $this->Compressor->Dispatch($_dispatchType, $_fileList);

        return true;
    }
}
?>
