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

use Base\Models\User\SWIFT_UserOrganization;
use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_UserOrganizationTest
 * @group base
 * @group base-api
 */
class Controller_UserOrganizationTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\Api\Controller_UserOrganization', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->Record = [
            'userorganizationid' => 1,
            'organizationname' => 'Test',
            'address' => 'test address',
            'city' => 'test',
            'state' => 'il',
            'postalcode' => '50506',
            'country' => 'US',
            'phone' => '50506',
            'postalcode' => '50506',
            'fax' => '45434',
            'dateline' => 12344343,
            'lastupdate' => 12344343,
            'slaplanid' => 1,
            'slaexpirytimeline' => 324434
        ];

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

        \SWIFT::GetInstance()->Database->Record = [
            'userorganizationid' => 1,
            'organizationtype' => SWIFT_UserOrganization::TYPE_SHARED,
            'organizationname' => 'Test',
            'address' => 'test address',
            'city' => 'test',
            'state' => 'il',
            'postalcode' => '50506',
            'country' => 'US',
            'phone' => '50506',
            'postalcode' => '50506',
            'fax' => '45434',
            'dateline' => 12344343,
            'lastupdate' => 12344343,
            'slaplanid' => 1,
            'slaexpirytimeline' => 324434
        ];

        $this->assertTrue($obj->Get(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Get', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPostReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Post(),
            'Returns false');

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'slaplancache') {
                return [
                    1 => []
                ];
            }
        };

        $_POST['slaplanid'] = 2;

        $this->assertFalse($obj->Post(),
            'Returns false');

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['userorganizationid' => 1]);

        $_POST['slaplanid'] = 1;
        $_POST['name'] = 'test';
        $_POST['organizationtype'] = 'shared';
        $_POST['address'] = 'address';
        $_POST['city'] = 'city';
        $_POST['state'] = 'state';
        $_POST['postalcode'] = '60609';
        $_POST['country'] = 'us';
        $_POST['phone'] = '10273';
        $_POST['fax'] = '2344235';
        $_POST['website'] = 'website';
        $_POST['slaplanexpiry'] = 134312423423;

        $this->assertFalse($obj->Post(),
            'Returns false');

        $this->assertClassNotLoaded($obj, 'Post');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPutReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Put(1),
            'Returns false');

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'slaplancache') {
                return [
                    1 => []
                ];
            }
        };

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['userorganizationid' => 1, 'organizationtype' => 'shared']);

        $_POST['slaplanid'] = 2;

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $_POST['slaplanid'] = 1;

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $_POST['name'] = 'test';
        $_POST['organizationtype'] = 'shared';
        $_POST['address'] = 'address';
        $_POST['city'] = 'city';
        $_POST['state'] = 'state';
        $_POST['postalcode'] = '60609';
        $_POST['country'] = 'us';
        $_POST['phone'] = '10273';
        $_POST['fax'] = '2344235';
        $_POST['website'] = 'website';
        $_POST['slaplanexpiry'] = 134312423423;

        $this->assertTrue($obj->Put(1),
            'Returns true');

        $_POST['organizationtype'] = 'restricted';

        $this->assertTrue($obj->Put(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Put', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Delete(1),
            'Returns false');

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['userorganizationid' => 1, 'organizationtype' => 'shared']);

        $this->assertTrue($obj->Delete(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Delete', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessUserOrganizationsClassNotLoaded()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod('Base\Api\Controller_UserOrganizationMock', 'ProcessUserOrganizations');

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);

        $obj->SetIsClassLoaded(false);

        $method->invoke($obj);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_UserOrganizationMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Api\Controller_UserOrganizationMock');
    }
}

class Controller_UserOrganizationMock extends Controller_UserOrganization
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

