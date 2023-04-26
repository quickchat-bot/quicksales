<?php
                    /**
                    * ###############################################
                    *
                    * SWIFT Framework
                    * _______________________________________________
                    *
                    * @package        SWIFT
                    * @copyright    Copyright (c) 2001-2014, QuickSupport
                    * @license        http://www.opencart.com.vn/license
                    * @link        http://www.opencart.com.vn
                    *
                    * ###############################################
                    */

                      namespace Base\Admin; use Controller_admin; use SWIFT; use SWIFT_App; use SWIFT_CacheManager; use LiveChat\Models\Skill\SWIFT_ChatSkill; use SWIFT_DataID; use Parser\Models\EmailQueue\SWIFT_EmailQueue; use SWIFT_Exception; use SWIFT_Hook; use SWIFT_Image_Exception; use SWIFT_ImageResize; use SWIFT_Interface; use SWIFT_Model; use SWIFT_Session; use Base\Models\Staff\SWIFT_Staff; use Base\Models\Staff\SWIFT_StaffActivityLog; use Base\Models\Staff\SWIFT_StaffAssign; use Base\Models\Staff\SWIFT_StaffProfileImage; use Base\Models\Staff\SWIFT_StaffSettings; use Base\Library\UserInterface\SWIFT_UserInterface;  class Controller_Staff extends Controller_admin { static public $_activeSessionList = array();  const MENU_ID = 2; const NAVIGATION_ID = 1;  public function __construct() { parent::__construct(); $this->Load->Library('Staff:StaffPasswordPolicy', [], true, false, 'base'); $this->Language->Load('staff'); $this->Language->Load('admin_staffpermissions'); }  public static function DeleteList($_staffIDList, $_byPassCSRF = false) { $_SWIFT = SWIFT::GetInstance();  if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) { SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash')); return false; }   if (in_array($_SWIFT->Staff->GetStaffID(), $_staffIDList)) { SWIFT::Error($_SWIFT->Language->Get('titlestaffdelsame'), sprintf($_SWIFT->Language->Get('msgstaffdelsame'), text_to_html_entities($_SWIFT->Staff->GetProperty('fullname')))); return false; } if ($_SWIFT->Staff->GetPermission('admin_candeletestaff') == '0') { SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm')); return false; } if (_is_array($_staffIDList)) { $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff WHERE staffid IN (" . BuildIN($_staffIDList) . ")"); while ($_SWIFT->Database->NextRecord()) { SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletestaff'), text_to_html_entities($_SWIFT->Database->Record['fullname']), htmlspecialchars($_SWIFT->Database->Record['email'])), SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_STAFF, SWIFT_StaffActivityLog::INTERFACE_ADMIN); }  unset($_hookCode); ($_hookCode = SWIFT_Hook::Execute('admin_staff_delete')) ? eval($_hookCode) : false;  SWIFT_Staff::DeleteList($_staffIDList); } return true; }  public static function EnableList($_staffIDList, $_byPassCSRF = false) { $_SWIFT = SWIFT::GetInstance();  if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) { SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash')); return false; }  if ($_SWIFT->Staff->GetPermission('admin_caneditstaff') == '0') { SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm')); return false; } if (_is_array($_staffIDList)) {  $_activeStaffCount = SWIFT_Staff::ActiveStaffCount(); $_disableStaffCount = 0; $_disableStaffCountContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "staff WHERE staffid IN (" . BuildIN($_staffIDList) . ") AND isenabled = '0'"); if (isset($_disableStaffCountContainer['totalitems'])) { $_disableStaffCount = $_disableStaffCountContainer['totalitems']; } $_totalActiveStaffCount = $_activeStaffCount + $_disableStaffCount; if (SWIFT::Get('licensedstaff') != false && $_totalActiveStaffCount > SWIFT::Get('licensedstaff')) { SWIFT::Error($_SWIFT->Language->Get('titlestafflicense'), $_SWIFT->Language->Get('msgenablestafflicense')); return false; } $_finalStaffIDList = array(); $_finalText = ''; $_index = 1; $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff WHERE staffid IN (" . BuildIN($_staffIDList) . ")"); while ($_SWIFT->Database->NextRecord()) { if ($_SWIFT->Database->Record['isenabled'] == 0) { $_finalStaffIDList[] = $_SWIFT->Database->Record['staffid']; $_finalText .= $_index . '. ' . text_to_html_entities($_SWIFT->Database->Record['fullname']) . ' (' . htmlspecialchars($_SWIFT->Database->Record['email']) . ")<BR />\n"; SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityenablestaff'), text_to_html_entities($_SWIFT->Database->Record['fullname']), htmlspecialchars($_SWIFT->Database->Record['email'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_STAFF, SWIFT_StaffActivityLog::INTERFACE_ADMIN); $_index++; } } if (!count($_finalStaffIDList)) { return false; } SWIFT::Info(sprintf($_SWIFT->Language->Get('titleenablestaff'), count($_finalStaffIDList)), sprintf($_SWIFT->Language->Get('msgenablestaff'), $_finalText));  unset($_hookCode); ($_hookCode = SWIFT_Hook::Execute('admin_staff_enable')) ? eval($_hookCode) : false;  SWIFT_Staff::EnableStaffList($_finalStaffIDList); } return true; }  public static function DisableList($_staffIDList, $_byPassCSRF = false) { $_SWIFT = SWIFT::GetInstance();  if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) { SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash')); return false; }  if ($_SWIFT->Staff->GetPermission('admin_caneditstaff') == '0') { SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm')); return false; } if (in_array($_SWIFT->Staff->GetStaffID(), $_staffIDList)) { SWIFT::Error(sprintf($_SWIFT->Language->Get('titlenoenable'), text_to_html_entities($_SWIFT->Staff->GetProperty('fullname'))), sprintf($_SWIFT->Language->Get('msgnoenable'), text_to_html_entities($_SWIFT->Staff->GetProperty('fullname'))));  if (count($_staffIDList) == 1) { return false; } } if (_is_array($_staffIDList)) { $_finalStaffIDList = array(); $_finalText = ''; $_index = 1; $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff WHERE staffid IN (" . BuildIN($_staffIDList) . ")"); while ($_SWIFT->Database->NextRecord()) { if ($_SWIFT->Database->Record['isenabled'] == 1 && $_SWIFT->Staff->GetStaffID() != $_SWIFT->Database->Record['staffid']) { $_finalStaffIDList[] = $_SWIFT->Database->Record['staffid']; $_finalText .= $_index . '. ' . text_to_html_entities($_SWIFT->Database->Record['fullname']) . ' (' . htmlspecialchars($_SWIFT->Database->Record['email']) . ")<BR />\n"; SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydisablestaff'), text_to_html_entities($_SWIFT->Database->Record['fullname']), htmlspecialchars($_SWIFT->Database->Record['email'])), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_STAFF, SWIFT_StaffActivityLog::INTERFACE_ADMIN); $_index++; } } if (!count($_finalStaffIDList)) { return false; } SWIFT::Info(sprintf($_SWIFT->Language->Get('titledisablestaff'), count($_finalStaffIDList)), sprintf($_SWIFT->Language->Get('msgdisablestaff'), $_finalText));  unset($_hookCode); ($_hookCode = SWIFT_Hook::Execute('admin_staff_disable')) ? eval($_hookCode) : false;  SWIFT_Staff::DisableStaffList($_finalStaffIDList); } return true; }  public function Delete($_staffID) { if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); } self::DeleteList(array($_staffID), true); $this->Load->Manage(); return true; }  public function Disable($_staffID) { if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); } self::DisableList(array($_staffID), true); $this->Load->Manage(); return true; }  public function Enable($_staffID) { if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); } self::EnableList(array($_staffID), true); $this->Load->Manage(); return true; }  public function Manage($_reportID = 0) { $_SWIFT = SWIFT::GetInstance(); if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); } $this->_LoadActiveSessions(); $this->UserInterface->Header($this->Language->Get('staff') . ' > ' . $this->Language->Get('managestaff'), self::MENU_ID, self::NAVIGATION_ID); if ($_SWIFT->Staff->GetPermission('admin_canviewstaff') == '0') { $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm')); } else { $this->View->RenderGrid(); } $this->UserInterface->Footer(); return true; }  protected function _LoadActiveSessions() { if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); } $_threshold = DATENOW - 600;  $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "sessions
            WHERE sessiontype IN ('" . SWIFT_Interface::INTERFACE_STAFF . "', '" . SWIFT_Interface::INTERFACE_ADMIN . "', '" . SWIFT_Interface::INTERFACE_PDA . "', '" . SWIFT_Interface::INTERFACE_WINAPP . "', '" . SWIFT_Interface::INTERFACE_MOBILE . "', '" . SWIFT_Interface::INTERFACE_INSTAALERT . "', '" . SWIFT_Interface::INTERFACE_STAFFAPI . "') AND lastactivity >= '" . $_threshold . "'"); while ($this->Database->NextRecord()) { if (!in_array($this->Database->Record['typeid'], self::$_activeSessionList)) { self::$_activeSessionList[] = (int)($this->Database->Record['typeid']); } } return true; }  private function RunChecks($_mode, $_staffID = 0) { $_SWIFT = SWIFT::GetInstance(); if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); }   if (!isset($_POST['csrfhash']) || !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) { SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash')); return false; }  $_staffCache = $this->Cache->Get('staffcache'); $_staffGroupCache = $this->Cache->Get('staffgroupcache'); if (trim($_POST['firstname']) == '' || trim($_POST['lastname']) == '' || trim($_POST['username']) == '' || trim($_POST['email']) == '') { $this->UserInterface->CheckFields('firstname', 'lastname', 'username', 'password', 'passwordconfirm', 'email'); $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty')); return false; } elseif ($_mode == SWIFT_UserInterface::MODE_INSERT && (trim($_POST['password']) == '' || trim($_POST['passwordconfirm']) == '')) { SWIFT::ErrorField('password'); SWIFT::ErrorField('passwordconfirm'); $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty')); return false; } elseif ($_POST['password'] != $_POST['passwordconfirm']) { SWIFT::ErrorField('password'); SWIFT::ErrorField('passwordconfirm'); $this->UserInterface->Error($this->Language->Get('titlepwnomatch'), $this->Language->Get('msgpwnomatch')); return false; } elseif (!empty($_POST['password']) && !$this->StaffPasswordPolicy->Check($_POST['password'])) { SWIFT::ErrorField('password'); SWIFT::ErrorField('passwordconfirm'); $this->UserInterface->Error($this->Language->Get('titlepwpolicy'), $this->Language->Get('msgpwpolicy') . ' ' . $this->StaffPasswordPolicy->GetPasswordPolicyString()); return false; } elseif ($_POST['groupassigns'] == '0' && (!isset($_POST['assigned']) || !_is_array($_POST['assigned']))) { $this->UserInterface->Error($this->Language->Get('titlenodep'), $this->Language->Get('msgnodep')); return false; } elseif (!IsEmailValid($_POST['email'])) { SWIFT::ErrorField('email'); $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty')); return false; } elseif (trim($_POST['mobilenumber']) != '' && trim(preg_replace('/[^0-9]/', '', $_POST['mobilenumber'])) != $_POST['mobilenumber']) { SWIFT::ErrorField('mobilenumber'); $this->UserInterface->Error($this->Language->Get('titlemobilenumberinvalid'), $this->Language->Get('msgmobilenumberinvalid')); return false; } elseif (SWIFT::Get('isdemo') == true) { $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode')); return false; } if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_caninsertstaff') == '0') || ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_caneditstaff') == '0')) { $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm')); return false; } if (isset($_FILES['profileimage']) && is_uploaded_file($_FILES['profileimage']['tmp_name'])) { $_pathInfoContainer = pathinfo($_FILES['profileimage']['name']); if (isset($_pathInfoContainer['extension']) && !empty($_pathInfoContainer['extension'])) { $_pathInfoContainer['extension'] = strtolower($_pathInfoContainer['extension']); if ($_pathInfoContainer['extension'] != 'gif' && $_pathInfoContainer['extension'] != 'png' && $_pathInfoContainer['extension'] != 'jpg' && $_pathInfoContainer['extension'] != 'jpeg') { SWIFT::ErrorField('profileimage'); $this->UserInterface->Error($this->Language->Get('titleinvalidfileext'), $this->Language->Get('msginvalidfileext')); return false; } } }  if (_is_array($_staffCache)) { foreach ($_staffCache as $_key => $_val) { if (trim($_POST['username']) == $_val['username'] && (empty($_staffID) || $_staffID != $_val['staffid'])) { SWIFT::Error($this->Language->Get('titleusernameexists'), sprintf($this->Language->Get('msgusernameexists'), htmlspecialchars($_POST['username']), text_to_html_entities($_val['fullname']))); return false; } } }  if (_is_array($_staffCache)) { foreach ($_staffCache as $_key => $_val) { if (trim($_POST['email']) == $_val['email'] && (empty($_staffID) || $_staffID != $_val['staffid'])) { SWIFT::Error($this->Language->Get('titleemailexists'), sprintf($this->Language->Get('msgemailexists'), htmlspecialchars($_POST['email']), text_to_html_entities($_val['fullname']))); return false; } } }  if (SWIFT_App::IsInstalled(APP_PARSER)) { $this->Load->Model('EmailQueue:EmailQueue', [], false, false, APP_PARSER); if (SWIFT_EmailQueue::EmailQueueExistsWithEmail($_POST['email'])) { SWIFT::Error($this->Language->Get('titleemailqueuematch'), sprintf($this->Language->Get('msgemailqueuematch'), htmlspecialchars($_POST['email']))); return false; } }  $_activeStaffCount = SWIFT_Staff::ActiveStaffCount(); if ($_POST['isenabled'] == '1' && (($_mode == SWIFT_UserInterface::MODE_EDIT && $_staffCache[$_staffID]['isenabled'] == '0') || ($_mode == SWIFT_UserInterface::MODE_INSERT))) { $_activeStaffCount += 1; } if (SWIFT::Get('licensedstaff') != false && $_activeStaffCount > SWIFT::Get('licensedstaff')) { $this->UserInterface->Error($this->Language->Get('titlestafflicense'), $this->Language->Get('msgstafflicense')); return false; }  if ($_mode == SWIFT_UserInterface::MODE_EDIT) { $_doAdminCheck = true; if (_is_array($_staffCache)) { foreach ($_staffCache as $_key => $_val) { $_isAdmin = false; if (isset($_staffGroupCache[$_val['staffgroupid']]) && $_staffGroupCache[$_val['staffgroupid']]['isadmin'] == '1') { $_isAdmin = true; }  if ($_SWIFT->Staff->GetStaffID() != $_val['staffid'] && $_isAdmin == true) { $_doAdminCheck = false; break; } } }  if ($_doAdminCheck && $_SWIFT->Staff->IsAdmin() == true && $_staffID == $_SWIFT->Staff->GetStaffID() && isset($_staffGroupCache[$_POST['staffgroupid']]) && $_staffGroupCache[$_POST['staffgroupid']]['isadmin'] == 0) { SWIFT::Error($this->Language->Get('titlegrouperror'), $this->Language->Get('msggrouperror')); return false; }  if ($_POST['isenabled'] == '0' && $_SWIFT->Staff->GetStaffID() == $_staffID) { SWIFT::ErrorField('isenabled'); SWIFT::Error(sprintf($this->Language->Get('titlenoenable'), text_to_html_entities($_SWIFT->Staff->GetProperty('fullname'))), sprintf($this->Language->Get('msgnoenable'), text_to_html_entities($_SWIFT->Staff->GetProperty('fullname')))); return false; } }  unset($_hookCode); $_hookResult = null; ($_hookCode = SWIFT_Hook::Execute('admin_staff_runchecks')) ? ($_hookResult = eval($_hookCode)) : false; if ($_hookResult !== null) return $_hookResult;  return true; }  public function Insert() { $_SWIFT = SWIFT::GetInstance(); if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); } $this->UserInterface->Header($this->Language->Get('staff') . ' > ' . $this->Language->Get('insertstaff'), self::MENU_ID, self::NAVIGATION_ID); if ($_SWIFT->Staff->GetPermission('admin_caninsertstaff') == '0') { $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm')); } else { $this->View->Render(SWIFT_UserInterface::MODE_INSERT); } $this->UserInterface->Footer(); return true; }  private function _RenderConfirmation($_mode) { if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); } $_staffGroupCache = $this->Cache->Get('staffgroupcache'); if ($_mode == SWIFT_UserInterface::MODE_EDIT) { $_type = 'update'; } else { $_type = 'insert'; } if (!isset($_staffGroupCache[$_POST['staffgroupid']])) { throw new SWIFT_Exception(SWIFT_INVALIDDATA); } $_fullName = trim(text_to_html_entities($_POST['firstname']) . ' ' . text_to_html_entities($_POST['lastname'])); SWIFT::Info(sprintf($this->Language->Get('title' . $_type . 'staff'), $_fullName, htmlspecialchars($_POST['email'])), sprintf($this->Language->Get('msg' . $_type . 'staff'), $_fullName, $_fullName, htmlspecialchars($_POST['username']), htmlspecialchars($_POST['email']), htmlspecialchars($_staffGroupCache[$_POST['staffgroupid']]['title']), IIF($_POST['isenabled'] == '1', $this->Language->Get('yes'), $this->Language->Get('no')), htmlspecialchars($_POST['mobilenumber']), IIF($_POST['groupassigns'] == '1', $this->Language->Get('yes'), $this->Language->Get('no')))); return true; }  private function _ProcessAssignedDepartmentData() { $_SWIFT = SWIFT::GetInstance(); if (!isset($_POST['assigned']) || !_is_array($_POST['assigned'])) { return array(); } $_departmentCache = $_SWIFT->Cache->Get('departmentcache'); $_assignedDepartmentIDList = array(); foreach ($_POST['assigned'] as $_key => $_val) { if (!isset($_departmentCache[$_key])) { continue; } $_department = $_departmentCache[$_key]; $_parentDepartment = array(); if (!empty($_department['parentdepartmentid']) && isset($_departmentCache[$_department['parentdepartmentid']])) { $_parentDepartment = $_departmentCache[$_department['parentdepartmentid']]; } $_isAssigned = false; if ($_val) { $_isAssigned = true;  if (!empty($_parentDepartment['parentdepartmentid'])) { if (!isset($_POST['assigned'][$_parentDepartment['departmentid']]) || empty($_POST['assigned'][$_parentDepartment['departmentid']])) { $_isAssigned = false; } } } if ($_isAssigned) { $_assignedDepartmentIDList[] = $_key; } } return $_assignedDepartmentIDList; }  protected function _ProcessUploadedProfileImage(SWIFT_Model $_SWIFT_StaffObject) { if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); } elseif (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_INVALIDDATA); }   if (isset($_FILES['profileimage']) && is_uploaded_file($_FILES['profileimage']['tmp_name'])) { $maxFileSize = defined('MAXIMUM_UPLOAD_SIZE') ? MAXIMUM_UPLOAD_SIZE : 5242880; if ($_FILES['profileimage']['size'] > $maxFileSize) { SWIFT::Error($this->Language->Get('staffprofileimage') ?: 'Profile Picture', $this->Language->Get('wrong_image_size')); return false; } $_profileImage = SWIFT_StaffProfileImage::RetrieveOnStaff($_SWIFT_StaffObject->GetStaffID()); SWIFT_StaffProfileImage::DeleteOnStaff(array($_SWIFT_StaffObject->GetStaffID())); $_pathInfoContainer = pathinfo($_FILES['profileimage']['name']); if (isset($_pathInfoContainer['extension']) && !empty($_pathInfoContainer['extension'])) { $_ImageResizeObject = new SWIFT_ImageResize($_FILES['profileimage']['tmp_name']); $_ImageResizeObject->SetKeepProportions(true); $_fileContents = false; try { $_ImageResizeObject->Resize(); $_fileContents = base64_encode($_ImageResizeObject->Get()); } catch (SWIFT_Image_Exception $_SWIFT_Image_ExceptionObject) { if ($_profileImage) { $_fileContents = $_profileImage->GetProperty('imagedata'); unset($_profileImage); } SWIFT::Error($this->Language->Get('staffprofileimage'), $this->Language->Get('wrong_profile_image')); } if ($_fileContents) { SWIFT_StaffProfileImage::Create($_SWIFT_StaffObject->GetStaffID(), SWIFT_StaffProfileImage::TYPE_PUBLIC, $_pathInfoContainer['extension'], $_fileContents); } else { return false; } } } return true; }  protected function _ProcessChatSkills(SWIFT_Staff $_SWIFT_StaffObject) { if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); } elseif (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_INVALIDDATA); } if (!SWIFT_App::IsInstalled(APP_LIVECHAT)) { return false; } $_chatSkillIDList = array(); if (isset($_POST['skills'])) { foreach ($_POST['skills'] as $_key => $_val) { if ($_val == '1') { $_chatSkillIDList[] = $_key; } } } $this->Load->LoadModel('Skill:ChatSkill', APP_LIVECHAT); SWIFT_ChatSkill::Assign($_SWIFT_StaffObject->GetStaffID(), $_chatSkillIDList); return true; }  public function InsertSubmit() { $_SWIFT = SWIFT::GetInstance(); if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); } if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT)) { $_liveChatGreeting = ''; if (isset($_POST['greeting']) && !empty($_POST['greeting'])) { $_liveChatGreeting = $_POST['greeting']; } $_SWIFT_StaffObject = SWIFT_Staff::Create($_POST['firstname'], $_POST['lastname'], $_POST['designation'], $_POST['username'], $_POST['password'], $_POST['staffgroupid'], $_POST['email'], $_POST['mobilenumber'], $_POST['signature'], $_POST['groupassigns'], $_POST['isenabled'], $_liveChatGreeting, $_POST['iprestriction'], $_POST['timezonephp'], $_POST['enabledst']); if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CREATEFAILED); } $_groupAssigns = (int)($_POST['groupassigns']); SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityinsertstaff'), $_POST['firstname'] . ' ' . $_POST['lastname'], $_POST['email']), SWIFT_StaffActivityLog::ACTION_INSERT, SWIFT_StaffActivityLog::SECTION_STAFF, SWIFT_StaffActivityLog::INTERFACE_ADMIN); $this->_ProcessUploadedProfileImage($_SWIFT_StaffObject); $_permissionContainer = array(); if (isset($_POST['perm']) && _is_array($_POST['perm'])) { $_permissionContainer = $_POST['perm']; } SWIFT_StaffSettings::RebuildStaffSettings($_SWIFT_StaffObject->GetStaffID(), $_permissionContainer);  if (!$_groupAssigns) { $_assignedDepartmentIDList = $this->_ProcessAssignedDepartmentData(); SWIFT_StaffAssign::AssignStaffList($_SWIFT_StaffObject, $_assignedDepartmentIDList); } else { SWIFT_StaffAssign::DeleteList($_SWIFT_StaffObject); SWIFT_StaffAssign::RebuildCache(); }  $this->_ProcessChatSkills($_SWIFT_StaffObject);  SWIFT_CacheManager::EmptyCacheDirectory();  unset($_hookCode); ($_hookCode = SWIFT_Hook::Execute('admin_staff_insert')) ? eval($_hookCode) : false;  $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT); $this->Load->Manage(); return true; } $this->Load->Insert(); return false; }  public function Edit($_staffID) { $_SWIFT = SWIFT::GetInstance(); if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); } elseif (empty($_staffID)) { throw new SWIFT_Exception(SWIFT_INVALIDDATA); } $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_staffID)); if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_INVALIDDATA); } $this->UserInterface->Header($this->Language->Get('staff') . ' > ' . $this->Language->Get('editstaff'), self::MENU_ID, self::NAVIGATION_ID); if ($_SWIFT->Staff->GetPermission('admin_caneditstaff') == '0') { $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm')); } else { $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_StaffObject); } $this->UserInterface->Footer(); return true; }  public function EditSubmit($_staffID) { if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); } elseif (empty($_staffID)) { throw new SWIFT_Exception(SWIFT_INVALIDDATA); } $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_staffID)); if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_INVALIDDATA); } if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_StaffObject->GetStaffID())) { $_liveChatGreeting = ''; if (isset($_POST['greeting']) && !empty($_POST['greeting'])) { $_liveChatGreeting = $_POST['greeting']; } $_updateResult = $_SWIFT_StaffObject->Update($_POST['firstname'], $_POST['lastname'], $_POST['designation'], $_POST['username'], $_POST['password'], $_POST['staffgroupid'], $_POST['email'], $_POST['mobilenumber'], $_POST['signature'], $_POST['groupassigns'], $_POST['isenabled'], $_liveChatGreeting, $_POST['iprestriction'], $_POST['timezonephp'], $_POST['enabledst']); if (!$_updateResult) { throw new SWIFT_Exception(SWIFT_INVALIDDATA); } $_groupAssigns = (int)($_POST['groupassigns']); SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activityupdatestaff'), $_POST['firstname'] . ' ' . $_POST['lastname'], $_POST['email']), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_STAFF, SWIFT_StaffActivityLog::INTERFACE_ADMIN); $this->_ProcessUploadedProfileImage($_SWIFT_StaffObject); $_permissionContainer = array(); if (isset($_POST['perm']) && _is_array($_POST['perm'])) { $_permissionContainer = $_POST['perm']; } SWIFT_StaffSettings::RebuildStaffSettings($_SWIFT_StaffObject->GetStaffID(), $_permissionContainer);  if (!$_groupAssigns) { $_assignedDepartmentIDList = $this->_ProcessAssignedDepartmentData(); SWIFT_StaffAssign::AssignStaffList($_SWIFT_StaffObject, $_assignedDepartmentIDList); } else { SWIFT_StaffAssign::DeleteList($_SWIFT_StaffObject); SWIFT_StaffAssign::RebuildCache(); }  $this->_ProcessChatSkills($_SWIFT_StaffObject);  SWIFT_CacheManager::EmptyCacheDirectory();  unset($_hookCode); ($_hookCode = SWIFT_Hook::Execute('admin_staff_update')) ? eval($_hookCode) : false;  $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT); $this->Load->Manage(); return true; } $this->Load->Edit($_staffID); return false; }  public function GetProfileImage($_staffID = 0) { HeaderNoCache(); if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); } elseif (empty($_staffID) || !is_numeric($_staffID)) { return false; }  try { $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_staffID)); $_SWIFT_StaffProfileImageObject = SWIFT_StaffProfileImage::RetrieveOnStaff($_SWIFT_StaffObject->GetStaffID()); } catch (SWIFT_Exception $_SWIFT_ExceptionObject) { return false; } if (!$_SWIFT_StaffProfileImageObject instanceof SWIFT_StaffProfileImage || !$_SWIFT_StaffProfileImageObject->GetIsClassLoaded()) { return false; } $_SWIFT_StaffProfileImageObject->Output(); return true; }  public function ClearProfileImage($_staffID) { $_SWIFT = SWIFT::GetInstance(); if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); } elseif (empty($_staffID)) { throw new SWIFT_Exception(SWIFT_INVALIDDATA); } if ($_SWIFT->Staff->GetPermission('admin_caneditstaff') == '0') { $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm')); return false; } $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_staffID)); if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_INVALIDDATA); } SWIFT_StaffProfileImage::DeleteOnStaff(array($_staffID));  SWIFT_CacheManager::EmptyCacheDirectory(); $this->Load->Method('Edit', $_staffID); return true; }  public function GetInfo() { return false; } public function _LoadTemplateGroup($_templateGroupName = '') { return false; } public function _ProcessNews() { return false; } public function _DispatchError($_msg) { return false; } public function _DispatchConfirmation() { return false; } public function _ProcessKnowledgebaseCategories() { return false; } protected function _RunStep1(){ return false; } public function Console(){ return false; } public function ShowProgress($_title, $_msg){ return false; } public function StepProcessor(){ return false; } } ?>
