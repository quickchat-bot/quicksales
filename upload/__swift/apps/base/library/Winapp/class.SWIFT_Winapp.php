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

namespace Base\Library\Winapp;

use SWIFT_Library;

/**
 * The Windows Application Data Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Winapp extends SWIFT_Library
{
    // Core Constants
    const DATA_COMPRESSED = 'compressed';
    const DATA_UNCOMPRESSED = 'uncompressed';

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
     * Decodes the Winapp Data
     * For Compressed Data:
     * compressed;<MD5 HASH OF COMPRESSED DATA>;<MD5 HASH OF UNCOMPRESSED DATA>;<SIZE OF COMPRESSED DATA>;<SIZE OF UNCOMPRESSED DATA>;<ACTUAL COMPRESSED DATA>
     * For Uncompressed Data:
     * uncompressed;0;<MD5 HASH OF UNCOMPRESSED DATA>;0;<SIZE OF UNCOMPRESSED DATA>;<ACTUAL UNCOMPRESSED DATA>
     *
     * @author Varun Shoor
     * @param string $_data The Windows Application Data
     * @return string|bool The Uncompressed/Processed Data
     */
    public function Decode($_data)
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_posDataType = strpos($_data, ";");
        $_dataType = substr($_data, 0, $_posDataType);

        $_posMD5Compressed = strpos($_data, ";", ($_posDataType + 1));
        $_md5Compressed = substr($_data, ($_posDataType + 1), ($_posMD5Compressed - $_posDataType - 1));

        $_posMD5Uncompressed = strpos($_data, ";", ($_posMD5Compressed + 1));
        $_md5Uncompressed = substr($_data, ($_posMD5Compressed + 1), ($_posMD5Uncompressed - $_posMD5Compressed - 1));

        $_posSizeCompressed = strpos($_data, ";", ($_posMD5Uncompressed + 1));
        $_sizeCompressed = substr($_data, ($_posMD5Uncompressed + 1), ($_posSizeCompressed - $_posMD5Uncompressed - 1));

        $_posSizeUncompressed = strpos($_data, ";", ($_posSizeCompressed + 1));
        $_sizeUncompressed = substr($_data, ($_posSizeCompressed + 1), ($_posSizeUncompressed - $_posSizeCompressed - 1));

        $_posFinalData = strpos($_data, ";", ($_posSizeUncompressed + 1));
        $_finalData = substr($_data, ($_posSizeUncompressed + 1), strlen($_data));

        if ($_dataType == self::DATA_UNCOMPRESSED) {
            return $_finalData;
        } elseif ($_dataType == self::DATA_COMPRESSED && extension_loaded("zlib")) {
            return gzuncompress(base64_decode($_finalData));
        } else {
            return $_data;
        }
    }

    /**
     * Prints winapp data, kayako winapps requires data in specific manner
     * For Compressed Data:
     * compressed;<MD5 HASH OF COMPRESSED DATA>;<MD5 HASH OF UNCOMPRESSED DATA>;<SIZE OF COMPRESSED DATA>;<SIZE OF UNCOMPRESSED DATA>;<ACTUAL COMPRESSED DATA>
     * For Uncompressed Data:
     * uncompressed;0;<MD5 HASH OF UNCOMPRESSED DATA>;0;<SIZE OF UNCOMPRESSED DATA>;<ACTUAL UNCOMPRESSED DATA>
     * Its best to compress the data and send it over, makes the updates almost instant.
     *
     * @author Varun Shoor
     * @param string $_data The Data to Encode
     * @param bool $_returnData Whether to Return Data rather than echo it
     * @return string|bool
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

        if (function_exists('gzencode') && $_doCompress == true) {
            $_sizeUncompressed = strlen($_data);
            $_md5Uncompressed = md5($_data);
            $_compressedData = gzcompress($_data, $_compressionLevel);
            $_sizeCompressed = strlen($_compressedData);
            $_md5Compressed = md5($_compressedData);

            $_finalData = '';

            $_finalData .= self::DATA_COMPRESSED . ';' . $_md5Compressed . ';' . $_md5Uncompressed . ';' . $_sizeCompressed . ';' . $_sizeUncompressed . ';' . $_compressedData;

        } else {
            $_sizeUncompressed = strlen($_data);
            $_md5Uncompressed = md5($_data);
            $_compressedData = '';
            $_sizeCompressed = '0';
            $_md5Compressed = '0';

            $_finalData = self::DATA_UNCOMPRESSED . ';' . $_md5Compressed . ';' . $_md5Uncompressed . ';' . $_sizeCompressed . ';' . $_sizeUncompressed . ';' . $_data;
        }

        if ($_returnData) {
            return $_finalData;
        }

//        header('Content-Length: ' . strlen($_finalData));

        echo $_finalData;

        return true;
    }
}

?>
