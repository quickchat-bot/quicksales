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
 * We can use an instance of Command class to execute system commands and or call a callable
 * callable should return true to indicate success
 *
 * @author Atul Atri <atul.atri@opencart.com.vn>
 */
class SWIFT_Command extends SWIFT_Library
{
    /**
     * @var string|null
     */
    private $_command;

    /**
     * @var string
     */
    private $_output;

    /**
     * @var int
     */
    private $_statusCode;

    /**
     * @var string
     */
    private $_name;

    /**
     * @var callable
     */
    private $_callback;

    /**
     * @var array
     */
    private $_callbackParams;


    /**
     * @author Atul Atri <atul.atri@opencart.com.vn>
     *
     * @param string $_commandName
     */
    public function __construct($_commandName)
    {
        parent::__construct();

        $this->SetName($_commandName);
    }

    /**
     * Either you can set command or callback
     *
     * @author Atul Atri <atul.atri@opencart.com.vn>
     *
     * @param string $_command
     *
     * @return SWIFT_Command
     * @throws SWIFT_Exception in case command is not string
     */
    public function SetCommand($_command)
    {
        if (!is_string($_command)) {
            throw new SWIFT_Exception('Command should be string.');
        }

        if (isset($this->_callback)) {
            throw new SWIFT_Exception("Callback is already set.");
        }

        $this->_command = $_command;

        return $this;
    }

    /**
     * @author Atul Atri <atul.atri@opencart.com.vn>
     *
     * @return string|null
     */
    public function GetCommand()
    {
        return $this->_command;
    }

    /**
     * Either you can set command or callback
     *
     * @param callable $_callback
     * @param array    $_callbackParams
     *
     * @return SWIFT_Command
     * @throws SWIFT_Exception
     */
    public function SetCallback( /*callable*/
        $_callback, array $_callbackParams = array())
    {
        if (isset($this->_command)) {
            throw new SWIFT_Exception("Command is already set.");
        }

        $this->_callback       = $_callback;
        $this->_callbackParams = $_callbackParams;

        return $this;
    }

    /**
     * @return callable
     */
    public function GetCallback()
    {
        return $this->_callback;
    }

    /**
     * @return array
     */
    public function GetCallbackParams()
    {
        return $this->_callbackParams;
    }

    /**
     * Execute this command.
     * String command is executed on system shell.
     * callable is called using call_user_func_array.
     *
     * @author Atul Atri <atul.atri@opencart.com.vn>
     */
    public function Execute()
    {
        $_command = $this->GetCommand();

        if (!is_null($_command)) {
            $this->System->Execute($_command, $this->_output, $this->_statusCode);
        } else {
            $_callbackParams = $this->GetCallbackParams();

            if (!empty($_callbackParams)) {
                $this->_statusCode = call_user_func_array($this->GetCallback(), $_callbackParams);
            } else {
                $this->_statusCode = call_user_func($this->GetCallback());
            }
        }
    }

    /**
     * Output of command execution
     *
     * @author Atul Atri <atul.atri@opencart.com.vn>
     *
     * @return mixed
     */
    public function GetOutput()
    {
        return $this->_output;
    }

    /**
     * Status code returned by command execution
     *
     * @author Atul Atri <atul.atri@opencart.com.vn>
     * @return int
     */
    public function GetStatusCode()
    {
        return $this->_statusCode;
    }

    /**
     * Was command execution successful
     *
     * @author Atul Atri <atul.atri@opencart.com.vn>
     * @return int|bool
     */
    public function IsSuccess()
    {
        $_command = $this->GetCommand();

        if (!is_null($_command)) {
            return $this->GetStatusCode() === 0;
        }

        return $this->GetStatusCode();
    }

    /**
     * @author Atul Atri <atul.atri@opencart.com.vn>
     *
     * @param string $_name
     *
     * @return SWIFT_Command
     * @throws Exception
     */
    public function SetName($_name)
    {
        if (empty($_name) || !is_string($_name)) {
            throw new Exception(SWIFT_INVALIDDATA);
        }

        $this->_name = $_name;

        return $this;
    }

    /**
     * @author Atul Atri <atul.atri@opencart.com.vn>
     *
     * @return string
     */
    public function GetName()
    {
        return $this->_name;
    }
}
