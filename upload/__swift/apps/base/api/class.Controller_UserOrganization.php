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
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Base\API;

use Base\Models\User\SWIFT_UserOrganization;
use Controller_api;
use SWIFT;
use SWIFT_Exception;
use SWIFT_REST_Interface;
use SWIFT_RESTServer;

/**
 * The UserOrganization API Controller
 *
 * @author Varun Shoor
 */
class Controller_UserOrganization extends Controller_api implements SWIFT_REST_Interface
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
     * Retrieve & Dispatch the User Organizations
     *
     * @author Varun Shoor
     * @param int $_userOrganizationID (OPTIONAL) The User Organization ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function ProcessUserOrganizations($_userOrganizationID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_userOrganizationContainer = array();

        if (!empty($_userOrganizationID)) {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "userorganizations WHERE userorganizationid = '" . ($_userOrganizationID) . "'");
        } else {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "userorganizations ORDER BY userorganizationid ASC");
        }

        while ($this->Database->NextRecord()) {
            $_userOrganizationContainer[$this->Database->Record['userorganizationid']] = $this->Database->Record;
        }

        $this->XML->AddParentTag('userorganizations');
        foreach ($_userOrganizationContainer as $_userOrganizationID => $_userOrganization) {
            $_userOrganizationType = 'restricted';

            if ($_userOrganization['organizationtype'] == SWIFT_UserOrganization::TYPE_SHARED) {
                $_userOrganizationType = 'shared';
            }

            $this->XML->AddParentTag('userorganization');
            $this->XML->AddTag('id', $_userOrganizationID);
            $this->XML->AddTag('name', $_userOrganization['organizationname']);
            $this->XML->AddTag('organizationtype', $_userOrganizationType);
            $this->XML->AddTag('address', $_userOrganization['address']);
            $this->XML->AddTag('city', $_userOrganization['city']);
            $this->XML->AddTag('state', $_userOrganization['state']);
            $this->XML->AddTag('postalcode', $_userOrganization['postalcode']);
            $this->XML->AddTag('country', $_userOrganization['country']);
            $this->XML->AddTag('phone', $_userOrganization['phone']);
            $this->XML->AddTag('fax', $_userOrganization['fax']);
            $this->XML->AddTag('website', $_userOrganization['website']);

            $this->XML->AddComment('Timeline Properties');
            $this->XML->AddTag('dateline', $_userOrganization['dateline']);
            $this->XML->AddTag('lastupdate', $_userOrganization['lastupdate']);

            $this->XML->AddComment('Custom SLA Properties');
            $this->XML->AddTag('slaplanid', $_userOrganization['slaplanid']);
            $this->XML->AddTag('slaplanexpiry', $_userOrganization['slaexpirytimeline']);
            $this->XML->EndParentTag('userorganization');
        }
        $this->XML->EndParentTag('userorganizations');

        return true;
    }

    /**
     * Get a list of User Organizations
     *
     * Example Output:
     *
     * <userorganizations>
     *    <userorganization>
     *        <id>1</id>
     *        <name>QuickSupport Infotech Ltd.</name>
     *        <organizationtype>restricted</organizationtype>
     *        <address>2nd Floor, Midas Corporate Park, 37 GT Road</address>
     *        <city>Jalandhar</city>
     *        <state>Punjab</state>
     *        <postalcode>144001</postalcode>
     *        <country>India</country>
     *        <phone />
     *        <fax />
     *        <website>http://www.opencart.com.vn</website>
     *
     *        <!-- Timeline Properties -->
     *        <dateline><![CDATA[1296540309]]></dateline>
     *        <lastupdate><![CDATA[1296540309]]></lastupdate>
     *
     *        <!-- Custom SLA Properties -->
     *        <slaplanid><![CDATA[0]]></slaplanid>
     *        <slaplanexpiry><![CDATA[0]]></slaplanexpiry>
     *    </userorganization>
     * </userorganizations>
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->ProcessUserOrganizations();

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Retrieve the User Organization
     *
     * Example Output:
     *
     * <userorganizations>
     *    <userorganization>
     *        <id>1</id>
     *        <name>QuickSupport Infotech Ltd.</name>
     *        <organizationtype>restricted</organizationtype>
     *        <address>2nd Floor, Midas Corporate Park, 37 GT Road</address>
     *        <city>Jalandhar</city>
     *        <state>Punjab</state>
     *        <postalcode>144001</postalcode>
     *        <country>India</country>
     *        <phone />
     *        <fax />
     *        <website>http://www.opencart.com.vn</website>
     *
     *        <!-- Timeline Properties -->
     *        <dateline><![CDATA[1296540309]]></dateline>
     *        <lastupdate><![CDATA[1296540309]]></lastupdate>
     *
     *        <!-- Custom SLA Properties -->
     *        <slaplanid><![CDATA[0]]></slaplanid>
     *        <slaplanexpiry><![CDATA[0]]></slaplanexpiry>
     *    </userorganization>
     * </userorganizations>
     *
     * @author Varun Shoor
     * @param string $_userOrganizationID The User Organization ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_userOrganizationID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->ProcessUserOrganizations((int)($_userOrganizationID));

        $this->XML->EchoXML();

        return true;
    }

    /**
     * Create a User Organization
     *
     * Required Fields:
     * name
     * organizationtype
     *
     * Example Output:
     *
     * <userorganizations>
     *    <userorganization>
     *        <id>1</id>
     *        <name>QuickSupport Infotech Ltd.</name>
     *        <organizationtype>restricted</organizationtype>
     *        <address>2nd Floor, Midas Corporate Park, 37 GT Road</address>
     *        <city>Jalandhar</city>
     *        <state>Punjab</state>
     *        <postalcode>144001</postalcode>
     *        <country>India</country>
     *        <phone />
     *        <fax />
     *        <website>http://www.opencart.com.vn</website>
     *
     *        <!-- Timeline Properties -->
     *        <dateline><![CDATA[1296540309]]></dateline>
     *        <lastupdate><![CDATA[1296540309]]></lastupdate>
     *
     *        <!-- Custom SLA Properties -->
     *        <slaplanid><![CDATA[0]]></slaplanid>
     *        <slaplanexpiry><![CDATA[0]]></slaplanexpiry>
     *    </userorganization>
     * </userorganizations>
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_slaPlanCache = $this->Cache->Get('slaplancache');
        if (isset($_POST['slaplanid']) && !empty($_POST['slaplanid']) && !isset($_slaPlanCache[$_POST['slaplanid']])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid SLA Plan Specified');

            return false;
        }

        if (isset($_POST['name']) && trim($_POST['name']) != '' && !empty($_POST['name']) && isset($_POST['organizationtype']) && ($_POST['organizationtype'] == 'restricted' || $_POST['organizationtype'] == 'shared')) {
            $_organizationType = SWIFT_UserOrganization::TYPE_RESTRICTED;
            if ($_POST['organizationtype'] == 'shared') {
                $_organizationType = SWIFT_UserOrganization::TYPE_SHARED;
            }

            $_emailContainer = array();

            $_address = $_city = $_state = $_postalCode = $_country = $_phone = $_fax = $_website = '';
            $_slaPlanID = $_slaPlanExpiry = '';

            if (isset($_POST['address']) && !empty($_POST['address'])) {
                $_address = $_POST['address'];
            }

            if (isset($_POST['city']) && !empty($_POST['city'])) {
                $_city = $_POST['city'];
            }

            if (isset($_POST['state']) && !empty($_POST['state'])) {
                $_state = $_POST['state'];
            }

            if (isset($_POST['postalcode']) && !empty($_POST['postalcode'])) {
                $_postalCode = $_POST['postalcode'];
            }

            if (isset($_POST['country']) && !empty($_POST['country'])) {
                $_country = $_POST['country'];
            }

            if (isset($_POST['phone']) && !empty($_POST['phone'])) {
                $_phone = $_POST['phone'];
            }

            if (isset($_POST['fax']) && !empty($_POST['fax'])) {
                $_fax = $_POST['fax'];
            }

            if (isset($_POST['website']) && !empty($_POST['website'])) {
                $_website = $_POST['website'];
            }

            if (isset($_POST['slaplanid']) && !empty($_POST['slaplanid'])) {
                $_slaPlanID = (int)($_POST['slaplanid']);
            }

            if (isset($_POST['slaplanexpiry']) && !empty($_POST['slaplanexpiry'])) {
                $_slaPlanExpiry = (int)($_POST['slaplanexpiry']);
            }


            $_SWIFT_UserOrganizationObject = SWIFT_UserOrganization::Create($_POST['name'], $_organizationType, $_emailContainer, $_address, $_city, $_state, $_postalCode, $_country, $_phone, $_fax, $_website, $_slaPlanID, $_slaPlanExpiry);
            if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                // @codeCoverageIgnoreStart
                // will not be reached
                $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'User Organization Creation Failed');

                return false;
            }
            // @codeCoverageIgnoreEnd

            $this->ProcessUserOrganizations($_SWIFT_UserOrganizationObject->GetUserOrganizationID());

            $this->XML->EchoXML();

        } else {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'One of the required fields is empty');

            return false;
        }

        return false;
    }

    /**
     * Update the User Organization ID
     *
     * Required Fields:
     * name
     *
     * Example Output:
     *
     * <userorganizations>
     *    <userorganization>
     *        <id>1</id>
     *        <name>QuickSupport Infotech Ltd.</name>
     *        <organizationtype>restricted</organizationtype>
     *        <address>2nd Floor, Midas Corporate Park, 37 GT Road</address>
     *        <city>Jalandhar</city>
     *        <state>Punjab</state>
     *        <postalcode>144001</postalcode>
     *        <country>India</country>
     *        <phone />
     *        <fax />
     *        <website>http://www.opencart.com.vn</website>
     *
     *        <!-- Timeline Properties -->
     *        <dateline><![CDATA[1296540309]]></dateline>
     *        <lastupdate><![CDATA[1296540309]]></lastupdate>
     *
     *        <!-- Custom SLA Properties -->
     *        <slaplanid><![CDATA[0]]></slaplanid>
     *        <slaplanexpiry><![CDATA[0]]></slaplanexpiry>
     *    </userorganization>
     * </userorganizations>
     *
     * @author Varun Shoor
     * @param int $_userOrganizationID The User Organization ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_userOrganizationID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_UserOrganizationObject = false;

        $_errorMessage = '';

        try {
            $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_errorMessage = ': ' . $_SWIFT_ExceptionObject->getMessage();
        }

        if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'User Organization Load Failed' . $_errorMessage);

            return false;
        }

        $_slaPlanCache = $this->Cache->Get('slaplancache');
        if (isset($_POST['slaplanid']) && !empty($_POST['slaplanid']) && !isset($_slaPlanCache[$_POST['slaplanid']])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'Invalid SLA Plan Specified');

            return false;
        }

        if (isset($_POST['name']) && trim($_POST['name']) != '' && !empty($_POST['name'])) {
            $_organizationType = $_SWIFT_UserOrganizationObject->GetProperty('organizationtype');

            if (isset($_POST['organizationtype']) && ($_POST['organizationtype'] == 'restricted' || $_POST['organizationtype'] == 'shared')) {
                if ($_POST['organizationtype'] == 'shared') {
                    $_organizationType = SWIFT_UserOrganization::TYPE_SHARED;
                } else if ($_POST['organizationtype'] == 'restricted') {
                    $_organizationType = SWIFT_UserOrganization::TYPE_RESTRICTED;
                }
            }

            $_emailContainer = array();

            $_address = $_city = $_state = $_postalCode = $_country = $_phone = $_fax = $_website = '';
            $_slaPlanID = $_slaPlanExpiry = '';

            if (isset($_POST['address']) && !empty($_POST['address'])) {
                $_address = $_POST['address'];
            }

            if (isset($_POST['city']) && !empty($_POST['city'])) {
                $_city = $_POST['city'];
            }

            if (isset($_POST['state']) && !empty($_POST['state'])) {
                $_state = $_POST['state'];
            }

            if (isset($_POST['postalcode']) && !empty($_POST['postalcode'])) {
                $_postalCode = $_POST['postalcode'];
            }

            if (isset($_POST['country']) && !empty($_POST['country'])) {
                $_country = $_POST['country'];
            }

            if (isset($_POST['phone']) && !empty($_POST['phone'])) {
                $_phone = $_POST['phone'];
            }

            if (isset($_POST['fax']) && !empty($_POST['fax'])) {
                $_fax = $_POST['fax'];
            }

            if (isset($_POST['website']) && !empty($_POST['website'])) {
                $_website = $_POST['website'];
            }

            if (isset($_POST['slaplanid']) && !empty($_POST['slaplanid'])) {
                $_slaPlanID = (int)($_POST['slaplanid']);
            }

            if (isset($_POST['slaplanexpiry']) && !empty($_POST['slaplanexpiry'])) {
                $_slaPlanExpiry = (int)($_POST['slaplanexpiry']);
            }

            $_SWIFT_UserOrganizationObject->Update($_POST['name'], $_organizationType, $_emailContainer, $_address, $_city, $_state, $_postalCode, $_country, $_phone, $_fax, $_website, $_slaPlanID, $_slaPlanExpiry);

            $this->ProcessUserOrganizations($_SWIFT_UserOrganizationObject->GetUserOrganizationID());

            $this->XML->EchoXML();

        } else {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'One of the required fields is empty');

            return false;
        }

        return true;
    }

    /**
     * Delete a User Organization
     *
     * Example Output:
     * No output is sent, if server returns HTTP Code 200, then the deletion was successful
     *
     * @author Varun Shoor
     * @param int $_userOrganizationID The User Organization ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_userOrganizationID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_SWIFT_UserOrganizationObject = false;

        $_errorMessage = '';

        try {
            $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($_userOrganizationID);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            $_errorMessage = ': ' . $_SWIFT_ExceptionObject->getMessage();
        }

        if (!$_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization || !$_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_BADREQUEST, 'User Organization Load Failed' . $_errorMessage);

            return false;
        }

        SWIFT_UserOrganization::DeleteList(array($_userOrganizationID));

        return true;
    }
}

?>
