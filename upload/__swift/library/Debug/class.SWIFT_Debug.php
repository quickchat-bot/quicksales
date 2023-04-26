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
 * Debug class is used to dump the exceptions and debug statements.
 * This class is inspired from Kohana Framework debugging mechanism.
 *
 * @author Mahesh Salaria
 */
class SWIFT_Debug extends SWIFT_Library
{
    /**
     * Constructor
     *
     * @author Mahesh Salariae
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Destructor
     *
     * @author Mahesh Salariae
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Returns an HTML string, highlighting a specific line of a file, with some
     * number of lines padded above and below.
     *
     * @author Mahesh Salaria
     * @param int $_file
     * @param int $_lineNumber
     * @param int $_padding
     * @return bool|string
     */
    public static function GetSource($_file, $_lineNumber, $_padding = 5)
    {
        if (!$_file || !is_readable($_file))
        {
            // Continuing will cause errors
            return false;
        }

        // Open the file and set the line position
        $_file = fopen($_file, 'r');
        $_line = 0;

        // Set the reading range
        $_range = array('start' => $_lineNumber - $_padding, 'end' => $_lineNumber + $_padding);

        // Set the zero-padding amount for line numbers
        $_format = '% ' . strlen($_range['end']) . 'd';

        $_source = '';
        while (($_row = fgets($_file)) !== false)
        {
            // Increment the line number
            if (++$_line > $_range['end'])
                break;

            if ($_line >= $_range['start'])
            {
                // Make the row safe for output
                $_row = htmlspecialchars($_row, ENT_NOQUOTES, 'UTF-8');

                // Trim whitespace and sanitize the row
                $_row = '<span class="number">' . sprintf($_format, $_line) . '</span> '. $_row;

                if ($_line === $_lineNumber)
                {
                    // Apply highlighting to this row
                    $_row = '<span class="line highlight">'.$_row.'</span>';
                } else {
                    $_row = '<span class="line">' . $_row . '</span>';
                }

                // Add to the captured source
                $_source .= $_row;
            }
        }

        // Close the file
        fclose($_file);

        return '<pre class="source"><code>' . $_source . '</code></pre>';
    }


    /**
     * Dump Backtrace with file lines, parameters and variables.
     *
     * @author Mahesh Salaria
     * @param array $_stackTrace stack trace array
     * @return string $_output
     */
    public static function RetrunStackTrace($_stackTrace = array())
    {
        if (!_is_array($_stackTrace))
        {
            // If $_stackTrace is empty array then try to generate using debug_backtrace
            $_stackTrace = debug_backtrace();
        }

        // Non-standard function calls
        $_statements = array('include', 'include_once', 'require', 'require_once');

        $_output = array();
        foreach ($_stackTrace as $_step)
        {
            if (!isset($_step['function']))
            {
                // Invalid trace step
                continue;
            }

            if (isset($_step['file']) && isset($_step['line']))
            {
                // Include the source of this step
                $_source = self::GetSource($_step['file'], $_step['line']);
            }

            if (isset($_step['file']))
            {
                $_file = $_step['file'];

                if (isset($_step['line']))
                {
                    $_line = $_step['line'];
                }
            }

            // function()
            $_function = $_step['function'];

            if (in_array($_step['function'], $_statements))
            {
                if (empty($_step['args']))
                {
                    // No arguments
                    $_args = array();
                } else {
                    // Sanitize the file path
                    $_args = array($_step['args'][0]);
                }

            } else if (isset($_step['args'])) {
                if (!function_exists($_step['function']) || strpos($_step['function'], '{closure}') !== false)
                {
                    // Introspection on closures or language constructs in a stack trace is impossible
                    $_params = NULL;

                } else {
                    if (isset($_step['class']))
                    {
                        if (method_exists($_step['class'], $_step['function']))
                        {
                            $_reflection = new ReflectionMethod($_step['class'], $_step['function']);
                        } else {
                            $_reflection = new ReflectionMethod($_step['class'], '__call');
                        }

                    } else {
                        $_reflection = new ReflectionFunction($_step['function']);
                    }

                    // Get the function parameters
                    $_params = $_reflection->getParameters();
                }

                $_args = array();

                foreach ($_step['args'] as $_key => $_var)
                {
                    if (isset($_params[$_key]))
                    {
                        // Assign the argument by the parameter name
                        $_args[$_params[$_key]->name] = $_var;

                    } else {
                        // Assign the argument by number
                        $_args[$_key] = $_var;
                    }
                }
            }

            if (isset($_step['class']))
            {
                // Class->method() or Class::method()
                $_function = $_step['class'] . $_step['type'] . $_step['function'];
            }

            $_output[] = array(
                'function' => $_function,
                'args'     => isset($_args)   ? $_args : NULL,
                'file'     => isset($_file)   ? $_file : NULL,
                'line'     => isset($_line)   ? $_line : NULL,
                'source'   => isset($_source) ? $_source : NULL,
            );

            unset($_function, $_args, $_file, $_line, $_source);
        }

        return self::DumpOutput($_output);
    }

