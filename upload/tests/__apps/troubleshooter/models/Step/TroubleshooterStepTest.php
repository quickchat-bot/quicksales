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

namespace Troubleshooter\Models\Step;

use SWIFT_Exception;
use Troubleshooter\Models\Category\CategoryMock;

/**
 * Class TroubleshooterStepTest
 * @group troubleshooter
 */
class TroubleshooterStepTest extends \SWIFT_TestCase
{
    /**
     * @param bool $loaded
     * @return StepMock
     * @throws SWIFT_Exception
     */
    public function getStep($loaded = true)
    {
        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            '1' => [
                'displayorder' => 0,
            ]
        ]);

        $mockDB = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDB->method('NextRecord')->willReturnOnConsecutiveCalls(true, false, true, false);
        $mockDB->method('AutoExecute')->willReturn(true);
        $mockDB->method('QueryFetch')
            ->willReturn([
                'troubleshooterstepid' => 1,
                'steptype' => '1',
                'staffvisibilitycustom' => '1',
                'displayorder' => '0',
            ]);
        $mockDB->method('Insert_ID')
            ->willReturnOnConsecutiveCalls(1, 0);

        $this->mockProperty($mockDB, 'Record', [
            'troubleshooterstepid' => 1,
        ]);

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        \SWIFT::GetInstance()->Language = $mockLang;
        \SWIFT::GetInstance()->Database = $mockDB;
        \SWIFT::GetInstance()->Cache = $mockCache;

        $data = new \SWIFT_DataID(1);
        $data->SetIsClassLoaded($loaded);
        $obj = new StepMock($data);
        $this->mockProperty($obj, 'Database', $mockDB);

        return $obj;
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testConstructorThrowsException()
    {
        $this->setExpectedException('SWIFT_Exception', 'Failed to load Troubleshooter Step Object');
        $this->getStep(false);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDestructorCallsDestruct()
    {
        $obj = $this->getStep();
        $obj->_updatePool = [];
        $this->assertNotNull($obj);
        $obj->__destruct();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testProcessUpdatePoolThrowsException()
    {
        $obj = $this->getStep();
        $obj->_updatePool = [];
        $this->assertFalse($obj->ProcessUpdatePool());
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->ProcessUpdatePool();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetTroubleshooterStepIDThrowsException()
    {
        $obj = $this->getStep();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetTroubleshooterStepID();
    }

    /**
     * @throws \SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testLoadDataReturnsTrueWithValidStepId()
    {
        $obj = $this->getStep();

        // LoadData is protected. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('LoadData');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($obj,
            new \SWIFT_DataStore(['troubleshooterstepid' => 1])));

        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invoke($obj, new \SWIFT_DataStore([]));
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetDataStoreThrowsException()
    {
        $obj = $this->getStep();
        $this->assertNotNull($obj->GetDataStore());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetDataStore();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetPropertyThrowsException()
    {
        $obj = $this->getStep();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetProperty('');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetPropertyThrowsInvalidDataException()
    {
        $obj = $this->getStep();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->GetProperty('invalid');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testIsValidStatusReturnsFalse()
    {
        $obj = $this->getStep();
        $this->assertFalse($obj::IsValidStatus(0),
            'Returns false with invalid status');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testCreateThrowsInvalidDataException()
    {
        $obj = $this->getStep();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj::Create(1, 0, '', '', 0, false);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testCreateThrowsCreateFailedException()
    {
        $obj = $this->getStep();
        // decrease id
        $this->setExpectedException('SWIFT_Exception', SWIFT_CREATEFAILED);
        \SWIFT::GetInstance()->Database->Insert_ID();
        $obj::Create(1, 1, 'subject', 'contents', 0, false);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testUpdateThrowsNotLoadedException()
    {
        $obj = $this->getStep();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Update('', '', 0, 0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testUpdateStatusThrowsNotLoadedException()
    {
        $obj = $this->getStep();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->UpdateStatus(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testUpdateStatusThrowsInvalidDataException()
    {
        $obj = $this->getStep();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->UpdateStatus(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getStep();
        $this->assertTrue($obj->Delete(),
            'Returns true after deleting');

        // class is already unloaded
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Delete();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testDeleteListReturnsFalse()
    {
        $obj = $this->getStep();
        $this->assertFalse($obj::DeleteList(''),
            'Returns false if array is not provided');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetLastDisplayOrderReturnsInt()
    {
        $obj = $this->getStep();
        $this->assertEquals(1, $obj::GetLastDisplayOrder(1));
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRetrieveStepsReturnsArray()
    {
        $obj = $this->getStep();
        $this->assertArrayHasKey('_troubleshooterStepContainer', $obj::RetrieveSteps(1));
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testProcessPostAttachmentsReturnsTrue()
    {
        $obj = $this->getStep();

        $_POST['_existingAttachmentIDList'] = [1];

        $this->assertFalse($obj->ProcessPostAttachments(),
            'Returns false if no files are defined');

        $tmpfname = tempnam('/tmp', 'FOO');

        $_FILES['trattachments'] = [
            'name' => ['FOO'],
            'type' => ['text/plain'],
            'size' => [3],
            'error' => [0],
            'tmp_name' => [$tmpfname],
        ];

        $this->assertTrue($obj->ProcessPostAttachments(),
            'Returns true if after processing attachments');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->ProcessPostAttachments();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRetrieveSubStepsReturnsArray()
    {
        $obj = $this->getStep();
        $this->assertInternalType('array', $obj::RetrieveSubSteps(1, 1));
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetStatusLabelReturnsLabel()
    {
        $obj = $this->getStep();
        $this->assertEquals('published', $obj::GetStatusLabel(1));
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetStatusLabelThrowsException()
    {
        $obj = $this->getStep();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj::GetStatusLabel(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetStatusLabelThrowsExceptionWithInvalidStatus()
    {
        $obj = $this->getStep();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj::GetStatusLabel(3);
    }
}

class StepMock extends SWIFT_TroubleshooterStep
{
    public $_updatePool;

    public function __destruct()
    {
        // prevent exception to be thrown when destroying the object and it's not loaded
        $this->SetIsClassLoaded(true);
        parent::__destruct();
    }

    public static function IsValidStatus($_troubleshooterStatus)
    {
        return $_troubleshooterStatus > 0;
    }
}
