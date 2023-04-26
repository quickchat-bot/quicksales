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

namespace LiveChat\Library\Canned;

use LiveChat\Library\Canned\SWIFT_Canned_Exception;
use SWIFT;
use LiveChat\Models\Canned\SWIFT_CannedCategory;
use LiveChat\Models\Canned\SWIFT_CannedResponse;
use SWIFT_Library;
use SWIFT_XML;

/**
 * The Canned Manager Class
 *
 * @author Varun Shoor
 */
class SWIFT_CannedManager extends SWIFT_Library
{
    static protected $_cannedCategoryCache = false;
    static protected $_cannedResponseCache = false;

    static private $_hiddenCategories = [];

    /**
     * Prepare the XML Packet to Dispatch to Winapp
     *
     * @author Varun Shoor
     * @param SWIFT_XML $_SWIFT_XMLObject The SWIFT_XML Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Canned_Exception If Invalid Data is Provided
     */
    public static function DispatchXML(SWIFT_XML $_SWIFT_XMLObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_XMLObject instanceof SWIFT_XML || !$_SWIFT_XMLObject->GetIsClassLoaded()) {
            throw new SWIFT_Canned_Exception(SWIFT_INVALIDDATA);
        }

        if (!self::$_cannedCategoryCache) {
            self::$_cannedCategoryCache = SWIFT_CannedCategory::RetrieveCannedCategories();
        }

        if (!self::$_cannedResponseCache) {
            self::$_cannedResponseCache = SWIFT_CannedResponse::RetrieveCannedResponses();
        }

        $_cannedCategoryCache = self::$_cannedCategoryCache;

        $_SWIFT_XMLObject->AddParentTag('canned');

        $_SWIFT_XMLObject->AddParentTag('category', array('id' => '0', 'title' => 'root'));
        self::GetSubCannedCategoryXML($_SWIFT_XMLObject);
        $_SWIFT_XMLObject->EndParentTag('category');

        $_SWIFT_XMLObject->EndParentTag('canned');

