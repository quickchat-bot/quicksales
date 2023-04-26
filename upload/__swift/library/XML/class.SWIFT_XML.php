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

/**
 * XML Handler class, used to parse and write XML data, Partial code taken from comments of xml_parse_into_struct() function of PHP Manual. Credit where its due.
 *
 * @author Varun Shoor
 */
class SWIFT_XML extends SWIFT_Model
{

    private $_xmlData = array();
    private $_indent = 0;
    private $_CRLF = "\n";
    private $_charset = "UTF-8";
    private $_xmlStringData = '';
    /** @var \Base\Library\Winapp\SWIFT_Winapp */
    public $Winapp;
    /** @var \Base\Library\StaffAPI\SWIFT_StaffAPI */
    public $StaffAPI;

    /**
     * The constructor
     *
     * @author Varun Shoor
     * @param string $_charset The default charset for the XMLl
     */
    public function __construct($_charset = "UTF-8")
    {
        parent::__construct();

        $this->BuildXML($_charset);

        $this->SetIsClassLoaded(true);
    }

    /**
     * Function to be called before XML building starts
     *
     * @author Varun Shoor
     * @param string $_charset The default charset for the XML
     * @param array $_parentAttributes
     * @param bool $_noHeader
     * @return bool
     */
    public function BuildXML($_charset = 'UTF-8', $_parentAttributes = array(), $_noHeader = false)
    {
        $this->_charset = $_charset;

        $this->_xmlData = array();
        $this->_xmlStringData = '';

        $_extendedAttributeList = array();
        foreach ($_parentAttributes as $_keyName => $_value) {
            $_extendedAttributeList[] = Clean($_keyName) . '="' . htmlentities($_value, ENT_COMPAT) . '"';
        }

        $_extendedAttributeString = '';
        if (count($_extendedAttributeList)) {
            $_extendedAttributeString = ' ' . implode(' ', $_extendedAttributeList);
        }

        if (!$_noHeader) {
            $this->AddToXMLData('<?xml version="1.0" encoding="' . $this->_charset . '"' . $_extendedAttributeString . '?>' . $this->_CRLF);
        }

        return true;
    }

    protected function AddToXMLData($_xmlString)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->_xmlStringData .= $_xmlString;

