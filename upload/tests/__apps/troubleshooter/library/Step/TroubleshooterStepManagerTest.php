<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Troubleshooter\Library\Step;

/**
 * Class TroubleshooterStepManagerTest
 * @group troubleshooter
 */
class GetSubTroubleshooterTree extends \SWIFT_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')
            ->willReturnOnConsecutiveCalls(true, false, true, false);

        $this->mockProperty($mockDb, 'Record', [
            'troubleshootercategoryid' => 1,
            'troubleshooterstepid' => 1,
            'childtroubleshooterstepid' => 1,
            'parenttroubleshooterstepid' => 0,
            'title' => 'title',
            'categorytype' => 1,
        ]);

        \SWIFT::GetInstance()->Database = $mockDb;
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetCategoryOptionsReturnsArray()
    {
        $obj = new SWIFT_TroubleshooterStepManager();

        $categoryOptions = $obj::GetCategoryOptions(1, [1], 1, true);
        $this->assertArrayHasKey('checked',
            $categoryOptions[0]);

        $categoryOptions = $obj::GetCategoryOptions(1, [0], 2, true);
        $this->assertInternalType(
            'array',
            $categoryOptions[0]);

        $categoryOptions = $obj::GetCategoryOptions(1, [1]);
        $this->assertArrayHasKey(
            'selected',
            $categoryOptions[0]);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetCategoryTreeReturnsArray()
    {
        $obj = new SWIFT_TroubleshooterStepManager();

        $this->assertContains('<ul class="swifttree">',
            $obj::GetCategoryTree(),
            'Method returns HTML code');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetSubCategoryOptionsReturnsArray()
    {
        $obj = new MgrMock();
        $this->assertInternalType('array', $obj->CallGetSubCategoryOptions());
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetSubTroubleshooterTreeReturnsHtml()
    {
        $obj = new MgrMock();
        $this->assertTrue($obj->CallGetSubTroubleshooterTree());
    }
}

class MgrMock extends SWIFT_TroubleshooterStepManager
{
    /**
     * MgrMock constructor.
     */
    public function __construct()
    {
        self::$_troubleshooterStepCache['_troubleshooterParentMap'] = [
            [
                [
                    'troubleshootercategoryid' => 1,
                    'troubleshooterstepid' => 1,
                    'childtroubleshooterstepid' => 1,
                    'parenttroubleshooterstepid' => 0,
                    'title' => 'title',
                    'categorytype' => 1,
                ]
            ],
            [
                [
                    'troubleshootercategoryid' => 1,
                    'troubleshooterstepid' => 1,
                    'childtroubleshooterstepid' => 1,
                    'parenttroubleshooterstepid' => 0,
                    'title' => 'title',
                    'categorytype' => 1,
                ]
            ],
        ];
    }

    /**
     * @return mixed
     * @throws \SWIFT_Exception
     */
    public function CallGetSubCategoryOptions()
    {
        $arr = [];

        return self::GetSubCategoryOptions(1, [1], 0, $arr, 0, 0, true);
    }

    /**
     * @return mixed
     * @throws \SWIFT_Exception
     */
    public function CallGetSubTroubleshooterTree()
    {
        $html = '';

        return self::GetSubTroubleshooterTree(1, 0, $html);
    }
}
