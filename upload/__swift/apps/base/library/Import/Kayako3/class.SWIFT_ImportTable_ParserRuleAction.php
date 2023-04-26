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

namespace Base\Library\Import\QuickSupport3;

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;
use Parser\Models\Rule\SWIFT_ParserRule;

/**
 * Import Table: ParserRuleAction
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_ParserRuleAction extends SWIFT_ImportTable
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_ImportManager $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'ParserRuleAction');

        if (!$this->TableExists(TABLE_PREFIX . 'ruleactions')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Rule:ParserRule', APP_PARSER);
    }

    /**
     * Import the data based on offset in the table
     *
     * @author Varun Shoor
     * @return int The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetOffset() == 0) {
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "parserruleactions");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ruleactions ORDER BY ruleactionid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newParserRuleID = $this->ImportManager->GetImportRegistry()->GetKey('parserrule', $this->DatabaseImport->Record['parserruleid']);
            if ($_newParserRuleID == false) {
                $this->GetImportManager()->AddToLog('Parser rule action import failed due to non existant parser rule id (incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            /*

            // Pre Parse Actions
            define("PARSERACTION_FORWARD", "forward");
            define("PARSERACTION_REPLY", "reply");
            define("PARSERACTION_IGNORE", "ignore");
            define("PARSERACTION_NOAUTORESPONDER", "noautoresponder");
            define("PARSERACTION_NOALERTRULES", "noalertrules");
            define("PARSERACTION_NOTICKET", "noticket");

            // Post Parse Actions
            define("PARSERACTION_DEPARTMENT", "department");
            define("PARSERACTION_OWNER", "owner");
            define("PARSERACTION_STATUS", "status");
            define("PARSERACTION_PRIORITY", "priority");
            define("PARSERACTION_ADDNOTE", "addnote");
            define("PARSERACTION_FLAGTICKET", "flagticket");
            define("PARSERACTION_SLAPLAN", "slaplan");

            */

            $_typeID = '0';
            $_typeChar = $this->DatabaseImport->Record['typechar'];
            $_typeData = $this->DatabaseImport->Record['typedata'];
            if ($this->DatabaseImport->Record['name'] == 'department') {
                $_typeID = $this->ImportManager->GetImportRegistry()->GetKey('department', $this->DatabaseImport->Record['typeid']);
            } elseif ($this->DatabaseImport->Record['name'] == 'owner') {
                $_typeID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['typeid']);
            } elseif ($this->DatabaseImport->Record['name'] == 'status') {
                $_typeID = $this->ImportManager->GetImportRegistry()->GetKey('ticketstatus', $this->DatabaseImport->Record['typeid']);
            } elseif ($this->DatabaseImport->Record['name'] == 'priority') {
                $_typeID = $this->ImportManager->GetImportRegistry()->GetKey('ticketpriority', $this->DatabaseImport->Record['typeid']);
            } elseif ($this->DatabaseImport->Record['name'] == 'slaplan') {
                $_typeID = $this->ImportManager->GetImportRegistry()->GetKey('slaplan', $this->DatabaseImport->Record['typeid']);
            } elseif ($this->DatabaseImport->Record['name'] == 'flagticket') {
                $_typeID = (int)($this->DatabaseImport->Record['typeid']);
            }

            $this->GetImportManager()->AddToLog('Importing Parser Rule Action ID: ' . htmlspecialchars($this->DatabaseImport->Record['ruleactionid']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'parserruleactions',
                array('parserruleid' => $_newParserRuleID, 'name' => $this->DatabaseImport->Record['name'],
                    'typeid' => $_typeID, 'typedata' => $_typeData, 'typechar' => $_typeChar), 'INSERT');
            $_parserRuleActionID = $this->Database->InsertID();
        }

        SWIFT_ParserRule::RebuildCache();

        return $_count;
    }

    /**
     * Retrieve the total number of records in a table
     *
     * @author Varun Shoor
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTotal()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "ruleactions");
        if (isset($_countContainer['totalitems'])) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Retrieve the number of items to process in a pass
     *
     * @author Varun Shoor
     * @return int The Number of Items
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetItemsPerPass()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return 500;
    }
}

?>
