<?php
/**
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2016, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 *
 */

namespace Base\Library\CallHomeData;

use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * The Call home Data object
 *
 * @author Nidhi Gupta <nidhi.gupta@kayako.com>
 */
class SWIFT_CallHomeData extends SWIFT_Library
{
    /**
     * Constructor
     *
     * @author Nidhi Gupta <nidhi.gupta@kayako.com>
     *
     * @return bool
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Call home method
     *
     * @author Nidhi Gupta <nidhi.gupta@kayako.com>
     * @return bool
     * @throws SWIFT_Exception
     */
    public function CallHomeData()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_callHomeContainer = array();
        $_activeStaffCount = 0;

        if (SWIFT_App::IsInstalled(APP_BASE)) {

            $_activeStaffCountContainer = $this->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "staff WHERE isenabled = '1'");

            if (isset($_activeStaffCountContainer['totalitems'])) {
                $_activeStaffCount = $_activeStaffCountContainer['totalitems'];
            }

//            $_callHomeContainer = $this->Database->QueryFetchALL("SELECT staff.firstname, staff.lastname, staff.email, staff.designation, staff.timezonephp, staff.mobilenumber, staff.enabledst, staffgroup.title, staffgroup.isadmin, staff.isenabled FROM " . TABLE_PREFIX . "staff AS staff
//      LEFT JOIN  " . TABLE_PREFIX . "staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)");
//
//
//            while ($this->Database->NextRecord()) {
//                $_callHomeContainer[] = array(
//                    'firstname'    => $this->Database->Record['firstname'], 'lastname' => $this->Database->Record['lastname'], 'email' => $this->Database->Record['email'],
//                    'title'        => $this->Database->Record['title'], 'designation' => $this->Database->Record['designation'],
//                    'timezonephp'  => $this->Database->Record['timezonephp'],
//                    'mobilenumber' => $this->Database->Record['mobilenumber'], 'enabledst' => $this->Database->Record['enabledst'],
//                    'isenabled'    => $this->Database->Record['isenabled']
//                );
//            }
        }

        return @file_get_contents((base64_decode('aHR0cHM6Ly9teS5rYXlha28uY29tL0JhY2tlbmQvTGljZW5zZS9JbmRleC8=') . base64_encode('d=' . SWIFT::Get('basename') . '&c=' . (int)($_activeStaffCount) . '&v=' . SWIFT_VERSION)));
    }
}

