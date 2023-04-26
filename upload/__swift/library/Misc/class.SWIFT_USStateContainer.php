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
 * The United States State Container
 *
 * @author Varun Shoor
 */
class SWIFT_USStateContainer extends SWIFT_Library {
    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct() {
        parent::__destruct();
    }

    /**
     * The US State Container
     *
     * @author Varun Shoor
     * @return array The State Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_stateContainer = array();

        $_stateContainer['AL'] = 'Alabama';
        $_stateContainer['AK'] = 'Alaska';
        $_stateContainer['AS'] = 'American Samoa';
        $_stateContainer['AZ'] = 'Arizona';
        $_stateContainer['AR'] = 'Arkansas';
        $_stateContainer['CA'] = 'California';
        $_stateContainer['CO'] = 'Colorado';
        $_stateContainer['CT'] = 'Connecticut';
        $_stateContainer['DE'] = 'Delaware';
        $_stateContainer['DC'] = 'District of Columbia';
        $_stateContainer['FM'] = 'Federated States of Micronesia';
        $_stateContainer['FL'] = 'Florida';
        $_stateContainer['GA'] = 'Georgia';
        $_stateContainer['GU'] = 'Guam';
        $_stateContainer['HI'] = 'Hawaii';
        $_stateContainer['ID'] = 'Idaho';
        $_stateContainer['IL'] = 'Illinois';
        $_stateContainer['IN'] = 'Indiana';
        $_stateContainer['IA'] = 'Iowa';
        $_stateContainer['KS'] = 'Kansas';
        $_stateContainer['KY'] = 'Kentucky';
        $_stateContainer['LA'] = 'Louisiana';
        $_stateContainer['ME'] = 'Maine';
        $_stateContainer['MH'] = 'Marshall Islands';
        $_stateContainer['MD'] = 'Maryland';
        $_stateContainer['MA'] = 'Massachusetts';
        $_stateContainer['MI'] = 'Michigan';
        $_stateContainer['MN'] = 'Minnesota';
        $_stateContainer['MS'] = 'Mississippi';
        $_stateContainer['MO'] = 'Missouri';
        $_stateContainer['MT'] = 'Montana';
        $_stateContainer['NE'] = 'Nebraska';
        $_stateContainer['NV'] = 'Nevada';
        $_stateContainer['NH'] = 'New Hampshire';
        $_stateContainer['NJ'] = 'New Jersey';
        $_stateContainer['NM'] = 'New Mexico';
        $_stateContainer['NY'] = 'New York';
        $_stateContainer['NC'] = 'North Carolina';
        $_stateContainer['ND'] = 'North Dakota';
        $_stateContainer['MP'] = 'Northern Mariana Islands';
        $_stateContainer['OH'] = 'Ohio';
        $_stateContainer['OK'] = 'Oklahoma';
        $_stateContainer['OR'] = 'Oregon';
        $_stateContainer['PW'] = 'Palau';
        $_stateContainer['PA'] = 'Pennsylvania';
        $_stateContainer['PR'] = 'Puerto Rico';
        $_stateContainer['RI'] = 'Rhode Island';
        $_stateContainer['SC'] = 'South Carolina';
        $_stateContainer['SD'] = 'South Dakota';
        $_stateContainer['TN'] = 'Tennessee';
        $_stateContainer['TX'] = 'Texas';
        $_stateContainer['UT'] = 'Utah';
        $_stateContainer['VT'] = 'Vermont';
        $_stateContainer['VI'] = 'Virgin Islands';
        $_stateContainer['VA'] = 'Virginia';
        $_stateContainer['WA'] = 'Washington';
        $_stateContainer['WV'] = 'West Virginia';
        $_stateContainer['WI'] = 'Wisconsin';
        $_stateContainer['WY'] = 'Wyoming';
        $_stateContainer['AE'] = 'Armed Forces Middle East';
        $_stateContainer['AA'] = 'Armed Forces Americas (except Canada)';
        $_stateContainer['AP'] = 'Armed Forces Pacific';
        
        return $_stateContainer;
    }
}
?>