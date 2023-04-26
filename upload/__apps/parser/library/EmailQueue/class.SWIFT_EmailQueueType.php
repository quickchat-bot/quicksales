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

namespace Parser\Library\EmailQueue;

use Parser\Library\EmailQueue\SWIFT_EmailQueue_Exception;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use Parser\Library\EmailQueue\SWIFT_EmailQueueType_Backend;
use Parser\Library\EmailQueue\SWIFT_EmailQueueType_News;
use Parser\Library\EmailQueue\SWIFT_EmailQueueType_Tickets;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * The Email Queue Type Management Class (NEWS/TICKETS)
 *
 * @author Varun Shoor
 */
abstract class SWIFT_EmailQueueType extends SWIFT_Library
{
    private $_queueType = false;
    private $_valueContainer = array();
    protected $EmailQueue = false;

    // Core Constants
    const TYPE_TICKETS = APP_TICKETS;
    const TYPE_NEWS = APP_NEWS;
    const TYPE_BACKEND = APP_BACKEND;

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param mixed $_queueType The Queue Type
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct($_queueType)
    {
        if (!$this->SetQueueType($_queueType)) {
            // Is the exception message good?
            throw new SWIFT_EmailQueue_Exception('Could not SetQueueType');
        }

        parent::__construct();
    }

    /**
     * Get the Email Queue Type Object from the Email Queue Object
     *
     * @author Varun Shoor
     *
     * @param SWIFT_EmailQueue $_SWIFT_EmailQueueObject The Email Queue Object
     *
     * @return mixed "Parser\Library\EmailQueue\SWIFT_EmailQueueType" (OBJECT) on Success, "false" otherwise
     */
    public static function GetFromEmailQueueObject(SWIFT_EmailQueue $_SWIFT_EmailQueueObject)
    {
        if (!$_SWIFT_EmailQueueObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_EmailQueue_Exception('Invalid Email Queue Object');
        }

        $_queueType = $_SWIFT_EmailQueueObject->GetProperty('type');
        if (!self::IsValidType($_queueType)) {
            throw new SWIFT_EmailQueue_Exception('Invalid Email Queue Type Data');
            // @codeCoverageIgnoreEnd
        }

        switch ($_queueType) {
            case self::TYPE_TICKETS:
                $_SWIFT_EmailQueueTypeObject = new SWIFT_EmailQueueType_Tickets($_SWIFT_EmailQueueObject->GetProperty('tgroupid'),
                    $_SWIFT_EmailQueueObject->GetProperty('departmentid'), $_SWIFT_EmailQueueObject->GetProperty('tickettypeid'),
                    $_SWIFT_EmailQueueObject->GetProperty('priorityid'), $_SWIFT_EmailQueueObject->GetProperty('ticketstatusid'),
                    $_SWIFT_EmailQueueObject->GetProperty('ticketautoresponder'));
                $_SWIFT_EmailQueueTypeObject->SetEmailQueue($_SWIFT_EmailQueueObject);
                return $_SWIFT_EmailQueueTypeObject;

                break;

            case self::TYPE_NEWS:
                $_SWIFT_EmailQueueTypeObject = new SWIFT_EmailQueueType_News();
                $_SWIFT_EmailQueueTypeObject->SetEmailQueue($_SWIFT_EmailQueueObject);
                return $_SWIFT_EmailQueueTypeObject;

                break;

            case self::TYPE_BACKEND:
                $_SWIFT_EmailQueueTypeObject = new SWIFT_EmailQueueType_Backend();
                $_SWIFT_EmailQueueTypeObject->SetEmailQueue($_SWIFT_EmailQueueObject);
                return $_SWIFT_EmailQueueTypeObject;

                break;

                break;
        }

        // @codeCoverageIgnoreStart
        // will never be reached
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Check to see if its a valid queue type
     *
     * @author Varun Shoor
     *
     * @param string $_queueType The Queue Type
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_queueType)
    {
        if ($_queueType == self::TYPE_TICKETS || $_queueType == self::TYPE_NEWS || $_queueType == self::TYPE_BACKEND) {
            return true;
        }

        return false;
    }

    /**
     * Set the Queue Type
     *
     * @author Varun Shoor
     *
     * @param mixed $_queueType The Queue Type
     *
     * @return bool "true" on Success, "false" otherwise
     */
    protected function SetQueueType($_queueType)
    {
        if (!self::IsValidType($_queueType)) {
            return false;
        }

        $this->_queueType = $_queueType;

        return true;
    }

    /**
     * Retrieve the Queue Type
     *
     * @author Varun Shoor
     * @return mixed "_queueType" (CONSTANT) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetQueueType()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_queueType;
    }

    /**
     * Set the Value in the Container
     *
     * @author Varun Shoor
     *
     * @param string $_key   The Value Key
     * @param string $_value The Value Data
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_EmailQueue_Exception If the Class is not Loaded
     */
    protected function SetValue($_key, $_value)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_key)) {
            throw new SWIFT_EmailQueue_Exception('Invalid Data Provided');
        }

        $this->_valueContainer[$_key] = $_value;

        return true;
    }

    /**
     * Get the Value from the Container
     *
     * @author Varun Shoor
     *
     * @param string $_key The Value Key
     *
     * @return mixed "_valueContainer[key]" on Success, "false" otherwise
     * @throws SWIFT_EmailQueue_Exception If the Class is not Loaded
     */
    public function GetValue($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_key)) {
            throw new SWIFT_EmailQueue_Exception('Invalid Data Provided');
        }

        if (!isset($this->_valueContainer[$_key])) {
            return '';
        }

        return $this->_valueContainer[$_key];
    }

    /**
     * Get the Complete Value Container
     *
     * @author Varun Shoor
     * @return mixed "_valueContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_EmailQueue_Exception If the Class is not Loaded
     */
    public function GetValueContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_valueContainer;
    }

    /**
     * Set the Email Queue Object
     *
     * @author Varun Shoor
     *
     * @param \Parser\Models\EmailQueue\SWIFT_EmailQueue $_SWIFT_EmailQueueObject The Email Queue Object
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetEmailQueue(SWIFT_EmailQueue $_SWIFT_EmailQueueObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_EmailQueueObject instanceof SWIFT_EmailQueue || !$_SWIFT_EmailQueueObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        $this->EmailQueue = $_SWIFT_EmailQueueObject;

        return true;
    }

    /**
     * Retrieve the Email Queue Object
     *
     * @author Varun Shoor
     * @return \Parser\Models\EmailQueue\SWIFT_EmailQueue The Parser\Models\EmailQueue\SWIFT_EmailQueue Object
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetEmailQueue()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->EmailQueue;
    }
}

?>
