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

/**
 * The Console Controller Class
 *
 * @author Varun Shoor
 */
class Controller_console extends SWIFT_Controller
{
    /**
     * Constructor
     *
     * @param bool $_runPendingTasks Run pending tasks
     *
     * @author Varun Shoore
     */
    public function __construct($_runPendingTasks = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        parent::__construct();

        // If its the cluster class then we attempt to load the server
        if ($_SWIFT->Router->GetApp()->GetName() == APP_CLUSTER && ($_SWIFT->Router->GetAction() != 'CheckInstalled' || ($_SWIFT->Router->GetAction() == 'CheckInstalled' && file_exists('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CONFIG_DIRECTORY . '/server.xml'))))
        {
            if (file_exists('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CONFIG_DIRECTORY . '/server.xml')) {
                $this->Load->AppLibrary(APP_CLUSTER, 'Cluster:Cluster', array('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CONFIG_DIRECTORY . '/server.xml'));

                // Halt executition if we cannot load the cluster data
                if (!$this->Cluster instanceof SWIFT_Library || !$this->Cluster->GetIsClassLoaded())
                {
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                }

                $_SWIFT->SetClass('Cluster', $this->Cluster);
            } else {
                $this->Load->AppLibrary(APP_CLUSTER, 'Cluster:Cluster', false, false);

                $class = 'SWIFT_Cluster';
                $_result = $class::RetrieveInstallXML();
                if (!$_result) {
                    log_error_and_exit();
                }

                $this->Load->AppLibrary(APP_CLUSTER, 'Cluster:Cluster', array('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CONFIG_DIRECTORY . '/server.xml'));

                // Halt executition if we cannot load the cluster data
                if (!$this->Cluster instanceof SWIFT_Library || !$this->Cluster->GetIsClassLoaded())
                {
                    throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                }

                $_SWIFT->SetClass('Cluster', $this->Cluster);
            }
        } else if ($_runPendingTasks) {
            SWIFT_CronManager::RunPendingTasks();
        }
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
}
?>
