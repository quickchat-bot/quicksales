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
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Parser\Models\Rule;

use Base\Library\Rules\SWIFT_Rules;
use Knowledgebase\Admin\LoaderMock;
use PHPUnit\Framework\Constraint\IsType;
use SWIFT_Exception;

/**
 * Class ParserRuleTest
 * @group parser-models
 */
class ParserRuleTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Models\Rule\SWIFT_ParserRule', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessUpdatePoolReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->ProcessUpdatePool(),
            'Returns false');

        $obj->_updatePool = ['key' => 'value'];

        $this->assertTrue($obj->ProcessUpdatePool(),
            'Returns true');


        $obj->SetIsClassLoaded(false);

        $this->assertFalse($obj->ProcessUpdatePool(),
            'Returns false');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetParserRuleIDReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_INT, $obj->GetParserRuleID(),
            'Returns int');

        $obj->SetIsClassLoaded(false);

        $this->expectException(SWIFT_Rule_Exception::class);
        $obj->GetParserRuleID();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDataStoreReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertEquals($obj->_dataStore, $obj->GetDataStore(),
            'Returns _dataStore');

        $obj->SetIsClassLoaded(false);

        $this->assertClassNotLoaded($obj, 'GetDataStore');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertyReturnsTrue()
    {
        $obj = $this->getMocked();

        $obj->_dataStore['key'] = 'value';
        $this->assertEquals('value', $obj->GetProperty('key'));

        $this->setExpectedException(SWIFT_Rule_Exception::class);
        $obj->GetProperty('no_key');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertyClassNotLoaded()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetProperty', ['key']);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateThrowsException()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Rule_Exception::class, SWIFT_INVALIDDATA);
        $obj->Create('', 1, 1, 1, 1, false, [], []);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateReturnsParserRule()
    {
        $obj = $this->getMocked();

        $_criteriaContainer = ['_criteria'];
        $_actionsContainer = [ ['name' => '_actions', 'typechar' => ''] ];

        $this->assertInstanceOf(SWIFT_ParserRule::class,
            $obj->Create('title', true, 1, 1, 1, false,
                $_criteriaContainer, $_actionsContainer));

        static::$databaseCallback['Insert_ID'] = function() {
            return null;
        };

        $this->setExpectedException(SWIFT_Rule_Exception::class);
        $obj->Create('title', true, 1, 1, 1, false,
            $_criteriaContainer, $_actionsContainer);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateReturnsTrue()
    {
        $obj = $this->getMocked();

        $_criteriaContainer = ['_criteria'];
        $_actionsContainer = [ ['name' => '_actions', 'typeid' => 0] ];

        $this->assertTrue($obj->Update('title', true, 1, 1, 1, false,
            $_criteriaContainer, $_actionsContainer));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'Update', 'title', true, 1, 1, 1, false, [], []);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateThrowsException()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Rule_Exception::class);
        $this->assertTrue($obj->Update('title', true, 1, 1, 1, false, null, null));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Delete());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'Delete');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->DeleteList('non_array'));

        $count = 0;
        static::$nextRecordType = \SWIFT_TestCase::NEXT_RECORD_RETURN_CALLBACK;
        static::$databaseCallback['NextRecord'] = function () use (&$count) {
            if ($count == 0) {
                \SWIFT::GetInstance()->Database->Record['title'] = 'title';
                \SWIFT::GetInstance()->Database->Record['parserruleid'] = 1;
                $count++;
                return true;
            } else {
                return false;
            }
        };

        $this->assertTrue($obj->DeleteList([2]));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEnableListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->EnableList('non_array'));

        $this->assertFalse($obj->EnableList([2]));

        $count = 0;
        static::$nextRecordType = \SWIFT_TestCase::NEXT_RECORD_RETURN_CALLBACK;
        static::$databaseCallback['NextRecord'] = function () use (&$count) {
            if ($count == 0) {
                \SWIFT::GetInstance()->Database->Record['title'] = 'title';
                \SWIFT::GetInstance()->Database->Record['parserruleid'] = 1;
                $count++;
                return true;
            } elseif ($count == 1) {
                \SWIFT::GetInstance()->Database->Record['title'] = 'title';
                \SWIFT::GetInstance()->Database->Record['parserruleid'] = 1;
                \SWIFT::GetInstance()->Database->Record['isenabled'] = '1';
                $count++;
                return true;
            } else {
                return false;
            }
        };

        $this->assertTrue($obj->EnableList([2]));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDisableListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->DisableList('non_array'));

        $this->assertFalse($obj->DisableList([2]));

        $count = 0;
        static::$nextRecordType = \SWIFT_TestCase::NEXT_RECORD_RETURN_CALLBACK;
        static::$databaseCallback['NextRecord'] = function () use (&$count) {
            if ($count == 0) {
                \SWIFT::GetInstance()->Database->Record['title'] = 'title';
                \SWIFT::GetInstance()->Database->Record['parserruleid'] = 1;
                $count++;
                return true;
            } elseif ($count == 1) {
                \SWIFT::GetInstance()->Database->Record['title'] = 'title';
                \SWIFT::GetInstance()->Database->Record['parserruleid'] = 1;
                \SWIFT::GetInstance()->Database->Record['isenabled'] = '0';
                $count++;
                return true;
            } else {
                return false;
            }
        };

        $this->assertTrue($obj->DisableList([2]));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetCriteriaPointerReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_ARRAY, $obj->GetCriteriaPointer());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testExtendCustomCriteriaReturnsTrue()
    {
        $obj = $this->getMocked();

        $_criteriaPointer = [];

        $count = 0;
        static::$nextRecordType = \SWIFT_TestCase::NEXT_RECORD_RETURN_CALLBACK;
        static::$databaseCallback['NextRecord'] = function () use (&$count) {
            if ($count == 0) {
                \SWIFT::GetInstance()->Database->Record['title'] = 'title';
                \SWIFT::GetInstance()->Database->Record['bayescategoryid'] = 1;
                \SWIFT::GetInstance()->Database->Record['ticketstatusid'] = 1;
                \SWIFT::GetInstance()->Database->Record['tickettypeid'] = 1;
                \SWIFT::GetInstance()->Database->Record['priorityid'] = 1;
                \SWIFT::GetInstance()->Database->Record['usergroupid'] = 1;
                \SWIFT::GetInstance()->Database->Record['staffid'] = 1;
                \SWIFT::GetInstance()->Database->Record['fullname'] = 1;
                \SWIFT::GetInstance()->Database->Record['emailqueueid'] = 1;
                $count++;
                return true;
            } else {
                $count = 0;
                return false;
            }
        };

        $this->assertTrue($obj->ExtendCustomCriteria($_criteriaPointer));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetRuleTypeLabelCompletely()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->GetRuleTypeLabel('invalid'));
        $this->assertNotNull($obj->GetRuleTypeLabel(SWIFT_ParserRule::TYPE_PREPARSE));
        $this->assertNotNull($obj->GetRuleTypeLabel(SWIFT_ParserRule::TYPE_POSTPARSE));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testExecuteAllRulesReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->ExecuteAllRules('invalid', []));

        static::$databaseCallback['CacheGet'] = function ($x) {
            switch ($x) {
                case 'parserrulecache':
                    return [ '1' => [
                        'ruletype' => SWIFT_ParserRule::TYPE_PREPARSE,
                        'isenabled' => '1',
                        'matchtype' => SWIFT_Rules::RULE_MATCHALL,
                        '_criteria' => [ '_criteria' ],
                        ] ];
                    break;
            }
        };

        $count = 0;
        static::$nextRecordType = \SWIFT_TestCase::NEXT_RECORD_RETURN_CALLBACK;
        static::$databaseCallback['NextRecord'] = function () use (&$count) {
            if ($count == 0) {
                \SWIFT::GetInstance()->Database->Record['rulematchtype'] = SWIFT_Rules::RULE_MATCHALL;
                \SWIFT::GetInstance()->Database->Record['parserrulecriteriaid'] = 1;
                \SWIFT::GetInstance()->Database->Record['name'] = 'name';
                \SWIFT::GetInstance()->Database->Record['ruleop'] = 'ruleop';
                \SWIFT::GetInstance()->Database->Record['rulematch'] = 'rulematch';
                $count++;
                return true;
            } else {
                $count = 0;
                return false;
            }
        };

        $this->assertInternalType(IsType::TYPE_ARRAY, $obj->ExecuteAllRules(SWIFT_ParserRule::TYPE_PREPARSE, []));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRebuildCacheReturnsTrue()
    {
        $obj = $this->getMocked();

        $count = 0;
        static::$nextRecordType = \SWIFT_TestCase::NEXT_RECORD_RETURN_CALLBACK;
        static::$databaseCallback['NextRecord'] = function () use (&$count) {
            if ($count == 0) {
                \SWIFT::GetInstance()->Database->Record['rulematchtype'] = SWIFT_Rules::RULE_MATCHALL;
                \SWIFT::GetInstance()->Database->Record['parserruleid'] = 1;
                \SWIFT::GetInstance()->Database->Record['parserrulecriteriaid'] = 1;
                \SWIFT::GetInstance()->Database->Record['name'] = 'name';
                \SWIFT::GetInstance()->Database->Record['ruleop'] = 'ruleop';
                \SWIFT::GetInstance()->Database->Record['rulematch'] = 'rulematch';
                $count++;
                return true;
            } else {
                $count = 0;
                return false;
            }
        };

        $this->assertTrue($obj->RebuildCache());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testClearActionsListReturnsFalse()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->ClearActionsListProxy('non_array'));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testClearCriteriaListReturnsFalse()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->ClearCriteriaListProxy('non_array'));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetActionContainerReturnsArray()
    {
        $obj = $this->getMocked();


        $count = 0;
        static::$nextRecordType = \SWIFT_TestCase::NEXT_RECORD_RETURN_CALLBACK;
        static::$databaseCallback['NextRecord'] = function () use (&$count) {
            if ($count == 0) {
                \SWIFT::GetInstance()->Database->Record['parserruleactionid'] = 1;
                $count++;
                return true;
            } else {
                $count = 0;
                return false;
            }
        };

        $this->assertInternalType(IsType::TYPE_ARRAY, $obj->GetActionContainer());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetActionContainer');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testClearActionsThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'ClearActions');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testClearCriteriaThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'ClearCriteria');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertActionsCompletely()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->InsertActions('non_array'));

        // for coverage's sake
        $obj->InsertActions([ 'action' ]);

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException(SWIFT_Rule_Exception::class, SWIFT_CLASSNOTLOADED);
        $obj->InsertActions([ 'action' ]);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertCriteriaCompletely()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->InsertCriteria('non_array'));

        // a case
        $this->assertTrue($obj->InsertCriteria(['criteria']));

        // another case
        $this->assertTrue($obj->InsertCriteria([
            ['name' => 'name', 'ruleop' => 'ruleop']
        ]));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException(SWIFT_Rule_Exception::class, SWIFT_CLASSNOTLOADED);
        $obj->InsertCriteria([ 'action' ]);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSetRulePropertiesThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException(SWIFT_Exception::class, SWIFT_CLASSNOTLOADED);
        $obj->SetRuleProperties(null);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_ParserRuleMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Models\Rule\SWIFT_ParserRuleMock');
    }
}

class SWIFT_ParserRuleMock extends SWIFT_ParserRule
{
    public $_dataStore;
    public $_updatePool;

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'parserruleid' => 1,
            'totalitems' => 1,
            'matchtype' => SWIFT_Rules::RULE_MATCHALL,
            '_criteria' => SWIFT_Rules::CRITERIA_MATCHTYPEEXT,
        ]);

        parent::__construct(1);
    }

    public function ClearCriteriaListProxy($_parserRuleIDList = []) {
        return self::ClearCriteriaList($_parserRuleIDList);
    }

    public function ClearActionsListProxy($_parserRuleIDList = []) {
        return self::ClearActionsList($_parserRuleIDList);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

