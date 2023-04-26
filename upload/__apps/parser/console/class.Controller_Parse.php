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

namespace Parser\Console;
use Controller_console;
use Parser\Library\MailParser\SWIFT_MailParser;
use SWIFT;
use SWIFT_Console;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use SWIFT_Exception;

/**
 * The Console Mail Parsing Controller
 *
 * @property SWIFT_MailParser $MailParser
 * @author Varun Shoor
 */
class Controller_Parse extends Controller_console
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        SWIFT::Set('isparser', true);
    }

    /**
     * Parse the incoming email through PIPE
     *
     * @author Varun Shoor
     *
     * @param string $_forceEmailQueueAddress Check if the email is fetched through fetchdaemon so we can force override queue
     * @param string $stream
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index($_forceEmailQueueAddress = '', $stream = 'php://stdin')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Parse incoming data
        $_incomingData = '';
        $_filePointer = fopen($stream, 'r');
        if ($_filePointer) {
            while (!feof($_filePointer)) {
                $_incomingData .= fgets($_filePointer, 120);
            }
        }

        fclose($_filePointer);

        $this->Load->Library('MailParser:MailParser', array($_incomingData), true, false, APP_PARSER);
        $_queueCache = $this->Cache->Get('queuecache');

        if (trim($_forceEmailQueueAddress) != '') {
            foreach ($_queueCache['pointer'] as $_pointer) {
                if ($_queueCache['list'][$_pointer]['email'] == $_forceEmailQueueAddress) {
                    $_SWIFT_EmailQueueObjectDispatch = SWIFT_EmailQueue::RetrieveStore($_queueCache['list'][$_pointer]);

                    $this->MailParser->Process(false, $_SWIFT_EmailQueueObjectDispatch);

                    break;
                }
            }
        } else {
            $this->MailParser->Process(false);
        }

        return true;
    }

    /**
     * Parse the incoming email through a local file
     *
     * @author Varun Shoor
     *
     * @param string $_filePath The File Path
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function File($_filePath)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!file_exists($_filePath)) {
            $this->Console->Message('The Email Data File "' . $_filePath . '" does not exist!', SWIFT_Console::CONSOLE_ERROR);

            return false;
        }

        $_fileData = file_get_contents($_filePath);

        $this->Load->Library('MailParser:MailParser', array($_fileData), true, false, APP_PARSER);

        $this->MailParser->Process();

        return true;
    }
}