        return true;
    }

    /**
     * Retrieve the canned category options in a loop
     *
     * @author Varun Shoor
     * @param SWIFT_XML $_SWIFT_XMLObject The SWIFT_XML Object Pointer
     * @param int $_parentCannedCategoryID The Parent Canned Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Canned_Exception If Invalid Data is Provided
     */
    protected static function GetSubCannedCategoryXML(SWIFT_XML $_SWIFT_XMLObject, $_parentCannedCategoryID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();
        self::$_hiddenCategories = [];

        if (!$_SWIFT_XMLObject instanceof SWIFT_XML || !$_SWIFT_XMLObject->GetIsClassLoaded()) {
            throw new SWIFT_Canned_Exception(SWIFT_INVALIDDATA);
        }

        $_cannedCategoryCache = self::$_cannedCategoryCache;
        $_cannedResponseCache = self::$_cannedResponseCache;

        $_childCount = 0;

        if (isset($_cannedCategoryCache['_cannedParentMap'][$_parentCannedCategoryID])) {
            $_childCount = count($_cannedCategoryCache['_cannedParentMap'][$_parentCannedCategoryID]);
        }

        if ($_childCount > 0) {
            foreach ($_cannedCategoryCache['_cannedParentMap'][$_parentCannedCategoryID] as $_key => $_val) {
                if ($_val['categorytype'] == SWIFT_CannedCategory::TYPE_PRIVATE
                    && $_val['staffid'] != SWIFT::GetInstance()->Staff->GetID()) {
                    self::$_hiddenCategories[] = $_val['cannedcategoryid'];
                    continue;
                }

                $_SWIFT_XMLObject->AddParentTag('category', array('id' => $_val['cannedcategoryid'], 'title' => $_val['title']));

                if (isset($_cannedCategoryCache['_cannedParentMap'][$_val['cannedcategoryid']]) && count($_cannedCategoryCache['_cannedParentMap'][$_val['cannedcategoryid']]) > 0) {
                    self::GetSubCannedCategoryXML($_SWIFT_XMLObject, $_val['cannedcategoryid']);
                }

                // Build the responses
                if (isset($_cannedResponseCache['_responseParentMap'][$_val['cannedcategoryid']]) && count($_cannedResponseCache['_responseParentMap'][$_val['cannedcategoryid']]) > 0) {
                    self::BuildResponseMapXML($_SWIFT_XMLObject, $_cannedResponseCache['_responseParentMap'][$_val['cannedcategoryid']]);
                }

                $_SWIFT_XMLObject->EndParentTag('category');
            }
        }

        // Build the responses
        if (isset($_cannedResponseCache['_responseParentMap'][$_parentCannedCategoryID]) && count($_cannedResponseCache['_responseParentMap'][$_parentCannedCategoryID]) > 0) {
            self::BuildResponseMapXML($_SWIFT_XMLObject, $_cannedResponseCache['_responseParentMap'][$_parentCannedCategoryID]);
        }

        return true;
    }

    /**
     * Build the Response parent map
     *
     * @author Varun Shoor
     * @param SWIFT_XML $_SWIFT_XMLObject The SWIFT_XML Object Pointer
     * @param array $_responseParentContainer The Response Parent Container Array
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Canned_Exception If Invalid Data is Provided
     */
    protected static function BuildResponseMapXML(SWIFT_XML $_SWIFT_XMLObject, $_responseParentContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_responseParentContainer)) {
            return false;
        }

        if (!$_SWIFT_XMLObject instanceof SWIFT_XML || !$_SWIFT_XMLObject->GetIsClassLoaded()) {
            throw new SWIFT_Canned_Exception(SWIFT_INVALIDDATA);
        }

        $_cannedCategoryCache = self::$_cannedCategoryCache;
        $_cannedResponseCache = self::$_cannedResponseCache;

        extract($_cannedCategoryCache);
        extract($_cannedResponseCache);

        foreach ($_responseParentContainer as $_subKey => $_subVal) {
            if (in_array($_subVal['cannedcategoryid'], self::$_hiddenCategories)) {
                continue;
            }

            $_SWIFT_XMLObject->AddParentTag('response', array('id' => $_subVal['cannedresponseid'], 'title' => $_subVal['title']));

            if (!empty($_subVal['urldata'])) {
                $_SWIFT_XMLObject->AddTag('url', $_subVal['urldata']);
            }

            if (!empty($_subVal['imagedata'])) {
                $_SWIFT_XMLObject->AddTag('image', $_subVal['imagedata']);
            }

            if ($_subVal['responsetype'] == SWIFT_CannedResponse::TYPE_MESSAGE && !empty($_subVal['contents'])) {
                $_SWIFT_XMLObject->AddTag('message', $_subVal['contents']);
            }

            if ($_subVal['responsetype'] == SWIFT_CannedResponse::TYPE_CODE && !empty($_subVal['contents'])) {
                $_SWIFT_XMLObject->AddTag('code', $_subVal['contents'], array('lang' => 'plain'));
            }

            $_SWIFT_XMLObject->EndParentTag('response');
        }

        return true;
    }

    /**
     * Retrieve the Canned Category Options
     *
     * @author Varun Shoor
     * @param int $_selectedCannedCategoryID (OPTIONAL) The Canned Category ID to be selected by default
     * @return array
     */
    public static function GetCannedCategoryOptions($_selectedCannedCategoryID = 0, $_activeCannedCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::$_cannedCategoryCache) {
            self::$_cannedCategoryCache = SWIFT_CannedCategory::RetrieveCannedCategories();
        }

        $_cannedCategoryCache = self::$_cannedCategoryCache;

        $_optionContainer = array();

        $_optionContainer[0]['title'] = $_SWIFT->Language->Get('parentcategoryitem');
        $_optionContainer[0]['value'] = '0';
        $_optionContainer[0]['selected'] = $_selectedCannedCategoryID == 0;

        self::GetSubCannedCategoryOptions($_selectedCannedCategoryID, 0, $_optionContainer, 0, $_activeCannedCategoryID);

        return $_optionContainer;
    }

    /**
     * Retrieve the canned category options in a loop
     *
     * @author Varun Shoor
     * @param int $_selectedCannedCategoryID The Selected Canned Category ID
     * @param int $_parentCannedCategoryID The Parent Canned Category ID
     * @param array $_optionContainer The Option Container
     * @param int $_indent
     * @param bool $_activeCannedCategoryID
     * @return array
     * @throws \Base\Library\Staff\SWIFT_Staff_Exception
     */
    protected static function GetSubCannedCategoryOptions($_selectedCannedCategoryID, $_parentCannedCategoryID, &$_optionContainer, $_indent = 0, $_activeCannedCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cannedCategoryCache = self::$_cannedCategoryCache;

        $_childCount = 0;

        if (isset($_cannedCategoryCache['_cannedParentMap'][$_parentCannedCategoryID])) {
            $_childCount = count($_cannedCategoryCache['_cannedParentMap'][$_parentCannedCategoryID]);
        }

        if ($_childCount > 0) {
            $_childIndent = str_repeat('   ', $_indent);
            $_childPrefix = $_childIndent . '|- ';

            foreach ($_cannedCategoryCache['_cannedParentMap'][$_parentCannedCategoryID] as $_key => $_val) {
                if ($_activeCannedCategoryID == $_val['cannedcategoryid']) {
                    continue;
                }

                if ($_val['categorytype'] == SWIFT_CannedCategory::TYPE_PUBLIC || ($_val['categorytype'] == SWIFT_CannedCategory::TYPE_PRIVATE && $_val['staffid'] == $_SWIFT->Staff->GetStaffID())) {
                    $_optionArray['title'] = $_childPrefix . $_val['title'];
                    $_optionArray['value'] = $_val['cannedcategoryid'];
                    $_optionArray['selected'] = IIF($_selectedCannedCategoryID == $_val['cannedcategoryid'], true, false);

                    $_optionContainer[] = $_optionArray;

                    if (isset($_cannedCategoryCache['_cannedParentMap'][$_val['cannedcategoryid']]) && count($_cannedCategoryCache['_cannedParentMap'][$_val['cannedcategoryid']]) > 0) {
                        self::GetSubCannedCategoryOptions($_selectedCannedCategoryID, $_val['cannedcategoryid'], $_optionContainer, ($_indent + 1), $_activeCannedCategoryID);
                    }
                }
            }
        }

        return $_optionContainer;
    }

    /**
     * Render the options for the given category id
     *
     * @author Varun Shoor
     * @param int $_cannedCategoryID The Canned Category ID
     * @return string The Rendered Item HTML
     */
    protected static function GetTreeItemOptions($_cannedCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_returnHTML = '&nbsp;&nbsp;-&nbsp;&nbsp;' . '<div title="' . $_SWIFT->Language->Get('insertcategory') . '" class="cannedcategorynew" onclick="javascript: InsertCannedCategoryWindow(\'' . ($_cannedCategoryID) . '\');">&nbsp;</div> <div title="' . $_SWIFT->Language->Get('insertresponse') . '" class="cannedresponsenew" onclick="javascript: InsertCannedResponseWindow(\'' . ($_cannedCategoryID) . '\');">&nbsp;</div> <div title="' . $_SWIFT->Language->Get('filterresponses') . '" class="cannedresponsesearch" onclick="javascript: loadViewportData(\'' . SWIFT::Get('basename') . '/LiveChat/CannedCategory/QuickResponseFilter/category/' . ($_cannedCategoryID) . '/\');">&nbsp;</div> ';

        return $_returnHTML;
    }

    /**
     * Retrieve the Canned Category Tree HTML
     *
     * @author Varun Shoor
     * @param int $_selectedCannedCategoryID (OPTIONAL) The Canned Category ID to be selected by default
     * @return string
     */
    public static function GetCannedCategoryTree($_selectedCannedCategoryID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::$_cannedCategoryCache) {
            self::$_cannedCategoryCache = SWIFT_CannedCategory::RetrieveCannedCategories();
        }

        if (!self::$_cannedResponseCache) {
            self::$_cannedResponseCache = SWIFT_CannedResponse::RetrieveCannedResponses(false);
        }

        $_cannedCategoryCache = self::$_cannedCategoryCache;
        $_cannedResponseCache = self::$_cannedResponseCache;

        extract($_cannedResponseCache);

        $_extendedCannedCategoryTitle = '';
        if (isset($_cannedResponseCache['_responseParentMap'][0]) && count($_cannedResponseCache['_responseParentMap'][0]) > 0) {
            $_extendedCannedCategoryTitle .= ' <font color="red">(' . count($_cannedResponseCache['_responseParentMap'][0]) . ')</font>';
        }

        $_renderHTML = '<ul class="swifttree">';

        $_renderHTML .= '<li><span class="folder">&nbsp;&nbsp;&nbsp;' . $_SWIFT->Language->Get('rootcategory') . $_extendedCannedCategoryTitle . self::GetTreeItemOptions(0) . '</span>';

        self::GetSubCannedCategoryTree($_selectedCannedCategoryID, 0, $_renderHTML);

        $_renderHTML .= '</li></ul>';

        return $_renderHTML;
    }

    /**
     * Retrieve the canned category tree HTML in a loop
     *
     * @author Varun Shoor
     * @param int $_selectedCannedCategoryID The Selected Canned Category ID
     * @param int $_parentCannedCategoryID The Parent Canned Category ID
     * @param string $_renderHTML The Rendered HTML
     * @return string
     */
    protected static function GetSubCannedCategoryTree($_selectedCannedCategoryID, $_parentCannedCategoryID, &$_renderHTML)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cannedCategoryCache = self::$_cannedCategoryCache;
        $_cannedResponseCache = self::$_cannedResponseCache;

        $_childCount = 0;

        if (isset($_cannedCategoryCache['_cannedParentMap'][$_parentCannedCategoryID])) {
            $_childCount = count($_cannedCategoryCache['_cannedParentMap'][$_parentCannedCategoryID]);
        }

        if ($_childCount > 0) {
            $_renderHTML .= '<ul>';

            foreach ($_cannedCategoryCache['_cannedParentMap'][$_parentCannedCategoryID] as $_key => $_val) {
                if ($_val['categorytype'] == SWIFT_CannedCategory::TYPE_PUBLIC || ($_val['categorytype'] == SWIFT_CannedCategory::TYPE_PRIVATE && $_val['staffid'] == $_SWIFT->Staff->GetStaffID())) {
                    if ($_selectedCannedCategoryID == $_val['cannedcategoryid']) {
                        $_cannedCategoryTitle = '<b>' . htmlspecialchars($_val['title']) . '</b>';
                    } else {
                        $_cannedCategoryTitle = htmlspecialchars($_val['title']);
                    }

                    if (isset($_cannedResponseCache['_responseParentMap'][$_val['cannedcategoryid']]) && count($_cannedResponseCache['_responseParentMap'][$_val['cannedcategoryid']]) > 0) {
                        $_cannedCategoryTitle .= ' <font color="red">(' . count($_cannedResponseCache['_responseParentMap'][$_val['cannedcategoryid']]) . ')</font>';
                    }

                    $_renderHTML .= '<li><span class="' . IIF($_val['categorytype'] == SWIFT_CannedCategory::TYPE_PUBLIC, 'folder', 'folderfaded') . '"><a href="javascript: void(0);" onclick="javascript: EditCannedCategoryWindow(\'' . (int)($_val['cannedcategoryid']) . '\');">' . $_cannedCategoryTitle . '</a>' . self::GetTreeItemOptions($_val['cannedcategoryid']) . '</span>';

                    if (isset($_cannedCategoryCache['_cannedParentMap'][$_val['cannedcategoryid']]) && count($_cannedCategoryCache['_cannedParentMap'][$_val['cannedcategoryid']]) > 0) {
                        self::GetSubCannedCategoryTree($_selectedCannedCategoryID, $_val['cannedcategoryid'], $_renderHTML);
                    }

                    $_renderHTML .= '</li>';
                }
            }

            $_renderHTML .= '</ul>';
        }

        return $_renderHTML;
    }
}