    /**
    * Dump Output in well formated way
    *
    * @author Mahesh Salaria
    * @param array $_output output array
    * @return string|bool $_htmlOutput on Success, "false" otherwise
    * @throws SWIFT_Exception If the Class is not Loaded
    */
    public static function DumpOutput($_output = array())
    {
        if (!_is_array($_output))
        {
            return false;
        }

        $_htmlOutput = '';

        $_htmlOutput .= "<script type='text/javascript'>
                        document.documentElement.className = document.documentElement.className + ' js';
                        function toggle(elem)
                        {
                            elem = document.getElementById(elem);

                            if (elem.style && elem.style['display'])
                                // Only works with the style attr
                                var disp = elem.style['display'];
                            else if (elem.currentStyle)
                                // For MSIE, naturally
                                var disp = elem.currentStyle['display'];
                            else if (window.getComputedStyle)
                                // For most other browsers
                                var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');

                            // Toggle the state of the 'display' style
                            elem.style.display = disp == 'block' ? 'none' : 'block';
                            return false;
                        }
                        </script>";


        foreach ($_output as $_key => $_val)
        {
            $_collapsedCSS = 'collapsed';
            if ($_key < 4) {
                $_collapsedCSS = '';
            }

            $_argID = 'args' . $_key;

            $_htmlOutput .= '<span class="sourcetitle">';

            $_htmlOutput .= "<a href='#" . $_key . "' onclick='return toggle(\"" . $_key . "\");'>#" . $_key . ' File: ' . SecureFilePath($_val['file']) . " Line: " . $_val['line'] . "</a>";

            $_argsOutput = array();
            if (isset($_val['args']) && _is_array($_val['args'])) {
                foreach ($_val['args'] as $_argKey => $_argVal) {
                    $_argsOutput[] = self::VariableDump($_argVal);
                }
            }

            $_htmlOutput .= " Function: <span class='sourcedesc'>" . $_val['function'] . "(" . implode(', ', $_argsOutput) . ")</span> <br />";

            $_htmlOutput .= "<pre id='" . $_key . "' class='" . $_collapsedCSS . "'><code>" . $_val['source'] . "</code></pre>";
        }

        $_htmlOutput .= '</span>';

        $_htmlOutput = "<div id='kayako_exception'>" . $_htmlOutput . "</div>";

        return $_htmlOutput;
    }

