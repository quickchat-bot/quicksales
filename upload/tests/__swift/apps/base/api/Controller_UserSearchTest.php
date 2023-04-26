<?php
/**
 * ###############################################
 *
 * Kayako Classic
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

namespace Base\Api;

use Base\Models\User\SWIFT_User;
use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_UserSearchTest
 * @group base
 * @group base-api
 */
class Controller_UserSearchTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\Api\Controller_UserSearch', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPostReturnsTrue()
    {
        $obj = $this->getMocked();

        $_POST['query'] = 'query';

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        \SWIFT::GetInstance()->Database->Record = [
            'userid' => 1,
            'linktypeid' => 1,
            'email' => 'test@test.com',
            'userrole' => SWIFT_User::ROLE_MANAGER,
            'salutation' => SWIFT_User::SALUTATION_MR
        ];

        $this->assertTrue($obj->Post());

        $this->assertClassNotLoaded($obj, 'Post');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->GetList(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'GetList');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Get(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Get');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPutReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Put(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Put');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Delete(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Delete');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_UserSearchMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Api\Controller_UserSearchMock');
    }
}

class Controller_UserSearchMock extends Controller_UserSearch
{
    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct();
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

