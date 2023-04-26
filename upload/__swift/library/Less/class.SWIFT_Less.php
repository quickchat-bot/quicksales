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
 * Handles the CSS Less Parsing System
 *
 * @author Varun Shoor
 */
class SWIFT_Less extends SWIFT_Library
{
    protected $LessCompiler = false;

    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
        parent::__construct();

        $this->LessCompiler = new lessc();
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
     * Parse the Less Data into CSS
     *
     * @author Varun Shoor
     * @param string $_lessData
     * @return string The Parsed CSS Data
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Parse($_lessData)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        try {
            $_lessDataCompiled = $this->LessCompiler->parse($_lessData);
        } catch (Exception $_SWIFT_ExceptionObject) {
            // if excpetion retrun $_lessData
            return $_lessData;
        }

        return $_lessDataCompiled;
    }
}
?>