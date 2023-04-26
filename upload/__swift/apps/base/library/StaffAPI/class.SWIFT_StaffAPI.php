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

namespace Base\Library\StaffAPI;

use SWIFT_Library;

/**
 * The Staff API Application Data Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_StaffAPI extends SWIFT_Library
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
     * Prints Staff API Data
     *
     * @author Varun Shoor
     * @param string $_data The Data to Encode
     * @param bool $_returnData Whether to Return Data rather than echo it
     * @return bool|string
     */
    public function Encode($_data, $_returnData = false)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_doCompress = false;
        if ($this->Settings->Get('cpu_compresswinappdata') == 1) {
            $_doCompress = true;
        }

        $_compressionLevel = $this->Settings->Get('cpu_winappcompresslevel');

        // Default to 9
        if (empty($_compressionLevel)) {
            $_compressionLevel = 1;
        }

        $_finalData = '';
        if (function_exists('gzencode') && $_doCompress == true) {
            $_finalData = gzencode($_data, $_compressionLevel);
        } else {
            $_finalData = $_data;
        }

        if ($_returnData) {
            return $_finalData;
        }

        @header('Content-Length: ' . strlen($_finalData));
        @header('Content-MD5: ' . base64_encode(md5($_data)));
        if (function_exists('gzencode') && $_doCompress == true) {
            @header('Content-Encoding: gzip');
        }

        @header('Content-Type: text/xml');

        echo $_finalData;

        return '';
    }
}

?>
