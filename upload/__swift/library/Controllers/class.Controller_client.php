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

use Base\Models\PolicyLink\SWIFT_PolicyLink;
use Base\Models\Template\SWIFT_TemplateGroup;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserConsent;
use Base\Models\Widget\SWIFT_Widget;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use News\Models\NewsItem\SWIFT_NewsItem;

/**
 * The Client Controller Class
 *
 * @author Varun Shoor
 */
class Controller_client extends SWIFT_Controller
{
    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
        $_SWIFT = SWIFT::GetInstance();

        parent::__construct();

        $_customTemplateGroup = false;

        // We dont check the session when loading CSS...
        if (($_SWIFT->Router->GetController() == 'Default' && $_SWIFT->Router->GetAction() == 'Index'))
        {
            $_argumentContainer = $_SWIFT->Router->GetArguments();

            // Did the user specify a custom template group?
            if (isset($_argumentContainer[0]) && !empty($_argumentContainer[0]))
            {
                $_customTemplateGroup = $_argumentContainer[0];
            }
        }

        if (!empty($_customTemplateGroup)) {
                    $this->Template->LoadTemplateGroup($_customTemplateGroup);
        }

        if (in_array(SWIFT_INTERFACE, ['tests', 'console'])) {
            return;
        }

        // We dont check the session when loading CSS...
        if (!SWIFT_Session::Start($this->Interface)) {
            // Failed to load session
            if (!SWIFT_Session::InsertAndStart(0))
            {
                echo 'Failed to load session';
                log_error_and_exit();
            }
        }

        // Check for template group restriction..
        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-1554 When users are logged into their associated template group, they can easily switch to other template group by changing the URL
         *
         */
        if ($_SWIFT->TemplateGroup->GetProperty('restrictgroups') == '1' && $_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded() && $_SWIFT->TemplateGroup->GetRegisteredUserGroupID() != $_SWIFT->User->GetProperty('usergroupid'))
        {
            $_templateGroupCache = $this->Cache->Get('templategroupcache');
            $_templateGroupString = '';
            foreach ($_templateGroupCache as $_templateGroup) {
                if ($_templateGroup['regusergroupid'] == $_SWIFT->User->GetProperty('usergroupid')) {
                    $_templateGroupString = $_templateGroup['title'];

                    break;
                }
            }

            if (empty($_templateGroupString)) {
                $_defaultTemplateGroupID = SWIFT_TemplateGroup::GetDefaultGroupID();

                if (!isset($_templateGroupCache[$_defaultTemplateGroupID])) {
                    throw new SWIFT_Exception('Invalid Default Template Group');
                }

                $_templateGroupString = $_templateGroupCache[$_defaultTemplateGroupID]['title'];
            }

            $_queryString = '';
            if ($_SWIFT->Router->GetArgumentsAsString() != '') {
                $_queryString = $_SWIFT->Router->GetArgumentsAsString();
            }

            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
             *
             * SWIFT-3635 User is redirected to an invalid URL in case 'Restrict Users Group' setting is enabled under Template Group setting.
             */
            $_redirectURL = SWIFT::Get('basename') . '/' . $_templateGroupString . $_queryString;

            header('location: ' . $_redirectURL);
            return;
        }

        // Remember me template variables processing
        $_cookieLoginEmail = $this->Cookie->Get('scloginemail', true);
        $_cookieLoginPassword = $this->Cookie->Get('scloginpassword', true);
        $_cookieHashCheck = $this->Cookie->Get('schashcheck');
        if (!empty($_cookieLoginEmail) && !empty($_cookieLoginPassword) && $_cookieHashCheck == sha1(SWIFT::Get('InstallationHash')) && !$_SWIFT->Session->IsLoggedIn())
        {
            header('location: ' . StripTrailingSlash(SWIFT::Get('swiftpath')) . '/index.php?/Base/User/Login');
        }

