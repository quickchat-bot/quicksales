<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Banjo Mofesola Paul <banjo.paul@aurea.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Parser\Library\UnifiedSearch;

use Base\Models\Staff\SWIFT_Staff;
use Knowledgebase\Admin\LoaderMock;
use PHPUnit\Framework\Constraint\IsType;
use SWIFT_Exception;

/**
 * Class UnifiedSearch_parserTest
 * @group parser-library
 */
class UnifiedSearch_parserTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Library\UnifiedSearch\SWIFT_UnifiedSearch_parser', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_ARRAY, $obj->Search());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'Search');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchRulesReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_ARRAY, $obj->SearchRules());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'SearchRules');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchQueuesReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_ARRAY, $obj->SearchQueues());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'SearchQueues');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_UnifiedSearch_parserMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Library\UnifiedSearch\SWIFT_UnifiedSearch_parserMock');
    }
}

class SWIFT_UnifiedSearch_parserMock extends SWIFT_UnifiedSearch_parser
{
    public $_data;

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);
        $this->_data = [];

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($this->_data);

        parent::__construct('query', \SWIFT_Interface::INTERFACE_ADMIN, new SWIFT_Staff(), 1);
    }

    public function SearchRules()
    {
        return parent::SearchRules(); // TODO: Change the autogenerated stub
    }

    public function SearchQueues()
    {
        return parent::SearchQueues(); // TODO: Change the autogenerated stub
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

