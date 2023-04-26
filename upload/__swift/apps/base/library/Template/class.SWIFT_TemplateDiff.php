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

namespace Base\Library\Template;

use SWIFT_Library;
use Horde_Text_Diff;
use Horde_Text_Diff_Renderer_weightage;

/**
 * The Template Diff Renderer & Version Calculator
 *
 * @author Varun Shoor
 */
class SWIFT_TemplateDiff extends SWIFT_Library
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
     * Calculates the amount of changes and returns a new version number
     *
     * @author Varun Shoor
     * @param string $_currentVersion The Current Template Version
     * @param string $_oldText The Old Text
     * @param string $_newText The New Text
     * @return string The New Version Number
     */
    public static function GetVersion($_currentVersion, $_oldText, $_newText)
    {

        $_oldTextContainer = explode(SWIFT_CRLF, preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_oldText));
        $_newTextContainer = explode(SWIFT_CRLF, preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_newText));

        $_Text_DiffObject = new Horde_Text_Diff('auto', [$_oldTextContainer, $_newTextContainer]);

        $_Text_Diff_RendererObject = new Horde_Text_Diff_Renderer_weightage();
        $_Text_Diff_RendererObject->render($_Text_DiffObject);

        // Current Version should be like: 1.00.00
        $_versionContainer = explode('.', $_currentVersion);
        if (count($_versionContainer) != 3) {
            $_versionContainer = array('1', '00', '00');
        }

        // Start from 3rd
        $_weightage = $_Text_Diff_RendererObject->weightage;

        // Minimum Weightage
        if (empty($_weightage)) {
            $_weightage = 0;
        }

        if ($_weightage == 0 && $_versionContainer[2] == 0) {
            $_versionContainer[2] = '01';
        }

        $_versionContainer[2] += $_weightage; // Example: 80+30=110
        if (strlen($_versionContainer[2]) == 1) {
            $_versionContainer[2] = strval($_versionContainer[2] . '0');
        } else {
            $_versionContainer[2] = strval($_versionContainer[2]);
        }

        if ($_versionContainer[2] <= 99) {
            return $_versionContainer[0] . '.' . $_versionContainer[1] . '.' . $_versionContainer[2];
        }

        // Seems like the 3rd number has more than 100 points.. Ex: 280
        $_division = $_versionContainer[2] / 100; // = 2.80
        $_division = number_format($_division, 2);
        $_divisionContainer = explode('.', $_division);

        $_versionContainer[1] += (int)($_divisionContainer[0]);
        if (strlen($_versionContainer[1]) == 1) {
            $_versionContainer[1] = strval('0' . $_versionContainer[1]);
        }

        if (strlen($_divisionContainer[1]) == 1) {
            $_versionContainer[2] = strval($_divisionContainer[1] . '0');
        } else {
            $_versionContainer[2] = strval($_divisionContainer[1]);
        }

        if ($_versionContainer[1] <= 99) {
            return $_versionContainer[0] . '.' . $_versionContainer[1] . '.' . $_versionContainer[2];
        }

        // Seems like the 2nd number has more than 100 points.. Ex: 280
        $_division = $_versionContainer[1] / 100; // = 2.80
        $_division = number_format($_division, 2);
        $_divisionContainer = explode('.', $_division);

        $_versionContainer[0] += (int)($_divisionContainer[0]);
        if (strlen($_divisionContainer[1]) == 1) {
            $_versionContainer[1] = strval($_divisionContainer[1] . '0');
        } else {
            $_versionContainer[1] = strval($_divisionContainer[1]);
        }

        return $_versionContainer[0] . '.' . $_versionContainer[1] . '.' . $_versionContainer[2];
    }
}

?>
