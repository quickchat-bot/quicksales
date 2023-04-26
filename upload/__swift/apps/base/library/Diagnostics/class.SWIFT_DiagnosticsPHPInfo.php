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

namespace Base\Library\Diagnostics;

use Base\Library\Diagnostics\SWIFT_Diagnostics_Exception;
use SWIFT_Model;

/**
 * The phpinfo() Parser
 *
 * @author Varun Shoor
 */
class SWIFT_DiagnosticsPHPInfo extends SWIFT_Model
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Parse the PHP Extension Data
     *
     * @author Varun Shoor
     * @return mixed "_extensionContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Diagnostics_Exception If the Class is not Loaded
     */
    public function ParseExtensions()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Diagnostics_Exception(SWIFT_CLASSNOTLOADED);
        }

        ob_start();
        phpinfo(INFO_MODULES);
        $_infoContentHolder = ob_get_contents();
        ob_end_clean();

        $_infoContentHolder = strip_tags($_infoContentHolder, '<h2><th><td>');
        $_infoContentHolder = preg_replace('/<th[^>]*>([^<]+)<\/th>/', "<info>\\1</info>", $_infoContentHolder);
        $_infoContentHolder = preg_replace('/<td[^>]*>([^<]+)<\/td>/', "<info>\\1</info>", $_infoContentHolder);

        $_temporaryContainer = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/', $_infoContentHolder, -1, PREG_SPLIT_DELIM_CAPTURE);
        $_extensionContainer = array();

        for ($i = 1; $i < count($_temporaryContainer); $i++) {

            if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/', $_temporaryContainer[$i], $_matchesContainer)) {
                $_extensionName = trim($_matchesContainer[1]);
                $_extensionSubContainer = explode("\n", $_temporaryContainer[$i + 1]);
                foreach ($_extensionSubContainer AS $_subSection) {
                    $_partOne = '<info>([^<]+)<\/info>';
                    $_partTwo = "/$_partOne\s*$_partOne\s*$_partOne/";
                    $_partThree = "/$_partOne\s*$_partOne/";
                    if (preg_match($_partThree, $_subSection, $_matchesContainer)) {
                        if (!isset($_matchesContainer[3])) {
                            $_matchesContainer[3] = '';
                        }

                        $_extensionContainer[$_extensionName][trim($_matchesContainer[1])] = array(trim($_matchesContainer[2]), trim($_matchesContainer[3]));
                    } elseif (preg_match($_partTwo, $_partOne, $_matchesContainer)) {
                        $_extensionContainer[$_extensionName][trim($_matchesContainer[1])] = trim($_matchesContainer[2]);
                    }
                }
            }
        }

        return $_extensionContainer;
    }

    /**
     * Parse the PHP Configuration Variables
     *
     * @author Varun Shoor
     * @return mixed "_variableHolder" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Diagnostics_Exception If the Class is not Loaded
     */
    public function ParseConfig()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Diagnostics_Exception(SWIFT_CLASSNOTLOADED);
        }

        ob_start();
        phpinfo(INFO_GENERAL | INFO_CONFIGURATION);

        $_infoContentHolder = ob_get_contents();
        ob_end_clean();

        $_variableHolder = $_matchesContainer = array();

        if (preg_match_all('/<tr><td class="e">(.*?)<\/td><td class="v">(.*?)<\/td><td class="v">(.*?)<\/td><\/tr>/', $_infoContentHolder, $_matchesContainer, PREG_SET_ORDER)) {
            foreach ($_matchesContainer as $_variable) {
                if ($_variable[2] == '<i>no value</i>') continue;

                if (!isset($_variable[2])) {
                    $_variable[2] = '';
                }

                $_variableHolder[$_variable[1]] = $_variable[2];
            }
        }

        return $_variableHolder;
    }
}

?>
