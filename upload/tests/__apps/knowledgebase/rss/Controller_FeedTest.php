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

namespace Knowledgebase\Rss;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_FeedTest
 * @group knowledgebase
 */
class Controller_FeedTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsInstance()
    {
        $obj = new Controller_FeedMock();
        $this->assertInstanceOf('Knowledgebase\Rss\Controller_Feed', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $obj = new Controller_FeedMock();

        $settings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('Get')->willReturnOnConsecutiveCalls(0, 1);

        $mgr = $this->getMockBuilder('Knowledgebase\Library\Rss\SWIFT_KnowledgebaseRSSManager')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['Dispatch'])
            ->getMock();

        $obj->KnowledgebaseRSSManager = $mgr;

        $this->mockProperty($obj, 'Settings', $settings);

        $this->assertFalse($obj->Index(),
            'Returns false with kb_enrss = 0');

        $this->assertTrue($obj->Index(),
            'Returns true with kb_enrss = 1');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Index();
    }
}

class Controller_FeedMock extends Controller_Feed
{
    public function __construct()
    {
        $this->Load = new LoaderMock();
        parent::__construct();
    }

    protected function Initialize()
    {
        return true;
    }
}
