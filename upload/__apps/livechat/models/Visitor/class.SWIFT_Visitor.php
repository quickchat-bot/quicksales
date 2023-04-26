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

namespace LiveChat\Models\Visitor;

use Base\Models\Department\SWIFT_Department;
use LiveChat\Models\Ban\SWIFT_VisitorBan;
use LiveChat\Models\Session\SWIFT_SessionManager;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_Model;
use SWIFT_Session;
use LiveChat\Models\Visitor\SWIFT_Visitor_Exception;
use LiveChat\Models\Visitor\SWIFT_VisitorData;

/**
 * The Live Chat Visitor Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Visitor extends SWIFT_Model
{
    const TABLE_NAME = 'visitorfootprints';
    const PRIMARY_KEY = 'sessionid';

    const TABLE_STRUCTURE = "sessionid C(255) DEFAULT '' NOTNULL,
                                pageurl C(255) DEFAULT '' NOTNULL,
                                pagehash C(32) DEFAULT '' NOTNULL,
                                pagetitle C(255) DEFAULT '' NOTNULL,
                                country C(255) DEFAULT '' NOTNULL,
                                countrycode C(2) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                lastactivity I DEFAULT '0' NOTNULL,
                                ipaddress C(50) DEFAULT '0.0.0.0' NOTNULL,
                                hostname C(80) DEFAULT '' NOTNULL,
                                referrer C(255) DEFAULT '' NOTNULL,
                                resolution C(30) DEFAULT '' NOTNULL,
                                colordepth C(20) DEFAULT '' NOTNULL,
                                appversion C(150) DEFAULT '' NOTNULL,
                                operatingsys C(20) DEFAULT '' NOTNULL,
                                browsername C(150) DEFAULT '' NOTNULL,
                                browserversion C(150) DEFAULT '' NOTNULL,
                                browsercode C(2) DEFAULT '' NOTNULL,
                                searchenginename C(20) DEFAULT '' NOTNULL,
                                searchstring C(255) DEFAULT '' NOTNULL,
                                searchengineurl C(200) DEFAULT '' NOTNULL,
                                platform C(150) DEFAULT '' NOTNULL,
                                rowbgcolor C(7) DEFAULT '' NOTNULL,
                                rowfrcolor C(7) DEFAULT '' NOTNULL,
                                hasnote I2 DEFAULT '0' NOTNULL,
                                repeatvisit I2 DEFAULT '0' NOTNULL,
                                lastvisit I DEFAULT '0' NOTNULL,
                                lastchat I DEFAULT '0' NOTNULL,
                                topull I2 DEFAULT '0' NOTNULL,
                                campaignid I DEFAULT '0' NOTNULL,
                                campaigntitle I DEFAULT '0' NOTNULL,

                                geoiptimezone C(255) DEFAULT '' NOTNULL,
                                geoipisp C(255) DEFAULT '' NOTNULL,
                                geoiporganization C(255) DEFAULT '' NOTNULL,
                                geoipnetspeed C(255) DEFAULT '' NOTNULL,
                                geoipcountry C(10) DEFAULT '' NOTNULL,
                                geoipcountrydesc C(255) DEFAULT '' NOTNULL,
                                geoipregion C(255) DEFAULT '' NOTNULL,
                                geoipcity C(255) DEFAULT '' NOTNULL,
                                geoippostalcode C(255) DEFAULT '' NOTNULL,
                                geoiplatitude C(255) DEFAULT '' NOTNULL,
                                geoiplongitude C(255) DEFAULT '' NOTNULL,
                                geoipmetrocode C(255) DEFAULT '' NOTNULL,
                                geoipareacode C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'sessionid, pagehash';
    const INDEX_2 = 'lastactivity';

    /**
     * BUG FIX: Bishwanath Jha <bishwanath.jha@kayako.com>
     *
     * SWIFT-1619: To make mysql REPLACE command working creating UNIQUE KEY here, because unless the table has a PRIMARY KEY or UNIQUE index,
     * using a REPLACE statement makes no sense
     *
     */
    const INDEXTYPE_1 = 'UNIQUE';

    private $_sessionID;
    private $_pageHash;
    private $_footprintData = array();
    static private $_onlineStatusMapCache = array();

    // Core Constants
    const PROACTIVE_NONE = '0';
    const PROACTIVE_ENGAGE = '4';
    const PROACTIVE_INLINE = '6';
    const PROACTIVE_ENGAGECUSTOM = '8';

    const PROACTIVERESULT_DISPATCHED = '1';
    const PROACTIVERESULT_DENIED = '2';
    const PROACTIVERESULT_ACCEPTED = '3';

    const BAN_IP = 0;
    const BAN_CLASSC = 1;
    const BAN_CLASSB = 2;
    const BAN_CLASSA = 3;

    const DEFAULT_VISITOR_INACTIVITY = 500;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_sessionID The Session ID of the Visitor
     * @param string $_pageHash The Unique Page Hash
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function __construct($_sessionID, $_pageHash = '')
    {
        parent::__construct();

        if (trim($_sessionID) == '') {
            return;
        }

        if (!$this->SetSessionID($_sessionID) || !$this->SetPageHash($_pageHash)) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        }
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        if (!$this->GetPageHash()) {
            $_queryResult = $this->Database->AutoExecute(TABLE_PREFIX . 'visitorfootprints', $this->GetUpdatePool(), 'UPDATE', "sessionid = '" . $this->Database->Escape($this->GetSessionID()) . "'");
        } else {
            $_queryResult = $this->Database->AutoExecute(TABLE_PREFIX . 'visitorfootprints', $this->GetUpdatePool(), 'UPDATE', "sessionid = '" . $this->Database->Escape($this->GetSessionID()) . "' AND pagehash = '" . $this->Database->Escape($this->GetPageHash()) . "'");
        }

        $this->ClearUpdatePool();

        return $_queryResult;
    }

    /**
     * Sets the Session ID for the Visitor
     *
     * @author Varun Shoor
     * @param string $_sessionID The Session ID of Visitor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    private function SetSessionID($_sessionID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_sessionID = $_sessionID;

        return true;
    }

    /**
     * Sets the Unique Page Hash
     *
     * @author Varun Shoor
     * @param string $_pageHash The Unique Page Hash
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    private function SetPageHash($_pageHash)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_pageHash = $_pageHash;

        return true;
    }

    /**
     * Retrieves the Session ID of the visitor associated with this object
     *
     * @author Varun Shoor
     * @return string Session ID on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function GetSessionID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_sessionID;
    }

    /**
     * Retreive the Page Hash associated with this object
     *
     * @author Varun Shoor
     * @return string Page Hash on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    private function GetPageHash()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_pageHash;
    }

    /**
     * Sets the footprint data for the visitor
     *
     * @author Varun Shoor
     * @param array $_footprintData The Footprint Data
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    private function SetFootprintData($_footprintData)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_footprintData = $_footprintData;
    }

    /**
     * Retrieve the Footprint Data Container
     *
     * @author Varun Shoor
     * @return mixed "_footprintData" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    private function GetFootprintData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_footprintData;
    }

    /**
     * Insert a new Visitor Footprint
     *
     * @author Varun Shoor
     * @param string $_sessionID The Session ID for the Visitor
     * @param array $_properties The Visitor Properties Container
     * @return SWIFT_Visitor
     * @throws SWIFT_Visitor_Exception If Invalid Data is Provided
     */
    public static function Insert($_sessionID, $_properties)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_sessionID)) {
            throw new SWIFT_Visitor_Exception(SWIFT_INVALIDDATA);
        }

        $_visitorFields = array(
            // Generic Properties
            'sessionid' => ReturnNone($_sessionID),
            'pageurl' => ReturnNone($_properties['location']),
            'pagehash' => md5($_properties['location']),
            'pagetitle' => ReturnNone($_properties['pagetitle']),
            'country' => ReturnNone(''),
            'countrycode' => ReturnNone(''),
            'dateline' => DATENOW,
            'lastactivity' => DATENOW,
            'ipaddress' => ReturnNone($_properties['ipaddress']),
            'referrer' => ReturnNone($_properties['referrer']),
            'resolution' => ReturnNone($_properties['resolution']),
            'colordepth' => ReturnNone($_properties['colordepth']),
            'browsername' => ReturnNone($_properties['browsername']),
            'browserversion' => ReturnNone($_properties['browserversion']),
            'browsercode' => ReturnNone($_properties['browsercode']),
            'appversion' => ReturnNone($_properties['appversion']),
            'platform' => ReturnNone($_properties['platform']),
            'operatingsys' => ReturnNone($_properties['operatingsys']),
            'hasnote' => (int)($_properties['hasnotes']),
            'repeatvisit' => (int)($_properties['repeatvisit']),
            'lastvisit' => (int)($_properties['lastvisit']),
            'lastchat' => (int)($_properties['lastchat']),

            // GeoIP Properties
            'geoipisp' => ReturnNone($_properties['geoipisp']),
            'geoiporganization' => ReturnNone($_properties['geoiporganization']),
            'geoipnetspeed' => ReturnNone($_properties['geoipnetspeed']),
            'geoipcountry' => ReturnNone($_properties['geoipcountry']),
            'geoipcountrydesc' => ReturnNone($_properties['geoipcountrydesc']),
            'geoipregion' => ReturnNone($_properties['geoipregion']),
            'geoipcity' => ReturnNone($_properties['geoipcity']),
            'geoippostalcode' => ReturnNone($_properties['geoippostalcode']),
            'geoiplatitude' => ReturnNone($_properties['geoiplatitude']),
            'geoiplongitude' => ReturnNone($_properties['geoiplongitude']),
            'geoipmetrocode' => ReturnNone($_properties['geoipmetrocode']),
            'geoipareacode' => ReturnNone($_properties['geoipareacode']),
            'geoiptimezone' => ReturnNone($_properties['geoiptimezone'])
        );

        if (isset($_properties['host'])) {
            $_visitorFields['hostname'] = ReturnNone($_properties['host']);
        }

        if (isset($_properties['searchenginename'])) {
            $_visitorFields['searchenginename'] = ReturnNone($_properties['searchenginename']);
        }

        if (isset($_properties['searchstring'])) {
            $_visitorFields['searchstring'] = ReturnNone($_properties['searchstring']);
        }

        if (isset($_properties['searchengineurl'])) {
            $_visitorFields['searchengineurl'] = ReturnNone($_properties['searchengineurl']);
        }

        if (isset($_properties['rowbgcolor'])) {
            $_visitorFields['rowbgcolor'] = ReturnNone($_properties['rowbgcolor']);
        }

        if (isset($_properties['rowfrcolor'])) {
            $_visitorFields['rowfrcolor'] = ReturnNone($_properties['rowfrcolor']);
        }

        if (isset($_properties['topull'])) {
            $_visitorFields['topull'] = (int)($_properties['topull']);
        }

        $_queryResult = $_SWIFT->Database->Replace(TABLE_PREFIX . 'visitorfootprints', $_visitorFields, array('sessionid', 'pagehash'));

        return new SWIFT_Visitor($_sessionID, md5($_properties['location']));
    }

    /**
     * Update the last activity for the visitor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function Update()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('lastactivity', DATENOW);

        return $this->ProcessUpdatePool();
    }

    /**
     * Flush Inactive Visitors
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function Flush()
    {
        $_SWIFT = SWIFT::GetInstance();

        // If the value is null or corrupted (non-numeric) then use the default 500
        $visitorActivity = $_SWIFT->Settings->Get('security_visitorinactivity') ?? self::DEFAULT_VISITOR_INACTIVITY;
        $_timeline = DATENOW - is_numeric($visitorActivity) ? $visitorActivity : self::DEFAULT_VISITOR_INACTIVITY;

        $_deleteSessionList = array();

        $_SWIFT->Database->Query("SELECT sessionid FROM " . TABLE_PREFIX . "sessions WHERE sessiontype IN('" . SWIFT_Interface::INTERFACE_VISITOR . "', '" . SWIFT_Interface::INTERFACE_CHAT . "') AND lastactivity <= '" . (int)($_timeline) . "'", 2);
        while ($_SWIFT->Database->NextRecord(2)) {
            $_deleteSessionList[] = $_SWIFT->Database->Record2["sessionid"];
        }

        if (count($_deleteSessionList) > 0) {
            $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitorfootprints WHERE sessionid IN(" . BuildIN($_deleteSessionList) . ")");

            SWIFT_Session::DeleteList($_deleteSessionList);
        }

        return true;
    }

    /**
     * Processes a URL to see if it is of a popular search engine
     *
     * @author Varun Shoor
     * @param string $_fullURL The Full URL to Process
     * @return array|bool (name, url, query, fullurl) array on Success, "false" otherwise
     */
    public static function CheckSearchEngine($_fullURL)
    {
        $_URL = parse_url($_fullURL);

        if (!isset($_URL['query']) || !isset($_URL['host'])) {
            return false;
        }

        $_query = array('q' => '');
        parse_str($_URL["query"], $_query);
        $_returnResult = array();

        if (!$_query) {
            return false;
        }

        if (stristr($_URL['host'], 'google') && isset($_query['q'])) {
            $_returnResult['name'] = 'Google';
            $_returnResult['url'] = 'http://www.google.com';
            $_returnResult['query'] = $_query['q'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else if (stristr($_URL['host'], 'yahoo') && isset($_query['p'])) {
            $_returnResult['name'] = 'Yahoo!';
            $_returnResult['url'] = 'http://www.yahoo.com';
            $_returnResult['query'] = $_query['p'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else if (stristr($_URL['host'], 'bing') && isset($_query['q'])) {
            $_returnResult['name'] = 'Bing';
            $_returnResult['url'] = 'http://www.bing.com';
            $_returnResult['query'] = $_query['q'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else if (stristr($_URL['host'], 'aol') && isset($_query['query'])) {
            $_returnResult['name'] = 'AOL';
            $_returnResult['url'] = 'http://www.aol.com';
            $_returnResult['query'] = $_query['query'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else if (stristr($_URL['host'], 'netscape') && isset($_query['netscape'])) {
            $_returnResult['name'] = 'Netscape';
            $_returnResult['url'] = 'http://www.netscape.com';
            $_returnResult['query'] = $_query['netscape'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else if (stristr($_URL['host'], 'lycos') && isset($_query['query'])) {
            $_returnResult['name'] = 'Lycos';
            $_returnResult['url'] = 'http://www.lycos.com';
            $_returnResult['query'] = $_query['query'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else if (stristr($_URL['host'], 'hotbot') && isset($_query['query'])) {
            $_returnResult['name'] = 'HotBot';
            $_returnResult['url'] = 'http://www.hotbot.com';
            $_returnResult['query'] = $_query['query'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else if (stristr($_URL['host'], 'excite') && isset($_query['search'])) {
            $_returnResult['name'] = 'Excite';
            $_returnResult['url'] = 'http://www.excite.com';
            $_returnResult['query'] = $_query['search'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else if (stristr($_URL['host'], 'dogpile') && isset($_query['q'])) {
            $_returnResult['name'] = 'DogPile';
            $_returnResult['url'] = 'http://www.dogpile.com';
            $_returnResult['query'] = $_query['q'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else if (stristr($_URL['host'], 'dmoz') && isset($_query['search'])) {
            $_returnResult['name'] = 'dmoz.org';
            $_returnResult['url'] = 'http://www.dmoz.org';
            $_returnResult['query'] = $_query['search'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else if (stristr($_URL['host'], 'ask') && isset($_query['ask'])) {
            $_returnResult['name'] = 'Ask';
            $_returnResult['url'] = 'http://www.ask.com';
            $_returnResult['query'] = $_query['ask'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else if (stristr($_URL['host'], 'altavista') && isset($_query['q'])) {
            $_returnResult['name'] = 'Altavista';
            $_returnResult['url'] = 'http://www.altavista.com';
            $_returnResult['query'] = $_query['q'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else if (stristr($_URL['host'], 'alltheweb') && isset($_query['q'])) {
            $_returnResult['name'] = 'Alltheweb';
            $_returnResult['url'] = 'http://www.alltheweb.com';
            $_returnResult['query'] = $_query['q'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else if (stristr($_URL['host'], 'search.com') && isset($_query['q'])) {
            $_returnResult['name'] = 'C|Net Search';
            $_returnResult['url'] = 'http://www.search.com';
            $_returnResult['query'] = $_query['q'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else if (stristr($_URL['host'], 'baidu.com') && isset($_query['wd'])) {
            $_returnResult['name'] = 'Baidu';
            $_returnResult['url'] = 'http://www.baidu.com';
            $_returnResult['query'] = $_query['wd'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else if (stristr($_URL['host'], 'alexa.com') && isset($_query['q'])) {
            $_returnResult['name'] = 'Alexa';
            $_returnResult['url'] = 'http://www.alexa.com';
            $_returnResult['query'] = $_query['q'];
            $_returnResult['_fullURL'] = $_fullURL;
        } else {
            return false;
        }

        return $_returnResult;
    }

    /**
     * Prints an image depending upon size specified best way to communicate after JS has been loaded
     *
     * @author Varun Shoor
     * @param int $_type The Image Width Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EchoGIFImage($_type)
    {
        header('Content-Type: image/gif');
        $_type++;

        switch ($_type) {
            case '1':
                echo base64_decode('R0lGODlhAQAEAIAAAP/a2gAAACH5BAAAAAAALAAAAAABAAQAAAIChFEAOw==');
                break;
            case '2':
                echo base64_decode('R0lGODlhAgAEAIAAAKEGBgAAACH5BAAAAAAALAAAAAACAAQAAAIDhG8FADs=');
                break;
            case '3':
                echo base64_decode('R0lGODlhAwAEAIAAAI5wcAAAACH5BAAAAAAALAAAAAADAAQAAAIDhI9WADs=');
                break;
            case '4':
                echo base64_decode('R0lGODlhBAAEAIAAAH4/PwAAACH5BAAAAAAALAAAAAAEAAQAAAIEhI8JBQA7');
                break;
        }

        return true;
    }

    /**
     * Resets the Visitor's Proactive Status
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function ResetVisitorStatus()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_queryResult = $this->Database->AutoExecute(TABLE_PREFIX . 'sessions', array('status' => '0'), 'UPDATE', "sessionid = '" . $this->Database->Escape($this->GetSessionID()) . "'");

        if (!$_queryResult) {
            return false;
        }

        return true;
    }

    /**
     * Loads the Visitor Footprint Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function LoadVisitor()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        } else if (count($this->GetFootprintData())) {
            return false;
        }

        if (!$this->GetPageHash()) {
            $_footprintData = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "visitorfootprints WHERE sessionid = '" . $this->Database->Escape($this->GetSessionID()) . "'");
        } else {
            $_footprintData = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "visitorfootprints WHERE sessionid = '" . $this->Database->Escape($this->GetSessionID()) . "' AND pagehash = '" . $this->Database->Escape($this->GetPageHash()) . "'");
        }
        if (!isset($_footprintData['sessionid']) || empty($_footprintData['sessionid'])) {
            return false;
        }

        $this->SetFootprintData($_footprintData);

        return true;
    }

    /**
     * Checks to see if the ban is a valid visitor ban type
     *
     * @author Varun Shoor
     * @param int $_banType The Ban Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidVisitorBan($_banType)
    {
        if ($_banType != self::BAN_IP && $_banType != self::BAN_CLASSC && $_banType != self::BAN_CLASSB && $_banType != self::BAN_CLASSA) {
            return false;
        }

        return true;
    }

    /**
     * Processes the given IP and returns a valid regular expression based on ban type
     *
     * @author Varun Shoor
     * @param int $_banType The Ban Type
     * @param string $_ipAddress The IP Address
     * @return array
     */
    public static function GetProcessedRegularExpression($_banType, $_ipAddress)
    {
        $_ipMatches = array();
        if (!self::IsValidVisitorBan($_banType) || !preg_match("/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/", $_ipAddress) || !preg_match('@([\d]{1,3})\.([\d]{1,3})\.([\d]{1,3})\.([\d]{1,3})@', $_ipAddress, $_ipMatches)) {
            return array($_ipAddress, false);
        }

        if ($_banType == self::BAN_IP) {
            return array($_ipAddress, false);
        } else if ($_banType == self::BAN_CLASSC) {
            return array('@' . $_ipMatches[1] . '\.' . $_ipMatches[2] . '\.' . $_ipMatches[3] . '\.' . '(?:[\d]{1,3})' . '@', true);
        } else if ($_banType == self::BAN_CLASSB) {
            return array('@' . $_ipMatches[1] . '\.' . $_ipMatches[2] . '\.' . '(?:[\d]{1,3})' . '\.' . '(?:[\d]{1,3})' . '@', true);
        } else if ($_banType == self::BAN_CLASSA) {
            return array('@' . $_ipMatches[1] . '\.' . '(?:[\d]{1,3})' . '\.' . '(?:[\d]{1,3})' . '\.' . '(?:[\d]{1,3})' . '@', true);
        }

        return array($_ipAddress, false);
    }

    /**
     * Bans the Visitor
     *
     * @author Varun Shoor
     * @param int $_banType The Ban Type
     * @param int $_staffID The Staff ID who is banning this visitor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Ban($_banType, $_staffID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!self::IsValidVisitorBan($_banType)) {
            throw new SWIFT_Visitor_Exception(SWIFT_INVALIDDATA);
        }

        $this->LoadVisitor();
        $_footprintData = $this->GetFootprintData();
        if (!isset($_footprintData['sessionid']) || empty($_footprintData['sessionid'])) {
            return false;
        }

        $_ipAddressProcessed = $_isRegex = false;

        $_visitorSession = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "sessions WHERE sessionid = '" . $this->Database->Escape($this->GetSessionID()) . "'");
        if (is_array($_visitorSession) && !empty($_visitorSession['sessionid']) && !empty($_visitorSession['ipaddress'])) {
            $_banProcessedData = self::GetProcessedRegularExpression($_banType, $_visitorSession['ipaddress']);
            $_ipAddressProcessed = $_banProcessedData[0];
            $_isRegex = $_banProcessedData[1];

            SWIFT_VisitorBan::Insert($_ipAddressProcessed, $_isRegex, $_staffID);

            // Delete the data too
            $this->DeleteVisitorFootprints();

            return true;
        }

        return false;
    }

    /**
     * Deletes the visitor footprints based on sessionid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function DeleteVisitorFootprints()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT_Session::DeleteList(array($this->GetSessionID()));

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitorfootprints WHERE sessionid = '" . $this->Database->Escape($this->GetSessionID()) . "'");

        return true;
    }

    /**
     * Gets the complete online status map with skills & department ids
     *
     * @author Varun Shoor
     * @return array|bool (onlineStaffIDList, onlineDepartmentIDList, onlineSkillIDList) on Success, "false" otherwise
     */
    public static function GetStaffOnlineStatusMap()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (_is_array(self::$_onlineStatusMapCache)) {
            return self::$_onlineStatusMapCache;
        }

        $_skillsCacheContainer = $_SWIFT->Cache->Get('skillscache');

        $_staffIDList = $_staffGroupIDList = $_statusMap = $_chatSkillIDList = $_skillsCache = array();
        if (_is_array($_skillsCacheContainer)) {
            $_skillsCache = $_skillsCacheContainer;
        }

        $_SWIFT->Database->Query("SELECT staff.staffid, staff.groupassigns, staffgroup.staffgroupid, sessions.status FROM `" . TABLE_PREFIX . "sessions` AS sessions
            LEFT JOIN `" . TABLE_PREFIX . "staff` AS staff ON (sessions.typeid = staff.staffid)
            LEFT JOIN `" . TABLE_PREFIX . "staffgroup` AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)
            WHERE sessions.sessiontype IN('" . SWIFT_Interface::INTERFACE_WINAPP . "') and sessions.lastactivity >= '" . (DATENOW - 180) . "'");

        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record["status"] == SWIFT_Session::STATUS_ONLINE) {
                // Build a list of online staff members
                $_staffIDList[] = $_SWIFT->Database->Record["staffid"];

                foreach ($_skillsCacheContainer as $key => $val) {
                    if (in_array($_SWIFT->Database->Record['staffid'], $val['links']) && !in_array($val['chatskillid'], $_chatSkillIDList)) {
                        $_chatSkillIDList[] = $val['chatskillid'];
                    }
                }

                if ($_SWIFT->Database->Record["groupassigns"] == 1) {
                    $_staffGroupIDList[] = $_SWIFT->Database->Record["staffgroupid"];
                }
            }
        }

        if (!_is_array($_staffIDList)) {
            return false;
        }

        $_availableDeptartmentIDList = self::GetDepartmentsFromStaffIDs($_staffIDList, $_staffGroupIDList);

        $_statusMap = array($_staffIDList, $_availableDeptartmentIDList, $_chatSkillIDList);

        self::$_onlineStatusMapCache = $_statusMap;

        return $_statusMap;
    }

    /**
     * Returns a status code depending upon the status of staff visitor monitor (online, offline, away, back in 5)
     *
     * @author Varun Shoor
     * @param array $_filterDepartmentIDList The Department ID List to filter results on
     * @return int
     */
    public static function GetStaffOnlineStatus($_filterDepartmentIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffIDList = $_staffGroupIDList = $_awayStaffIDList = $_awayGroupIDList = $_backStaffIDList = $_backGroupIDList = array();
        $_SWIFT->Database->Query("SELECT staff.staffid, staff.groupassigns, staffgroup.staffgroupid, sessions.status FROM `" . TABLE_PREFIX . "sessions` AS sessions
            LEFT JOIN `" . TABLE_PREFIX . "staff` AS staff ON (sessions.typeid = staff.staffid)
            LEFT JOIN `" . TABLE_PREFIX . "staffgroup` AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)
            WHERE sessions.sessiontype IN ('" . SWIFT_Interface::INTERFACE_WINAPP . "') and sessions.lastactivity >= '" . (DATENOW - 180) . "';");

        // Iterate all logged-in staff members and sort them into 3 lists:
        // - online
        // - away
        // - back in 5
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record["status"] == SWIFT_Session::STATUS_ONLINE) {
                // Build a list of online staff members
                $_staffIDList[] = $_SWIFT->Database->Record["staffid"];

                if ($_SWIFT->Database->Record["groupassigns"] == 1) {
                    $_staffGroupIDList[] = $_SWIFT->Database->Record["staffgroupid"];
                }
            } else if ($_SWIFT->Database->Record["status"] == SWIFT_Session::STATUS_AWAY || $_SWIFT->Database->Record["status"] == SWIFT_Session::STATUS_AUTOAWAY || $_SWIFT->Database->Record["status"] == SWIFT_Session::STATUS_BUSY) {
                // Same for away staff members
                $_awayStaffIDList[] = $_SWIFT->Database->Record["staffid"];

                if ($_SWIFT->Database->Record["groupassigns"] == 1) {
                    $_awayGroupIDList[] = $_SWIFT->Database->Record["staffgroupid"];
                }
            } else if ($_SWIFT->Database->Record["status"] == SWIFT_Session::STATUS_BACK) {
                // And "back in 5" staff members
                $_backStaffIDList[] = $_SWIFT->Database->Record["staffid"];

                if ($_SWIFT->Database->Record["groupassigns"] == 1) {
                    $_backGroupIDList[] = $_SWIFT->Database->Record["staffgroupid"];
                }
            }
        }

        // If no department has been specified, then check all departments.
        // See later in the following if-statement block for per-department handling.
        if (!_is_array($_filterDepartmentIDList)) {
            // Pull a list of public departments available for these staff members and staff groups.
            $_arrayStaff = array_merge($_staffIDList, $_awayStaffIDList, $_backStaffIDList);
            $_arrayGroups = array_merge($_staffGroupIDList, $_awayStaffIDList, $_backGroupIDList);
            $_availableDeptartments = self::GetDepartmentsFromStaffIDs($_arrayStaff, $_arrayGroups);

            // No public departments are online.
            if (empty($_availableDeptartments)) {
                return SWIFT_Session::STATUS_OFFLINE;
            }

            // We can just match on any logged-in staff member.
            if (count($_staffIDList) > 0) {
                return SWIFT_Session::STATUS_ONLINE;   // At least one staff member is online
            } else if (count($_awayStaffIDList) > 0) {
                return SWIFT_Session::STATUS_AWAY;     // At least one staff member is away
            } else if (count($_backStaffIDList) > 0) {
                return SWIFT_Session::STATUS_BACK;     // At least one staff member is "back in 5"
            }

            // If this falls through, it'll return SWIFT_Session::STATUS_OFFLINE.
        } else {
            // A specific department is in question; check staff and group assignments for the staff members that are currently
            // logged in and see if any of them match the department.

            // First, try to match an online staff member
            if (count($_staffIDList) > 0) {
                $_onlineDeptartments = self::GetDepartmentsFromStaffIDs($_staffIDList, $_staffGroupIDList);

                foreach ($_onlineDeptartments as $_onlineDepartmentID) {
                    if (in_array($_onlineDepartmentID, $_filterDepartmentIDList)) {
                        // We have an ONLINE staff member for this department.
                        return SWIFT_Session::STATUS_ONLINE;
                    }
                }
            }

            // If none are away, perhaps they are "back in 5?"
            if (count($_backStaffIDList) > 0) {
                $_backDepartments = self::GetDepartmentsFromStaffIDs($_backStaffIDList, $_backGroupIDList);

                foreach ($_backDepartments as $_backDepartmentID) {
                    if (in_array($_backDepartmentID, $_filterDepartmentIDList)) {
                        // We have a BACK IN 5 staff member for this department.
                        return SWIFT_Session::STATUS_BACK;
                    }
                }
            }

            // If no staff members are online, maybe they are away.
            if (count($_awayStaffIDList) > 0) {
                $_awayDepartments = self::GetDepartmentsFromStaffIDs($_awayStaffIDList, $_awayGroupIDList);

                foreach ($_awayDepartments as $_awayDepartmentID) {
                    if (in_array($_awayDepartmentID, $_filterDepartmentIDList)) {
                        // We have an AWAY staff member for this department.
                        return SWIFT_Session::STATUS_AWAY;
                    }
                }
            }
        }

        // If execution reaches here, we've exhausted all leads.  The staff members must all be offline.
        return SWIFT_Session::STATUS_OFFLINE;
    }

    /**
     * Returns a phone status code depending upon the status of staff visitor monitor (online, offline, private, dnd)
     *
     * @author Varun Shoor
     * @param array $_filterDepartmentIDList The Department ID List to filter results on
     * @return int
     */
    public static function GetStaffPhoneStatus($_filterDepartmentIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffIDList = $_staffGroupIDList = $_awayStaffIDList = $_awayGroupIDList = array();
        $_SWIFT->Database->Query("SELECT staff.staffid, staff.groupassigns, staffgroup.staffgroupid, sessions.phonestatus FROM `" . TABLE_PREFIX . "sessions` AS sessions LEFT JOIN `" . TABLE_PREFIX . "staff` AS staff ON (sessions.typeid = staff.staffid) LEFT JOIN `" . TABLE_PREFIX . "staffgroup` AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid) WHERE sessions.sessiontype IN('" . SWIFT_Interface::INTERFACE_WINAPP . "') and sessions.lastactivity >= '" . (DATENOW - 180) . "';");

        // Iterate all logged-in staff members and sort them into 2 lists:
        // - online
        // - away
        while ($_SWIFT->Database->NextRecord()) {
            if ($_SWIFT->Database->Record["phonestatus"] == SWIFT_Session::PHONESTATUS_AVAILABLE) {
                // Build a list of online staff members
                $_staffIDList[] = $_SWIFT->Database->Record["staffid"];

                if ($_SWIFT->Database->Record["groupassigns"] == 1) {
                    $_staffGroupIDList[] = $_SWIFT->Database->Record["staffgroupid"];
                }
            } else if ($_SWIFT->Database->Record["phonestatus"] == SWIFT_Session::PHONESTATUS_DND) {
                // Same for away staff members
                $_awayStaffIDList[] = $_SWIFT->Database->Record["staffid"];

                if ($_SWIFT->Database->Record["groupassigns"] == 1) {
                    $_awayGroupIDList[] = $_SWIFT->Database->Record["staffgroupid"];
                }
            }
        }

        // If no department has been specified, then check all departments.
        // See later in the following if-statement block for per-department handling.
        if (!_is_array($_filterDepartmentIDList)) {
            // Pull a list of public departments available for these staff members and staff groups.
            $_arrayStaff = array_merge($_staffIDList, $_awayStaffIDList);
            $_arrayGroups = array_merge($_staffGroupIDList, $_awayStaffIDList);
            $_availableDeptartments = self::GetDepartmentsFromStaffIDs($_arrayStaff, $_arrayGroups);

            // No public departments are online.
            if (empty($_availableDeptartments)) {
                return SWIFT_Session::STATUS_OFFLINE;
            }

            // We can just match on any logged-in staff member.
            if (count($_staffIDList) > 0) {
                return SWIFT_Session::STATUS_ONLINE;   // At least one staff member is online
            } else if (count($_awayStaffIDList) > 0) {
                return SWIFT_Session::STATUS_AWAY;     // At least one staff member is away
            }

            // If this falls through, it'll return SWIFT_Session::STATUS_OFFLINE.
        } else {
            // A specific department is in question; check staff and group assignments for the staff members that are currently
            // logged in and see if any of them match the department.

            // First, try to match an online staff member
            if (count($_staffIDList) > 0) {
                $_onlineDeptartments = self::GetDepartmentsFromStaffIDs($_staffIDList, $_staffGroupIDList);

                foreach ($_onlineDeptartments as $_onlineDepartmentID) {
                    if (in_array($_onlineDepartmentID, $_filterDepartmentIDList)) {
                        // We have an ONLINE staff member for this department.
                        return SWIFT_Session::STATUS_ONLINE;
                    }
                }
            }

            // If no staff members are online, maybe they are away.
            if (count($_awayStaffIDList) > 0) {
                $_awayDepartments = self::GetDepartmentsFromStaffIDs($_awayStaffIDList, $_awayGroupIDList);

                foreach ($_awayDepartments as $_awayDepartmentID) {
                    if (in_array($_awayDepartmentID, $_filterDepartmentIDList)) {
                        // We have an AWAY staff member for this department.
                        return SWIFT_Session::STATUS_AWAY;
                    }
                }
            }
        }

        // If execution reaches here, we've exhausted all leads.  The staff members must all be offline.
        return SWIFT_Session::STATUS_OFFLINE;
    }

    /**
     * Retrieves the Departments Assigned to a given staff member or group
     *
     * @author Varun Shoor
     * @param array $_staffIDList The Staff ID List
     * @param array $_groupIDList The Group ID List
     * @return array
     */
    static private function GetDepartmentsFromStaffIDs($_staffIDList, $_groupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->Query("SELECT departments.departmentid FROM " . TABLE_PREFIX . "departments AS departments
            LEFT JOIN " . TABLE_PREFIX . "staffassigns AS staffassigns ON (staffassigns.departmentid = departments.departmentid)
            LEFT JOIN " . TABLE_PREFIX . "groupassigns AS groupassigns ON (groupassigns.departmentid = departments.departmentid)
            WHERE (staffassigns.staffid IN (" . BuildIN($_staffIDList) . ") OR groupassigns.staffgroupid IN(" . BuildIN($_groupIDList) . "))
                AND (departments.departmenttype = 'public' AND departments.departmentapp = '" . APP_LIVECHAT . "')
                GROUP BY departments.departmentid");

        $_returnValue = array();

        while ($_SWIFT->Database->NextRecord()) {
            $_returnValue[] = $_SWIFT->Database->Record['departmentid'];
        }

        return $_returnValue;
    }

    /**
     * Checks for banned status for this specific visitor
     *
     * @author Varun Shoor
     * @param bool $_overrideCookie If set to true, will override the cookie settings
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsBanned($_overrideCookie = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Cookie->Parse('visitorsession');
        $_banCookieContainer = $_SWIFT->Cookie->GetVariable('visitorsession', 'isbanned');

        $_isBanned = false;

        if ($_banCookieContainer == "" || $_banCookieContainer == "0" || $_overrideCookie == true) {
            // it seems like we never did check this visitor to see if hes banned check now
            if (SWIFT_VisitorBan::IsBanned(SWIFT::Get('IP'))) {
                // He is banned!
                $_SWIFT->Cookie->AddVariable('visitorsession', 'isbanned', '1');

                $_isBanned = true;
            } else {
                $_SWIFT->Cookie->AddVariable('visitorsession', 'isbanned', '0');

                $_isBanned = false;
            }

            $_SWIFT->Cookie->Rebuild('visitorsession');

        } else if ($_banCookieContainer == "1") {
            $_isBanned = true;
        }

        return $_isBanned;
    }

    /**
     * Retrieve the last visit for this visitor
     *
     * @author Varun Shoor
     * @return mixed "lastvisit" (INT) on Success, "false" otherwise
     */
    public static function GetLastVisit()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Cookie->Parse('visitor');

        if ($_SWIFT->Cookie->GetVariable('visitor', 'lastvisit')) {
            return $_SWIFT->Cookie->GetVariable('visitor', 'lastvisit');
        }

        $_SWIFT->Cookie->AddVariable('visitor', 'lastvisit', DATENOW);
        $_SWIFT->Cookie->Rebuild('visitor', true);

        return false;
    }

    /**
     * Engage a visitor with inline or alert system
     *
     * @author Varun Shoor
     * @param string $_proactiveType The type of engagement
     * @param int $_staffID The Staff ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function Engage($_proactiveType, $_staffID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        } else if ($_proactiveType != self::PROACTIVE_INLINE && $_proactiveType != self::PROACTIVE_ENGAGE) {
            throw new SWIFT_Visitor_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_SessionManagerObject = new SWIFT_SessionManager($this->GetSessionID());
        if ($_SWIFT_SessionManagerObject instanceof SWIFT_SessionManager && $_SWIFT_SessionManagerObject->GetIsClassLoaded()) {
            $_SWIFT_SessionManagerObject->UpdateSessionStatus($_proactiveType, self::PROACTIVERESULT_DISPATCHED);
        }

        $this->Database->Replace(TABLE_PREFIX . 'visitorpulls', array('visitorsessionid' => $this->GetSessionID(), 'staffid' => $_staffID, 'dateline' => DATENOW), array('visitorsessionid'));

        return true;
    }

    /**
     * Deletes the Engage Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function FlushEngageData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_SessionManagerObject = new SWIFT_SessionManager($this->GetSessionID());
        if ($_SWIFT_SessionManagerObject instanceof SWIFT_SessionManager && $_SWIFT_SessionManagerObject->GetIsClassLoaded()) {
            $_SWIFT_SessionManagerObject->UpdateSessionStatus(self::PROACTIVE_NONE, self::PROACTIVERESULT_DENIED);
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitorpulls WHERE visitorsessionid = '" . $this->Database->Escape($this->GetSessionID()) . "'");

        return true;
    }

    /**
     * Accepts the Engage Confirmation
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function AcceptedProactiveChat()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_SessionManagerObject = new SWIFT_SessionManager($this->GetSessionID());
        if ($_SWIFT_SessionManagerObject instanceof SWIFT_SessionManager && $_SWIFT_SessionManagerObject->GetIsClassLoaded()) {
            $_SWIFT_SessionManagerObject->UpdateSessionStatus(self::PROACTIVE_NONE, self::PROACTIVERESULT_ACCEPTED);
        }

        return true;
    }

    /**
     * Adds a note for this visitor
     *
     * @author Varun Shoor
     * @param string $_noteContents The Note Contents
     * @param int $_staffID The Staff ID
     * @return bool "true" on Success, "false" otherwise
     */
    public function AddNote($_noteContents, $_staffID)
    {

        return true;
    }

    /**
     * Adds a skill for this visitor, will be used to intelligently decide round robin
     *
     * @author Varun Shoor
     * @param int $_visitorRuleID The Visitor Rule ID
     * @param int $_visitorSkillID The Visitor Skill ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function AddSkill($_visitorRuleID, $_visitorSkillID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_visitorSkillID) || empty($_visitorRuleID)) {
            throw new SWIFT_Visitor_Exception(SWIFT_INVALIDDATA);
        }

        SWIFT_VisitorData::Insert($this->GetSessionID(), $_visitorRuleID, SWIFT_VisitorData::DATATYPE_SKILL, $_visitorSkillID, $_visitorSkillID);

        return true;
    }

    /**
     * Adds a variable for this visitor, shows up in the information pane
     *
     * @author Varun Shoor
     * @param int $_visitorRuleID The Visitor Rule ID
     * @param string $_key The Variable Key
     * @param string $_value The Variable Value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function AddVariable($_visitorRuleID, $_key, $_value)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_visitorRuleID)) {
            throw new SWIFT_Visitor_Exception(SWIFT_INVALIDDATA);
        }

        SWIFT_VisitorData::Insert($this->GetSessionID(), $_visitorRuleID, SWIFT_VisitorData::DATATYPE_VARIABLE, $_key, $_value);

        return true;
    }

    /**
     * Adds an alert for staff members regarding this visitor
     *
     * @author Varun Shoor
     * @param int $_visitorRuleID The Visitor Rule ID
     * @param string $_title The Alert Title
     * @param string $_value The Alert value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function AddAlert($_visitorRuleID, $_title, $_value)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_visitorRuleID)) {
            throw new SWIFT_Visitor_Exception(SWIFT_INVALIDDATA);
        }

        SWIFT_VisitorData::Insert($this->GetSessionID(), $_visitorRuleID, SWIFT_VisitorData::DATATYPE_ALERT, $_title, $_value);

        return true;
    }

    /**
     * Sets the Visitor group
     *
     * @author Varun Shoor
     * @param int $_visitorGroupID The Visitor Group ID
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded or If Invalid Data is Provided
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetGroup($_visitorGroupID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_visitorGroupID)) {
            throw new SWIFT_Visitor_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_SessionManagerObject = new SWIFT_SessionManager($this->GetSessionID());
        if (!$_SWIFT_SessionManagerObject instanceof SWIFT_SessionManager || !$_SWIFT_SessionManagerObject->GetIsClassLoaded()) {
            return false;
        }

        $_SWIFT_SessionManagerObject->SetVisitorGroup($_visitorGroupID);

        return true;
    }

    /**
     * Sets the Visitor Department
     *
     * @author Varun Shoor
     * @param int $_departmentID The Visitor Department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetDepartment($_departmentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_departmentID)) {
            throw new SWIFT_Visitor_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_SessionManagerObject = new SWIFT_SessionManager($this->GetSessionID());
        if (!$_SWIFT_SessionManagerObject instanceof SWIFT_SessionManager || !$_SWIFT_SessionManagerObject->GetIsClassLoaded()) {
            return false;
        }

        $_SWIFT_SessionManagerObject->SetDepartment($_departmentID);

        return true;
    }

    /**
     * Sets the Visitor Grid Color Code
     *
     * @author Varun Shoor
     * @param string $_hexCode The Visitor Hex Color Code
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function SetColor($_hexCode)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_SessionManagerObject = new SWIFT_SessionManager($this->GetSessionID());
        if (!$_SWIFT_SessionManagerObject instanceof SWIFT_SessionManager || !$_SWIFT_SessionManagerObject->GetIsClassLoaded()) {
            return false;
        }

        $_SWIFT_SessionManagerObject->SetColor($_hexCode);

        return true;
    }

    /**
     * Get Page URL
     *
     * @author Ravi Sharma
     *
     * @return string|bool
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function GetPageURL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_visitorFootprintContainer = $this->Database->QueryFetch("SELECT pageurl FROM " . TABLE_PREFIX . "visitorfootprints
                                                                   WHERE sessionid = '" . $this->Database->Escape($this->GetSessionID()) . "'
                                                                   ORDER BY dateline DESC");

        if (!isset($_visitorFootprintContainer['pageurl']) || empty($_visitorFootprintContainer['pageurl'])) {
            return false;
        }

        return $_visitorFootprintContainer['pageurl'];
    }

    /**
     * Dispatches the Javascript Variable
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DispatchJSVariable()
    {
        $_SWIFT = SWIFT::GetInstance();

        echo '<script type="text/javascript" language="Javascript">';
        $_departments = SWIFT_Department::GetDepartmentMap(APP_LIVECHAT);
        $_index = 0;

        $_departmentsData = array();

        foreach ($_departments as $key => $val) {
            $_departmentsData[] = '"' . $_index . '": {"0": "' . (int)($val['departmentid']) . '", "1": "' . addslashes($val['title']) . '"}';
            $_index++;

            if (_is_array($val['subdepartments'])) {
                foreach ($val['subdepartments'] as $subkey => $subval) {
                    $_departmentsData[] = '"' . $_index . '": {"0": "' . (int)($subval['departmentid']) . '", "1": "   |- ' . addslashes($subval['title']) . '"}';

                    $_index++;
                }
            }
        }

        if (!_is_array($_departmentsData)) {
            $_departmentsData[] = '"0": {"0": "0", "1": "' . addslashes($_SWIFT->Language->Get('notavailable')) . '"}';
        }

        echo 'var lsdepartmentsobj = {' . implode(',', $_departmentsData) . '}';
        echo '</script>';
    }

    /**
     * Check if footprints are available for a session
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If class is not loaded
     */
    public function HasFootprints()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_visitorFootprintContainer = $this->Database->QueryFetch("SELECT COUNT(sessionid) AS totalcount FROM " . TABLE_PREFIX . "visitorfootprints WHERE sessionid = '" . $this->Database->Escape($this->GetSessionID()) . "'");

        if (isset($_visitorFootprintContainer['totalcount']) && (int)($_visitorFootprintContainer['totalcount']) > 0) {
            return true;
        }

        return false;
    }
}
