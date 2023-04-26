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
 * Date Testing
 *
 * @author Varun Shoor
 */
class SWIFT_DateTest extends SWIFT_TestCase
{

    /**
     * Tests the Calendar Date Format Function
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function testGetCalendarDateFormatUS()
    {
        $mockSet = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockSet->method('Get')->willReturn('us');

        SWIFT::GetInstance()->Settings = $mockSet;

        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Settings->UpdateKey('settings', 'dt_caltype', 'us');

        $_dateFormat = SWIFT_Date::GetCalendarDateFormat();

        $this->assertEquals('m/d/Y', $_dateFormat);

        return true;
    }

    /**
     * Tests the Calendar Date Format Function: EU
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function testGetCalendarDateFormatEU()
    {
        $mockSet = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockSet->method('Get')->willReturn('eu');

        SWIFT::GetInstance()->Settings = $mockSet;

        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Settings->UpdateKey('settings', 'dt_caltype', 'eu');

        $_dateFormat = SWIFT_Date::GetCalendarDateFormat();

        $this->assertEquals('d/m/Y', $_dateFormat);

        return true;
    }

    /**
     * Test the Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function testConstruct()
    {
        $_SWIFT_DateObject = new SWIFT_Date();
        $this->assertInstanceOf('SWIFT_Date', $_SWIFT_DateObject);

        return true;
    }

    /**
     * Test the Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function testDestruct()
    {
        $_SWIFT_DateObject = new SWIFT_Date();
        $_SWIFT_DateObject->__destruct();

        $this->assertInstanceOf('SWIFT_Date', $_SWIFT_DateObject);

        return true;
    }

}
?>