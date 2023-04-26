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

namespace Tests\Api\News;

use Tests\Api\BaseApiTestCase;

/**
 * Class CategoryTest
 * @group news
 * @group newscategory
 */
class CategoryTest extends BaseApiTestCase
{
    private static $_id;

    /**
     * Test POST endpoint
     * ** THIS SHOULD BE THE FIRST TEST IN ORDER TO TEST CRUD **
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testPost(): void
    {
        $response = $this->getResponse('/News/Category', 'POST', [
            'title' => 'Custom',
            'visibilitytype' => SWIFT_PUBLIC,
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['newscategory']['title']);
        static::$_id = $list['newscategory']['id'];
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testGetAll(): void
    {
        $response = $this->getResponse('/News/Category');
        $list = $this->getArrayFromResponse($response);
        $id = static::$_id;
        if (!isset($list['newscategory']['title'])) {
            $list = array_values(array_filter($list['newscategory'], function ($ug) use ($id) {
                return $ug['id'] === $id;
            }));
        } else {
            $list = [$list['newscategory']];
        }
        $this->assertEquals('Custom', $list[0]['title']);
    }

    /**
     * Test GET from ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAll
     */
    public function testGetFromId(): void
    {
        $response = $this->getResponse('/News/Category/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['newscategory']['title']);
    }

    /**
     * Test PUT endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetFromId
     */
    public function testPut(): void
    {
        $response = $this->getResponse('/News/Category/' . static::$_id, 'PUT', [
            'title' => 'NewCustom',
            'visibilitytype' => SWIFT_PUBLIC,
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('NewCustom', $list['newscategory']['title']);
    }

    /**
     * Test DELETE endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPut
     */
    public function testDelete(): void
    {
        $response = $this->getResponse('/News/Category/' . static::$_id, 'DELETE');
        $this->assertEmpty($response->getBody()->getContents());
    }
}
