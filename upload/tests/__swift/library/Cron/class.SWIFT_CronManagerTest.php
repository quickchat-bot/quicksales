<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Andriy Lesyuk
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2013, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

/**
 * Cron Manager Test Cases
 *
 * @author Andriy Lesyuk
 */
class SWIFT_CronManagerTest extends SWIFT_TestCase
{

    /**
     * Test Addition of Months
     *
     * @author Andriy Lesyuk
     * @return bool "true" on Success, "false" otherwise
     */
    public function testAddMonth()
    {
        $_date1stJan = strtotime('1 January 2013');

        $_date1stFeb = SWIFT_CronManager::AddMonth($_date1stJan, 1);

        $this->assertInternalType('integer', $_date1stFeb);
        $this->assertEquals('01/02/2013', strftime("%d/%m/%Y", $_date1stFeb));

        $_date1stApr = SWIFT_CronManager::AddMonth($_date1stJan, 1, 3);

        $this->assertInternalType('integer', $_date1stApr);
        $this->assertEquals('01/04/2013', strftime("%d/%m/%Y", $_date1stApr));

        $_date31thJan = strtotime('31 January 2013');

        $_date28thFeb = SWIFT_CronManager::AddMonth($_date31thJan, 31);

        $this->assertInternalType('integer', $_date28thFeb);
        $this->assertEquals('28/02/2013', strftime("%d/%m/%Y", $_date28thFeb));
    }

}
?>