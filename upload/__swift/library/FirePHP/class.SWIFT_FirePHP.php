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

include_once __DIR__ . '/ChromePhpWSE.php';

/**
 * FirePHP Connector Class
 *
 * @author Varun Shoor
 */
class SWIFT_FirePHP extends SWIFT_Base
{
    private $_FirePHPObject = false;

    private $_firePHPLoaded = false;

    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
        parent::__construct();

        if (defined('SWIFT_DEBUG') && constant('SWIFT_DEBUG') == true && SWIFT_INTERFACE != 'winapp' && SWIFT_INTERFACE != 'console' && SWIFT_INTERFACE != 'tests' && SWIFT_INTERFACE != 'setup')
        {
            $this->_firePHPLoaded = true;
            ChromePhpWSE::setEnabled(true) ;
            $this->_FirePHPObject = ChromePhpWSE::getInstance();

            ob_start();

        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Enable and disable logging to Firebug
     *
     * @see FirePHP->setEnabled()
     * @param boolean $Enabled TRUE to enable, FALSE to disable
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetEnabled($Enabled) {
        if (!$this->GetIsClassLoaded() || !$this->_firePHPLoaded)
        {
            return false;
        }

        $this->_FirePHPObject->setEnabled($Enabled);

        return true;
    }

    /**
     * Check if logging is enabled
     *
     * @see FirePHP->getEnabled()
     * @return bool "true" on Success, "false" otherwise
     */
    public function GetEnabled() {
        if (!$this->GetIsClassLoaded() || !$this->_firePHPLoaded)
        {
            return false;
        }

        return $this->_FirePHPObject->getEnabled();
    }

    /**
     * Specify a filter to be used when encoding an object
     *
     * Filters are used to exclude object members.
     *
     * @see FirePHP->setObjectFilter()
     * @param string $Class The class name of the object
     * @param array $Filter An array or members to exclude
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetObjectFilter($Class, $Filter) {
        if (!$this->GetIsClassLoaded() || !$this->_firePHPLoaded)
        {
            return false;
        }

        $this->_FirePHPObject->setObjectFilter($Class, $Filter);

        return true;
    }

    /**
     * Set some options for the library
     *
     * @see FirePHP->setOptions()
     * @param array $Options The options to be set
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetOptions($Options) {
        if (!$this->GetIsClassLoaded() || !$this->_firePHPLoaded)
        {
            return false;
        }

        $this->_FirePHPObject->setOptions($Options);

        return true;
    }

    /**
     * Get options for the library
     *
     * @see FirePHP->getOptions()
     * @return mixed "Options" (ARRAY) on Success, "false" otherwise
     */
    public function GetOptions() {
        if (!$this->GetIsClassLoaded() || !$this->_firePHPLoaded)
        {
            return false;
        }

        return $this->_FirePHPObject->getOptions();
    }

    /**
     * Log object to firebug
     *
     * @see http://www.firephp.org/Wiki/Reference/Fb
     * @return bool "true" on Success, "false" otherwise
     * @throws Exception
     */
    public function Send()
    {
        if (!$this->GetIsClassLoaded() || !$this->_firePHPLoaded)
        {
            return false;
        }

        $_argumentContainer = func_get_args();

        return call_user_func_array(array($this->_FirePHPObject, 'fb'), $_argumentContainer);
    }

    /**
     * Start a group for following messages
     *
     * Options:
     *   Collapsed: [true|false]
     *   Color:     [#RRGGBB|ColorName]
     *
     * @param string $Name
     * @param array $Options OPTIONAL Instructions on how to log the group
     * @return bool "true" on Success, "false" otherwise
     */
    public function Group($Name, $Options=null) {
        if (!$this->GetIsClassLoaded() || !$this->_firePHPLoaded)
        {
            return false;
        }

        return $this->_FirePHPObject->group($Name, $Options);
    }

    /**
     * Ends a group you have started before
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws Exception
     */
    public function GroupEnd() {
        if (!$this->GetIsClassLoaded() || !$this->_firePHPLoaded)
        {
            return false;
        }

        return $this->Send(null, null, ChromePhpWSE::GROUP_END);
    }

    /**
     * Log object with label to firebug console
     *
     * @see ChromePhpWSE::LOG
     * @param mixed $Object
     * @param string $Label
     * @return bool "true" on Success, "false" otherwise
     * @throws Exception
     */
    public function AddToLog($Object, $Label=null) {
        if (!$this->GetIsClassLoaded() || !$this->_firePHPLoaded)
        {
            return false;
        }

        if ($this->Log instanceof SWIFT_Log) {
            $this->Log((string) $Label);
        }

        return $this->Send($Object, $Label, ChromePhpWSE::LOG);
    }

    /**
     * Log object with label to firebug console
     *
     * @see ChromePhpWSE::INFO
     * @param mixed $Object
     * @param string $Label
     * @return bool "true" on Success, "false" otherwise
     * @throws Exception
     */
    public function Info($Object, $Label=null) {
        if (!$this->GetIsClassLoaded() || !$this->_firePHPLoaded)
        {
            return false;
        }

        return $this->Send($Object, $Label, ChromePhpWSE::INFO);
    }

    /**
     * Log object with label to firebug console
     *
     * @see ChromePhpWSE::WARN
     * @param mixed $Object
     * @param string $Label
     * @return bool
     * @throws Exception
     */
    public function Warn($Object, $Label=null) {
        if (!$this->GetIsClassLoaded() || !$this->_firePHPLoaded)
        {
            return false;
        }

        return $this->Send($Object, $Label, ChromePhpWSE::WARN);
    }

    /**
     * Log object with label to firebug console
     *
     * @see ChromePhpWSE::ERROR
     * @param mixed $Object
     * @param string $Label
     * @return bool "true" on Success, "false" otherwise
     * @throws Exception
     */
    public function Error($Object, $Label=null) {
        if (!$this->GetIsClassLoaded() || !$this->_firePHPLoaded)
        {
            return false;
        }

        return $this->Send($Object, $Label, ChromePhpWSE::ERROR);
    }

    /**
     * Dumps key and variable to firebug server panel
     *
     * @see ChromePhpWSE::DUMP
     * @param string $Key
     * @param mixed $Variable
     * @return bool
     * @throws Exception
     */
    public function Dump($Key, $Variable) {
        if (!$this->GetIsClassLoaded() || !$this->_firePHPLoaded)
        {
            return false;
        }

        return $this->Send($Variable, $Key, ChromePhpWSE::DUMP);
    }

    /**
     * Log a trace in the firebug console
     *
     * @see ChromePhpWSE::TRACE
     * @param string $Label
     * @return bool
     * @throws Exception
     */
    public function Trace($Label) {
        if (!$this->GetIsClassLoaded() || !$this->_firePHPLoaded)
        {
            return false;
        }

        return $this->Send($Label, ChromePhpWSE::TRACE);
    }

    /**
     * Log a table in the firebug console
     *
     * @see ChromePhpWSE::TABLE
     * @param string $Label
     * @param string $Table
     * @return bool
     * @throws Exception
     */
    public function Table($Label, $Table) {
        if (!$this->GetIsClassLoaded() || !$this->_firePHPLoaded)
        {
            return false;
        }

        return $this->Send($Table, $Label, ChromePhpWSE::TABLE);
    }
}
?>
