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

/**
 * The SWIFT Mail Queue Manager
 *
 * @property SWIFT_Mail $Mail
 * @author Varun Shoor
 */
class SWIFT_MailQueueManager extends SWIFT_Model
{
    private $_emailQueueDataToSend = array();

    /**
     * Parses the Variables in the Email Text
     *
     * @author Varun Shoor
     * @param string $_string The Data String
     * @param string $_toEmail The Destination Email
     * @return string
     */
    protected function ParseVariables($_string, $_toEmail)
    {
        return str_replace('[$email]', $_toEmail, $_string);
    }

    /**
     * Adds an email to the queue
     *
     * @author Varun Shoor
     * @param string $_toEmail The To Email Address
     * @param string $_fromEmail The From Email Address
     * @param string $_fromName The From Name
     * @param string $_subject The Email Subject
     * @param string $_dataText The Text Data
     * @param string $_dataHTML (OPTIONAL) The HTML Data
     * @param bool $_isHTML (OPTIONAL) Whether this is an HTML Email
     * @param bool $_rebuildCache (OPTIONAL) Whether the system should rebuild cache at end
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Mail_Exception If the Class is not Loaded
     */
    public function AddToQueue($_toEmail, $_fromEmail, $_fromName, $_subject, $_dataText, $_dataHTML = '', $_isHTML = false, $_rebuildCache = true)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Mail_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->AutoExecute(TABLE_PREFIX.'mailqueuedata', array('toemail' => ReturnNone($_toEmail), 'fromemail' => ReturnNone($_fromEmail), 'fromname' => ReturnNone($_fromName), 'subject' => ReturnNone($_subject), 'datatext' => ReturnNone($this->ParseVariables($_dataText, $_toEmail)), 'datahtml' => ReturnNone($this->ParseVariables($_dataHTML, $_toEmail)), 'dateline' => DATENOW, 'ishtml' => (int) ($_isHTML)), 'INSERT');

        if ($_rebuildCache)
        {
            $this->RecountMailQueue();
        }

//        $this->ProcessMailQueue();

        return true;
    }

    /**
    * Recounts the Mail Queue Data
    *
    * @author Varun Shoor
    * @return bool "true" on Success, "false" otherwise
    * @throws SWIFT_Mail_Exception If the Class is not Loaded
    */
    public function RecountMailQueue() {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Mail_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_resultCount = $this->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM ". TABLE_PREFIX ."mailqueuedata");
        $this->Settings->UpdateKey("mail", "queuecount", (int) ($_resultCount["totalitems"]));

        return true;
    }

    /**
    * Flushes the Mail Queue Data based on a given list
    *
    * @author Varun Shoor
    * @param string $_mailQueueDataIDList The Mail Queue Data ID Container
    * @return bool "true" on Success, "false" otherwise
    * @throws SWIFT_Mail_Exception If the Class is not Loaded
    */
    public function DeleteMailQueueDataList($_mailQueueDataIDList) {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Mail_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!_is_array($_mailQueueDataIDList)) {
            return false;
        }

        $this->Database->Query("DELETE FROM ". TABLE_PREFIX ."mailqueuedata WHERE mailqueuedataid IN (". BuildIN($_mailQueueDataIDList) .")");

        return true;
    }

    /**
    * Processes the Mail Queue for dispatch of mail items
    *
    * @author Varun Shoor
    * @return bool "true" on Success, "false" otherwise
    * @throws SWIFT_Mail_Exception If the Class is not Loaded
    */
    public function ProcessMailQueue() {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Mail_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_flushMailQueueDataIDList = array();

        $_batchCount = $this->Settings->Get('cpu_mailqueuebatch');
        if (empty($_batchCount))
        {
            $_batchCount = "5";
        }

        $this->Load->Library('Mail:Mail');

        $this->Database->QueryLimit("SELECT * FROM ". TABLE_PREFIX ."mailqueuedata ORDER BY mailqueuedataid ASC ", (int) ($_batchCount), 0);
        while ($this->Database->NextRecord())
        {
            $_flushMailQueueDataIDList[] = $this->Database->Record['mailqueuedataid'];
            // Check if toemail already exits then no need to add data to array.
            if (!isset($this->_emailQueueDataToSend[$this->Database->Record['toemail']])) {
                $this->_emailQueueDataToSend[$this->Database->Record['toemail']] = $this->Database->Record;
            }
        }

        $this->DeleteMailQueueDataList($_flushMailQueueDataIDList);
        $this->RecountMailQueue();

        $this->SendQueueEmails();

        return true;
    }

    /**
     * Send Emails from the Queue passed in array
     *
     * @author Mahesh Salaria
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Mail_Exception If the Class is not Loaded
     */
    public function SendQueueEmails()
    {
        if (!$this->GetIsClassLoaded())     {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!_is_array($this->_emailQueueDataToSend)) {
            return false;
        }

        foreach ($this->_emailQueueDataToSend as $_mailData) {

            $this->Mail = new SWIFT_Mail();

            $this->Mail->SetFromField($_mailData['fromemail'], $_mailData['fromname']);
            $this->Mail->SetSubjectField($_mailData['subject']);

            if ($_mailData['ishtml'] == '1' && $_mailData['datahtml'] != '')
            {
                $this->Mail->SetDataText($_mailData['datatext']);
                $this->Mail->SetDataHTML($_mailData['datahtml']);
            } else {
                if (!empty($_mailData['datahtml']) && empty($_mailData['datatext']))
                {
                    $this->Mail->SetDataText($_mailData['datahtml']);
                } else {
                    $this->Mail->SetDataText($_mailData['datatext']);
                }
            }

            if (isset($_mailData['toname']))
            {
                $this->Mail->SetToField($_mailData['toemail'], $_mailData['toname']);
            } else {
                $this->Mail->SetToField($_mailData['toemail']);
            }

            $this->Mail->SendMail(false);
        }

        return true;
    }
}
?>
