<?php

namespace LiveChat\Models\Visitor;


use SWIFT_TestCase;

class SWIFT_VisitorTest extends SWIFT_TestCase
{
    public function visitorActivityProvider()
    {
        return [
            [null],
            [''],
            [' '],
            [0],
            ['0'],
            [1000],
            ['1000'],
            ['abcd']
        ];
    }

    /**
     * @dataProvider visitorActivityProvider
     * @param $visitorActivity
     */
    public function testVisitorActivityFlush($visitorActivity)
    {
        error_reporting(E_ALL);
        $settings = $this->createMock(\SWIFT_Settings::class);
        $settings->method('Get')
            ->will($this->returnValueMap([
                ['security_visitorinactivity', $visitorActivity]
            ]));

        $swift = \SWIFT::GetInstance();
        $swift->Settings = $settings;

        $db = $this->createMock(\SWIFT_Database::class);
        $db->method('Query')
            ->willReturn([['sessionid' => 'abcd']]);
        $db->method('NextRecord')
            ->will($this->onConsecutiveCalls(
                [['sessionid' => 1]],
                false));
        $db->Record2 = ['sessionid' => 1];
        $swift->Database = $db;

        $actual = SWIFT_Visitor::Flush();
        $this->assertTrue($actual);
    }
}
