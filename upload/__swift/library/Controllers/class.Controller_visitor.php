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

/**
 * The Visitor Controller Class
 *
 * @author Varun Shoor
 */
class Controller_visitor extends SWIFT_Controller
{
    // Core Constants
    const COOKIE_VISITOR = 'visitor';
    const COOKIE_VISITORSESSION = 'visitorsession';

    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
        parent::__construct();

        $this->InitializeVisitor();
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Initialize the Visitor Details
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function InitializeVisitor()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // Lets see if theres an entry for ban
        $this->Cookie->Parse(self::COOKIE_VISITORSESSION);
        $_banCookie = $this->Cookie->GetVariable(self::COOKIE_VISITORSESSION, 'isbanned');

        $_isBanned = false;

        if ($_banCookie == '')
        {
            // it seems like we never did check this visitor to see if hes banned check now
            $_banResult = $this->Database->QueryFetch("SELECT visitorbanid FROM ". TABLE_PREFIX ."visitorbans WHERE ipaddress = '". $this->Database->Escape(SWIFT::Get('IP')) ."'");
            if (trim($_banResult['visitorbanid']) != "")
            {
                // He is banned!
                $this->Cookie->AddVariable(self::COOKIE_VISITORSESSION, 'isbanned', '1');

                $_isBanned = true;
            } else {
                $this->Cookie->AddVariable(self::COOKIE_VISITORSESSION, 'isbanned', '0');

                $_isBanned = false;
            }

            $this->Cookie->Rebuild(self::COOKIE_VISITORSESSION);

        } else if ($_banCookie == '1') {

            $_isBanned = true;
        } else {
            $_isBanned = false;
        }

        // If the Visitor is Banned. we end the execution right here.
        if ($_isBanned)
        {
            log_error_and_exit();
        }

//        $this->Template->LoadTemplateGroup();

        return true;
    }
}
