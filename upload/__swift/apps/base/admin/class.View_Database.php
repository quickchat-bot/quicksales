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

namespace Base\Admin;

use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The Database View Management Class
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_Database extends SWIFT_View
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
     * Renders the Table Info
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderTableInfo()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Start(get_short_class($this), '/Base/Database/TableInfo', SWIFT_UserInterface::MODE_INSERT, false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('database'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN DATABASE TAB
         * ###############################################
         */
        $_serverInformationContainer = $this->Database->GetADODBObject()->ServerInfo();
        $_TableInfoTabObject = $this->UserInterface->AddTab(IIF((strtoupper(DB_TYPE) == 'MYSQLI'), 'MYSQL', strtoupper(DB_TYPE)) . ': ' . $_serverInformationContainer['version'], 'icon_dbtables.gif', 'general', true);

        $_metaTableContainer = $this->Database->GetADODBObject()->MetaTables('TABLES');

        $_tableIndex = 1;
        if (is_array($_metaTableContainer)) {
            foreach ($_metaTableContainer as $_key => $_val) {
                $_TableInfoTabObject->Title('<b>' . $_tableIndex . '. ' . mb_strtoupper($_val) . '</b>', 'icon_settings2.gif', 6);
                $_databaseColumnContainer = $this->Database->GetADODBObject()->MetaColumns($_val);

                $_columnContainer = array();
                $_columnContainer[0]['width'] = '10';
                $_columnContainer[0]['value'] = '&nbsp;';

                $_columnContainer[1]['width'] = '';
                $_columnContainer[1]['value'] = $this->Language->Get('fieldname');
                $_columnContainer[1]['align'] = 'left';

                $_columnContainer[2]['width'] = '120';
                $_columnContainer[2]['value'] = $this->Language->Get('maxlength');
                $_columnContainer[2]['align'] = 'left';

                $_columnContainer[3]['width'] = '150';
                $_columnContainer[3]['value'] = $this->Language->Get('fieldtype');
                $_columnContainer[3]['align'] = 'left';

                $_columnContainer[4]['width'] = '150';
                $_columnContainer[4]['value'] = $this->Language->Get('fieldprimary');
                $_columnContainer[4]['align'] = 'left';

                $_columnContainer[5]['width'] = '150';
                $_columnContainer[5]['value'] = $this->Language->Get('fieldautoincrement');
                $_columnContainer[5]['align'] = 'left';

                $_TableInfoTabObject->Row($_columnContainer, 'gridtabletitlerow');

                $_tableIndex++;

                $_index = 1;
                foreach ($_databaseColumnContainer as $_columnKey => $_columnVal) {
                    $_columnContainer = array();
                    $_columnContainer[0]['width'] = '10';
                    $_columnContainer[0]['value'] = $_index;
                    $_columnContainer[0]['class'] = 'gridtabletitlerow';

                    $_columnContainer[1]['value'] = $_columnVal->name;
                    $_columnContainer[1]['align'] = 'left';

                    $_columnContainer[2]['value'] = $_columnVal->max_length;
                    $_columnContainer[2]['align'] = 'left';

                    $_columnContainer[3]['value'] = mb_strtoupper($_columnVal->type);
                    $_columnContainer[3]['align'] = 'left';

                    $_columnContainer[4]['value'] = IIF($_columnVal->primary_key == 1, $this->Language->Get('yes'), $this->Language->Get('no'));
                    $_columnContainer[4]['align'] = 'left';

                    $_columnContainer[5]['value'] = IIF($_columnVal->auto_increment == 1, $this->Language->Get('yes'), $this->Language->Get('no'));
                    $_columnContainer[5]['align'] = 'left';

                    $_TableInfoTabObject->Row($_columnContainer);

                    $_index++;
                }
            }
        }

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }
}

?>
