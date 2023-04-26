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

namespace LiveChat\Visitor;

use Base\Models\GeoIP\SWIFT_GeoIP;
use Controller_visitor;
use LiveChat\Models\Rule\SWIFT_VisitorRule;
use LiveChat\Models\Session\SWIFT_SessionManager;
use LiveChat\Models\Visitor\SWIFT_Visitor;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Session;

/**
 * Update Footprint Controller
 *
 * @author Varun Shoor
 */
class Controller_VisitorUpdate extends Controller_visitor
{

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');
    }

    /**
     * Index Action
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Index($_arguments)
    {

    }

    /**
     * Check the visitor IP address to see if its ignored or not..
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _CheckVisitorIP()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$this->Settings->Get('livechat_ignorerange') || trim($this->Settings->Get('livechat_ignorerange')) == '') {
            return false;
        }

        if (stristr($this->Settings->Get('livechat_ignorerange'), ',')) {
            $_networkContainer = explode(',', $this->Settings->Get('livechat_ignorerange'));
            if (_is_array($_networkContainer)) {
                foreach ($_networkContainer as $_key => $_val) {
                    if (NetMatch(trim($_val), SWIFT::Get('IP'))) {
                        return true;
                    }
                }
            }
        } else {
            return NetMatch($this->Settings->Get('livechat_ignorerange'), SWIFT::Get('IP'));
        }

        return false;
    }

    /**
     * Update the Visitor Footprint
     *
     * @author Varun Shoor
     * @param array $_arguments The Arguments Passed into the Function
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Session cannot be loaded
     */
    public function UpdateFootprint($_arguments)
    {
        if (!isset($_arguments['_sessionID']) || empty($_arguments['_sessionID'])) {
            return false;
        }

        HeaderNoCache();

        // Is this visitor in ignore list?
        if ($this->_CheckVisitorIP()) {
            SWIFT_Visitor::EchoGIFImage('1');

            return true;
        }


        // Try to get the session
        $_isNewSession = false;
        if (!isset($_arguments['_isFirstTime']) || !is_numeric($_arguments['_isFirstTime'])) {
            $_arguments['_isFirstTime'] = 0;
        }
        $_SWIFT_SessionManagerObject = new SWIFT_SessionManager($_arguments['_sessionID']);
        if ($_SWIFT_SessionManagerObject instanceof SWIFT_SessionManager && $_SWIFT_SessionManagerObject->GetIsClassLoaded()) {
            if (!$_SWIFT_SessionManagerObject->ProcessVisitorUpdateSession()) {
                $_isBanned = SWIFT_Visitor::IsBanned(true);
                if ($_isBanned) {
                    SWIFT_Visitor::EchoGIFImage('1');

                    return true;
                }

                $_arguments['_isFirstTime'] = 1;
                $_arguments['_lastVisitTimeline'] = SWIFT_Visitor::GetLastVisit();
                //    $_isNewSession = true;
            }
        }

        $_SWIFT = SWIFT::GetInstance();

        // Update this session last activity
        if (!$_SWIFT->Session && isset($_arguments['_sessionID']) && !empty($_arguments['_sessionID'])) {
            if (!SWIFT_Session::Start($_SWIFT->Interface, $_arguments['_sessionID'])) {
                // Failed to load session
                if (!SWIFT_Session::InsertAndStart(0)) {
                    return false;
                }
            }
            $_arguments['_isFirstTime'] = 1;
        } else if ($_SWIFT->Session instanceof SWIFT_Session && $_SWIFT->Session->GetIsClassLoaded()) {
            $_SWIFT->Session->UpdateLastActivity();
        }

        /**
         * Calculate stuff and insert footprint
         */
        $_returnActionsContainer = array();
        $_visitorPropertiesContainer = array();
        $_visitorPropertiesContainer['pagetitle'] = $_visitorPropertiesContainer['appversion'] = $_visitorPropertiesContainer['browserversion'] = '';
        $_visitorPropertiesContainer['browsername'] = $_visitorPropertiesContainer['browsercode'] = $_visitorPropertiesContainer['operatingsys'] = '';
        $_visitorPropertiesContainer['platform'] = $_visitorPropertiesContainer['resolution'] = $_visitorPropertiesContainer['colordepth'] = '';

        if (isset($_arguments['_browserName'])) {
            $_visitorPropertiesContainer['browsername'] = strip_tags($_arguments['_browserName']);
        }

        if (isset($_arguments['_browserVersion'])) {
            $_visitorPropertiesContainer['browserversion'] = strip_tags($_arguments['_browserVersion']);
        }

        if (isset($_arguments['_appVersion'])) {
            $_visitorPropertiesContainer['appversion'] = strip_tags($_arguments['_appVersion']);
        }

        if (isset($_arguments['_browserCode'])) {
            $_visitorPropertiesContainer['browsercode'] = strip_tags($_arguments['_browserCode']);
        }

        if (isset($_arguments['_operatingSys'])) {
            $_visitorPropertiesContainer['operatingsys'] = strip_tags($_arguments['_operatingSys']);
        }

        if (isset($_arguments['_platform'])) {
            $_visitorPropertiesContainer['platform'] = strip_tags($_arguments['_platform']);
        }

        if (isset($_arguments['_resolution'])) {
            $_visitorPropertiesContainer['resolution'] = strip_tags($_arguments['_resolution']);
        }

        if (isset($_arguments['_colorDepth'])) {
            $_visitorPropertiesContainer['colordepth'] = strip_tags($_arguments['_colorDepth']);
        }

        if (isset($_arguments['_pageTitle'])) {
            $_visitorPropertiesContainer['pagetitle'] = htmlspecialchars(DecodeUTF8(base64_decode($_arguments['_pageTitle'])));
        }

        $_remoteIP = '';
        if (strstr(SWIFT::Get('IP'), ',')) {
            $_ipAddresses = explode(', ', SWIFT::Get('IP'));
            $_remoteIP = $_ipAddresses[0];
        } else {
            $_remoteIP = SWIFT::Get('IP');
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1577 PHP [Notice]: Undefined index: _lastVisitTimeline (visitor/class.Controller_VisitorUpdate.php:235)
         *
         * Comments: None
         */

        $_visitorPropertiesContainer['location'] = '';
        $_visitorPropertiesContainer['hasnotes'] = '0';
        $_visitorPropertiesContainer['repeatvisit'] = '0';
        $_visitorPropertiesContainer['lastvisit'] = '0';
        $_visitorPropertiesContainer['lastchat'] = '0';
        $_visitorPropertiesContainer['sessionid'] = '';
        if (isset($_arguments['_url'])) {
            $_visitorPropertiesContainer['location'] = strip_tags(DecodeUTF8($_arguments['_url']));
        }

        if (isset($_arguments['_hasNotes'])) {
            $_visitorPropertiesContainer['hasnotes'] = strip_tags($_arguments['_hasNotes']);
        }

        if (isset($_arguments['_repeatVisit'])) {
            $_visitorPropertiesContainer['repeatvisit'] = strip_tags($_arguments['_repeatVisit']);
        }

        if (isset($_arguments['_lastVisitTimeline'])) {
            $_visitorPropertiesContainer['lastvisit'] = strip_tags($_arguments['_lastVisitTimeline']);
        }

        if (isset($_arguments['_lastChatTimeline'])) {
            $_visitorPropertiesContainer['lastchat'] = strip_tags($_arguments['_lastChatTimeline']);
        }

        if (isset($_arguments['_sessionID'])) {
            $_visitorPropertiesContainer['sessionid'] = strip_tags($_arguments['_sessionID']);
        }

        /**
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-5012 Error: gethostbyaddr(): Address is not a valid IPv4 or IPv6 address
         *
         * Comments: The address is checked for a valid IP address.
         */
        $_visitorPropertiesContainer['ipaddress'] = SWIFT::Get('IP');
        $_visitorPropertiesContainer['hostname'] = preg_match('/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/', $_remoteIP) ? gethostbyaddr($_remoteIP) : false;

        if (isset($_arguments['_referrer'])) {
            $_searchEngineContainer = SWIFT_Visitor::CheckSearchEngine($_arguments['_referrer']);
            if (is_array($_searchEngineContainer)) {
                // User came from some search engine.
                $_visitorPropertiesContainer['searchenginename'] = DecodeUTF8($_searchEngineContainer['name']);
                $_visitorPropertiesContainer['searchstring'] = DecodeUTF8($_searchEngineContainer['query']);
                $_visitorPropertiesContainer['searchengineurl'] = DecodeUTF8($_searchEngineContainer['url']);
                $_visitorPropertiesContainer['rowbgcolor'] = $this->Settings->Get('livesupport_searchbgcolor');
                $_visitorPropertiesContainer['rowfrcolor'] = $this->Settings->Get('livesupport_searchfrcolor');
            }
        }

        $_visitorPropertiesContainer['referrer'] = '';
        if (isset($_arguments['_referrer'])) {
            $_visitorPropertiesContainer['referrer'] = strip_tags(DecodeUTF8($_arguments['_referrer']));
        }

        // ======= GeoIP =======
        $_geoIPContainer = array('geoipisp' => SWIFT_GeoIP::GEOIP_ISP, 'geoiporganization' => SWIFT_GeoIP::GEOIP_ORGANIZATION, 'geoipnetspeed' => SWIFT_GeoIP::GEOIP_NETSPEED, 'geoipcountry' => SWIFT_GeoIP::GEOIP_COUNTRY, 'geoipcountrydesc' => SWIFT_GeoIP::GEOIP_COUNTRYDESC, 'geoipregion' => SWIFT_GeoIP::GEOIP_REGION, 'geoipcity' => SWIFT_GeoIP::GEOIP_CITY, 'geoippostalcode' => SWIFT_GeoIP::GEOIP_POSTALCODE, 'geoiplatitude' => SWIFT_GeoIP::GEOIP_LATITUDE, 'geoiplongitude' => SWIFT_GeoIP::GEOIP_LONGITUDE, 'geoipmetrocode' => SWIFT_GeoIP::GEOIP_METROCODE, 'geoipareacode' => SWIFT_GeoIP::GEOIP_AREACODE, 'geoiptimezone' => SWIFT_GeoIP::GEOIP_TIMEZONE);

        foreach ($_geoIPContainer as $_geoIPKey => $_geoIPVal) {
            if (isset($_arguments['_geoIP_' . $_geoIPVal])) {
                $_visitorPropertiesContainer[$_geoIPKey] = $_arguments['_geoIP_' . $_geoIPVal];
            } else {
                $_visitorPropertiesContainer[$_geoIPKey] = '';
            }
        }

        if (isset($_arguments['_isNewSession']) && $_arguments['_isNewSession'] == '1') {
            $_isNewSession = true;
        }

        $_proactiveResult = 0;
        if ($_SWIFT->Session instanceof SWIFT_Session) {
            $_proactiveResult = $_SWIFT->Session->GetProperty('proactiveresult');
        }

        /*
         * BUG FIX - Ravi Sharma
         *
         * SWIFT-736 The number of pages the visitor has browsed is shown incorrectly sometimes.
         *
         * Comments: None
         */
        $_SWIFT_VisitorObject = false;
        if ($_arguments['_isFirstTime'] == 1) {

            try {
                $_SWIFT_VisitorObject = new SWIFT_Visitor($_arguments['_sessionID']);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                // Do nothing
            }

            $_pageurl = '';

            if ($_SWIFT_VisitorObject instanceof SWIFT_Visitor && $_SWIFT_VisitorObject->GetIsClassLoaded()) {
                $_pageurl = $_SWIFT_VisitorObject->GetPageUrl();
            }
            if (isset($_arguments['_url']) && $_arguments['_url'] == $_pageurl) {
                $_arguments['_isFirstTime'] = 0;
            }
        }

        /**
         * Is the page being loaded for first time?
         */
        if ($_arguments['_isFirstTime'] == 1) {

            if ($_visitorPropertiesContainer['sessionid'] != '') {
                $_SWIFT_VisitorObject = SWIFT_Visitor::Insert($_visitorPropertiesContainer['sessionid'], $_visitorPropertiesContainer);
            }

            // Now get all the footprints under this visitor for rule purposes
            if ($_isNewSession) {

                $_returnActionsEntersSite = SWIFT_VisitorRule::ExecuteAllRules(SWIFT_VisitorRule::RULETYPE_VISITORENTERSSITE, $_arguments['_sessionID'], md5($_visitorPropertiesContainer['location']), $_proactiveResult);
                if (_is_array($_returnActionsEntersSite)) {
                    $_returnActionsContainer = array_merge($_returnActionsContainer, $_returnActionsEntersSite);
                }
            }
        } else if ($_arguments['_isFirstTime'] == 0) {
            if (BuildRandom(1, 3) == 1) {
                SWIFT_Visitor::Flush();
            }

            if ($_SWIFT->Session && isset($_arguments['_url'])) {
                $_SWIFT_VisitorObject = new SWIFT_Visitor($_SWIFT->Session->GetSessionID(), md5($_arguments['_url']));
                if ($_SWIFT_VisitorObject instanceof SWIFT_Visitor && $_SWIFT_VisitorObject->GetIsClassLoaded()) {
                    $_SWIFT_VisitorObject->Update();
                }
            }
        }

        $_returnActionsEntersPage = SWIFT_VisitorRule::ExecuteAllRules(SWIFT_VisitorRule::RULETYPE_VISITORENTERSPAGE, $_arguments['_sessionID'], md5($_visitorPropertiesContainer['location']), $_proactiveResult);
        if (_is_array($_returnActionsEntersPage)) {
            $_returnActionsContainer = array_merge($_returnActionsContainer, $_returnActionsEntersPage);
        }

        if (_is_array($_returnActionsContainer)) {
            foreach ($_returnActionsContainer as $_key => $_val) {
                if ($_val == SWIFT_Visitor::PROACTIVE_INLINE || $_val == SWIFT_Visitor::PROACTIVE_ENGAGE) {
                    $_SWIFT->Session->UpdateStatus($_val);

                    break;
                }
            }
        }

        if ($_SWIFT_VisitorObject instanceof SWIFT_Visitor && $_SWIFT_VisitorObject->GetIsClassLoaded()) {
            if ($_SWIFT->Session->GetProperty('status') == SWIFT_Visitor::PROACTIVE_INLINE) {
                SWIFT_Visitor::EchoGIFImage('2');

                $_SWIFT_VisitorObject->ResetVisitorStatus();
            } else if ($_SWIFT->Session->GetProperty('status') == SWIFT_Visitor::PROACTIVE_ENGAGE) {
                SWIFT_Visitor::EchoGIFImage('3');

                $_SWIFT_VisitorObject->ResetVisitorStatus();
            } else {
                SWIFT_Visitor::EchoGIFImage('1');
            }
        }

        return true;
    }

    /**
     * Reset the Proactive Chat Data
     *
     * @author Varun Shoor
     * @param array $_arguments The Arguments passed Over
     * @return bool "true" on Success, "false" otherwise
     */
    public function ResetProactive($_arguments)
    {
        if (!isset($_arguments['_sessionID']) || empty($_arguments['_sessionID'])) {
            return false;
        }

        $_SWIFT_VisitorObject = new SWIFT_Visitor($_arguments['_sessionID']);
        if ($_SWIFT_VisitorObject instanceof SWIFT_Visitor && $_SWIFT_VisitorObject->GetIsClassLoaded()) {
            $_SWIFT_VisitorObject->FlushEngageData();
        }

        return true;
    }

    /**
     * Accept the Proactive Chat
     *
     * @author Varun Shoor
     * @param array $_arguments The Arguments passed Over
     * @return bool "true" on Success, "false" otherwise
     */
    public function AcceptProactive($_arguments)
    {
        if (!isset($_arguments['_sessionID']) || empty($_arguments['_sessionID'])) {
            return false;
        }

        $_SWIFT_VisitorObject = new SWIFT_Visitor($_arguments['_sessionID']);
        if ($_SWIFT_VisitorObject instanceof SWIFT_Visitor && $_SWIFT_VisitorObject->GetIsClassLoaded()) {
            $_SWIFT_VisitorObject->AcceptedProactiveChat();
        }

        return true;
    }

}
