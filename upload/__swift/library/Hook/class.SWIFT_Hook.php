<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2013, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The Hook Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Hook extends SWIFT_Library
{
    const DEFAULT_PRIORITY = 50;

    static private $_hookMap = array();

    /**
     * Execute the given hook and return the hook code
     *
     * @author Varun Shoor
     *
     * @param string $_hookName The Hook Name
     *
     * @return mixed "_hookContents" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If Hook file does not exist
     */
    public static function Execute($_hookName)
    {
        $_hookName = Clean($_hookName);

        // No hook set?
        if (!isset(self::$_hookMap[$_hookName]) || !_is_array(self::$_hookMap[$_hookName])) {
            return false;
        }

        $_hookContents = '';

        asort(self::$_hookMap[$_hookName]);

        // Seems like we have a hook registered..
        foreach (self::$_hookMap[$_hookName] as $_key => $_val) {
            $_appNameContainer = explode(':', $_val);
            $_appName          = Clean($_appNameContainer[1]);
            $_hookFileName     = SWIFT_App::GetHookFilePath($_appName, $_hookName);

            if (!$_hookFileName || !file_exists($_hookFileName)) {
                throw new SWIFT_Exception('Hook file does not exist: ' . $_hookName . ' in: ' . $_appName);

                continue;
            }

            // Add the app to loader
            SWIFT_Loader::AddApp(SWIFT_App::Get($_appName));

            $_hookContents .= file_get_contents($_hookFileName);
        }

        if (!empty($_hookContents)) {
            $_hookContents = '?>' . $_hookContents;

            return $_hookContents;
        }

        return false;
    }

    /**
     * Register a app with a hook map
     *
     * @author Varun Shoor
     *
     * @param string   $_appName      The App Name
     * @param string   $_hookName     The Hook Name
     * @param int|bool $_hookPriority (OPTIONAL) The Hook Priority in Execution
     *
     * @return SWIFT_Hook
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Register($_appName, $_hookName, $_hookPriority = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_appName) || empty($_hookName) || !SWIFT_App::IsInstalled($_appName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->AddHook($_appName, $_hookName, $_hookPriority);

        return $this;
    }

    /**
     * Add a hook to the hook map
     *
     * @author Varun Shoor
     *
     * @param string   $_appName      The App Name
     * @param string   $_hookName     The Hook Name
     * @param mixed $_hookPriority (OPTIONAL) The Hook Priority in Execution
     *
     * @return SWIFT_Hook|bool
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function AddHook($_appName, $_hookName, $_hookPriority = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_appName) || empty($_hookName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!isset(self::$_hookMap[$_hookName])) {
            self::$_hookMap[$_hookName] = array();
        }

        if (in_array($_appName, self::$_hookMap[$_hookName])) {
            return true;
        }

        if (!$_hookPriority || !is_numeric($_hookPriority)) {
            $_hookPriority = self::DEFAULT_PRIORITY;
        } else {
            $_hookPriority = (int) ($_hookPriority);
        }

        self::$_hookMap[$_hookName][] = $_hookPriority . ':' . $_appName;

        return $this;
    }
}