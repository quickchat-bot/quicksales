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

namespace Tests\Api\Knowledgebase;

use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use Tests\Api\BaseApiTestCase;

/**
 * Class CategoryTest
 * @group knowledgebase
 * @group kbcategory
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
        $response = $this->getResponse('/Knowledgebase/Category', 'POST', [
            'title' => 'Custom',
            'categorytype' => SWIFT_KnowledgebaseCategory::TYPE_GLOBAL,
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['kbcategory']['title']);
        static::$_id = $list['kbcategory']['id'];
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testGetAll(): void
    {
        // TODO: test ListAll/$count$/$start$/$sortField$/$sortOrder$
        $response = $this->getResponse('/Knowledgebase/Category');
        $list = $this->getArrayFromResponse($response);
        $id = static::$_id;
        if (!isset($list['kbcategory']['title'])) {
            $list = array_values(array_filter($list['kbcategory'], function ($ug) use ($id) {
                return $ug['id'] === $id;
            }));
        } else {
            $list = [$list['kbcategory']];
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
        $response = $this->getResponse('/Knowledgebase/Category/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['kbcategory']['title']);
    }

    /**
     * Test PUT endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetFromId
     */
    public function testPut(): void
    {
        $response = $this->getResponse('/Knowledgebase/Category/' . static::$_id, 'PUT', [
            'title' => 'NewCustom',
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('NewCustom', $list['kbcategory']['title']);
    }

    /**
     * Test DELETE endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPut
     */
    public function testDelete(): void
    {
        $response = $this->getResponse('/Knowledgebase/Category/' . static::$_id, 'DELETE');
        $this->assertEmpty($response->getBody()->getContents());
    }
}