        $this->Template->Assign('_userLoginEmail', '');
        $this->Template->Assign('_userLoginPassword', '');
        $this->Template->Assign('_userRememberMe', false);

        if ($_SWIFT->Session->IsLoggedIn())
        {
            $this->Template->Assign('_userIsLoggedIn', true);

            /**
             * GDPR Feature KAYAKO-2164
             * @author Arotimi Busayo
             *
             * Show check off screen for yet to be captured Registered User processing consent.
             */
            $_registrationPolicyURL = SWIFT_PolicyLink::RetrieveURL($_SWIFT->User->GetProperty('languageid'));
            $this->Template->Assign('_registrationPolicyURL', $_registrationPolicyURL);
            if (! (SWIFT_UserConsent::RetrieveConsent($_SWIFT->User->GetUserID(), SWIFT_UserConsent::CONSENT_REGISTRATION))) {
                $this->Template->Assign('_showCheckOffScreen', true);
            }
        } else {
            $this->Template->Assign('_userIsLoggedIn', false);
            $languageID = $this->Cookie->GetVariable('client', 'languageid');
            $_registrationPolicyURL = SWIFT_PolicyLink::RetrieveURL($languageID);
            $this->Template->Assign('_registrationPolicyURL', $_registrationPolicyURL);
        }

        $_isNewsAppRegistered = false;
        if (SWIFT_App::IsInstalled(APP_NEWS) && SWIFT_Widget::IsWidgetVisible(APP_NEWS))
        {
            $_isNewsAppRegistered = true;
        }

        $this->Template->Assign('_isNewsAppRegistered', $_isNewsAppRegistered);

        $_redirectURL = htmlspecialchars($_SWIFT->Router->GetArgumentsAsString());
        $_redirectURL = preg_replace('#/knowledgebase/comments/submit/(\d+)#i', '/Knowledgebase/Article/View/$1', $_redirectURL);

        $this->Template->Assign('_redirectAction', $_redirectURL);

        $this->Template->Assign('_csrfhash', $_SWIFT->Session->GetProperty('csrfhash'));

        $_canPostComments = true;
        $_canSubscribeNews = false;

        if (SWIFT_User::GetPermission('perm_canpostcomment') == '0') {
            $_canPostComments = false;
        }

        if (SWIFT_App::IsInstalled(APP_NEWS) && SWIFT_User::GetPermission('perm_cansubscribenews') != '0' && SWIFT_Widget::IsWidgetVisible(APP_NEWS)) {
            $_canSubscribeNews = true;
        }

        $this->Template->Assign('_canPostComments', $_canPostComments);
        $this->Template->Assign('_canSubscribeNews', $_canSubscribeNews);

        $_navbarMenuItemContainer = array();

