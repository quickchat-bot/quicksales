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

namespace LiveChat\Winapp;

use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffProfileImage;
use Controller_winapp;
use LiveChat\Models\Call\SWIFT_Call;
use LiveChat\Models\Chat\SWIFT_Chat;
use LiveChat\Models\Visitor\SWIFT_Visitor;
use LiveChat\Models\Visitor\SWIFT_VisitorData;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_Session;

/**
 * The Controller that Dispatches the Visitor List
 *
 * @author Varun Shoor
 */
class Controller_FetchVisitors extends Controller_winapp
{
    protected $_staffList = array();
    protected $_staffContainer = array();
    protected $_staffIDList = array();
    protected $_chatObjectIDList = array();
    protected $_chatSessionIDList = array();
    protected $_visitorContainer = array();
    protected $_visitorSessionIDList = array();
    protected $_visitorRawSessionIDList = array();
    protected $_localStaffCache = array();

    protected $_visitorCustomDataContainer = array();
    protected $_transferChatQueue = array();
    protected $_transferChatObjectIDList = array();
    protected $_transferFromChatQueue = array();
    protected $_proactiveChatQueue = array();
    protected $_proactiveChatObjectIDList = array();
    protected $_staffChatQueue = array();
    protected $_staffChatObjectIDList = array();
    protected $_visitorFootprintsContainer = array();
    protected $_sessionLastActivityContainer = array();
    protected $_pendingChatVisitorSessionIDList = array();
    protected $_pendingChatObjectList = array();
    protected $_pendingChatVisitorContainer = array();
    protected $_pendingStaffChatObjectContainer = array();
    protected $_inChatVisitorSessionIDList = array();
    protected $_inChatVisitorContainer = array();
    protected $_visitorChatContainer = array();
    protected $_queueChatObjectContainer = array();

