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

namespace Base\Library\Diff;

use ReflectionException;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;
use Horde_Text_Diff;
use Horde_Text_Diff_Renderer_Context;
use Horde_Text_Diff_Renderer_Inline;
use Horde_Text_Diff_Renderer_Unified;

/**
 * The Diff Render Handling Class
 *
 * @author Varun Shoor
 */
class SWIFT_DiffRenderer extends SWIFT_Library
{
    /**
     * Export Unified Diff
     *
     * @author Varun Shoor
     * @param string $_fileName The File Name to Export as
     * @param string $_oldText The Old Text
     * @param string $_newText The New Text
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Export($_fileName, $_oldText, $_newText)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_oldText = preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_oldText);
        $_newText = preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_newText);

        $_oldTextList = explode(SWIFT_CRLF, $_oldText);
        $_newTextList = explode(SWIFT_CRLF, $_newText);

        $_DiffObject = new Horde_Text_Diff('auto', [$_oldTextList, $_newTextList]);

        $_UnifiedRendererObject = new Horde_Text_Diff_Renderer_unified();

        $_unifiedText = trim($_UnifiedRendererObject->render($_DiffObject));

        $_fileName = Clean($_fileName) . '.patch';

        if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) {
            // IE Bug in download name workaround
            ini_set('zlib.output_compression', 'Off');
        }

        header('Content-Type: application/force-download');
        header('Content-Disposition: attachment; filename="' . $_fileName . '"');
        header('Content-Transfer-Encoding: binary');

        echo $_unifiedText;

        return true;
    }


    /**
     * Render the Inline HTML
     *
     * @param string $_oldText The Old Text
     * @param string $_newText The New Text
     * @return mixed "_finalText" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     * @author Varun Shoor
     */
    public function InlineHTML($_oldText, $_newText)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_oldText = preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_oldText);
        $_newText = preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_newText);

        $_finalText = Diff::toTable(Diff::compare($_oldText, $_newText));

        return $_finalText;
    }
}
