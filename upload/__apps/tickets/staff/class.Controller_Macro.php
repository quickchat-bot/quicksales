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

namespace Tickets\Staff;

use Controller_StaffBase;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use Base\Library\HTML\SWIFT_HTML;
use Tickets\Models\Macro\SWIFT_MacroCategory;
use Tickets\Models\Macro\SWIFT_MacroReply;

/**
 * The Macro Search + Menu Controller
 *
 * @author Varun Shoor
 */
class Controller_Macro extends Controller_StaffBase
{
    // Core Constants
    const MENU_ID = 2;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_STAFF);

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');
    }

    /**
     * Attempt to retrieve the macro category id data and dispatch it as JSON
     *
     * @author Varun Shoor
     * @param int $_macroReplyID The Macro Reply ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_macroReplyID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_macroReplyID))
        {
            echo json_encode(array('contents' => '', 'departmentid' => '-1', 'ownerstaffid' => '-1', 'tickettypeid' => '-1', 'ticketstatusid' => '-1',
                'priorityid' => '-1', 'tagcontents' => array()));
            return false;
        }

        $_SWIFT_MacroReplyObject = new SWIFT_MacroReply(new SWIFT_DataID($_macroReplyID));
        if (!$_SWIFT_MacroReplyObject instanceof SWIFT_MacroReply || !$_SWIFT_MacroReplyObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $_SWIFT_MacroReplyObject->UpdateUsage();

        $_doContinue = false;
        if ($_SWIFT_MacroReplyObject->GetProperty('macrocategoryid') == '0')
        {
            $_doContinue = true;
        } else {

            $_SWIFT_MacroCategoryObject = new SWIFT_MacroCategory(new SWIFT_DataID($_SWIFT_MacroReplyObject->GetProperty('macrocategoryid')));
            if (!$_SWIFT_MacroCategoryObject instanceof SWIFT_MacroCategory || !$_SWIFT_MacroCategoryObject->GetIsClassLoaded())
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            if (($_SWIFT_MacroCategoryObject->GetProperty('categorytype') == SWIFT_MacroCategory::TYPE_PUBLIC && $_SWIFT_MacroCategoryObject->GetProperty('restrictstaffgroupid') == '0') ||
                    ($_SWIFT_MacroCategoryObject->GetProperty('categorytype') == SWIFT_MacroCategory::TYPE_PUBLIC && $_SWIFT_MacroCategoryObject->GetProperty('restrictstaffgroupid') == $_SWIFT->Staff->GetProperty('staffgroupid')) ||
                    ($_SWIFT_MacroCategoryObject->GetProperty('categorytype') == SWIFT_MacroCategory::TYPE_PRIVATE && $_SWIFT_MacroCategoryObject->GetProperty('staffid') == $_SWIFT->Staff->GetStaffID()))
            {
                $_doContinue = true;
            }
        }

        if ($_doContinue == true)
        {
            $_finalDataStore = $_SWIFT_MacroReplyObject->GetDataStore();
            $_finalDataStore['tagcontents'] = mb_unserialize($_finalDataStore['tagcontents']);

            $_isHTML = SWIFT_HTML::DetectHTMLContent($_finalDataStore['contents']);

            If (!$_isHTML){
                $_finalDataStore['contents'] = nl2br($_finalDataStore['contents']);
            }

            if ($_finalDataStore['ownerstaffid'] == '-2')
            {
                $_finalDataStore['ownerstaffid'] = $_SWIFT->Staff->GetStaffID();
            }

            echo json_encode($_finalDataStore);
        }

        return true;
    }

    /**
     * Search macros
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLookup()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['q']) || trim($_POST['q']) == '')
        {
            return false;
        }

        $_POST['q'] = trim($_POST['q']);

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1677 Macro lookup doesn't work for macros in parent category
         *
         * Comments: None
         */

        $this->Database->QueryLimit("SELECT macroreplies.*, macroreplydata.*, macrocategories.* FROM " . TABLE_PREFIX . "macroreplies AS macroreplies
            LEFT JOIN " . TABLE_PREFIX . "macroreplydata AS macroreplydata ON (macroreplies.macroreplyid = macroreplydata.macroreplyid)
            LEFT JOIN " . TABLE_PREFIX . "macrocategories AS macrocategories ON (macroreplies.macrocategoryid = macrocategories.macrocategoryid)
            WHERE ((" . BuildSQLSearch('macroreplies.subject', $_POST['q']) . ")
                OR (" . BuildSQLSearch('macroreplydata.contents', $_POST['q']) . ")
                OR (" . BuildSQLSearch('macrocategories.title', $_POST['q']) . "))
                AND (macrocategories.categorytype = '" . SWIFT_MacroCategory::TYPE_PUBLIC . "' OR (macrocategories.categorytype = '" . SWIFT_MacroCategory::TYPE_PRIVATE . "' AND macrocategories.staffid = '" . $_SWIFT->Staff->GetStaffID() . "') OR macroreplies.macrocategoryid = '0')
            ORDER BY macroreplies.subject ASC", 6);
        while ($this->Database->NextRecord())
        {
            /* Bug Fix : Saloni Dhall
             * SWIFT-3739 : Security issue (medium)
             * Comments : This ajax request gets title, subject, folder image display data and further passed to oldautocomplete plugin in order to display data in div. This is the only approach which seems right, so sanitizing data at the time of rendering.
             */
            $_displayHTML = '<b><img src="' . SWIFT::Get('themepathimages') . 'icon_folderyellow3.gif' . '" align="absmiddle" border="0" /> ' . $this->Input->SanitizeForXSS($this->Database->Record['title']). '</b><br />';
            $_displayHTML .= $this->Input->SanitizeForXSS($this->Database->Record['subject']);
            echo str_replace('|', '', $_displayHTML) . '|' . $this->Database->Record['macroreplyid'] . SWIFT_CRLF;
        }

        return true;
    }

    /**
     * Retrieve the menu
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetMenu()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_macroCategoryContainer = $_macroReplyContainer = $_parentCategoryIDList = array();

        $_macroCategoryContainer[0] = array();
        $_macroCategoryContainer[0]['subcategories'] = array();
        $_macroCategoryContainer[0]['replies'] = array();

        // First get all the categories that are directly under the parent category
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "macrocategories WHERE parentcategoryid = '0' ORDER BY title ASC");
        while ($this->Database->NextRecord())
        {
            if (($this->Database->Record['categorytype'] == SWIFT_MacroCategory::TYPE_PUBLIC && $this->Database->Record['restrictstaffgroupid'] == '0') ||
                    ($this->Database->Record['categorytype'] == SWIFT_MacroCategory::TYPE_PUBLIC && $this->Database->Record['restrictstaffgroupid'] == $_SWIFT->Staff->GetProperty('staffgroupid')) ||
                    ($this->Database->Record['categorytype'] == SWIFT_MacroCategory::TYPE_PRIVATE && $this->Database->Record['staffid'] == $_SWIFT->Staff->GetStaffID()))
            {
                $_macroCategoryContainer[$this->Database->Record['macrocategoryid']] = $this->Database->Record;
                $_macroCategoryContainer[$this->Database->Record['macrocategoryid']]['subcategories'] = array();
                $_macroCategoryContainer[$this->Database->Record['macrocategoryid']]['replies'] = array();

                $_macroCategoryContainer[0]['subcategories'][$this->Database->Record['macrocategoryid']] = &$_macroCategoryContainer[$this->Database->Record['macrocategoryid']];

                $_parentCategoryIDList[] = $this->Database->Record['macrocategoryid'];
            }
        }

        // Now get all sub categories
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "macrocategories WHERE parentcategoryid != '0' ORDER BY title ASC");

        while ($this->Database->NextRecord())
        {
            if (($this->Database->Record['categorytype'] == SWIFT_MacroCategory::TYPE_PUBLIC && $this->Database->Record['restrictstaffgroupid'] == '0')
                || ($this->Database->Record['categorytype'] == SWIFT_MacroCategory::TYPE_PUBLIC && $this->Database->Record['restrictstaffgroupid'] == $_SWIFT->Staff->GetProperty('staffgroupid'))
                || ($this->Database->Record['categorytype'] == SWIFT_MacroCategory::TYPE_PRIVATE && $this->Database->Record['staffid'] == $_SWIFT->Staff->GetStaffID()))
            {
                $_macroCategoryContainer[$this->Database->Record['macrocategoryid']] = $this->Database->Record;
                $_macroCategoryContainer[$this->Database->Record['macrocategoryid']]['subcategories'] = array();
                $_macroCategoryContainer[$this->Database->Record['macrocategoryid']]['replies'] = array();
                $_macroCategoryContainer[$this->Database->Record['parentcategoryid']]['subcategories'][$this->Database->Record['macrocategoryid']] = &$_macroCategoryContainer[$this->Database->Record['macrocategoryid']];
            }
        }

        foreach ($_macroCategoryContainer as $_key => $_val)
        {
            if (!isset($_val['parentcategoryid']) || !isset($_macroCategoryContainer[$_val['parentcategoryid']]))
            {
                continue;
            }

            $_macroCategoryContainer[$_val['parentcategoryid']]['subcategories'][$_val['macrocategoryid']] = &$_macroCategoryContainer[$_val['macrocategoryid']];
        }

        // Now get all the replies
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "macroreplies ORDER BY subject ASC");
        while ($this->Database->NextRecord())
        {
            if (!isset($_macroCategoryContainer[$this->Database->Record['macrocategoryid']]))
            {
                continue;
            }

            $_macroCategoryContainer[$this->Database->Record['macrocategoryid']]['replies'][$this->Database->Record['macroreplyid']] = $this->Database->Record;
        }

        echo $this->RenderMenu($_macroCategoryContainer[0]);

        return true;
    }

    /**
     * Render the Menu
     *
     * @author Varun Shoor
     * @param array $_macroCategoryContainer The Macro Category Container
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RenderMenu($_macroCategoryContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_macroCategoryContainer)) {
            return '';
        }

        $_returnHTML = '<ul>';

        $_itemCount = 0;

        if (isset($_macroCategoryContainer['subcategories']))
        {
            foreach ($_macroCategoryContainer['subcategories'] as $_macroCategoryID => $_macroCategory)
            {
                $_itemCount++;

                $_returnHTML .= '<li><a href="#"><img src="' . SWIFT::Get('themepathimages') . 'icon_folderyellow3.gif" align="absmiddle" border="0" /> ' . htmlspecialchars($_macroCategory['title']) . '</a>';
                    $_returnHTML .= $this->RenderMenu($_macroCategory);
                $_returnHTML .= '</li>';
            }
        }

        if (isset($_macroCategoryContainer['replies']))
        {
            foreach ($_macroCategoryContainer['replies'] as $_macroReplyID => $_macroReply)
            {
                $_returnHTML .= '<li><a href="#m_' . $_macroReplyID . '">' . htmlspecialchars($_macroReply['subject']) . '</a></li>';

                $_itemCount++;
            }
        }

        if (empty($_itemCount))
        {
            $_returnHTML .= '<li><a href="#m_0">' . $this->Language->Get('noitemstodisplay') . '</a></li>';
        }

        $_returnHTML .= '</ul>';

        return $_returnHTML;
    }
}