        /**
         * Begin Hook: client_init
         */

        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('client_init')) ? eval($_hookCode) : false;

        /**
         * End Hook
         */


        $this->Template->Assign('_navbarMenuItemContainer', $_navbarMenuItemContainer);
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Process the News Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _ProcessNews()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_ProcessNewsCategories();

        $_showNews = $_filterNews = false;

        $_newsContainer = $_newsCategoryContainer = array();
        if (SWIFT_App::IsInstalled(APP_NEWS) && $this->Settings->Get('nw_enablenewsnav') == '1' && SWIFT_Widget::IsWidgetVisible(APP_NEWS))
        {
            SWIFT_Loader::LoadModel('NewsItem:NewsItem', APP_NEWS);

            $_showNews = true;
            $_newsCategoryContainer = SWIFT_NewsItem::RetrieveCategoryCount(array(SWIFT_NewsItem::TYPE_GLOBAL, SWIFT_NewsItem::TYPE_PUBLIC), SWIFT::Get('usergroupid'));
            if (count($_newsCategoryContainer))
            {
                $_filterNews = true;
            }

            $_newsContainer = SWIFT_NewsItem::Retrieve($this->Settings->Get('nw_maxnewslist'), 0, array(SWIFT_NewsItem::TYPE_GLOBAL, SWIFT_NewsItem::TYPE_PUBLIC), SWIFT::Get('usergroupid'));
        }

        $this->Template->Assign('_showIndexNews', $_showNews);
        $this->Template->Assign('_filterNews', $_filterNews);
        $this->Template->Assign('_newsContainer', $_newsContainer);
        $this->Template->Assign('_newsCategoryContainer', $_newsCategoryContainer);
        $this->Template->Assign('_newsCount', count($_newsContainer));

        return true;
    }

    /**
     * Process the News Categories
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _ProcessNewsCategories()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_filterNews = false;

        $_newsCategoryContainer = array();
        if (SWIFT_App::IsInstalled(APP_NEWS) && SWIFT_Widget::IsWidgetVisible(APP_NEWS))
        {
            SWIFT_Loader::LoadModel('NewsItem:NewsItem', APP_NEWS);

            $_newsCategoryContainer = SWIFT_NewsItem::RetrieveCategoryCount(array(SWIFT_NewsItem::TYPE_GLOBAL, SWIFT_NewsItem::TYPE_PUBLIC), SWIFT::Get('usergroupid'));
            if (count($_newsCategoryContainer))
            {
                $_filterNews = true;
            }
        }

        $this->Template->Assign('_filterNews', $_filterNews);
        $this->Template->Assign('_newsCategoryContainer', $_newsCategoryContainer);

        return true;
    }

    /**
     * Process the Knowledgebase Categories
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _ProcessKnowledgebaseCategories()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_filterKnowledgebase = false;

        $_knowledgebaseCategoryContainer = array();
        if (SWIFT_App::IsInstalled(APP_KNOWLEDGEBASE) && SWIFT_Widget::IsWidgetVisible(APP_KNOWLEDGEBASE))
        {
            SWIFT_Loader::LoadModel('Category:KnowledgebaseCategory', APP_KNOWLEDGEBASE);

            $_knowledgebaseMainCategoryContainer = SWIFT_KnowledgebaseCategory::Retrieve(array(SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, SWIFT_KnowledgebaseCategory::TYPE_PUBLIC,
                SWIFT_KnowledgebaseCategory::TYPE_INHERIT), 0, 0, SWIFT::Get('usergroupid'), false);

            $_knowledgebaseCategoryContainer = $_knowledgebaseMainCategoryContainer[0];
            if (count($_knowledgebaseCategoryContainer))
            {
                $_filterKnowledgebase = true;
            }

        }

        $this->Template->Assign('_filterKnowledgebase', $_filterKnowledgebase);
        $this->Template->Assign('_navKnowledgebaseCategoryContainer', $_knowledgebaseCategoryContainer);

        return true;
    }

    /**
     * Load a Template Group
     *
     * @author Varun Shoor
     * @param mixed $_templateGroup The Template Group Name or ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _LoadTemplateGroup($_templateGroup) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())     {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');

        if (empty($_templateGroup)) {
            return false;
        }

        if (is_numeric($_templateGroup) && !isset($_templateGroupCache[$_templateGroup])) {
            return false;
        } else if (is_string($_templateGroup)) {
            $_finalTemplateGroupID = false;
            foreach ($_templateGroupCache as $_templateGroupID => $_templateGroupContainer) {
                if ($_templateGroupContainer['title'] == $_templateGroup) {
                    $_finalTemplateGroupID = $_templateGroupID;
                }
            }

            if (empty($_finalTemplateGroupID)) {
                return false;
            }

            $_templateGroup = $_finalTemplateGroupID;
        }

        // If the template group is already loaded then bail out
        if ($_SWIFT->TemplateGroup instanceof SWIFT_TemplateGroup && $_SWIFT->TemplateGroup->GetIsClassLoaded() && $_SWIFT->TemplateGroup->GetTemplateGroupID() == $_templateGroup) {
            return false;
        }

        $_SWIFT->Template->LoadTemplateGroup($_templateGroup);

        return true;
    }
}
