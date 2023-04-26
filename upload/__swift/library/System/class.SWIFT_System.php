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

/**
 * System Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_System extends SWIFT_Library
{
    const IFCFGETH0FILE = '/etc/sysconfig/network-scripts/ifcfg-eth0';

    /**
     * Constructor
     *
     * @author Varun Shoore
     * @throws SWIFT_System_Exception If the Class is not Loaded
    */
    public function __construct()
    {
        parent::__construct();
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
     * Gets the system IP Address
     *
     * @author Varun Shoor
     * @return object SWIFT_IPAddress object, "false" on failure
     * @throws SWIFT_System_Exception If the class is not loaded, or if the ETH0 IP File does not exist or If SWIFT_IPAddress Creation Fails
     */
    public function GetSystemIPAddress()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_System_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!file_exists(self::IFCFGETH0FILE) || !is_readable(self::IFCFGETH0FILE))
        {
            throw new SWIFT_System_Exception(self::IFCFGETH0FILE . ' does not exist');
        }

        $_containerArray = array();
        $_fileArray = file(self::IFCFGETH0FILE);

        $_argumentList = array('IPADDR', 'DEVICE', 'ONBOOT', 'HWADDR', 'NETMASK', 'GATEWAY', 'TYPE');

        foreach ($_fileArray as $key=>$val)
        {
            $_matches = array();
            if (preg_match('@^(.+)=(.*?)@U', $val, $_matches))
            {
                if (in_array(trim($_matches[1]), $_argumentList) && isset($_matches[2]))
                {
                    $_containerArray[trim($_matches[1])] = trim($_matches[2]);
                }
            }
        }

        $_SWIFT_IPAddressObject = new SWIFT_IPAddress($_containerArray['IPADDR'], $_containerArray['NETMASK'], $_containerArray['GATEWAY'], $_containerArray['TYPE'], $_containerArray['HWADDR'], $_containerArray['DEVICE'], IIF($_containerArray['ONBOOT'] == 'yes', true, false));

        if (!$_SWIFT_IPAddressObject instanceof SWIFT_IPAddress || !$_SWIFT_IPAddressObject->GetIsClassLoaded())
        {
            throw new SWIFT_System_Exception('SWIFT_IPAddress Creation Failed');
        }

        return $_SWIFT_IPAddressObject;
    }

    /**
     * Executes a given command
     *
     * @author Varun Shoor
     * @param string $_command The command to execute
     * @param string $_output (REFERENCE) The output being returned
     * @param string $_returnStatus (REFERENCE) The return status of command
     * @param bool $_noOutput If set to true then no output will be pushed to the console
     * @return integer The Return Status of the command
     * @throws SWIFT_System_Exception If the Class is not Loaded
     */
    public function Execute($_command, &$_output = '', &$_returnStatus = '', $_noOutput = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_System_Exception(SWIFT_CLASSNOTLOADED);
        }

        ob_start();
        passthru($_command, $_returnStatus);
        $_contents = ob_get_contents();

        if ($_noOutput)
        {
            ob_end_clean();
        } else {
            ob_end_flush();
        }
        $_output = explode("\n", $_contents);

        if ($this->Log instanceof SWIFT_Log)
        {
            $this->Log($_command . "\n" . $_contents);
        }

        return $_returnStatus;
    }

    /**
     * Kills the specified processes with a 'word' in them
     *
     * @author Varun Shoor
     * @param mixed $_wildcard The Wildcard Container
     * @return bool True on success, false otherwise
     * @throws SWIFT_System_Exception If the Class is not Loaded
     */
    public function KillAllProcessWithQuery($_wildcard)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_System_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_wildcard))
        {
            $_wildcard = array($_wildcard);
        }

        $_pidList = array();

        $_output = array();
        $_executeResult = false;

        $_pidList = $this->GetPIDList($_wildcard);

        if (count($_pidList))
        {
            $this->Execute('kill -9 ' . implode(' ', $_pidList) . ' 2>&1', $_output, $_executeResult);
            if ($_executeResult == '0')
            {
                $this->Console->Message('(QMAIL) Killed PID: ' . implode(' ,', $_pidList), SWIFT_Console::CONSOLE_MESSAGE);
            } else {
                $this->Console->Message('(QMAIL) Failed to Kill PID: ' . implode(' ,', $_pidList), SWIFT_Console::CONSOLE_WARNING);
            }
        }

        return true;
    }

    /**
     * Checks the specified processes with a 'word' in them
     *
     * @author Varun Shoor
     * @param mixed $_wildcard
     * @return bool True on success, false otherwise
     * @throws SWIFT_System_Exception If the Class is not Loaded
     */
    public function CheckAllProcessWithQuery($_wildcard)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_System_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!_is_array($_wildcard))
        {
            $_wildcard = array($_wildcard);
        }

        $_pidList = $this->GetPIDList($_wildcard);

        if (count($_pidList))
        {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the PID Process list based on Word
     *
     * @author Varun Shoor
     * @param mixed $_wildcard The Wildcard Container
     * @return mixed "_pidList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_System_Exception If the Class is not Loaded
     */
    protected function GetPIDList($_wildcard)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_System_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!_is_array($_wildcard))
        {
            $_wildcard = array($_wildcard);
        }

        $_pidList = array();

        $this->Execute('ps axo pid,comm,args 2>&1', $_output, $_executeResult, false);
        foreach ($_wildcard as $_wildcardKey => $_wildcardVal)
        {
            $this->Console->Message('Attempting to retrieve all process with the word: ' . $_wildcardVal, SWIFT_Console::CONSOLE_MESSAGE);

            if (is_array($_output))
            {
                foreach ($_output as $_key => $_val)
                {
                    $_matches = array();

                    $_val = trim($_val);
                    if (preg_match('@(.+?)[\s]+(.+?)[\s]+(.+)@', $_val, $_matches))
                    {
                        if (is_numeric($_matches[1]) && stristr($_val, $_wildcardVal))
                        {
                            $_pidList[] = (int) ($_matches[1]);
                        }
                    }
                }
            }
        }

        return $_pidList;
    }

    /**
     * Recursively deletes the directory
     *
     * @author Varun Shoor
     * @param string $_directory The path to directory
     * @return bool $_deleteContainer Deletes the container directory
     * @throws SWIFT_System_Exception If the Class is not Loaded
     */
    public function DeleteDirectory($_directory, $_deleteContainer = true)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_System_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!file_exists($_directory) || !is_dir($_directory))
        {
            return false;
        }

        if ($_deleteContainer)
        {
            $this->Execute('rm -rf ' . $_directory);
        } else {
            $this->Execute('rm -rf ' . $_directory . '/*');
        }

        return true;
    }

    /**
     * Reboot the machine
     *
     * @author Varun Shoor
     * @param bool $_promptUser
     * @return bool True on success, false otherwise
     * @throws SWIFT_System_Exception If the Class is not Loaded
     */
    function RebootServer($_promptUser = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_System_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if ($_promptUser) {
            $_promptResult = '';
            while ($_promptResult != 'y' && $_promptResult != 'n')
            {
                $_promptResult = strtolower($this->Console->Prompt('Would you like to proceed with the reboot? [y/n]:'));
            }

            if ($_promptResult == 'n')
            {
                return false;
            }
        }

        $_output = array();
        $_executeResult = false;

        $this->Console->Message('Attempting to reboot the server...', SWIFT_Console::CONSOLE_MESSAGE);

        $this->Execute('reboot 2>&1', $_output, $_executeResult);
        if ($_executeResult == '0')
        {
            $this->Console->Message('(REBOOT) Reboot initiated...', SWIFT_Console::CONSOLE_MESSAGE);
        } else {
            $this->Console->Message('(REBOOT) Failed to reboot the server', SWIFT_Console::CONSOLE_ERROR);

            return false;
        }

        return true;
    }

    /**
     * Synchronizes the timezone for the system
     *
     * @author Varun Shoor
     * @return bool True on success, false otherwise
     * @throws SWIFT_System_Exception If the Class is not Loaded
     */
    public function SynchronizeTime()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_System_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_output = array();
        $_executeResult = false;

        $this->Execute('ntpdate clock.fmt.he.net 2>&1', $_output, $_executeResult);
        $this->Execute('ntpdate clock.sjc.he.net 2>&1', $_output, $_executeResult);
        $this->Execute('ntpdate dewey.lib.ci.phoenix.az.us 2>&1', $_output, $_executeResult);

        return true;
    }

    /**
     * Retrieves the filename with given prefix and extension
     *
     * @author Varun Shoor
     * @param string $_directory
     * @param string $_prefix
     * @param string $_extension
     * @return mixed "File Name" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SearchFilename($_directory, $_prefix, $_extension)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_directory = StripTrailingSlash($_directory);

        $_prefix = strtolower($_prefix);

        if ($_directoryHandle = opendir($_directory)) {
            while (false !== ($_fileName = readdir($_directoryHandle))) {
                $_filePath = $_directory . '/' . $_fileName;

                if ($_fileName != '.' && $_fileName != '..' && !is_dir($_filePath)) {
                    $_pathInfoContainer = pathinfo($_filePath);

                    if (!isset($_pathInfoContainer['filename']) || empty($_pathInfoContainer['filename']) || !isset($_pathInfoContainer['extension'])) {
                        continue;
                    }

                    $_fileName = strtolower($_pathInfoContainer['filename']);
                    $_fileExtension = strtolower($_pathInfoContainer['extension']);

                    if (substr($_fileName, 0, strlen($_prefix)) == $_prefix && $_fileExtension == $_extension) {
                        return $_directory . '/' . $_fileName;
                    }
                }
            }

            closedir($_directoryHandle);
        }

        return false;
    }

    /**
     * Retrieves the directory name with given prefix and extension
     *
     * @author Varun Shoor
     * @param string $_directory
     * @param string $_prefix
     * @return mixed "File Name" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SearchDirectoryName($_directory, $_prefix)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_directory = StripTrailingSlash($_directory);

        $_prefix = strtolower($_prefix);

        if ($_directoryHandle = opendir($_directory)) {
            while (false !== ($_fileName = readdir($_directoryHandle))) {
                $_filePath = $_directory . '/' . $_fileName;

                if ($_fileName != '.' && $_fileName != '..' && is_dir($_filePath)) {

                    $_directoryName = strtolower($_fileName);

                    if (substr($_directoryName, 0, strlen($_prefix)) == $_prefix) {
                        return $_directory . '/' . $_directoryName;
                    }
                }
            }

            closedir($_directoryHandle);
        }

        return false;
    }
}
?>
