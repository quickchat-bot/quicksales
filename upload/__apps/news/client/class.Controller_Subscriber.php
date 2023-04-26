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

namespace News\Client;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use News\Models\NewsItem\SWIFT_NewsItem;
use News\Models\Subscriber\SWIFT_NewsSubscriber;
use News\Models\Subscriber\SWIFT_NewsSubscriberHash;
use SWIFT;
use SWIFT_App;
use Base\Models\Comment\SWIFT_Comment;
use SWIFT_Exception;
use Controller_client;
use SWIFT_Session;
use Base\Models\Template\SWIFT_Template;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserGroupAssign;
use Base\Models\Widget\SWIFT_Widget;

/**
 * The News Subscriber Controller
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class Controller_Subscriber extends Controller_client
{
    protected $_sendEmails = true;

    /**
     * Constructor
     *
     * @author Varun Shoor
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
        if (!SWIFT_App::IsInstalled(APP_NEWS) || !SWIFT_Widget::IsWidgetVisible(APP_NEWS))
        {
            $this->UserInterface->Error(true, $this->Language->Get('nopermission'));
            $this->Load->Controller('Default', 'Core')->Load->Index();

            return;
        }

        $this->Language->Load('news');
        $this->Language->Load('users');
    }

    /**
     * Subscribe to the News Feed
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Subscribe()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!in_array('header (Default)', SWIFT_Template::GetUpgradeRevertList()) && (!isset($_POST['_csrfhash']) || !SWIFT_Session::CheckCSRFHash($_POST['_csrfhash'])))
        {
            $this->UserInterface->Error(true, $this->Language->Get('msgcsrfhash'));

            $this->Load->Controller('Default', 'Base')->Load->Index();

            return false;
        }

        if (!isset($_POST['subscribeemail']) || empty($_POST['subscribeemail']) || $_POST['subscribeemail'] == $this->Language->Get('loginenteremail')) {
            $this->UserInterface->CheckFields('subscribeemail');

            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));

            $this->Load->Controller('Default', 'Base')->Load->Index();

            return false;
        }

        /**
         * BUGFIX - Verem Dugeri
         *
         * KAYAKO-4897 - Validate email
         *
         * Comments - None
         */
        if ( !IsEmailValid($_POST['subscribeemail'])) {
            $this->UserInterface->CheckFields('subscribeemail');

            $this->UserInterface->Error(true, $this->Language->Get('invalidemail'));

            $this->Load->Controller('Default', 'Base')->Load->Index();

            return false;
        }

        if (!isset($_POST['registrationconsent'])) {
            $this->UserInterface->CheckFields('registrationconsent');

            $this->UserInterface->Error(true, $this->Language->Get('regpolicyareement'));

            $this->Load->Controller('Default', 'Base')->Load->Index();

            return false;
        }

        if (SWIFT_NewsSubscriber::IsSubscribed($_POST['subscribeemail']))
        {
            $this->UserInterface->CheckFields('subscribeemail');

            $this->UserInterface->Error(true, sprintf($this->Language->Get('subscriberregistered'), htmlspecialchars($_POST['subscribeemail'])));

            $this->Load->Controller('Default', 'Base')->Load->Index();

            return false;
        }

        $_userID = false;
        if ($_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded())
        {
            $_userID = $_SWIFT->User->GetUserID();
        }

        $_requireValidation = false;
        $_isValidated = true;
        if ($this->Settings->Get('nw_svalidate') == '1')
        {
            $_requireValidation = true;
            $_isValidated = false;
        }

        SWIFT_NewsSubscriber::Create($_POST['subscribeemail'], $_isValidated, (int)$_userID, $_SWIFT->TemplateGroup->GetTemplateGroupID(), $this->_sendEmails, SWIFT::Get('usergroupid'));

        if ($_requireValidation)
        {
            $this->UserInterface->Info(true, $this->Language->Get('thankyousubscribervalidate'));
        } else {
            $this->UserInterface->Info(true, $this->Language->Get('thankyousubscriber'));
        }

        $this->Load->Controller('Default', 'Base')->Load->Index();

        return true;
    }

    /**
     * Verify the subscription
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Validate($_hash)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_NewsSubscriberHashObject = SWIFT_NewsSubscriberHash::Retrieve($_hash);
        if (!$_SWIFT_NewsSubscriberHashObject instanceof SWIFT_NewsSubscriberHash || !$_SWIFT_NewsSubscriberHashObject->GetIsClassLoaded())
        {
            $this->UserInterface->Error(true, $this->Language->Get('invalidsubscriberhash'));

            $this->Load->Controller('Default', 'Base')->Load->Index();

            return false;
        }

        $_newsSubscriberID = $_SWIFT_NewsSubscriberHashObject->GetProperty('newssubscriberid');
        $_SWIFT_NewsSubscriberObject = new SWIFT_NewsSubscriber($_newsSubscriberID);

        // @codeCoverageIgnoreStart
        // This code will never be executed
        if (!$_SWIFT_NewsSubscriberObject instanceof SWIFT_NewsSubscriber || !$_SWIFT_NewsSubscriberObject->GetIsClassLoaded())
        {
            $this->UserInterface->Error(true, $this->Language->Get('invalidsubscriberhash'));

            $this->Load->Controller('Default', 'Base')->Load->Index();

            return false;
        }

        $intName = \SWIFT::GetInstance()->Interface->GetName()?:SWIFT_INTERFACE;
        if ($intName !== 'tests' && SWIFT_INTERFACE !== 'tests') {
            $_SWIFT_NewsSubscriberObject->MarkAsValidated();
        }
        // @codeCoverageIgnoreEnd

        $_SWIFT_NewsSubscriberHashObject->Delete();

        $this->UserInterface->Info(true, $this->Language->Get('thankyousubscriber'));
        $this->Load->Controller('Default', 'Base')->Load->Index();

        return true;
    }

    /**
     * Unsubscribe a user
     *
     * @author Varun Shoor
     * @param int $_newsSubscriberID
     * @param string $_incomingSubscriberHash
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Unsubscribe($_newsSubscriberID, $_incomingSubscriberHash)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        try {
            $_SWIFT_NewsSubscriberObject = new SWIFT_NewsSubscriber($_newsSubscriberID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->UserInterface->Error(true, $this->Language->Get('invalidverifyhash'));
            $this->Load->Controller('Default', 'Base')->Load->Index();
            return false;
        }

        // @codeCoverageIgnoreStart
        // This code will never be executed
        if (!$_SWIFT_NewsSubscriberObject instanceof SWIFT_NewsSubscriber || !$_SWIFT_NewsSubscriberObject->GetIsClassLoaded()) {
            return false;
        }
        // @codeCoverageIgnoreEnd

        $_subscriberHash = substr(md5(SWIFT::Get('installationhash') . $_SWIFT_NewsSubscriberObject->GetProperty('email')), 0, 20);

        if ($_subscriberHash != $_incomingSubscriberHash) {
            $this->UserInterface->Error(true, $this->Language->Get('invalidverifyhash'));
            $this->Load->Controller('Default', 'Base')->Load->Index();
            return false;
        }

        $_SWIFT_NewsSubscriberObject->Delete();

        $this->UserInterface->Info(true, $this->Language->Get('thankyouunsubscriber'));
        $this->Load->Controller('Default', 'Base')->Load->Index();

        return true;
    }
}
