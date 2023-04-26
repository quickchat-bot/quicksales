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

namespace News\Rss;

use News\Admin\LoaderMock;

/**
 * Class Controller_FeedTest
 * @group news
 */
class Controller_FeedTest extends \SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance()
    {
        $obj = new Controller_FeedMock();
        $this->assertInstanceOf('News\Rss\Controller_Feed', $obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturnOnConsecutiveCalls('0', '1');

        $mockMgr = $this->getMockBuilder('News\Library\Rss\SWIFT_NewsRSSManager')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Controller_FeedMock([
            'Settings' => $mockSettings,
            'NewsRSSManager' => $mockMgr,
        ]);

        $this->assertFalse($obj->Index());
        $this->assertTrue($obj->Index());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Index();
    }
}

class Controller_FeedMock extends Controller_Feed
{
    /**
     * Controller_FeedMock constructor.
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->Load = new LoaderMock();
        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }
        parent::__construct();
    }

    public function Initialize()
    {
        return true;
    }
}
