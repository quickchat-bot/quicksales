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

namespace Tests\Api\Base;

use Tests\Api\BaseApiTestCase;

/**
 * Class UserOrganizationTest
 * @group base
 * @group userorganization
 */
class UserOrganizationTest extends BaseApiTestCase
{
    private static $_id;

    /**
     * Test POST endpoint
     * ** THIS SHOULD BE THE FIRST TEST IN ORDER TO TEST CRUD **
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testPost(): void
    {
        $response = $this->getResponse('/Base/UserOrganization', 'POST', [
            'name' => 'Custom',
            'organizationtype' => 'restricted',
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['userorganization']['name']);
        static::$_id = $list['userorganization']['id'];
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testGetAll(): void
    {
        $response = $this->getResponse('/Base/UserOrganization');
        $list = $this->getArrayFromResponse($response);
        $id = static::$_id;
        $list = array_values(array_filter($list['userorganization'], function($ug)  use($id) { return $ug['id'] === $id;}));
        $this->assertEquals('Custom', $list[0]['name']);
    }

    /**
     * Test GET from ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAll
     */
    public function testGetFromId(): void
    {
        $response = $this->getResponse('/Base/UserOrganization/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['userorganization']['name']);
    }

    /**
     * Test PUT endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetFromId
     */
    public function testPut(): void
    {
        $response = $this->getResponse('/Base/UserOrganization/' . static::$_id, 'PUT', [
            'name' => 'NewCustom',
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('NewCustom', $list['userorganization']['name']);
    }

    /**
     * Test DELETE endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPut
     */
    public function testDelete(): void
    {
        $response = $this->getResponse('/Base/UserOrganization/' . static::$_id, 'DELETE');
        $this->assertEmpty($response->getBody()->getContents());
    }
}
