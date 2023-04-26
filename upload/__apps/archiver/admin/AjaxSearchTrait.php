<?php

namespace Archiver\Admin;

use SWIFT_Exception;
use Base\Models\User\SWIFT_UserEmailManager;

trait AjaxSearchTrait
{
    /**
     * Searches using Auto Complete
     *
     * @author Werner Garcia (based on Varun Shoor code)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AjaxSearch()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_POST['q']) || empty($_POST['q'])) {
            return false;
        }

        $_emailContainer = $_emailMap = array();

        $_userIDList = array();
        $this->Database->QueryLimit('SELECT useremails . linktypeid
            FROM ' . TABLE_PREFIX . 'useremails AS useremails
            WHERE((' . BuildSQLSearch('useremails . email', $_POST['q'], false, false) . "))
                AND useremails.linktype = '" . SWIFT_UserEmailManager::LINKTYPE_USER . "'", 6);
        while ($this->Database->NextRecord()) {
            $_userIDList[] = $this->Database->Record['linktypeid'];
        }

        $this->Database->QueryLimit('SELECT users . userid FROM ' . TABLE_PREFIX . 'users AS users
            WHERE((' . BuildSQLSearch('users . fullname', $_POST['q'], false, false) . '))', 6);
        while ($this->Database->NextRecord()) {
            if (!in_array($this->Database->Record['userid'], $_userIDList, true)) {
                $_userIDList[] = $this->Database->Record['userid'];
            }
        }

        $_userOrganizationIDList = array();
        $this->Database->QueryLimit('SELECT userorganizations . userorganizationid
            FROM ' . TABLE_PREFIX . 'userorganizations AS userorganizations
            WHERE((' . BuildSQLSearch('userorganizations . organizationname', $_POST['q'], false, false) . '))', 6);
        while ($this->Database->NextRecord()) {
            $_userOrganizationIDList[] = $this->Database->Record['userorganizationid'];
        }

        if (count($_userOrganizationIDList)) {
            $this->Database->QueryLimit('SELECT users . userid
                FROM ' . TABLE_PREFIX . 'users AS users
                WHERE users . userorganizationid IN(' . BuildIN($_userOrganizationIDList) . ')', 6);
            while ($this->Database->NextRecord()) {
                if (!in_array($this->Database->Record['userid'], $_userIDList, true)) {
                    $_userIDList[] = $this->Database->Record['userid'];
                }
            }
        }

        $this->Database->QueryLimit('SELECT useremails .*, users . fullname, users . userid, userorganizations . organizationname
            FROM ' . TABLE_PREFIX . 'useremails AS useremails
            LEFT JOIN ' . TABLE_PREFIX . 'users AS users ON(useremails . linktypeid = users . userid)
            LEFT JOIN ' . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            WHERE useremails.linktype = '" . SWIFT_UserEmailManager::LINKTYPE_USER . "' AND useremails.linktypeid IN (" . BuildIN($_userIDList) . ')',
            6);
        while ($this->Database->NextRecord()) {
            if (in_array($this->Database->Record['email'], $_emailContainer, true)) {
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

            if (isset($_emailMapLink['fullname']) && !empty($_emailMapLink['fullname'])) {
                $_finalDisplayText .= text_to_html_entities($_emailMapLink['fullname']);
            }

            if (isset($_emailMapLink['organizationname']) && !empty($_emailMapLink['organizationname'])) {
                $_finalDisplayText .= ' (' . text_to_html_entities($_emailMapLink['organizationname']) . ')';
            }
            $_finalDisplayText .= '<br/>';
            $_finalDisplayText .= htmlspecialchars($_emailMapLink['email']);

            echo str_replace('|', '', $_finalDisplayText) . '|' . $_emailMapLink['fullname'] . '|' . str_replace('|',
                    '', htmlspecialchars($_emailMapLink['email'])) . SWIFT_CRLF;
        }

        return true;
    }

}