        return true;
    }

    /**
     * @author Simaranjit Singh <simaranjit.singh@kayako.com>
     *
     * @param string $_string
     * @return string The Processed String
     */
    public static function Clean($_string)
    {
        // Cleaning control characters
        $_string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $_string);

        // CDATA closing tags handling
        $_string = str_replace("]]>", "]]]]><![CDATA[>", $_string);

        return $_string;
    }

    /**
     * Sets the default XML Characterset
     *
     * @author Varun Shoor
     * @param string $_charset The default charset for the XML
     * @return bool
     */
    public function SetCharset($_charset)
    {
        $this->_charset = $_charset;

        return true;
    }

    /**
     * Sets CRLF, should be either \r\n or \n
     *
     * @author Varun Shoor
     * @param string $_CRLF The CRLF Type
     * @return bool
     */
    public function SetCRLF($_CRLF)
    {
        $this->_CRLF = $_CRLF;

        return true;
    }

    /**
     * Converts XML data to a structured array
     *
     * @author Varun Shoor
     * @param string $_xmlText The XML contents as Text
     * @return array|false The XML Processed Array
     */
    public function XMLToTree($_xmlText)
    {
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);

        xml_parse_into_struct($parser, $_xmlText, $vals, $index);
        xml_parser_free($parser);

        $stack = array(array());
        $stacktop = 0;
        $parent = $tagi = array();
        foreach ($vals as $val) {
            $type = $val['type'];

            if ($type == 'open' || $type == 'complete') {
                // open tag
                $stack[$stacktop++] = $tagi;
                $tagi = array('tag' => $val['tag']);

                if (isset($val['attributes'])) {
                    $tagi['attrs'] = $val['attributes'];
                }

                if (isset($val['value']) && trim($val["value"]) != "") {
                    $tagi['values'][] = $val['value'];
                }
            }

            if ($type == 'complete' || $type == 'close') {
                // finish tag
                $tags[] = $oldtagi = $tagi;
                $tagi = $stack[--$stacktop];
                $oldtag = $oldtagi['tag'];
                unset($oldtagi['tag']);
                $tagi['children'][$oldtag][] = $oldtagi;
                $parent = $tagi;
            }

            if ($type == 'cdata' && trim($val["value"]) != "") {
                $tagi['values'][] = $val['value'];
            }
        }

        if (isset($parent['children'])) {
            return $parent['children'];
        } else {
            return false;
        }
    }

    /**
     * Adds a Parent Tag
     *
     * @author Varun Shoor
     * @param string $_tagName The Name of Tag
     * @param array $_attributes Attributes if any
     * @return bool
     */
    public function AddParentTag($_tagName, $_attributes = array())
    {
        $this->AddToXMLData($this->ReturnParentTag($_tagName, $_attributes));

        return true;
    }

    /**
     * Adds a Non-Parent Tag
     *
     * @author Varun Shoor
     * @param string $_tagName The Name of Tag
     * @param array $_attributes Attributes if any
     * @param bool $_noCDATA Whether or not to use CDATA enclosure for the contents of this tag
     * @return bool
     */
    public function AddTag($_tagName, $_value, $_attributes = array(), $_noCDATA = false)
    {
        $this->AddToXMLData($this->ReturnTag($_tagName, $_value, $_attributes, $_noCDATA));

        return true;
    }

    /**
     * Ends a Non-Parent XML Tag
     *
     * @author Varun Shoor
     * @param string $_tagName The Name of Tag
     * @return bool
     */
    public function EndTag($_tagName)
    {
        $this->AddToXMLData($this->ReturnEndTag($_tagName));

        return true;
    }

    /**
     * Ends a Parent XML Tag
     *
     * @author Varun Shoor
     * @param string $_tagName The Name of Tag
     * @return bool
     */
    public function EndParentTag($_tagName)
    {
        return $this->EndTag($_tagName);
    }

    /**
     * Add an XML Comment
     *
     * @author Varun Shoor
     * @param string $_comment The Comment to Add
     * @return bool
     */
    public function AddComment($_comment)
    {
        $this->AddToXMLData("<!-- " . $_comment . " -->" . $this->_CRLF);

        return true;
    }

    /**
     * Returns XML Data as single string
     *
     * @author Varun Shoor
     * @return string The XML Data to return
     */
    public function ReturnXML()
    {
        return $this->_xmlStringData;

//        return implode("", $this->_xmlData);
    }

    /**
     * Returns XML Data as Array
     *
     * @author Varun Shoor
     * @return array The XML Data to return
     */
    public function ReturnXMLArray()
    {
        return $this->_xmlData;
    }

    /**
     * Spits out XML Data
     *
     * @author Varun Shoor
     * @return bool
     */
    public function EchoXML()
    {
        echo trim($this->ReturnXML());

        return true;
    }

    /**
     * Output the Winapp Compatible Data
     *
     * @author Varun Shoor
     * @param bool $_doConversion Whether to carry out character set conversion
     * @return bool "true" on Success, "false" otherwise
     */
    public function ReturnXMLWinapp($_doConversion = true)
    {
        $data = '';

        $this->Load->Library('Winapp:Winapp', [], true, false, 'base');

        $_defaultCharacterSet = strtoupper($this->Language->Get('charset'));

        if (empty($_defaultCharacterSet)) {
            $_defaultCharacterSet = 'UTF-8';
        }

        if ($_doConversion == true && $_defaultCharacterSet != 'UTF-8') {
            // If mbstring is installed and we have a codepage to convert from, convert to UTF-8 from the specified codepage.
            if (extension_loaded('mbstring') && function_exists('mb_convert_encoding')) {
                $_finalData = mb_convert_encoding($this->ReturnXML(), 'UTF-8', $_defaultCharacterSet);
            } else {
                // mbstring must not be installed; let's see if we can reasonably use utf8_encode.
                // attempt to fall back on utf8_encode if the codepage is ISO-8859-1 ("Western")
                if ($_defaultCharacterSet == 'ISO-8859-1' && function_exists('utf8_encode')) {
                    $_finalData = utf8_encode($this->ReturnXML());
                } else {
                    // Can't use utf8_encode, as it won't work properly anyhow.
                    $_finalData = $this->ReturnXML();
                }
            }
        } else {
            // The codepage of the help desk is already UTF-8, or the data we're sending is, so we don't need mbstring.
            $_finalData = $this->ReturnXML();
        }

        return $this->Winapp->Encode(trim($_finalData), true);
    }

    /**
     * Output the Winapp Compatible Data
     *
     * @author Varun Shoor
     * @param bool $_doConversion Whether to carry out character set conversion
     * @return bool "true" on Success, "false" otherwise
     */
    public function EchoXMLWinapp($_doConversion = true)
    {
        echo $this->ReturnXMLWinapp($_doConversion);

        return true;
    }

    /**
     * Output the Staff API Compatible Data
     *
     * @author Varun Shoor
     * @param bool $_doConversion Whether to carry out character set conversion
     * @param bool $_returnData
     * @return bool "true" on Success, "false" otherwise
     */
    public function ReturnXMLStaffAPI($_doConversion = true, $_returnData = true)
    {
        $data = '';

        $this->Load->Library('StaffAPI:StaffAPI', [], true, false, 'base');

        $_defaultCharacterSet = strtoupper($this->Language->Get('charset'));

        if (empty($_defaultCharacterSet)) {
            $_defaultCharacterSet = 'UTF-8';
        }

        if ($_doConversion == true && $_defaultCharacterSet != 'UTF-8') {
            // If mbstring is installed and we have a codepage to convert from, convert to UTF-8 from the specified codepage.
            if (extension_loaded('mbstring') && function_exists('mb_convert_encoding')) {
                $_finalData = mb_convert_encoding($this->ReturnXML(), 'UTF-8', $_defaultCharacterSet);
            } else {
                // mbstring must not be installed; let's see if we can reasonably use utf8_encode.
                // attempt to fall back on utf8_encode if the codepage is ISO-8859-1 ("Western")
                if ($_defaultCharacterSet == 'ISO-8859-1' && function_exists('utf8_encode')) {
                    $_finalData = utf8_encode($this->ReturnXML());
                } else {
                    // Can't use utf8_encode, as it won't work properly anyhow.
                    $_finalData = $this->ReturnXML();
                }
            }
        } else {
            // The codepage of the help desk is already UTF-8, or the data we're sending is, so we don't need mbstring.
            $_finalData = $this->ReturnXML();
        }

        return $this->StaffAPI->Encode(trim($_finalData), $_returnData);
    }

    /**
     * Output the Staff API Compatible Data
     *
     * @author Varun Shoor
     * @param bool $_doConversion Whether to carry out character set conversion
     * @return bool "true" on Success, "false" otherwise
     */
    public function EchoXMLStaffAPI($_doConversion = true)
    {
        echo $this->ReturnXMLStaffAPI($_doConversion, false);

        return true;
    }

    /**
     * Returns a Processed Parent Tag
     *
     * @author Varun Shoor
     * @param string $_tagName The Name of Tag
     * @param array $_attributes Attributes if any
     * @return string The processed XML Data
     */
    private function ReturnParentTag($_tagName, $_attributes = array())
    {
        $_attributesResult = '';

        if (_is_array($_attributes)) {
            foreach ($_attributes as $key => $val) {
                $_attributesResult .= " " . $key . '="' . htmlspecialchars($val) . '"';
            }
        }

        $_returnValue = "<" . $_tagName . $_attributesResult . ">" . $this->_CRLF;
        $this->_indent++;

        return $_returnValue;
    }

    /**
     * Returns a Processed Non-Parent Tag
     *
     * @author Varun Shoor
     * @param string $_tagName The Name of Tag
     * @param string $_value The Value of Tag
     * @param array $_attributes Attributes if any
     * @param bool $_noCDATA Whether or not to use CDATA enclosure for the contents of this tag
     * @return string The processed XML Data
     */
    private function ReturnTag($_tagName, $_value, $_attributes = array(), $_noCDATA = false)
    {
        $_attributesResult = '';
        if (_is_array($_attributes)) {
            foreach ($_attributes as $key => $val) {
                $_attributesResult .= " " . $key . '="' . htmlspecialchars($val) . '"';
            }
        }

        if ($_noCDATA) {
            if ($_value == '') {
                return "<" . $_tagName . $_attributesResult . " />" . $this->_CRLF;
            } else {
                return "<" . $_tagName . $_attributesResult . ">" . htmlspecialchars($_value) . "</" . $_tagName . ">" . $this->_CRLF;
            }
        } else {
            if ($_value == '') {
                return "<" . $_tagName . $_attributesResult . " />" . $this->_CRLF;
            }
            /*
             * BUG FIX - Mansi Wason, Simaranjit Singh
             *
             * SWIFT-3408 "Broken XML Response"
             * SWIFT-4108 Issue with control characters in Kayako Staff/Rest APIs
             *
             * Comments: Multiple adjacent CDATA
             */
            else {
                return "<" . $_tagName . $_attributesResult . "><![CDATA[" . self::Clean($_value) . "]]></" . $_tagName . ">" . $this->_CRLF;
            }
        }
    }

    /**
     * Returns an Ending Tag Data
     *
     * @author Varun Shoor
     * @param string $_tagName The Name of Tag
     * @return string The processed XML Ending Tag
     */
    private function ReturnEndTag($_tagName)
    {
        $this->_indent--;
        $_returnValue = "</" . $_tagName . ">" . $this->_CRLF;

        return $_returnValue;
    }

}

?>
