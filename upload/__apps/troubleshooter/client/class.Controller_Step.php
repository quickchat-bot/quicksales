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

namespace Troubleshooter\Client;

use Base\Library\Comment\SWIFT_CommentManager;
use Base\Library\UserInterface\SWIFT_UserInterfaceClient;
use SWIFT;
use SWIFT_App;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Comment\SWIFT_Comment;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_MIME_Exception;
use SWIFT_MIMEList;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;
use Troubleshooter\Models\Step\SWIFT_TroubleshooterStep;
use Base\Models\Widget\SWIFT_Widget;

/**
 * The Troubleshooter Step Controller
 *
 * @property SWIFT_CommentManager $CommentManager
 * @property SWIFT_UserInterfaceClient $UserInterface
 * @author Varun Shoor
 */
class Controller_Step extends \Controller_client
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2528: Widget particular pages shows up using direct URIs irrespective of whether the widget's visibility is restricted.
         *
         * Comments: None
         */
        if (!SWIFT_App::IsInstalled(APP_TROUBLESHOOTER) || !SWIFT_Widget::IsWidgetVisible(APP_TROUBLESHOOTER))
        {
            $this->UserInterface->Error(true, $this->Language->Get('nopermission'));
            $this->Load->Controller('Default', 'Core')->Load->Index();

            return;
        }

        $this->Load->Library('Comment:CommentManager', [], true, false, 'base');

        $this->Language->Load('troubleshooter');
    }

    /**
     * The Troubleshooter Category Rendering Function
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID The Troubleshooter Category ID
     * @param int $_activeTroubleshooterStepID (OPTIONAL) The Currently Active Troubleshooter Step ID
     * @param string $_troubleshooterStepHistory (OPTIONAL) The Troubleshooter Step History
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function View($_troubleshooterCategoryID = 0, $_activeTroubleshooterStepID = 0, $_troubleshooterStepHistory = '')
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4370 Security issue (medium)
         *
         * Comments: We are expecting $_troubleshooterCategoryID as an integer.
         */
        if (empty($_troubleshooterCategoryID) || !is_numeric($_troubleshooterCategoryID)) {
            $this->Load->Controller('List', 'Troubleshooter')->Load->Index();

            return false;
        }

        /**
         * ---------------------------------------------
         * REPLICA EXISTS IN STAFF!
         * ---------------------------------------------
         */

        if (empty($_activeTroubleshooterStepID) && isset($_POST['nexttroubleshooterstepid']))
        {
            $_activeTroubleshooterStepID = $_POST['nexttroubleshooterstepid'];
        }

        if (empty($_troubleshooterStepHistory) && isset($_POST['troubleshooterstephistory']))
        {
            /*
             * BUG FIX - Simaranjit Singh
             *
             * SWIFT-3659: Security issue
             *
             * Comments: None
             */
            $_troubleshooterStepHistory = $this->Input->SanitizeForXSS($_POST['troubleshooterstephistory']);
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-3921 Disable Next Button on Troubleshooter step unless a selection is made.
         *
         * Comments: Parsing the history and handling next and back.
         */
        // Was back button triggered?
        $length1 = strrpos($_troubleshooterStepHistory, ':');
        if ($length1 === false) {
            $length1 = 0;
        }
        if (isset($_POST['isback']) && $_POST['isback'] == '1' && strpos($_POST['troubleshooterstephistory'], ':') != false)
        {
            $length = strrpos($_POST['troubleshooterstephistory'], ':');
            if ($length === false) {
                $length = 0;
            }
            $_troubleshooterStepHistory = substr($_POST['troubleshooterstephistory'], 0, $length);
            $_activeTroubleshooterStepID = substr($_POST['troubleshooterstephistory'], $length +1);

            // We need to move one step back
            if (strpos($_troubleshooterStepHistory, ':') != false)
            {
                $_activeTroubleshooterStepID = substr($_troubleshooterStepHistory, $length1 +1);
                $_troubleshooterStepHistory = substr($_troubleshooterStepHistory, 0, $length1);
            } else {
                $_activeTroubleshooterStepID = 0;
            }
        } else if (!isset($_POST['nexttroubleshooterstepid']) && isset($_POST['isback']) && $_POST['isback'] == '0' && strpos($_POST['troubleshooterstephistory'], ':') != false) {
            $_troubleshooterStepHistoryContainer = explode(':', $_POST['troubleshooterstephistory']);
            $_activeTroubleshooterStepID         = end($_troubleshooterStepHistoryContainer);
            $_troubleshooterStepHistory          = substr($_troubleshooterStepHistory, 0, $length1);
        }

        $_SWIFT_TroubleshooterCategoryObject = false;

        $_extendedTitle = $this->Language->Get('troubleshooter');

        try {
            $_SWIFT_TroubleshooterCategoryObject = new SWIFT_TroubleshooterCategory(new SWIFT_DataID($_troubleshooterCategoryID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {


            return false;
        }

        if (!$_SWIFT_TroubleshooterCategoryObject instanceof SWIFT_TroubleshooterCategory || !$_SWIFT_TroubleshooterCategoryObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if (!$_SWIFT_TroubleshooterCategoryObject->CanAccess(array(SWIFT_TroubleshooterCategory::TYPE_GLOBAL, SWIFT_TroubleshooterCategory::TYPE_PUBLIC), 0, SWIFT::Get('usergroupid')))
        {
            throw new SWIFT_Exception('Access Denied');
        }

        if (empty($_troubleshooterStepHistory))
        {
            $_troubleshooterStepHistory = '0';
        }

        $_troubleshooterStepSubject = $_SWIFT_TroubleshooterCategoryObject->GetProperty('title');
        $_troubleshooterStepContents = nl2br($_SWIFT_TroubleshooterCategoryObject->GetProperty('description'));
        $_troubleshooterStepHasAttachments = '0';
        $_attachmentContainer = array();
        $_troubleshooterStepCount = 0;

        $_troubleshooterStepContainer = array();

        $_troubleshooterStepAllowComments = false;

        if (!empty($_activeTroubleshooterStepID))
        {
            $_SWIFT_TroubleshooterStepObject = false;

            try {
                $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID($_activeTroubleshooterStepID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                return false;
            }

            if ($_SWIFT_TroubleshooterStepObject->GetProperty('troubleshootercategoryid') != $_troubleshooterCategoryID)
            {
                // @codeCoverageIgnoreStart
                // This code will never be executed
                throw new SWIFT_Exception('Invalid Step Category');
                // @codeCoverageIgnoreEnd
            }

            $_troubleshooterStepHistory .= ':' . $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID();

            /**
             * ---------------------------------------------
             * Ticket Redirection Logic
             * ---------------------------------------------
             */
            if (SWIFT_App::IsInstalled(APP_TICKETS) && $_SWIFT_TroubleshooterStepObject->GetProperty('redirecttickets') == '1')
            {
                $_departmentCache = $this->Cache->Get('departmentcache');

                // Redirect to department
                if (isset($_departmentCache[$_SWIFT_TroubleshooterStepObject->GetProperty('redirectdepartmentid')]))
                {
                    $_POST['ticketsubject'] = $_SWIFT_TroubleshooterStepObject->GetProperty('subject');

                    if ($_SWIFT_TroubleshooterStepObject->GetProperty('ticketsubject') != '')
                    {
                        $_POST['ticketsubject'] = $_SWIFT_TroubleshooterStepObject->GetProperty('ticketsubject');
                    }

                    //$_SWIFT_StringHTMLToTextObject = new SWIFT_StringHTMLToText();
                    //$_POST['ticketmessage'] = $_SWIFT_StringHTMLToTextObject->Convert($_SWIFT_TroubleshooterStepObject->GetProperty('contents'), false);
                    $_POST['ticketmessage'] = $_SWIFT_TroubleshooterStepObject->GetProperty('contents');

                    if ($_SWIFT_TroubleshooterStepObject->GetProperty('tickettypeid') != '0')
                    {
                        $_POST['tickettypeid'] = $_SWIFT_TroubleshooterStepObject->GetProperty('tickettypeid');
                    }

                    if ($_SWIFT_TroubleshooterStepObject->GetProperty('priorityid') != '0')
                    {
                        $_POST['ticketpriorityid'] = $_SWIFT_TroubleshooterStepObject->GetProperty('priorityid');
                    }

                    $this->Load->Controller('Submit', 'Tickets')->Load->RenderForm($_SWIFT_TroubleshooterStepObject->GetProperty('redirectdepartmentid'));


                // Redirect to ticket submission
                } else {
                    $this->Load->Controller('Submit', 'Tickets')->Load->Index();

                }

                return true;
            }


            $_troubleshooterStepSubject = $_SWIFT_TroubleshooterStepObject->GetProperty('subject');
            $_troubleshooterStepContents = $_SWIFT_TroubleshooterStepObject->GetProperty('contents');
            $_troubleshooterStepHasAttachments = $_SWIFT_TroubleshooterStepObject->GetProperty('hasattachments');
            $_troubleshooterStepAllowComments = $_SWIFT_TroubleshooterStepObject->GetProperty('allowcomments');

            // Attachment Logic
            $_attachmentContainer = array();
            if ($_SWIFT_TroubleshooterStepObject->GetProperty('hasattachments') == '1')
            {
                $_attachmentContainer = SWIFT_Attachment::Retrieve(SWIFT_Attachment::LINKTYPE_TROUBLESHOOTERSTEP, $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID());

                foreach ($_attachmentContainer as $_attachmentID => $_attachment)
                {
                    $_mimeDataContainer = array();
                    try
                    {
                        $_fileExtension = mb_strtolower(substr($_attachment['filename'], (strrpos($_attachment['filename'], '.')+1)));

                        $_MIMEListObject = new SWIFT_MIMEList();
                        $_mimeDataContainer = $_MIMEListObject->Get($_fileExtension);
                    } catch (SWIFT_MIME_Exception $_SWIFT_MIME_ExceptionObject) {
                        // Do nothing
                    }

                    $_attachmentIcon = 'icon_file.gif';
                    if (isset($_mimeDataContainer[1]))
                    {
                        $_attachmentIcon = $_mimeDataContainer[1];
                    }

                    $_attachmentContainer[$_attachmentID] = array();
                    $_attachmentContainer[$_attachmentID]['icon'] = $_attachmentIcon;
                    $_attachmentContainer[$_attachmentID]['link'] = SWIFT::Get('basename') . '/Troubleshooter/Step/GetAttachment/' . $_troubleshooterCategoryID . '/' . $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID() . '/' . $_attachment['attachmentid'];
                    $_attachmentContainer[$_attachmentID]['name'] = htmlspecialchars($_attachment['filename']);
                    $_attachmentContainer[$_attachmentID]['size'] = FormattedSize($_attachment['filesize']);
                }
            }

            $_extendedTitle = $_SWIFT_TroubleshooterCategoryObject->GetProperty('title');
            $this->Template->Assign('_extendedTitle', $_SWIFT_TroubleshooterCategoryObject->GetProperty('title'));
            $this->Template->Assign('_extendedTitleLink', SWIFT::Get('basename') . '/Troubleshooter/Step/View/' . $_SWIFT_TroubleshooterCategoryObject->GetTroubleshooterCategoryID());

            $this->CommentManager->LoadSupportCenter('Troubleshooter', SWIFT_Comment::TYPE_TROUBLESHOOTER, $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID(), $_troubleshooterStepHistory);
        } else {
            $_SWIFT_TroubleshooterCategoryObject->IncrementViews();
        }

        $_troubleshooterStepContainer = SWIFT_TroubleshooterStep::RetrieveSubSteps($_troubleshooterCategoryID, $_activeTroubleshooterStepID);
        $_troubleshooterStepCount = count($_troubleshooterStepContainer);

        $_showBackButton = false;
        if (!empty($_troubleshooterStepHistory))
        {
            $_showBackButton = true;
        }

        $this->Template->Assign('_troubleshooterCategoryID', $_troubleshooterCategoryID);
        $this->Template->Assign('_troubleshooterStepSubject', htmlspecialchars($_troubleshooterStepSubject));
        $this->Template->Assign('_troubleshooterStepContents', StripScriptTags($_troubleshooterStepContents));
        $this->Template->Assign('_troubleshooterStepHasAttachments', $_troubleshooterStepHasAttachments);
        $this->Template->Assign('_troubleshooterStepAllowComments', $_troubleshooterStepAllowComments);
        $this->Template->Assign('_troubleshooterStepContainer', $_troubleshooterStepContainer);
        $this->Template->Assign('_troubleshooterStepHistory', $_troubleshooterStepHistory);
        $this->Template->Assign('_troubleshooterStepCount', $_troubleshooterStepCount);
        $this->Template->Assign('_attachmentContainer', $_attachmentContainer);
        $this->Template->Assign('_showBackButton', $_showBackButton);

        $this->Template->Assign('_pageTitle', htmlspecialchars($_extendedTitle));

        $this->UserInterface->Header('troubleshooter');
        $this->Template->Render('troubleshooterstep');
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Dispatch the Attachment
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID The Troubleshooter Category ID
     * @param int $_troubleshooterStepID The Troubleshooter Step ID
     * @param int $_attachmentID The Attachment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetAttachment($_troubleshooterCategoryID, $_troubleshooterStepID, $_attachmentID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TroubleshooterCategoryObject = false;

        try {
            $_SWIFT_TroubleshooterCategoryObject = new SWIFT_TroubleshooterCategory(new SWIFT_DataID($_troubleshooterCategoryID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        if (!$_SWIFT_TroubleshooterCategoryObject instanceof SWIFT_TroubleshooterCategory || !$_SWIFT_TroubleshooterCategoryObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if (!$_SWIFT_TroubleshooterCategoryObject->CanAccess(array(SWIFT_TroubleshooterCategory::TYPE_GLOBAL, SWIFT_TroubleshooterCategory::TYPE_PUBLIC), 0, SWIFT::Get('usergroupid')))
        {
            throw new SWIFT_Exception('Access Denied');
        }

        $_SWIFT_TroubleshooterStepObject = false;

        try {
            $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID($_troubleshooterStepID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        if ($_SWIFT_TroubleshooterStepObject->GetProperty('troubleshootercategoryid') != $_troubleshooterCategoryID)
        {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception('Invalid Step Category');
            // @codeCoverageIgnoreEnd
        }

        $_SWIFT_AttachmentObject = new SWIFT_Attachment($_attachmentID);
        // Did the object load up?
        if (!($_SWIFT_AttachmentObject instanceof SWIFT_Attachment && $_SWIFT_AttachmentObject->GetIsClassLoaded())) {
            // @codeCoverageIgnoreStart
            // This code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($_SWIFT_AttachmentObject->GetProperty('linktype') != SWIFT_Attachment::LINKTYPE_TROUBLESHOOTERSTEP || $_SWIFT_AttachmentObject->GetProperty('linktypeid') != $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $interfaceName = \SWIFT::GetInstance()->Interface->GetName()?:SWIFT_INTERFACE;
        if ($interfaceName === 'tests' || $interfaceName === 'console') {
            return true;
        }
        // @codeCoverageIgnoreStart
        // This code will never be executed in tests
        $_SWIFT_AttachmentObject->Dispatch();

        return true;
        // @codeCoverageIgnoreEnd
    }
}
