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

namespace Base\Intranet;

use Base\Library\ProfileImage\SWIFT_ProfileImage;
use Base\Models\User\SWIFT_UserEmailManager;
use Controller_intranet;
use SWIFT;
use SWIFT_Exception;

/**
 * The User Controller
 *
 * @author Varun Shoor
 */
class Controller_User extends Controller_intranet
{
    // Core Constants
    const MENU_ID = 8;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        $_SWIFT = SWIFT::GetInstance();

        parent::__construct();

        $this->Load->Library('User:UserRenderManager', [], true, false, 'base');

        $this->Load->Library('Misc:TimeZoneContainer');

        $this->Load->Library('CustomField:CustomFieldRendererStaff', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        $this->Language->Load('staff_users');

        if ($_SWIFT->Staff->GetPermission('cu_entab') == '0') {
            throw new SWIFT_Exception(SWIFT_NOPERMISSION);
        }
    }

    /**
     * Searches using Auto Complete
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AjaxSearch()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!isset($_POST['q']) || empty($_POST['q'])) {
            return false;
        }

        $_emailContainer = $_emailMap = array();

        $_userIDList = array();
        $this->Database->QueryLimit("SELECT useremails.linktypeid
            FROM " . TABLE_PREFIX . "useremails AS useremails
            WHERE ((" . BuildSQLSearch('useremails.email', $_POST['q'], false, false) . "))
                AND useremails.linktype = '" . SWIFT_UserEmailManager::LINKTYPE_USER . "'", 50);
        while ($this->Database->NextRecord()) {
            $_userIDList[] = $this->Database->Record['linktypeid'];
        }

        $this->Database->QueryLimit("SELECT users.userid FROM " . TABLE_PREFIX . "users AS users
            WHERE ((" . BuildSQLSearch('users.fullname', $_POST['q'], false, false) . ")
                OR (" . BuildSQLSearch('users.phone', $_POST['q'], false, false) . "))", 50);
        while ($this->Database->NextRecord()) {
            if (!in_array($this->Database->Record['userid'], $_userIDList)) {
                $_userIDList[] = $this->Database->Record['userid'];
            }
        }

        $_userOrganizationIDList = array();
        $this->Database->QueryLimit("SELECT userorganizations.userorganizationid
            FROM " . TABLE_PREFIX . "userorganizations AS userorganizations
            WHERE ((" . BuildSQLSearch('userorganizations.organizationname', $_POST['q'], false, false) . ")
                OR (" . BuildSQLSearch('userorganizations.address', $_POST['q'], false, false) . ")
                OR (" . BuildSQLSearch('userorganizations.phone', $_POST['q'], false, false) . ")
                )", 50);
        while ($this->Database->NextRecord()) {
            $_userOrganizationIDList[] = $this->Database->Record['userorganizationid'];
        }

        if (count($_userOrganizationIDList)) {
            $this->Database->QueryLimit("SELECT users.userid
                FROM " . TABLE_PREFIX . "users AS users
                WHERE users.userorganizationid IN (" . BuildIN($_userOrganizationIDList) . ")", 50);
            while ($this->Database->NextRecord()) {
                if (!in_array($this->Database->Record['userid'], $_userIDList)) {
                    $_userIDList[] = $this->Database->Record['userid'];
                }
            }
        }


        $this->Database->QueryLimit("SELECT useremails.*, users.fullname, users.phone AS userphone, users.userid, userorganizations.organizationname, userorganizations.phone AS organizationphone, userorganizations.address, userorganizations.city, userorganizations.state, userorganizations.country
            FROM " . TABLE_PREFIX . "useremails AS useremails
            LEFT JOIN " . TABLE_PREFIX . "users AS users ON (useremails.linktypeid = users.userid)
            LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            WHERE useremails.linktype = '" . SWIFT_UserEmailManager::LINKTYPE_USER . "' AND useremails.linktypeid IN (" . BuildIN($_userIDList) . ")", 50);
        while ($this->Database->NextRecord()) {
            if (in_array($this->Database->Record['email'], $_emailContainer)) {
                continue;
            }

            $_emailContainer[] = $this->Database->Record['email'];
            $_emailMap[$this->Database->Record['email']] = $this->Database->Record;

            if (isset($this->Database->Record['userid']) && !empty($this->Database->Record['userid'])) {
                $_emailMap['userid'] = $this->Database->Record['userid'];
            } else {
                $_emailMap['userid'] = $this->Database->Record['linktypeid'];
            }
        }

        sort($_emailContainer);

        foreach ($_emailContainer as $_emailAddress) {
            $_emailMapLink = $_emailMap[$_emailAddress];

            $_finalDisplayText = '';

            // Varun Shoor (QuickSupport Infotech Ltd.)
            // 2nd Floor, Midas Corporate Park
            // Jalandhar, Punjab, India
            // Organization Phone: +91 181xxx
            // User Phone: +91 xxx
            $_finalDisplayText .= 'User ID: ' . htmlspecialchars($_emailMapLink['userid']) . '<br />';
            $_finalDisplayText .= htmlspecialchars($_emailAddress) . '<br />';
            if (isset($_emailMapLink['fullname']) && !empty($_emailMapLink['fullname'])) {
                $_finalDisplayText .= text_to_html_entities($_emailMapLink['fullname']);
            }

            if (isset($_emailMapLink['organizationname']) && !empty($_emailMapLink['organizationname'])) {
                $_finalDisplayText .= ' (' . text_to_html_entities($_emailMapLink['organizationname']) . ')';
            }

            if (!empty($_finalDisplayText)) {
                $_finalDisplayText .= '<br />';
            }

            if (isset($_emailMapLink['address']) && !empty($_emailMapLink['address'])) {
                $_finalDisplayText .= preg_replace("#(\r\n|\r|\n)#s", '', nl2br(htmlspecialchars($_emailMapLink['address']))) . '<br />';
            }

            if ((isset($_emailMapLink['city']) && !empty($_emailMapLink['city'])) ||
                (isset($_emailMapLink['state']) && !empty($_emailMapLink['state'])) ||
                (isset($_emailMapLink['country']) && !empty($_emailMapLink['counrty']))) {
                $_extendedAddress = '';
                if (!empty($_emailMapLink['city']) && !empty($_emailMapLink['state'])) {
                    $_extendedAddress .= htmlspecialchars($_emailMapLink['city']) . ', ' . htmlspecialchars($_emailMapLink['state']);
                } else if (!empty($_emailMapLink['city'])) {
                    $_extendedAddress .= htmlspecialchars($_emailMapLink['city']);

                } else if (!empty($_emailMapLink['state'])) {
                    $_extendedAddress .= htmlspecialchars($_emailMapLink['state']);

                }

                if (!empty($_emailMapLink['country'])) {
                    if (empty($_extendedAddress)) {
                        $_extendedAddress .= htmlspecialchars($_emailMapLink['country']);

                    } else {
                        $_extendedAddress .= ' - ' . htmlspecialchars($_emailMapLink['country']);
                    }
                }

                if (!empty($_extendedAddress)) {
                    $_extendedAddress .= '<br />';
                }

                $_finalDisplayText .= $_extendedAddress;
            }

            if (isset($_emailMapLink['organizationphone']) && !empty($_emailMapLink['organizationphone'])) {
                $_finalDisplayText .= sprintf($this->Language->Get('phoneext'), htmlspecialchars($_emailMapLink['organizationphone'])) . '<br />';
            }

            if (isset($_emailMapLink['userphone']) && !empty($_emailMapLink['userphone'])) {
                $_finalDisplayText .= sprintf($this->Language->Get('phoneext'), htmlspecialchars($_emailMapLink['userphone'])) . '<br />';
            }

            echo str_replace('|', '', $_finalDisplayText) . '|' . (int)($_emailMapLink['userid']) . '|' . str_replace('|', '', text_to_html_entities($_emailMapLink['fullname'])) . SWIFT_CRLF;
        }

        return true;
    }

    /**
     * Display the Avatar
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @param string $_emailAddressHash (OPTIONAL) The Email Address Hash
     * @param int $_preferredWidth (OPTIONAL) The Preferred Width
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DisplayAvatar($_userID, $_emailAddressHash = '', $_preferredWidth = 60)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        SWIFT_ProfileImage::OutputOnUserID($_userID, $_emailAddressHash, $_preferredWidth);

        return true;
    }
}

?>
