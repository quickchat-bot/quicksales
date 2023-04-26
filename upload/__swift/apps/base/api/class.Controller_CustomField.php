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

namespace Base\API;

use Controller_api;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;

/**
 * The CustomField API Controller
 *
 * @author Pavel Titkov
 */
class Controller_CustomField extends Controller_api implements SWIFT_REST_Interface
{

    /**
     * Constructor
     *
     * @author Pavel Titkov
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');
    }

    /**
     * GetList
     *
     * @author Pavel Titkov
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /*
         * BUG FIX - Ruchi Kothari
         *
         * SWIFT-2557 CustomField API does not return any Group type along with custom fields.
         *
         */
        $this->Database->Query("SELECT customfields.*, customfieldgroups.grouptype FROM " . TABLE_PREFIX . "customfields AS customfields
            LEFT JOIN " . TABLE_PREFIX . "customfieldgroups AS customfieldgroups ON (customfields.customfieldgroupid = customfieldgroups.customfieldgroupid)
            ORDER BY customfields.displayorder ASC");

        $this->XML->AddParentTag('customfields');
        while ($this->Database->NextRecord()) {
            $this->XML->AddTag('customfield', '', $this->Database->Record);
        }

        $this->XML->EndParentTag('customfields');

        $this->XML->EchoXML();

        return true;
    }

    /**
     * ListOptions
     *
     * @author Pavel Titkov
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ListOptions($_customFieldID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_customFieldID)) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'No Custom Field ID Provided');

            return false;
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldoptions
            WHERE customfieldid = '" . (int)($_customFieldID) . "'
            ORDER BY displayorder ASC");

        $this->XML->AddParentTag('customfieldoptions');
        while ($this->Database->NextRecord()) {
            $this->XML->AddTag('option', '', $this->Database->Record);
        }

        $this->XML->EndParentTag('customfieldoptions');

        $this->XML->EchoXML();

        return true;
    }

}
