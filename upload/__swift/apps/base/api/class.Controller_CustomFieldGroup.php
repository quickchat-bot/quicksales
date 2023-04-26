<?php

/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    Kayako Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-Kayako Singapore Pte. Ltd.h Ltd.
 * @license    http://www.kayako.com/license
 * @link        http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

namespace Base\API;

use Controller_api;
use SWIFT_Exception;
use SWIFT_REST_Interface;

/**
 * The CustomFieldGroup API Controller
 *
 * @author Ruchi Kothari
 */
class Controller_CustomFieldGroup extends Controller_api implements SWIFT_REST_Interface
{
    /**
     * GetList
     *
     * @author Ruchi Kothari
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        $this->ProcessCustomFieldGroups();

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve custom field groups based on departments
     *
     * @author Amarjeet Kaur
     * @param int $_departmentID department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_departmentID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ProcessCustomFieldGroups($_departmentID);

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Process CustomField Groups
     *
     * @author Amarjeet Kaur
     * @param int $_departmentID department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessCustomFieldGroups($_departmentID = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!empty($_departmentID)) {

            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldgroups AS customfieldgroups INNER JOIN " . TABLE_PREFIX . "customfielddeplinks AS customfielddeplinks
            ON customfieldgroups.customfieldgroupid = customfielddeplinks.customfieldgroupid WHERE customfielddeplinks.departmentid = '" . $_departmentID . "' ORDER BY displayorder ASC");

        } else {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldgroups ORDER BY displayorder ASC");
        }

        $this->XML->AddParentTag('customfieldgroups');

        while ($this->Database->NextRecord()) {
            $this->XML->AddTag('customfieldgroup', '', $this->Database->Record);
        }

        $this->XML->EndParentTag('customfieldgroups');

        return true;
    }
}
