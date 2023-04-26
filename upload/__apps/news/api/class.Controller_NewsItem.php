<?php

/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    QuickSupport Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-2009, QuickSupport Singapore Pte. Ltd.
 * @license    http://www.kayako.com/license
 * @link        http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

namespace News\Api;

use News\Models\Category\SWIFT_NewsCategoryLink;
use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use Controller_api;
use SWIFT_RESTServer;
use Base\Models\Staff\SWIFT_Staff;
use SWIFT_XML;

/**
 * The News API Controller
 *
 * @property SWIFT_XML $XML
 * @property SWIFT_RESTServer $RESTServer
 * @author Simaranjit Singh
 */
class Controller_NewsItem extends Controller_api implements SWIFT_REST_Interface
{

    /**
     * Constructor
     *
     * @author Simaranjit Singh
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');
        $this->Load->Model('User:User', [], false, false, 'base');
    }

    /**
     * Retrieve & Dispatch News Items
     *
     * @author Simaranjit Singh
     * @param int $_newsItemID (OPTIONAL) The News Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessNewsItems($_newsItemID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_newsItemContainer = array();

        if (!empty($_newsItemID)) {
            $this->Database->Query("SELECT newsitems.*,newsitemdata.contents
                FROM " . TABLE_PREFIX . "newsitems AS newsitems, " . TABLE_PREFIX . "newsitemdata AS newsitemdata
                WHERE newsitems.newsitemid = '" .  ($_newsItemID) . "' AND newsitemdata.newsitemid=newsitems.newsitemid");
        } else {
            $this->Database->Query("SELECT newsitems.*,newsitemdata.contents
                FROM " . TABLE_PREFIX . "newsitems AS newsitems, " . TABLE_PREFIX . "newsitemdata AS newsitemdata
                WHERE newsitemdata.newsitemid=newsitems.newsitemid");
        }

        while ($this->Database->NextRecord()) {
            $_newsItemContainer[$this->Database->Record['newsitemid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('newsitems');

        foreach ($_newsItemContainer as $_newsItemID => $_newsItem) {
            $_SWIFT_NewsItemObject = new SWIFT_NewsItem((int)$_newsItemID);

            $this->XML->AddParentTag('newsitem');
            $this->XML->AddTag('id', $_newsItem['newsitemid']);
            $this->XML->AddTag('staffid', $_newsItem['staffid']);
            $this->XML->AddTag('newstype', $_newsItem['newstype']);
            $this->XML->AddTag('newsstatus', $_newsItem['newsstatus']);
            $this->XML->AddTag('author', $_newsItem['author']);
            $this->XML->AddTag('email', $_newsItem['email']);
            $this->XML->AddTag('subject', $_newsItem['subject']);
            $this->XML->AddTag('emailsubject', $_newsItem['emailsubject']);
            $this->XML->AddTag('dateline', $_newsItem['dateline']);
            $this->XML->AddTag('start', $_newsItem['start']);
            $this->XML->AddTag('expiry', $_newsItem['expiry']);
            $this->XML->AddTag('issynced', $_newsItem['issynced']);
            $this->XML->AddTag('totalcomments', $_newsItem['totalcomments']);
            $this->XML->AddTag('uservisibilitycustom', $_newsItem['uservisibilitycustom']);

            //For user group
            $this->XML->AddParentTag('usergroupidlist');
            $_userGroupIDList = $_SWIFT_NewsItemObject->GetLinkedUserGroupIDList();
            foreach ($_userGroupIDList as $_userGroupID) {
                $this->XML->AddTag('usergroupid', $_userGroupID);
            }
            $this->XML->EndParentTag('usergroupidlist');

            $this->XML->AddTag('staffvisibilitycustom', $_newsItem['staffvisibilitycustom']);

            //For staff group
            $this->XML->AddParentTag('staffgroupidlist');
            $_staffGroupIDList = $_SWIFT_NewsItemObject->GetLinkedStaffGroupIDList();
            foreach ($_staffGroupIDList as $_staffGroupID) {
                $this->XML->AddTag('staffgroupid', $_staffGroupID);
            }
            $this->XML->EndParentTag('staffgroupidlist');

            $this->XML->AddTag('allowcomments', $_newsItem['allowcomments']);
            $this->XML->AddTag('contents', $_newsItem['contents']);

            $_newsCategoryIDList = SWIFT_NewsCategoryLink::RetrieveOnNewsItem(array($_SWIFT_NewsItemObject->GetNewsItemID()));

            //For news categorires
            $this->XML->AddParentTag('categories');
            foreach ($_newsCategoryIDList as $_newsCategoryID) {
                $this->XML->AddTag('categoryid', $_newsCategoryID);
            }

            $this->XML->EndParentTag('categories');

            $this->XML->EndParentTag('newsitem');
        }

        $this->XML->EndParentTag('newsitems');

        return true;
    }

    /**
     * Get a list of News Items
     *
     * Example Output:
     *
     * <newsitems>
     *     <newsitem>
     *         <id><![CDATA[2]]></id>
     *         <staffid><![CDATA[1]]></staffid>
     *         <newstype><![CDATA[1]]></newstype>
     *         <newsstatus><![CDATA[2]]></newsstatus>
     *         <author><![CDATA[admin admin]]></author>
     *         <email><![CDATA[admin@gmail.com]]></email>
     *         <subject><![CDATA[TItle]]></subject>
     *         <emailsubject />
     *         <dateline><![CDATA[1336074301]]></dateline>
     *         <expiry><![CDATA[0]]></expiry>
     *         <issynced><![CDATA[0]]></issynced>
     *         <totalcomments><![CDATA[0]]></totalcomments>
     *         <uservisibilitycustom><![CDATA[0]]></uservisibilitycustom>
     *         <usergroupidlist>
     *         </usergroupidlist>
     *         <staffvisibilitycustom><![CDATA[0]]></staffvisibilitycustom>
     *         <staffgroupidlist>
     *         </staffgroupidlist>
     *         <allowcomments><![CDATA[1]]></allowcomments>
     *         <contents><![CDATA[Content]]></contents>
     *         <categories>
     *             <categoryid><![CDATA[1]]></categoryid>
     *             <categoryid><![CDATA[2]]></categoryid>
     *             <categoryid><![CDATA[3]]></categoryid>
     *         </categories>
     *     </newsitem>
     * </newsitems>
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessNewsItems(0);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Get a News Items
     *
     * Example Output:
     *
     * <newsitems>
     *     <newsitem>
     *         <id><![CDATA[2]]></id>
     *         <staffid><![CDATA[1]]></staffid>
     *         <newstype><![CDATA[1]]></newstype>
     *         <newsstatus><![CDATA[2]]></newsstatus>
     *         <author><![CDATA[admin admin]]></author>
     *         <email><![CDATA[admin@gmail.com]]></email>
     *         <subject><![CDATA[TItle]]></subject>
     *         <emailsubject />
     *         <dateline><![CDATA[1336074301]]></dateline>
     *         <expiry><![CDATA[0]]></expiry>
     *         <issynced><![CDATA[0]]></issynced>
     *         <totalcomments><![CDATA[0]]></totalcomments>
     *         <uservisibilitycustom><![CDATA[0]]></uservisibilitycustom>
     *         <usergroups>
     *         </usergroups>
     *         <staffvisibilitycustom><![CDATA[0]]></staffvisibilitycustom>
     *         <staffgroups>
     *         </staffgroups>
     *         <allowcomments><![CDATA[1]]></allowcomments>
     *         <contents><![CDATA[Content]]></contents>
     *         <categories>
     *             <categoryid><![CDATA[1]]></categoryid>
     *             <categoryid><![CDATA[2]]></categoryid>
     *             <categoryid><![CDATA[3]]></categoryid>
     *         </categories>
     *     </newsitem>
     * </newsitems>
     * @author Simaranjit Singh
     * @param int $_newsItemID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_newsItemID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessNewsItems($_newsItemID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * ListAll
     *
     * Example Output:
     *
     * <newsitems>
     *     <newsitem>
     *         <id><![CDATA[2]]></id>
     *         <staffid><![CDATA[1]]></staffid>
     *         <newstype><![CDATA[1]]></newstype>
     *         <newsstatus><![CDATA[2]]></newsstatus>
     *         <author><![CDATA[admin admin]]></author>
     *         <email><![CDATA[admin@gmail.com]]></email>
     *         <subject><![CDATA[TItle]]></subject>
     *         <emailsubject />
     *         <dateline><![CDATA[1336074301]]></dateline>
     *         <expiry><![CDATA[0]]></expiry>
     *         <issynced><![CDATA[0]]></issynced>
     *         <totalcomments><![CDATA[0]]></totalcomments>
     *         <uservisibilitycustom><![CDATA[0]]></uservisibilitycustom>
     *         <usergroups>
     *         </usergroups>
     *         <staffvisibilitycustom><![CDATA[0]]></staffvisibilitycustom>
     *         <staffgroups>
     *         </staffgroups>
     *         <allowcomments><![CDATA[1]]></allowcomments>
     *         <contents><![CDATA[Content]]></contents>
     *         <categories>
     *             <categoryid><![CDATA[1]]></categoryid>
     *             <categoryid><![CDATA[2]]></categoryid>
     *             <categoryid><![CDATA[3]]></categoryid>
     *         </categories>
     *     </newsitem>
     * </newsitems>
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ListAll($_newsCategoryID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_newsItemContainer = SWIFT_NewsItem::Retrieve(500, 0, array(SWIFT_NewsItem::TYPE_GLOBAL, SWIFT_NewsItem::TYPE_PUBLIC, SWIFT_NewsItem::TYPE_PRIVATE), 0, 0, $_newsCategoryID);

        $this->XML->AddParentTag('newsitems');

        foreach ($_newsItemContainer as $_newsItemID => $_newsItem) {
            $_SWIFT_NewsItemObject = new SWIFT_NewsItem($_newsItem['newsitemid']);

            $this->XML->AddParentTag('newsitem');
            $this->XML->AddTag('id', $_newsItem['newsitemid']);
            $this->XML->AddTag('staffid', $_newsItem['staffid']);
            $this->XML->AddTag('newstype', $_newsItem['newstype']);
            $this->XML->AddTag('newsstatus', $_newsItem['newsstatus']);
            $this->XML->AddTag('author', $_newsItem['author']);
            $this->XML->AddTag('email', $_newsItem['email']);
            $this->XML->AddTag('subject', $_newsItem['subject']);
            $this->XML->AddTag('emailsubject', $_newsItem['emailsubject']);
            $this->XML->AddTag('dateline', $_newsItem['dateline']);
            $this->XML->AddTag('start', $_newsItem['start']);
            $this->XML->AddTag('expiry', $_newsItem['expiry']);
            $this->XML->AddTag('issynced', $_newsItem['issynced']);
            $this->XML->AddTag('totalcomments', $_newsItem['totalcomments']);
            $this->XML->AddTag('uservisibilitycustom', $_newsItem['uservisibilitycustom']);

            //For user group
            $this->XML->AddParentTag('usergroupidlist');
            $_userGroupIDList = $_SWIFT_NewsItemObject->GetLinkedUserGroupIDList();
            foreach ($_userGroupIDList as $_userGroupID) {
                $this->XML->AddTag('usergroupid', $_userGroupID);
            }
            $this->XML->EndParentTag('usergroupidlist');

            $this->XML->AddTag('staffvisibilitycustom', $_newsItem['staffvisibilitycustom']);

            //For staff group
            $this->XML->AddParentTag('staffgroupidlist');
            $_staffGroupIDList = $_SWIFT_NewsItemObject->GetLinkedStaffGroupIDList();
            foreach ($_staffGroupIDList as $_staffGroupID) {
                $this->XML->AddTag('staffgroupid', $_staffGroupID);
            }
            $this->XML->EndParentTag('staffgroupidlist');

            $this->XML->AddTag('allowcomments', $_newsItem['allowcomments']);
            $this->XML->AddTag('contents', $_newsItem['contents']);

            $_newsCategoryIDList = SWIFT_NewsCategoryLink::RetrieveOnNewsItem(array($_SWIFT_NewsItemObject->GetNewsItemID()));

            //For news categorires
            $this->XML->AddParentTag('categories');
            foreach ($_newsCategoryIDList as $_newsCategoryID) {
                $this->XML->AddTag('categoryid', $_newsCategoryID);
            }

            $this->XML->EndParentTag('categories');

            $this->XML->EndParentTag('newsitem');
        }

        $this->XML->EndParentTag('newsitems');

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Insert a news item
     *
     * Required Fields:
     * staffid
     * subject
     * contetns
     *
     * Example Output:
     *
     * <newsitems>
     *     <newsitem>
     *         <id><![CDATA[2]]></id>
     *         <staffid><![CDATA[1]]></staffid>
     *         <newstype><![CDATA[1]]></newstype>
     *         <newsstatus><![CDATA[2]]></newsstatus>
     *         <author><![CDATA[admin admin]]></author>
     *         <email><![CDATA[admin@gmail.com]]></email>
     *         <subject><![CDATA[TItle]]></subject>
     *         <emailsubject />
     *         <dateline><![CDATA[1336074301]]></dateline>
     *         <expiry><![CDATA[0]]></expiry>
     *         <issynced><![CDATA[0]]></issynced>
     *         <totalcomments><![CDATA[0]]></totalcomments>
     *         <uservisibilitycustom><![CDATA[0]]></uservisibilitycustom>
     *         <usergroups>
     *         </usergroups>
     *         <staffvisibilitycustom><![CDATA[0]]></staffvisibilitycustom>
     *         <staffgroups>
     *         </staffgroups>
     *         <allowcomments><![CDATA[1]]></allowcomments>
     *         <contents><![CDATA[Content]]></contents>
     *         <categories>
     *             <categoryid><![CDATA[1]]></categoryid>
     *             <categoryid><![CDATA[2]]></categoryid>
     *             <categoryid><![CDATA[3]]></categoryid>
     *         </categories>
     *     </newsitem>
     * </newsitems>
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        //I need staff cache to get infromation of staff who posted news
        $_newsCategoryCache = (array) $this->Cache->Get('newscategorycache');
        $_userGroupCache = (array) $this->Cache->Get('usergroupcache');
        $_staffGroupCache = (array) $this->Cache->Get('staffgroupcache');

        $_newsType = SWIFT_NewsItem::TYPE_GLOBAL;
        if (isset($_POST['newstype']) && ($_POST['newstype'] == SWIFT_NewsItem::TYPE_PUBLIC || $_POST['newstype'] == SWIFT_NewsItem::TYPE_PRIVATE)) {
            $_newsType = $_POST['newstype'];
            return false;
        }

        $_newsStatus = SWIFT_NewsItem::STATUS_PUBLISHED;
        if (isset($_POST['newsstatus']) && $_POST['newsstatus'] == SWIFT_NewsItem::STATUS_DRAFT) {
            $_newsStatus = $_POST['newsstatus'];
        }

        if (!isset($_POST['staffid']) || empty($_POST['staffid']) || trim($_POST['staffid']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Staff ID missing');
            return false;
        }

        if (!isset($_POST['subject']) || empty($_POST['subject']) || trim($_POST['subject']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Subject is empty');
            return false;
        }

        if (!isset($_POST['contents']) || empty($_POST['contents']) || trim($_POST['contents']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Content is empty');
            return false;
        }

        // Try to load details of staff like full name and email
        try {
            $_SWIFT_StaffObject_Creator = new SWIFT_Staff(new SWIFT_DataID($_POST['staffid']));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid Staff ID');

            return false;
        }

        $_fromName = '';
        if (isset($_POST['fromname']) && !empty($_POST['fromname']) && trim($_POST['fromname']) !== '') {
            $_fromName = $_POST['fromname'];
        }

        $_email = '';
        if (isset($_POST['email']) && IsEmailValid($_POST['email'])) {
            $_email = $_POST['email'];
        }

        $_customEmailSubject = '';
        if (isset($_POST['customemailsubject']) && !empty($_POST['customemailsubject']) && trim($_POST['customemailsubject']) !== '') {
            $_customEmailSubject = $_POST['customemailsubject'];
        }

        $_sendMail = false;
        if (isset($_POST['sendemail']) && $_POST['sendemail'] == 1) {
            $_sendMail = TRUE;
        }

        $_allowComments = '1';
        if (isset($_POST['allowcomments']) && $_POST['allowcomments'] == '0') {
            $_allowComments = $_POST['allowcomments'];
        }

        $_userVisibilityCustom = '0';
        if (isset($_POST['uservisibilitycustom']) && $_POST['uservisibilitycustom'] == '1') {
            $_userVisibilityCustom = (int) ($_POST['uservisibilitycustom']);
        }

        $_staffVisibilityCustom = '0';
        if (isset($_POST['staffvisibilitycustom'])) {
            $_staffVisibilityCustom = (int) ($_POST['staffvisibilitycustom']);
        }

        $_start = GetDateFieldTimestamp('start');
        $_expiry = GetDateFieldTimestamp('expiry');

        if ($_start > $_expiry && $_expiry !== 0) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Start date must be before expiry date');

            return false;
        }

        $_newsCategoryIDList = array();
        if (isset($_POST['newscategoryidlist']) && !empty($_POST['newscategoryidlist'])) {
            $_newsCategoryIDList = explode(',', $_POST['newscategoryidlist']);
            // I need to make sure that user is not enring any invalid or non existing cateories, sorry naughty guys :)
            $_newsCategoryIDList = array_intersect(array_keys($_newsCategoryCache), $_newsCategoryIDList);
        }

        $_userGroupIDList = array();
        if (isset($_POST['usergroupidlist']) && !empty($_POST['usergroupidlist'])) {
            $_userGroupIDList = explode(',', $_POST['usergroupidlist']);
            // I need to make sure that user is not enring any invalid or non existing group, sorry naughty guys :)
            $_userGroupIDList = array_intersect(array_keys($_userGroupCache), $_userGroupIDList);
        }

        $_staffGroupIDList = array();
        if (isset($_POST['staffgroupidlist']) && !empty($_POST['staffgroupidlist'])) {
            $_staffGroupIDList = explode(',', $_POST['staffgroupidlist']);
            // I need to make sure that user is not enring any invalid or non existing group, sorry naughty guys :)
            $_staffGroupIDList = array_intersect(array_keys($_staffGroupCache), $_staffGroupIDList);
        }

        $_newsItemID = SWIFT_NewsItem::Create($_newsType, $_newsStatus, $_SWIFT_StaffObject_Creator->GetProperty('fullname'), $_SWIFT_StaffObject_Creator->GetProperty('email'), $_POST['subject'], '', $_POST['contents'], $_POST['staffid'], $_expiry, $_allowComments, $_userVisibilityCustom, $_userGroupIDList, $_staffVisibilityCustom, $_staffGroupIDList, FALSE, '', $_customEmailSubject, FALSE, $_newsCategoryIDList, $_sendMail, $_fromName, $_email, $_start);

        if (!$_newsItemID) {
            // @codeCoverageIgnoreStart
            // This will never be executed
            return false;
            // @codeCoverageIgnoreEnd
        }

        $this->ProcessNewsItems($_newsItemID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Update news item
     *
     * Required Fields:
     * newsitemid
     * staffid
     * subject
     * contetns
     *
     * Example Output:
     *
     * <newsitems>
     *     <newsitem>
     *         <id><![CDATA[2]]></id>
     *         <staffid><![CDATA[1]]></staffid>
     *         <newstype><![CDATA[1]]></newstype>
     *         <newsstatus><![CDATA[2]]></newsstatus>
     *         <author><![CDATA[admin admin]]></author>
     *         <email><![CDATA[admin@gmail.com]]></email>
     *         <subject><![CDATA[TItle]]></subject>
     *         <emailsubject />
     *         <dateline><![CDATA[1336074301]]></dateline>
     *         <expiry><![CDATA[0]]></expiry>
     *         <issynced><![CDATA[0]]></issynced>
     *         <totalcomments><![CDATA[0]]></totalcomments>
     *         <uservisibilitycustom><![CDATA[0]]></uservisibilitycustom>
     *         <usergroups>
     *         </usergroups>
     *         <staffvisibilitycustom><![CDATA[0]]></staffvisibilitycustom>
     *         <staffgroups>
     *         </staffgroups>
     *         <allowcomments><![CDATA[1]]></allowcomments>
     *         <contents><![CDATA[Content]]></contents>
     *         <categories>
     *             <categoryid><![CDATA[1]]></categoryid>
     *             <categoryid><![CDATA[2]]></categoryid>
     *             <categoryid><![CDATA[3]]></categoryid>
     *         </categories>
     *     </newsitem>
     * </newsitems>
     *
     * @author Simaranjit Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_newsItemID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_NewsItemObject = false;

        if (!isset($_POST['editedstaffid']) || trim($_POST['editedstaffid']) == '' || (int) ($_POST['editedstaffid']) == 0) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Staff is missing or invalid');

            return false;
        }

        try {
            $_SWIFT_NewsItemObject = new SWIFT_NewsItem($_newsItemID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid news item ID');

            return false;
        }

        $_subject = $_SWIFT_NewsItemObject->GetProperty('subject');
        if (isset($_POST['subject']) && trim($_POST['subject']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Subject is missing');

            return false;
        }

        $_subject = $_POST['subject'];

        $_contents = $_SWIFT_NewsItemObject->GetProperty('contents');
        if (isset($_POST['contents']) && trim($_POST['contents']) == '') {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Contents is missing');

            return false;
        }

        $_contents = $_POST['contents'];

        //I need staff cache to get infromation of staff who posted news
        $_newsCategoryCache = (array)$this->Cache->Get('newscategorycache');
        $_userGroupCache = (array)$this->Cache->Get('usergroupcache');
        $_staffGroupCache = (array)$this->Cache->Get('staffgroupcache');

        $_userGroupIDList = $_staffGroupIDList = $_newsCategoryIDList = array();

        $_newsStatus = $_SWIFT_NewsItemObject->GetProperty('newsstatus');
        if (isset($_POST['newsstatus']) && ($_POST['newsstatus'] == SWIFT_NewsItem::STATUS_DRAFT || $_POST['newsstatus'] == SWIFT_NewsItem::STATUS_PUBLISHED)) {
            $_newsStatus = $_POST['newsstatus'];
        }

        $_SWIFT_NewsItemObject->UpdateStatus($_newsStatus);

        $_staffID = $_POST['editedstaffid'];

        // Try to load details of staff like full name and email
        $_SWIFT_StaffObject_Creator = false;
        try {
            $_SWIFT_StaffObject_Creator = new SWIFT_Staff(new SWIFT_DataID($_staffID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Staff ID is invalid');

            return false;
        }

        $_fromName = '';
        if (isset($_POST['fromname']) && !empty($_POST['fromname']) && trim($_POST['fromname']) !== '') {
            $_fromName = $_POST['fromname'];
        }

        $_email = $_SWIFT_NewsItemObject->GetProperty('email');
        if (isset($_POST['email']) && IsEmailValid($_POST['email'])) {
            $_email = $_POST['email'];
        }

        $_customEmailSubject = $_SWIFT_NewsItemObject->GetProperty('emailsubject');
        if (isset($_POST['customemailsubject']) && !empty($_POST['customemailsubject']) && trim($_POST['customemailsubject']) !== '') {
            $_customEmailSubject = $_POST['customemailsubject'];
        }

        $_sendMail = false;
        if (isset($_POST['sendemail']) && $_POST['sendemail'] == 1) {
            $_sendMail = true;
        }

        $_allowComments = $_SWIFT_NewsItemObject->GetProperty('allowcomments');
        if (isset($_POST['allowcomments']) && ($_POST['allowcomments'] == '0' || $_POST['allowcomments'] == '1')) {
            $_allowComments = $_POST['allowcomments'];
        }

        $_userVisibilityCustom = $_SWIFT_NewsItemObject->GetProperty('uservisibilitycustom');
        if (isset($_POST['uservisibilitycustom']) && ($_POST['uservisibilitycustom'] == '0' || $_POST['uservisibilitycustom'] == '1')) {
            $_userVisibilityCustom = (int) ($_POST['uservisibilitycustom']);
        }

        $_staffVisibilityCustom = $_SWIFT_NewsItemObject->GetProperty('staffvisibilitycustom');
        if (isset($_POST['staffvisibilitycustom']) && ($_POST['staffvisibilitycustom'] == '0' || $_POST['staffvisibilitycustom'] == '1')) {
            $_staffVisibilityCustom = (int) ($_POST['staffvisibilitycustom']);
        }

        $_start = $_SWIFT_NewsItemObject->GetProperty('start');
        if (isset($_POST['start']) && !empty($_POST['start'])) {
            $_start = GetDateFieldTimestamp($_POST['start']);
        }

        $_expiry = $_SWIFT_NewsItemObject->GetProperty('expiry');
        if (isset($_POST['expiry']) && !empty($_POST['expiry'])) {
            $_expiry = GetDateFieldTimestamp($_POST['expiry']);
        }

        if ($_start > $_expiry && $_expiry !== 0) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Start date must be before expiry date');

            return false;
        }

        $_newsCategoryIDList = SWIFT_NewsCategoryLink::RetrieveOnNewsItem(array($_SWIFT_NewsItemObject->GetNewsItemID()));
        if (isset($_POST['newscategoryidlist']) && !empty($_POST['newscategoryidlist'])) {
            $_newsCategoryIDList = explode(',', $_POST['newscategoryidlist']);
            //I need to make sure that user is not enring any invalid or non existing cateories, sorry naughty guys :)
            $_newsCategoryIDList = array_intersect(array_keys($_newsCategoryCache), $_newsCategoryIDList);
        }

        $_userGroupIDList = $_SWIFT_NewsItemObject->GetLinkedUserGroupIDList();
        if (isset($_POST['usergroupidlist']) && !empty($_POST['usergroupidlist'])) {
            $_userGroupIDList = explode(',', $_POST['usergroupidlist']);
            //I need to make sure that user is not enring any invalid or non existing group, sorry naughty guys :)
            $_userGroupIDList = array_intersect(array_keys($_userGroupCache), $_userGroupIDList);
        }

        $_staffGroupIDList = $_SWIFT_NewsItemObject->GetLinkedStaffGroupIDList();
        if (isset($_POST['staffgroupidlist']) && !empty($_POST['staffgroupidlist'])) {
            $_staffGroupIDList = explode(',', $_POST['staffgroupidlist']);
            //I need to make sure that user is not enring any invalid or non existing group, sorry naughty guys :)
            $_staffGroupIDList = array_intersect(array_keys($_staffGroupCache), $_staffGroupIDList);
        }

        $_updateResult = $_SWIFT_NewsItemObject->Update($_subject, '', $_contents, $_staffID, $_expiry, $_allowComments, $_userVisibilityCustom, $_userGroupIDList, $_staffVisibilityCustom, $_staffGroupIDList, $_customEmailSubject, $_newsCategoryIDList, $_sendMail, $_fromName, $_email, $_start);

        // @codeCoverageIgnoreStart
        // This code will never be executed
        if (!$_updateResult) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'An error occured during update');
        }
        // @codeCoverageIgnoreEnd

        $this->ProcessNewsItems($_newsItemID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     *     Delete news item
     *
     * @author Simaranjit Singh
     * @param string $_newsItemID news item ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_newsItemID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_NewsItemObject = false;
        try {
            $_SWIFT_NewsItemObject = new SWIFT_NewsItem((int)$_newsItemID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid news item ID');
            return false;
        }

        SWIFT_NewsItem::DeleteList(array($_newsItemID));

        return true;
    }

}