    /**
     * Variables Dump in HTML format
     *
     * Borrows heavily on concepts from the Debug class of [Nette](http://nettephp.com/).
     *
     * @author Mahesh Salaria
     * @param mixed $_var Variable
     * @param int $_length length
     * @param int $_limit limit
     * @param int $_level level
     * @return string output
     */
    protected static function VariableDump(&$_var, $_length = 128, $_limit = 1, $_level = 0)
    {
        if ($_var === NULL)
        {
            return '<span class="variabletype">NULL</span>';
        } else if (is_bool($_var)) {
            return $_var ? 'TRUE' : 'FALSE';
        } else if (is_float($_var)) {
            return '<span class="variabletype">float</span> ' . $_var;
        } else if (is_resource($_var)) {
            if (($_type = get_resource_type($_var)) === 'stream' AND $_meta = stream_get_meta_data($_var))
            {
                $_meta = stream_get_meta_data($_var);

                if (isset($_meta['uri']))
                {
                    $_file = $_meta['uri'];

                    if (function_exists('stream_is_local'))
                    {
                        // Only exists on PHP >= 5.2.4
                        if (stream_is_local($_file))
                        {
                            $_file = SecureFilePath($_file);
                        }
                    }

                    return '<span class="variabletype">resource</span><span>(' . $_type . ')</span> ' . htmlspecialchars($_file, ENT_NOQUOTES, 'UTF-8');
                }
            } else {
                return '<span class="variabletype">resource</span><span>(' . $_type . ')</span>';
            }
        } else if (is_string($_var)) {
            // Clean invalid multibyte characters. iconv is only invoked
            // if there are non ASCII characters in the string, so this
            // isn't too much of a hit.

            if (strlen($_var) > $_length)
            {
                // Encode the truncated string
                $_str = htmlspecialchars(substr($_var, 0, $_length), ENT_NOQUOTES, 'UTF-8').'&nbsp;&hellip;';
            } else {
                // Encode the string
                $_str = htmlspecialchars($_var, ENT_NOQUOTES, 'UTF-8');
            }

            if (substr($_str, 0, 1) == '/') {
                $_str = SecureFilePath($_str);
            }

            return "" . $_str . "";
        } else if (is_array($_var)) {
            $_output = array();

            // Indentation for this variable
            $_space = str_repeat($_s = '    ', $_level);

            static $_marker;

            if ($_marker === NULL)
            {
                // Make a unique marker
                $_marker = uniqid("\x00");
            }

            if (empty($_var))
            {
                // Do nothing
            } else if (isset($_var[$_marker])) {
                $_output[] = "(\n$_space$_s*RECURSION*\n$_space)";
            } else if ($_level < $_limit) {
                $_output[] = "<span>(";

                $_var[$_marker] = TRUE;
                foreach ($_var as $_key => & $_val)
                {
                    if ($_key === $_marker) continue;
                    if (!is_int($_key))
                    {
                        $_key = '"' . htmlspecialchars($_key, ENT_NOQUOTES, 'UTF-8') . '"';
                    }

                    if ($_key != '0')
                    {
                        $_output[] = ", ";
                    }

                    $_output[] = self::VariableDump($_val, $_length, $_limit, $_level + 1);
                }
                unset($_var[$_marker]);

                $_output[] = "$_space)</span>";
            } else {
                // Depth too great
                $_output[] = "(\n$_space$_s...\n$_space)";
            }

            return implode($_output);
        } else if (is_object($_var)) {
            // Copy the object as an array
            $_array = (array) $_var;

            $_output = array();

            // Indentation for this variable
            $_space = str_repeat($_s = '    ', $_level);

            $_hash = spl_object_hash($_var);

            // Objects that are being dumped
            static $_objects = array();

            if (empty($_var))
            {
                // Do nothing
            } else if (isset($_objects[$_hash])) {
                $_output[] = "{\n$_space$_s*RECURSION*\n$_space}";
            } else if ($_level < $_limit) {
                $_output[] = "<code>{";

                $_objects[$_hash] = TRUE;
                foreach ($_array as $_key => & $_val)
                {
                    if ($_key[0] === "\x00")
                    {
                        // Determine if the access is protected or protected
                        $_access = '<span class="variabletype">(' . (($_key[1] === '*') ? 'protected' : 'private') . ')</span>';

                        // Remove the access level from the variable name
                        $_key = substr($_key, strrpos($_key, "\x00") + 1);
                    } else {
                        $_access = '<span class="variabletype">(public)</span>';
                    }

                    $_output[] = "$_space$_s$_access $_key => " . self::VariableDump($_val, $_length, $_limit, $_level + 1);
                }
                unset($_objects[$_hash]);

                $_output[] = "$_space}</code>";
            } else {
                // Depth too great
                $_output[] = "{\n$_space$_s...\n$_space}";
            }

            return '<span><span class="variabletype">(object)</span> ' . get_class($_var) . '</span>';
        } else {
            return '<span class="variabletype">' . gettype($_var) . '</span> ' . htmlspecialchars(print_r($_var, TRUE), ENT_NOQUOTES, 'UTF-8');
        }
    }
}
?>