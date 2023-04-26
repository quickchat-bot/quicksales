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
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Knowledgebase\Library\Category;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
 * Class KnowledgebaseCategoryManagerTest
 * @group knowledgebase
 */
class KnowledgebaseCategoryManagerTest extends \SWIFT_TestCase
{
    /**
     * @param array $_cache
     * @return SWIFT_KnowledgebaseCategoryManagerMock
     * @throws SWIFT_Exception
     */
    public function getLibrary($_cache = [])
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([1]);

        SWIFT::GetInstance()->Cache = $mockCache;

        SWIFT::GetInstance()->Load = new LoaderMock();

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturn('1');

        SWIFT::GetInstance()->Settings = $mockSettings;

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetProperty')->willReturn('1');

        SWIFT::GetInstance()->Staff = $mockStaff;

        return new SWIFT_KnowledgebaseCategoryManagerMock([
            'Language' => $mockLang,
            'Settings' => $mockSettings,
            'Database' => $mockDb,
        ], $_cache);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetCategoryOptionsReturnsArray()
    {
        $obj = $this->getLibrary();
        $this->assertInternalType('array', $obj::GetCategoryOptions([0], 1, true));
        $this->assertInternalType('array', $obj::GetCategoryOptions([0], 1, false));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetCategoryTreeReturnsHtml()
    {
        $obj = $this->getLibrary();
        $this->assertContains('<ul class="swifttree">', $obj::GetCategoryTree());
    }

    /**
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testGetSubKnowledgebaseCategoryTreeReturnsHtml()
    {
        $obj = $this->getLibrary([
            '_knowledgebaseParentMap' => [
                2 => [
                    [
                        'kbcategoryid' => 2,
                    ],
                ],
            ],
        ]);
        $mock = new \ReflectionClass($obj);
        $method = $mock->getMethod('GetSubKnowledgebaseCategoryTree');
        $method->setAccessible(true);
        $html = '';
        $this->assertContains('<li><span class="folder">', $method->invokeArgs($obj, [2, 2, &$html]));
    }

    /**
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testGetSubCategoryOptionsReturnsArray()
    {
        $obj = $this->getLibrary([
            '_knowledgebaseParentMap' => [
                3 => [
                    [
                        'kbcategoryid' => 1,
                    ],
                    [
                        'kbcategoryid' => 3,
                    ],
                ],
            ],
        ]);
        $mock = new \ReflectionClass($obj);
        $method = $mock->getMethod('GetSubCategoryOptions');
        $method->setAccessible(true);
        $arr = [];
        $this->assertInternalType('array', $method->invokeArgs($obj, [[3], 3, &$arr, 0, 1, true]));
        $this->assertInternalType('array', $method->invokeArgs($obj, [[3], 3, &$arr, 0, 1, false]));
    }
}

class SWIFT_KnowledgebaseCategoryManagerMock extends SWIFT_KnowledgebaseCategoryManager
{
    private static $_count1 = 0;
    private static $_count2 = 0;

    /**
     * SWIFT_KnowledgebaseCategoryManagerMock constructor.
     * @param array $services
     * @param array $_cache
     * @throws SWIFT_Exception
     */
    public function __construct(array $services = [], array $_cache = [])
    {
        $this->Load = new LoaderMock();
        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }
        parent::__construct();
        static::$_knowledgebaseCategoryCache = empty($_cache) ? false : $_cache;
    }

    public function Initialize()
    {
        return true;
    }

    /**
     * Mock method to prevent infinite recursion
     *
     * @param int $_selectedKnowledgebaseCategoryID
     * @param int $_parentKnowledgebaseCategoryID
     * @param string $_renderHTML
     * @return mixed|string
     * @throws SWIFT_Exception
     */
    protected static function GetSubKnowledgebaseCategoryTree(
        $_selectedKnowledgebaseCategoryID,
        $_parentKnowledgebaseCategoryID,
        &$_renderHTML
    ) {
        if ((int)$_parentKnowledgebaseCategoryID === 2) {
            static::$_count1++;
            if (static::$_count1 >= 2) {
                return $_renderHTML;
            }
        }

        return parent::GetSubKnowledgebaseCategoryTree($_selectedKnowledgebaseCategoryID,
            $_parentKnowledgebaseCategoryID,
            $_renderHTML);
    }

    /**
     * Mock method to prevent infinite recursion
     *
     * @param $_selectedKnowledgebaseCategoryIDList
     * @param int $_parentKnowledgebaseCategoryID
     * @param $_optionContainer
     * @param int $_indent
     * @param bool $_activeKnowledgebaseCategoryID
     * @param bool $_isCheckbox
     * @return mixed|string
     */
    protected static function GetSubCategoryOptions($_selectedKnowledgebaseCategoryIDList, $_parentKnowledgebaseCategoryID, &$_optionContainer, $_indent = 0,
        $_activeKnowledgebaseCategoryID = false, $_isCheckbox = false) {
        if ((int)$_parentKnowledgebaseCategoryID === 3) {
            static::$_count2++;
            if (static::$_count2 % 2 === 0) {
                return $_optionContainer;
            }
        }

        return parent::GetSubCategoryOptions($_selectedKnowledgebaseCategoryIDList, $_parentKnowledgebaseCategoryID, $_optionContainer, $_indent,
            $_activeKnowledgebaseCategoryID, $_isCheckbox);
    }
}
