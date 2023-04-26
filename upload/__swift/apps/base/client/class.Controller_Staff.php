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

                      namespace Base\Client; use Base\Models\Staff\SWIFT_Staff; use Base\Models\Staff\SWIFT_StaffProfileImage; use Controller_client; use SWIFT_DataID; use SWIFT_Exception;  class Controller_Staff extends Controller_client {  public function GetProfileImage($_staffID = 0) { HeaderNoCache(); if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); } else if (empty($_staffID) || !is_numeric($_staffID)) { $this->_DisplayEmptyImage(); return false; }  try { $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_staffID)); $_SWIFT_StaffProfileImageObject = SWIFT_StaffProfileImage::RetrieveOnStaff($_SWIFT_StaffObject->GetStaffID()); } catch (SWIFT_Exception $_SWIFT_ExceptionObject) { $this->_DisplayEmptyImage(); return false; } if (!$_SWIFT_StaffProfileImageObject instanceof SWIFT_StaffProfileImage || !$_SWIFT_StaffProfileImageObject->GetIsClassLoaded()) { $this->_DisplayEmptyImage(); return false; } $_SWIFT_StaffProfileImageObject->Output(); return true; }  protected function _DisplayEmptyImage() { if (!$this->GetIsClassLoaded()) { throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED); return false; } header('Content-Type: image/gif'); echo base64_decode('R0lGODlhAQABAIAAAP//////zCH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='); return true; }  public function GetCount() { $staff = $this->Database->QueryFetch("SELECT COUNT(1) AS total FROM " . TABLE_PREFIX . SWIFT_Staff::TABLE_NAME . " WHERE isenabled = 1"); $result = ['activestaffcount' => $staff['total']]; echo json_encode($result); } } ?>
