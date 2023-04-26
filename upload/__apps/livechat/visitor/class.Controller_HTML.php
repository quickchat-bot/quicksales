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
use LiveChat\Models\Note\SWIFT_VisitorNote;
use LiveChat\Models\Session\SWIFT_SessionManager;
use LiveChat\Models\Visitor\SWIFT_Visitor;
use LiveChat\Models\Visitor\SWIFT_VisitorData;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_Session;
use SWIFT_TemplateEngine;

/**
 * The Visitor HTML Controller
 *
 * @author Varun Shoor
 */
class Controller_HTML extends Controller_visitor
{
    // Core Constants
    const TAG_HTMLBUTTON = 'htmlbutton';
    const TAG_SITEBADGE = 'sitebadge';
    const TAG_TEXTLINK = 'textlink';
    const TAG_MONITORING = 'monitoring';

    public $FirePHP;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        // Load the Template & Language Caches
        $this->Template->LoadCache(array('proactivechatdiv', 'chatimage'));
        $this->Language->Queue('livechatclient');
        $this->Language->Queue('geoip');
        $this->Language->LoadQueue();
    }

    /**
     * Convert the Base64 Data into an array
     *
     * @author Varun Shoor
     * @param string $_base64DataMain
     * @return array The processed array
     * @throws SWIFT_Exception
     */
    public static function Base64ToArray($_base64DataMain)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_base64DataMain)) {
            return array();
        }

        $_valueContainer = array();

        $_base64Data = $_base64DataMain;
        if (strstr($_base64DataMain, ':')) {
            $_base64Data = substr($_base64DataMain, strpos($_base64DataMain, ':') + 1);
        }

        $_base64Decoded = base64_decode($_base64Data);
        if (empty($_base64Decoded)) {
            return array();
        }

        // Now that we have the base64 data.. we need to split it according to new line
        $_chunkHolder = explode(SWIFT_CRLF, $_base64Decoded);
        if (!_is_array($_chunkHolder) || count($_chunkHolder) != 2) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_chunkSignature = $_chunkHolder[1];
        if (trim($_chunkSignature) == '' || empty($_chunkSignature)) {
            return array();
        } else if ($_chunkSignature != sha1($_chunkHolder[0] . SWIFT::Get('InstallationHash'))) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        parse_str($_chunkHolder[0], $_valueContainer);
        if (!_is_array($_valueContainer)) {
            return array();
        }

        $_valueContainer['filterdepartmentidlist'] = array();
        $_valueContainer['routechatskillidlist'] = array();

        foreach (array('filterdepartmentid', 'routechatskillid') as $_key => $_val) {
            if (isset($_valueContainer[$_val]) && !empty($_valueContainer[$_val])) {
                if (is_numeric($_valueContainer[$_val])) {
                    $_valueContainer[$_val . 'list'][] = (int)($_valueContainer[$_val]);
                } else if (strstr($_valueContainer[$_val], ',')) {
                    $_chunkContainer = explode(',', $_valueContainer[$_val]);
                    if (_is_array($_chunkContainer)) {
                        foreach ($_chunkContainer as $_chunkKey => $_chunkVal) {
                            if (is_numeric($_chunkVal)) {
                                $_valueContainer[$_val . 'list'][] = (int)($_chunkVal);
                            }
                        }
                    }
                }
            }
        }

        $_valueContainer['filterdepartmentid'] = implode(',', $_valueContainer['filterdepartmentidlist']);
        $_valueContainer['routechatskillid'] = implode(',', $_valueContainer['routechatskillidlist']);

        return $_valueContainer;
    }

    /**
     * Display the HTML Button
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function HTMLButtonBase()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_valueContainer = array('filterdepartmentid' => '', 'filterdepartmentidlist' => array(), 'routechatskillid' => '', 'routechatskillidlist' => array());

        $this->_Render(self::TAG_HTMLBUTTON, $_valueContainer);

        return true;
    }

    /**
     * Display the HTML Button
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function HTMLButton()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_valueContainer = self::Base64ToArray($this->Router->GetRawQueryString());

        $this->_Render(self::TAG_HTMLBUTTON, $_valueContainer);

        return true;
    }

    /**
     * Display the Site Badge
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SiteBadge()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_valueContainer = self::Base64ToArray($this->Router->GetRawQueryString());

        $this->_Render(self::TAG_SITEBADGE, $_valueContainer);

        return true;
    }

    /**
     * Display the Image
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function NoJSImage()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_valueContainer = self::Base64ToArray($this->Router->GetRawQueryString());

        $this->_Render(self::TAG_HTMLBUTTON, $_valueContainer, true);

        return true;
    }

    /**
     * Display the Text Link
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function TextLink()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return true;
    }

    /**
     * Display the Monitoring Code
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Monitoring()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_valueContainer = self::Base64ToArray($this->Router->GetRawQueryString());

        $this->_Render(self::TAG_MONITORING, $_valueContainer);

        return true;
    }

    /**
     * Insert visitor footprint
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertFootprint()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_valueContainer = array('filterdepartmentid' => '', 'filterdepartmentidlist' => array(), 'routechatskillid' => '', 'routechatskillidlist' => array(), 'insertfootprintandleave' => true);

        $this->_Render(self::TAG_MONITORING, $_valueContainer);

        return true;
    }

    /**
     * Process the additional incoming visitor data like skills, alerts & variables
     *
     * @author Varun Shoor
     * @param string $_visitorSessionID The Visitor Session ID
     * @param array $_valueContainer THe Value Container
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ProcessIncomingVisitorData($_visitorSessionID, $_valueContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ((isset($_valueContainer['routechatskillidlist']) && _is_array($_valueContainer['routechatskillidlist'])) ||
            (isset($_valueContainer['variable']) && _is_array($_valueContainer['variable'])) ||
            (isset($_valueContainer['alert']) && _is_array($_valueContainer['alert']))) {
            if (!empty($_visitorSessionID)) {

                SWIFT_VisitorData::DeleteList($_visitorSessionID, array(0));
            }
        } else {
            return false;
        }

        // Process Chat Skills
        if (isset($_valueContainer['routechatskillidlist']) && _is_array($_valueContainer['routechatskillidlist'])) {
            foreach ($_valueContainer['routechatskillidlist'] as $_key => $_val) {
                SWIFT_VisitorData::Insert($_visitorSessionID, 0, SWIFT_VisitorData::DATATYPE_SKILL, (int)($_val), (int)($_val));
            }
        }

        // Process Variables
        if (isset($_valueContainer['variable']) && _is_array($_valueContainer['variable'])) {
            foreach ($_valueContainer['variable'] as $_key => $_val) {
                if (!isset($_val[0]) || !isset($_val[1]) || empty($_val[0])) {
                    continue;
                }

                SWIFT_VisitorData::Insert($_visitorSessionID, 0, SWIFT_VisitorData::DATATYPE_VARIABLE, $_val[0], $_val[1]);
            }
        }

        // Process Alerts
        if (isset($_valueContainer['alert']) && _is_array($_valueContainer['alert'])) {
            foreach ($_valueContainer['alert'] as $_key => $_val) {
                if (!isset($_val[0]) || !isset($_val[1]) || empty($_val[0])) {
                    continue;
                }

                SWIFT_VisitorData::Insert($_visitorSessionID, 0, SWIFT_VisitorData::DATATYPE_ALERT, $_val[0], $_val[1]);
            }
        }

        return true;
    }

    /**
     * Core Renderer Controller
     *
     * @param mixed $_tagType The Tag Type
     * @param array $_valueContainer The Value Container
     * @param bool $_dispatchOnlyImage Whether to dispatch only image and no JS
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @author Varun Shoor
     */
    protected function _Render($_tagType, $_valueContainer, $_dispatchOnlyImage = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Handling Caching of the Header Data based on Settings
        @header("Content-Type: text/javascript");

        if ($this->Settings->Get('livesupport_cachehtmljscode') == '1') {
            HeaderCache();
        } else {
            HeaderNoCache();
        }

        $_hasNotes = false;

        if (isset($_valueContainer['skipuserdetails']) && $_valueContainer['skipuserdetails'] == '1') {
            $this->Template->Assign('_isInlineRequest', true);
        } else {
            $this->Template->Assign('_isInlineRequest', false);
        }

        if (!isset($_valueContainer['prompttype'])) {
            $_valueContainer['prompttype'] = 'chat';
        }

        $_staffStatus = 0;
        $_uniqueID = '';
        if (isset($_valueContainer['uniqueid'])) {
            $_uniqueID = $_valueContainer['uniqueid'];
        }


        $_promptType = '';
        if ($_valueContainer['prompttype'] == 'chat') {
            $_staffStatus = SWIFT_Visitor::GetStaffOnlineStatus($_valueContainer['filterdepartmentidlist']);
            $_promptType = 'chat';
            $this->Template->Assign('_promptPhone', '');

        } else if ($_valueContainer['prompttype'] == 'call') {
            $_staffStatus = SWIFT_Visitor::GetStaffPhoneStatus($_valueContainer['filterdepartmentidlist']);
            $_promptType = 'call';
            $this->Template->Assign('_promptPhone', 'phone');
        }

        $this->Template->Assign('_promptType', $_promptType);

        if ($_promptType == 'chat') {
            if ($_staffStatus == SWIFT_Session::STATUS_ONLINE) {
                $this->Template->Assign('_staffStatus', 'online');
                $this->Template->Assign('_staffStatusText', $this->Language->Get('clickforlivechat'));
            } else if ($_staffStatus == SWIFT_Session::STATUS_AWAY || $_staffStatus == SWIFT_Session::STATUS_AUTOAWAY || $_staffStatus == SWIFT_Session::STATUS_BUSY) {
                $this->Template->Assign('_staffStatus', 'away');
                $this->Template->Assign('_staffStatusText', $this->Language->Get('lsisaway'));
            } else if ($_staffStatus == SWIFT_Session::STATUS_BACK) {
                $this->Template->Assign('_staffStatus', 'back');
                $this->Template->Assign('_staffStatusText', $this->Language->Get('lsbacksoon'));
            } else {
                $this->Template->Assign('_staffStatus', 'offline');
                $this->Template->Assign('_staffStatusText', $this->Language->Get('clickleavemessage'));
            }

        } else if ($_promptType == 'call') {
            if ($_staffStatus == SWIFT_Session::STATUS_ONLINE) {
                $this->Template->Assign('_staffStatus', 'online');
                $this->Template->Assign('_staffStatusText', $this->Language->Get('clickforlivechat'));
            } else if ($_staffStatus == SWIFT_Session::STATUS_AWAY) {
                $this->Template->Assign('_staffStatus', 'away');
                $this->Template->Assign('_staffStatusText', $this->Language->Get('lsisaway'));
            } else {
                $this->Template->Assign('_staffStatus', 'offline');
                $this->Template->Assign('_staffStatusText', $this->Language->Get('clickleavemessage'));
            }

        }

        $_isNewSession = false;
        // Does this user have a session id set?
        $_cookieSessionID = $this->Cookie->Get('sessionid' . SWIFT_Interface::INTERFACE_VISITOR);
        if (trim($_cookieSessionID) == '') {
            $_isNewSession = true;

            $_sessionID = SWIFT_Session::InsertAndStart(0);
        } else {
            $_sessionID = $_cookieSessionID;

            SWIFT_Session::Start($_SWIFT->Interface);
        }

        // Parse the Visitor Cache Cookie. This cookie is used to cache the data to speed up any recurring fetches
        $_rebuildCookieCache = false;
        $this->Cookie->Parse('visitor');

        // Does this person have a geoip set in cookie? This allows us to restrict geoip fetching query to just one per session, neat huh? ;)
        $_geoIPExecuted = $this->Cookie->GetVariable('visitor', 'geoip');
        $_geoIP = array();
        if ($_geoIPExecuted == 1) {
            // GeoIP has been executed.. retrieve the values
            $_geoIP[SWIFT_GeoIP::GEOIP_ISP] = $this->Cookie->GetVariable('visitor', SWIFT_GeoIP::GEOIP_ISP);
            $_geoIP[SWIFT_GeoIP::GEOIP_ORGANIZATION] = $this->Cookie->GetVariable('visitor', SWIFT_GeoIP::GEOIP_ORGANIZATION);
            $_geoIP[SWIFT_GeoIP::GEOIP_NETSPEED] = $this->Cookie->GetVariable('visitor', SWIFT_GeoIP::GEOIP_NETSPEED);
            $_geoIP[SWIFT_GeoIP::GEOIP_COUNTRY] = $this->Cookie->GetVariable('visitor', SWIFT_GeoIP::GEOIP_COUNTRY);
            $_geoIP[SWIFT_GeoIP::GEOIP_COUNTRYDESC] = $this->Cookie->GetVariable('visitor', SWIFT_GeoIP::GEOIP_COUNTRYDESC);
            $_geoIP[SWIFT_GeoIP::GEOIP_REGION] = $this->Cookie->GetVariable('visitor', SWIFT_GeoIP::GEOIP_REGION);
            $_geoIP[SWIFT_GeoIP::GEOIP_CITY] = $this->Cookie->GetVariable('visitor', SWIFT_GeoIP::GEOIP_CITY);
            $_geoIP[SWIFT_GeoIP::GEOIP_POSTALCODE] = $this->Cookie->GetVariable('visitor', SWIFT_GeoIP::GEOIP_POSTALCODE);
            $_geoIP[SWIFT_GeoIP::GEOIP_LATITUDE] = $this->Cookie->GetVariable('visitor', SWIFT_GeoIP::GEOIP_LATITUDE);
            $_geoIP[SWIFT_GeoIP::GEOIP_LONGITUDE] = $this->Cookie->GetVariable('visitor', SWIFT_GeoIP::GEOIP_LONGITUDE);
            $_geoIP[SWIFT_GeoIP::GEOIP_METROCODE] = $this->Cookie->GetVariable('visitor', SWIFT_GeoIP::GEOIP_METROCODE);
            $_geoIP[SWIFT_GeoIP::GEOIP_AREACODE] = $this->Cookie->GetVariable('visitor', SWIFT_GeoIP::GEOIP_AREACODE);
            $_geoIP[SWIFT_GeoIP::GEOIP_TIMEZONE] = $this->Cookie->GetVariable('visitor', SWIFT_GeoIP::GEOIP_TIMEZONE);

        } else {
            $_geoIPContainer = SWIFT_GeoIP::GetIPDetails(SWIFT::Get('IP'), array(SWIFT_GeoIP::GEOIP_ISP, SWIFT_GeoIP::GEOIP_NETSPEED, SWIFT_GeoIP::GEOIP_ORGANIZATION, SWIFT_GeoIP::GEOIP_CITY));

            // ======= GeoIP - ISP =======
            if (isset($_geoIPContainer[SWIFT_GeoIP::GEOIP_ISP]) && !empty($_geoIPContainer[SWIFT_GeoIP::GEOIP_ISP])) {
                $this->Cookie->AddVariable('visitor', SWIFT_GeoIP::GEOIP_ISP, $_geoIPContainer[SWIFT_GeoIP::GEOIP_ISP]);
                $_geoIP[SWIFT_GeoIP::GEOIP_ISP] = $_geoIPContainer[SWIFT_GeoIP::GEOIP_ISP];
            }

            // ======= GeoIP - Organization =======
            if (isset($_geoIPContainer[SWIFT_GeoIP::GEOIP_ORGANIZATION]) && !empty($_geoIPContainer[SWIFT_GeoIP::GEOIP_ORGANIZATION])) {
                $this->Cookie->AddVariable('visitor', SWIFT_GeoIP::GEOIP_ORGANIZATION, $_geoIPContainer[SWIFT_GeoIP::GEOIP_ORGANIZATION]);
                $_geoIP[SWIFT_GeoIP::GEOIP_ORGANIZATION] = $_geoIPContainer[SWIFT_GeoIP::GEOIP_ORGANIZATION];
            }

            // ======= GeoIP - Netspeed =======
            if (isset($_geoIPContainer[SWIFT_GeoIP::GEOIP_NETSPEED]) && !empty($_geoIPContainer[SWIFT_GeoIP::GEOIP_NETSPEED])) {
                $_returnedNetSpeed = $_geoIPContainer[SWIFT_GeoIP::GEOIP_NETSPEED];
                $_languageNetSpeed = $this->Language->Get($_returnedNetSpeed);
                $_netSpeed = $_returnedNetSpeed;
                if (!empty($_languageNetSpeed)) {
                    $_netSpeed = $_languageNetSpeed;
                }
                $this->Cookie->AddVariable('visitor', SWIFT_GeoIP::GEOIP_NETSPEED, $_netSpeed);
                $_geoIP[SWIFT_GeoIP::GEOIP_NETSPEED] = $_netSpeed;
            }

            // ======= GeoIP - City =======
            if (isset($_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]) && _is_array($_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY])) {
                $geoData = require_once('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_INCLUDESDIRECTORY . '/data.geoipregions.php');

                $_returnedCountry = $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['country'];
                $_returnedRegion = $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['region'];

                $_isoRegion = '';
                if (isset($geoData['__region'][$_returnedCountry][$_returnedRegion])) {
                    $_isoRegion = $geoData['__region'][$_returnedCountry][$_returnedRegion];
                }

                if (empty($_isoRegion) && isset($_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['region'])) {
                    $_isoRegion = $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['region'];
                }

                $_isoCountry = '';
                if (isset($geoData['__country'][$_returnedCountry])) {
                    $_isoCountry = $geoData['__country'][$_returnedCountry];
                }
                if (empty($_isoCountry) && isset($_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['country'])) {
                    $_isoCountry = $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['country'];
                }

                $_timeZone = '';
                if (isset($geoData['__timezone'][$_returnedCountry][$_returnedRegion])) {
                    $_timeZone = $geoData['__timezone'][$_returnedCountry][$_returnedRegion];
                } else if (isset($geoData['__timezone'][$_returnedCountry]['0'])) {
                    $_timeZone = $geoData['__timezone'][$_returnedCountry]['0'];
                }

                $this->Cookie->AddVariable('visitor', SWIFT_GeoIP::GEOIP_COUNTRY, $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['country']);
                $this->Cookie->AddVariable('visitor', SWIFT_GeoIP::GEOIP_COUNTRYDESC, $_isoCountry);
                $this->Cookie->AddVariable('visitor', SWIFT_GeoIP::GEOIP_REGION, $_isoRegion);
                $this->Cookie->AddVariable('visitor', SWIFT_GeoIP::GEOIP_CITY, $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['city']);
                $this->Cookie->AddVariable('visitor', SWIFT_GeoIP::GEOIP_POSTALCODE, $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['postalcode']);
                $this->Cookie->AddVariable('visitor', SWIFT_GeoIP::GEOIP_LATITUDE, $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['latitude']);
                $this->Cookie->AddVariable('visitor', SWIFT_GeoIP::GEOIP_LONGITUDE, $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['longitude']);
                $this->Cookie->AddVariable('visitor', SWIFT_GeoIP::GEOIP_METROCODE, $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['metrocode']);
                $this->Cookie->AddVariable('visitor', SWIFT_GeoIP::GEOIP_AREACODE, $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['areacode']);
                $this->Cookie->AddVariable('visitor', SWIFT_GeoIP::GEOIP_TIMEZONE, $_timeZone);
                $_geoIP[SWIFT_GeoIP::GEOIP_COUNTRY] = $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['country'];
                $_geoIP[SWIFT_GeoIP::GEOIP_COUNTRYDESC] = $_isoCountry;
                $_geoIP[SWIFT_GeoIP::GEOIP_REGION] = $_isoRegion;
                $_geoIP[SWIFT_GeoIP::GEOIP_CITY] = $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['city'];
                $_geoIP[SWIFT_GeoIP::GEOIP_POSTALCODE] = $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['postalcode'];
                $_geoIP[SWIFT_GeoIP::GEOIP_LATITUDE] = $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['latitude'];
                $_geoIP[SWIFT_GeoIP::GEOIP_LONGITUDE] = $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['longitude'];
                $_geoIP[SWIFT_GeoIP::GEOIP_METROCODE] = $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['metrocode'];
                $_geoIP[SWIFT_GeoIP::GEOIP_AREACODE] = $_geoIPContainer[SWIFT_GeoIP::GEOIP_CITY]['areacode'];
                $_geoIP[SWIFT_GeoIP::GEOIP_TIMEZONE] = $_timeZone;
            }

            // To prevent further execution
            $this->Cookie->AddVariable('visitor', 'geoip', 1);

            $_rebuildCookieCache = true;
        }

        // We only execute the note query if we havent checked for that already
        if ($this->Cookie->GetVariable('visitor', 'notecheck') != '1') {
            // Does this visitor have a note attached to him?
            $_visitorNote = $this->Database->QueryFetch("SELECT COUNT(*) as totalnotes FROM " . TABLE_PREFIX . "visitornotes WHERE linktype = '" . SWIFT_VisitorNote::LINKTYPE_VISITOR . "' AND linktypevalue = '" . $this->Database->Escape(SWIFT::Get('IP')) . "'");
            if ($_visitorNote['totalnotes'] > 0) {
                $_hasNotes = '1';
            } else {
                $_hasNotes = '0';
            }

            $this->Cookie->AddVariable('visitor', 'notecheck', '1');

            $_rebuildCookieCache = true;
        }

        $_repeatVisit = false;
        $_lastVisitTimeline = false;
        $_lastChatTimeline = false;
        if ($this->Cookie->GetVariable('visitor', 'sessionid') != $_sessionID && !empty($_sessionID)) {
            if ($this->Cookie->GetVariable('visitor', 'lastvisit')) {
                $_lastVisitTimeline = (int)($this->Cookie->GetVariable('visitor', 'lastvisit'));
            }

            $this->Cookie->AddVariable('visitor', 'sessionid', $_sessionID);
            $this->Cookie->AddVariable('visitor', 'lastvisit', DATENOW);

            $_repeatVisit = true;

            $_rebuildCookieCache = true;
        }

        if ($this->Cookie->GetVariable('visitor', 'lastchat')) {
            $_lastChatTimeline = (int)($this->Cookie->GetVariable('visitor', 'lastchat'));
        }

        if ($_rebuildCookieCache == true) {
            $this->Cookie->Rebuild('visitor', true);
        }

        // Update this session last activity
        $_SWIFT_SessionManagerObject = new SWIFT_SessionManager($_sessionID);
        if ($_SWIFT_SessionManagerObject instanceof SWIFT_SessionManager && $_SWIFT_SessionManagerObject->GetIsClassLoaded()) {
            $_SWIFT_SessionManagerObject->UpdateSessionLastActivity();
        }

        $_randomSuffix = substr(BuildHash(), 0, 8);
        $this->Template->Assign('_randomSuffix', $_randomSuffix);

        $_isBanned = SWIFT_Visitor::IsBanned(false);
        $this->FirePHP->Info('--' . $_isBanned . '--');
        $this->Template->Assign('_isBanned', $_isBanned);
        $this->Template->Assign('_sessionID', $_sessionID);

        $_geoIPURL = '';
        foreach ($_geoIP as $_key => $_val) {
            $_geoIP[$_key] = addslashes($_val);
            $_geoIPURL .= '+"/_geoIP_' . ($_key) . '="+encodeURIComponent(geoip_' . $_randomSuffix . '[' . ($_key) . '])';
        }

        /*
         * ###############################################
         * PROCESS INCOMING DATA
         * ###############################################
         */
        self::ProcessIncomingVisitorData($_sessionID, $_valueContainer);


        /*
         * ###############################################
         * PROCESS ON ONLY IMAGE DISPATCH
         * ###############################################
         */
        if ($_dispatchOnlyImage == true) {
            $_imageURL = SWIFT::Get('themepathimages') . 'staffoffline.svg';

            $_defaultOnlineImage = 'staffonline.svg';
            $_defaultAwayImage = 'staffaway.svg';
            $_defaultBackImage = 'staffbackin5.svg';
            $_defaultOfflineImage = 'staffoffline.svg';
            if ($_promptType == 'call') {
                $_defaultOnlineImage = 'staffphoneonline.svg';
                $_defaultAwayImage = 'staffphoneaway.svg';
                $_defaultOfflineImage = 'staffphoneoffline.svg';

            }

            if ($_staffStatus == SWIFT_Session::STATUS_ONLINE) {
                if (isset($_valueContainer['customonline']) && !empty($_valueContainer['customonline'])) {
                    $_imageURL = $_valueContainer['customonline'];
                } else {
                    $_imageURL = SWIFT::Get('themepathimages') . $_defaultOnlineImage;
                }
            } else if ($_staffStatus == SWIFT_Session::STATUS_AWAY || $_staffStatus == SWIFT_Session::STATUS_AUTOAWAY || $_staffStatus == SWIFT_Session::STATUS_BUSY) {
                if (isset($_valueContainer['customaway']) && !empty($_valueContainer['customaway'])) {
                    $_imageURL = $_valueContainer['customaway'];
                } else {
                    $_imageURL = SWIFT::Get('themepathimages') . $_defaultAwayImage;
                }
            } else if ($_staffStatus == SWIFT_Session::STATUS_BACK) {
                if (isset($_valueContainer['custombackshortly']) && !empty($_valueContainer['custombackshortly'])) {
                    $_imageURL = $_valueContainer['custombackshortly'];
                } else {
                    $_imageURL = SWIFT::Get('themepathimages') . $_defaultBackImage;
                }
            } else if ($_staffStatus == SWIFT_Session::STATUS_OFFLINE || $_staffStatus == SWIFT_Session::STATUS_INVISIBLE) {
                if (isset($_valueContainer['customoffline']) && !empty($_valueContainer['customoffline'])) {
                    $_imageURL = $_valueContainer['customoffline'];
                } else {
                    $_imageURL = SWIFT::Get('themepathimages') . $_defaultOfflineImage;
                }
            }

            $intName = $_SWIFT->Interface->GetName() ?: SWIFT_INTERFACE;
            if ($intName === 'tests') {
                return true;
            }

            @header('Content-Type: ' . kc_mime_content_type($_imageURL));

            echo file_get_contents($_imageURL);

            exit;
        }

        $this->Template->Assign('_geoIP', $_geoIP);
        $this->Template->Assign('_hasNotes', (int)($_hasNotes));
        $this->Template->Assign('_isNewSession', (int)($_isNewSession));
        $this->Template->Assign('_repeatVisit', (int)($_repeatVisit));
        $this->Template->Assign('_lastVisitTimeline', (int)($_lastVisitTimeline));
        $this->Template->Assign('_lastChatTimeline', (int)($_lastChatTimeline));
        $this->Template->Assign('_clientRefresh', $this->Settings->Get('livesupport_clientpagerefresh') - 1);
        $this->Template->Assign('_chatWidth', $this->Settings->Get('livesupport_chatwidth') - 1);
        $this->Template->Assign('_chatHeight', $this->Settings->Get('livesupport_chatheight') - 1);
        $this->Template->Assign('_filterDepartmentID', urlencode($_valueContainer['filterdepartmentid']));
        $this->Template->Assign('_geoIPURL', $_geoIPURL);
        $this->Template->Assign('_visitorEngage', SWIFT_Visitor::PROACTIVE_ENGAGE);

        if (isset($_GET['fullname'])) {
            $this->Template->Assign('_fullName', urlencode($_GET['fullname']));
        } else {
            $this->Template->Assign('_fullName', '');
        }

        if (isset($_GET['email'])) {
            $this->Template->Assign('_email', urlencode($_GET['email']));
        } else {
            $this->Template->Assign('_email', '');
        }

        if (isset($_GET['subject'])) {
            $this->Template->Assign('_subject', urlencode($_GET['subject']));
        } else {
            $this->Template->Assign('_subject', '');
        }

        $this->Template->Assign('_inlineChatDivWidth', (int)($this->Settings->Get('livesupport_chatwidth')));

        $_proactiveChatTemplateData = $this->Template->Get('proactivechatdiv');
        $_inlineChatTemplateData = $this->Template->Get('inlinechatdiv');

        $this->Template->Assign('_proactiveChatData', self::HTMLToJavaScript($_proactiveChatTemplateData, "divData += ", false, false));
        $this->Template->Assign('_inlineChatData', self::HTMLToJavaScript($_inlineChatTemplateData, "divData += ", false, false));

        if (isset($_valueContainer['insertfootprintandleave'])) {
            $this->Template->Assign('_insertFootprintAndLeave', $_valueContainer['insertfootprintandleave']);
        } else {
            $this->Template->Assign('_insertFootprintAndLeave', false);
        }

        $this->Template->Render('visitor_htmlcode', SWIFT_TemplateEngine::TYPE_FILE);

        /*
         * ###############################################
         * TIME TO DECIDE ON RENDERING TYPE
         * ###############################################
         */
        $this->Template->Assign('_customImage', false);

        $_templateContents = '';
        if ($_tagType == self::TAG_SITEBADGE && !empty($_valueContainer['sitebadgecolor']) && !empty($_valueContainer['badgelanguage']) && !empty($_valueContainer['badgetext'])) {
            foreach (array('onlinecolor', 'offlinecolor', 'awaycolor', 'backshortlycolor') as $_key => $_val) {
                if (!isset($_valueContainer[$_val]) || !isset($_valueContainer[$_val . 'hover']) || !isset($_valueContainer[$_val . 'border'])) {
                    $_valueContainer[$_val] = $_valueContainer[$_val . 'hover'] = $_valueContainer[$_val . 'border'] = '#000000';
                }
            }

            if ($_staffStatus == SWIFT_Session::STATUS_ONLINE) {
                $this->Template->Assign('_badgeHoverColor', $_valueContainer['onlinecolorhover']);
                $this->Template->Assign('_badgeBorderColor', $_valueContainer['onlinecolorborder']);
                $this->Template->Assign('_badgeBackgroundColor', $_valueContainer['onlinecolor']);
            } else if ($_staffStatus == SWIFT_Session::STATUS_AWAY || $_staffStatus == SWIFT_Session::STATUS_AUTOAWAY || $_staffStatus == SWIFT_Session::STATUS_BUSY) {
                $this->Template->Assign('_badgeHoverColor', $_valueContainer['awaycolorhover']);
                $this->Template->Assign('_badgeBorderColor', $_valueContainer['awaycolorborder']);
                $this->Template->Assign('_badgeBackgroundColor', $_valueContainer['awaycolor']);
            } else if ($_staffStatus == SWIFT_Session::STATUS_BACK) {
                $this->Template->Assign('_badgeHoverColor', $_valueContainer['backshortlycolorhover']);
                $this->Template->Assign('_badgeBorderColor', $_valueContainer['backshortlycolorborder']);
                $this->Template->Assign('_badgeBackgroundColor', $_valueContainer['backshortlycolor']);
            } else {
                $this->Template->Assign('_badgeHoverColor', $_valueContainer['offlinecolorhover']);
                $this->Template->Assign('_badgeBorderColor', $_valueContainer['offlinecolorborder']);
                $this->Template->Assign('_badgeBackgroundColor', $_valueContainer['offlinecolor']);
            }

            $this->Template->Assign('_badgeTextColor', Clean($_valueContainer['sitebadgecolor']));
            $this->Template->Assign('_badgeLanguage', Clean($_valueContainer['badgelanguage']));
            $this->Template->Assign('_badgeText', Clean($_valueContainer['badgetext']));

            $_templateContents = $this->Template->Get('chatbadge');
        } else if ($_tagType == self::TAG_MONITORING) {
        } else if ($_tagType == self::TAG_HTMLBUTTON) {
            if ($_staffStatus == SWIFT_Session::STATUS_ONLINE && isset($_valueContainer['customonline']) && !empty($_valueContainer['customonline'])) {
                $this->Template->Assign('_customImage', htmlspecialchars($_valueContainer['customonline']));
            } else if (($_staffStatus == SWIFT_Session::STATUS_AWAY || $_staffStatus == SWIFT_Session::STATUS_AUTOAWAY || $_staffStatus == SWIFT_Session::STATUS_BUSY) && isset($_valueContainer['customaway']) && !empty($_valueContainer['customaway'])) {
                $this->Template->Assign('_customImage', htmlspecialchars($_valueContainer['customaway']));
            } else if ($_staffStatus == SWIFT_Session::STATUS_BACK && isset($_valueContainer['custombackshortly']) && !empty($_valueContainer['custombackshortly'])) {
                $this->Template->Assign('_customImage', htmlspecialchars($_valueContainer['custombackshortly']));
            } else if (($_staffStatus == SWIFT_Session::STATUS_OFFLINE || $_staffStatus == SWIFT_Session::STATUS_INVISIBLE) && isset($_valueContainer['customoffline']) && !empty($_valueContainer['customoffline'])) {
                $this->Template->Assign('_customImage', htmlspecialchars($_valueContainer['customoffline']));
            }

            $_templateContents = $this->Template->Get('chatimage');
        }

        if (!$_isBanned && !empty($_templateContents)) {
            echo 'var swifttagdiv=document.createElement("div");swifttagdiv.innerHTML = "' . addslashes(str_replace("\n", "", str_replace("\r\n", "\n", $_templateContents))) . '";document.getElementById("swifttagdatacontainer' . $_uniqueID . '").appendChild(swifttagdiv);';
        }

        return true;
    }

    /**
     * Converts HTML to Javascript write statements
     *
     * @author Varun Shoor
     * @param string $_htmlData The HTML Data
     * @param string $_prefix The Prefix
     * @param bool $_doSplit Whether to split the data line by line
     * @param bool $_isFunction Whether the data should be prefixed as a function
     * @return string The Processed Javascript Data
     */
    public static function HTMLToJavaScript($_htmlData, $_prefix, $_doSplit = false, $_isFunction = true)
    {
        $_htmlData = preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_htmlData);
        $_suffix = '';

        if ($_isFunction) {
            $_prefix = $_prefix . '(';
            $_suffix = ')';
        }

        $_javaScriptData = '';
        if ($_doSplit) {
            $_splitData = explode(SWIFT_CRLF, $_htmlData);
            foreach ($_splitData as $_key => $_val) {
                $_javaScriptData .= $_prefix . '"' . addslashes($_val) . '"' . $_suffix . ';' . SWIFT_CRLF;
            }
        } else {
            $_htmlData = str_replace(SWIFT_CRLF, "", $_htmlData);
            $_javaScriptData .= $_prefix . '"' . addslashes($_htmlData) . '"' . $_suffix . ';' . SWIFT_CRLF;
        }

        return $_javaScriptData;
    }

}
