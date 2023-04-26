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

namespace Parser\Models\EmailQueue;

use Parser\Library\EmailQueue\SWIFT_EmailQueue_Exception;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_Exception;

/**
 * The Email Queue Pipe Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_EmailQueuePipe extends SWIFT_EmailQueue
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct($_SWIFT_DataObject);
    }

    /**
     * Create a new Email Queue
     *
     * @author Varun Shoor
     *
     * @param string $_queueEmail           The Queue Email Address
     * @param object $_EmailQueueTypeObject The Email Queue Type Object Pointer (NEWS/TICKETS)
     * @param string $_queuePrefix          The Queue Prefix
     * @param string $_customFromName       The Custom From Name
     * @param string $_customFromEmail      The Custom From Email
     * @param string $_queueSignature       The Queue Signature
     * @param bool   $_registrationRequired Whether the user should be registered for message acceptance to work
     * @param bool   $_isEnabled            Whether this Queue is Enabled
     *
     * @return mixed "Parser\Models\EmailQueue\SWIFT_EmailQueuePipe" (OBJECT) on Success, "false" otherwise
     * @throws \Parser\Models\EmailQueue\SWIFT_EmailQueue_Exception When Invalid Data is Specified or the Object couldnt be created
     */
    public static function Create($_queueEmail, $_EmailQueueTypeObject, $_queuePrefix, $_customFromName, $_customFromEmail,
                                  $_queueSignature, $_registrationRequired, $_isEnabled, $_ = null, $__ = null)
    {
        $_emailQueueID = parent::Create($_queueEmail, self::FETCH_PIPE, $_EmailQueueTypeObject, $_queuePrefix, $_customFromName, $_customFromEmail,
            $_queueSignature, $_registrationRequired, $_isEnabled, true);

        $_SWIFT_EmailQueuePipeObject = new SWIFT_EmailQueuePipe(new SWIFT_DataID($_emailQueueID));
        if (!$_SWIFT_EmailQueuePipeObject instanceof SWIFT_EmailQueuePipe || !$_SWIFT_EmailQueuePipeObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            throw new SWIFT_EmailQueue_Exception('Unable to Load Email Queue (PIPE)');
            // @codeCoverageIgnoreEnd
        }

        return $_SWIFT_EmailQueuePipeObject;
    }

    /**
     * Update the Email Queue Record
     *
     * @author Varun Shoor
     *
     * @param string $_queueEmail           The Queue Email Address
     * @param object $_EmailQueueTypeObject The Email Queue Type Object Pointer (NEWS/TICKETS)
     * @param string $_queuePrefix          The Queue Prefix
     * @param string $_customFromName       The Custom From Name
     * @param string $_customFromEmail      The Custom From Email
     * @param string $_queueSignature       The Queue Signature
     * @param bool   $_registrationRequired Whether the user should be registered for message acceptance to work
     * @param bool   $_isEnabled            Whether this Queue is Enabled
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws \Parser\Models\EmailQueue\SWIFT_EmailQueue_Exception If the Class is not Loaded
     */
    public function Update($_queueEmail, $_EmailQueueTypeObject, $_queuePrefix, $_customFromName, $_customFromEmail,
                           $_queueSignature, $_registrationRequired, $_isEnabled, $_ = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_EmailQueue_Exception(SWIFT_CLASSNOTLOADED);
        }

        return parent::Update($_queueEmail, $_EmailQueueTypeObject, $_queuePrefix, $_customFromName, $_customFromEmail, $_queueSignature,
            $_registrationRequired, $_isEnabled);
    }
}

?>
