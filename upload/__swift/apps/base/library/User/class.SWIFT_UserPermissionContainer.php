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
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Library\User;

use SWIFT_App;
use SWIFT_Library;

/**
 * The Permission Container Class
 *
 * @author Varun Shoor
 */
class SWIFT_UserPermissionContainer extends SWIFT_Library
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retrieve the User Permission Container
     *
     * @author Varun Shoor
     * @return array The Staff Permission Container
     */
    public static function GetDefault()
    {
        $_userPermissionContainer = array(
            APP_CORE => array(
                'perm_canpostcomment',
            ),

            APP_TICKETS => array(
                'perm_canchangepriorities',
                'perm_sendautoresponder',
            ),

            APP_NEWS => array(
                'perm_cansubscribenews',
            ),
        );

        $_permissionContainer = array_merge($_userPermissionContainer, SWIFT_App::GetPermissionContainer('user'));

        return $_permissionContainer;
    }

}

?>
