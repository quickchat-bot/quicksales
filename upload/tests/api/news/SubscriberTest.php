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
 * Class SubscriberTest
 * @group news
 * @group subscriber
 */
class SubscriberTest extends BaseApiTestCase
{
    private static $_id;

    /**
     * Test POST endpoint
     * ** THIS SHOULD BE THE FIRST TEST IN ORDER TO TEST CRUD **
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testPost(): void
    {
        $response = $this->getResponse('/News/Subscriber', 'POST', [
            'email' => 'testsubscriber@mail.com',
            'isvalidated' => 1,
            'sendemails' => 0,
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('testsubscriber@mail.com', $list['newssubscriber']['email']);
        static::$_id = $list['newssubscriber']['id'];
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testGetAll(): void
    {
        $response = $this->getResponse('/News/Subscriber');
        $list = $this->getArrayFromResponse($response);
        $id = static::$_id;
        if (!isset($list['newssubscriber']['email'])) {
            $list = array_values(array_filter($list['newssubscriber'], function ($ug) use ($id) {
                return $ug['id'] === $id;
            }));
        } else {
            $list = [$list['newssubscriber']];
        }
        $this->assertEquals('testsubscriber@mail.com', $list[0]['email']);
    }

    /**
     * Test GET from ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAll
     */
    public function testGetFromId(): void
    {
        $response = $this->getResponse('/News/Subscriber/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('testsubscriber@mail.com', $list['newssubscriber']['email']);
    }

    /**
     * Test PUT endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetFromId
     */
    public function testPut(): void
    {
        $response = $this->getResponse('/News/Subscriber/' . static::$_id, 'PUT', [
            'email' => 'newtestsubscriber@mail.com',
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('newtestsubscriber@mail.com', $list['newssubscriber']['email']);
    }

    /**
     * Test DELETE endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPut
     */
    public function testDelete(): void
    {
        $response = $this->getResponse('/News/Subscriber/' . static::$_id, 'DELETE');
        $this->assertEmpty($response->getBody()->getContents());
    }
}