    public $XML;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('livesupport');
    }

    /**
     * The Main Dispatcher Function
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        HeaderNoCache();

        $this->_DispatchPacket();

        return true;
    }

    /**
     * The Main Dispatcher Function
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Stream()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        HeaderNoCache();

        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);
        for ($i = 0; $i < ob_get_level(); $i++) {
            ob_end_flush();
        }
        ob_implicit_flush(1);

        for ($_ii = 0; $_ii < Controller_winapp::POLL_ITTERATIONS; $_ii++) {
            $_contents = $this->_DispatchPacket(true);

            $_contentsHash = md5($_contents);
            echo sprintf(Controller_winapp::POLL_HEADER, $_contentsHash) . SWIFT_CRLF;
            echo $_contents . SWIFT_CRLF;
            echo sprintf(Controller_winapp::POLL_FOOTER, $_contentsHash) . SWIFT_CRLF;

            // Attempt to update the Session Activity...
            $_mod = $_ii % 2;

            if ($_mod == 0) {
                $_SWIFT->Session->UpdateActivityCombined(true);
            }

            sleep(Controller_winapp::POLL_SLEEPTIME);
        }

        return true;
    }

    /**
     * Dispatch the Packet
     *
     * @author Varun Shoor
     * @param bool $_doReturnData Whether to Return Data rather than outputing it
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _DispatchPacket($_doReturnData = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->XML->BuildXML();

        $this->XML->AddParentTag('kayako_livechat');

        $_staffGroupCache = $this->Cache->Get('staffgroupcache');

        /**
         * ---------------------------------------------
         * PROCESS SESSIONS FIRST
         * ---------------------------------------------
         */
        $this->_ProcessSessionsAndStaff();


        /**
         * ---------------------------------------------
         * THEN PROCESS CHAT OBJECTS
         * ---------------------------------------------
         */
        $this->_ProcessChatObjects();

        /**
         * ---------------------------------------------
         * PROCESS VISITORS
         * ---------------------------------------------
         */
        $this->_ProcessVisitors();

        /**
         * ###############################################
         * BEGIN VISITOR XML OUTPUT
         * ###############################################
         */
        $_visitorIndex = 0;

        foreach ($this->_visitorContainer as $_key => $_val) {
            $_visitorIndex++;
            $_sessionID = $_key;
            $_visitorData = array();
            $_visitorWaitTime = 0;

            if (!isset($_val['sessionid']) || (isset($_val['notloaded']) && $_val['notloaded'] == true)) {
                continue;
            }

            $_totalFootprintCount = 0;
            if (isset($this->_visitorFootprintsContainer[$_sessionID])) {
                $_totalFootprintCount = count($this->_visitorFootprintsContainer[$_sessionID]);
            }

            $this->XML->AddParentTag('visitor');
            $this->XML->AddTag('id', $_visitorIndex, false, true);
            $this->XML->AddTag('sessionid', $_val['sessionid'], false, true);

            // Pending Chats
            if (in_array($_val['sessionid'], $this->_pendingChatVisitorSessionIDList)) {
                $_visitorData['incomingchat'] = '1';
                $_visitorData['inchat'] = '0';
                $_visitorData['state'] = 2;
                $_visitorData['chatterid'] = $this->_pendingChatVisitorContainer[$_val['sessionid']]['userfullname'];
                $_visitorData['chatsessionid'] = $this->_pendingChatVisitorContainer[$_val['sessionid']]['chatsessionid'];
                $_visitorData['department'] = $this->_pendingChatVisitorContainer[$_val['sessionid']]['departmenttitle'];

                $_visitorWaitTime = round(time() - $this->_pendingChatVisitorContainer[$_val['sessionid']]['dateline']);

                // Active Chats
            } else if (in_array($_val['sessionid'], $this->_inChatVisitorSessionIDList)) {
                $_visitorData['incomingchat'] = '0';
                $_visitorData['inchat'] = '1';
                $_visitorData['state'] = 3;
                $_visitorData['chatterid'] = $this->_inChatVisitorContainer[$_val['sessionid']]['userfullname'];
                $_visitorData['chatsessionid'] = $this->_inChatVisitorContainer[$_val['sessionid']]['chatsessionid'];
                $_visitorData['department'] = $this->_inChatVisitorContainer[$_val['sessionid']]['departmenttitle'];
                $_visitorData['staffname'] = $this->_inChatVisitorContainer[$_val['sessionid']]['staffname'];

                // Default
            } else {

                // Override the Chatter ID (in case of old chat objects)
                if (isset($_val['chatterid'])) {
                    $_visitorData['chatterid'] = '';
                } else {
                    $_visitorData['chatterid'] = '';
                }

                $_visitorData['incomingchat'] = '0';
                $_visitorData['inchat'] = '0';
                $_visitorData['state'] = 1;
                $_visitorData['chatsessionid'] = '';
                $_visitorData['department'] = '';
            }

            $this->XML->AddTag('incomingchat', $_visitorData['incomingchat'], false, true);
            $this->XML->AddTag('inchat', $_visitorData['inchat'], false, true);
            $this->XML->AddTag('department', $_visitorData['department'], false, true);

            if (isset($_visitorData['staffname'])) {
                $this->XML->AddTag('chatstaffname', $_visitorData['staffname'], false, true);
            } else {
                $this->XML->AddTag('chatstaffname', '', false, true);
            }

            $this->XML->AddTag('waittime', $_visitorWaitTime, false, true);

            if (isset($_val['lastvisit']) && !empty($_val['lastvisit'])) {
                $this->XML->AddTag('lastvisit', (int)($_val['lastvisit']), false, true);
            } else {
                $this->XML->AddTag('lastvisit', '-1', false, true);
            }

            if (isset($_val['lastchat']) && !empty($_val['lastchat'])) {
                $this->XML->AddTag('lastchat', (int)($_val['lastchat']), false, true);
            } else {
                $this->XML->AddTag('lastchat', '-1', false, true);
            }

            if (isset($_val['callstate']) && $_val['callstate'] == '1') {
                $_visitorData['state'] = '8';
            }

            $this->XML->AddTag('state', $_visitorData['state'], false, true);

            if (isset($_val['currentpage']) && isset($_val['pagetitle']) && isset($_val['referrer'])) {
                $this->XML->AddTag('currentpage', $_val['currentpage'], false, false);
                $this->XML->AddTag('pagetitle', $_val['pagetitle'], false, false);
                $this->XML->AddTag('referrer', $_val['referrer'], false, false);
                $this->XML->AddTag('noofpages', $_totalFootprintCount, false, true);
            }

            $this->XML->AddTag('chatterid', $_visitorData['chatterid'], false, false);

            if (isset($_val['ipaddress']) && isset($_val['hostname'])) {
                $this->XML->AddTag('ipaddress', $_val['ipaddress'], false, false);
            }

            if (isset($_val['hostname'])) {
                $this->XML->AddTag('hostname', $_val['hostname'], false, false);
            }

            if (isset($_val['countryname']) && isset($_val['countrycode'])) {
                $this->XML->AddTag('country', trim($_val['countryname']), false, true);
                $this->XML->AddTag('countrycode', $_val['countrycode'], false, true);
            }

            if (isset($_val['browsername']) && isset($_val['browserversion']) && isset($_val['browsercode']) && isset($_val['resolution']) && isset($_val['platform']) && isset($_val['operatingsys'])) {
                $this->XML->AddTag('browser', $_val['browsername'], false, false);
                $this->XML->AddTag('browserversion', $_val['browserversion'], false, false);
                $this->XML->AddTag('browsercode', $_val['browsercode'], false, false);
                $this->XML->AddTag('resolution', $_val['resolution'], false, false);
                $this->XML->AddTag('platform', $_val['platform'], false, false);
                $this->XML->AddTag('operatingsys', $_val['operatingsys'], false, false);
            }

            if (isset($_val['searchenginename'])) {
                $this->XML->AddTag('searchenginecode', $_val['searchenginename'], false, false);
            } else {
                $this->XML->AddTag('searchenginecode', '', false, false);
            }

            if (isset($_val['searchstring'])) {
                $this->XML->AddTag('searchstring', $_val['searchstring'], false, false);
            } else {
                $this->XML->AddTag('searchstring', '', false, false);
            }

            if (isset($_val['min']) && isset($_val['max'])) {
                $this->XML->AddTag('min', $_val['min'], false, true);
                $this->XML->AddTag('max', $_val['max'], false, true);
                $this->XML->AddTag('diff', $_val['max'] - $_val['min'], false, true);
            }

            if (isset($_val['rowbgcolor']) && !empty($_val['rowbgcolor'])) {
                $rgb = HexToRGB(str_replace('#', '', $_val['rowbgcolor']));
                $this->XML->AddTag('rowbgcolor', $rgb['red'] . ',' . $rgb['green'] . ',' . $rgb['blue'], array('hex' => $_val['rowbgcolor']), true);
            } else {
                $this->XML->AddTag('rowbgcolor', '', false, true);
            }

            if (isset($_val['rowfrcolor']) && !empty($_val['rowfrcolor'])) {
                $_rgbContainer = HexToRGB(str_replace('#', '', $_val['rowfrcolor']));
                $this->XML->AddTag('rowfrcolor', $_rgbContainer['red'] . ',' . $_rgbContainer['green'] . ',' . $_rgbContainer['blue'], array('hex' => $_val['rowfrcolor']), true);
            } else {
                $this->XML->AddTag('rowfrcolor', '', false, true);
            }

            $this->XML->AddTag('isspider', '0', false, true);

            if (isset($_val['appversion']) && isset($_val['colordepth'])) {
                $this->XML->AddTag('appversion', $_val['appversion'], false, false);
                $this->XML->AddTag('colordepth', $_val['colordepth'], false, false);
            }

            // We add this again at end because the XML Pointer in winapp will have to be resetted
            $this->XML->AddTag('chatsessionid', $_visitorData['chatsessionid'], false, false);

            if (isset($_val['hasnote'])) {
                $this->XML->AddTag('hasnote', $_val['hasnote'], false, true);
            }

            if (isset($_val['group'])) {
                $this->XML->AddTag('group', $_val['group'], array('color' => $_val['groupcolor']), true);
            } else {
                $this->XML->AddTag('group', '', false, true);
            }

            if (isset($_val['proactiveresult'])) {
                $this->XML->AddTag('proactiveresult', $_val['proactiveresult'], false, true);
            }

            if (isset($this->_visitorChatContainer[$_val['sessionid']]) && _is_array($this->_visitorChatContainer[$_val['sessionid']])) {
                foreach ($this->_visitorChatContainer[$_val['sessionid']] as $_visitorChatVal) {
                    if ($_visitorChatVal['chattype'] == SWIFT_Chat::CHATTYPE_CLIENT && !in_array($_visitorChatVal['departmentid'], $_SWIFT->Staff->GetAssignedDepartments(APP_LIVECHAT))) {
                        continue;
                    }

                    $this->XML->AddParentTag('chat', array('status' => $_visitorChatVal['chatstatus']));
                    $this->XML->AddTag('chatobjectid', $_visitorChatVal['chatobjectid'], false, true);
                    $this->XML->AddTag('chatsessionid', $_visitorChatVal['chatsessionid'], false, true);
                    $this->XML->AddTag('fullname', $_visitorChatVal['userfullname'], false, false);
                    $this->XML->AddTag('email', $_visitorChatVal['useremail'], false, false);
                    $this->XML->AddTag('phonenumber', $_visitorChatVal['phonenumber'], false, false);
                    $this->XML->AddTag('chattype', $_visitorChatVal['chattype'], false, true);
                    $this->XML->AddTag('departmentid', $_visitorChatVal['departmentid'], false, true);
                    $this->XML->AddTag('departmenttitle', $_visitorChatVal['departmenttitle'], false, false);
                    $this->XML->AddTag('staffid', $_visitorChatVal['staffid'], false, true);
                    $this->XML->AddTag('subject', $_visitorChatVal['subject'], false, false);
                    $this->XML->AddTag('iscall', $_visitorChatVal['isphone'], false, false);

                    // Calculate duration
                    $_startTimeSeconds = time() - $_visitorChatVal['dateline'];
                    $_durationSeconds = $_startTimeSeconds - (int)($_visitorChatVal['waittime']);
                    $this->XML->AddTag('duration', $_durationSeconds, false, false);

                    $this->XML->EndTag('chat');
                }
            }

            $this->XML->AddParentTag('footprints');
            $_footprintIndex = 0;
            $_footprintDateThreshold = time() - 60;

            if (isset($this->_visitorFootprintsContainer[$_val['sessionid']]) && _is_array($this->_visitorFootprintsContainer[$_val['sessionid']])) {
                foreach ($this->_visitorFootprintsContainer[$_val['sessionid']] as $_visitorFootprintVal) {
                    $_footprintIndex++;

                    $_isActive = false;
                    if ($_visitorFootprintVal['lastactivity'] > $_footprintDateThreshold) {
                        $_isActive = true;
                    }

                    $this->XML->AddTag('footprint', htmlspecialchars($_visitorFootprintVal['pageurl']), array('id' => $_footprintIndex, 'pagetitle' => addslashes(IIF(!trim($_visitorFootprintVal['pagetitle']), 'None', $_visitorFootprintVal['pagetitle'])), 'referrer' => $_visitorFootprintVal['referrer'], 'lastactivity' => $_visitorFootprintVal['lastactivity'], 'dateline' => $_visitorFootprintVal['dateline'], 'isactive' => (int)($_isActive)), false);
                }
            }
            $this->XML->EndTag('footprints');

            $this->XML->AddParentTag('history');
            $this->XML->EndTag('history');

            $this->XML->AddParentTag('notes');
            $this->XML->EndTag('notes');

            $this->XML->AddParentTag('geoip');
            if (isset($_val['geoipisp'])) $this->XML->AddTag('isp', $_val['geoipisp'], false, true);
            if (isset($_val['geoiporganization'])) $this->XML->AddTag('organization', $_val['geoiporganization'], false, true);
            if (isset($_val['geoipnetspeed'])) $this->XML->AddTag('netspeed', $_val['geoipnetspeed'], false, true);
            if (isset($_val['geoipcountry'])) $this->XML->AddTag('country', $_val['geoipcountry'], false, true);
            if (isset($_val['geoipcountrydesc'])) $this->XML->AddTag('countrydesc', $_val['geoipcountrydesc'], false, true);
            if (isset($_val['geoipregion'])) $this->XML->AddTag('region', $_val['geoipregion'], false, true);
            if (isset($_val['geoipcity'])) $this->XML->AddTag('city', $_val['geoipcity'], false, true);
            if (isset($_val['geoippostalcode'])) $this->XML->AddTag('postalcode', $_val['geoippostalcode'], false, true);
            if (isset($_val['geoiplatitude'])) $this->XML->AddTag('latitude', $_val['geoiplatitude'], false, true);
            if (isset($_val['geoiplongitude'])) $this->XML->AddTag('longitude', $_val['geoiplongitude'], false, true);
            if (isset($_val['geoipmetrocode'])) $this->XML->AddTag('metrocode', $_val['geoipmetrocode'], false, true);
            if (isset($_val['geoipareacode'])) $this->XML->AddTag('areacode', $_val['geoipareacode'], false, true);
            if (isset($_val['geoiptimezone'])) $this->XML->AddTag('timezone', $_val['geoiptimezone'], false, true);
            $this->XML->EndTag('geoip');

            $this->XML->AddParentTag('variables');
            if (isset($_val['sessionid']) && isset($this->_visitorCustomDataContainer[$_val['sessionid']][SWIFT_VisitorData::DATATYPE_VARIABLE]) && _is_array($this->_visitorCustomDataContainer[$_val['sessionid']][SWIFT_VisitorData::DATATYPE_VARIABLE])) {
                foreach ($this->_visitorCustomDataContainer[$_val['sessionid']][SWIFT_VisitorData::DATATYPE_VARIABLE] as $_variableVal) {
                    $this->XML->AddTag('key', $_variableVal['datavalue'], array('title' => $_variableVal['datakey']));
                }
            }
            $this->XML->EndTag('variables');

            $this->XML->AddParentTag('alerts', array('ttl' => $this->Settings->Get('livesupport_alertttl')));
            if (isset($this->_visitorCustomDataContainer[$_val['sessionid']][SWIFT_VisitorData::DATATYPE_ALERT]) && _is_array($this->_visitorCustomDataContainer[$_val['sessionid']][SWIFT_VisitorData::DATATYPE_ALERT])) {
                foreach ($this->_visitorCustomDataContainer[$_val['sessionid']][SWIFT_VisitorData::DATATYPE_ALERT] as $_alertVal) {
                    $this->XML->AddTag('alert', $_alertVal['datavalue'], array('id' => $_alertVal['visitordataid'], 'title' => $_alertVal['datakey']));
                }
            }
            $this->XML->EndTag('alerts');

            $this->XML->EndTag('visitor');
        }

        unset($this->_visitorChatContainer);
        unset($this->_visitorContainer);
        unset($this->_inChatVisitorContainer);
        unset($this->_inChatVisitorSessionIDList);
        unset($this->_pendingChatVisitorContainer);
        unset($this->_pendingChatVisitorSessionIDList);
        unset($this->_visitorFootprintsContainer);
        unset($this->_visitorCustomDataContainer);
        unset($this->_visitorSessionIDList);
        unset($this->_visitorRawSessionIDList);
        /**
         * ###############################################
         * END VISITOR XML OUTPUT
         * ###############################################
         */


        /**
         * ###############################################
         * BEGIN STAFF XML OUTPUT
         * ###############################################
         */
        $_dispatchAvatarXML = false;

        $_avatarThreshold = $_SWIFT->Session->GetProperty('lastactivitycustom');
        if (empty($_avatarThreshold)) {
            $_avatarThreshold = $_SWIFT->Session->GetProperty('lastactivity');
        }

        $this->XML->AddParentTag('stafflist');
        if (_is_array($_staffGroupCache)) {
            foreach ($_staffGroupCache as $_staffGroupVal) {
                $this->XML->AddParentTag('staffgroup', array('staffgroupid' => $_staffGroupVal['staffgroupid'], 'title' => $_staffGroupVal['title'], 'isadmin' => $_staffGroupVal['isadmin']));
                if (isset($this->_staffList[$_staffGroupVal['staffgroupid']]) && _is_array($this->_staffList[$_staffGroupVal['staffgroupid']])) {
                    foreach ($this->_staffList[$_staffGroupVal['staffgroupid']] as $_staffVal) {
                        if (isset($_staffVal['departments']) && _is_array($_staffVal['departments'])) {
                            $this->XML->AddTag('staff', '', array('staffid' => $_staffVal['staffid'], 'fullname' => $_staffVal['fullname'], 'username' => $_staffVal['username'],
                                'email' => $_staffVal['email'], 'mobilenumber' => $_staffVal['mobilenumber'], 'lastvisit' => $_staffVal['lastvisit'],
                                'lastactivity' => $_staffVal['lastactivity'], 'onlinetime' => $_staffVal['onlinetime'], 'sessionid' => $_staffVal['sessionid'],
                                'timezoneoffset' => $_staffVal['timezonephp'], 'isenabled' => $_staffVal['isenabled'], 'status' => $_staffVal['status'],
                                'statusmsg' => $_staffVal['statusmessage'], 'departments' => IIF(_is_array($_staffVal['departments']), implode(',', $_staffVal['departments']), ''),
                                'extension' => '', 'profileupdate' => $_staffVal['lastprofileupdate'], 'threshold' => $_avatarThreshold));

                            if ($_staffVal['lastprofileupdate'] >= $_avatarThreshold) {
                                $_dispatchAvatarXML = true;
                            }
                        }
                    }
                }

                $this->XML->EndParentTag('staffgroup');
            }
        }

        $this->XML->EndParentTag('stafflist');

        if ($_dispatchAvatarXML) {
            SWIFT_StaffProfileImage::DispatchXML($this->XML, SWIFT_StaffProfileImage::TYPE_PRIVATE);
        }

        unset($this->_staffChatObjectIDList);
        unset($this->_staffContainer);
        unset($this->_staffChatQueue);
        unset($this->_staffIDList);
        unset($this->_staffList);

        /**
         * ###############################################
         * END STAFF XML OUTPUT
         * ###############################################
         */


        /**
         * ###############################################
         * MISC OUTPUT
         * ###############################################
         */
        // ======= PENDING CHAT LIST =======
        $this->XML->AddParentTag('pendingchats');
        if (_is_array($this->_pendingChatObjectList)) {
            foreach ($this->_pendingChatObjectList as $_key => $_val) {
                $this->XML->AddParentTag('chat');
                $this->XML->AddTag('chatobjectid', $_val['chatobjectid']);
                $this->XML->AddTag('userfullname', $_val['userfullname']);
                $this->XML->AddTag('useremail', $_val['useremail']);
                $this->XML->AddTag('phonenumber', $_val['phonenumber']);
                $this->XML->AddTag('subject', $_val['subject']);
                $this->XML->AddTag('departmenttitle', $_val['departmenttitle']);
                $this->XML->AddTag('chatsessionid', $_val['chatsessionid']);
                $this->XML->AddTag('visitorsessionid', $_val['visitorsessionid']);
                $this->XML->AddTag('transferstatus', $_val['transferstatus']);
                $this->XML->AddTag('transferfromid', $_val['transferfromid']);
                $this->XML->AddTag('transfertoid', $_val['transfertoid']);
                $this->XML->AddTag('iscall', $_val['isphone']);
                $this->XML->EndTag('chat');
            }
        }

        if (_is_array($this->_pendingStaffChatObjectContainer)) {
            foreach ($this->_pendingStaffChatObjectContainer as $_key => $_val) {
                $this->XML->AddParentTag('staffchat');
                $this->XML->AddTag('chatobjectid', $_val['chatobjectid']);
                $this->XML->AddTag('chatsessionid', $_val['chatsessionid']);
                $this->XML->AddTag('staffid', $_val['creatorstaffid']);
                $this->XML->AddTag('staffname', $_val['userfullname']);
                $this->XML->AddTag('chattype', $_val['chattype']);
                $this->XML->AddTag('notes', $_val['notes']);
                $this->XML->EndTag('staffchat');
            }
        }
        $this->XML->EndTag('pendingchats');

        /**
         * ---------------------------------------------
         * CHAT QUEUE
         * ---------------------------------------------
         */
        $this->XML->AddParentTag('queue');

        $_queueChatObjectIDList = array();

        // First the pending chats
        if (_is_array($this->_pendingChatObjectList)) {
            foreach ($this->_pendingChatObjectList as $_key => $_val) {
                $_chatStatus = '2';
                if (($_val['chatstatus'] == SWIFT_Chat::CHAT_INCOMING) || ($_val['chatstatus'] == SWIFT_Chat::CHAT_INCHAT && $_val['transferstatus'] == SWIFT_Chat::TRANSFER_PENDING)) {
                    $_chatStatus = '0';
                }

                $_waitTime = DATENOW - $_val['dateline'];

                $_chatArguments = array('type' => 'user', 'status' => $_chatStatus);

                $_queueChatObjectIDList[] = $_val['chatobjectid'];

                $this->XML->AddParentTag('chat', $_chatArguments);
                $this->XML->AddTag('chatobjectid', $_val['chatobjectid']);
                $this->XML->AddTag('userfullname', $_val['userfullname']);
                $this->XML->AddTag('useremail', $_val['useremail']);
                $this->XML->AddTag('requeststaffid', '0');
                $this->XML->AddTag('phonenumber', $_val['phonenumber']);
                $this->XML->AddTag('subject', $_val['subject']);
                $this->XML->AddTag('departmenttitle', $_val['departmenttitle']);
                $this->XML->AddTag('chatsessionid', $_val['chatsessionid']);
                $this->XML->AddTag('visitorsessionid', $_val['visitorsessionid']);
                $this->XML->AddTag('transferstatus', $_val['transferstatus']);
                $this->XML->AddTag('transferfromid', $_val['transferfromid']);
                $this->XML->AddTag('transfertoid', $_val['transfertoid']);
                $this->XML->AddTag('iscall', $_val['isphone']);
                $this->XML->AddTag('chattype', 'chat');
                $this->XML->AddTag('staffid', '0');
                $this->XML->AddTag('staffname', '');
                $this->XML->AddTag('duration', $_val['duration']);
                $this->XML->AddTag('waittime', $_waitTime);
                $this->XML->AddTag('creationdate', $_val['creationdate']);
                $this->XML->AddTag('lastactivity', $_val['lastactivity']);
                $this->XML->AddTag('initialchattype', $_val['initialchattype']);
                $this->XML->AddTag('creatorstaffid', $_val['initialcreatorstaffid']);
                $this->XML->EndTag('chat');
            }
        }

        if (_is_array($this->_pendingStaffChatObjectContainer)) {
            foreach ($this->_pendingStaffChatObjectContainer as $_key => $_val) {
                $_chatStatus = '2';
                if ($_val['chatstatus'] == SWIFT_Chat::CHAT_INCOMING) {
                    $_chatStatus = '0';
                }

                $_waitTime = DATENOW - $_val['creationdate'];

                $_chatArguments = array('type' => 'staff', 'status' => $_chatStatus);

                $_queueChatObjectIDList[] = $_val['chatobjectid'];

                $this->XML->AddParentTag('chat', $_chatArguments);
                $this->XML->AddTag('chatobjectid', $_val['chatobjectid']);
                $this->XML->AddTag('userfullname', $_val['userfullname']);
                $this->XML->AddTag('useremail', $_val['useremail']);
                $this->XML->AddTag('requeststaffid', $_val['creatorstaffid']);
                $this->XML->AddTag('phonenumber', '');
                $this->XML->AddTag('subject', $_val['notes']);
                $this->XML->AddTag('departmenttitle', '');
                $this->XML->AddTag('chatsessionid', $_val['chatsessionid']);
                $this->XML->AddTag('visitorsessionid', '');
                $this->XML->AddTag('transferstatus', '');
                $this->XML->AddTag('transferfromid', '');
                $this->XML->AddTag('transfertoid', '');
                $this->XML->AddTag('iscall', '0');
                $this->XML->AddTag('chattype', $_val['chattype']);
                $this->XML->AddTag('staffid', '0');
                $this->XML->AddTag('staffname', '');
                $this->XML->AddTag('duration', $_val['duration']);
                $this->XML->AddTag('waittime', $_waitTime);
                $this->XML->AddTag('creationdate', $_val['creationdate']);
                $this->XML->AddTag('lastactivity', $_val['lastactivity']);
                $this->XML->AddTag('initialchattype', $_val['initialchattype']);
                $this->XML->AddTag('creatorstaffid', $_val['initialcreatorstaffid']);
                $this->XML->EndTag('chat');
            }
        }

        if (_is_array($this->_queueChatObjectContainer)) {
            foreach ($this->_queueChatObjectContainer as $_queueChatObjectID => $_queueChatObject) {
                // Already dispatched? ignore!
                if (in_array($_queueChatObjectID, $_queueChatObjectIDList) || !isset($_queueChatObject['chatobjectid'])) {
                    continue;
                }
                $_waitTime = DATENOW - $_queueChatObject['dateline'];

                $_queueChatType = 'user';
                $_queueRequestStaffID = 0;
                if ($_queueChatObject['chattype'] == SWIFT_Chat::CHATTYPE_STAFF) {
                    $_queueChatType = 'staff';
                    $_queueRequestStaffID = $_queueChatObject['creatorstaffid'];
                }

                $_queueChatStatus = '2';
                if ($_queueChatObject['chatstatus'] == SWIFT_Chat::CHAT_INCOMING) {
                    $_queueChatStatus = '0';
                } else if ($_queueChatObject['chatstatus'] == SWIFT_Chat::CHAT_INCHAT) {
                    $_queueChatStatus = '1';
                }

                $_chatArguments = array('type' => $_queueChatType, 'status' => $_queueChatStatus);
                $this->XML->AddParentTag('chat', $_chatArguments);
                $this->XML->AddTag('chatobjectid', $_queueChatObject['chatobjectid']);
                $this->XML->AddTag('userfullname', $_queueChatObject['userfullname']);
                $this->XML->AddTag('useremail', $_queueChatObject['useremail']);
                $this->XML->AddTag('requeststaffid', $_queueRequestStaffID);
                $this->XML->AddTag('phonenumber', $_queueChatObject['phonenumber']);
                $this->XML->AddTag('subject', $_queueChatObject['subject']);
                $this->XML->AddTag('departmenttitle', $_queueChatObject['departmenttitle']);
                $this->XML->AddTag('chatsessionid', $_queueChatObject['chatsessionid']);
                $this->XML->AddTag('visitorsessionid', $_queueChatObject['visitorsessionid']);
                $this->XML->AddTag('transferstatus', $_queueChatObject['transferstatus']);
                $this->XML->AddTag('transferfromid', $_queueChatObject['transferfromid']);
                $this->XML->AddTag('transfertoid', $_queueChatObject['transfertoid']);
                $this->XML->AddTag('iscall', $_queueChatObject['isphone']);
                $this->XML->AddTag('chattype', 'chat');
                $this->XML->AddTag('staffid', $_queueChatObject['staffid']);
                $this->XML->AddTag('staffname', $_queueChatObject['staffname']);
                $this->XML->AddTag('duration', (DATENOW - $_queueChatObject['dateline']));
                $this->XML->AddTag('waittime', $_waitTime);
                $this->XML->AddTag('creationdate', $_queueChatObject['dateline']);
                $this->XML->AddTag('lastactivity', $_queueChatObject['lastpostactivity']);
                $this->XML->AddTag('initialchattype', $_queueChatObject['chattype']);
                $this->XML->AddTag('creatorstaffid', $_queueChatObject['creatorstaffid']);
                $this->XML->EndTag('chat');
            }
        }

        $this->XML->EndTag('queue');

        $this->XML->EndTag('kayako_livechat');

        unset($this->_pendingChatObjectList);
        unset($this->_pendingStaffChatObjectContainer);

        $_randomNumber = mt_rand(1, 8);
        $_randomModulator = $_randomNumber % 2;

        if ($_randomModulator == 1) {
            SWIFT_Chat::FlushInactive();
        }

        if ($_doReturnData) {
            return $this->XML->ReturnXMLWinapp();
        } else {
            $this->XML->EchoXMLWinapp();
        }

        return true;
    }

    /**
     * Process the Sessions and Staff Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _ProcessSessionsAndStaff()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_staffCache = $this->Cache->Get('staffcache');

        /*
         * ==============================
         * Initialize the Variables
         * ==============================
         */
        $_timeToFetch = time() - 180;
        $_timeToFlush = time() - $_SWIFT->Settings->Get('security_visitorinactivity');
        $_flushExecuted = false;

        $this->_localStaffCache = $_staffCache;

        /**
         * ###############################################
         * PROCESS ONLINE SESSIONS
         * ###############################################
         */

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "sessions
            WHERE sessiontype IN ('" . SWIFT_Interface::INTERFACE_VISITOR . "', '" . SWIFT_Interface::INTERFACE_WINAPP . "', '" . SWIFT_Interface::INTERFACE_CHAT . "')
            AND lastactivity >= '" . ($_timeToFetch) . "'");
        while ($this->Database->NextRecord()) {
            /*
             * ==============================
             * Process Winapp Session
             * ==============================
             */
            if ($this->Database->Record['sessiontype'] == SWIFT_Interface::INTERFACE_WINAPP) {
                $_staffID = (int)($this->Database->Record['typeid']);
                $this->_staffIDList[] = $_staffID;

                if (!$this->_localStaffCache || !isset($this->_localStaffCache[$_staffID])) {
                    continue;
                }

                $_staffContainer = $this->_localStaffCache[$_staffID];

                if (!isset($this->_staffList[$_staffContainer['staffgroupid']])) {
                    $this->_staffList[$_staffContainer['staffgroupid']] = array();
                }

                if (!isset($this->_staffList[$_staffContainer['staffgroupid']][$_staffID])) {
                    $this->_staffList[$_staffContainer['staffgroupid']][$_staffID] = array();
                }

                $_staffPointer = &$this->_staffList[$_staffContainer['staffgroupid']][$_staffID];

                $_staffPointer['staffid'] = $_staffID;
                $_staffPointer['fullname'] = $_staffContainer['fullname'];
                $_staffPointer['username'] = $_staffContainer['username'];
                $_staffPointer['email'] = $_staffContainer['email'];
                $_staffPointer['mobilenumber'] = $_staffContainer['mobilenumber'];
                $_staffPointer['lastvisit'] = $_staffContainer['lastvisit'];
                $_staffPointer['lastactivity'] = $this->Database->Record['lastactivity'];
                $_staffPointer['timezonephp'] = $_staffContainer['timezonephp'];
                $_staffPointer['isenabled'] = $_staffContainer['isenabled'];
                $_staffPointer['statusmessage'] = $_staffContainer['statusmessage'];
                $_staffPointer['lastprofileupdate'] = $_staffContainer['lastprofileupdate'];
                $_staffPointer['isonline'] = true;
                $_staffPointer['onlinetime'] = $this->Database->Record['dateline'];
                $_staffPointer['sessionid'] = $this->Database->Record['sessionid'];

                // Override status for current staff
                if (isset($_POST['status']) && $_staffID == $_SWIFT->Staff->GetStaffID()) {
                    $_staffPointer['status'] = (int)($_POST['status']);
                } else {
                    $_staffPointer['status'] = (int)($this->Database->Record['status']);
                }

                $_staffPointer['departments'] = SWIFT_Staff::GetAssignedDepartmentsOnStaffID($_staffID, APP_LIVECHAT);

                /*
                 * ==============================
                 * Process Chat Session
                 * ==============================
                 */
            } else if ($this->Database->Record['sessiontype'] == SWIFT_Interface::INTERFACE_CHAT) {
                $this->_chatObjectIDList[] = (int)($this->Database->Record['typeid']);

                $this->_chatSessionIDList[$this->Database->Record['typeid']] = $this->Database->Record['sessionid'];

                /*
                 * ==============================
                 * Process Visitor Session
                 * ==============================
                 */
            } else if ($this->Database->Record['sessiontype'] == SWIFT_Interface::INTERFACE_VISITOR) {
                $this->_visitorSessionIDList[] = "'" . $this->Database->Record['sessionid'] . "'";
                $this->_visitorRawSessionIDList[] = $this->Database->Record['sessionid'];

                if (!isset($this->_visitorContainer[$this->Database->Record['sessionid']])) {
                    $this->_visitorContainer[$this->Database->Record['sessionid']] = array();
                }

                $this->_visitorContainer[$this->Database->Record['sessionid']]['sessionid'] = $this->Database->Record['sessionid'];
                $this->_visitorContainer[$this->Database->Record['sessionid']]['notloaded'] = true;
                $this->_visitorContainer[$this->Database->Record['sessionid']]['visitorgroupid'] = (int)($this->Database->Record['visitorgroupid']);
                $this->_visitorContainer[$this->Database->Record['sessionid']]['gridcolor'] = $this->Database->Record['gridcolor'];
                $this->_visitorContainer[$this->Database->Record['sessionid']]['proactiveresult'] = $this->Database->Record['proactiveresult'];
            }

            if ($this->Database->Record['lastactivity'] <= $_timeToFlush && $_flushExecuted != true &&
                ($this->Database->Record['sessiontype'] == SWIFT_Interface::INTERFACE_CHAT || $this->Database->Record['sessiontype'] == SWIFT_Interface::INTERFACE_VISITOR)) {
                $_flushExecuted = true;

                SWIFT_Visitor::Flush();
            }
        }

        /**
         * ###############################################
         * PROCESS OFFLINE STAFF
         * ###############################################
         */
        foreach ($this->_localStaffCache as $_staffID => $_staffContainer) {
            if (!in_array($_staffID, $this->_staffIDList)) {
                $this->_staffIDList[] = $_staffID;

                if (!isset($this->_staffList[$_staffContainer['staffgroupid']])) {
                    $this->_staffList[$_staffContainer['staffgroupid']] = array();
                }

                if (!isset($this->_staffList[$_staffContainer['staffgroupid']][$_staffID])) {
                    $this->_staffList[$_staffContainer['staffgroupid']][$_staffID] = array();
                }

                $this->_staffList[$_staffContainer['staffgroupid']][$_staffID] = $_staffContainer;

                $_staffPointer = &$this->_staffList[$_staffContainer['staffgroupid']][$_staffID];

                $_staffPointer['isonline'] = false;
                $_staffPointer['onlinetime'] = '0';
                $_staffPointer['sessionid'] = '';
                $_staffPointer['status'] = SWIFT_Session::STATUS_OFFLINE;
                $_staffPointer['departments'] = SWIFT_Staff::GetAssignedDepartmentsOnStaffID($_staffID, APP_LIVECHAT);
            }
        }

        return true;
    }

    /**
     * Process the Chat Objects
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _ProcessChatObjects()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        /**
         * ###############################################
         * PROCESS CHAT OBJECTS
         * ###############################################
         */
        $_chatCount = $_pendingChatIndex = 0;
        $_chatObjectList = $_chatObjectContainer = $_chatObjectIDList = $_queueChatObjectContainer = $_queueChatObjectIDList = array();

        $_noAnswerChatObjectIDList = $_timeoutChatObjectIDList = array();
        $_flushDateline = DATENOW - 1800;
        $_flushLastPostActivity = DATENOW - 1800;
        $_sessionThreshold = DATENOW - 120;

        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments(APP_LIVECHAT);

        $this->Database->Query("SELECT chatobjects.*, sessions.lastactivity AS lastactivity
            FROM " . TABLE_PREFIX . "chatobjects AS chatobjects
            LEFT JOIN " . TABLE_PREFIX . "sessions AS sessions ON (chatobjects.chatsessionid = sessions.sessionid)
            WHERE chatobjects.chatstatus IN ('" . SWIFT_Chat::CHAT_INCOMING . "', '" . SWIFT_Chat::CHAT_INCHAT . "') OR chatobjects.chatobjectid IN (" . BuildIN($this->_chatObjectIDList) . ")");
        while ($this->Database->NextRecord()) {
            $_isQueueChat = false;
            if (!in_array($this->Database->Record['chatobjectid'], $this->_chatObjectIDList)) {
                $_isQueueChat = true;

                // Its a staff chat and we are neither the starter or the receiver? Bail!
                if ($this->Database->Record['chattype'] == SWIFT_Chat::CHATTYPE_STAFF && $this->Database->Record['creatorstaffid'] != $_SWIFT->Staff->GetStaffID() && $this->Database->Record['staffid'] != $_SWIFT->Staff->GetStaffID()) {
                    continue;
                }
            }

            if ($this->Settings->Get('ls_routingmode') == 'openqueue') {
                if (in_array($this->Database->Record['departmentid'], $_assignedDepartmentIDList)) {
                    $_queueChatObjectContainer[$this->Database->Record['chatobjectid']] = $this->Database->Record;
                }
            } else {
                $_queueChatObjectContainer[$this->Database->Record['chatobjectid']] = $this->Database->Record;
            }

            // We only goto main processing if this chat object is in the receivable list
            if ($_isQueueChat) {
                $_queueChatObjectIDList[] = $this->Database->Record['chatobjectid'];

                /**
                 * ---------------------------------------------
                 * EXPIRY CHECKS
                 * ---------------------------------------------
                 */

                $_timeSinceChat = DATENOW - $this->Database->Record['dateline'];

                $_lastPostActivity = 0;
                if ((int)($this->Database->Record['lastpostactivity']) > 0) {
                    $_lastPostActivity = DATENOW - $this->Database->Record['lastpostactivity'];
                }

                // Regular open queue timeout?
                if ($this->Settings->Get('ls_routingmode') == 'openqueue' && $this->Database->Record['chatstatus'] == SWIFT_Chat::CHAT_INCOMING && $_timeSinceChat >= $this->Settings->Get('ls_openqueuetimeout')) {
                    $_noAnswerChatObjectIDList[] = $this->Database->Record['chatobjectid'];

                    // No last post activity since 1 hour?
                } else if ($_lastPostActivity >= 3600) {
                    $_timeoutChatObjectIDList[] = $this->Database->Record['chatobjectid'];

                    // Flush
                } else if ($this->Database->Record['dateline'] < $_flushDateline && $this->Database->Record['lastpostactivity'] < $_flushLastPostActivity && $this->Database->Record['lastactivity'] < $_sessionThreshold) {
                    $_timeoutChatObjectIDList[] = $this->Database->Record['chatobjectid'];

                }

                continue;
            }

            $_chatObjectContainer[$this->Database->Record['chatobjectid']] = $this->Database->Record;
            $_chatObjectContainer[$this->Database->Record['chatobjectid']]['isinvite'] = '0';
            $_chatObjectContainer[$this->Database->Record['chatobjectid']]['invitestaffid'] = '0';

            $_chatObjectIDList[] = $this->Database->Record['chatobjectid'];
        }

        // Load up the chat childs
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatchilds WHERE chatobjectid IN (" . BuildIN($_chatObjectIDList) . ")");
        while ($this->Database->NextRecord()) {
            if ($this->Database->Record['isinvite'] == '0') {
                continue;
            }

            if (!in_array($this->Database->Record['chatobjectid'], $_queueChatObjectIDList)) {
                $_chatObjectContainer[$this->Database->Record['chatobjectid']]['isinvite'] = $this->Database->Record['isinvite'];
                $_chatObjectContainer[$this->Database->Record['chatobjectid']]['invitestaffid'] = $this->Database->Record['staffid'];
            }

            if ($this->Settings->Get('ls_routingmode') == 'openqueue') {
                $_queueChatObjectContainer[$this->Database->Record['chatobjectid']]['isinvite'] = $this->Database->Record['isinvite'];
                $_queueChatObjectContainer[$this->Database->Record['chatobjectid']]['invitestaffid'] = $this->Database->Record['staffid'];
            }

        }

        // Process the expiry stuff
        if (count($_noAnswerChatObjectIDList)) {
            SWIFT_Chat::BulkUpdateChatStatus($_noAnswerChatObjectIDList, SWIFT_Chat::CHAT_NOANSWER);
        }

        if (count($_timeoutChatObjectIDList)) {
            SWIFT_Chat::BulkUpdateChatStatus($_timeoutChatObjectIDList, SWIFT_Chat::CHAT_TIMEOUT);
        }

        $this->_queueChatObjectContainer = $_queueChatObjectContainer;

        foreach ($_chatObjectContainer as $_chatObjectID => $_chatObject) {
            $_chatObjectList[count($_chatObjectList)] = $_chatObject;

            if (!isset($this->_visitorContainer[$_chatObject['visitorsessionid']]) && $_chatObject['chattype'] == SWIFT_Chat::CHATTYPE_CLIENT) {
                $this->_visitorContainer[$_chatObject['visitorsessionid']] = array('sessionid' => $_chatObject['visitorsessionid'], 'notloaded' => true);
            }

            if (isset($this->_visitorContainer[$_chatObject['visitorsessionid']]) && !isset($this->_visitorContainer[$_chatObject['visitorsessionid']]['chatterid'])) {
                $this->_visitorContainer[$_chatObject['visitorsessionid']]['chatterid'] = $_chatObject['userfullname'];
            }

            if ($_chatObject['callstatus'] == SWIFT_Call::STATUS_ACCEPTED || $_chatObject['callstatus'] == SWIFT_Call::STATUS_PENDING) {
                $this->_visitorContainer[$_chatObject['visitorsessionid']]['callstate'] = '1';
            }

            if ($_chatObject['isphone'] == '1') {
                $this->_visitorContainer[$_chatObject['visitorsessionid']]['isphone'] = '1';
            }

            if (isset($this->_visitorContainer[$_chatObject['visitorsessionid']]) &&
                (!isset($this->_visitorContainer[$_chatObject['visitorsessionid']]['ipaddress']) || empty($this->_visitorContainer[$_chatObject['visitorsessionid']]['ipaddress']))) {
                $this->_visitorContainer[$_chatObject['visitorsessionid']]['ipaddress'] = $_chatObject['ipaddress'];
                $this->_visitorContainer[$_chatObject['visitorsessionid']]['hostname'] = $_chatObject['ipaddress'];
            }

            $this->_visitorChatContainer[$_chatObject['visitorsessionid']][] = $_chatObject;

            /*
             * ==============================
             * Process Pending Chats (Visitor)
             * ==============================
             */
            if (
                (
                    ($_chatObject['chatstatus'] == SWIFT_Chat::CHAT_INCHAT && $_chatObject['transferstatus'] == SWIFT_Chat::TRANSFER_PENDING && $_chatObject['transfertoid'] == $_SWIFT->Staff->GetStaffID())

                    ||

                    ($this->Settings->Get('ls_routingmode') == 'roundrobin' &&
                        (
                        ($_chatObject['chatstatus'] == SWIFT_Chat::CHAT_INCOMING && $_chatObject['staffid'] == $_SWIFT->Staff->GetStaffID())
                        )
                    )

                    ||

                    ($this->Settings->Get('ls_routingmode') == 'openqueue' &&
                        (
                        ($_chatObject['chatstatus'] == SWIFT_Chat::CHAT_INCOMING)
                        )
                    )

                )

                && $_chatObject['chattype'] == SWIFT_Chat::CHATTYPE_CLIENT) {
                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-1698 Chats are being passed between staff even though the chat is already handled by a staff member
                 * SWIFT-1600 Pop-up for the chat request is prompted to two staff members simultaneously
                 *
                 * Comments: None
                 */
                $_considerPendingChat = true;

                if ($this->Settings->Get('ls_routingmode') == 'roundrobin') {
                    $_roundRobinTimeRetryTimline = $_chatObject['roundrobintimeline'] + $this->Settings->Get('livesupport_roundrobintimetry');
                    $_roundRobinSeconds = $this->Settings->Get('livesupport_roundrobintimetry');

                    $_roundRobinDifference = $_roundRobinTimeRetryTimline - DATENOW;
                    $_roundRobinPercentage = ($_roundRobinDifference / $_roundRobinSeconds) * 100;

                    // If the round robin timeline percentage is greater than 75% then bail
                    if ($_roundRobinPercentage > 75 && (int)($_chatObject['roundrobinhits']) != 0) {
                        $_considerPendingChat = false;
                    }

                    /**
                     * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
                     *
                     * SWIFT-3702 In Open Queue mode, chats cannot be transferred to staff if live chat department is not assigned.
                     *
                     * Comments: Assigned department shouldn't check in case of transfer chat.
                     */
                } else if ($this->Settings->Get('ls_routingmode') == 'openqueue' && (!in_array($_chatObject['departmentid'], $_assignedDepartmentIDList) && $_chatObject['transferfromid'] == 0) || ($_SWIFT->Session->GetProperty('status') != SWIFT_Session::STATUS_ONLINE && $_chatObject['transferstatus'] != SWIFT_Chat::TRANSFER_PENDING)) {
                    $_considerPendingChat = false;
                }

                if ($_considerPendingChat == true && isset($this->_chatSessionIDList[$_chatObject['chatobjectid']])) {
                    $this->_pendingChatVisitorSessionIDList[] = $_chatObject['visitorsessionid'];
                    $this->_pendingChatVisitorContainer[$_chatObject['visitorsessionid']]['userfullname'] = $_chatObject['userfullname'];
                    $this->_pendingChatVisitorContainer[$_chatObject['visitorsessionid']]['dateline'] = $_chatObject['dateline'];
                    $this->_pendingChatVisitorContainer[$_chatObject['visitorsessionid']]['chatsessionid'] = $this->_chatSessionIDList[$_chatObject['chatobjectid']];
                    $this->_pendingChatVisitorContainer[$_chatObject['visitorsessionid']]['departmenttitle'] = $_chatObject["departmenttitle"];

                    $this->_pendingChatObjectList[$_pendingChatIndex]['chatobjectid'] = $_chatObject['chatobjectid'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['userfullname'] = $_chatObject['userfullname'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['useremail'] = $_chatObject['useremail'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['transferstatus'] = $_chatObject['transferstatus'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['transferfromid'] = $_chatObject['transferfromid'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['transfertoid'] = $_chatObject['transfertoid'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['subject'] = $_chatObject['subject'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['chatsessionid'] = $this->_chatSessionIDList[$_chatObjectID];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['visitorsessionid'] = $_chatObject['visitorsessionid'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['departmenttitle'] = $_chatObject['departmenttitle'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['transferstatus'] = $_chatObject['transferstatus'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['transferfromid'] = $_chatObject['transferfromid'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['transfertoid'] = $_chatObject['transfertoid'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['isphone'] = $_chatObject['isphone'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['phonenumber'] = $_chatObject['phonenumber'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['duration'] = (DATENOW - $_chatObject['dateline']);
                    $this->_pendingChatObjectList[$_pendingChatIndex]['waittime'] = $_chatObject['waittime'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['creationdate'] = $_chatObject['dateline'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['lastactivity'] = $_chatObject['lastpostactivity'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['chatstatus'] = $_chatObject['chatstatus'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['dateline'] = $_chatObject['dateline'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['initialchattype'] = $_chatObject['chattype'];
                    $this->_pendingChatObjectList[$_pendingChatIndex]['initialcreatorstaffid'] = $_chatObject['creatorstaffid'];

                    $_pendingChatIndex++;
                }

                /*
                 * ==============================
                 * Process Pending Chats (Staff)
                 * ==============================
                 */
            } else if (($_chatObject['chatstatus'] == SWIFT_Chat::CHAT_INCOMING && $_chatObject['staffid'] == $_SWIFT->Staff->GetStaffID()) && $_chatObject['chattype'] == SWIFT_Chat::CHATTYPE_STAFF) {
                if (isset($this->_chatSessionIDList[$_chatObject['chatobjectid']])) {
                    $this->_pendingStaffChatObjectContainer[] = array('chatobjectid' => $_chatObject['chatobjectid'],
                        'chatsessionid' => $this->_chatSessionIDList[$_chatObjectID],
                        'staffid' => $_chatObject['staffid'], 'staffname' => $_chatObject['staffname'], 'useremail' => $_chatObject['useremail'],
                        'chattype' => SWIFT_Chat::STAFFCHAT_DEFAULT, 'notes' => '', 'creatorstaffid' => $_chatObject['creatorstaffid'],
                        'userfullname' => $_chatObject['userfullname'], 'duration' => (DATENOW - $_chatObject['dateline']),
                        'waittime' => $_chatObject['waittime'], 'creationdate' => $_chatObject['dateline'], 'lastactivity' => $_chatObject['lastpostactivity'],
                        'chatstatus' => $_chatObject['chatstatus'], 'initialchattype' => $_chatObject['chattype'], 'initialcreatorstaffid' => $_chatObject['creatorstaffid']);
                }


                /*
                 * ==============================
                 * Process Invite Chats (Staff)
                 * ==============================
                 */
            } else if ($_chatObject['chatstatus'] == SWIFT_Chat::CHAT_INCHAT && $_chatObject['isinvite'] == 1 && $_chatObject['invitestaffid'] == $_SWIFT->Staff->GetStaffID()) {
                if (isset($this->_chatSessionIDList[$_chatObject['chatobjectid']])) {
                    //Atul Atri: Adding  initialcreatorstaffid (tag creatorstaffid) because in case of invite tag requeststaffid contains requested staff id
                    $_staffID = $_chatObject['invitestaffid'];
                    $this->_pendingStaffChatObjectContainer[] = array('chatobjectid' => $_chatObject['chatobjectid'],
                        'chatsessionid' => $this->_chatSessionIDList[$_chatObjectID],
                        'staffid' => $_chatObject['staffid'], 'staffname' => $_chatObject['staffname'], 'useremail' => $_chatObject['useremail'],
                        'chattype' => SWIFT_Chat::STAFFCHAT_INVITE, 'notes' => '', 'creatorstaffid' => $_staffID,
                        'userfullname' => $_chatObject['userfullname'], 'duration' => (DATENOW - $_chatObject['dateline']),
                        'waittime' => $_chatObject['waittime'], 'creationdate' => $_chatObject['dateline'], 'lastactivity' => $_chatObject['lastpostactivity'],
                        'chatstatus' => $_chatObject['chatstatus'], 'initialchattype' => $_chatObject['chattype'], 'initialcreatorstaffid' => $_chatObject['creatorstaffid']);

                }


                /*
                 * ==============================
                 * Process Active Chats
                 * ==============================
                 */
            } else if ($_chatObject['chatstatus'] == SWIFT_Chat::CHAT_INCHAT) {
                $this->_inChatVisitorSessionIDList[] = $_chatObject['visitorsessionid'];

                if (isset($this->_chatSessionIDList[$_chatObjectID])) {
                    $this->_inChatVisitorContainer[$_chatObject['visitorsessionid']]['userfullname'] = $_chatObject['userfullname'];
                    $this->_inChatVisitorContainer[$_chatObject['visitorsessionid']]['chatsessionid'] = $this->_chatSessionIDList[$_chatObjectID];
                    $this->_inChatVisitorContainer[$_chatObject['visitorsessionid']]['staffname'] = $_chatObject['staffname'];
                    $this->_inChatVisitorContainer[$_chatObject['visitorsessionid']]['departmenttitle'] = $_chatObject['departmenttitle'];
                }
            }


            /*
             * ==============================
             * !! Run Other Checks !!
             * ==============================
             */

            // Is this a proactive chat?
            if ($_chatObject['isproactive'] == '1' && isset($this->_chatSessionIDList[$_chatObjectID])) {
                $_proactiveChatIndex = count($this->_proactiveChatQueue);

                $this->_proactiveChatQueue[$_proactiveChatIndex]['chatobjectid'] = $_chatObjectID;
                $this->_proactiveChatQueue[$_proactiveChatIndex]['visitorsessionid'] = $_chatObject['visitorsessionid'];
                $this->_proactiveChatQueue[$_proactiveChatIndex]['chatsessionid'] = $this->_chatSessionIDList[$_chatObjectID];
                $this->_proactiveChatQueue[$_proactiveChatIndex]['caption'] = $_chatObject['userfullname'];
                $this->_proactiveChatQueue[$_proactiveChatIndex]['type'] = 'customer';

                $this->_proactiveChatObjectIDList[] = $_chatObjectID;
            }

            // Are we supposed to initiate a chat over here?
            if ($_chatObject['transfertoid'] == $_SWIFT->Staff->GetStaffID() && $_chatObject['transferstatus'] == SWIFT_Chat::TRANSFER_PENDING && isset($this->_chatSessionIDList[$_chatObjectID])) {
                $_transferChatIndex = count($this->_transferChatQueue);

                $this->_transferChatQueue[$_transferChatIndex]['chatobjectid'] = $_chatObjectID;
                $this->_transferChatQueue[$_transferChatIndex]['visitorsessionid'] = $_chatObject['visitorsessionid'];
                $this->_transferChatQueue[$_transferChatIndex]['chatsessionid'] = $this->_chatSessionIDList[$_chatObjectID];
                $this->_transferChatQueue[$_transferChatIndex]['caption'] = $_chatObject['userfullname'];
                $this->_transferChatQueue[$_transferChatIndex]['type'] = 'customer';
                $this->_transferChatObjectIDList[] = $_chatObject['chatobjectid'];
            }

            // Did we transfer over some chats?
            if ($_chatObject['transferfromid'] == $_SWIFT->Staff->GetStaffID() && $_chatObject['transferstatus'] == SWIFT_Chat::TRANSFER_ACCEPTED && isset($this->_chatSessionIDList[$_chatObjectID])) {
                $_transferChatIndex = count($this->_transferFromChatQueue);
                $this->_transferFromChatQueue[$_transferChatIndex]['chatsessionid'] = $this->_chatSessionIDList[$_chatObjectID];
                $this->_transferFromChatQueue[$_transferChatIndex]['visitorsessionid'] = $_chatObject['visitorsessionid'];
                $this->_transferFromChatQueue[$_transferChatIndex]['displayname'] = $_chatObject['userfullname'];
            }

            $_chatCount++;
        }

        return true;
    }

    /**
     * Retrieve the Processed Visitor Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _ProcessVisitors()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_visitorGroupCache = $this->Cache->Get('visitorgroupcache');

        /**
         * ###############################################
         * PROCESS VISITOR FOOTPRINTS
         * ###############################################
         */

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorfootprints
                                WHERE sessionid IN('0'" . (isset($this->_visitorSessionIDList) && count($this->_visitorSessionIDList) ? ',' . implode(', ', $this->_visitorSessionIDList) : '') . ")
                                ORDER BY dateline DESC");

        $_footprintCount = 0;
        while ($this->Database->NextRecord()) {
            $_footprintCount++;

            if (!in_array('\'' . $this->Database->Record['sessionid'] . '\'', $this->_visitorSessionIDList)) {
                $this->_visitorSessionIDList[] = '\'' . $this->Database->Record['sessionid'] . '\'';
                $this->_visitorRawSessionIDList[] = $this->Database->Record['sessionid'];
            }

            if (!isset($this->_visitorContainer[$this->Database->Record['sessionid']])) {
                $this->_visitorContainer[$this->Database->Record['sessionid']] = array();
            }

            // Makes it easy to reference
            $_visitorPointer = &$this->_visitorContainer[$this->Database->Record['sessionid']];

            if (!isset($_visitorPointer['sessionid']) || (isset($_visitorPointer['notloaded']) && $_visitorPointer['notloaded'] == true)) {
                $_visitorPointer['notloaded'] = false;

                /**
                 * This is all static content, it will be same for all pages and we update it only once per session
                 */

                $_visitorPointer['sessionid'] = $this->Database->Record['sessionid'];
                $_visitorPointer['hostname'] = $this->Database->Record['hostname'];
                $_visitorPointer['countryname'] = $this->Database->Record['country'];
                $_visitorPointer['countrycode'] = $this->Database->Record['countrycode'];
                $_visitorPointer['browsername'] = $this->Database->Record['browsername'];
                $_visitorPointer['browserversion'] = $this->Database->Record['browserversion'];
                $_visitorPointer['browsercode'] = $this->Database->Record['browsercode'];
                $_visitorPointer['resolution'] = $this->Database->Record['resolution'];
                $_visitorPointer['platform'] = $this->Database->Record['platform'];
                $_visitorPointer['operatingsys'] = $this->Database->Record['operatingsys'];
                $_visitorPointer['colordepth'] = $this->Database->Record['colordepth'];
                $_visitorPointer['appversion'] = $this->Database->Record['appversion'];
                $_visitorPointer['ipaddress'] = $this->Database->Record['ipaddress'];
                $_visitorPointer['campaignid'] = $this->Database->Record['campaignid'];
                $_visitorPointer['campaigntitle'] = $this->Database->Record['campaigntitle'];
                $_visitorPointer['hasnote'] = $this->Database->Record['hasnote'];
                $_visitorPointer['referrer'] = $this->Database->Record['referrer'];

                // ======= GeoIP =======
                $_visitorPointer['geoipisp'] = $this->Database->Record['geoipisp'];
                $_visitorPointer['geoiporganization'] = $this->Database->Record['geoiporganization'];
                $_visitorPointer['geoipnetspeed'] = $this->Database->Record['geoipnetspeed'];
                $_visitorPointer['geoipcountry'] = $this->Database->Record['geoipcountry'];
                $_visitorPointer['geoipcountrydesc'] = $this->Database->Record['geoipcountrydesc'];
                $_visitorPointer['geoipregion'] = $this->Database->Record['geoipregion'];
                $_visitorPointer['geoipcity'] = $this->Database->Record['geoipcity'];
                $_visitorPointer['geoippostalcode'] = $this->Database->Record['geoippostalcode'];
                $_visitorPointer['geoiplatitude'] = $this->Database->Record['geoiplatitude'];
                $_visitorPointer['geoiplongitude'] = $this->Database->Record['geoiplongitude'];
                $_visitorPointer['geoipmetrocode'] = $this->Database->Record['geoipmetrocode'];
                $_visitorPointer['geoipareacode'] = $this->Database->Record['geoipareacode'];
                $_visitorPointer['geoiptimezone'] = $this->Database->Record['geoiptimezone'];
            }

            /**
             * Is current page empty? Set it then, as we fetch by dateline the first record will always be the current page
             */
            if (!isset($_visitorPointer['currentpage'])) {
                $_visitorPointer['currentpage'] = $this->Database->Record['pageurl'];
                $_visitorPointer['pagetitle'] = $this->Database->Record['pagetitle'];
            }

            /**
             * Process the last visit and last chat timelines
             */
            if ((!isset($_visitorPointer['lastvisit']) || empty($_visitorPointer['lastvisit'])) && !empty($this->Database->Record['lastvisit'])) {
                $_visitorPointer['lastvisit'] = $this->Database->Record['lastvisit'];
            }

            if ((!isset($_visitorPointer['lastchat']) || empty($_visitorPointer['lastchat'])) && !empty($this->Database->Record['lastchat'])) {
                $_visitorPointer['lastchat'] = $this->Database->Record['lastchat'];
            }

            /**
             * Does this record have a search entry? Yep, Add it, Search entry in referrer will only be for single record if there are multiple footprints so we should add it now
             */
            if (trim($this->Database->Record['searchenginename']) != '') {
                $_visitorPointer['searchenginename'] = $this->Database->Record['searchenginename'];
                $_visitorPointer['searchstring'] = $this->Database->Record['searchstring'];
            }

            /**
             * Are the color entries not empty? Yeah, Set them too!
             */
            if (trim($this->Database->Record['rowbgcolor']) != '') {
                $_visitorPointer['rowbgcolor'] = $this->Database->Record['rowbgcolor'];
            }
            if (trim($this->Database->Record['rowfrcolor']) != '') {
                $_visitorPointer['rowfrcolor'] = $this->Database->Record['rowfrcolor'];
            }

            /**
             * Rules Overrides
             */
            if (!empty($_visitorPointer['visitorgroupid']) && isset($_visitorGroupCache[$_visitorPointer['visitorgroupid']])) {
                $_visitorPointer['group'] = $_visitorGroupCache[$_visitorPointer['visitorgroupid']]['title'];
                $_visitorPointer['groupcolor'] = $_visitorGroupCache[$_visitorPointer['visitorgroupid']]['color'];
            }

            if (!empty($_visitorPointer['gridcolor'])) {
                $_visitorPointer['rowfrcolor'] = $_visitorPointer['gridcolor'];
            }

            /**
             * Does this user has notes attached to him?
             */
            if ($this->Database->Record['hasnote'] == '1') {
                $_visitorPointer['hasnote'] = $this->Database->Record['hasnote'];
            }

            /**
             * Add Footprints
             */
            if (!isset($this->_visitorFootprintsContainer[$this->Database->Record['sessionid']])) {
                $this->_visitorFootprintsContainer[$this->Database->Record['sessionid']] = array();
            }

            $_visitorFootprintPointer = &$this->_visitorFootprintsContainer[$this->Database->Record['sessionid']];

            $_footprintIndex = count($_visitorFootprintPointer);
            $_visitorFootprintPointer[$_footprintIndex]['pagetitle'] = $this->Database->Record['pagetitle'];
            $_visitorFootprintPointer[$_footprintIndex]['dateline'] = $this->Database->Record['dateline'];
            $_visitorFootprintPointer[$_footprintIndex]['lastactivity'] = $this->Database->Record['lastactivity'];
            $_visitorFootprintPointer[$_footprintIndex]['pageurl'] = $this->Database->Record['pageurl'];
            $_visitorFootprintPointer[$_footprintIndex]['referrer'] = $this->Database->Record['referrer'];

            if (!isset($this->_sessionLastActivityContainer[$this->Database->Record['sessionid']])) {
                $this->_sessionLastActivityContainer[$this->Database->Record['sessionid']] = array();
            }

            $this->_sessionLastActivityContainer[$this->Database->Record['sessionid']][] = $this->Database->Record['lastactivity'];
        }

        /**
         * BUG FIX - Abhishek Mittal <abhishek.mittal@kayako.com>
         *
         * SWIFT-949 Custom Field values are not displayed correctly in KD for Radio custom fileds
         *
         */
        $_customFieldGroupContainer = SWIFT_CustomFieldManager::Retrieve(array(SWIFT_CustomFieldGroup::GROUP_LIVECHATPRE, SWIFT_CustomFieldGroup::GROUP_LIVECHATPOST));

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitordata WHERE visitorsessionid IN ('0'" .
            IIF(count($this->_visitorSessionIDList), ',') . implode(', ', $this->_visitorSessionIDList) . ")");
        while ($this->Database->NextRecord()) {
            if (!isset($this->_visitorContainer[$this->Database->Record['visitorsessionid']])) {
                continue;
            }

            if (!isset($this->_visitorCustomDataContainer[$this->Database->Record['visitorsessionid']])) {
                $this->_visitorCustomDataContainer[$this->Database->Record['visitorsessionid']] = array();
            }

            /**
             * BUG FIX - Abhishek Mittal <abhishek.mittal@kayako.com>
             *
             * SWIFT-949 Custom Field values are not displayed correctly in KD for Radio custom fileds
             *
             */
            $_dataValue = "";
            $_customFieldIDList = array();

            if (strpos($this->Database->Record['datavalue'], '<br />') !== false) {
                $_customFieldIDList = explode("<br />", $this->Database->Record['datavalue']);
            }

            if (_is_array($_customFieldGroupContainer)) {
                foreach ($_customFieldGroupContainer as $_customFieldGroupList) {

                    if (_is_array($_customFieldGroupList)) {
                        foreach ($_customFieldGroupList['_fields'] as $_customField) {

                            if ($this->Database->Record['datakey'] == $_customField['title'] && _is_array($_customField['_options'])) {
                                foreach ($_customField['_options'] as $_optionID => $_optionValue) {

                                    if (_is_array($_customFieldIDList)) {
                                        if (in_array($_optionID, $_customFieldIDList)) {
                                            $_dataValue .= $_optionValue['optionvalue'] . "<br />";
                                        }
                                    } else {
                                        if ($_optionID == $this->Database->Record['datavalue']) {
                                            $_dataValue = $_optionValue['optionvalue'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($_dataValue)) {
                $this->Database->Record['datavalue'] = $_dataValue;
            }

            $_visitorDataPointer = &$this->_visitorCustomDataContainer[$this->Database->Record['visitorsessionid']];
            $_visitorDataPointer[$this->Database->Record['datatype']][] = $this->Database->Record;
        }

        foreach ($this->_visitorRawSessionIDList as $_sessionID) {
            if (!isset($this->_visitorContainer[$_sessionID]) || !isset($this->_visitorFootprintsContainer[$_sessionID])) {
                continue;
            }

            $this->_visitorContainer[$_sessionID]['min'] = $this->_visitorFootprintsContainer[$_sessionID][count($this->_visitorFootprintsContainer[$_sessionID]) - 1]['dateline'];
            $this->_visitorContainer[$_sessionID]['referrer'] = $this->_visitorFootprintsContainer[$_sessionID][count($this->_visitorFootprintsContainer[$_sessionID]) - 1]['referrer'];
            $this->_visitorContainer[$_sessionID]['max'] = max($this->_sessionLastActivityContainer[$_sessionID]);
        }

        return true;
    }
}
