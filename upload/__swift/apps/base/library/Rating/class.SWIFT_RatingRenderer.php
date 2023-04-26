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

namespace Base\Library\Rating;

use Base\Models\Rating\SWIFT_RatingResult;
use SWIFT;
use SWIFT_Library;

/**
 * Handles all the rendering routines for the ratings
 *
 * @author Varun Shoor
 */
class SWIFT_RatingRenderer extends SWIFT_Library
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
     * Render the Navigation box
     *
     * @author Varun Shoor
     * @param array $_ratingContainer The Rating Container
     * @return string|bool
     */
    public static function Render($_ratingTypeList, $_typeID, $_ajaxURL, $_ratingContainer, $_ratingResultContainerInput = false, $_classPrefix = 'navinfo')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ratingContainer)) {
            return false;
        }

        $_ratingIDList = array_keys($_ratingContainer);

        $_ratingResultContainer = $_ratingResultContainerInput;
        if ($_ratingResultContainerInput === false) {
            $_ratingResultContainer = SWIFT_RatingResult::Retrieve($_ratingIDList, array($_typeID));
        }

        $_outputHTML = $_customJSCode = '';

        foreach ($_ratingContainer as $_ratingID => $_rating) {
            $_finalRatingResultContainer = false;
            if (isset($_ratingResultContainer[$_ratingID])) {
                $_finalRatingResultContainer = $_ratingResultContainer[$_ratingID];
            }

            $_ratingHTML = $_disabledResult = '';
            if (($_rating['iseditable'] == '0' && isset($_finalRatingResultContainer[$_typeID])) || $_rating['isclientonly'] == '1') {
                $_disabledResult = ' disabled="disabled"';
            }

            $_resultValue = 0;
            if (isset($_finalRatingResultContainer[$_typeID])) {
                $_resultValue = floatval($_finalRatingResultContainer[$_typeID]['ratingresult']);
            }

            for ($index = 0; $index < ($_rating['ratingscale']); $index++) {
//                $_resultValueIndex = (($index+1)/4);
                $_resultValueIndex = $index + 1;
                $_checkedResult = '';

                if ($_resultValueIndex == $_resultValue) {
                    $_checkedResult = ' checked="checked"';
                }
                $_ratingHTML .= '<input name="rating_' . $_ratingID . '_' . $_typeID . '" type="radio" value="' . $_resultValueIndex . '" class="rating"' . $_disabledResult . $_checkedResult . '/>';
            }

            $_customJSCode .= '$("input[name=rating_' . $_ratingID . '_' . $_typeID . ']").rating({callback: function(value, link) { TriggerRating(\'' . $_ajaxURL . '\', \'' . $_ratingID . '\', \'' . $_typeID . '\', value, ' . IIF($_rating['iseditable'] == '1', 'false', 'true') . '); }});';
            $_outputHTML .= '<div class="' . $_classPrefix . 'item">' .
                '<div class="' . $_classPrefix . 'itemtitle">' . htmlspecialchars($_rating['ratingtitle']) . '</div><div style="display: inline-block; height: 16px;">' . $_ratingHTML . '</div></div>';
        }

        $_outputHTML .= '<script type="text/javascript">';
        $_outputHTML .= 'if (window.$UIObject) { window.$UIObject.Queue(function(){';
        $_outputHTML .= $_customJSCode;
        $_outputHTML .= '}); }</script>';

        return $_outputHTML;
    }

    /**
     * Render the Navigation box
     *
     * @author Varun Shoor
     * @param array $_ratingContainer The Rating Container
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RenderNavigationBox($_ratingTypeList, $_typeID, $_ajaxURL, $_ratingContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ratingContainer)) {
            return false;
        }

        $_outputHTML = self::Render($_ratingTypeList, $_typeID, $_ajaxURL, $_ratingContainer);

        $_SWIFT->UserInterface->AddNavigationBox($_SWIFT->Language->Get('ratings'), $_outputHTML);

        return $_outputHTML;
    }
}

?>
