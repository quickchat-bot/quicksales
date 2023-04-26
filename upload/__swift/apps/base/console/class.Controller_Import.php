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

namespace Base\Console;

use Base\Library\Import\SWIFT_ImportManager;
use Controller_console;
use SWIFT;
use SWIFT_Console;
use SWIFT_Exception;

/**
 *
 *
 * @author Varun Shoor
 */
class Controller_Import extends Controller_console
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('admin_import');
    }

    /**
     * Import from V3
     *
     * @author Varun Shoor
     * @param int|false $_limitNumberOfPasses (OPTIONAL) Limit the number of passes and bail out
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Version3($_limitNumberOfPasses = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->Console->WriteLine('====================', false, SWIFT_Console::COLOR_GREEN);
        $this->Console->WriteLine('Version3 Import', false, SWIFT_Console::COLOR_YELLOW);
        $this->Console->WriteLine('====================', false, SWIFT_Console::COLOR_GREEN);
        $this->Console->WriteLine();

        $_databaseHost = $this->Console->Prompt('Database Host:');
        $_databaseName = $this->Console->Prompt('Database Name:');
        $_databasePort = $this->Console->Prompt('Database Port (enter for default port):');
        $_databaseSocket = $this->Console->Prompt('Database Socket (enter for default socket):');
        $_databaseUsername = $this->Console->Prompt('Database Username:');
        $_databasePassword = $this->Console->Prompt('Database Password:');

        if (empty($_databasePort)) {
            $_databasePort = 3306;
        }

        $_SWIFT_ImportManagerObject = SWIFT_ImportManager::GetImportManagerObject('Kayako3');

//        $_SWIFT_ImportManagerObject->GetImportRegistry()->DeleteAll();

        $_SWIFT_ImportManagerObject->UpdateForm($_databaseHost, $_databaseName, $_databasePort, $_databaseUsername, $_databasePassword, $_databaseSocket);

        $_importResult = $_SWIFT_ImportManagerObject->ImportPre();

        if (!$_importResult) {
            $_errorContainer = SWIFT::GetErrorContainer();

            if (_is_array($_errorContainer)) {
                foreach ($_errorContainer as $_error) {
                    $this->Console->WriteLine($this->Console->Red($_error['title'] . SWIFT_CRLF . $_error['message']));
                }
            }

            return false;
        }

        $_processTotalRecordCount = true;
        $_processedRecords = $_SWIFT_ImportManagerObject->GetProcessedRecordCount();
        if ($_processedRecords > 0) {
            // @codeCoverageIgnoreStart
            // Will not be reached
            $_processTotalRecordCount = false;
        }
        // @codeCoverageIgnoreEnd

//        $_SWIFT_ImportManagerObject->ResetProcessedRecordCount();
        $_SWIFT_ImportManagerObject->StartImport($_processTotalRecordCount);

        $_passCount = 0;
        $_percentage = 0;
        $_recordsProcessed = 0;
        $_totalRecordCount = 0;

        while ($_SWIFT_ImportTableObject = $_SWIFT_ImportManagerObject->Import()) {
            $_logContainer = $_SWIFT_ImportManagerObject->GetLog();
            $_percentage = $_SWIFT_ImportManagerObject->GetProcessedPercent();
            $_totalRecordCount = $_SWIFT_ImportManagerObject->GetTotalRecordCount();
            $_startTime = $_SWIFT_ImportManagerObject->GetStartTime();
            $_recordsProcessed = $_SWIFT_ImportManagerObject->GetProcessedRecordCount();
            $_remainingRecords = $_totalRecordCount - $_recordsProcessed;

            $_tableTitle = $_SWIFT_ImportTableObject->GetTableName();

            $this->Console->WriteLine('Table Name: ' . $_SWIFT_ImportTableObject->GetTableName() . ', Total Records: ' . $_totalRecordCount . ', Processed: ' . $_recordsProcessed . ', Percent: ' . $this->Console->Red($_percentage . '%'));
            //        sleep(2);
            foreach ($_logContainer as $_logEntry) {
                if ($_logEntry[0] == SWIFT_ImportManager::LOG_FAILURE) {
                    $_errorMessage = '';
                    if (isset($_logEntry[2])) {
                        $_errorMessage = $_logEntry[2];
                    }
                    $this->Console->WriteLine($this->Console->Red('FAILED: ' . $_logEntry[1] . ' ' . $_errorMessage));
                }
            }

            $_passCount++;

            if ($_limitNumberOfPasses !== false && !empty($_limitNumberOfPasses) && $_passCount >= $_limitNumberOfPasses) {
                $this->Console->WriteLine($this->Console->Green('Breaking at Pass #' . $_passCount));

                break;
            }
        }

        $_SWIFT_ImportTableObject = $_SWIFT_ImportManagerObject->Import();
        if (!$_SWIFT_ImportTableObject && ($_recordsProcessed >= $_totalRecordCount || $_percentage >= 100)) {
            $this->Console->WriteLine($this->Console->Green('Completed'));
        }

        return true;
    }
}
