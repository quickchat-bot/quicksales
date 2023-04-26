<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Parser\Admin;

use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use Parser\Models\Log\SWIFT_ParserLog;
use SWIFT_View;

/**
 * The Parser Log view
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_ParserLog extends SWIFT_View
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
     * Render the Parser Log Form
     *
     * @author Varun Shoor
     *
     * @param SWIFT_ParserLog $_SWIFT_ParserLogObject The Parser\Models\Log\SWIFT_ParserLog Object Pointer (Only for EDIT Mode)
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Render(SWIFT_ParserLog $_SWIFT_ParserLogObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_queueCache = $this->Cache->Get('queuecache');

        $this->UserInterface->Start(get_short_class($this), '/Parser/ParserLog/ReParse/' . $_SWIFT_ParserLogObject->GetParserLogID(),
            SWIFT_UserInterface::MODE_EDIT, true);

        $this->UserInterface->SetDialogOptions(false);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('reprocessemail'), 'fa-check-circle');
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Parser/ParserLog/Delete/' .
            $_SWIFT_ParserLogObject->GetParserLogID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('parserlog'),
            SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);


        $_infoContainer = $_columnContainer = array();
        $_infoContainer[] = array($this->Language->Get('pparserlogid'), htmlspecialchars($_SWIFT_ParserLogObject->GetParserLogID()));
        $_infoContainer[] = array($this->Language->Get('ppticketid'), (int)($_SWIFT_ParserLogObject->GetProperty('typeid')));
        $_infoContainer[] = array($this->Language->Get('ppticketmaskid'), htmlspecialchars($_SWIFT_ParserLogObject->GetProperty('ticketmaskid')));
        $_infoContainer[] = array($this->Language->Get('ppdate'), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME,
            $_SWIFT_ParserLogObject->GetProperty('dateline')));
        $_infoContainer[] = array($this->Language->Get('pptimeline'),
            SWIFT_Date::ColorTime(DATENOW - $_SWIFT_ParserLogObject->GetProperty('dateline')));

        if (!isset($_queueCache['list'][$_SWIFT_ParserLogObject->GetProperty('emailqueueid')])) {
            $_infoContainer[] = array($this->Language->Get('ppemailqueue'), htmlspecialchars($this->Language->Get('na')));
        } else {
            $_infoContainer[] = array($this->Language->Get('ppemailqueue'),
                htmlspecialchars($_queueCache['list'][$_SWIFT_ParserLogObject->GetProperty('emailqueueid')]['email']));
        }

        $_infoContainer[] = array($this->Language->Get('ppstatus'),
            IIF($_SWIFT_ParserLogObject->GetProperty('logtype') == SWIFT_ParserLog::TYPE_SUCCESS, '<i class="fa fa-check-circle" aria-hidden="true"></i>&nbsp;' . $this->Language->Get('success'), '<i class="fa fa-minus-circle" aria-hidden="true"></i>&nbsp;' . $this->Language->Get('failure')));
        $_infoContainer[] = array($this->Language->Get('ppsubject'), htmlspecialchars($_SWIFT_ParserLogObject->GetProperty('subject')));
        $_infoContainer[] = array($this->Language->Get('ppfromemail'), htmlspecialchars($_SWIFT_ParserLogObject->GetProperty('fromemail')));
        $_infoContainer[] = array($this->Language->Get('pptoemail'), htmlspecialchars($_SWIFT_ParserLogObject->GetProperty('toemail')));
        $_infoContainer[] = array($this->Language->Get('ppsize'), FormattedSize($_SWIFT_ParserLogObject->GetProperty('size')));
        $_infoContainer[] = array($this->Language->Get('pptimetaken'), number_format($_SWIFT_ParserLogObject->GetProperty('parsetimetaken'), 3) .
            "&nbsp;" . $this->Language->Get('seconds'));

        $_GeneralTabObject->Title($this->Language->Get('generalinformation'), 'doublearrows.gif', '4');

        $_count = $_index = 0;
        $_lastIndex = count($_infoContainer) - 1;
        foreach ($_infoContainer as $_key => $_val) {
            $_columnContainer[$_index]['align'] = 'left';
            $_columnContainer[$_index]['valign'] = 'top';
            $_columnContainer[$_index]['nowrap'] = true;
            $_columnContainer[$_index]['value'] = $_val[0];
            $_columnContainer[$_index]['class'] = 'gridrow1';
            $_index++;
            $_columnContainer[$_index]['align'] = 'left';
            $_columnContainer[$_index]['valign'] = 'top';
            $_columnContainer[$_index]['nowrap'] = true;
            $_columnContainer[$_index]['value'] = $_val[1];
            $_columnContainer[$_index]['class'] = 'gridrow2';
            $_count++;
            $_index++;

            if ($_count == 2 || $_key == $_lastIndex) {
                $_GeneralTabObject->Row($_columnContainer);
                $_columnContainer = array();
                $_index = 0;
                $_count = 0;
            }
        }

        $_GeneralTabObject->Title($this->Language->Get('ppresult'), 'doublearrows.gif', '4');

        $_columnContainer = array();
        $_columnContainer[0]['value'] = htmlspecialchars($_SWIFT_ParserLogObject->GetProperty('description'));
        $_columnContainer[0]['align'] = 'left';
        $_columnContainer[0]['colspan'] = '4';
        $_GeneralTabObject->Row($_columnContainer);

        $_GeneralTabObject->Title($this->Language->Get('mimedata'), 'doublearrows.gif', '4');

        $_columnContainer = array();
        $_columnContainer[0]['align'] = 'left';
        $_columnContainer[0]['valign'] = 'top';
        $_columnContainer[0]['colspan'] = '4';
        $_columnContainer[0]['nowrap'] = true;
        $_columnContainer[0]['value'] = '<textarea style="WIDTH:99%;" name="contents" cols="50" rows="15" class="swifttextarea">' .
            htmlspecialchars($_SWIFT_ParserLogObject->GetProperty('contents')) . '</textarea>' . SWIFT_CRLF;
        $_columnContainer[0]['class'] = 'gridrow1';
        $_GeneralTabObject->Row($_columnContainer);

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Parser Log Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('ticketfiletypegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT parserlogs.* FROM ' . TABLE_PREFIX . 'parserlogs AS parserlogs LEFT JOIN ' .
                TABLE_PREFIX . 'parserlogdata AS parserlogdata ON (parserlogs.parserlogid = parserlogdata.parserlogid) WHERE (' .
                $this->UserInterfaceGrid->BuildSQLSearch('parserlogs.subject') . ' OR ' .
                $this->UserInterfaceGrid->BuildSQLSearch('parserlogs.fromemail') . ' OR ' .
                $this->UserInterfaceGrid->BuildSQLSearch('parserlogs.toemail') . ' OR ' .
                $this->UserInterfaceGrid->BuildSQLSearch('parserlogs.description') . ' OR ' .
                $this->UserInterfaceGrid->BuildSQLSearch('parserlogdata.contents') . ')',
                'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX . 'parserlogs AS parserlogs LEFT JOIN ' . TABLE_PREFIX .
                'parserlogdata AS parserlogdata ON (parserlogs.parserlogid = parserlogdata.parserlogid) WHERE (' .
                $this->UserInterfaceGrid->BuildSQLSearch('parserlogs.subject') . ' OR ' .
                $this->UserInterfaceGrid->BuildSQLSearch('parserlogs.fromemail') . ' OR ' .
                $this->UserInterfaceGrid->BuildSQLSearch('parserlogs.toemail') . ' OR ' .
                $this->UserInterfaceGrid->BuildSQLSearch('parserlogs.description') . ' OR ' .
                $this->UserInterfaceGrid->BuildSQLSearch('parserlogdata.contents') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'parserlogs AS parserlogs', 'SELECT COUNT(*) AS totalitems FROM ' .
            TABLE_PREFIX . 'parserlogs AS parserlogs');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('parserlogid', 'parserlogid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
            SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('parserlogs.subject', $this->Language->Get('emailsubjectresult'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('parserlogs.dateline', $this->Language->Get('date'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 170, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC),
            true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('parserlogs.parsetimetaken', $this->Language->Get('emailparsetime'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 130, SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        /**
         * BUG FIX - Mansi Wason <mansi.wason@opencart.com.vn>
         *
         * SWIFT-1045 'Filter in the Mail Parser log which could be used to show only those emails whose status is 'Failure'.'
         *
         * Comment - Added the new field for the log Type.
         */
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('parserlogs.logtype', $this->Language->Get('emaillogtype'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 130, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
            array('Parser\Admin\Controller_ParserLog', 'DeleteList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     *
     * @param array $_fieldContainer The Field Record Value Container
     *
     * @return array
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fieldContainer['parserlogs.subject'] = '<a href="' . SWIFT::Get('basename') . '/Parser/ParserLog/View/' . (int)($_fieldContainer['parserlogid']) . '" onclick="' . "javascript: return UICreateWindowExtended(event, '" .
            SWIFT::Get('basename') . "/Parser/ParserLog/View/" . (int)($_fieldContainer['parserlogid']) . "', 'editparserlog', '" .
            $_SWIFT->Language->Get('winviewparserlog') . "', '" . $_SWIFT->Language->Get('loadingwindow') . "', 900, 710, true, this);" .
            '" title="' . $_SWIFT->Language->Get('edit') . '">' . sprintf($_SWIFT->Language->Get('emailsubresultformat'),
                htmlspecialchars($_fieldContainer['subject']), htmlspecialchars($_fieldContainer['description'])) . '</a>';

        $_fieldContainer['parserlogs.parsetimetaken'] = number_format($_fieldContainer['parsetimetaken'], 3) . "&nbsp;" .
            $_SWIFT->Language->Get('seconds');
        $_fieldContainer['icon'] = IIF($_fieldContainer['logtype'] == SWIFT_ParserLog::TYPE_SUCCESS, '<i class="fa fa-check-circle" aria-hidden="true"></i>', '<i class="fa fa-minus-circle" aria-hidden="true"></i>');
        $_fieldContainer['parserlogs.dateline'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_fieldContainer['dateline']);
        $_fieldContainer['parserlogs.logtype'] = $_fieldContainer['logtype'];

        return $_fieldContainer;
    }
}

?>
