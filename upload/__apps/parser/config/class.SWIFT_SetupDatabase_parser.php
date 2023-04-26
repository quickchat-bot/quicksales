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
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser;

use Base\Library\Rules\SWIFT_Rules;
use Parser\Models\Breakline\SWIFT_Breakline;
use SWIFT_Cron;
use Parser\Models\EmailQueue\SWIFT_EmailQueuePipe;
use Parser\Library\EmailQueue\SWIFT_EmailQueueType_Tickets;
use SWIFT_Exception;
use SWIFT_Loader;
use Parser\Models\Loop\SWIFT_LoopRule;
use Parser\Models\Rule\SWIFT_ParserRule;
use SWIFT_SetupDatabase;
use SWIFT_SetupDatabaseIndex;
use SWIFT_SetupDatabaseTable;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;

/**
 * The Main Installer
 *
 * @author Varun Shoor
 */
class SWIFT_SetupDatabase_parser extends SWIFT_SetupDatabase
{
    // Core Constants
    const PAGE_COUNT = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct(APP_PARSER);

        // Parser Libraries
        SWIFT_Loader::LoadModel('Breakline:Breakline', APP_PARSER, false);
        SWIFT_Loader::LoadModel('Loop:LoopRule', APP_PARSER, false);
        SWIFT_Loader::LoadModel('Rule:ParserRule', APP_PARSER, false);
    }

    /**
     * Loads the table into the container
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function LoadTables()
    {
        // ======= PARSERLOGDATA =======
        $this->AddTable('parserlogdata', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "parserlogdata", "parserlogdataid I PRIMARY AUTO NOTNULL, parserlogid I DEFAULT '0' NOTNULL, contents X2"));
        $this->AddIndex('parserlogdata', new SWIFT_SetupDatabaseIndex("parserlogdata1", TABLE_PREFIX . "parserlogdata", "parserlogid"));

        // ======= PARSERRULECRITERIA =======
        $this->AddTable('parserrulecriteria', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "parserrulecriteria", "parserrulecriteriaid I PRIMARY AUTO NOTNULL, parserruleid I DEFAULT '0' NOTNULL, name C(100) DEFAULT '' NOTNULL, ruleop I2 DEFAULT '0' NOTNULL, rulematch C(255) DEFAULT '' NOTNULL, rulematchtype I2 DEFAULT '0' NOTNULL"));
        $this->AddIndex('parserrulecriteria', new SWIFT_SetupDatabaseIndex("parserrulecriteria1", TABLE_PREFIX . "parserrulecriteria", "parserruleid"));

        // ======= PARSERRULEACTIONS =======
        $this->AddTable('parserruleactions', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "parserruleactions", "parserruleactionid I PRIMARY AUTO NOTNULL, parserruleid I DEFAULT '0' NOTNULL, name C(100) DEFAULT '' NOTNULL, typeid I DEFAULT '0' NOTNULL, typedata X2, typechar C(255) DEFAULT '' NOTNULL"));
        $this->AddIndex('parserruleactions', new SWIFT_SetupDatabaseIndex("parserruleactions1", TABLE_PREFIX . "parserruleactions", "parserruleid"));

        return true;
    }

    /**
     * Get the Page Count for Execution
     *
     * @author Varun Shoor
     * @return int
     */
    public function GetPageCount()
    {
        return self::PAGE_COUNT;
    }

    /**
     * Function that does the heavy execution
     *
     * @author Varun Shoor
     *
     * @param int $_pageIndex The Page Index
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function Install($_pageIndex)
    {
        parent::Install($_pageIndex);

        /*
         * ###############################################
         * INSERT DEFAULT BREAKLINES
         * ###############################################
         */
        SWIFT_Breakline::Create('----- Original Message -----', false, '1');
        SWIFT_Breakline::Create('-----Original Message-----', false, '2');
        SWIFT_Breakline::Create('<!-- Break Line -->', false, '3');
        SWIFT_Breakline::Create('====== Please reply above this line ======', false, '4');
        SWIFT_Breakline::Create('_____', false, '5');

        /*
         * ###############################################
         * INSERT DEFAULT LOOP RULES
         * ###############################################
         */
        SWIFT_LoopRule::Create($this->Language->Get('defaultrule'), 600, 2, 600, true);

        /*
         * ###############################################
         * CRON
         * ###############################################
         */
        SWIFT_Cron::Create('parser', 'Parser', 'ParserMinute', 'POP3IMAP', '0', '10', '0', false);
        SWIFT_Cron::Create('parsercleanup', 'Parser', 'ParserDaily', 'Cleanup', '0', '0', '-1', true);

        /**
         * ---------------------------------------------
         * PARSER RULES
         * ---------------------------------------------
         */
        SWIFT_ParserRule::Create(
            'Undelivered Mail Returned to Sender (Loop Prevention)',
            true,
            1,
            SWIFT_ParserRule::TYPE_PREPARSE,
            SWIFT_ParserRule::RULE_MATCHEXTENDED,
            true,
            array(array('name' => SWIFT_ParserRule::PARSER_SUBJECT, 'ruleop' => SWIFT_ParserRule::OP_CONTAINS, 'rulematch' => 'Undelivered Mail Returned to Sender', 'rulematchtype' => SWIFT_ParserRule::RULE_MATCHALL)),
            array(array('name' => SWIFT_ParserRule::PARSERACTION_IGNORE, 'typeid' => '1'))
        );

        SWIFT_ParserRule::Create(
            'DELIVERY FAILURE (Loop Prevention)',
            true,
            2,
            SWIFT_ParserRule::TYPE_PREPARSE,
            SWIFT_ParserRule::RULE_MATCHEXTENDED,
            true,
            array(array('name' => SWIFT_ParserRule::PARSER_SUBJECT, 'ruleop' => SWIFT_ParserRule::OP_CONTAINS, 'rulematch' => 'DELIVERY FAILURE', 'rulematchtype' => SWIFT_ParserRule::RULE_MATCHALL)),
            array(array('name' => SWIFT_ParserRule::PARSERACTION_IGNORE, 'typeid' => '1'))
        );

        SWIFT_ParserRule::Create(
            'Mail Delivery System (Loop Prevention)',
            true,
            3,
            SWIFT_ParserRule::TYPE_PREPARSE,
            SWIFT_ParserRule::RULE_MATCHEXTENDED,
            true,
            array(array('name' => SWIFT_ParserRule::PARSER_SENDERNAME, 'ruleop' => SWIFT_ParserRule::OP_CONTAINS, 'rulematch' => 'Mail Delivery', 'rulematchtype' => SWIFT_ParserRule::RULE_MATCHALL)),
            array(array('name' => SWIFT_ParserRule::PARSERACTION_IGNORE, 'typeid' => '1'))
        );

        SWIFT_ParserRule::Create(
            'mailer-daemon (Loop Prevention)',
            true,
            4,
            SWIFT_ParserRule::TYPE_PREPARSE,
            SWIFT_ParserRule::RULE_MATCHEXTENDED,
            true,
            array(array('name' => SWIFT_ParserRule::PARSER_SENDEREMAIL, 'ruleop' => SWIFT_ParserRule::OP_CONTAINS, 'rulematch' => 'mailer-daemon', 'rulematchtype' => SWIFT_ParserRule::RULE_MATCHALL)),
            array(array('name' => SWIFT_ParserRule::PARSERACTION_IGNORE, 'typeid' => '1'))
        );

        SWIFT_ParserRule::Create(
            'Returned mail: see transcript for details (Loop Prevention)',
            true,
            5,
            SWIFT_ParserRule::TYPE_PREPARSE,
            SWIFT_ParserRule::RULE_MATCHEXTENDED,
            true,
            array(array('name' => SWIFT_ParserRule::PARSER_SUBJECT, 'ruleop' => SWIFT_ParserRule::OP_CONTAINS, 'rulematch' => 'Returned mail: see transcript for details', 'rulematchtype' => SWIFT_ParserRule::RULE_MATCHALL)),
            array(array('name' => SWIFT_ParserRule::PARSERACTION_IGNORE, 'typeid' => '1'))
        );

        SWIFT_ParserRule::Create(
            'Delivery Status Notification (Loop Prevention)',
            true,
            6,
            SWIFT_ParserRule::TYPE_PREPARSE,
            SWIFT_ParserRule::RULE_MATCHEXTENDED,
            true,
            array(array('name' => SWIFT_ParserRule::PARSER_SUBJECT, 'ruleop' => SWIFT_ParserRule::OP_CONTAINS, 'rulematch' => 'Delivery Status Notification (Failure)', 'rulematchtype' => SWIFT_ParserRule::RULE_MATCHALL)),
            array(array('name' => SWIFT_ParserRule::PARSERACTION_IGNORE, 'typeid' => '1'))
        );

        SWIFT_ParserRule::Create(
            'Delivery has failed to these recipients (Loop Prevention)',
            true,
            7,
            SWIFT_ParserRule::TYPE_PREPARSE,
            SWIFT_ParserRule::RULE_MATCHEXTENDED,
            true,
            array(
                array('name' => SWIFT_ParserRule::PARSER_BODY, 'ruleop' => SWIFT_ParserRule::OP_CONTAINS, 'rulematch' => 'Delivery has failed to these recipients', 'rulematchtype' => SWIFT_ParserRule::RULE_MATCHALL),
                array('name' => SWIFT_ParserRule::PARSER_SENDEREMAIL, 'ruleop' => SWIFT_ParserRule::OP_CONTAINS, 'rulematch' => 'postmaster@', 'rulematchtype' => SWIFT_ParserRule::RULE_MATCHALL)
            ),
            array(array('name' => SWIFT_ParserRule::PARSERACTION_IGNORE, 'typeid' => '1'))
        );

        $this->InstallSampleData();

        $this->ExecuteQueue();

        return true;
    }

    /**
     * @author Saloni Dhall <saloni.dhall@kayako.com>
     * @author Utsav Handa <utsav.handa@kayako.com>
     *
     * @return bool
     */
    public function InstallSampleData()
    {
        if (!defined('INSTALL_SAMPLE_DATA') || INSTALL_SAMPLE_DATA != true) {
            return false;
        }

        // Create an Email Queue
        if (preg_match('/(.*?)\.kayako.com/', parse_url($_POST['producturl'], PHP_URL_HOST), $_matches)) {
            SWIFT_EmailQueuePipe::Create(
                sprintf($this->Language->Get('sample_emailqueueaddress'), strtolower(array_pop($_matches))),
                new SWIFT_EmailQueueType_Tickets(1, 1, 1, 1, 1, 0),
                false,
                false,
                false,
                false,
                false,
                '1'
            );
        }

        return true;
    }

    /**
     * Uninstalls the App
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Uninstall()
    {
        parent::Uninstall();

        SWIFT_Cron::DeleteOnName(array('parser', 'parsercleanup'));

        return true;
    }

    /**
     * Upgrade from 4.00.911
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_00_911()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_processActionList = array(SWIFT_ParserRule::PARSERACTION_ADDTAGS, SWIFT_ParserRule::PARSERACTION_REMOVETAGS);
        $_finalUpdateContainer = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserruleactions WHERE name IN (" . BuildIN($_processActionList) . ")");
        while ($this->Database->NextRecord()) {
            $_unserializedData = unserialize($this->Database->Record['typedata']);

            $_finalUpdateContainer[$this->Database->Record['name']] = json_encode($_unserializedData);
        }

        foreach ($_finalUpdateContainer as $_name => $_typeData) {
            $this->Database->AutoExecute(TABLE_PREFIX . 'parserruleactions', array('typedata' => $_typeData), 'UPDATE', "name = '" . $this->Database->Escape($_name) . "'");
        }

        return true;
    }

    /**
     * Upgrade from 4.01.341
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_01_341()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Retrieve all parser rules which dont have extended match
        $_parserRuleContainer = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "parserrules WHERE matchtype != '" . SWIFT_Rules::RULE_MATCHEXTENDED . "'");
        while ($this->Database->NextRecord()) {
            $_parserRuleContainer[$this->Database->Record['parserruleid']] = $this->Database->Record;
        }

        foreach ($_parserRuleContainer as $_parserRuleID => $_parserRule) {
            // Update individual criteria's
            $this->Database->AutoExecute(TABLE_PREFIX . 'parserrulecriteria', array('rulematchtype' => $_parserRule['matchtype']), 'UPDATE', "parserruleid = '" . (int)($_parserRuleID) . "'");

            // Reset the base match type to extended
            $this->Database->AutoExecute(TABLE_PREFIX . 'parserrules', array('matchtype' => SWIFT_Rules::RULE_MATCHEXTENDED), 'UPDATE', "parserruleid = '" . (int)($_parserRuleID) . "'");
        }

        SWIFT_ParserRule::RebuildCache();

        return true;
    }

    /**
     * Upgrade from 4.92.2
     *
     * @author Verem Duger <verem.dugeri@crossover.com>
     *
     * @return bool true on success
     * @throws  SWIFT_Exception if class not loaded
     */
    public function Upgrade_4_92_4()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_emailQueueContainer = [];

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "emailqueues");

        while ($this->Database->NextRecord()) {
            $_emailQueueContainer[] = $this->Database->Record;
        }

        foreach ($_emailQueueContainer as $row) {
            $_userPassword = $row['userpassword'];

            try {
                // try to decrypt the password if it's already encrypted
                $decrypted = \SWIFT_Cryptor::Decrypt($_userPassword);
            } catch (\Exception $ex) {
                // if the password is not encrypted, use it
                $decrypted = $_userPassword;
            }

            $row['userpassword'] = \SWIFT_Cryptor::Encrypt($decrypted);

            $this->Database->AutoExecute(TABLE_PREFIX . "emailqueues", $row, 'UPDATE', "emailqueueid= '" . (int)($row['emailqueueid']) . "'");
        }

        return true;
    }

    public function Upgrade_4_94_4()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableName =  TABLE_PREFIX . 'emailqueues';

        // Add new columns
        $this->Database->Query("ALTER TABLE $_tableName ADD COLUMN authtype varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL, 
            ADD COLUMN clientid varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            ADD COLUMN clientsecret` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            ADD COLUMN authendpoint` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            ADD COLUMN tokenendpoint` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            ADD COLUMN authscopes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            ADD COLUMN accesstoken` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
            ADD COLUMN refreshtoken` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL");
        $_console = new \SWIFT_Console();
        $_console->WriteLine('Email Queue table modification...' . $_console->Green('Done'));

        return true;
    }


    public function Upgrade_4_94_7()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableName =  TABLE_PREFIX . 'emailqueues';

        // Add new columns
        $this->Database->Query("ALTER TABLE $_tableName ADD COLUMN tokenexpiry INT NOT NULL DEFAULT 0,
            ADD COLUMN smtphost varchar(255) NOT NULL DEFAULT '',
            ADD COLUMN smtpport varchar(255) NOT NULL DEFAULT ''");
        $_console = new \SWIFT_Console();
        $_console->WriteLine('Email Queue table modification...' . $_console->Green('Done'));

        return true;
    }

    public function Upgrade_4_97_6()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableName =  TABLE_PREFIX . 'mailmessageid';

        // create table
        $this->Database->Query("CREATE TABLE $_tableName (
                messageid VARCHAR(150) PRIMARY KEY,
                dateline INT)");

        $this->Database->Query("SELECT * 
                                FROM information_schema.tables
                                WHERE  table_schema = DATABASE() AND 
                                    table_name = $_tableName
                                LIMIT 1");
        $tableExists = [];
        while ($this->Database->NextRecord()) {
            $tableExists[] = $this->Database->Record;
        }

        if (empty($tableExists)) {
            $_console = new \SWIFT_Console();
            $_console->WriteLine('Mailmessageid table creation...' . $_console->Yellow('Failed.'));
        }

        return true;
    }

	public function Upgrade_4_98_4()
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
		}

		$_tableName =  TABLE_PREFIX . SWIFT_TicketAuditLog::TABLE_NAME;
		$_columnName = 'actionmsgparams';

		// Add new columns
		$this->Database->Query("ALTER TABLE $_tableName ADD COLUMN $_columnName varchar(1000) DEFAULT NULL");

		$this->Database->Query("SELECT COLUMN_NAME
		  FROM INFORMATION_SCHEMA.COLUMNS
		  WHERE TABLE_SCHEMA = DATABASE() 
		 AND TABLE_NAME = '$_columnName' and COLUMN_NAME = '$_columnName'");

		$columnExists = [];
		while ($this->Database->NextRecord()) {
			$columnExists[] = $this->Database->Record;
		}

		if (empty($columnExists)) {
			$_console = new \SWIFT_Console();
			$_console->WriteLine('Audit log table modification...' . $_console->Green('Failed'));
		}

		return true;
	}

	public function Upgrade_4_98_6() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableName =  TABLE_PREFIX . 'perflog';

        // create table
        $this->Database->Query("CREATE TABLE $_tableName (
 	            ID INT PRIMARY KEY AUTO_INCREMENT,
 	            FUNCTION_NAME VARCHAR(100),
 	            START_TIME INT,
 	            END_TIME INT,
 	            DURATION INT,
 	            MESSAGE VARCHAR(500))");

        // create index
        $this->Database->Query("CREATE INDEX perflog_function_name ON $_tableName(FUNCTION_NAME)");
        $this->Database->Query("CREATE INDEX perflog_duration ON $_tableName(DURATION)");
        $this->Database->Query("CREATE INDEX perflog_duration ON $_tableName(START_TIME)");

        $this->Database->Query("SELECT * 
                                FROM information_schema.tables
                                WHERE  table_schema = DATABASE() AND 
                                    table_name = $_tableName
                                LIMIT 1");
        $tableExists = [];
        while ($this->Database->NextRecord()) {
            $tableExists[] = $this->Database->Record;
        }

        if (empty($tableExists)) {
            $_console = new \SWIFT_Console();
            $_console->WriteLine('perflog table creation...' . $_console->Yellow('Failed.'));
        }

        return true;
    }

}
