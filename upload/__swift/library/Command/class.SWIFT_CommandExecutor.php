<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Atul Atri
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2013, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

/**
 * Command Executor executes the commands provided to it
 *
 * This execute a chain of commands.
 *
 * @author Atul Atri <atul.atri@opencart.com.vn>
 */
class SWIFT_CommandExecutor extends SWIFT_Library
{
    const CMD_COMMAND          = 0;
    const CMD_BREAK_ON_FAILURE = 1;
    const CMD_FAILURE_MESSAGE  = 2;
    const CMD_SUCCESS_MESSAGE  = 3;

    const SUCCESS_MSG_TEMPLATE = 'Command executed.';
    const FAILURE_MSG_TEMPLATE = 'Command failed to execute.';

    const CMD_NAME_CHARS = 50;

    private $_commands = array();

    /**
     * List of commands can be passed into  constructor.
     *
     * This list is parsed using method Parse in this class. See self::Parse for an example
     *
     * @author Atul Atri <atul.atri@opencart.com.vn>
     *
     * @param array $_commands list of commands
     */
    public function __construct(array $_commands = array())
    {
        parent::__construct();

        if (!empty($_commands)) {
            $this->Parse($_commands);
        }
    }

    /**
     * Add a command in command chain
     *
     * @author Atul Atri <atul.atri@opencart.com.vn>
     *
     * @param SWIFT_Command $_Command
     * @param bool          $_breakOnFailure Stop executing command chain if this command fails to execute
     * @param mixed        $_successMessage (OPTIONAL)
     * @param mixed        $_failureMessage (OPTIONAL)
     *
     * @throws SWIFT_Exception
     * @return SWIFT_CommandExecutor
     */
    public function AddCommand(SWIFT_Command $_Command, $_breakOnFailure = true, $_successMessage = '', $_failureMessage = '')
    {
        if (!$_Command instanceof SWIFT_Command) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } else if (!is_string($_successMessage) || !is_string($_failureMessage)) {
            throw new SWIFT_Exception('Success or Failure message should be string');
        }

        if (!$_successMessage) {
            $_successMessage = self::SUCCESS_MSG_TEMPLATE;
        }

        if (!$_failureMessage) {
            $_failureMessage = self::FAILURE_MSG_TEMPLATE;
        }

        $_commandContainer                             = array();
        $_commandContainer[self::CMD_COMMAND]          = $_Command;
        $_commandContainer[self::CMD_BREAK_ON_FAILURE] = $_breakOnFailure;
        $_commandContainer[self::CMD_SUCCESS_MESSAGE]  = $_successMessage;
        $_commandContainer[self::CMD_FAILURE_MESSAGE]  = $_failureMessage;

        $this->_commands[] = $_commandContainer;

        return $this;
    }

    /**
     * Parse commands from array.
     * <br/>
     * Example:
     * <br/>
     * <pre>
     *        array(
     *            'ls -lst', // This is a system command
     *            array('ls -lst', false) // This is a system command. We can pass array of params of method AddCommand in this class. Here second element 'false' means- failure of this command will break execution of command chain
     *            array('ls -lst', false, ' Message to be shown when command is executed successfully', 'Message to be shown when command is failed to execute'),
     *            new SWIFT_Command('System command name', 'ls -slt'),
     *            array(new SWIFT_CommandClosure('Closure command name', Anonymous_Function), false, ' Message to be shown when command is executed successfully', 'Message to be shown when command is failed to execute'))
     *        )
     * </pre>
     *
     * @author Atul Atri <atul.atri@opencart.com.vn>
     *
     * @param array $_commandMap
     *
     * @return SWIFT_CommandExecutor
     * @throws SWIFT_Exception
     */
    public function Parse(array $_commandMap)
    {
        if (empty($_commandMap) || !_is_array($_commandMap)) {
            throw new SWIFT_Exception('Empty command map provided..');
        }

        foreach ($_commandMap as $_nextCommandCommand) {
            $_CommandNode    = $_nextCommandCommand;
            $_breakOnFailure = true;
            $_successMessage = '';
            $_failureMessage = '';

            if (is_array($_nextCommandCommand)) {
                if (!isset($_nextCommandCommand[self::CMD_COMMAND])) {
                    throw new SWIFT_Exception('Command not found!');
                }

                $_CommandNode = $_nextCommandCommand[self::CMD_COMMAND];

                if (isset($_nextCommandCommand[self::CMD_BREAK_ON_FAILURE])) {
                    $_breakOnFailure = $_nextCommandCommand[self::CMD_BREAK_ON_FAILURE];
                }

                if (isset($_nextCommandCommand[self::CMD_SUCCESS_MESSAGE])) {
                    $_successMessage = $_nextCommandCommand[self::CMD_SUCCESS_MESSAGE];
                }

                if (isset($_nextCommandCommand[self::CMD_FAILURE_MESSAGE])) {
                    $_failureMessage = $_nextCommandCommand[self::CMD_FAILURE_MESSAGE];
                }
            }

            $_Command = null;

            if ($_CommandNode instanceof SWIFT_Command) {
                $_Command = $_CommandNode;
            } else if (is_string($_CommandNode)) {
                // This is a system command
                $_name = substr($_CommandNode, 0, self::CMD_NAME_CHARS);

                if (strlen($_CommandNode) > self::CMD_NAME_CHARS + 1) {
                    $_name .= '...';
                }

                // Convert to object
                $_Command = new SWIFT_Command($_name);
                $_Command->SetCommand($_CommandNode);
            } else {
                throw new SWIFT_Exception('Invalid command: ' . print_r($_nextCommandCommand, true));
            }

            $this->AddCommand($_Command, $_breakOnFailure, $_successMessage, $_failureMessage);
        }

        return $this;
    }

    /**
     * Return command list (command-chain).
     *
     * @author Atul Atri <atul.atri@opencart.com.vn>
     * @return array
     */
    public function  GetCommands()
    {
        return $this->_commands;
    }

    /**
     * Execute chain of commands
     *
     * @author Atul Atri <atul.atri@opencart.com.vn>
     * @return bool false if command-chain failed to execute.
     */
    public function Execute()
    {
        $_returnMe = true;

        foreach ($this->_commands as $_nexCommandArray) {
            /**
             * @var SWIFT_Command $_Command
             */

            $_Command = $_nexCommandArray[self::CMD_COMMAND];

            $this->Console->Message('(Executor): About to execute command: ' . $_Command->GetName());

            $_Command->Execute();

            if (!$_Command->IsSuccess()) {
                //TODO dump $_output to some file. $_output could be big so we can not show it on console.
                $this->Console->Message('(Executor): ' . $_Command->GetName() . '=>' . $_nexCommandArray[self::CMD_FAILURE_MESSAGE], SWIFT_Console::CONSOLE_ERROR);

                if ($_nexCommandArray[self::CMD_BREAK_ON_FAILURE]) {
                    $_returnMe = false;

                    break;
                }
            } else {
                $this->Console->Message('(Executor): ' . $_Command->GetName() . '=>' . $_nexCommandArray[self::CMD_SUCCESS_MESSAGE]);
            }
        }

        return $_returnMe;
    }
}
